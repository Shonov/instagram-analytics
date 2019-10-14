<?php

namespace App\Jobs;

use App\SystemAccount;
use Carbon\Carbon;
use Grabber\InstagramAccountsBundle\Models\Account;
use Grabber\InstagramAccountsBundle\Models\Media;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use InstagramAPI\Exception\EmptyResponseException;
use InstagramAPI\Exception\NetworkException;
use InstagramAPI\Instagram;
use Throwable;

class GetPostsInfo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
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
     * The console command logger.
     *
     * @var \Illuminate\Support\Facades\Log
     */
    protected $logger;

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
        $this->logger->info('Start posts info - ' . $this->account->login . ' with - ' . json_encode(collect($this->systemAccounts)->pluck('login')));

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

        $countDeleted = \DB::table('instagram_account_medias')
            ->where('instagram_account_id', '=', $this->account->id)
            ->where('created_at', '>=', Carbon::today())
            ->delete();

        $this->logger->info('[' . $this->account->login . '] deleted posts - ' . $countDeleted);


        $this->account->statistics()->orderByDesc('created_at')->first()->update([
            'is_posts_loaded' => null,
        ]);


        $this->currentAccountIndex = 0;
        $this->currentAccount = $this->igs[$this->currentAccountIndex];
        $this->lastTick = time();

        $mediaMaxId = null;

        $allStatistic = [
            'like_count' => 0,
            'comment_count' => 0,
            'views_count' => 0,
        ];

        while (true) {
            try {

                $feed = $this->currentAccount->timeline->getUserFeed($this->account->id, $mediaMaxId);
            } catch (EmptyResponseException | NetworkException $exception) {

                $this->logger->error('[Network Exception] Stop grub posts - ' . $this->account->login . ' with - ' . json_encode(collect($this->systemAccounts)->pluck('login')));

                $this->account->statistics()->orderByDesc('created_at')->first()->update([
                    'is_posts_loaded' => false,
                ]);

                foreach ($this->systemAccounts as $systemAccount) {
                    $systemAccount->update([
                        'is_work' => false,
                    ]);
                }

                return;
            }

            foreach ($feed->getItems() as $key => $post) {
                if ($post->isCarouselMedia()) {
                    $picUrl = $post->getCarouselMedia()[0]->getImageVersions2()->getCandidates()[0]->getUrl();
                } else {
                    $picUrl = $post->getImageVersions2()->getCandidates()[0]->getUrl();
                }

                $media = Media::find($post->getPk());

                $mediaData = [
                    'id' => $post->getPk(),
                    'media_type' => $post->getMediaType(),
                    'filter_type' => $post->getFilterType(),
                    'pic_url' => $picUrl,
                    'posted_at' => Carbon::createFromTimestamp(+$post->getTakenAt()),
                    'code' => $post->getCode(),
                ];


                if ($media === null) {
                    (new Media($mediaData))->save();
                } else {
                    $media->update($mediaData);
                    $media->touch();
                }

                $this->account->medias()->newExistingPivot([
                    'like_count' => $post->getLikeCount() ?? 0,
                    'comment_count' => $post->getCommentCount() ?? 0,
                    'view_count' => $post->getViewCount() ?? 0,
                    'instagram_account_id' => $this->account->id,
                    'instagram_media_id' => $post->getPk(),
                ])->save();

                $allStatistic['like_count'] += ($post->getLikeCount() ?? 0);
                $allStatistic['comment_count'] += ($post->getCommentCount() ?? 0);
                $allStatistic['views_count'] += ($post->getViewCount() ?? 0);
            }


            if (!$feed->isMoreAvailable()) {
                break;
            }
            $mediaMaxId = $feed->getNextMaxId();
        }

        foreach ($this->systemAccounts as $systemAccount) {
            $systemAccount->update([
                'is_work' => false,
            ]);
        }

        $allStatistic['is_posts_loaded'] = true;

        $this->account->statistics()->orderByDesc('created_at')->first()->update($allStatistic);
        $this->logger->info('Stop posts info - ' . $this->account->login . ' with - ' . json_encode(collect($this->systemAccounts)->pluck('login')));

    }

    public function nextAccount()
    {
        if ($this->currentAccountIndex === (count($this->igs) - 1)) {
            $this->currentAccountIndex = 0;

            $time = time() - $this->lastTick;


            if ($time < +env('API_TIMING')) {
                dump('next-account with sleep: ' . (+env('API_TIMING') - $time));

                sleep(+env('API_TIMING') - $time);
            }

            $this->lastTick = time();
        } else {
            $this->currentAccountIndex++;
        }

        $this->currentAccount = $this->igs[$this->currentAccountIndex];
    }
}
