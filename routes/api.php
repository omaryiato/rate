<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\Client\MainController;
use App\Http\Controllers\Dashboard\CategoryController;
use App\Http\Controllers\Dashboard\PlaceController;
// use App\Http\Controllers\Dashboard\ReviewController;
use App\Http\Controllers\Dashboard\UserController;
use Illuminate\Support\Facades\Route;

// Both prefixes share the same routes, security, and rate-limit buckets.
$apiRoutes = static function (): void {
    Route::post('signup', [AuthController::class, 'signup'])->middleware('throttle:signup')->name('auth.signup');

    Route::post('login', [AuthController::class, 'login'])->middleware('throttle:login')->name('auth.login');

    Route::middleware([
        'auth:sanctum',
        'account.active',
    ])->group(function () {

        Route::post('logout', [AuthController::class, 'logout'])->name('post.logout');

        Route::get('me', [AuthController::class, 'me'])->name('get.me');
        // Compatibility alias: session inspection only, without renewing the token.
        Route::get('refresh', [AuthController::class, 'me'])->name('get.refresh');

        Route::group(['prefix' => 'dashboard', 'middleware' => 'admin.access'], function () {

            /***************************************** Users *******************************************/

            Route::GET('user', [UserController::class, 'index'])->name('get.user')->middleware('admin.access:users');
            Route::GET('user/{user}', [UserController::class, 'show'])->name('get.user.user')->middleware('admin.access:users');
            Route::POST('user', [UserController::class, 'store'])->name('post.user')->middleware('admin.access:users');
            Route::POST('user/{user}', [UserController::class, 'update'])->name('post.user.user')->middleware('admin.access:users');
            Route::DELETE('user/{user}', [UserController::class, 'destroy'])->name('delete.user.user')->middleware('admin.access:users');

            /***************************************** Category *******************************************/

            Route::GET('category', [CategoryController::class, 'index'])->name('get.category');
            Route::GET('category/{category}', [CategoryController::class, 'show'])->name('get.category.category');
            Route::POST('category', [CategoryController::class, 'store'])->name('post.category');
            Route::POST('category/{category}', [CategoryController::class, 'update'])->name('post.category.category');
            Route::DELETE('category/{category}', [CategoryController::class, 'destroy'])->name('delete.category.category');

            /***************************************** Places *******************************************/

            Route::GET('place', [PlaceController::class, 'index'])->name('get.place');
            Route::GET('place/{place}', [PlaceController::class, 'show'])->name('get.place.place');
            Route::POST('place', [PlaceController::class, 'store'])->name('post.place');
            Route::POST('place/{place}', [PlaceController::class, 'update'])->name('post.place.place');
            Route::DELETE('place/{place}', [PlaceController::class, 'destroy'])->name('delete.place.place');

            /***************************************** Place Review *******************************************/

            // Route::GET('place-review', [ReviewController::class, 'index']);
            // Route::GET('place-review/{review}', [ReviewController::class, 'show']);
            // Route::DELETE('place-review/{review}', [ReviewController::class, 'destroy']);

        });

    });

    Route::group(['prefix' => 'client'], function () {

        /***************************************** Index *******************************************/

        Route::GET('index', [MainController::class, 'index'])->name('get.index');

        Route::GET('places/{category}', [MainController::class, 'places'])->name('get.places.category');
        Route::get('places/{place}/reviews', [MainController::class, 'reviews'])->name('get.places.place.reviews');

        // Define a route to Get Orders List Orders
        Route::POST('review', [MainController::class, 'review'])->middleware(['auth:sanctum', 'account.active', 'throttle:review'])->name('post.review');

        // Define a route to Get Orders List Orders
        Route::POST('feedback', [MainController::class, 'feedback'])->middleware('throttle:feedback')->name('post.feedback');

    });

};

Route::prefix('v1')->name('v1.')->group($apiRoutes);
Route::group([], $apiRoutes);
