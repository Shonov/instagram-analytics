<?php

namespace App\Jobs;

use App\SystemAccount;
use Grabber\InstagramAccountsBundle\Models\Account;
use Grabber\StatisticBundle\Models\Statistic;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use InstagramAPI\Exception\EmptyResponseException;
use InstagramAPI\Exception\NetworkException;
use InstagramAPI\Exception\NotFoundException;
use InstagramAPI\Instagram;
use Throwable;

class GetInfoFromAccounts implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * @var Collection|Account[]
     */
    private $accounts;
    /**
     * @var SystemAccount
     */
    private $systemAccount;
    /**
     * @var Instagram
     */
    private $ig;

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
     * @param SystemAccount $systemAccount
     */
    public function __construct($accounts, SystemAccount $systemAccount)
    {
        $this->accounts = $accounts;
        $this->systemAccount = $systemAccount;
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

        $this->logger->info('Start get account info - ' . json_encode(collect($this->accounts)->pluck('login')) . ' with - ' . $this->systemAccount->login);
        $this->ig = new Instagram(env('APP_DEBUG'), env('APP_DEBUG'), []);

        $this->ig->setProxy(
            'http://' . $this->systemAccount->proxy->login . ':' .
            $this->systemAccount->proxy->password . '@' .
            $this->systemAccount->proxy->ip . ':' .
            $this->systemAccount->proxy->http
        );

        try {
            $loginResponse = $this->ig->login($this->systemAccount->login, $this->systemAccount->password);
        } catch (EmptyResponseException | NetworkException $exception) {
            $this->logger->error('EmptyResponseException: ' . $this->systemAccount->login);
            return;
        } catch (Throwable $exception) {

            $this->systemAccount->update([
                'is_blocked' => true,
            ]);

            throw $exception;
        }

        $this->systemAccount->update([
            'is_work' => true,
        ]);

        foreach ($this->accounts as $account) {
            $this->logger->info('Get info form: ' . $account->login . ' with - ' . $this->systemAccount->login);

            try {
                $info = $this->ig->people->getInfoById($account->id)->getUser();
            }  catch (EmptyResponseException | NetworkException $exception) {
                $this->systemAccount->update([
                    'is_work' => false,
                ]);

                return;
            } catch (NotFoundException $exception) {
                continue;
            }

            $account->statistics()->save(new Statistic([
                'following_count' => $info->getFollowingCount(),
                'follower_count' => $info->getFollowerCount(),
                'media_count' => $info->getMediaCount(),
                'usertags_count' => $info->getUsertagsCount(),
                'like_count' => 0,
                'comment_count' => 0,
                'is_subscribers_loaded' => false,
                'is_posts_loaded' => false,
            ]));

            $account->update([
                'login' => $info->getUsername(),
                'pic_url' => $info->getProfilePicUrl(),
                'is_private' => $info->getIsPrivate(),
            ]);

            $account->touch();

            $this->logger->info('Stop get info: ' . $account->login . ' with - ' . $this->systemAccount->login);
            sleep(+env('API_TIMING'));
        }

        $this->systemAccount->update([
            'is_work' => false,
        ]);
    }
}
