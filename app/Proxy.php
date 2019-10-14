<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Proxy
 *
 * @property int $id
 * @property string $login
 * @property string $password
 * @property string $ip
 * @property int $http
 * @property int $socks
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Proxy whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Proxy whereHttp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Proxy whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Proxy whereIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Proxy whereLogin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Proxy wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Proxy whereSocks($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Proxy whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Proxy extends Model
{

}
