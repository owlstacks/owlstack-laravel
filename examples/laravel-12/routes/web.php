<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SocialMediaController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// Individual social media test routes
Route::get('/test-telegram', [SocialMediaController::class, 'testTelegram'])
    ->name('social.test.telegram');

Route::get('/test-x', [SocialMediaController::class, 'testX'])
    ->name('social.test.x');

Route::get('/test-facebook', [SocialMediaController::class, 'testFacebook'])
    ->name('social.test.facebook');

Route::get('/test-linkedin', [SocialMediaController::class, 'testLinkedIn'])
    ->name('social.test.linkedin');

// Test all platforms
Route::get('/test-all-social', [SocialMediaController::class, 'testAll'])
    ->name('social.test.all');
