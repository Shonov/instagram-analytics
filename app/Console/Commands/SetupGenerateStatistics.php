<?php

namespace App\Console\Commands;

use App\Jobs\GenerateAccountData;
use Carbon\Carbon;
use Grabber\InstagramAccountsBundle\Models\Account;
use Grabber\StatisticBundle\Models\Statistic;
use Illuminate\Console\Command;

class SetupGenerateStatistics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statistic:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     * @throws \Throwable
     */
    public function handle()
    {
        $statistics = Statistic::query()
            ->where('created_at', '>=', Carbon::today())
            ->get();

        foreach ($statistics as $statistic) {
            $account = Account::find($statistic->instagram_account_id);
            dump($account, $statistic);

            (new GenerateAccountData($account, $statistic))->handle();
        }

        return true;
    }
}
