@extends('layouts.app')

@section('content')
<div class="container py-4">

    <div class="mb-4">
        <h4 class="fw-bold mb-1">
            <i class="bi bi-clipboard2-pulse me-2"></i>Mis Pacientes
        </h4>
        <p class="text-muted mb-0">Pacientes actualmente bajo tu atención</p>
    </div>

    @if($stays->isEmpty())
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <i class="bi bi-person-x text-muted" style="font-size:3rem;"></i>
                <p class="mt-3 text-muted mb-0">
                    Actualmente no tienes pacientes asignados.<br>
                    Cuando el administrador te asigne pacientes, aparecerán aquí.
                </p>
            </div>
        </div>
    @else
        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Cuarto</th>
                            <th>Paciente</th>
                            <th>Diagnóstico</th>
                            <th>Fecha de ingreso</th>
                            <th>Mi especialidad</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($stays as $stay)
                        <tr style="cursor:pointer;"
                            onclick="window.location='{{ route('doctor.patientDetail', $stay) }}'">
                            <td class="fw-semibold">Cuarto {{ $stay->room->number }}</td>
                            <td>{{ $stay->patient->fullName() }}</td>
                            <td class="text-muted">{{ Str::limit($stay->diagnosis, 80) }}</td>
                            <td>{{ $stay->admission_date->format('d/m/Y H:i') }}</td>
                            <td>
                                @if($stay->currentDoctors->isNotEmpty())
                                    {{ \App\Enums\DoctorSpecialty::from($stay->currentDoctors->first()->specialty)->label() }}
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
@endsection
