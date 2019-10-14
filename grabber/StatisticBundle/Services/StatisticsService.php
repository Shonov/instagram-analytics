<?php
/**
 * getGraphd by PhpStorm.
 * User: Vitaly
 * Date: 30.08.2018
 * Time: 20:51
 */

namespace Grabber\StatisticBundle\Services;


use App\SystemAccount;
use Carbon\Carbon;
use Grabber\InstagramAccountsBundle\Models\Account;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StatisticsService
{
    public function getPeriodOfTime(Account $account, Request $request)
    {
        $startDate = $request->get('startDate', null);
        $endDate = $request->get('endDate', null);

        if ($startDate === null) {
            $startDate = $account->created_at;
        } else {
            $startDate = new Carbon($startDate);
        }

        if ($endDate === null) {
            $endDate = Carbon::today();
        } else {
            $endDate = new Carbon($endDate);
        }

        return [
            $startDate,
            $endDate
        ];
    }

    public function getStatistic(Account $account, $startDate, $endDate)
    {
        Log::channel('querylog')->info('[Start] getStatistic():          id ' . $account->id);
        $startTime = microtime(true);

        $statistic = $this->getGeneralStatistic($account, $startDate, $endDate);

        $yesterday = $account->statistics()
            ->where([
                ['created_at', '>=', $startDate->copy()->addDays(-1)],
                ['created_at', '<', $startDate],
            ])
            ->first();

        $values = [];
        $maxChange = 0;
        $averageLikes = 0;
        $videoViews = 0;
        if (count($statistic) > 1) {
            for ($i = 0; $i < count($statistic); $i++) {
                $values['followers'][] = $statistic[$i]['follower_count'];
                $values['likes'][] = $statistic[$i]['like_count'];
                $values['views'][] = $statistic[$i]['views_count'];
//                if ($i !== 0) {
//                    if ($statistic[$i - 1]['views_count'] < $statistic[$i]['views_count']) {
//                        $values['views'][] = 0;
//                        continue;
//                    }
//                    $values['views'][] = $statistic[$i - 1]['views_count'] - $statistic[$i]['views_count'];
//                }
            }
//            if ($yesterday === null) {
//                $values['views'][count($values['views'])] = 0;
//            } else {
//                $values['views'][] = $statistic[count($statistic) - 1]['views_count'] - $yesterday->views_count;
//            }
//            $maxChange = max($values['followers']) - min($values['followers']);
            for ($i = 0; $i < count($values['followers']) - 1; $i++) {
                $current = $values['followers'][$i] - $values['followers'][$i + 1];
                if ($current > $maxChange) {
                    $maxChange = $current;
                }
            }

            $averageLikes = intval(floor((max($values['likes']) - min($values['likes'])) / count($statistic)));
            $videoViews = array_reverse($values['views']);
        } else if (count($statistic) === 1 && $yesterday !== null) {
            $maxChange = $statistic[0]['follower_count'] - $yesterday->follower_count;
            $averageLikes = $statistic[0]['like_count'];
            $videoViews = [$statistic[0]['views_count'] - $yesterday->views_count];
        }

        Log::channel('querylog')->info('[End]   getStatistic():          id ' . $account->id . '     [ ' . round(microtime(true) - $startTime, 4) . ' s' . ' ]' . "\n");

        $lastStatistic = $statistic[0];

        return [
            'days' => $statistic,
            'maxChangePerDay' => $maxChange,
            'averageLikesPerDay' => $averageLikes,
            'videoViews' => $videoViews,
            'time' => round(microtime(true) - $startTime, 4),
            'time_to_grab_subs' => $lastStatistic->time_to_grab_subs,
            'can_view' => $lastStatistic->can_view,
        ];
    }

    public function getGeneralStatistic(Account $account, $startDate, $endDate)
    {
        Log::channel('querylog')->info('[Start] getGeneralStatistic():   id ' . $account->id);
        $startTime = microtime(true);

        $statistic = $account->statistics()
            ->where([
                ['created_at', '>=', $startDate],
                ['created_at', '<', $endDate->copy()->addDays(1)],
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        Log::channel('querylog')->info('[End]   getGeneralStatistic():   id ' . $account->id . '     [ ' . round(microtime(true) - $startTime, 4) . ' s' . ' ]' . "\n");
        return $statistic;
    }

    /**
     * Graph Gender of followers
     */
    public function getGenderFollowers(Account $account, $endDate)
    {
        Log::channel('querylog')->info('[Start] getGenderFollowers():   id ' . $account->id);
        $startTime = microtime(true);
        $start = microtime(true);

        $genderFollowers = [];
        foreach (['male' => 1, 'female' => 0, 'undefined' => null] as $key => $value) {
            $genderFollowers[$key] = $account->followers()
                ->where([
                    ['gender', $value],
                    ['instagram_account_followers.created_at', '>=', $endDate],
                    ['instagram_account_followers.created_at', '<', $endDate->copy()->addDays(1)],
                ])
                ->count();

            Log::channel('querylog')->info("        " . $key . ":  " . '[ ' . (microtime(true) - $start) . ' s ]');
            $start = microtime(true);
        }
        Log::channel('querylog')->info('[End]   getGenderFollowers():   id ' . $account->id . '     [ ' . round(microtime(true) - $startTime, 4) . ' s' . ' ]' . "\n");
        return $genderFollowers;
    }

    /**
     * Graph Privacy of Followers
     */
    public function getPrivacyFollowers(Account $account, $endDate)
    {
        Log::channel('querylog')->info('[Start] getPrivacyFollowers():   id ' . $account->id);
        $startTime = microtime(true);
        $start = microtime(true);

        $result['private'] = DB::table('instagram_profiles')
            ->select('is_private')
            ->whereIn('id', function (Builder $query) use ($account, $endDate) {
                return $query->select('instagram_profile_id')
                    ->from('instagram_account_followers')
                    ->where([
                        ['instagram_account_id', $account->id],
                        ['created_at', '>=', $endDate],
                        ['created_at', '<', $endDate->copy()->addDays(1)],
                    ]);
            })
            ->where('is_private', true)
            ->count();

        Log::channel('querylog')->info("        private:  " . '     [ ' . (microtime(true) - $start) . ' s ]');
        $start = microtime(true);

        $result['open'] = DB::table('instagram_profiles')
            ->select('is_private')
            ->whereIn('id', function (Builder $query) use ($account, $endDate) {
                return $query->select('instagram_profile_id')
                    ->from('instagram_account_followers')
                    ->where([
                        ['instagram_account_id', $account->id],
                        ['created_at', '>=', $endDate],
                        ['created_at', '<', $endDate->copy()->addDays(1)],
                    ]);
            })
            ->where('is_private', false)
            ->count();

        Log::channel('querylog')->info("        open:  " . '        [ ' . (microtime(true) - $start) . ' s ]');
        Log::channel('querylog')->info('[End]   getPrivacyFollowers():   id ' . $account->id . '     [ ' . round(microtime(true) - $startTime, 4) . ' s' . ' ]' . "\n");
        return $result;
    }

    /**
     * Graph Business Accounts
     */
    public function getBusinessAccounts(Account $account, $endDate)
    {
        Log::channel('querylog')->info('[Start] getBusinessAccounts():   id ' . $account->id);
        $startTime = microtime(true);

        $data = [
            "business" => DB::table('instagram_profiles')
                ->whereIn('id', function (Builder $query) use ($account, $endDate) {
                    return $query->select('instagram_profile_id')
                        ->from('instagram_account_followers')
                        ->where([
                            ['instagram_account_id', $account->id],
                            ['created_at', '>=', $endDate],
                            ['created_at', '<', $endDate->copy()->addDays(1)],
                        ]);
                })
                ->where('is_business', true)
                ->count('*'),
        ];

        $data['normal'] = $account->lastStatistic->follower_count - +$data['business'];

        Log::channel('querylog')->info('[End]   getBusinessAccounts():   id ' . $account->id . '     [ ' . round(microtime(true) - $startTime, 4) . ' s' . ' ]' . "\n");
        return $data;
//        return [
//            "business" => DB::table('instagram_profiles')
//                ->whereIn('id', function (Builder $query) use ($account, $endDate) {
//                    return $query->select('instagram_profile_id')
//                        ->from('instagram_account_followers')
//                        ->where([
//                            ['instagram_account_id', $account->id],
//                            ['created_at', '>=', $endDate],
//                            ['created_at', '<', $endDate->copy()->addDays(1)],
//                        ]);
//                })
//                ->where('is_business', true)
//                ->count('*'),
//            "normal" => DB::table('instagram_profiles')
//                ->whereIn('id', function (Builder $query) use($account, $endDate) {
//                    return $query->select('instagram_profile_id')
//                        ->from('instagram_account_followers')
//                        ->where([
//                            ['instagram_account_id', $account->id],
//                            ['created_at', '>=', $endDate],
//                            ['created_at', '<', $endDate->copy()->addDays(1)],
//                        ]);
//                })
//                ->where('is_business', true)
//                ->count('*')
//        ];
    }

    /**
     * Graph Distribution by Follower Count
     */
    public function getDistByFollowerCount(Account $account, $endDate)
    {
        Log::channel('querylog')->info('[Start] getDistByFollowerCount():   id ' . $account->id);
        $startTime = microtime(true);

        $conditions = [
            'less_10' => [
                'right' => '10',
            ],
            'more_10_less_100' => [
                'left' => '10',
                'right' => '100',
            ],
            'more_100_less_1k' => [
                'left' => '100',
                'right' => '1000',
            ],
            'more_1k_less_10k' => [
                'left' => '1000',
                'right' => '10000',
            ],
            'more_10k_less_100k' => [
                'left' => '10000',
                'right' => '100000',
            ],
            'more_100k_less_1m' => [
                'left' => '100000',
                'right' => '1000000',
            ],
            'more_1m' => [
                'left' => '1000000',
            ],
        ];
        $result = $this->getStatisticForGraphs($account, $conditions, 'follower_count', $endDate);

        Log::channel('querylog')->info('[End]   getDistByFollowerCount():   id ' . $account->id . '     [ ' . round(microtime(true) - $startTime, 4) . ' s' . ' ]' . "\n");
        return $result;
    }

    public function getStatisticForGraphs(Account $account, Array $conditions, $cond, $endDate)
    {
        Log::channel('querylog')->info('    [Start] getStatisticForGraphs():   id ' . $account->id);
        $startTime = microtime(true);

        $array = [];

        foreach ($conditions as $key => $condition) {
            $start = microtime(true);

            $query = $account->followers()
                ->wherePivot('created_at', '>=', $endDate)
                ->wherePivot('created_at', '<', $endDate->copy()->addDays(1));

            if (isset($condition['left'])) {
                $query = $query->where($cond, '>=', $condition['left']);
            }

            if (isset($condition['right'])) {
                $query = $query->where($cond, '<', $condition['right']);
            }
            $array[$key] = $query->count();

            Log::channel('querylog')->info("        " . $key . ":     " . '[ ' . (microtime(true) - $start) . ' s ]');
        }

        Log::channel('querylog')->info('    [End]   getStatisticForGraphs():   id ' . $account->id . '     [ ' . round(microtime(true) - $startTime, 4) . ' s' . ' ]');
        return $array;
    }

    /**
     * Graph Distribution by Following Count
     */
    public function getDistByFollowingCount(Account $account, $endDate)
    {
        Log::channel('querylog')->info('[Start] getDistByFollowingCount():   id ' . $account->id);
        $startTime = microtime(true);

        $conditions = [
            'more_0_less_500' => [
                'left' => '0',
                'right' => '500',
            ],
            'more_500_less_1k' => [
                'left' => '500',
                'right' => '1000',
            ],
            'more_1k_less_2k' => [
                'left' => '1000',
                'right' => '2000',
            ],
            'more_2k' => [
                'left' => '2000',
            ],
        ];
        $result = $this->getStatisticForGraphs($account, $conditions, 'following_count', $endDate);

        Log::channel('querylog')->info('[End]   getDistByFollowingCount():   id ' . $account->id . '     [ ' . round(microtime(true) - $startTime, 4) . ' s' . ' ]' . "\n");
        return $result;
    }

    /**
     * Graph Follower to Following Ratio (Quality Score)
     */
    public function getGraphFTFR(Account $account, $endDate)
    {
        Log::channel('querylog')->info('[Start] getGraphFTFR():   id ' . $account->id);
        $startTime = microtime(true);

        $conditions = [
            'less_02' => [
                'right' => '0.2',
            ],
            'more_02_less_1' => [
                'left' => '0.2',
                'right' => '1',
            ],
            'more_1_less_3' => [
                'left' => '1',
                'right' => '3',
            ],
            'more_3_less_10' => [
                'left' => '3',
                'right' => '10',
            ],
            'more_10' => [
                'left' => '10',
            ],
        ];
        $result = $this->getStatisticForGraphs($account, $conditions, DB::raw('case following_count when 0 then follower_count/1 else follower_count::real/following_count::real end'), $endDate);

        Log::channel('querylog')->info('[End]   getGraphFTFR():   id ' . $account->id . '     [ ' . round(microtime(true) - $startTime, 4) . ' s' . ' ]' . "\n");
        return $result;
    }

    /**
     * Graph Bots Count
     */
    public function getBotsCount(Account $account, $endDate)
    {
        Log::channel('querylog')->info('[Start] getBotsCount():   id ' . $account->id);
        $startTime = microtime(true);

        $result = $account->followers()
            ->where(function ($query) use ($account) {
                $query->where([
                    ['following_count', '>', 3000],
                    ['media_count', '<', 5]
                ])
                    ->orWhere('media_count', '<', 5);
            })
            ->where([
                ['instagram_account_followers.created_at', '>=', $endDate],
                ['instagram_account_followers.created_at', '<', $endDate->copy()->addDays(1)],
            ])
            ->count('*');

        Log::channel('querylog')->info('[End]   getGraphFTFR():   id ' . $account->id . '     [ ' . round(microtime(true) - $startTime, 4) . ' s' . ' ]' . "\n");
        return $result;
    }

    /**
     * Graph Top followers
     */
    public function getTopFollowers(Account $account, $endDate)
    {
        Log::channel('generate:statistic')->info('[Start] getTopFollowers():       id ' . $account->id);
        $startTime = microtime(true);

        $result = DB::table('instagram_profiles')
            ->select('id', 'full_name', 'profile_pic_url', 'follower_count')
            ->whereIn('id', function (Builder $query) use ($account, $endDate) {
                return $query->select('instagram_profile_id')
                    ->from('instagram_account_followers')
                    ->where([
                        ['created_at', '>=', $endDate],
                        ['instagram_account_id', '=', $account->id],
                    ]);
            })
            ->orderByDesc('follower_count')
            ->limit(10)
            ->get();

        Log::channel('generate:statistic')->info('[End]   getTopFollowers():       id ' . $account->id . '     [ ' . round(microtime(true) - $startTime, 4) . ' s' . ' ]' . "\n");
        return $result;
    }

    /**
     * Graph Top unsubscribers
     */
    public function getTopUnsubscribers(Account $account, $startDate, $endDate)
    {
        Log::channel('generate:statistic')->info('[Start] getTopUnsubscribers():   id ' . $account->id);
        $startTime = microtime(true);

        $result = DB::table('instagram_profiles')
            ->select('id', 'full_name', 'profile_pic_url', 'follower_count')
            ->whereNotIn('id', function (Builder $query) use ($account, $endDate) {
                return $query->select('instagram_profile_id')
                    ->from('instagram_account_followers')
                    ->where([
                        ['instagram_account_id', '=', $account->id],
                        ['created_at', '>=', $endDate],
                        ['created_at', '<', $endDate->copy()->addDays(1)]
                    ]);
            })
            ->whereIn('id', function (Builder $query) use ($account, $startDate, $endDate) {
                return $query->select('instagram_profile_id')
                    ->from('instagram_account_followers')
                    ->where([
                        ['instagram_account_id', '=', $account->id],
                        ['created_at', '>=', $startDate->format('Y-m-d')],
                        ['created_at', '<', $endDate],
                    ]);
            })
            ->orderByDesc('follower_count')
            ->limit(10)
            ->get();

        Log::channel('generate:statistic')->info('[End]   getTopUnsubscribers():   id ' . $account->id . '     [ ' . round(microtime(true) - $startTime, 4) . ' s' . ' ]' . "\n");
        return $result;
    }

    /**
     * Graph Real Reach
     */
    public function getRealReach(Account $account, $endDate)
    {
        Log::channel('querylog')->info('[Start] getRealReach():   id ' . $account->id);
        $startTime = microtime(true);
        $start = microtime(true);

        $result['easily'] = $account->followers()
            ->where([
                ['instagram_account_followers.created_at', '>=', $endDate],
                ['instagram_account_followers.created_at', '<', $endDate->copy()->addDays(1)],
                ['following_count', '<', 800],
            ])
            ->count('*');

        Log::channel('querylog')->info("        easily:     " . '[ ' . (microtime(true) - $start) . ' s ]');
        $start = microtime(true);

        $result['reachable'] = $account->followers()
            ->where([
                ['instagram_account_followers.created_at', '>=', $endDate],
                ['instagram_account_followers.created_at', '<', $endDate->copy()->addDays(1)],
                ['following_count', '>=', 800],
                ['following_count', '<', 1500],
            ])
            ->count('*');

        Log::channel('querylog')->info("        reachable:     " . '[ ' . (microtime(true) - $start) . ' s ]');
        $start = microtime(true);

        $result['hardly'] = $account->followers()
            ->where([
                ['instagram_account_followers.created_at', '>=', $endDate],
                ['instagram_account_followers.created_at', '<', $endDate->copy()->addDays(1)],
                ['following_count', '>=', 1500],
                ['following_count', '<', 3000],
            ])
            ->count('*');

        Log::channel('querylog')->info("        hardly:     " . '[ ' . (microtime(true) - $start) . ' s ]');
        $start = microtime(true);

        $result['unreachable'] = $account->followers()
            ->where([
                ['instagram_account_followers.created_at', '>=', $endDate],
                ['instagram_account_followers.created_at', '<', $endDate->copy()->addDays(1)],
                ['following_count', '>=', 3000],
            ])
            ->count('*');

        Log::channel('querylog')->info("        unreachable:     " . '[ ' . (microtime(true) - $start) . ' s ]');
        Log::channel('querylog')->info('[End]   getRealReach():   id ' . $account->id . '     [ ' . round(microtime(true) - $startTime, 4) . ' s' . ' ]' . "\n");
        return $result;
    }

    /**
     * Graph Engagement rate
     */
    public function getEngagementRate(Account $account, $startDate, $endDate)
    {
        Log::channel('querylog')->info('[Start] getEngagementRate():     id ' . $account->id);
        $startTime = microtime(true);
        if ($account->medias()->count() != 0 && ($account->actualFollowers()->count() != 0)) {
            $statistic = $this->getGeneralStatistic($account, $startDate, $endDate);
            $profileER = [];
            foreach ($statistic as $item) {
                $profileER[] = (object)[
                    'created_at' => $item->created_at,
                    'engagement' => round(($item->like_count + $item->comment_count) / $item->follower_count, 2),
                ];
            }
            $engagementRate = $profileER[0]->engagement;
            $profileER = array_reverse($profileER);
        } else {
            $profileER = [];
            $engagementRate = [0];
        }

        Log::channel('querylog')->info('[End]   getEngagementRate():     id ' . $account->id . '     [ ' . round(microtime(true) - $startTime, 4) . ' s' . ' ]' . "\n");
        return [
            'engagement' => $engagementRate,
            'profileEngagement' => $profileER
        ];
    }
//    /**
//     Graph
//     */
//    public function get(Account $account, Request $request)
//    {
//
//    }
}