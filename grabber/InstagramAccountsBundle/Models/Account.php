<?php
/**
 * Created by PhpStorm.
 * User: Nikita
 * Date: 05.05.2018
 * Time: 11:26
 */

namespace Grabber\InstagramAccountsBundle\Models;


use Carbon\Carbon;
use Grabber\StatisticBundle\Models\Follower;
use Grabber\StatisticBundle\Models\Statistic;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Class Account
 *
 * @package Grabber\InstagramAccountsBundle\Models
 * @mixin \Eloquent
 * @property int $id
 * @property string $login
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string|null $pic_url
 * @property-read \Illuminate\Database\Eloquent\Collection|\Grabber\InstagramAccountsBundle\Models\Media[] $carousels
 * @property-read \Illuminate\Database\Eloquent\Collection|\Grabber\StatisticBundle\Models\Follower[] $followers
 * @property-read \Grabber\StatisticBundle\Models\Statistic $lastStatistic
 * @property-read \Illuminate\Database\Eloquent\Collection|\Grabber\InstagramAccountsBundle\Models\Media[] $photos
 * @property-read \Illuminate\Database\Eloquent\Collection|\Grabber\StatisticBundle\Models\Statistic[] $statistics
 * @property-read \Illuminate\Database\Eloquent\Collection|\Grabber\InstagramAccountsBundle\Models\Media[] $videos
 * @method static \Illuminate\Database\Eloquent\Builder|\Grabber\InstagramAccountsBundle\Models\Account whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Grabber\InstagramAccountsBundle\Models\Account whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Grabber\InstagramAccountsBundle\Models\Account whereLogin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Grabber\InstagramAccountsBundle\Models\Account wherePicUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Grabber\InstagramAccountsBundle\Models\Account whereUpdatedAt($value)
 * @property-read \Illuminate\Database\Eloquent\Collection|\Grabber\InstagramAccountsBundle\Models\Media[] $medias
 */
class Account extends Model
{
    protected $dateFormat = 'Y-m-d H:i:sO';
    protected $table = 'instagram_accounts';

    protected $fillable = [
        'id',
        'login',
        'pic_url',
        'is_private'
    ];

    protected $hidden = [
        'pivot'
    ];


    public function statistics()
    {
        return $this->hasMany(Statistic::class, 'instagram_account_id');
    }

    public function lastStatistic()
    {
        return $this->hasOne(Statistic::class, 'instagram_account_id')->latest();
    }

    public function actualFollowers()
    {
        return $this->followers()->wherePivot('created_at', '>=', Carbon::today());
    }

    public function followers()
    {
        return $this->belongsToMany(Follower::class, 'instagram_account_followers', 'instagram_account_id', 'instagram_profile_id')->withTimestamps();
    }

    public function medias()
    {
        return $this->belongsToMany(
            Media::class,
            'instagram_account_medias',
            'instagram_account_id',
            'instagram_media_id'
        )->using(AccountMediaPivot::class);
    }

    public function photos()
    {
        return $this->medias()->withPivot('like_count', 'comment_count')->where('media_type', '=', 1);
    }

    public function videos()
    {
        return $this->medias()->withPivot('comment_count', 'view_count', 'created_at')->where('media_type', '=', 2);
    }

    public function carousels()
    {
        return $this->medias()->where('media_type', '=', 8);
    }
}
