<?php
/**
 * Created by PhpStorm.
 * User: nikita
 * Date: 21.05.18
 * Time: 23:44
 */

namespace Grabber\StatisticBundle\Controllers;

use Grabber\InstagramAccountsBundle\Models\Account;
use Grabber\StatisticBundle\Models\TopFollower;
use Grabber\StatisticBundle\Models\TopUnfollower;
use Grabber\StatisticBundle\Services\StatisticsService;
use Illuminate\Http\Request;
use Log;

class StatisticsController
{
    public $statisticsService;

    public function __construct(StatisticsService $statisticsService)
    {
        $this->statisticsService = $statisticsService;
    }

    public function getStatistic(Account $account, Request $request)
    {
        [$startDate, $endDate] = $this->statisticsService->getPeriodOfTime($account, $request);

        return response()->json([
            'statistic' => $this->statisticsService->getStatistic($account, $startDate, $endDate),
        ]);
    }

    public function getTopNewFollowers(Request $request)
    {
        $statisticId = $request->get('statistic_id');

        Log::channel('querylog')->info('[Start] getTopFollowers():       id ' . $statisticId);

        $startTime = microtime(true);

        $followers = TopFollower::query()
            ->whereStatisticId($statisticId)
            ->with('follower')
            ->get();

        Log::channel('querylog')->info('[End]   getTopFollowers():       id ' . $statisticId . '     [ ' . round(microtime(true) - $startTime, 4) . ' s' . ' ]' . "\n");

        return response()->json([
            'topNewFollowers' => $followers,
        ]);
    }

    public function getTopLostFollowers(Account $account, Request $request)
    {
        [$startDate, $endDate] = $this->statisticsService->getPeriodOfTime($account, $request);

        $statisticIds = $account->statistics()
            ->where([
                ['created_at', '>=', $startDate],
                ['created_at', '<', $endDate->copy()->addDays(1)],
            ])
            ->orderBy('created_at', 'desc')
            ->get(['id'])
            ->pluck('id');

        $topUnfollowers = TopUnfollower::query()
            ->whereIn('statistic_id', $statisticIds->toArray())
            ->orderBy('follower_count', 'DESC')
            ->with('follower')
            ->get()
            ->unique('instagram_profile_id')
            ->splice(0, 10);

        return response()->json([
            'topLostFollowers' => $topUnfollowers,
        ]);
    }

    public function getGenderFollowers(Account $account, Request $request)
    {
        $endDate = $this->statisticsService->getPeriodOfTime($account, $request)[1];

        return response()->json([
            'genderFollowers' => $this->statisticsService->getGenderFollowers($account, $endDate),
        ]);
    }

    public function getPrivateAndOpenAccounts(Account $account, Request $request)
    {
        $endDate = $this->statisticsService->getPeriodOfTime($account, $request)[1];

        return response()->json([
            'privateAndOpenAccounts' => $this->statisticsService->getPrivacyFollowers($account, $endDate),
        ]);
    }

    public function getBusinessAndUsualAccounts(Account $account, Request $request)
    {
        $endDate = $this->statisticsService->getPeriodOfTime($account, $request)[1];

        return response()->json([
            'businessAndUsualAccounts' => $this->statisticsService->getBusinessAccounts($account, $endDate),
        ]);
    }

    public function getFollowersByOurFollowers(Account $account, Request $request)
    {
        $endDate = $this->statisticsService->getPeriodOfTime($account, $request)[1];

        return response()->json([
            'followersByOurFollowers' => $this->statisticsService->getDistByFollowerCount($account, $endDate),
        ]);
    }

    public function getFollowersByOurFollowing(Account $account, Request $request)
    {
        $endDate = $this->statisticsService->getPeriodOfTime($account, $request)[1];

        return response()->json([
            'followersByOurFollowing' => $this->statisticsService->getDistByFollowingCount($account, $endDate),
        ]);
    }

    public function getFollowersAndFollowing(Account $account, Request $request)
    {
        $endDate = $this->statisticsService->getPeriodOfTime($account, $request)[1];

        return response()->json([
            'followersAndFollowing' => $this->statisticsService->getGraphFTFR($account, $endDate),
        ]);
    }

    public function getCountBots(Account $account, Request $request)
    {
        $endDate = $this->statisticsService->getPeriodOfTime($account, $request)[1];

        return response()->json([
            'countBots' => $this->statisticsService->getBotsCount($account, $endDate),
        ]);
    }

    public function getReachUsers(Account $account, Request $request)
    {
        $endDate = $this->statisticsService->getPeriodOfTime($account, $request)[1];

        return response()->json([
            'reachUsers' => $this->statisticsService->getRealReach($account, $endDate),
        ]);
    }

    public function getEngagement(Account $account, Request $request)
    {
        [$startDate, $endDate] = $this->statisticsService->getPeriodOfTime($account, $request);

        return response()->json([
            'engagementRate' => $this->statisticsService->getEngagementRate($account, $startDate, $endDate),
        ]);
    }
}