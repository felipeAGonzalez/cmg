@extends('pdfs.layouts.base')

@section('document-title', 'Hoja Frontal')

@section('content')
    @php
        $service      = $formData['service'] ?? null;
        $serviceLabel = $service === 'other'
            ? ($formData['service_other'] ?? '—')
            : (config('services_catalog')[$service] ?? '—');
        $maritalLabel = config('marital_statuses')[$formData['marital_status'] ?? ''] ?? '—';
        $stateLabel   = config('mexican_states')[$formData['state'] ?? ''] ?? '';
        $cityState    = trim(($formData['city'] ?? '') . ($stateLabel ? ', ' . $stateLabel : ''), ', ') ?: '—';
    @endphp

    {{-- Datos del paciente --}}
    <table class="data-table" style="border: 1px solid #000; margin-bottom: 8px;">
        <tr>
            <td style="width: 22%; border: 1px solid #000;" class="field-label">Nombre del paciente</td>
            <td colspan="3" style="border: 1px solid #000;">
                <table style="width:100%;">
                    <tr>
                        <td style="text-align:center;">{{ $patient->last_name_one }}</td>
                        @if($patient->last_name_two)
                        <td style="text-align:center;">{{ $patient->last_name_two }}</td>
                        @endif
                        <td style="text-align:center;">{{ $patient->name }}</td>
                    </tr>
                    <tr>
                        <td style="text-align:center; font-size: 8px; color:#555;">apellido paterno</td>
                        @if($patient->last_name_two)
                        <td style="text-align:center; font-size: 8px; color:#555;">apellido materno</td>
                        @endif
                        <td style="text-align:center; font-size: 8px; color:#555;">nombre(s)</td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td style="border: 1px solid #000;" class="field-label">Fecha de nacimiento</td>
            <td style="border: 1px solid #000;">{{ \Carbon\Carbon::parse($patient->birth_date)->format('d/m/Y') }}</td>
            <td style="border: 1px solid #000; width: 22%;" class="field-label">Fecha de ingreso</td>
            <td style="border: 1px solid #000;">{{ \Carbon\Carbon::parse($stay->admission_date)->format('d/m/Y H:i') }}</td>
        </tr>
        <tr>
            <td style="border: 1px solid #000;" class="field-label">Sexo</td>
            <td style="border: 1px solid #000;">{{ $patient->gender === 'M' ? 'Masculino' : 'Femenino' }}</td>
            <td style="border: 1px solid #000;" class="field-label">Fecha de egreso</td>
            <td style="border: 1px solid #000;">
                {{ $stay->discharge_date
                    ? \Carbon\Carbon::parse($stay->discharge_date)->format('d/m/Y H:i')
                    : '—' }}
            </td>
        </tr>
        <tr>
            <td style="border: 1px solid #000;" class="field-label">Servicio</td>
            <td style="border: 1px solid #000;">{{ $serviceLabel }}</td>
            <td style="border: 1px solid #000;" class="field-label">Ocupación</td>
            <td style="border: 1px solid #000;">{{ $formData['occupation'] ?? '—' }}</td>
        </tr>
        <tr>
            <td style="border: 1px solid #000;" class="field-label">Estado civil</td>
            <td style="border: 1px solid #000;">{{ $maritalLabel }}</td>
            <td style="border: 1px solid #000;" class="field-label">Ciudad y estado</td>
            <td style="border: 1px solid #000;">{{ $cityState }}</td>
        </tr>
        <tr>
            <td style="border: 1px solid #000;" class="field-label">Domicilio</td>
            <td colspan="3" style="border: 1px solid #000;">{{ $formData['address'] ?? '—' }}</td>
        </tr>
        <tr>
            <td style="border: 1px solid #000;" class="field-label">Teléfono</td>
            <td colspan="3" style="border: 1px solid #000;">{{ $formData['phone'] ?? '—' }}</td>
        </tr>
    </table>

    {{-- Diagnóstico de ingreso (en vivo) --}}
    <div class="section-title">Diagnóstico de ingreso</div>
    <div class="text-block">{{ $stay->diagnosis }}</div>

    {{-- Diagnósticos finales --}}
    <div class="section-title">Diagnósticos finales</div>
    <div class="text-block">{{ $formData['final_diagnoses'] ?? '' }}</div>

    {{-- Médicos tratantes (en vivo) --}}
    <div class="section-title">Médicos tratantes</div>
    <table class="data-table">
        @forelse($stay->currentDoctors as $stayDoctor)
            <tr>
                <td style="border: 1px solid #000; width: 60%;">
                    {{ $stayDoctor->doctor?->fullName() ?? '—' }}
                </td>
                <td style="border: 1px solid #000; width: 40%;">
                    {{ \App\Enums\DoctorSpecialty::tryFrom($stayDoctor->specialty)?->label() ?? $stayDoctor->specialty }}
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="2" style="border: 1px solid #000; text-align: center; color: #777;">
                    Sin médicos asignados.
                </td>
            </tr>
        @endforelse
    </table>
@endsection
