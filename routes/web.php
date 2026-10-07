<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/lapangan/{id}', function ($id) {
    return view('lapangan.show', ['id' => $id]);
});