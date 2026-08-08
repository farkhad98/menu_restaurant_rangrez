<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Dashboard\AuthController;
use App\Http\Controllers\Dashboard\CategoriesController;
use App\Http\Controllers\Dashboard\ProductsController;
use App\Http\Controllers\HomeController;

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

Route::group(['prefix' => 'dashboard', 'middleware' => 'guest'], function() {
    Route::get('/login', [AuthController::class, 'loginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
});

Route::group(['prefix' => 'dashboard', 'middleware' => 'auth'], function() {
    Route::get('/', function() {
        return redirect()->route('products.index');
    })->name('dashboard.index');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::resource('/categories', CategoriesController::class)->except(['show']);
    Route::resource('/products', ProductsController::class)->except(['show']);
});

Route::group(['prefix' => '{locale}', 'where' => ['locale' => 'ru|en']], function() {
    Route::get('/{vue_capture?}', [HomeController::class, 'index'])->where('vue_capture', '[\/\w\.-]*')->name('index');
});

Route::get('/', function () {
    return redirect(app()->getLocale());
});
