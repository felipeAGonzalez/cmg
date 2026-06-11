<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Stay extends Model
{
    protected $fillable = [
        'patient_id',
        'room_id',
        'birth_parent_stay_id',
        'diagnosis',
        'height_cm',
        'weight_kg',
        'admission_date',
        'discharge_date',
    ];

    protected function casts(): array
    {
        return [
            'admission_date' => 'datetime',
            'discharge_date' => 'datetime',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /** Estancia de la madre, si esta estancia es un nacimiento. */
    public function birthParent(): BelongsTo
    {
        return $this->belongsTo(Stay::class, 'birth_parent_stay_id');
    }

    /** Nacimientos (bebés) ligados a esta estancia. */
    public function births(): HasMany
    {
        return $this->hasMany(Stay::class, 'birth_parent_stay_id');
    }

    /** True si esta estancia es un recién nacido ligado a una madre. */
    public function isBirth(): bool
    {
        return $this->birth_parent_stay_id !== null;
    }

    public function stayDoctors(): HasMany
    {
        return $this->hasMany(StayDoctor::class);
    }

    public function currentDoctors(): HasMany
    {
        return $this->hasMany(StayDoctor::class)->whereNull('removed_at');
    }

    public function roomTransfers(): HasMany
    {
        return $this->hasMany(RoomTransfer::class);
    }

    public function instructions(): HasMany
    {
        return $this->hasMany(StayInstruction::class)->latest();
    }

    public function stayDocuments(): HasMany
    {
        return $this->hasMany(StayDocument::class);
    }

    public function vitalSignReadings(): HasMany
    {
        return $this->hasMany(VitalSignReading::class);
    }

    public function shiftSummaries(): HasMany
    {
        return $this->hasMany(ShiftSummary::class);
    }

    public function medicationOrders(): HasMany
    {
        return $this->hasMany(MedicationOrder::class);
    }

    public function medicationAdministrations(): HasMany
    {
        return $this->hasMany(MedicationAdministration::class);
    }

    public function nursingEntries(): HasMany
    {
        return $this->hasMany(NursingEntry::class);
    }

    public function fluidBalanceOrders(): HasMany
    {
        return $this->hasMany(FluidBalanceOrder::class);
    }

    /** La orden de balance de líquidos activa (única), o null. */
    public function activeFluidBalanceOrder(): ?FluidBalanceOrder
    {
        return $this->fluidBalanceOrders()->whereNull('suspended_at')->first();
    }

    /**
     * Documentos de la estancia ordenados por el orden de despliegue del catálogo.
     */
    public function getDocumentsOrdered(): Collection
    {
        return $this->stayDocuments()
            ->with('document')
            ->get()
            ->sortBy(fn ($sd) => sprintf('%010d-%s', $sd->document->display_order, $sd->document->name))
            ->values();
    }

    /**
     * Genera los stay_documents universales del catálogo para esta estancia.
     * Idempotente: usa firstOrCreate para no duplicar si ya existen.
     */
    public function generateUniversalDocuments(): void
    {
        $universalDocuments = Document::active()->universal()->get();

        foreach ($universalDocuments as $document) {
            $this->stayDocuments()->firstOrCreate(
                ['document_id' => $document->id],
                ['status' => StayDocument::STATUS_PENDING],
            );
        }
    }

    public function isActive(): bool
    {
        return $this->discharge_date === null;
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('discharge_date');
    }

    public function scopeDischarged(Builder $query): Builder
    {
        return $query->whereNotNull('discharge_date');
    }
}
