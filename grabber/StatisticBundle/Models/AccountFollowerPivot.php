<?php
/**
 * Created by PhpStorm.
 * User: nikita
 * Date: 30.07.18
 * Time: 14:33
 */

namespace Grabber\StatisticBundle\Models;


use Illuminate\Database\Eloquent\Relations\Pivot;

class AccountFollowerPivot extends Pivot
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    protected $table = 'instagram_account_followers';

    protected $dateFormat = 'Y-m-d H:i:sO';

}
