<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\SystemAccount
 *
 * @property int $id
 * @property string $login
 * @property string $password
 * @property int $proxy_id
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SystemAccount whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SystemAccount whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SystemAccount whereLogin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SystemAccount wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SystemAccount whereProxyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SystemAccount whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property-read \App\Proxy $proxy
 */
class SystemAccount extends Model
{
    protected $dateFormat = 'Y-m-d H:i:sO';

    protected $fillable = [
        'login',
        'password',
        'proxy_id',
        'start_work_at',
        'is_work',
        'is_blocked'
    ];

    public function proxy()
    {
        return $this->belongsTo(Proxy::class);
    }
}
