<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transfusion_checklists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stay_id')->constrained()->cascadeOnDelete();
            $table->string('folio', 50)->nullable();

            $table->timestamp('started_at');
            $table->timestamp('finalized_at')->nullable();

            // ============ SECCIÓN 1: ENTRADA ============
            $table->boolean('entry_identity_confirmed')->default(false);
            $table->boolean('entry_indication_confirmed')->default(false);
            $table->boolean('entry_product_confirmed')->default(false);
            $table->boolean('entry_consent_confirmed')->default(false);
            $table->boolean('entry_via_unique')->default(false);
            $table->boolean('entry_via_permeable')->default(false);
            $table->boolean('entry_asepsis_done')->default(false);
            $table->boolean('entry_check_flebotech')->default(false);
            $table->boolean('entry_check_availability')->default(false);
            $table->boolean('entry_check_transport')->default(false);
            $table->boolean('entry_check_vitals')->default(false);
            $table->boolean('entry_equipment_ok')->default(false);
            $table->string('entry_allergies', 10)->nullable();
            $table->string('entry_allergies_detail', 500)->nullable();
            $table->string('entry_previous_reactions', 30)->nullable();
            $table->string('entry_bleeding_risk', 30)->nullable();
            $table->string('entry_blood_products_available', 30)->nullable();

            // ============ SECCIÓN 2: PAUSA ============
            $table->boolean('pause_doctor_on_duty_present')->default(false);
            $table->boolean('pause_anesthesiologist_present')->default(false);
            $table->boolean('pause_nurse_present')->default(false);
            $table->boolean('pause_identity_verified')->default(false);
            $table->boolean('pause_indication_verified')->default(false);
            $table->boolean('pause_access_verified')->default(false);
            $table->boolean('pause_product_verified')->default(false);

            $table->string('product_group', 10)->nullable();
            $table->string('product_rh_factor', 10)->nullable();
            $table->string('product_folio', 50)->nullable();
            $table->string('product_quantity', 50)->nullable();

            $table->decimal('product_volume_total', 8, 1)->nullable();
            $table->boolean('product_red_cells')->default(false);
            $table->decimal('product_red_cells_amount', 8, 1)->nullable();
            $table->boolean('product_fresh_plasma')->default(false);
            $table->decimal('product_fresh_plasma_amount', 8, 1)->nullable();
            $table->boolean('product_platelet_concentrate')->default(false);
            $table->decimal('product_platelet_concentrate_amount', 8, 1)->nullable();
            $table->boolean('product_cryoprecipitate')->default(false);
            $table->decimal('product_cryoprecipitate_amount', 8, 1)->nullable();
            $table->boolean('product_factor_vii')->default(false);
            $table->decimal('product_factor_vii_amount', 8, 1)->nullable();
            $table->boolean('product_apheresis')->default(false);
            $table->decimal('product_apheresis_amount', 8, 1)->nullable();
            $table->string('product_other', 200)->nullable();
            $table->decimal('product_other_amount', 8, 1)->nullable();

            $table->unsignedSmallInteger('pause_vitals_fc')->nullable();
            $table->string('pause_vitals_ta', 20)->nullable();
            $table->decimal('pause_vitals_temp', 4, 1)->nullable();
            $table->unsignedSmallInteger('pause_vitals_fr')->nullable();

            // ============ SECCIÓN 3: DURANTE Y SALIDA ============
            $table->boolean('during_monitoring_done')->default(false);
            $table->boolean('during_vitals_monitored')->default(false);
            $table->boolean('during_adverse_reactions_monitored')->default(false);
            $table->boolean('during_duration_monitored')->default(false);
            $table->boolean('during_via_permeability_monitored')->default(false);

            $table->boolean('exit_vitals_confirmed')->default(false);
            $table->boolean('exit_logbook_filled')->default(false);
            $table->boolean('exit_bag_disposed')->default(false);

            $table->boolean('adverse_events_occurred')->default(false);
            $table->string('adverse_events_detail', 500)->nullable();
            $table->boolean('adverse_events_registered')->default(false);
            $table->string('adverse_events_register_location', 200)->nullable();

            $table->foreignId('created_by_id')->constrained('users');
            $table->foreignId('updated_by_id')->nullable()->constrained('users');

            $table->timestamps();

            $table->index(['stay_id', 'finalized_at']);
            $table->index('started_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transfusion_checklists');
    }
};
