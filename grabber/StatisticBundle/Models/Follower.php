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

/**
 * Grabber\StatisticBundle\Models\Follower
 *
 * @property int $id
 * @property string $full_name
 * @property string $profile_pic_url
 * @property int $following_count
 * @property int $follower_count
 * @property int $media_count
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property bool|null $is_private
 * @property bool|null $is_business
 * @property-read \Grabber\InstagramAccountsBundle\Models\Account $account
 * @method static \Illuminate\Database\Eloquent\Builder|\Grabber\StatisticBundle\Models\Follower whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Grabber\StatisticBundle\Models\Follower whereFollowerCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Grabber\StatisticBundle\Models\Follower whereFollowingCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Grabber\StatisticBundle\Models\Follower whereFullName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Grabber\StatisticBundle\Models\Follower whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Grabber\StatisticBundle\Models\Follower whereIsBusiness($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Grabber\StatisticBundle\Models\Follower whereIsPrivate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Grabber\StatisticBundle\Models\Follower whereMediaCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Grabber\StatisticBundle\Models\Follower whereProfilePicUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Grabber\StatisticBundle\Models\Follower whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property int|null $gender
 * @method static \Illuminate\Database\Eloquent\Builder|\Grabber\StatisticBundle\Models\Follower whereGender($value)
 */
class Follower extends Model
{
    protected $dateFormat = 'Y-m-d H:i:sO';

    protected $table = 'instagram_profiles';

    protected $fillable = [
        'id',
        'full_name',
        'profile_pic_url',
        'media_count',
        'following_count',
        'follower_count',
        'is_private',
        'is_business',
        'gender',
    ];

    protected $hidden = [
        'pivot'
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function newPivot(Model $parent, array $attributes, $table, $exists, $using = null)
    {
        return new AccountFollowerPivot($attributes);
    }
}
