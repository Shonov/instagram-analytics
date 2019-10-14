<?php
/**
 * Created by PhpStorm.
 * User: Vitaly
 * Date: 08.09.2018
 * Time: 20:10
 */

namespace Grabber\StatisticBundle\Controllers;


use Grabber\InstagramAccountsBundle\Models\Account;
use Grabber\StatisticBundle\Services\PostsService;
use Illuminate\Http\Request;

class PostsController
{
    private $postsService;

    public function __construct(PostsService $postsService)
    {
        $this->postsService = $postsService;
    }
//    public function getPostsStatistic(Account $account, Request $request)
//    {
//        [$startDate, $endDate] = (new \Grabber\StatisticBundle\Services\StatisticsService)->getPeriodOfTime($account, $request);
//        $type = $request->get('filter', 0);
//        $sorting = $request->get('sort', 0);
//
//        return response()->json([
//            'topPosts' => $this->postsService->getTopPosts($account, $endDate, $type, $sorting),
//            'numberOfPosts' => $this->postsService->getCountPosts($account, $startDate, $endDate),
//            'mostEngagingPostTypes' => $this->postsService->getMostEngagingPostTypes($account, $endDate),
//            'postTypes' => $this->postsService->getPostTypes($account, $endDate),
//        ]);
//    }

    public function getTopPosts(Account $account, Request $request)
    {
        $endDate = (new \Grabber\StatisticBundle\Services\StatisticsService)->getPeriodOfTime($account, $request)[1];
        $type = $request->get('filter', 0);
        $sorting = $request->get('sort', 0);

        return response()->json([
            'topPosts' => $this->postsService->getTopPosts($account, $endDate, $type, $sorting),
        ]);
    }
    public function getNumberPosts(Account $account, Request $request)
    {
        [$startDate, $endDate] = (new \Grabber\StatisticBundle\Services\StatisticsService)->getPeriodOfTime($account, $request);

        return response()->json([
            'numberOfPosts' => $this->postsService->getCountPosts($account, $startDate, $endDate),
        ]);
    }
    public function getMostEngagingPostTypes(Account $account, Request $request)
    {
        $endDate = (new \Grabber\StatisticBundle\Services\StatisticsService)->getPeriodOfTime($account, $request)[1];

        return response()->json([
            'mostEngagingPostTypes' => $this->postsService->getMostEngagingPostTypes($account, $endDate),
        ]);
    }
    public function getPostTypes(Account $account, Request $request)
    {
        $endDate = (new \Grabber\StatisticBundle\Services\StatisticsService)->getPeriodOfTime($account, $request)[1];

        return response()->json([
            'postTypes' => $this->postsService->getPostTypes($account, $endDate),
        ]);
    }
    public function getSortedTopPosts(Account $account, Request $request) {
        $endDate = (new \Grabber\StatisticBundle\Services\StatisticsService)->getPeriodOfTime($account, $request)[1];
        $type = intval($request->get('filter', 0));
        $sorting = intval($request->get('sort', 0));

        return response()->json([
            'topPosts' => $this->postsService->getTopPosts($account, $endDate, $type, $sorting),
        ]);
    }
}