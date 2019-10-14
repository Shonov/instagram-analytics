<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['namespace' =>'App\Http\Controllers'], function () {
    Route::post('auth/register', 'AuthController@register');
    //Route::group(['middleware' => 'api'], function () {

    //});
    Route::post('auth/login', 'AuthController@login')->name('login');

    Route::group(['middleware' => 'auth:api'], function () {
        Route::get('auth/user', 'AuthController@user');
        Route::get('auth/refresh', 'AuthController@refresh');
        Route::post('auth/logout', 'AuthController@logout');
    });
});

Route::group(['middleware' => 'auth:api'], function () {
    Route::group([], function () {
        Route::post('accounts', 'Grabber\AccountsBundle\Controllers\AccountsController@add');
        Route::get('accounts', 'Grabber\AccountsBundle\Controllers\AccountsController@all');
        Route::delete('accounts/{account}',  'Grabber\AccountsBundle\Controllers\AccountsController@delete');

        Route::group([], function () {
            Route::get('statistics/{account}/posts', 'Grabber\StatisticBundle\Controllers\PostsController@getTopPosts');
            Route::get('statistics/{account}/sorted-posts', 'Grabber\StatisticBundle\Controllers\PostsController@getSortedTopPosts');
            Route::get('statistics/{account}/number-posts', 'Grabber\StatisticBundle\Controllers\PostsController@getNumberPosts');
            Route::get('statistics/{account}/most-engaging-post-types', 'Grabber\StatisticBundle\Controllers\PostsController@getMostEngagingPostTypes');
            Route::get('statistics/{account}/post-types', 'Grabber\StatisticBundle\Controllers\PostsController@getPostTypes');

            Route::get('statistics/{account}/statistic', 'Grabber\StatisticBundle\Controllers\StatisticsController@getStatistic');
            Route::get('statistics/{account}/top-new-followers', 'Grabber\StatisticBundle\Controllers\StatisticsController@getTopNewFollowers');
            Route::get('statistics/{account}/top-lost-followers', 'Grabber\StatisticBundle\Controllers\StatisticsController@getTopLostFollowers');
            Route::get('statistics/{account}/gender-followers', 'Grabber\StatisticBundle\Controllers\StatisticsController@getGenderFollowers');
            Route::get('statistics/{account}/private-and-open-accounts', 'Grabber\StatisticBundle\Controllers\StatisticsController@getPrivateAndOpenAccounts');
            Route::get('statistics/{account}/business-and-usual-accounts', 'Grabber\StatisticBundle\Controllers\StatisticsController@getBusinessAndUsualAccounts');
            Route::get('statistics/{account}/followers-by-our-followers', 'Grabber\StatisticBundle\Controllers\StatisticsController@getFollowersByOurFollowers');
            Route::get('statistics/{account}/followers-by-our-following', 'Grabber\StatisticBundle\Controllers\StatisticsController@getFollowersByOurFollowing');
            Route::get('statistics/{account}/followers-and-following', 'Grabber\StatisticBundle\Controllers\StatisticsController@getFollowersAndFollowing');
            Route::get('statistics/{account}/bots', 'Grabber\StatisticBundle\Controllers\StatisticsController@getCountBots');
            Route::get('statistics/{account}/reach', 'Grabber\StatisticBundle\Controllers\StatisticsController@getReachUsers');
            Route::get('statistics/{account}/engagement', 'Grabber\StatisticBundle\Controllers\StatisticsController@getEngagement');
        });
    });
});
