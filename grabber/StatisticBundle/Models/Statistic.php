<?php
/**
 * Created by PhpStorm.
 * User: Nikita
 * Date: 05.05.2018
 * Time: 18:54
 */

namespace Grabber\StatisticBundle\Models;


use Illuminate\Database\Eloquent\Model;

/**
 * Class Statistic
 *
 * @package Grabber\StatisticBundle\Models
 * @property int $id
 * @property int $following_count
 * @property int $follower_count
 * @property int $media_count
 * @property int $usertags_count
 * @property int $like_count
 * @property int $comment_count
 * @property int $instagram_account_id
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \Grabber\StatisticBundle\Models\BusinessStatistic $businessStatistic
 * @method static \Illuminate\Database\Eloquent\Builder|\Grabber\StatisticBundle\Models\Statistic whereCommentCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Grabber\StatisticBundle\Models\Statistic whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Grabber\StatisticBundle\Models\Statistic whereFollowerCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Grabber\StatisticBundle\Models\Statistic whereFollowingCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Grabber\StatisticBundle\Models\Statistic whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Grabber\StatisticBundle\Models\Statistic whereInstagramAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Grabber\StatisticBundle\Models\Statistic whereLikeCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Grabber\StatisticBundle\Models\Statistic whereMediaCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Grabber\StatisticBundle\Models\Statistic whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Grabber\StatisticBundle\Models\Statistic whereUsertagsCount($value)
 * @mixin \Eloquent
 * @property int|null $views_count
 * @method static \Illuminate\Database\Eloquent\Builder|\Grabber\StatisticBundle\Models\Statistic whereViewsCount($value)
 */
class Statistic extends Model
{
    protected $table = 'statistics';

    protected $dateFormat = 'Y-m-d H:i:sO';

    protected $fillable = [
        'following_count',
        'follower_count',
        'media_count',
        'usertags_count',
        'like_count',
        'comment_count',
        'views_count',
        'posted_at',
        'instagram_account_id',
        'total_sub',
        'total_unsub',
        'is_subscribers_loaded',
        'is_posts_loaded',
        'can_view',
        'time_to_grab_subs',
    ];

    public function businessStatistic()
    {
        return $this->hasOne(BusinessStatistic::class, 'statistic_id');
    }
}