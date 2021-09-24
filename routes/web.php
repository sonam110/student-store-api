<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/payout', 'App\Http\Controllers\API\FrontController@payout');
Route::get('/check-send-mail', 'App\Http\Controllers\API\FrontController@checkSendMail');

Route::get('/get-all-files', 'App\Http\Controllers\API\FrontController@getAllFiles');

Route::get('/add-thumb-filename', 'App\Http\Controllers\API\FrontController@addThumbFileName');

Route::get('/login', [App\Http\Controllers\Admin\MasterController::class,'login'])->name('login');
