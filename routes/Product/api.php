<?php


use App\Http\Controllers;
use Illuminate\Support\Facades\Route;


Route::group(['prefix' => 'v1'], function () {

    Route::resource('product', 'ProductController');
});
