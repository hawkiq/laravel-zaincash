<?php

use Hawkiq\LaravelZaincash\Controllers\ZainCashController;
use Illuminate\Support\Facades\Route;

Route::get('redirect', [ZainCashController::class,'redirect'])->name('redirect');