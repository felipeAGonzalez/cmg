<?php

use App\Http\Controllers\DoctorController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PasswordChangeController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\RoomTransferController;
use App\Http\Controllers\StayController;
use App\Http\Controllers\StayDoctorController;
use App\Http\Controllers\UserController;
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
