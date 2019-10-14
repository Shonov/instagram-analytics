<?php
/**
 * Created by PhpStorm.
 * User: savanchuk
 * Date: 19.09.2018
 * Time: 10:49
 */

namespace App\Jobs;


interface InstagramJobInterface
{
    public function init();

    public function run();

    public function runTask();

    public function catchError();
}