<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Master\ProductTypesController;
use App\Http\Controllers\Master\ProductBrandsController;
use App\Http\Controllers\Master\ProductController;
use App\Http\Controllers\Master\ProductBundleController;
use App\Http\Controllers\Master\UsersController;
use App\Http\Controllers\Master\ItemController;
use App\Http\Controllers\Master\CustomerController;
use App\Http\Controllers\Master\ConditionController;
use App\Http\Controllers\Master\StatusController;
use App\Http\Controllers\Master\MitraController;
use App\Http\Controllers\Master\RekeningController;
use App\Http\Controllers\ConfigController;

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

    Route::group(['prefix' => 'config'], function(){
        Route::get('/', [ConfigController::class, 'index'])->name('config.main');
        Route::post('/save', [ConfigController::class, 'save'])->name('config.main.save');
    });

    Route::group(['prefix' => 'master'], function(){
        Route::group(['prefix' => 'rekening'], function(){
            Route::get('/', [RekeningController::class, 'index'])->name('master.rekening');
            Route::post('/search', [RekeningController::class, 'search'])->name('master.rekening.search');
            Route::post('/view', [RekeningController::class, 'view'])->name('master.rekening.view');
            Route::post('/upsert', [RekeningController::class, 'upsert'])->name('master.rekening.upsert');
            Route::post('/delete', [RekeningController::class, 'delete'])->name('master.rekening.delete');
        });

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

        Route::group(['prefix' => 'item-status'], function(){
            Route::get('/', [StatusController::class, 'index'])->name('master.item_status');
            Route::post('/search', [StatusController::class, 'search'])->name('master.item_status.search');
            Route::post('/view', [StatusController::class, 'view'])->name('master.item_status.view');
            Route::post('/upsert', [StatusController::class, 'upsert'])->name('master.item_status.upsert');
            Route::post('/delete', [StatusController::class, 'delete'])->name('master.item_status.delete');
        });

        Route::group(['prefix' => 'item-condition'], function(){
            Route::get('/', [ConditionController::class, 'index'])->name('master.item_condition');
            Route::post('/search', [ConditionController::class, 'search'])->name('master.item_condition.search');
            Route::post('/view', [ConditionController::class, 'view'])->name('master.item_condition.view');
            Route::post('/upsert', [ConditionController::class, 'upsert'])->name('master.item_condition.upsert');
            Route::post('/delete', [ConditionController::class, 'delete'])->name('master.item_condition.delete');
        });

        Route::group(['prefix' => 'product-brands'], function(){
            Route::get('/', [ProductBrandsController::class, 'index'])->name('master.product_brands');
            Route::post('/search', [ProductBrandsController::class, 'search'])->name('master.product_brands.search');
            Route::post('/view', [ProductBrandsController::class, 'view'])->name('master.product_brands.view');
            Route::post('/upsert', [ProductBrandsController::class, 'upsert'])->name('master.product_brands.upsert');
            Route::post('/delete', [ProductBrandsController::class, 'delete'])->name('master.product_brands.delete');
        });

        Route::group(['prefix' => 'customer'], function(){
            Route::get('/', [CustomerController::class, 'index'])->name('master.customer');
            Route::post('/search', [CustomerController::class, 'search'])->name('master.customer.search');

            Route::get('/view/{customer_id?}', [CustomerController::class, 'view'])->name('master.customer.view');
            Route::get('/edit/{customer_id?}', [CustomerController::class, 'edit'])->name('master.customer.edit');
            Route::get('/add', [CustomerController::class, 'add'])->name('master.customer.add');

            Route::post('/upsert', [CustomerController::class, 'upsert'])->name('master.customer.upsert');
            Route::post('/delete', [CustomerController::class, 'delete'])->name('master.customer.delete');
        });

        Route::group(['prefix' => 'mitra'], function(){
            Route::get('/', [MitraController::class, 'index'])->name('master.mitra');
            Route::post('/search', [MitraController::class, 'search'])->name('master.mitra.search');

            Route::get('/view/{mitra_id?}', [MitraController::class, 'view'])->name('master.mitra.view');
            Route::get('/edit/{mitra_id?}', [MitraController::class, 'edit'])->name('master.mitra.edit');
            Route::get('/add', [MitraController::class, 'add'])->name('master.mitra.add');

            Route::post('/upsert', [MitraController::class, 'upsert'])->name('master.mitra.upsert');
            Route::post('/delete', [MitraController::class, 'delete'])->name('master.mitra.delete');
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
            Route::get('/product/{product_id?}', [ItemController::class, 'product_header'])->name('master.item.product.form');
            Route::post('/search-item', [ItemController::class, 'search_item'])->name('master.item.product.form.search');

            Route::get('/view/{item_id?}', [ItemController::class, 'view'])->name('master.item.view');
            Route::get('/edit/{item_id?}', [ItemController::class, 'edit'])->name('master.item.edit');
            Route::get('/add/{product_id?}', [ItemController::class, 'add'])->name('master.item.add');

            Route::post('/upsert', [ItemController::class, 'upsert'])->name('master.item.upsert');
            Route::post('/delete', [ItemController::class, 'delete'])->name('master.item.delete');
        });

        Route::group(['prefix' => 'customers'], function(){
            Route::get('/', [CustomerController::class, 'index'])->name('master.customer');
            Route::post('/search', [CustomerController::class, 'search'])->name('master.customer.search');
            
            Route::get('/view/{customer_id?}', [CustomerController::class, 'view'])->name('master.customer.view');
            Route::get('/edit/{customer_id?}', [CustomerController::class, 'edit'])->name('master.customer.edit');
            Route::get('/add', [CustomerController::class, 'add'])->name('master.customer.add');

            Route::post('/upsert', [CustomerController::class, 'upsert'])->name('master.customer.upsert');
            Route::post('/delete', [CustomerController::class, 'delete'])->name('master.customer.delete');
        });
    });

    Route::group(['prefix' => 'transaction'], function(){
        Route::group(['prefix' => 'rent'], function(){
            Route::get('/', [TransactionController::class, 'index'])->name('transaction.rent');
            Route::post('/search', [TransactionController::class, 'search'])->name('transaction.rent.search');

            Route::get('/new', [TransactionController::class, 'add'])->name('transaction.rent.add');
            Route::get('/view/{transaction_id}', [TransactionController::class, 'view'])->name('transaction.rent.view');
            //print = ngelock
            Route::get('/print/{transaction_id}', [TransactionController::class, 'print'])->name('transaction.rent.print');

            Route::post('/upsert', [TransactionController::class, 'upsert'])->name('transaction.rent.upsert');
            Route::post('/delete', [TransactionController::class, 'delete'])->name('transaction.rent.delete');
        });
    });
});