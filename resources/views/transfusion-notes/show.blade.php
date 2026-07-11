@extends('layouts.app')

@section('content')
<div class="container py-4" style="max-width:900px;">

    <div class="d-flex align-items-center justify-content-between mb-4">
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('transfusionNotes.index', $note->stay) }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Volver
            </a>
            <div>
                <h2 class="mb-0">
                    <i class="bi bi-droplet-half me-2" style="color:#E91E63;"></i>
                    Nota Transfusional
                </h2>
                <p class="text-muted mb-0 small">
                    Paciente: <strong>{{ $note->stay->patient->fullName() }}</strong>
                    &middot; Cuarto {{ $note->stay->room->number ?? '—' }}
                    @if($note->start_datetime)
                        &middot; {{ $note->start_datetime->format('d/m/Y H:i') }}
                        @if($note->end_datetime) — {{ $note->end_datetime->format('H:i') }} @endif
                    @endif
                </p>
            </div>
        </div>
        <div class="d-flex gap-2">
            @if($canEdit)
                <a href="{{ route('transfusionNotes.edit', $note) }}" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-pencil"></i> Editar
                </a>
            @endif
            <a href="{{ route('transfusionNotes.pdf', $note) }}" target="_blank" class="btn btn-sm btn-outline-danger">
                <i class="bi bi-file-earmark-pdf"></i> PDF
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Encabezado informativo --}}
    <div class="card mb-3 border-0 shadow-sm">
        <div class="card-body small">
            <div class="row g-2">
                <div class="col-md-6">
                    <strong>Médico tratante:</strong>
                    Dr(a). {{ $note->attendingDoctor?->fullName() ?? '—' }}
                </div>
                <div class="col-md-6">
                    <strong>Registrada por:</strong> {{ $note->createdBy?->fullName() ?? '—' }}
                    @if($note->updatedBy && $note->updatedBy->id !== $note->createdBy?->id)
                        &middot; Última edición: {{ $note->updatedBy->fullName() }}
                    @endif
                </div>
                @if($note->transfusionChecklist)
                    <div class="col-12">
                        <strong>Lista de verificación vinculada:</strong>
                        Folio {{ $note->transfusionChecklist->folio ?? $note->transfusion_checklist_id }}
                        — Finalizada {{ $note->transfusionChecklist->finalized_at->format('d/m/Y H:i') }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Signos vitales PREVIOS --}}
    @php
        $preVitals = array_filter([
            'TA' => $note->pre_ta,
            'FC' => $note->pre_fc,
            'FR' => $note->pre_fr,
            'TEMP' => $note->pre_temp,
            'SpO2' => $note->pre_spo2,
        ]);
    @endphp
    @if(!empty($preVitals))
    <div class="card mb-3 border-0 shadow-sm">
        <div class="card-header" style="background:#fce4ec; color:#c2185b; font-weight:600;">
            Signos vitales previos a la transfusión
        </div>
        <div class="card-body">
            <div class="row g-2 text-center">
                @foreach($preVitals as $label => $value)
                    <div class="col">
                        <div class="border rounded p-2">
                            <small class="text-muted d-block">{{ $label }}</small>
                            <strong>{{ $value }}</strong>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    {{-- Secciones narrativas --}}
    @foreach($sections as $key => $config)
        @php $content = $note->{$key}; @endphp
        @if(!empty(trim($content ?? '')))
            <div class="card mb-3 border-0 shadow-sm">
                <div class="card-header" style="background:#fce4ec; color:#c2185b; font-weight:600;">
                    {{ $config['label'] }}
                </div>
                <div class="card-body" style="white-space:pre-wrap;">{{ $content }}</div>
            </div>
        @endif
    @endforeach

    {{-- Signos vitales POSTERIORES --}}
    @php
        $postVitals = array_filter([
            'TA' => $note->post_ta,
            'FC' => $note->post_fc,
            'FR' => $note->post_fr,
            'TEMP' => $note->post_temp,
            'SpO2' => $note->post_spo2,
        ]);
    @endphp
    @if(!empty($postVitals))
    <div class="card mb-3 border-0 shadow-sm">
        <div class="card-header" style="background:#fce4ec; color:#c2185b; font-weight:600;">
            Signos vitales posteriores a la transfusión
        </div>
        <div class="card-body">
            <div class="row g-2 text-center">
                @foreach($postVitals as $label => $value)
                    <div class="col">
                        <div class="border rounded p-2">
                            <small class="text-muted d-block">{{ $label }}</small>
                            <strong>{{ $value }}</strong>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    @if($note->filledSectionsCount() === 0 && empty($preVitals) && empty($postVitals))
        <div class="text-center text-muted py-4">
            <em>Esta nota no tiene contenido registrado aún.</em>
        </div>
    @endif

    <p class="text-muted small text-end mt-3">
        Creada: {{ $note->created_at->format('d/m/Y H:i') }}
        @if($note->updated_at->ne($note->created_at))
            &middot; Actualizada: {{ $note->updated_at->format('d/m/Y H:i') }}
        @endif
    </p>
</div>
@endsection
