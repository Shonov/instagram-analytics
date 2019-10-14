<?php
/**
 * Created by PhpStorm.
 * User: nikita
 * Date: 04.06.18
 * Time: 13:11
 */

namespace Grabber\StatisticBundle\Models;


use Grabber\InstagramAccountsBundle\Models\Account;
use Illuminate\Database\Eloquent\Model;


class TopFollower extends Model
{
    protected $dateFormat = 'Y-m-d H:i:sO';

    protected $table = 'top_followers';

    protected $fillable = [
        'id',
        'statistic_id',
        'follower_count',
        'instagram_profile_id',
        'created_at',
        'updated_at',
    ];


    public function follower()
    {
        return $this->belongsTo(Follower::class, 'instagram_profile_id');
    }
}
