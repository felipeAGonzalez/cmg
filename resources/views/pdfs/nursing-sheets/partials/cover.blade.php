@php
    use Carbon\Carbon;

    $genderLabel = match ($patient->gender) {
        'male', 'm', 'masculino'   => 'Masculino',
        'female', 'f', 'femenino'  => 'Femenino',
        default                    => $patient->gender ?? '—',
    };

    $admission = $stay->admission_date ? Carbon::parse($stay->admission_date) : null;
    $discharge = $stay->discharge_date ? Carbon::parse($stay->discharge_date) : null;

    $height = $stay->height_cm ? rtrim(rtrim(number_format($stay->height_cm, 2), '0'), '.') . ' cm' : '—';
    $weight = $stay->weight_kg ? rtrim(rtrim(number_format($stay->weight_kg, 2), '0'), '.') . ' kg' : '—';
@endphp

<table class="kv">
    <tr>
        <td class="label">Paciente</td>
        <td><strong>{{ $patient->fullName() }}</strong></td>
        <td class="label">Edad</td>
        <td>{{ $patient->age() }} años</td>
    </tr>
    <tr>
        <td class="label">Género</td>
        <td>{{ $genderLabel }}</td>
        <td class="label">Fecha de nacimiento</td>
        <td>{{ $patient->birth_date ? $patient->birth_date->format('d/m/Y') : '—' }}</td>
    </tr>
    <tr>
        <td class="label">Cuarto</td>
        <td>{{ $stay->room->number }}</td>
        <td class="label">Talla / Peso</td>
        <td>{{ $height }} · {{ $weight }}</td>
    </tr>
    <tr>
        <td class="label">Fecha de ingreso</td>
        <td>{{ $admission ? $admission->format('d/m/Y H:i') : '—' }}</td>
        <td class="label">Fecha de egreso</td>
        <td>
            @if($discharge)
                {{ $discharge->format('d/m/Y H:i') }}
                @if($stay->dischargeReasonLabel())
                    <br><span style="font-size:8px; color:#555;">Motivo: {{ $stay->dischargeReasonLabel() }}</span>
                @endif
            @else
                — (hospitalizado)
            @endif
        </td>
    </tr>
    <tr>
        <td class="label">Diagnóstico inicial</td>
        <td colspan="3">{{ $stay->diagnosis ?: '—' }}</td>
    </tr>
    <tr>
        <td class="label">Médicos asignados</td>
        <td colspan="3">
            @forelse($stay->currentDoctors as $sd)
                Dr(a). {{ $sd->doctor?->fullName() ?? '—' }}@if($sd->doctor?->specialtiesLabel()) — {{ $sd->doctor->specialtiesLabel() }}@endif @if($sd->doctor?->professional_license)(Céd. {{ $sd->doctor->professional_license }})@endif{{ ! $loop->last ? '; ' : '' }}
            @empty
                <span class="muted">Sin médicos asignados.</span>
            @endforelse
        </td>
    </tr>
    <tr>
        <td class="label">Periodo del expediente</td>
        <td colspan="3">
            {{ $admission ? $admission->format('d/m/Y') : '—' }}
            &mdash;
            {{ $discharge ? $discharge->format('d/m/Y') : now()->format('d/m/Y') . ' (a la fecha)' }}
        </td>
    </tr>
</table>

<div class="gen-note" style="margin-top:6px;">
    Documento generado el {{ $generatedAt->format('d/m/Y H:i') }} por {{ $generatedBy->fullName() }}.
</div>

@if(! empty($chartImage))
    <div style="margin-top:14px; text-align:center; page-break-inside:avoid;">
        <img src="{{ $chartImage }}" alt="Gráfica de signos vitales"
             style="display:block; width:100%; margin:0 auto;">
        <p style="font-size:8px; color:#666; margin:4px 0 0; text-align:center;">
            Periodo del gr&aacute;fico: {{ $admissionDate->format('d/m/Y H:i') }} &mdash; {{ $endDate->format('d/m/Y H:i') }}
        </p>
    </div>
@endif
