<?php

use App\Http\Controllers\DoctorController;
use App\Http\Controllers\FluidBalanceCaptureController;
use App\Http\Controllers\FluidBalanceOrderController;
use App\Http\Controllers\FrontSheetController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MedicationAdministrationController;
use App\Http\Controllers\MedicationOrderController;
use App\Http\Controllers\NursingEntryController;
use App\Http\Controllers\NursingSheetController;
use App\Http\Controllers\NursingSheetPdfController;
use App\Http\Controllers\PasswordChangeController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\RoomTransferController;
use App\Http\Controllers\ShiftSummaryController;
use App\Http\Controllers\StayController;
use App\Http\Controllers\StayDoctorController;
use App\Http\Controllers\StayMeasurementController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VitalSignController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return Auth::check()
        ? redirect()->route('home')
        : redirect()->route('login');
});

// Cambio de contraseña: auth + activo, pero SIN el middleware password.changed
// para que usuarios con must_change_password puedan acceder.
Route::middleware(['auth', 'user.active', 'prevent.back'])->group(function () {
    Route::get('/password/change', [PasswordChangeController::class, 'create'])->name('password.change');
    Route::post('/password/change', [PasswordChangeController::class, 'update'])->name('password.change.update');
});

Route::middleware(['auth', 'user.active', 'password.changed', 'prevent.back'])
    ->group(function () {

        Route::get('/home', [HomeController::class, 'index'])->name('home');

        // ─── Tablero y operaciones (admin + nurse) ───────────────────────────
        Route::middleware('role:admin,nurse')->group(function () {

            Route::get('/rooms', [RoomController::class, 'index'])->name('rooms.index');

            Route::get('/rooms/{room}/admit', [StayController::class, 'create'])->name('stays.create');
            Route::post('/rooms/{room}/admit', [StayController::class, 'store'])->name('stays.store');
            Route::get('/rooms/{room}/birth', [StayController::class, 'createBirth'])->name('stays.createBirth');
            Route::post('/rooms/{room}/birth', [StayController::class, 'storeBirth'])->name('stays.storeBirth');
            Route::get('/rooms/{room}/patient', [StayController::class, 'show'])->name('stays.show');
            Route::post('/stays/{stay}/discharge', [StayController::class, 'discharge'])->name('stays.discharge');

            Route::get('/patients/{patient}/edit', [PatientController::class, 'edit'])->name('patients.edit');
            Route::put('/patients/{patient}', [PatientController::class, 'update'])->name('patients.update');

            // Asignar médico (admin + nurse)
            Route::post('/stays/{stay}/doctors', [StayDoctorController::class, 'store'])->name('stayDoctors.store');

            // Escribir instrucción en nombre de un médico asignado (admin + nurse)
            Route::post('/stays/{stay}/instructions', [StayController::class, 'storeInstruction'])->name('stays.storeInstruction');

            // Hoja Frontal: llenar / editar (admin + nurse)
            Route::get('/stays/{stay}/front-sheet/edit', [FrontSheetController::class, 'edit'])->name('frontSheet.edit');
            Route::put('/stays/{stay}/front-sheet', [FrontSheetController::class, 'update'])->name('frontSheet.update');

            // Talla y peso de la estancia (admin + nurse)
            Route::get('/stays/{stay}/measurements', [StayMeasurementController::class, 'edit'])->name('stays.measurements.edit');
            Route::put('/stays/{stay}/measurements', [StayMeasurementController::class, 'update'])->name('stays.measurements.update');

            // Hojas de Enfermería: captura (admin + nurse)
            Route::post('/stays/{stay}/vital-signs', [VitalSignController::class, 'store'])->name('vitalSigns.store');
            Route::put('/vital-signs/{vitalSignReading}', [VitalSignController::class, 'update'])->name('vitalSigns.update');
            Route::delete('/vital-signs/{vitalSignReading}', [VitalSignController::class, 'destroy'])->name('vitalSigns.destroy');

            Route::get('/stays/{stay}/shift-summary', [ShiftSummaryController::class, 'edit'])->name('shiftSummary.edit');
            Route::put('/stays/{stay}/shift-summary', [ShiftSummaryController::class, 'update'])->name('shiftSummary.update');
        });

        // ─── Hojas de Enfermería: consulta (admin + nurse + médicos asignados) ─
        Route::middleware('role:admin,nurse,doctor')->group(function () {
            Route::get('/stays/{stay}/nursing-sheets', [NursingSheetController::class, 'index'])->name('nursingSheets.index');
        });

        // ─── Indicaciones / prescripciones (admin + doctor + nurse) ──────────
        // La validación fina de permisos se hace en el controlador.
        Route::middleware('role:admin,doctor,nurse')->group(function () {
            Route::get('/stays/{stay}/medication-orders', [MedicationOrderController::class, 'index'])->name('medicationOrders.index');
            Route::get('/stays/{stay}/medication-orders/create', [MedicationOrderController::class, 'create'])->name('medicationOrders.create');
            Route::post('/stays/{stay}/medication-orders', [MedicationOrderController::class, 'store'])->name('medicationOrders.store');
            Route::get('/medication-orders/{medicationOrder}/edit', [MedicationOrderController::class, 'edit'])->name('medicationOrders.edit');
            Route::put('/medication-orders/{medicationOrder}', [MedicationOrderController::class, 'update'])->name('medicationOrders.update');
            Route::get('/medication-orders/{medicationOrder}/suspend', [MedicationOrderController::class, 'suspendForm'])->name('medicationOrders.suspendForm');
            Route::post('/medication-orders/{medicationOrder}/suspend', [MedicationOrderController::class, 'suspend'])->name('medicationOrders.suspend');
        });

        // ─── Órdenes de balance de líquidos (admin + doctor + nurse) ─────────
        // La validación fina de permisos se hace en el controlador.
        Route::middleware('role:admin,doctor,nurse')->group(function () {
            Route::get('/stays/{stay}/fluid-balance-orders/create', [FluidBalanceOrderController::class, 'create'])->name('fluidBalanceOrders.create');
            Route::post('/stays/{stay}/fluid-balance-orders', [FluidBalanceOrderController::class, 'store'])->name('fluidBalanceOrders.store');
            Route::get('/fluid-balance-orders/{fluidBalanceOrder}/suspend', [FluidBalanceOrderController::class, 'suspendForm'])->name('fluidBalanceOrders.suspendForm');
            Route::post('/fluid-balance-orders/{fluidBalanceOrder}/suspend', [FluidBalanceOrderController::class, 'suspend'])->name('fluidBalanceOrders.suspend');
        });

        // ─── Captura del balance de líquidos hora por hora ───────────────────
        // Consulta: admin + nurse + médicos asignados (validación en controlador).
        Route::middleware('role:admin,doctor,nurse')->group(function () {
            Route::get('/fluid-balance-orders/{fluidBalanceOrder}/captures', [FluidBalanceCaptureController::class, 'index'])->name('fluidBalanceCaptures.index');
        });
        // Captura/edición/eliminación de tomas: solo admin + nurse.
        Route::middleware('role:admin,nurse')->group(function () {
            Route::post('/fluid-balance-orders/{fluidBalanceOrder}/entries', [FluidBalanceCaptureController::class, 'store'])->name('fluidBalanceCaptures.store');
            Route::put('/fluid-balance-entries/{fluidBalanceEntry}', [FluidBalanceCaptureController::class, 'update'])->name('fluidBalanceCaptures.update');
            Route::delete('/fluid-balance-entries/{fluidBalanceEntry}', [FluidBalanceCaptureController::class, 'destroy'])->name('fluidBalanceCaptures.destroy');
        });

        // ─── Administraciones de medicamentos ────────────────────────────────
        // Consulta: admin + nurse + médicos asignados (validación en controlador).
        Route::middleware('role:admin,doctor,nurse')->group(function () {
            Route::get('/stays/{stay}/medication-administrations', [MedicationAdministrationController::class, 'index'])->name('medicationAdministrations.index');
        });
        // Captura/edición/eliminación: solo admin + nurse.
        Route::middleware('role:admin,nurse')->group(function () {
            Route::get('/stays/{stay}/medication-administrations/create', [MedicationAdministrationController::class, 'create'])->name('medicationAdministrations.create');
            Route::post('/stays/{stay}/medication-administrations', [MedicationAdministrationController::class, 'store'])->name('medicationAdministrations.store');
            Route::get('/medication-administrations/{medicationAdministration}/edit', [MedicationAdministrationController::class, 'edit'])->name('medicationAdministrations.edit');
            Route::put('/medication-administrations/{medicationAdministration}', [MedicationAdministrationController::class, 'update'])->name('medicationAdministrations.update');
            Route::delete('/medication-administrations/{medicationAdministration}', [MedicationAdministrationController::class, 'destroy'])->name('medicationAdministrations.destroy');
        });

        // ─── Notas y registros de enfermería ─────────────────────────────────
        // Consulta: admin + nurse + médicos asignados (validación en controlador).
        Route::middleware('role:admin,doctor,nurse')->group(function () {
            Route::get('/stays/{stay}/nursing-entries', [NursingEntryController::class, 'index'])->name('nursingEntries.index');
        });
        // Captura/edición/eliminación: solo admin + nurse.
        Route::middleware('role:admin,nurse')->group(function () {
            Route::get('/stays/{stay}/nursing-entries/create', [NursingEntryController::class, 'create'])->name('nursingEntries.create');
            Route::post('/stays/{stay}/nursing-entries', [NursingEntryController::class, 'store'])->name('nursingEntries.store');
            Route::get('/nursing-entries/{nursingEntry}/edit', [NursingEntryController::class, 'edit'])->name('nursingEntries.edit');
            Route::put('/nursing-entries/{nursingEntry}', [NursingEntryController::class, 'update'])->name('nursingEntries.update');
            Route::delete('/nursing-entries/{nursingEntry}', [NursingEntryController::class, 'destroy'])->name('nursingEntries.destroy');
        });

        // ─── Hoja Frontal PDF (admin + nurse + médicos asignados) ────────────
        Route::middleware('role:admin,nurse,doctor')->group(function () {
            Route::get('/stays/{stay}/front-sheet/pdf', [FrontSheetController::class, 'pdf'])->name('frontSheet.pdf');
        });

        // ─── PDF compilado de Hojas de Enfermería (admin + nurse + médicos asignados) ─
        Route::middleware('role:admin,nurse,doctor')->group(function () {
            Route::get('/stays/{stay}/nursing-sheets/pdf', [NursingSheetPdfController::class, 'show'])->name('nursingSheets.pdf');
        });

        // ─── Traslado de cuartos (solo nurse) ───────────────────────────────
        Route::middleware('role:nurse')->group(function () {
            Route::get('/stays/{stay}/transfer', [RoomTransferController::class, 'create'])->name('roomTransfers.create');
            Route::post('/stays/{stay}/transfer', [RoomTransferController::class, 'store'])->name('roomTransfers.store');
        });

        // ─── Administración (solo admin) ─────────────────────────────────────
        Route::middleware('role:admin')->group(function () {

            Route::resource('rooms', RoomController::class)->except(['show', 'index']);
            Route::resource('patients', PatientController::class)->except(['edit', 'update']);

            Route::delete('/stay-doctors/{stayDoctor}', [StayDoctorController::class, 'destroy'])->name('stayDoctors.destroy');

            Route::resource('users', UserController::class)->except(['show']);
        });

        // ─── Vista del doctor ────────────────────────────────────────────────
        Route::middleware('role:doctor')->group(function () {
            Route::get('/my-patients', [DoctorController::class, 'index'])->name('doctor.myPatients');
            Route::get('/my-patients/{stay}', [DoctorController::class, 'show'])->name('doctor.patientDetail');
            Route::post('/my-patients/{stay}/instructions', [DoctorController::class, 'storeInstruction'])->name('doctor.storeInstruction');
        });
    });

require __DIR__.'/auth.php';
