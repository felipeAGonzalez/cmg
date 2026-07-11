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
use App\Http\Controllers\TransfusionChecklistController;
use App\Http\Controllers\EvolutionNoteController;
use App\Http\Controllers\MedicalHistoryTemplateController;
use App\Http\Controllers\EvolutionTemplateController;
use App\Http\Controllers\DischargeTemplateController;
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
            Route::resource('patients', PatientController::class)->except(['edit', 'update', 'create', 'store', 'show']);

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

        // ─── Expediente del paciente: consulta histórica (admin + doctor) ───────
        Route::middleware('role:admin,doctor')->group(function () {
            Route::get('/patients/{patient}', [PatientController::class, 'show'])->name('patients.show');
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
            Route::get('/medical-history-templates/{template}/content', [MedicalHistoryTemplateController::class, 'content'])->name('medicalHistoryTemplates.content');
        });

        // ─── Nota de Alta (admin + doctor + nurse) ────────────────────────
        Route::middleware('role:admin,doctor,nurse')->group(function () {
            Route::get('/stays/{stay}/discharge-note/edit', [DischargeNoteController::class, 'edit'])->name('dischargeNote.edit');
            Route::put('/stays/{stay}/discharge-note', [DischargeNoteController::class, 'update'])->name('dischargeNote.update');
            Route::get('/stays/{stay}/discharge-note/pdf', [DischargeNoteController::class, 'pdf'])->name('dischargeNote.pdf');
            Route::delete('/stays/{stay}/discharge-note', [DischargeNoteController::class, 'destroy'])->name('dischargeNote.destroy');
        });

        // ─── Notas de Evolución (admin + doctor + nurse) ──────────────────────
        Route::middleware('role:admin,doctor,nurse')->group(function () {
            Route::get('/stays/{stay}/evolution-notes', [EvolutionNoteController::class, 'index'])->name('evolutionNotes.index');
            Route::get('/stays/{stay}/evolution-notes/create', [EvolutionNoteController::class, 'create'])->name('evolutionNotes.create');
            Route::post('/stays/{stay}/evolution-notes', [EvolutionNoteController::class, 'store'])->name('evolutionNotes.store');
            Route::get('/evolution-notes/{note}', [EvolutionNoteController::class, 'show'])->name('evolutionNotes.show');
            Route::get('/evolution-notes/{note}/edit', [EvolutionNoteController::class, 'edit'])->name('evolutionNotes.edit');
            Route::put('/evolution-notes/{note}', [EvolutionNoteController::class, 'update'])->name('evolutionNotes.update');
            Route::get('/evolution-notes/{note}/pdf', [EvolutionNoteController::class, 'pdf'])->name('evolutionNotes.pdf');
            Route::delete('/evolution-notes/{note}', [EvolutionNoteController::class, 'destroy'])->name('evolutionNotes.destroy');
        });

        // ─── Transfusiones (admin + doctor + nurse) ────────────────────────
        Route::middleware('role:admin,doctor,nurse')->group(function () {
            Route::get('/stays/{stay}/transfusion-checklists', [TransfusionChecklistController::class, 'index'])->name('transfusionChecklists.index');
            Route::get('/stays/{stay}/transfusion-checklists/create', [TransfusionChecklistController::class, 'create'])->name('transfusionChecklists.create');
            Route::post('/stays/{stay}/transfusion-checklists', [TransfusionChecklistController::class, 'store'])->name('transfusionChecklists.store');
            Route::get('/transfusion-checklists/{checklist}', [TransfusionChecklistController::class, 'show'])->name('transfusionChecklists.show');
            Route::get('/transfusion-checklists/{checklist}/edit', [TransfusionChecklistController::class, 'edit'])->name('transfusionChecklists.edit');
            Route::put('/transfusion-checklists/{checklist}', [TransfusionChecklistController::class, 'update'])->name('transfusionChecklists.update');
            Route::post('/transfusion-checklists/{checklist}/finalize', [TransfusionChecklistController::class, 'finalize'])->name('transfusionChecklists.finalize');
            Route::get('/transfusion-checklists/{checklist}/pdf', [TransfusionChecklistController::class, 'pdf'])->name('transfusionChecklists.pdf');
            Route::delete('/transfusion-checklists/{checklist}', [TransfusionChecklistController::class, 'destroy'])->name('transfusionChecklists.destroy');
        });

        // ─── Plantillas médicas (admin + doctor) ─────────────────────────────
        Route::middleware('role:admin,doctor')->group(function () {
            // Historia Clínica
            Route::get('/medical-history-templates', [MedicalHistoryTemplateController::class, 'index'])->name('medicalHistoryTemplates.index');
            Route::get('/medical-history-templates/create', [MedicalHistoryTemplateController::class, 'create'])->name('medicalHistoryTemplates.create');
            Route::post('/medical-history-templates', [MedicalHistoryTemplateController::class, 'store'])->name('medicalHistoryTemplates.store');
            Route::get('/medical-history-templates/{template}', [MedicalHistoryTemplateController::class, 'show'])->name('medicalHistoryTemplates.show');
            Route::get('/medical-history-templates/{template}/edit', [MedicalHistoryTemplateController::class, 'edit'])->name('medicalHistoryTemplates.edit');
            Route::put('/medical-history-templates/{template}', [MedicalHistoryTemplateController::class, 'update'])->name('medicalHistoryTemplates.update');
            Route::post('/medical-history-templates/{template}/duplicate', [MedicalHistoryTemplateController::class, 'duplicate'])->name('medicalHistoryTemplates.duplicate');
            Route::delete('/medical-history-templates/{template}', [MedicalHistoryTemplateController::class, 'destroy'])->name('medicalHistoryTemplates.destroy');

            // Evolución
            Route::get('/evolution-templates', [EvolutionTemplateController::class, 'index'])->name('evolutionTemplates.index');
            Route::get('/evolution-templates/create', [EvolutionTemplateController::class, 'create'])->name('evolutionTemplates.create');
            Route::post('/evolution-templates', [EvolutionTemplateController::class, 'store'])->name('evolutionTemplates.store');
            Route::get('/evolution-templates/{template}', [EvolutionTemplateController::class, 'show'])->name('evolutionTemplates.show');
            Route::get('/evolution-templates/{template}/edit', [EvolutionTemplateController::class, 'edit'])->name('evolutionTemplates.edit');
            Route::put('/evolution-templates/{template}', [EvolutionTemplateController::class, 'update'])->name('evolutionTemplates.update');
            Route::post('/evolution-templates/{template}/duplicate', [EvolutionTemplateController::class, 'duplicate'])->name('evolutionTemplates.duplicate');
            Route::delete('/evolution-templates/{template}', [EvolutionTemplateController::class, 'destroy'])->name('evolutionTemplates.destroy');
            Route::get('/evolution-templates/{template}/content', [EvolutionTemplateController::class, 'content'])->name('evolutionTemplates.content');

            // Alta
            Route::get('/discharge-templates', [DischargeTemplateController::class, 'index'])->name('dischargeTemplates.index');
            Route::get('/discharge-templates/create', [DischargeTemplateController::class, 'create'])->name('dischargeTemplates.create');
            Route::post('/discharge-templates', [DischargeTemplateController::class, 'store'])->name('dischargeTemplates.store');
            Route::get('/discharge-templates/{template}', [DischargeTemplateController::class, 'show'])->name('dischargeTemplates.show');
            Route::get('/discharge-templates/{template}/edit', [DischargeTemplateController::class, 'edit'])->name('dischargeTemplates.edit');
            Route::put('/discharge-templates/{template}', [DischargeTemplateController::class, 'update'])->name('dischargeTemplates.update');
            Route::post('/discharge-templates/{template}/duplicate', [DischargeTemplateController::class, 'duplicate'])->name('dischargeTemplates.duplicate');
            Route::delete('/discharge-templates/{template}', [DischargeTemplateController::class, 'destroy'])->name('dischargeTemplates.destroy');
            Route::get('/discharge-templates/{template}/content', [DischargeTemplateController::class, 'content'])->name('dischargeTemplates.content');
        });

        // ─── Vista de pacientes (doctor + admin + nurse) ─────────────────────
        Route::middleware('role:admin,doctor,nurse')->group(function () {
            Route::get('/my-patients', [DoctorController::class, 'index'])->name('doctor.myPatients');
            Route::get('/my-patients/{stay}', [DoctorController::class, 'show'])->name('doctor.patientDetail');
        });

        // ─── Instrucciones de médico ─────────────────────────────────────────
        Route::middleware('role:doctor')->group(function () {
            Route::post('/my-patients/{stay}/instructions', [DoctorController::class, 'storeInstruction'])->name('doctor.storeInstruction');
        });

        // ─── Indicar / revertir alta (admin + doctor) ───────────────────────
        Route::middleware('role:admin,doctor')->group(function () {
            Route::post('/stays/{stay}/indicate-discharge', [StayController::class, 'indicateDischarge'])->name('stays.indicateDischarge');
            Route::post('/stays/{stay}/revert-discharge-indication', [StayController::class, 'revertDischargeIndication'])->name('stays.revertDischargeIndication');
        });
    });

require __DIR__.'/auth.php';
