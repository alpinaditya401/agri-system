<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BpsDomainController;
use App\Http\Controllers\Api\HarvestController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/bps/domains/provinces', [BpsDomainController::class, 'provinces']);
Route::get('/bps/domains/regencies', [BpsDomainController::class, 'regencies']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::apiResource('harvests', HarvestController::class);
});
