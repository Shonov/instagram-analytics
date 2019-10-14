<?php

use Illuminate\Database\Seeder;

/**
 * Created by PhpStorm.
 * User: nikita
 * Date: 28.07.18
 * Time: 9:47
 */
class ProxiesSeeder extends Seeder
{
    private $table = 'proxies';

    /**
     * Run the database seeds.
     *
     * @return void
     * @throws Exception
     */
    public function run()
    {
        $proxies = [];
        $content = file(storage_path('proxyAccounts').'/proxies.txt');

        if ($content[count($content) - 1] == "\r\n") array_splice($content, count($content) - 1, 1);

        for($i=0; $i < count($content) - 1; $i+=5) {
            $proxies[] = [
                'ip' => trim(preg_replace('/\s+/', ' ', explode(' ', $content[$i])[1])),
                'http' => trim(preg_replace('/\s+/', ' ', explode(' ', $content[$i + 1])[1])),
                'login' => trim(preg_replace('/\s+/', ' ', explode(' ', $content[$i + 2])[1])),
                'password' => trim(preg_replace('/\s+/', ' ', explode(' ', $content[$i + 3])[1])),
            ];
        }

        DB::table('system_accounts')->delete();
        DB::table($this->table)->delete();

        foreach ($proxies as $key => $proxy) {
            $proxy['id'] = $key + 1;
            $proxy['socks'] = 0;
            DB::table($this->table)->insert($proxy);
        }
    }
}
