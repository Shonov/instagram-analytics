<?php

namespace App\Jobs;

use App\SystemAccount;
use Carbon\Carbon;
use Grabber\InstagramAccountsBundle\Models\Account;
use Grabber\StatisticBundle\Models\Follower;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use InstagramAPI\Exception\EmptyResponseException;
use InstagramAPI\Exception\NetworkException;
use InstagramAPI\Instagram;
use InstagramAPI\Signatures;
use Throwable;

class GetSubsInfoFromAccount implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * The console command logger.
     *
     * @var \Illuminate\Support\Facades\Log
     */
    protected $logger;
    /**
     * @var Account
     */
    private $account;
    /**
     * @var SystemAccount[]
     */
    private $systemAccounts;
    /**
     * @var Instagram[]
     */
    private $igs;
    /**
     * @var integer
     */
    private $currentAccountIndex = 0;
    /**
     * @var Instagram
     */
    private $currentAccount;
    private $lastTick = 0;

    /**
     * Create a new job instance.
     *
     * @param Account $account
     * @param SystemAccount[] $systemAccounts
     */
    public function __construct(Account $account, array $systemAccounts)
    {
        $this->account = $account;
        $this->systemAccounts = $systemAccounts;
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws Throwable
     */
    public function handle()
    {
        $this->logger = Log::channel('grabber');
        $this->logger->info('Start grub subs - ' . $this->account->login . ' with - ' . json_encode(collect($this->systemAccounts)->pluck('login')));

        foreach ($this->systemAccounts as $systemAccount) {
            $ig = new Instagram(env('APP_DEBUG'), env('APP_DEBUG'), []);

            $ig->setProxy(
                'http://' . $systemAccount->proxy->login . ':' .
                $systemAccount->proxy->password . '@' .
                $systemAccount->proxy->ip . ':' .
                $systemAccount->proxy->http
            );

            try {
                $loginResponse = $ig->login($systemAccount->login, $systemAccount->password);
            } catch (EmptyResponseException | NetworkException $exception) {
                $this->logger->error('EmptyResponseException: ' . $systemAccount->login);

                return;
            } catch (Throwable $exception) {

                $systemAccount->update([
                    'is_blocked' => true,
                ]);

                throw $exception;
            }

            $this->igs[] = $ig;
        }

        foreach ($this->systemAccounts as $systemAccount) {
            $systemAccount->update([
                'is_work' => true,
            ]);
        }

        $this->currentAccountIndex = 0;
        $this->currentAccount = $this->igs[$this->currentAccountIndex];
        $this->lastTick = time();


        $this->logger->alert('Grabber start is_subscribers_loaded');
        $this->account->statistics()->orderByDesc('created_at')->first()->update([
            'is_subscribers_loaded' => null,
        ]);

        $userUUID = Signatures::generateUUID();
        $followersNextMaxId = null;

        $followers = [];

        $countDeleted = \DB::table('instagram_account_followers')
            ->where('instagram_account_id', '=', $this->account->id)
            ->where('created_at', '>=', Carbon::today())
            ->delete();

        $this->logger->info('[' . $this->account->login . '] deleted subs - ' . $countDeleted);

        $errorsToStop = count($this->systemAccounts);

        try {

            while ($errorsToStop > 0) {
                try {

                    $followersResponse = $this->currentAccount->people->getFollowers($this->account->id, $userUUID, null, $followersNextMaxId);

                    $this->nextAccount();

                    $this->logger->info('[In progress] Grub subs - ' . $this->account->login . ' with - ' . json_encode(collect($this->systemAccounts)->pluck('login')) . ' - count: ' . count($followersResponse->getUsers()));


                    foreach ($followersResponse->getUsers() as $follower) {

                        $dbFollower = Follower::find($follower->getPk());

                        $fillableData = [
                            'id' => $follower->getPk(),
                            'full_name' => $follower->getUsername(),
                            'profile_pic_url' => $follower->getProfilePicUrl(),
                            'media_count' => 0,
                            'following_count' => 0,
                            'follower_count' => 0,
                            'is_private' => null,
                            'is_business' => null,
                        ];

                        if ($dbFollower !== null) {
                            $this->account->followers()->attach($follower->getPk());
                        } else {
                            $this->account->followers()->save(new Follower($fillableData));
                        }
                    }

                    $followersNextMaxId = $followersResponse->getNextMaxId();

                    if ($followersNextMaxId === null) {
                        break;
                    }

                } catch (EmptyResponseException | NetworkException $exception) {
                    $this->logger->error('[Network Exception][' . $errorsToStop . '] Continue grub subs - ' . $this->account->login . ' with - ' . json_encode(collect($this->systemAccounts)->pluck('login')));
                    $errorsToStop--;
                }
            }
        } catch (Throwable $exception) {
            $this->logger->error('[Network Exception] Stop grub subs - ' . $this->account->login . ' with - ' . json_encode(collect($this->systemAccounts)->pluck('login')));


            $this->account->statistics()->orderByDesc('created_at')->first()->update([
                'is_subscribers_loaded' => false,
            ]);
            foreach ($this->systemAccounts as $systemAccount) {
                $systemAccount->update([
                    'is_work' => false,
                ]);
            }

            return;
        }

        $this->logger->info('Counting grub subs - ' . $this->account->login . ' with - ' . json_encode(collect($this->systemAccounts)->pluck('login')));


        if ($this->account->statistics()->count() === 1) {
            $countUnsubs = 0;
            $countSubs = 0;
        } else {
            $countUnsubs = \DB::table('instagram_profiles')->whereIn('id', function (Builder $query) {
                return $query
                    ->select('instagram_account_followers.instagram_profile_id')
                    ->from('instagram_account_followers')
                    ->where('instagram_account_followers.instagram_account_id', '=', $this->account->id)
                    ->where('instagram_account_followers.created_at', '>=', Carbon::yesterday())
                    ->where('instagram_account_followers.created_at', '<', Carbon::today());
            })->whereNotIn('id', function (Builder $query) {
                return $query
                    ->select('instagram_account_followers.instagram_profile_id')
                    ->from('instagram_account_followers')
                    ->where('instagram_account_followers.instagram_account_id', '=', $this->account->id)
                    ->where('instagram_account_followers.created_at', '>=', Carbon::today());
            })->count();

            $countSubs = \DB::table('instagram_profiles')->whereNotIn('id', function (Builder $query) {
                return $query
                    ->select('instagram_account_followers.instagram_profile_id')
                    ->from('instagram_account_followers')
                    ->where('instagram_account_followers.instagram_account_id', '=', $this->account->id)
                    ->where('instagram_account_followers.created_at', '>=', Carbon::yesterday())
                    ->where('instagram_account_followers.created_at', '<', Carbon::today());
            })->whereIn('instagram_profiles.id', function (Builder $query) {
                return $query
                    ->select('instagram_account_followers.instagram_profile_id')
                    ->from('instagram_account_followers')
                    ->where('instagram_account_followers.instagram_account_id', '=', $this->account->id)
                    ->where('instagram_account_followers.created_at', '>=', Carbon::today());
            })->count();
        }

        $this->account->statistics()->orderByDesc('created_at')->first()->update([
            'total_sub' => $countSubs,
            'total_unsub' => $countUnsubs,
        ]);

        foreach ($this->systemAccounts as $systemAccount) {
            $systemAccount->update([
                'is_work' => false,
            ]);
        }

        $this->account->statistics()->orderByDesc('created_at')->first()->update([
            'follower_count' => \DB::table('instagram_profiles')->whereIn('instagram_profiles.id', function ($query) {
                return $query
                    ->select('instagram_account_followers.instagram_profile_id')
                    ->from('instagram_account_followers')
                    ->where('instagram_account_followers.instagram_account_id', '=', $this->account->id)
                    ->where('instagram_account_followers.created_at', '>=', Carbon::today());
            })->count(),
            'is_subscribers_loaded' => true,
        ]);

        $this->logger->info('Stop grub subs - ' . $this->account->login . ' [' . $countSubs . '][' .
            $countUnsubs . '] ' . ' with - ' . json_encode(collect($this->systemAccounts)->pluck('login'))
        );
    }

    public function nextAccount()
    {
        if ($this->currentAccountIndex === (count($this->igs) - 1)) {
            $this->currentAccountIndex = 0;

            $time = time() - $this->lastTick;

            if ($time < +env('API_TIMING')) {
                $this->logger->info('next-account with sleep - ' . (+env('API_TIMING') - $time) . ' - ' . $this->account->login . ' with - ' . json_encode(collect($this->systemAccounts)->pluck('login')));

                sleep(+env('API_TIMING') - $time);
            }
            $this->lastTick = time();
        } else {
            $this->currentAccountIndex++;
        }

        $this->currentAccount = $this->igs[$this->currentAccountIndex];
    }
}
