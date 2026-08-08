<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;

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

Route::get('/categories', [HomeController::class, 'categories'])->name('site.categories');
Route::get('/categories/{category}', [HomeController::class, 'categoryDetail'])->name('site.categories.show');
Route::get('/products', [HomeController::class, 'products'])->name('site.products');
Route::get('/products/{product}', [HomeController::class, 'productDetail'])->name('site.products.show');
