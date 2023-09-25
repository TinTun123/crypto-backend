<?php

use App\Http\Controllers\ActivitiesLogController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\user\UserController;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use PHPUnit\Event\Tracer\Tracer;

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

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {

    return $request->user();

});



Route::middleware(['auth:sanctum'])->group(function () {

    Route::post('/updateProfile/{user}', [UserController::class, 'update']);
    Route::get('/getUser/{user}', [UserController::class, 'getUser']);
    Route::patch('/updatePwd/{user}', [UserController::class, 'updatePwd']);
    Route::get('/fetchUsers', [UserController::class, 'fetchUsers'])->middleware('isAdmin');
    Route::get('/download', [UserController::class, 'download'])->middleware('isAdmin');
    Route::post('/assignKey/{user}', [UserController::class, 'assignPrivateKey'])->middleware('isAdmin');
    Route::get('/getKey/{user}', [UserController::class, 'getKey']);
    Route::post('/changeLock/{user}', [UserController::class, 'toogleLock']);
    Route::post('/uploadKey/{user}', [UserController::class, 'uploadPriKey']);
    Route::get('/fetchLogs/{user}', [ActivitiesLogController::class, 'fetchLogs'])->middleware('isAdmin');
    Route::get('/fetchWallet', [TransactionController::class, 'fetchWallet'])->middleware('isAdmin');
    Route::post('/updateWallet/{wallet}', [TransactionController::class, 'updateWallet'])->middleware('isAdmin');
    Route::post('/topUpCoin/{wallet}/{user}', [TransactionController::class, 'topUpCoin'])->middleware('isAdmin');
    Route::post('/deleteUser/{user}', [UserController::class, 'deleteUser'])->middleware('isAdmin');
    Route::get('fetchBalance/{user}', [TransactionController::class, 'fetchBalance']);
    Route::post('/sentCoin', [TransactionController::class, 'sentCoin']);
    Route::post('/adminSentCoin', [TransactionController::class, 'AdminsentCoin'])->middleware('isAdmin');
    Route::get('/fetchTran/{user}', [TransactionController::class, 'fetchTran']);
    Route::get('/adminFetchTran', [TransactionController::class, 'adminFetchTran'])->middleware('isAdmin');
    Route::post('/denyTran/{transaction}', [TransactionController::class, 'rollbackTran'])->middleware('isAdmin');
    Route::get('/fetchCoinTran/{wallet}/{user}', [TransactionController::class, 'fetchCoinTran']);
    Route::get('/adminFetchUserTran/{user}', [TransactionController::class, 'adminFetchUserTran'])->middleware('isAdmin');
    Route::get('fetchWallet/{user}', [TransactionController::class, 'fetchWallet']);
    Route::post('/initSwap/{user}', [TransactionController::class, 'initSwap']);
    Route::get('fetchSwapTran/{user}', [TransactionController::class, 'fetchSwapTran']);
    Route::get('/adminFetchSwapTran', [TransactionController::class, 'adminFetchSwapTran'])->middleware('isAdmin');
});

Route::get('/', function () {

    return ['Laravel' => app()->version()];

});

require __DIR__.'/auth.php';
