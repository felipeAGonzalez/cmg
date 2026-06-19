@extends('layouts.app')

@section('content')
<div class="container py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2><i class="bi bi-clipboard-pulse"></i> Hoja de Triage</h2>
            <p class="text-muted mb-0">
                Paciente: <strong>{{ $patient->fullName() }}</strong>
                @if($patient->birth_date)
                    &middot; {{ $patient->birth_date->age }} a&ntilde;os
                    &middot; Nacimiento: {{ $patient->birth_date->format('d/m/Y') }}
                @endif
            </p>
        </div>
        <div class="text-end">
            <button type="button" id="btn-mark-normal" class="btn btn-outline-success btn-sm">
                <i class="bi bi-check-all"></i> Marcar todo como Ausente/Normal
            </button>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <strong>Hay errores en el formulario.</strong> Rev&iacute;salos antes de continuar.
        </div>
    @endif

    <form method="POST" action="{{ route('triage.store') }}" id="triage-form">
        @csrf
        <input type="hidden" name="patient_id" value="{{ $patient->id }}">

        @include('triage._form', ['triage' => null, 'now' => $now])

        <div class="d-flex justify-content-between mb-5">
            <a href="{{ url()->previous() }}" class="btn btn-secondary">
                <i class="bi bi-x"></i> Cancelar
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-circle"></i> Guardar hoja de triage
            </button>
        </div>
    </form>
</div>

@include('triage._scripts')
@endsection
