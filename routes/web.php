<?php

use App\Http\Controllers\FirebaseController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/firebase-test', [FirebaseController::class, 'test']);


Route::get('notification', [FirebaseController::class, 'notificationPage'])->name('notification');
Route::post('save-token', [FirebaseController::class, 'saveToken'])->name('save.token');


Route::get('/send-notification', [FirebaseController::class, 'showSendForm'])->name('notification.form');
Route::post('/send-notification', [FirebaseController::class, 'sendNotification'])->name('send.notification');
Route::get('/list-tokens', [FirebaseController::class, 'listUserTokens'])->name('list.tokens');
