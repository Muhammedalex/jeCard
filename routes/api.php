<?php

use App\Http\Controllers\CardController;
use App\Http\Controllers\LinksController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use League\CommonMark\Extension\CommonMark\Node\Inline\Link;

Route::middleware(['auth:sanctum'])->group(function(){
    Route::apiResource('cards', CardController::class)->except('update' , 'show');
    Route::apiResource('card/links', LinksController::class);
    Route::post('cards/{card}',[CardController::class , 'update']);
    Route::get('users',[UserController::class,'index'])->middleware('admin');
    Route::post('users',[UserController::class , 'store'])->middleware('admin');
    Route::put('users/{user}',[UserController::class , 'update']);
    Route::delete('users/{user}',[UserController::class , 'destroy'])->middleware('admin');

});
Route::get('cards/{slug}',[CardController::class , 'show']);


Route::middleware('auth:sanctum')->get('/user/{user?}', [UserController::class, 'getUser']);


require __DIR__.'/auth.php';
