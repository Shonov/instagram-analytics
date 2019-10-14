<?php
/**
 * Created by PhpStorm.
 * User: Nikita
 * Date: 07.05.2018
 * Time: 21:00
 */

namespace Grabber\StatisticBundle\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class BusinessStatistic
 *
 * @package Grabber\StatisticBundle\Models
 * @property int $id
 * @property int|null $average_engagement_count
 * @property int|null $followers_count
 * @property int|null $followers_delta_from_last_week
 * @property int|null $last_week_call
 * @property int|null $last_week_email
 * @property int|null $last_week_get_direction
 * @property int|null $last_week_impressions
 * @property int|null $last_week_profile_visits
 * @property int|null $last_week_reach
 * @property int|null $last_week_text
 * @property int|null $last_week_website_visits
 * @property int|null $posts_delta_from_last_week
 * @property int|null $week_over_week_call
 * @property int|null $week_over_week_email
 * @property int|null $week_over_week_get_direction
 * @property int|null $week_over_week_impressions
 * @property int|null $week_over_week_profile_visits
 * @property int|null $week_over_week_reach
 * @property int|null $week_over_week_text
 * @property int|null $week_over_week_website_visits
 * @property int|null $statistic_id
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\Grabber\StatisticBundle\Models\BusinessStatistic whereAverageEngagementCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Grabber\StatisticBundle\Models\BusinessStatistic whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Grabber\StatisticBundle\Models\BusinessStatistic whereFollowersCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Grabber\StatisticBundle\Models\BusinessStatistic whereFollowersDeltaFromLastWeek($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Grabber\StatisticBundle\Models\BusinessStatistic whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Grabber\StatisticBundle\Models\BusinessStatistic whereLastWeekCall($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Grabber\StatisticBundle\Models\BusinessStatistic whereLastWeekEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Grabber\StatisticBundle\Models\BusinessStatistic whereLastWeekGetDirection($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Grabber\StatisticBundle\Models\BusinessStatistic whereLastWeekImpressions($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Grabber\StatisticBundle\Models\BusinessStatistic whereLastWeekProfileVisits($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Grabber\StatisticBundle\Models\BusinessStatistic whereLastWeekReach($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Grabber\StatisticBundle\Models\BusinessStatistic whereLastWeekText($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Grabber\StatisticBundle\Models\BusinessStatistic whereLastWeekWebsiteVisits($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Grabber\StatisticBundle\Models\BusinessStatistic wherePostsDeltaFromLastWeek($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Grabber\StatisticBundle\Models\BusinessStatistic whereStatisticId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Grabber\StatisticBundle\Models\BusinessStatistic whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Grabber\StatisticBundle\Models\BusinessStatistic whereWeekOverWeekCall($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Grabber\StatisticBundle\Models\BusinessStatistic whereWeekOverWeekEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Grabber\StatisticBundle\Models\BusinessStatistic whereWeekOverWeekGetDirection($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Grabber\StatisticBundle\Models\BusinessStatistic whereWeekOverWeekImpressions($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Grabber\StatisticBundle\Models\BusinessStatistic whereWeekOverWeekProfileVisits($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Grabber\StatisticBundle\Models\BusinessStatistic whereWeekOverWeekReach($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Grabber\StatisticBundle\Models\BusinessStatistic whereWeekOverWeekText($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Grabber\StatisticBundle\Models\BusinessStatistic whereWeekOverWeekWebsiteVisits($value)
 * @mixin \Eloquent
 */
class BusinessStatistic extends Model
{
    protected $dateFormat = 'Y-m-d H:i:sO';

    protected $fillable = [
        'average_engagement_count',
        'followers_count',
        'followers_delta_from_last_week',
        'last_week_call',
        'last_week_email',
        'last_week_get_direction',
        'last_week_impressions',
        'last_week_profile_visits',
        'last_week_reach',
        'last_week_text',
        'last_week_website_visits',
        'posts_delta_from_last_week',
        'week_over_week_call',
        'week_over_week_email',
        'week_over_week_get_direction',
        'week_over_week_impressions',
        'week_over_week_profile_visits',
        'week_over_week_reach',
        'week_over_week_text',
        'week_over_week_website_visits',
    ];
}