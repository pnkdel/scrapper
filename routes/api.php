<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\ScrapesetupController;

Route::post('register', [AuthController::class, 'register']);
Route::post('authorization', [AuthController::class, 'authorization']);
Route::get('login/{code}', [AuthController::class, 'login']);
Route::get('/getToken', [AuthController::class, 'generateDumyToken']);
Route::get('/resetRecordID',  [ScrapesetupController::class, 'resetRecordID']);
Route::get('/jobs/{id}',  [ScrapesetupController::class, 'getJobDetails']);
Route::delete('/jobs/{id}',  [ScrapesetupController::class, 'deleteJob']);
Route::get('demouser', [AuthController::class, 'demouser']);


Route::get('/processJob',  [ScrapesetupController::class, 'processURLbyKeyID_redis']);
Route::get('/deleteJobMaster/{id}',  [ScrapesetupController::class, 'deleteJobMaster']);

Route::group(['middleware' => ['CustomAuth']], function(){
    Route::post('/jobs',  [ScrapesetupController::class, 'index']);
});



/*
Route::group(['middleware' => 'auth:sanctum'], function(){
    Route::get('/testApi', [TestController::class, 'testApi']);
    Route::post('/jobs',  "ScrapesetupController@index");
});*/

/*
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
*/