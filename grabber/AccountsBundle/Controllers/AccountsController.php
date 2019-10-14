<?php
/**
 * Created by PhpStorm.
 * User: nikita
 * Date: 20.05.18
 * Time: 23:11
 */

namespace Grabber\AccountsBundle\Controllers;


use App\Http\Controllers\Controller;
use Grabber\InstagramAccountsBundle\Models\Account;
use Grabber\InstagramAccountsBundle\Models\AccountMediaPivot;
use Grabber\InstagramAccountsBundle\Models\Media;
use Grabber\InstagramAccountsBundle\Models\UserInstagramAccount;
use Grabber\StatisticBundle\Models\AccountFollowerPivot;
use Grabber\StatisticBundle\Models\Follower;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use InstagramScraper\Exception\InstagramException;
use InstagramScraper\Exception\InstagramNotFoundException;
use InstagramScraper\Instagram as InstagramScraper;


class AccountsController extends Controller
{
    public function all()
    {
        // ToDo: to service
        $user = \Auth::user();

        return response()->json([
            'self_accounts' => \Auth::user()->selfAccounts()->orderBy('created_at')->get()->map(function ($account) {
                return $account->load('lastStatistic');
            }),
            'competitors' => \Auth::user()->competitors()->orderBy('created_at')->get()->map(function ($account) {
                return $account->load('lastStatistic');
            }),
        ]);
    }

    // ToDo: to Requests
    public function add(Request $request)
    {
        $user = \Auth::user();

        if ($user->max_accounts !== -1 && ($user->selfAccounts()->count() + $user->competitors()->count()) === $user->max_accounts) {
            return [
                'status' => false,
            ];
        }

        $data = $request->all();
        if (substr_count($data['login'], 'instagram.com')) {
            $rows = explode('/', $data['login']);
            if ($rows[count($rows) - 1] != "") {
                $data['login'] = $rows[count($rows) - 1];
            } else {
                $data['login'] = $rows[count($rows) - 2];
            }
        }

        $scraper = new InstagramScraper();

        try {
            $instagramAccount = $scraper->getAccount($data['login']);
        } catch (InstagramNotFoundException $e) {
            return [
                'status' => false,
            ];
        }

        $account = Account::find($instagramAccount->getId());
        if ($account === null) {
            $account = new Account([
                'id' => $instagramAccount->getId(),
                'login' => $instagramAccount->getUsername(),
                'pic_url' => $instagramAccount->getProfilePicUrl(),
                'is_private' => $instagramAccount->isPrivate(),
            ]);

            $account->save();
        }


        $newRelation = new UserInstagramAccount([
            'user_id' => $user->id,
            'instagram_account_id' => $account->id,
            'type' => $data['type'],
        ]);

        $newRelation->save();

        return [
            'status' => true,
        ];
    }

    public function delete(Account $account)
    {
        if (\DB::table('users_instagram_accounts')
                ->where('instagram_account_id', '=', $account->id)
                ->count() > 1) {
            \DB::table('users_instagram_accounts')
                ->where([
                    ['instagram_account_id', '=', $account->id],
                    ['user_id', '=', \Auth::user()->id],
                ])
                ->delete();
        } else {
            AccountFollowerPivot::query()
                ->where('instagram_account_id', '=', $account->id)
                ->delete();

            Follower::query()
                ->where('id', '=', $account->id)
                ->whereNotIn('id', function (Builder $query) {
                    return $query
                        ->select('instagram_profile_id')
                        ->from('instagram_account_followers');
                })
                ->delete();

            AccountMediaPivot::query()
                ->where('instagram_account_id', '=', $account->id)
                ->delete();

            Media::query()
                ->where('id', '=', $account->id)
                ->whereNotIn('id', function (Builder $query) {
                    return $query
                        ->select('instagram_media_id')
                        ->from('instagram_account_medias');
                })
                ->delete();


            \DB::table('users_instagram_accounts')
                ->where([
                    ['instagram_account_id', '=', $account->id],
                    ['user_id', '=', \Auth::user()->id],
                ])
                ->delete();

            $account->statistics()->delete();

            $account->delete();
        }
        return [
            'status' => true,
        ];
    }
}
