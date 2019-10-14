<?php
/**
 * Created by PhpStorm.
 * User: nikita
 * Date: 01.07.18
 * Time: 12:07
 */

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use InstagramScraper\Instagram as InstagramScraper;

class DevSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     * @throws \InstagramScraper\Exception\InstagramException
     * @throws \InstagramScraper\Exception\InstagramNotFoundException
     */
    public function run()
    {
        $faker = \Faker\Factory::create();

        $testAccount = [
            'email' => 'test@account.ae',
            'password' => '123456',
        ];

        $testAccountId = DB::table('users')->insertGetId([
            'name' => $faker->name,
            'email' => $testAccount['email'],
            'password' => bcrypt($testAccount['password']),
        ]);

        // faked passwords

        $instagramAccounts = [[
            'id' => '6522609916',
            'login' => 'rostov.public',
        ], [
            'id' => '5550474309',
            'login' => 'mur.mur.myu',
        ], [
            'id' => '6131245748',
            'login' => 'fifa2018.rnd',
        ], [
            'id' => '3242899369',
            'login' => 'digital3falcon',
        ],];

        $scraper = new InstagramScraper();

        foreach ($instagramAccounts as $account) {
            dump($account['login']);

            $accountImage = null;
            $instagramAccountId = $account['id'];
            DB::table('instagram_accounts')
                ->insertGetId([
                    'id' => $account['id'],
                    'login' => $account['login'],
                    'pic_url' => $accountImage,
                    'created_at' => Carbon::today()->subMonths(rand(0,7)),
                ]);

            DB::table('users_instagram_accounts')->insert([
                'user_id' => $testAccountId,
                'instagram_account_id' => $instagramAccountId,
                'type' => rand(0, 1) === 1 ? 'self_account' : 'competitor',
            ]);

            $list = [1,1,1,1,1,1,1,1,1,];
            dump('get media');

            foreach ($list as $item) {
                $day = Carbon::today();

                for($i = 0; $i <= (3 + rand(5, 10)); $i++) {
                    $id = rand(100000, 999999);
                    $type =  [1, 2, 8];
                    DB::table('instagram_medias')->insert([
                        'id' => $id,
                        'media_type' => $type[rand(0, 2)],
                        'filter_type' => 0,
                        'pic_url' => '',
                        'posted_at' => $day,
                    ]);

                    $created_at = $day;
                    for ($i = 0; $i <= rand(5, 10); $i++) {
                        DB::table('instagram_account_medias')->insert([
                            'like_count' => rand(0, 1000),
                            'comment_count' => rand(1000, 10000),
                            'view_count' => rand(1000, 10000),
                            'instagram_account_id' => $instagramAccountId,
                            'instagram_media_id' => $id,
                            'created_at' => $created_at,
                            'updated_at' => $created_at,
                        ]);
                        $created_at = $created_at->addDays(-1);
                    }
                }
                $day = $day->addDays(-1);
            }

            $countStatistics = rand(2, 30);

            $wiews = 50;
            for ($i = 0; $i < $countStatistics; $i++) {
                dump('create statistic: ' . ($i + 1));

                $followerCount = rand(10, 100);
                $day = Carbon::today()->addDays(-($countStatistics - $i - 1));

                $wiews = $wiews + rand(10, 200);

                $statisticId = DB::table('statistics')->insertGetId([
                    'following_count' => rand(0, 50),
                    'follower_count' => $followerCount,
                    'media_count' => ($i * 2) + rand(5, 10),
                    'usertags_count' => ($i * 2) + rand(0, 2),
                    'like_count' => ($i * 5) + rand(4, 10),
                    'comment_count' => ($i * 10) + rand(4, 10),
                    'instagram_account_id' => $instagramAccountId,
                    'created_at' => $day,
                    'views_count' => $wiews,
                    'total_sub' => ($i * 20) + rand(0, 30),
                    'total_unsub' => ($i * 10) + rand(0, 10),
                ]);

//                $businessStatisticsId = DB::table('business_statistics')->insertGetId([
//                    'average_engagement_count' => rand(0, 1000),
//                    'followers_count' => $followerCount,
//                    'followers_delta_from_last_week' => rand(0, 1000),
//                    'last_week_call' => rand(0, 1000),
//                    'last_week_email' => rand(0, 1000),
//                    'last_week_get_direction' => rand(0, 1000),
//                    'last_week_impressions' => rand(0, 1000),
//                    'last_week_profile_visits' => rand(0, 1000),
//                    'last_week_reach' => rand(0, 1000),
//                    'last_week_text' => rand(0, 1000),
//                    'last_week_website_visits' => rand(0, 1000),
//                    'posts_delta_from_last_week' => rand(0, 1000),
//                    'week_over_week_call' => rand(0, 1000),
//                    'week_over_week_email' => rand(0, 1000),
//                    'week_over_week_get_direction' => rand(0, 1000),
//                    'week_over_week_impressions' => rand(0, 1000),
//                    'week_over_week_profile_visits' => rand(0, 1000),
//                    'week_over_week_reach' => rand(0, 1000),
//                    'week_over_week_text' => rand(0, 1000),
//                    'week_over_week_website_visits' => rand(0, 1000),
//                    'statistic_id' => $statisticId,
//                    'created_at' => $day,
//                ]);

                $gender = [1, 0, null];

                dump('create followers: ' . $followerCount);
                for ($followerIndex = 0; $followerIndex < $followerCount; $followerIndex++) {
//                    $followerId = rand(1000000000, 9999999999);
                    $followerId = rand(10, 300);

                    if (DB::table('instagram_profiles')->find($followerId) === null) {
                        DB::table('instagram_profiles')->insert([
                            'id' => $followerId,
                            'full_name' => $faker->name,
                            'following_count' => rand(0, 10),
                            'follower_count' => rand(0, 5) + ($i * 5),
                            'media_count' => rand(0, 10),
                            'profile_pic_url' => '',
                            'is_private' => rand(0, 1),
                            'is_business' => rand(0, 1),
                            'gender' => $gender[rand(0, 2)],
                        ]);
                    }

                    DB::table('instagram_account_followers')->insert([
                        'instagram_profile_id' => $followerId,
                        'instagram_account_id' => $instagramAccountId,
                        'created_at' => $day,
                        'updated_at' => $day,
                    ]);
                }
            }
        }

        // $this->call(UsersTableSeeder::class);
    }
}
