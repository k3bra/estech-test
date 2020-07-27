<?php

use App\Account;
use App\Product;
use Illuminate\Support\Facades\Route;

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

Route::get('/', function () {
});

Route::get('/prices', 'ProductPriceController@index');


Route::get('/accounts', function () {
    return Account::all();
});

Route::get('/products', function () {

    return Product::all();
});



