<?php
/**
 * Created by PhpStorm.
 * User: nikita
 * Date: 24.07.18
 * Time: 20:56
 */

namespace Grabber\InstagramAccountsBundle\Models;


use Illuminate\Database\Eloquent\Model;

/**
 * Grabber\InstagramAccountsBundle\Models\UserInstagramAccount
 *
 * @property int $id
 * @property int $user_id
 * @property int $instagram_account_id
 * @property string $type
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\Grabber\InstagramAccountsBundle\Models\UserInstagramAccount whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Grabber\InstagramAccountsBundle\Models\UserInstagramAccount whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Grabber\InstagramAccountsBundle\Models\UserInstagramAccount whereInstagramAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Grabber\InstagramAccountsBundle\Models\UserInstagramAccount whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Grabber\InstagramAccountsBundle\Models\UserInstagramAccount whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Grabber\InstagramAccountsBundle\Models\UserInstagramAccount whereUserId($value)
 * @mixin \Eloquent
 */
class UserInstagramAccount extends Model
{
    protected $table = 'users_instagram_accounts';

    protected $fillable = [
        'user_id',
        'instagram_account_id',
        'type',
    ];
}
