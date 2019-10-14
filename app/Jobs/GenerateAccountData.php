<?php

namespace App\Jobs;

use App\SystemAccount;
use Grabber\InstagramAccountsBundle\Models\Account;
use Grabber\StatisticBundle\Models\Statistic;
use Grabber\StatisticBundle\Models\TopFollower;
use Grabber\StatisticBundle\Models\TopUnfollower;
use Grabber\StatisticBundle\Services\StatisticsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class GenerateAccountData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * @var Account
     */
    private $account;

    /**
     * @var Statistic
     */
    private $statistic;


    /**
     * Create a new job instance.
     *
     * @param Account $account
     */
    public function __construct(Account $account, Statistic $statistic)
    {
        $this->account = $account;
        $this->statistic = $statistic;
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws Throwable
     */
    public function handle()
    {
        $statisticsService = new StatisticsService();

        TopFollower::query()
            ->whereStatisticId($this->statistic->id)
            ->delete();

        TopUnfollower::query()
            ->whereStatisticId($this->statistic->id)
            ->delete();

        $data = $statisticsService->getTopFollowers($this->account, $this->statistic->created_at);

        foreach ($data as $item) {
            $topFollower = new TopFollower([
                'statistic_id' => $this->statistic->id,
                'follower_count' => $item->follower_count,
                'instagram_profile_id' => $item->id,
            ]);

            $topFollower->save();
        }

        dump($data);

        $data = $statisticsService->getTopUnsubscribers($this->account, $this->statistic->created_at->copy()->addDays(-1), $this->statistic->created_at);

        foreach ($data as $item) {
            $topUnfollower = new TopUnfollower([
                'statistic_id' => $this->statistic->id,
                'follower_count' => $item->follower_count,
                'instagram_profile_id' => $item->id,
            ]);

            $topUnfollower->save();
        }

        dump($data);

        $notUpdatedFollowersToUpdate = $this->account->followers()
            ->where([
                ['instagram_account_followers.created_at', '>=',  $this->statistic->created_at],
                ['instagram_account_followers.created_at', '<',  $this->statistic->created_at->copy()->addDays(1)],
            ])
            ->whereColumn('instagram_profiles.created_at', '=', 'instagram_profiles.updated_at')
            ->count();

        $allFollowers = $this->statistic->follower_count;
        $canView = (($notUpdatedFollowersToUpdate / $allFollowers) * 100) <= 20 ? true : false;

        $countSystemAccounts = SystemAccount::count();

        if ($notUpdatedFollowersToUpdate !== 0 && $countSystemAccounts !== 0) {
            $timeToGrabSubs = ceil($notUpdatedFollowersToUpdate / (500 * $countSystemAccounts));
        } else if ($notUpdatedFollowersToUpdate === 0) {
            $timeToGrabSubs = 0;
        } else {
            $timeToGrabSubs = null;
        }

        $this->statistic->update([
            'can_view' => $canView,
            'time_to_grab_subs' => (int)$timeToGrabSubs,
        ]);
    }
}
