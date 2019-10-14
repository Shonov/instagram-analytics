<?php

namespace App\Jobs;

use App\SystemAccount;
use Grabber\InstagramAccountsBundle\Models\Account;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use InstagramAPI\Exception\EmptyResponseException;
use InstagramAPI\Exception\InstagramException;
use InstagramAPI\Exception\NetworkException;
use InstagramAPI\Exception\NotFoundException;
use InstagramAPI\Instagram;
use Log;
use Throwable;

class GetAccountsInfo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * @var Collection|Account[]
     */
    private $accounts;
    /**
     * @var Collection|SystemAccount[]
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
     * The console command logger.
     *
     * @var \Illuminate\Support\Facades\Log
     */
    protected $logger;

    /**
     * Create a new job instance.
     *
     * @param Collection|Account[] $accounts
     * @param Collection|SystemAccount[] $systemAccounts
     */
    public function __construct($accounts, $systemAccounts)
    {
        $this->accounts = $accounts;
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
        $startTime = time();

        foreach ($this->systemAccounts as $systemAccount) {
            $ig = new Instagram(false, false, []);

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

        foreach ($this->accounts as $key => $account) {
            $startAccountTime = microtime(true);

            $this->logger->info('[Start][' . $this->systemAccounts[$this->currentAccountIndex]->login . ']  Grab account info: ' . $account->id);

            try {
                $startRequestTime = microtime(true);

                $info = $this->currentAccount->people->getInfoById($account->id)->getUser();

                $requestTime = round(microtime(true) - $startRequestTime, 4);

                $startUpdateTime = microtime(true);

                $account->update([
                    'full_name' => $info->getUsername(),
                    'profile_pic_url' => $info->getProfilePicUrl(),
                    'media_count' => $info->getMediaCount(),
                    'following_count' => $info->getFollowingCount(),
                    'follower_count' => $info->getFollowerCount(),
                    'is_private' => $info->getIsPrivate(),
                    'is_business' => $info->getIsBusiness(),
                    //'gender' => $info->getGender(),
                ]);

                $updateTime = round(microtime(true) - $startUpdateTime, 4);

            } catch (NotFoundException $e) {
                $startUpdateTime = microtime(true);

                $account->update([
                    'full_name' => 'deleted',
                    'profile_pic_url' => 'deleted',
                    'media_count' => 0,
                    'following_count' => 0,
                    'follower_count' => 0,
                    'is_private' => false,
                    'is_business' => false,
                ]);

                $updateTime = round(microtime(true) - $startUpdateTime, 4);

            } catch (EmptyResponseException | NetworkException $exception) {
                $this->nextAccount();
                continue;
            }

            $startTouchTime = microtime(true);

            $account->touch();

            $touchTime = round(microtime(true) - $startTouchTime, 4);

            $this->nextAccount();


            if (!isset($requestTime)) {
                $requestTime = 'Not Found Account';
            }

            $this->logger->info('[Finish]' .
                '[' . $this->systemAccounts[$this->currentAccountIndex]->login . '] ' .
                'Grab account info: ' . $account->id .
                ' [ ' . round(microtime(true) - $startAccountTime, 4) . ' ]' .
                '. Request: ' . $requestTime .
                '. Update: ' . $updateTime .
                '. Touch: ' . $touchTime
            );
        }

        foreach ($this->systemAccounts as $systemAccount) {
            $systemAccount->update([
                'is_work' => false,
            ]);
        }

        $this->logger->info('start time: ' . $startTime);
        $this->logger->info('all time: ' . (time() - $startTime));
    }

    public function nextAccount()
    {
        if ($this->currentAccountIndex === (count($this->igs) - 1)) {
            $this->currentAccountIndex = 0;

            $time = time() - $this->lastTick;

            if ($time < +env('API_TIMING')) {
                $this->logger->info('Last tick: ' . $this->lastTick . ' - current time:  ' . time() . ' - time to wait: ' . $time);

                sleep(+env('API_TIMING') - $time);
            }

            $this->lastTick = time();
        } else {
            $this->currentAccountIndex++;
        }

        $this->currentAccount = $this->igs[$this->currentAccountIndex];
    }
}
