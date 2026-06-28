<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransfusionChecklist extends Model
{
    protected $fillable = [
        'stay_id', 'folio',
        'started_at', 'finalized_at',
        // Sección 1: ENTRADA
        'entry_identity_confirmed', 'entry_indication_confirmed',
        'entry_product_confirmed', 'entry_consent_confirmed',
        'entry_via_unique', 'entry_via_permeable',
        'entry_asepsis_done',
        'entry_check_flebotech', 'entry_check_availability',
        'entry_check_transport', 'entry_check_vitals',
        'entry_equipment_ok',
        'entry_allergies', 'entry_allergies_detail',
        'entry_previous_reactions',
        'entry_bleeding_risk',
        'entry_blood_products_available',
        // Sección 2: PAUSA
        'pause_doctor_on_duty_present', 'pause_anesthesiologist_present',
        'pause_nurse_present',
        'pause_identity_verified', 'pause_indication_verified',
        'pause_access_verified', 'pause_product_verified',
        'product_group', 'product_rh_factor', 'product_folio',
        'product_quantity',
        'product_volume_total',
        'product_red_cells', 'product_red_cells_amount',
        'product_fresh_plasma', 'product_fresh_plasma_amount',
        'product_platelet_concentrate', 'product_platelet_concentrate_amount',
        'product_cryoprecipitate', 'product_cryoprecipitate_amount',
        'product_factor_vii', 'product_factor_vii_amount',
        'product_apheresis', 'product_apheresis_amount',
        'product_other', 'product_other_amount',
        'pause_vitals_fc', 'pause_vitals_ta',
        'pause_vitals_temp', 'pause_vitals_fr',
        // Sección 3: DURANTE Y SALIDA
        'during_monitoring_done', 'during_vitals_monitored',
        'during_adverse_reactions_monitored', 'during_duration_monitored',
        'during_via_permeability_monitored',
        'exit_vitals_confirmed', 'exit_logbook_filled', 'exit_bag_disposed',
        'adverse_events_occurred', 'adverse_events_detail',
        'adverse_events_registered', 'adverse_events_register_location',
        // Trazabilidad
        'created_by_id', 'updated_by_id',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finalized_at' => 'datetime',
        'entry_identity_confirmed' => 'boolean',
        'entry_indication_confirmed' => 'boolean',
        'entry_product_confirmed' => 'boolean',
        'entry_consent_confirmed' => 'boolean',
        'entry_via_unique' => 'boolean',
        'entry_via_permeable' => 'boolean',
        'entry_asepsis_done' => 'boolean',
        'entry_check_flebotech' => 'boolean',
        'entry_check_availability' => 'boolean',
        'entry_check_transport' => 'boolean',
        'entry_check_vitals' => 'boolean',
        'entry_equipment_ok' => 'boolean',
        'pause_doctor_on_duty_present' => 'boolean',
        'pause_anesthesiologist_present' => 'boolean',
        'pause_nurse_present' => 'boolean',
        'pause_identity_verified' => 'boolean',
        'pause_indication_verified' => 'boolean',
        'pause_access_verified' => 'boolean',
        'pause_product_verified' => 'boolean',
        'product_red_cells' => 'boolean',
        'product_fresh_plasma' => 'boolean',
        'product_platelet_concentrate' => 'boolean',
        'product_cryoprecipitate' => 'boolean',
        'product_factor_vii' => 'boolean',
        'product_apheresis' => 'boolean',
        'during_monitoring_done' => 'boolean',
        'during_vitals_monitored' => 'boolean',
        'during_adverse_reactions_monitored' => 'boolean',
        'during_duration_monitored' => 'boolean',
        'during_via_permeability_monitored' => 'boolean',
        'exit_vitals_confirmed' => 'boolean',
        'exit_logbook_filled' => 'boolean',
        'exit_bag_disposed' => 'boolean',
        'adverse_events_occurred' => 'boolean',
        'adverse_events_registered' => 'boolean',
        'product_volume_total' => 'decimal:1',
        'product_red_cells_amount' => 'decimal:1',
        'product_fresh_plasma_amount' => 'decimal:1',
        'product_platelet_concentrate_amount' => 'decimal:1',
        'product_cryoprecipitate_amount' => 'decimal:1',
        'product_factor_vii_amount' => 'decimal:1',
        'product_apheresis_amount' => 'decimal:1',
        'product_other_amount' => 'decimal:1',
        'pause_vitals_temp' => 'decimal:1',
    ];

    public function stay() { return $this->belongsTo(Stay::class); }
    public function createdBy() { return $this->belongsTo(User::class, 'created_by_id'); }
    public function updatedBy() { return $this->belongsTo(User::class, 'updated_by_id'); }

    public function canBeFinalized(): bool
    {
        $entryOk = $this->entry_identity_confirmed
            && $this->entry_indication_confirmed
            && $this->entry_product_confirmed
            && $this->entry_consent_confirmed;

        $pauseOk = !empty($this->product_group)
            && !empty($this->product_rh_factor)
            && !empty($this->product_folio)
            && $this->pause_identity_verified;

        $exitOk = $this->during_monitoring_done
            && $this->exit_vitals_confirmed;

        return $entryOk && $pauseOk && $exitOk;
    }

    public function isFinalized(): bool
    {
        return $this->finalized_at !== null;
    }

    public function statusLabel(): string
    {
        return $this->isFinalized() ? 'Finalizado' : 'En progreso';
    }

    public function statusBadgeClass(): string
    {
        return $this->isFinalized() ? 'bg-success' : 'bg-warning text-dark';
    }

    public function pendingForFinalization(): array
    {
        $pending = [];

        if (!$this->entry_identity_confirmed) $pending[] = 'Confirmar identidad (ENTRADA)';
        if (!$this->entry_indication_confirmed) $pending[] = 'Confirmar indicación (ENTRADA)';
        if (!$this->entry_product_confirmed) $pending[] = 'Confirmar producto (ENTRADA)';
        if (!$this->entry_consent_confirmed) $pending[] = 'Confirmar consentimiento (ENTRADA)';
        if (empty($this->product_group)) $pending[] = 'Capturar grupo sanguíneo (PAUSA)';
        if (empty($this->product_rh_factor)) $pending[] = 'Capturar factor RH (PAUSA)';
        if (empty($this->product_folio)) $pending[] = 'Capturar folio del producto (PAUSA)';
        if (!$this->pause_identity_verified) $pending[] = 'Verificar identidad del paciente (PAUSA)';
        if (!$this->during_monitoring_done) $pending[] = 'Confirmar monitoreo durante (DURANTE Y SALIDA)';
        if (!$this->exit_vitals_confirmed) $pending[] = 'Confirmar signos vitales al terminar (DURANTE Y SALIDA)';

        return $pending;
    }
}
