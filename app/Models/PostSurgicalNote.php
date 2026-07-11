<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostSurgicalNote extends Model
{
    protected $fillable = [
        'stay_id', 'surgery_date', 'surgery_time', 'surgery_type',
        'preop_diagnosis', 'postop_diagnosis', 'planned_surgery', 'performed_surgery',
        'surgical_time', 'complications', 'bleeding',
        'textile_count', 'textile_count_detail', 'ischemia_time',
        'patient_status_at_exit', 'prognosis',
        'surgeon_user_id', 'surgeon_other_name',
        'assistant_user_id', 'assistant_other_name',
        'anesthesiologist_user_id', 'anesthesiologist_other_name',
        'surgical_technique',
        'attending_doctor_id', 'created_by_id', 'updated_by_id',
    ];

    protected $casts = [
        'surgery_date' => 'date',
    ];

    public function stay()
    {
        return $this->belongsTo(Stay::class);
    }

    public function surgeonUser()
    {
        return $this->belongsTo(User::class, 'surgeon_user_id');
    }

    public function assistantUser()
    {
        return $this->belongsTo(User::class, 'assistant_user_id');
    }

    public function anesthesiologistUser()
    {
        return $this->belongsTo(User::class, 'anesthesiologist_user_id');
    }

    public function attendingDoctor()
    {
        return $this->belongsTo(User::class, 'attending_doctor_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by_id');
    }

    public function sections(): array
    {
        return [
            'preop_diagnosis'        => $this->preop_diagnosis,
            'postop_diagnosis'       => $this->postop_diagnosis,
            'planned_surgery'        => $this->planned_surgery,
            'performed_surgery'      => $this->performed_surgery,
            'complications'          => $this->complications,
            'bleeding'               => $this->bleeding,
            'patient_status_at_exit' => $this->patient_status_at_exit,
            'prognosis'              => $this->prognosis,
            'surgical_technique'     => $this->surgical_technique,
        ];
    }

    public function filledSectionsCount(): int
    {
        return collect($this->sections())
            ->filter(fn($c) => !empty(trim($c ?? '')))
            ->count();
    }

    public function surgeonName(): string
    {
        if ($this->surgeon_user_id && $this->surgeonUser) {
            return 'Dr(a). ' . $this->surgeonUser->name . ' ' . ($this->surgeonUser->last_name_one ?? '');
        }
        return $this->surgeon_other_name ?? '—';
    }

    public function assistantName(): string
    {
        if ($this->assistant_user_id && $this->assistantUser) {
            return 'Dr(a). ' . $this->assistantUser->name . ' ' . ($this->assistantUser->last_name_one ?? '');
        }
        return $this->assistant_other_name ?? '—';
    }

    public function anesthesiologistName(): string
    {
        if ($this->anesthesiologist_user_id && $this->anesthesiologistUser) {
            return 'Dr(a). ' . $this->anesthesiologistUser->name . ' ' . ($this->anesthesiologistUser->last_name_one ?? '');
        }
        return $this->anesthesiologist_other_name ?? '—';
    }

    public function effectiveSignatureBlock(): string
    {
        $doctor = $this->attendingDoctor;
        if (!$doctor) return '';

        $parts = [];
        $parts[] = trim('Dr(a). ' . $doctor->name . ' ' . ($doctor->last_name_one ?? '') . ' ' . ($doctor->last_name_two ?? ''));

        if (method_exists($doctor, 'specialtiesLabel') && $doctor->specialtiesLabel()) {
            $parts[] = $doctor->specialtiesLabel();
        }
        if (!empty($doctor->professional_license)) {
            $parts[] = 'Céd. Prof. ' . $doctor->professional_license;
        }

        return implode("\n", $parts);
    }
}
