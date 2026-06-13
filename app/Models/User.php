<?php

namespace App\Models;

use App\Models\StayDoctor;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'last_name_one',
        'last_name_two',
        'email',
        'password',
        'role',
        'must_change_password',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at'   => 'datetime',
            'password'            => 'hashed',
            'must_change_password' => 'boolean',
            'is_active'           => 'boolean',
        ];
    }

    public function isRoot(): bool
    {
        return $this->role === 'root';
    }

    public function isAdmin(): bool
    {
        // root tiene acceso a todo lo que admin tiene
        return $this->role === 'admin' || $this->role === 'root';
    }

    public function isDoctor(): bool
    {
        return $this->role === 'doctor';
    }

    public function isNurse(): bool
    {
        return $this->role === 'nurse';
    }

    public function fullName(): string
    {
        return trim("{$this->name} {$this->last_name_one} {$this->last_name_two}");
    }

    public function stayDoctors(): HasMany
    {
        return $this->hasMany(StayDoctor::class, 'doctor_id');
    }

    public function specialties(): BelongsToMany
    {
        return $this->belongsToMany(Specialty::class, 'user_specialty')
            ->withTimestamps();
    }

    /**
     * Devuelve las especialidades del médico como string separado por " · ".
     * Si no tiene ninguna, devuelve null.
     */
    public function specialtiesLabel(): ?string
    {
        if (! $this->relationLoaded('specialties')) {
            $this->load('specialties');
        }

        $names = $this->specialties->pluck('name')->all();

        return empty($names) ? null : implode(' · ', $names);
    }
}
