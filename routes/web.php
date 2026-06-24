<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DataCleaningController;

// Dashboard
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/api/chart-data', [DashboardController::class, 'getChartData']);
Route::get('/api/filtered-data', [DashboardController::class, 'getFilteredData']);
Route::get('/api/correlation-data', [DashboardController::class, 'getCorrelationData']);

// Data Cleaning
Route::get('/data-cleaning', [DataCleaningController::class, 'index'])->name('data-cleaning');
Route::get('/api/cleaning-details', [DataCleaningController::class, 'getCleaningDetails']);
