<?php

use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return view('pages.index');
});

Route::prefix('/form-text')->group(function () {
    Route::post('/create', [App\Http\Controllers\FormTextController::class, 'create']);
});


