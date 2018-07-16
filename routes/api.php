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

Route::get('refresh-token', 'API\HomeAPIController@resetToken');

Route::post('send-otp', 'API\HomeAPIController@sendOTP');

Route::post('validate-otp', 'API\HomeAPIController@validateOTP');

Route::group(['middleware' => ['jwt.auth']], function () {
    Route::post('register-device', 'API\UserDeviceTokenController@registerDeviceToken');

    Route::get('users/get-detail', 'API\UserAPIController@getDetail');
    Route::post('users/update-info', 'API\UserAPIController@updateInfo');
    Route::post('users/add-referral-user', 'API\UserAPIController@addReferralUser');
    Route::delete('users/delete-avatar', 'API\UserAPIController@deleteAvatar');

    Route::get('rankings/total-amount', 'API\RankingAPIController@getTotalAmountRanks');
    Route::get('rankings/seven-days-amount', 'API\RankingAPIController@getSevenDaysAmountRanks');

    Route::get('transactions', 'API\TransactionAPIController@index');
    Route::post('transactions/payment-request', 'API\TransactionAPIController@paymentRequest');

    // For game
    Route::get('game/next-game', ['as' => 'game.next_game', 'uses' => 'API\GameAPIConntroller@nextGame']);
    Route::resource('game', 'API\GameAPIConntroller');
    Route::get('game/{id}/result', ['as' => 'game.result', 'uses' => 'API\GameAPIConntroller@result']);

    // For Question
    Route::resource('question', 'API\QuestionAPIController');
    Route::get('question/{id}/result', ['as' => 'question.result', 'uses' => 'API\QuestionAPIController@result']);

    // For Answer
    Route::post('answer/use-point', ['as' => 'answer.use_point', 'uses' => 'API\AnswerAPIController@use_point']);
    Route::resource('answer', 'API\AnswerAPIController');

    // For Message
    Route::get('messages', 'API\MessageAPIController@index');
    Route::post('messages', 'API\MessageAPIController@store');

    //For User Bank
    Route::get('list-bank', 'API\BankAPIController@banks');
    Route::get('bank/{id}/branches', 'API\BankAPIController@bankBranches');
    Route::get('get-bank', 'API\BankAPIController@getBank');
    Route::resource('bank', 'API\BankAPIController');
    
    //For comment
    Route::group([
        'namespace' => 'API'
    ], function () {
        Route::get('comment', 'CommentAPIController@getComment');
        Route::post('comment', 'CommentAPIController@changeComment');
    });
});
