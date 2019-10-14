<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     * @throws Exception
     */
    public function run()
    {
        //throw new Exception('Not implemented');

        if (\App::environment() === 'local') {
            $this->call(DevSeeder::class);
        }

        $this->call(ProxiesSeeder::class);
        $this->call(SystemAccountsSeeder::class);

        // $this->call(UsersTableSeeder::class);
    }
}
