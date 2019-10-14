<?php

use Illuminate\Database\Seeder;
use InstagramAPI\Instagram;
use InstagramAPI\Signatures;

/**
 * Created by PhpStorm.
 * User: nikita
 * Date: 28.07.18
 * Time: 9:55
 */
class SystemAccountsSeeder extends Seeder
{

    private $table = 'system_accounts';

    /**
     * Run the database seeds.
     *
     * @return void
     * @throws Exception
     */
    public function run()
    {
        if (env('APP_ENV') === 'local') {
            return;
        }

        $accounts = [];
        $content = file(storage_path('proxyAccounts').'/accounts.txt');

        if ($content[count($content) - 1] == "\r\n") array_splice($content, count($content) - 1, 1);

        for($i=0; $i < count($content) - 1; $i+=3) {
            $accounts[] = [
                'login' => trim(preg_replace('/\s+/', ' ', explode(' ', $content[$i])[1])),
                'password' => trim(preg_replace('/\s+/', ' ', explode(' ', $content[$i + 1])[1])),
            ];
        }


        DB::table($this->table)->delete();

        foreach ($accounts as $key => $account) {

            dump('', '', $account);

            $ig = new Instagram(true, true, []);

            $account['proxy_id'] = $key + 1;

            $proxy = DB::table('proxies')->find($account['proxy_id']);

            $ig->setProxy(
                'http://' . $proxy->login . ':' .
                $proxy->password . '@' .
                $proxy->ip . ':' .
                $proxy->http
            );


            try {
                $loginResponse = $ig->login($account['login'], $account['password']);
            } catch (\InstagramAPI\Exception\ChallengeRequiredException $exception) {
                $error = $challenge = $exception->getResponse()->asArray();
                if ($error['message'] === 'challenge_required') {
                    $challenge = $error['challenge']['api_path'];
                    $step = 'select_verify_method';
                    while (true) {
                        if ($step === 'select_verify_method') {
                            $response = $this->sendChallenge($ig, $challenge, 'select_email')->asArray();
                        } else if ($step === 'verify_email') {
                            dump('[' . $account['login'] . '] wait code: ');
                            $code = trim(fgets(STDIN));
                            $response = $this->sendChallenge($ig, $challenge, 'send_code', $code)->asArray();
                        }
                        dump($response);
                        if (isset($response['logged_in_user'])) {
                            $ig->isMaybeLoggedIn = true;
                            $ig->account_id = $response['logged_in_user']['pk'];
                            $ig->settings->set('account_id', $ig->account_id);
                            $ig->settings->set('last_login', time());
                            $ig->settings->set('last_login', time());
                            $ig->session_id = Signatures::generateUUID(true);
                            $ig->settings->set('session_id', $ig->session_id);
                            $this->registerPushChannels($ig);
                            $ig->client->saveCookieJar();
                            dump('[' . $account['login'] . '] account success');
                            break;
                        } else if (isset($response['action']) && $response['action'] === 'close') {
                            $ig->isMaybeLoggedIn = true;
                            $ig->settings->set('account_id', 0);
                            $ig->settings->set('last_login', time());
                            $ig->settings->set('last_login', time());
                            $ig->session_id = Signatures::generateUUID(true);
                            $ig->settings->set('session_id', $ig->session_id);
                            $this->registerPushChannels($ig);
                            $ig->client->saveCookieJar();
                            dump('[' . $account['login'] . '] something went wrong');
                            break;
                        }
                        $step = $response['step_name'];

                        dump('next step ...');
                        fgets(STDIN);
                    }
                }
            } catch (\InstagramAPI\Exception\CheckpointRequiredException $exception) {
                try {
                    $error = $challenge = $exception->getResponse()->asArray();
                    $acceptUrl = 'https://i.instagram.com/terms/accept/';
                    if (isset($error['checkpoint_url']) && $error['checkpoint_url'] === $acceptUrl) {
                        $response = $this->sendTermsAccept($ig)->asArray();
                        dump($response);
                    }
                } catch (\InstagramAPI\Exception\NotFoundException $exception) {
                    dump($ig->people->getInfoByName('digital3falcon')->getUser()->asArray());
                }
            }

//            dump('Ready?');
//            fgets(STDIN);

            $account['id'] = $key + 1;
            $account['start_work_at'] = \Carbon\Carbon::now();
            DB::table($this->table)->insert($account);
        }

    }

    public function sendChallenge(Instagram $ig, $challenge_url, $type, $code = null)
    {
        $challenge_url = ltrim($challenge_url, '/');
        $request = $ig->request($challenge_url)
            ->setNeedsAuth(false)
            ->addPost('device_id', $ig->device_id)
            ->addPost('guid', $ig->uuid)
            ->addPost('_csrftoken', $ig->client->getToken());

        if ($type === 'select_email') {
            $request->addPost('choice', 1);
        } else if ($type === 'send_code') {
            $request->addPost('security_code', $code);
        }

        return $request->getResponse(new InstagramAPI\Response\UserInfoResponse());
    }

    public function registerPushChannels(Instagram $ig)
    {
        try {
            $lastFbnsToken = (int)$ig->settings->get('last_fbns_token');
        } catch (\Exception $e) {
            $lastFbnsToken = null;
        }
        if (!$lastFbnsToken || $lastFbnsToken < strtotime('-24 hours')) {
            try {
                $ig->settings->set('fbns_token', '');
            } catch (\Exception $e) {
                // Ignore storage errors.
            }

            return;
        }

        // Read our token from the storage.
        try {
            $fbnsToken = $ig->settings->get('fbns_token');
        } catch (\Exception $e) {
            $fbnsToken = null;
        }
        if ($fbnsToken === null) {
            return;
        }

        // Register our last token since we had a fresh (age <24 hours) one,
        // or clear our stored token if we fail to register it again.
        try {
            $ig->push->register('mqtt', $fbnsToken);
        } catch (\Exception $e) {
            try {
                $ig->settings->set('fbns_token', '');
            } catch (\Exception $e) {
                // Ignore storage errors.
            }
        }
    }

    public function sendTermsAccept(Instagram $ig)
    {
        $request = $ig->request('terms/accept/')
            ->setNeedsAuth(false)
            ->addPost('device_id', $ig->device_id)
            ->addPost('guid', $ig->uuid)
            ->addPost('_csrftoken', $ig->client->getToken());


        return $request->getResponse(new InstagramAPI\Response());
    }
}
