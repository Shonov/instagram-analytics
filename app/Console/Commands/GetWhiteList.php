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
use Grabber\StatisticBundle\Services\StatisticsService;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use InstagramAPI\Instagram;
use InstagramAPI\Signatures;

class GetWhiteList extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'white-list:start';

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
        $ig = new Instagram(false, false, []);

        $loginResponse = $ig->login('', '');

        dump($loginResponse);

        $userUUID = Signatures::generateUUID();
        $followersNextMaxId = null;

        while (true) {

            $followersResponse = $ig->people->getFollowing('', $userUUID, null, $followersNextMaxId);

            foreach ($followersResponse->getUsers() as $follower) {
                echo $follower->asArray()['username'] . PHP_EOL;
            }

            $followersNextMaxId = $followersResponse->getNextMaxId();

            if ($followersNextMaxId === null) {
                break;
            }

            sleep(5);
        }

        return true;
    }
}
