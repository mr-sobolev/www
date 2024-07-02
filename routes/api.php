<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/v1/tasks', [\App\Http\Controllers\APIController::class, 'store']);
Route::get('/v1/tasks', [\App\Http\Controllers\APIController::class, 'readData']);
