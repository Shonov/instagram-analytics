<?php

namespace Grabber\InstagramAccountsBundle\Models;


use Illuminate\Database\Eloquent\Relations\Pivot;

class AccountMediaPivot extends Pivot
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    protected $table = 'instagram_account_medias';

    protected $dateFormat = 'Y-m-d H:i:sO';

    protected $fillable = [
        'like_count',
        'comment_count',
        'view_count',
        'instagram_account_id',
        'instagram_media_id',
    ];
}
