<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Master\ProductTypesController;
use App\Http\Controllers\Master\ProductBrandsController;
use App\Http\Controllers\Master\ProductController;
use App\Http\Controllers\Master\UsersController;
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
    // return view('welcome');
    return redirect()->route('login');
});

// Route::get('/init', [AuthController::class, 'init']);
Route::group(['middleware' => 'guest'], function(){
    Route::group(['prefix' => 'login'], function(){
        Route::get('/', [AuthController::class, 'login'])->name('login');
        Route::post('/', [AuthController::class, 'login_process'])->name('login.post');
    });
});

Route::group(['middleware' => 'auth'], function(){
    Route::get('logout', [AuthController::class, 'logout'])->name('logout');
    Route::group(['prefix' => 'dashboard'], function(){
        Route::get('/', [DashboardController::class, 'dashboard'])->name('dashboard');
    });

    Route::group(['prefix' => 'master'], function(){
        Route::group(['prefix' => 'users'], function(){
            Route::get('/', [UsersController::class, 'index'])->name('master.users');
            Route::post('/search', [UsersController::class, 'search'])->name('master.users.search');
            Route::post('/view', [UsersController::class, 'view'])->name('master.users.view');
            Route::post('/upsert', [UsersController::class, 'upsert'])->name('master.users.upsert');
            Route::post('/delete', [UsersController::class, 'delete'])->name('master.users.delete');
        });

        Route::group(['prefix' => 'product-types'], function(){
            Route::get('/', [ProductTypesController::class, 'index'])->name('master.product_types');
            Route::post('/search', [ProductTypesController::class, 'search'])->name('master.product_types.search');
            Route::post('/view', [ProductTypesController::class, 'view'])->name('master.product_types.view');
            Route::post('/upsert', [ProductTypesController::class, 'upsert'])->name('master.product_types.upsert');
            Route::post('/delete', [ProductTypesController::class, 'delete'])->name('master.product_types.delete');
        });

        Route::group(['prefix' => 'product-brands'], function(){
            Route::get('/', [ProductBrandsController::class, 'index'])->name('master.product_brands');
            Route::post('/search', [ProductBrandsController::class, 'search'])->name('master.product_brands.search');
            Route::post('/view', [ProductBrandsController::class, 'view'])->name('master.product_brands.view');
            Route::post('/upsert', [ProductBrandsController::class, 'upsert'])->name('master.product_brands.upsert');
            Route::post('/delete', [ProductBrandsController::class, 'delete'])->name('master.product_brands.delete');
        });

        Route::group(['prefix' => 'products'], function(){
            Route::get('/', [ProductController::class, 'index'])->name('master.product');
            Route::post('/search', [ProductController::class, 'search'])->name('master.product.search');

            Route::get('/view/{product_id?}', [ProductController::class, 'view'])->name('master.product.view');
            Route::get('/edit/{product_id?}', [ProductController::class, 'edit'])->name('master.product.edit');
            Route::get('/add', [ProductController::class, 'add'])->name('master.product.add');

            Route::post('/upsert', [ProductController::class, 'upsert'])->name('master.product.upsert');
            Route::post('/delete', [ProductController::class, 'delete'])->name('master.product.delete');
        });

        Route::group(['prefix' => 'product_bundle'], function(){
            Route::get('/', [ProductBundleController::class, 'index'])->name('master.product_bundle');
            Route::post('/search', [ProductBundleController::class, 'search'])->name('master.product_bundle.search');

            Route::get('/view/{bundle_id?}', [ProductBundleController::class, 'view'])->name('master.product_bundle.view');
            Route::get('/edit/{bundle_id?}', [ProductBundleController::class, 'edit'])->name('master.product_bundle.edit');
            Route::get('/add', [ProductBundleController::class, 'add'])->name('master.product_bundle.add');

            Route::post('/upsert', [ProductBundleController::class, 'upsert'])->name('master.product_bundle.upsert');
            Route::post('/delete', [ProductBundleController::class, 'delete'])->name('master.product_bundle.delete');
        });

        Route::group(['prefix' => 'items'], function(){
            Route::get('/', [ItemController::class, 'index'])->name('master.item');
            Route::post('/search', [ItemController::class, 'search'])->name('master.item.search');
        });
    });
});