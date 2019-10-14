<?php
/**
 * Created by PhpStorm.
 * User: nikita
 * Date: 22.07.18
 * Time: 21:11
 */

namespace Grabber\InstagramAccountsBundle\Models;


use Illuminate\Database\Eloquent\Model;


/**
 * Grabber\InstagramAccountsBundle\Models\Media
 *
 * @property int $id
 * @property int $media_type
 * @property int $filter_type
 * @property string $pic_url
 * @property string|null $posted_at
 * @property-read \Grabber\InstagramAccountsBundle\Models\Account $media
 * @method static \Illuminate\Database\Eloquent\Builder|\Grabber\InstagramAccountsBundle\Models\Media whereFilterType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Grabber\InstagramAccountsBundle\Models\Media whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Grabber\InstagramAccountsBundle\Models\Media whereMediaType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Grabber\InstagramAccountsBundle\Models\Media wherePicUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Grabber\InstagramAccountsBundle\Models\Media wherePostedAt($value)
 * @mixin \Eloquent
 */
class Media extends Model
{
    protected $table = 'instagram_medias';
    protected $dateFormat = 'Y-m-d H:i:sO';

    protected $fillable = [
        'id',
        'media_type',
        'filter_type',
        'posted_at',
        'pic_url',
        'taken_at',
        'code',
    ];

    public function media()
    {
        return $this->belongsTo(Account::class);
    }

    public function newPivot(Model $parent, array $attributes, $table, $exists, $using = null)
    {
        return new AccountMediaPivot($attributes);
    }
}
