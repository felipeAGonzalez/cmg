<?php

use App\Http\Controllers\Admin\SpecialtyController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\FluidBalanceCaptureController;
use App\Http\Controllers\FluidBalanceOrderController;
use App\Http\Controllers\FrontSheetController;
use App\Http\Controllers\GlucoseMonitoringOrderController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MedicationAdministrationController;
use App\Http\Controllers\MedicationOrderController;
use App\Http\Controllers\NursingEntryController;
use App\Http\Controllers\NursingSheetController;
use App\Http\Controllers\AdmissionNotePdfController;
use App\Http\Controllers\AnesthesiaConsentController;
use App\Http\Controllers\AuthorizedConsentController;
use App\Http\Controllers\NursingSheetPdfController;
use App\Http\Controllers\PasswordChangeController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\RoomTransferController;
use App\Http\Controllers\ShiftSummaryController;
use App\Http\Controllers\StayController;
use App\Http\Controllers\StayDoctorController;
use App\Http\Controllers\StayMeasurementController;
use App\Http\Controllers\DischargeNoteController;
use App\Http\Controllers\MedicalHistoryController;
use App\Http\Controllers\MedicalTemplateController;
use App\Http\Controllers\TriageRecordController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WaitingRoomController;
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

        // ─── Órdenes de monitoreo de glucemia (admin + doctor + nurse) ───────
        // La validación fina de permisos se hace en el controlador.
        Route::middleware('role:admin,doctor,nurse')->group(function () {
            Route::get('/stays/{stay}/glucose-monitoring-orders/create', [GlucoseMonitoringOrderController::class, 'create'])->name('glucoseMonitoringOrders.create');
            Route::post('/stays/{stay}/glucose-monitoring-orders', [GlucoseMonitoringOrderController::class, 'store'])->name('glucoseMonitoringOrders.store');
            Route::get('/glucose-monitoring-orders/{glucoseMonitoringOrder}/suspend', [GlucoseMonitoringOrderController::class, 'suspendForm'])->name('glucoseMonitoringOrders.suspendForm');
            Route::post('/glucose-monitoring-orders/{glucoseMonitoringOrder}/suspend', [GlucoseMonitoringOrderController::class, 'suspend'])->name('glucoseMonitoringOrders.suspend');
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

        // ─── PDF de Nota de Ingreso (admin + nurse + médicos asignados) ──────
        Route::middleware('role:admin,nurse,doctor')->group(function () {
            Route::get('/stays/{stay}/admission-note/pdf', [AdmissionNotePdfController::class, 'show'])->name('admissionNote.pdf');
        });

        // ─── Consentimientos (admin + nurse + médicos asignados) ────────────
        Route::middleware('role:admin,doctor,nurse')->group(function () {
            // Consentimiento Autorizado Bajo Información
            Route::get('/stays/{stay}/authorized-consent/edit', [AuthorizedConsentController::class, 'edit'])->name('authorizedConsent.edit');
            Route::put('/stays/{stay}/authorized-consent', [AuthorizedConsentController::class, 'update'])->name('authorizedConsent.update');
            Route::get('/stays/{stay}/authorized-consent/pdf', [AuthorizedConsentController::class, 'pdf'])->name('authorizedConsent.pdf');

            // Consentimiento Informado para Anestesia
            Route::get('/stays/{stay}/anesthesia-consent/edit', [AnesthesiaConsentController::class, 'edit'])->name('anesthesiaConsent.edit');
            Route::put('/stays/{stay}/anesthesia-consent', [AnesthesiaConsentController::class, 'update'])->name('anesthesiaConsent.update');
            Route::get('/stays/{stay}/anesthesia-consent/pdf', [AnesthesiaConsentController::class, 'pdf'])->name('anesthesiaConsent.pdf');
        });

        // ─── Traslado de cuartos (solo nurse) ───────────────────────────────
        Route::middleware('role:nurse')->group(function () {
            Route::get('/stays/{stay}/transfer', [RoomTransferController::class, 'create'])->name('roomTransfers.create');
            Route::post('/stays/{stay}/transfer', [RoomTransferController::class, 'store'])->name('roomTransfers.store');
        });

        // ─── Crear pacientes (admin + doctor + nurse, necesario para flujo de triage) ─
        Route::middleware('role:admin,doctor,nurse')->group(function () {
            Route::get('/patients/create', [PatientController::class, 'create'])->name('patients.create');
            Route::post('/patients', [PatientController::class, 'store'])->name('patients.store');
        });

        // ─── Administración (solo admin) ─────────────────────────────────────
        Route::middleware('role:admin')->group(function () {

            Route::resource('rooms', RoomController::class)->except(['show', 'index']);
            Route::resource('patients', PatientController::class)->except(['edit', 'update', 'create', 'store']);

            Route::delete('/stay-doctors/{stayDoctor}', [StayDoctorController::class, 'destroy'])->name('stayDoctors.destroy');

            Route::resource('users', UserController::class)->except(['show']);

            // Catálogo de especialidades (sin destroy: solo activar/desactivar).
            Route::get('/admin/specialties', [SpecialtyController::class, 'index'])->name('specialties.index');
            Route::get('/admin/specialties/create', [SpecialtyController::class, 'create'])->name('specialties.create');
            Route::post('/admin/specialties', [SpecialtyController::class, 'store'])->name('specialties.store');
            Route::get('/admin/specialties/{specialty}/edit', [SpecialtyController::class, 'edit'])->name('specialties.edit');
            Route::put('/admin/specialties/{specialty}', [SpecialtyController::class, 'update'])->name('specialties.update');
            Route::post('/admin/specialties/{specialty}/toggle', [SpecialtyController::class, 'toggle'])->name('specialties.toggle');
        });

        // ─── Triage + Sala de Espera (admin + doctor + nurse) ────────────────
        Route::middleware('role:admin,doctor,nurse')->group(function () {
            Route::get('/waiting-room', [WaitingRoomController::class, 'index'])->name('waitingRoom.index');
            Route::get('/triage/start', [WaitingRoomController::class, 'start'])->name('triage.start');
            Route::get('/triage/patients/search', [WaitingRoomController::class, 'searchPatients'])->name('triage.patients.search');

            Route::get('/triage/create/{patient}', [TriageRecordController::class, 'create'])->name('triage.create');
            Route::post('/triage', [TriageRecordController::class, 'store'])->name('triage.store');
            Route::get('/triage/{triage}', [TriageRecordController::class, 'show'])->name('triage.show');
            Route::get('/triage/{triage}/edit', [TriageRecordController::class, 'edit'])->name('triage.edit');
            Route::put('/triage/{triage}', [TriageRecordController::class, 'update'])->name('triage.update');
            Route::post('/triage/{triage}/disposition', [TriageRecordController::class, 'updateDisposition'])->name('triage.updateDisposition');
            Route::post('/triage/{triage}/hospitalize', [TriageRecordController::class, 'hospitalize'])->name('triage.hospitalize');
            Route::get('/triage/{triage}/pdf', [TriageRecordController::class, 'pdf'])->name('triage.pdf');
        });

        // ─── Historia Clínica (admin + doctor + nurse) ────────────────────────
        Route::middleware('role:admin,doctor,nurse')->group(function () {
            Route::get('/stays/{stay}/medical-history/edit', [MedicalHistoryController::class, 'edit'])->name('medicalHistory.edit');
            Route::put('/stays/{stay}/medical-history', [MedicalHistoryController::class, 'update'])->name('medicalHistory.update');
            Route::get('/stays/{stay}/medical-history/pdf', [MedicalHistoryController::class, 'pdf'])->name('medicalHistory.pdf');
            Route::get('/medical-templates/{template}/content', [MedicalHistoryController::class, 'templateContent'])->name('medicalTemplates.content');
        });

        // ─── Nota de Egreso (admin + doctor + nurse) ─────────────────────
        Route::middleware('role:admin,doctor,nurse')->group(function () {
            Route::get('/stays/{stay}/discharge-note/edit', [DischargeNoteController::class, 'edit'])->name('dischargeNote.edit');
            Route::put('/stays/{stay}/discharge-note', [DischargeNoteController::class, 'update'])->name('dischargeNote.update');
            Route::get('/stays/{stay}/discharge-note/pdf', [DischargeNoteController::class, 'pdf'])->name('dischargeNote.pdf');
        });

        // ─── Plantillas médicas (admin + doctor) ─────────────────────────────
        Route::middleware('role:admin,doctor')->group(function () {
            Route::get('/medical-templates', [MedicalTemplateController::class, 'index'])->name('medicalTemplates.index');
            Route::get('/medical-templates/create', [MedicalTemplateController::class, 'create'])->name('medicalTemplates.create');
            Route::post('/medical-templates', [MedicalTemplateController::class, 'store'])->name('medicalTemplates.store');
            Route::get('/medical-templates/{template}', [MedicalTemplateController::class, 'show'])->name('medicalTemplates.show');
            Route::get('/medical-templates/{template}/edit', [MedicalTemplateController::class, 'edit'])->name('medicalTemplates.edit');
            Route::put('/medical-templates/{template}', [MedicalTemplateController::class, 'update'])->name('medicalTemplates.update');
            Route::post('/medical-templates/{template}/duplicate', [MedicalTemplateController::class, 'duplicate'])->name('medicalTemplates.duplicate');
            Route::delete('/medical-templates/{template}', [MedicalTemplateController::class, 'destroy'])->name('medicalTemplates.destroy');
        });

        // ─── Vista del doctor ────────────────────────────────────────────────
        Route::middleware('role:doctor')->group(function () {
            Route::get('/my-patients', [DoctorController::class, 'index'])->name('doctor.myPatients');
            Route::get('/my-patients/{stay}', [DoctorController::class, 'show'])->name('doctor.patientDetail');
            Route::post('/my-patients/{stay}/instructions', [DoctorController::class, 'storeInstruction'])->name('doctor.storeInstruction');
        });
    });

require __DIR__.'/auth.php';
