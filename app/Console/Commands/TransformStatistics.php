<?php

namespace App\Console\Commands;

use App\Jobs\GetAccountsInfo;
use App\Jobs\GetInfoFromAccounts;
use App\Jobs\GetPostsInfo;
use App\Jobs\GetSubsInfoFromAccount;
use App\SystemAccount;
use Carbon\Carbon;
use Grabber\InstagramAccountsBundle\Models\Account;
use Grabber\StatisticBundle\Models\Follower;
use Grabber\StatisticBundle\Models\TopFollower;
use Grabber\StatisticBundle\Models\TopUnfollower;
use Grabber\StatisticBundle\Services\StatisticsService;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

class TransformStatistics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'transform:start';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * The console command logger.
     *
     * @var \Illuminate\Support\Facades\Log
     */
    protected $logger;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->logger = Log::channel('grabber');
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        TopFollower::query()->delete();

        $statisticsService = new StatisticsService();

        $accounts = Account::all();

        foreach ($accounts as $account) {
//            $account = Account::find(5550474309);
            dump($account->toArray());
            $statistics = $statisticsService->getGeneralStatistic($account, new Carbon('2000-00-00'), (Carbon::now()));
            $statistics = $statistics->reverse();


            foreach ($statistics as $statistic) {
                dump($statistic->created_at);

                $data = $statisticsService->getTopFollowers($account, $statistic->created_at);

                foreach ($data as $item) {
                    $topFollower = new TopFollower([
                        'statistic_id' => $statistic->id,
                        'follower_count' => $item->follower_count,
                        'instagram_profile_id' => $item->id,
                    ]);

                    $topFollower->save();
                }

                $data = $statisticsService->getTopUnsubscribers($account, $statistic->created_at->copy()->addDays(-1), $statistic->created_at);

                dump($data->toArray());

                foreach ($data as $item) {
                    $topUnfollower = new TopUnfollower([
                        'statistic_id' => $statistic->id,
                        'follower_count' => $item->follower_count,
                        'instagram_profile_id' => $item->id,
                    ]);

                    $topUnfollower->save();
                }
            }
//            return;
        }

        dump('ok');

        return true;
    }
}
