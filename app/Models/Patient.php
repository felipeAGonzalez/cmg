<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Patient extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'last_name_one',
        'last_name_two',
        'birth_date',
        'gender',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
        ];
    }

    public function stays(): HasMany
    {
        return $this->hasMany(Stay::class);
    }

    public function currentStay(): HasOne
    {
        return $this->hasOne(Stay::class)->whereNull('discharge_date');
    }

    public function fullName(): string
    {
        return trim("{$this->name} {$this->last_name_one} {$this->last_name_two}");
    }

    public function age(): int
    {
        return $this->birth_date->age;
    }

    /**
     * Busca pacientes duplicados por nombre completo + fecha de nacimiento.
     * Maneja correctamente last_name_two nullable.
     */
    public function scopeSearchByFullName(
        Builder $query,
        string $name,
        string $lastNameOne,
        ?string $lastNameTwo,
        string $birthDate
    ): Builder {
        return $query
            ->where('name', $name)
            ->where('last_name_one', $lastNameOne)
            ->where(function (Builder $q) use ($lastNameTwo) {
                $lastNameTwo
                    ? $q->where('last_name_two', $lastNameTwo)
                    : $q->whereNull('last_name_two');
            })
            ->where('birth_date', $birthDate);
    }
}
