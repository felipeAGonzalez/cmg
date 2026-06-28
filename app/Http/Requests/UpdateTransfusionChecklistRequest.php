<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTransfusionChecklistRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array(auth()->user()->role, ['admin', 'doctor', 'nurse']);
    }

    protected function prepareForValidation()
    {
        $booleanFields = [
            'entry_identity_confirmed', 'entry_indication_confirmed',
            'entry_product_confirmed', 'entry_consent_confirmed',
            'entry_via_unique', 'entry_via_permeable',
            'entry_asepsis_done',
            'entry_check_flebotech', 'entry_check_availability',
            'entry_check_transport', 'entry_check_vitals',
            'entry_equipment_ok',
            'pause_doctor_on_duty_present', 'pause_anesthesiologist_present',
            'pause_nurse_present',
            'pause_identity_verified', 'pause_indication_verified',
            'pause_access_verified', 'pause_product_verified',
            'product_red_cells', 'product_fresh_plasma',
            'product_platelet_concentrate', 'product_cryoprecipitate',
            'product_factor_vii', 'product_apheresis',
            'during_monitoring_done', 'during_vitals_monitored',
            'during_adverse_reactions_monitored', 'during_duration_monitored',
            'during_via_permeability_monitored',
            'exit_vitals_confirmed', 'exit_logbook_filled', 'exit_bag_disposed',
            'adverse_events_occurred', 'adverse_events_registered',
        ];

        $merge = [];
        foreach ($booleanFields as $field) {
            $merge[$field] = $this->boolean($field);
        }
        $this->merge($merge);
    }

    public function rules(): array
    {
        return [
            'folio' => ['nullable', 'string', 'max:50'],

            'entry_identity_confirmed' => ['nullable', 'boolean'],
            'entry_indication_confirmed' => ['nullable', 'boolean'],
            'entry_product_confirmed' => ['nullable', 'boolean'],
            'entry_consent_confirmed' => ['nullable', 'boolean'],
            'entry_via_unique' => ['nullable', 'boolean'],
            'entry_via_permeable' => ['nullable', 'boolean'],
            'entry_asepsis_done' => ['nullable', 'boolean'],
            'entry_check_flebotech' => ['nullable', 'boolean'],
            'entry_check_availability' => ['nullable', 'boolean'],
            'entry_check_transport' => ['nullable', 'boolean'],
            'entry_check_vitals' => ['nullable', 'boolean'],
            'entry_equipment_ok' => ['nullable', 'boolean'],

            'entry_allergies' => ['nullable', 'in:no,yes'],
            'entry_allergies_detail' => ['nullable', 'string', 'max:500'],
            'entry_previous_reactions' => ['nullable', 'in:no,yes_doctor_aware'],
            'entry_bleeding_risk' => ['nullable', 'in:no,yes_with_access'],
            'entry_blood_products_available' => ['nullable', 'in:no,yes_crossmatched'],

            'pause_doctor_on_duty_present' => ['nullable', 'boolean'],
            'pause_anesthesiologist_present' => ['nullable', 'boolean'],
            'pause_nurse_present' => ['nullable', 'boolean'],
            'pause_identity_verified' => ['nullable', 'boolean'],
            'pause_indication_verified' => ['nullable', 'boolean'],
            'pause_access_verified' => ['nullable', 'boolean'],
            'pause_product_verified' => ['nullable', 'boolean'],

            'product_group' => ['nullable', 'string', 'max:10'],
            'product_rh_factor' => ['nullable', 'string', 'max:10'],
            'product_folio' => ['nullable', 'string', 'max:50'],
            'product_quantity' => ['nullable', 'string', 'max:50'],
            'product_volume_total' => ['nullable', 'numeric', 'min:0', 'max:10000'],

            'product_red_cells' => ['nullable', 'boolean'],
            'product_red_cells_amount' => ['nullable', 'numeric', 'min:0', 'max:10000'],
            'product_fresh_plasma' => ['nullable', 'boolean'],
            'product_fresh_plasma_amount' => ['nullable', 'numeric', 'min:0', 'max:10000'],
            'product_platelet_concentrate' => ['nullable', 'boolean'],
            'product_platelet_concentrate_amount' => ['nullable', 'numeric', 'min:0', 'max:10000'],
            'product_cryoprecipitate' => ['nullable', 'boolean'],
            'product_cryoprecipitate_amount' => ['nullable', 'numeric', 'min:0', 'max:10000'],
            'product_factor_vii' => ['nullable', 'boolean'],
            'product_factor_vii_amount' => ['nullable', 'numeric', 'min:0', 'max:10000'],
            'product_apheresis' => ['nullable', 'boolean'],
            'product_apheresis_amount' => ['nullable', 'numeric', 'min:0', 'max:10000'],
            'product_other' => ['nullable', 'string', 'max:200'],
            'product_other_amount' => ['nullable', 'numeric', 'min:0', 'max:10000'],

            'pause_vitals_fc' => ['nullable', 'integer', 'min:0', 'max:300'],
            'pause_vitals_ta' => ['nullable', 'string', 'max:20'],
            'pause_vitals_temp' => ['nullable', 'numeric', 'min:25', 'max:45'],
            'pause_vitals_fr' => ['nullable', 'integer', 'min:0', 'max:100'],

            'during_monitoring_done' => ['nullable', 'boolean'],
            'during_vitals_monitored' => ['nullable', 'boolean'],
            'during_adverse_reactions_monitored' => ['nullable', 'boolean'],
            'during_duration_monitored' => ['nullable', 'boolean'],
            'during_via_permeability_monitored' => ['nullable', 'boolean'],
            'exit_vitals_confirmed' => ['nullable', 'boolean'],
            'exit_logbook_filled' => ['nullable', 'boolean'],
            'exit_bag_disposed' => ['nullable', 'boolean'],
            'adverse_events_occurred' => ['nullable', 'boolean'],
            'adverse_events_detail' => ['nullable', 'string', 'max:500'],
            'adverse_events_registered' => ['nullable', 'boolean'],
            'adverse_events_register_location' => ['nullable', 'string', 'max:200'],
        ];
    }
}
