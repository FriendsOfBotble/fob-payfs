<?php

use FriendsOfBotble\PayFS\Http\Middleware\PayFSProtector;
use Illuminate\Support\Facades\Route;

Route::post('payfs/webhook', [
    'uses' => 'FriendsOfBotble\PayFS\Http\Controllers\WebhookController@__invoke',
    'as' => 'payfs.webhook',
    'middleware' => [PayFSProtector::class],
]);

Route::post('payfs/transactions/check', [
    'uses' => 'FriendsOfBotble\PayFS\Http\Controllers\TransactionCheckerController@__invoke',
    'as' => 'payfs.transactions.check',
]);
