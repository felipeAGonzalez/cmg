<?php

use App\Http\Controllers\Api\Integration\WarehouseActivePatientController;
use App\Http\Controllers\Api\Integration\WarehouseNurseController;
use Illuminate\Support\Facades\Route;

Route::middleware([
    'throttle:warehouse-integration',
    'warehouse.integration',
])->group(function () {
    Route::get('/integrations/warehouse/active-patients', WarehouseActivePatientController::class)
        ->name('api.integrations.warehouse.active-patients');
    Route::get('/integrations/warehouse/nurses', WarehouseNurseController::class)
        ->name('api.integrations.warehouse.nurses');
});
