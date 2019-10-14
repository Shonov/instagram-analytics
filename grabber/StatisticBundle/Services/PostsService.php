<?php
/**
 * Created by PhpStorm.
 * User: Vitaly
 * Date: 08.09.2018
 * Time: 20:10
 */

namespace Grabber\StatisticBundle\Services;


use Grabber\InstagramAccountsBundle\Models\Account;
use Grabber\InstagramAccountsBundle\Models\Media;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PostsService
{
    public function getTopPosts(Account $account, $endDate, $type, $sorting)
    {
        $start = microtime(true);
        Log::channel('querylog')->info('[Start] GetTopPosts():                id ' .$account->id);
        $sort = [
            0 => 'like_count + comment_count',
            1 => 'like_count',
            2 => 'comment_count',
            3 => 'view_count',
        ];
        if ($type === 3) $sorting = 2;

        $query = $account->medias()
            ->select('*')
            ->wherePivot('created_at', '>=', $endDate)
            ->wherePivot('created_at', '<',  $endDate->copy()->addDays(1));
        if ($sorting != 0) $query = $query->where('media_type', '=', $sorting);
        $query = $query->orderByDesc(DB::raw($sort[$type]))->limit(9)->get();

        Log::channel('querylog')->info('[End]   GetTopPosts():                id ' . $account->id . '     [ ' .round(microtime(true) - $start, 4).' s' . ' ]'  . "\n");
        return $query;
    }
    /**
    Graph Number of posts
     */
    public function getCountPosts(Account $account, $startDate, $endDate)
    {
        $start = microtime(true);
        Log::channel('querylog')->info('[Start] GetCountPosts():              id ' .$account->id);

        $countPosts = Media::query()
            ->select('posted_at', 'media_type')
            ->whereIn('id', function (Builder $query) use ($account) {
                return $query->select('instagram_media_id')
                    ->from('instagram_account_medias')
                    ->where('instagram_account_id', $account->id);
            })
            ->where([
                ['posted_at', '>=', $startDate],
                ['posted_at', '<', $endDate->copy()->addDays(1)],
            ])
            ->orderBy('posted_at', 'asc')
            ->get();

        for($i = 0; $i < count($countPosts); $i++) {
            $date = $countPosts[$i]->posted_at;
            $countPosts[$i]->posted_at = stristr($date, ' ', true);
        }

        $numberOfPosts = [];

        foreach ($countPosts as $key => $item) {
            if (!array_key_exists($item->posted_at, $numberOfPosts)) {
                $numberOfPosts[$item->posted_at] = [
                    'posted-at' => null,
                    'photo' => 0,
                    'video' => 0,
                    'carousel' => 0,
                ];
            }
            $numberOfPosts[$item->posted_at] = [
                'posted_at' => $item->posted_at,
                'photo' => $item->media_type === 1 ? $numberOfPosts[$item->posted_at]['photo'] += 1 : $numberOfPosts[$item->posted_at]['photo'],
                'video' => $item->media_type === 2 ? $numberOfPosts[$item->posted_at]['video'] += 1 : $numberOfPosts[$item->posted_at]['video'],
                'carousel' => $item->media_type === 8 ? $numberOfPosts[$item->posted_at]['carousel'] += 1 : $numberOfPosts[$item->posted_at]['carousel'],
            ];
        }
        Log::channel('querylog')->info('[End]   GetCountPosts():              id ' . $account->id . '     [ ' .round(microtime(true) - $start, 4).' s' . ' ]'  . "\n");
        return array_values($numberOfPosts);
    }

    /**
    Graph Most Engaging Post Types
     */
    public function getMostEngagingPostTypes(Account $account, $endDate)
    {
        $start = microtime(true);
        $startTime = microtime(true);
        Log::channel('querylog')->info('[Start] GetMostEngagingPostTypes():   id ' .$account->id);

        $result['photos'] = $account->photos()
            ->where([
                ['instagram_account_medias.created_at', '>=', $endDate],
                ['instagram_account_medias.created_at', '<', $endDate->copy()->addDays(1)],
            ])
            ->sum(DB::raw('(like_count + comment_count)::int'));

        Log::channel('querylog')->info("        Photos:     " . '[ ' . (microtime(true) - $start) . ' s ]');
        $start = microtime(true);

        $result['videos'] = $account->videos()
            ->where([
                ['instagram_account_medias.created_at', '>=', $endDate],
                ['instagram_account_medias.created_at', '<', $endDate->copy()->addDays(1)],
            ])
            ->sum(DB::raw('(like_count + comment_count + view_count)::int'));

        Log::channel('querylog')->info("        Videos:     " . '[ ' . (microtime(true) - $start) . ' s ]');
        $start = microtime(true);

        $result['carousels'] = $account->carousels()
            ->where([
                ['instagram_account_medias.created_at', '>=', $endDate],
                ['instagram_account_medias.created_at', '<', $endDate->copy()->addDays(1)],
            ])
            ->sum(DB::raw('(like_count + comment_count)::int'));

        Log::channel('querylog')->info("        Carousels:  " . '[ ' . (microtime(true) - $start) . ' s ]');
        Log::channel('querylog')->info('[End]   GetMostEngagingPostTypes():   id ' . $account->id . '     [ ' .round(microtime(true) - $startTime, 4).' s' . ' ]' . "\n");
        return  $result;
    }

    /**
    Graph Post Types
     */
    public function getPostTypes(Account $account, $endDate)
    {
        Log::channel('querylog')->info('[Start] GetPostTypes():               id ' .$account->id);
        $startTime = microtime(true);
        $start = microtime(true);

        Log::channel('querylog')->info("Photos:");

        $result['photos']['count'] = $account->photos()->where([
            ['instagram_account_medias.created_at', '>=', $endDate],
            ['instagram_account_medias.created_at', '<', $endDate->copy()->addDays(1)],
            ])->count('*');

        Log::channel('querylog')->info("        Count:     " . '[ ' . (microtime(true) - $start) . ' s ]');
        $start = microtime(true);

        $result['photos']['likes'] = $account->photos()->where([
            ['instagram_account_medias.created_at', '>=', $endDate],
            ['instagram_account_medias.created_at', '<', $endDate->copy()->addDays(1)],
            ])->sum(DB::raw('like_count::int'));

        Log::channel('querylog')->info('        Likes:     ' . '[ ' . (microtime(true) - $start) . ' s ]');
        $start = microtime(true);

        $result['photos']['comments'] = $account->photos()->where([
            ['instagram_account_medias.created_at', '>=', $endDate],
            ['instagram_account_medias.created_at', '<', $endDate->copy()->addDays(1)],
            ])->sum(DB::raw('comment_count::int'));

        Log::channel('querylog')->info('        Comments:  ' . '[ ' . (microtime(true) - $start) . ' s ]');
        $start = microtime(true);

        Log::channel('querylog')->info("Videos:");
        $result['videos']['count'] = $account->videos()->where([
            ['instagram_account_medias.created_at', '>=', $endDate],
            ['instagram_account_medias.created_at', '<', $endDate->copy()->addDays(1)],
        ])->count('*');

        Log::channel('querylog')->info("        Count:     " . '[ ' . (microtime(true) - $start) . ' s ]');
        $start = microtime(true);

        $result['videos']['likes'] = $account->videos()->where([
            ['instagram_account_medias.created_at', '>=', $endDate],
            ['instagram_account_medias.created_at', '<', $endDate->copy()->addDays(1)],
        ])->sum(DB::raw('like_count::int'));

        Log::channel('querylog')->info('        Likes:     ' . '[ ' . (microtime(true) - $start) . ' s ]');
        $start = microtime(true);

        $result['videos']['comments'] = $account->videos()->where([
            ['instagram_account_medias.created_at', '>=', $endDate],
            ['instagram_account_medias.created_at', '<',  $endDate->copy()->addDays(1)],
        ])->sum(DB::raw('comment_count::int'));

        Log::channel('querylog')->info('        Comments:  ' . '[ ' . (microtime(true) - $start) . ' s ]');
        $start = microtime(true);

        $result['videos']['views'] = $account->videos()->where([
            ['instagram_account_medias.created_at', '>=', $endDate],
            ['instagram_account_medias.created_at', '<', $endDate->copy()->addDays(1)],
        ])->sum(DB::raw('view_count::int'));

        Log::channel('querylog')->info('        Views:     ' . '[ ' . (microtime(true) - $start) . ' s ]');
        $start = microtime(true);

        Log::channel('querylog')->info("Carousels:");
        $result['carousels']['count'] = $account->carousels()
            ->where([
                ['instagram_account_medias.created_at', '>=', $endDate],
                ['instagram_account_medias.created_at', '<', $endDate->copy()->addDays(1)],
            ])->count('*');

        Log::channel('querylog')->info("        Count:     " . '[ ' . (microtime(true) - $start) . ' s ]');
        $start = microtime(true);

        $result['carousels']['likes'] = $account->carousels()->where([
            ['instagram_account_medias.created_at', '>=', $endDate],
            ['instagram_account_medias.created_at', '<', $endDate->copy()->addDays(1)],
        ])->sum(DB::raw('like_count::int'));

        Log::channel('querylog')->info('        Likes:     ' . '[ ' . (microtime(true) - $start) . ' s ]');
        $start = microtime(true);

        $result['carousels']['comments'] = $account->carousels()->where([
            ['instagram_account_medias.created_at', '>=', $endDate],
            ['instagram_account_medias.created_at', '<', $endDate->copy()->addDays(1)],
        ])->sum(DB::raw('comment_count::int'));

        Log::channel('querylog')->info('        Comments:  ' . '[ ' . (microtime(true) - $start) . ' s ]');

        Log::channel('querylog')->info('[End]   GetPostTypes():               id ' . $account->id . '     [ ' .round(microtime(true) - $startTime, 4).' s' . ' ]' . "\n");
        return $result;
    }
}