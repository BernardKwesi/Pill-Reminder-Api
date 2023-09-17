<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DosageController;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post("register", [AuthController::class,'register']);
Route::post("login", [AuthController::class,'login']);

Route::prefix("dosages")->middleware('auth:sanctum')->group(function (){
    Route::post("", [DosageController::class,'store']);
    Route::get("", [DosageController::class,'index']);
    Route::post("/update", [DosageController::class,'update']);
    Route::post("/delete", [DosageController::class,'destroy']);
    Route::post("/mark", [DosageController::class,'markDosage']);
});

Route::post("users/delete", [AuthController::class, "destroy"])->middleware('auth:sanctum');
Route::post("logout", [AuthController::class, "logout"])->middleware('auth:sanctum');