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
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

class SetupGrabber extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'grabber:start';

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
        if (env('STOP_GRABBER')) {
            return;
        }

        $this->logger->info('[Started] Grabber');

        SystemAccount::query()
            ->where('is_work', '=', false)
            ->where('is_blocked', '!=', true)
            ->where(
                'start_work_at',
                '<=',
                Carbon::now()->addMinutes(-(+env('ACCOUNT_ACTIVITY_TIME') + +env('ACCOUNT_TIME_TO_SLEEP')))
            )->update([
                'start_work_at' => Carbon::now(),
            ]);

        $systemAccounts = SystemAccount::with('proxy')
            ->where('is_work', '=', false)
            ->where('is_blocked', '!=', true)
            ->where(
                'start_work_at',
                '>=',
                Carbon::now()->addMinutes(-(+env('ACCOUNT_ACTIVITY_TIME') - +env('GRABBER_TIMING')))
            )
            ->get();

        $systemAccountsCount = $systemAccounts->count();

        if ($systemAccountsCount === 0) {

            $this->logger->info('[Finished] Grabber: 0 accounts');
            return;
        }

        $countRequests = 60 / +env('API_TIMING') * (+env('GRABBER_TIMING'));


        $accountsChunks = Account::query()
            ->whereColumn('created_at', '=', 'updated_at')
            ->orWhere('updated_at', '<=', Carbon::today())
            ->orWhereHas('statistics', function (Builder $query) {
                return $query
                    ->where('created_at', '>=', Carbon::today());
            }, '=', 0)
            ->get()
            ->chunk($countRequests * 4);

        foreach ($accountsChunks as $chunk) {
            $account = $systemAccounts->pop();
            $this->logger->info('[Started] Grabber - accounts to GetInfoFromAccounts: ' . json_encode($chunk->pluck('login')) . ' with - ' . $account->login);

            dispatch(new GetInfoFromAccounts($chunk, $account));
        }

        $accounts = Account::query()
            ->where('is_private', '=', false)
            ->whereHas('statistics', function (Builder $query) {
                return $query
                    ->where('is_posts_loaded', '=', false)
                    ->where('created_at', '>=', Carbon::today());
            }, '=', 1)
            ->with('lastStatistic')
            ->get();

        $this->logger->info('[Started] Grabber - accounts to GetPostsInfo: ' . json_encode($accounts->pluck('login')));


        foreach ($accounts as $account) {
            $countAccounts = max(ceil($account->lastStatistic->media_count / (18 * $countRequests)), 1);

            if ((int)$countAccounts <= count($systemAccounts)) {
                $pushSystemAccounts = [];
                for ($i = 0; $i < $countAccounts; $i++) {
                    $pushSystemAccounts[] = $systemAccounts->pop();
                }
                dispatch(new GetPostsInfo($account, $pushSystemAccounts));
            } else {
                $this->logger->error('Can grub posts from: ' . $account->login);
            }
        }

        if (count($systemAccounts) === 0) {
            return;
        }

        $accounts = Account::query()
            ->where('is_private', '=', false)
            ->whereHas('statistics', function (Builder $query) {
                return $query
                    ->where('is_subscribers_loaded', '=', false)
                    ->where('created_at', '>=', Carbon::today());
            }, '=', 1)
            ->with('lastStatistic')
            ->get()
            ->sortBy('lastStatistic.follower_count');

        $this->logger->info('[Started] Grabber - accounts to GetSubsInfoFromAccount: ' . json_encode($accounts->pluck('login')));

        $cantGrubSubs = false;

        foreach ($accounts as $account) {
            $countAccounts = ceil($account->lastStatistic->follower_count / (190 * $countRequests));

            if ((int)$countAccounts <= count($systemAccounts)) {
                $pushSystemAccounts = [];
                for ($i = 0; $i < $countAccounts; $i++) {
                    $pushSystemAccounts[] = $systemAccounts->pop();
                }
                dispatch(new GetSubsInfoFromAccount($account, $pushSystemAccounts));
            } else if ($systemAccountsCount < $countAccounts) {
                $this->logger->error('Can grub subs from: ' .
                    $account->login . 'need more accounts -> ' .
                    $systemAccountsCount . ' : ' . $countAccounts);
            } else {
                $cantGrubSubs = true;
                $this->logger->error('Can grub subs from: ' . $account->login);
            }
        }

        if (count($systemAccounts) === 0 || $cantGrubSubs) {
            return;
        }

        $systemAccountsChunks = $systemAccounts->chunk(4);

        foreach ($systemAccountsChunks as $key => $systemAccountsChunk) {
            $notUpdatedFollowersToUpdate = Follower::query()
                ->whereColumn('created_at', '=', 'updated_at')
                ->limit(count($systemAccountsChunk) * $countRequests)
                ->offset(count($systemAccountsChunk) * $countRequests * $key)
                ->orderBy('updated_at')
                ->get();

            if ($notUpdatedFollowersToUpdate->count() === 0) {
                break;
            }

            $sysAcc = $systemAccountsChunks->shift();

            $this->logger->info('[Started] Grabber - not updated accounts to GetAccountsInfo: ' . json_encode($sysAcc->pluck('login')));

            dispatch(new GetAccountsInfo($notUpdatedFollowersToUpdate, $sysAcc));
        }

        if ($systemAccountsChunks->count() === 0) {
            return;
        }

        foreach ($systemAccountsChunks as $key => $systemAccountsChunk) {
            $updatedFollowersToUpdate = Follower::query()
                ->whereColumn('created_at', '!=', 'updated_at')
                ->orWhere('updated_at', '<=', Carbon::today())
                ->limit(count($systemAccountsChunk) * $countRequests)
                ->offset(count($systemAccountsChunk) * $countRequests * $key)
                ->orderBy('updated_at')
                ->get();

            if ($updatedFollowersToUpdate->count() === 0) {
                break;
            }

            $sysAcc = $systemAccountsChunks->shift();

            $this->logger->info('[Started] Grabber - updated accounts to GetAccountsInfo: ' . json_encode($sysAcc->pluck('login')));

            dispatch(new GetAccountsInfo($updatedFollowersToUpdate, $sysAcc));
        }

        $this->logger->info('[Finished] Grabber');
    }
}
