<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Dashboard\UserController;
use App\Http\Controllers\Dashboard\CategoryController;
use App\Http\Controllers\Dashboard\PlaceController;
// use App\Http\Controllers\Dashboard\ReviewController;
use App\Http\Controllers\Client\MainController;
use App\Http\Controllers\API\AuthController;

Route::post('/login', [AuthController::class, 'login']);

// Route::middleware([
//     'auth:sanctum',
//     'admin.access',
//     'audit'
// ])->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/refresh', [AuthController::class, 'refresh']);

    Route::group(['prefix' => 'dashboard'], function () {

        /***************************************** Users *******************************************/

            Route::GET('user', [UserController::class, 'index']);
            Route::GET('user/{user}', [UserController::class, 'show']);
            Route::POST('user', [UserController::class, 'store']);
            Route::POST('user/{user}', [UserController::class, 'update']);
            Route::DELETE('user/{user}', [UserController::class, 'destroy']);

        /***************************************** Category *******************************************/

            Route::GET('category', [CategoryController::class, 'index']);
            Route::GET('category/{category}', [CategoryController::class, 'show']);
            Route::POST('category', [CategoryController::class, 'store']);
            Route::POST('category/{category}', [CategoryController::class, 'update']);
            Route::DELETE('category/{category}', [CategoryController::class, 'destroy']);

        /***************************************** Places *******************************************/

            Route::GET('place', [PlaceController::class, 'index']);
            Route::GET('place/{place}', [PlaceController::class, 'show']);
            Route::POST('place', [PlaceController::class, 'store']);
            Route::POST('place/{place}', [PlaceController::class, 'update']);
            Route::DELETE('place/{place}', [PlaceController::class, 'destroy']);

        /***************************************** Place Review *******************************************/

            // Route::GET('place-review', [ReviewController::class, 'index']);
            // Route::GET('place-review/{review}', [ReviewController::class, 'show']);
            // Route::DELETE('place-review/{review}', [ReviewController::class, 'destroy']);


    });


// });



Route::group(['prefix' => 'client'], function () {

    /***************************************** Index *******************************************/

        Route::GET('index', [MainController::class, 'index']);

        Route::GET('places/{category}', [MainController::class, 'places']);

         // Define a route to Get Orders List Orders
        Route::POST('review', [MainController::class, 'review']);

         // Define a route to Get Orders List Orders
        Route::POST('feedback', [MainController::class, 'feedback']);

});



