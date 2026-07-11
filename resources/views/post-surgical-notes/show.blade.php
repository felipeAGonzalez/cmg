@extends('layouts.app')

@section('content')
<div class="container py-4" style="max-width:900px;">

    <div class="d-flex align-items-center justify-content-between mb-4">
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('postSurgicalNotes.index', $note->stay) }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Volver
            </a>
            <div>
                <h2 class="mb-0">
                    <i class="bi bi-scissors me-2" style="color:#E91E63;"></i>Nota Postquirúrgica
                </h2>
                <p class="text-muted mb-0 small">
                    Paciente: <strong>{{ $note->stay->patient->fullName() }}</strong>
                    &middot; Cuarto {{ $note->stay->room->number ?? '—' }}
                    @if($note->surgery_date)
                        &middot; {{ $note->surgery_date->format('d/m/Y') }}
                        @if($note->surgery_time) {{ \Illuminate\Support\Str::of($note->surgery_time)->substr(0, 5) }} @endif
                    @endif
                </p>
            </div>
        </div>
        <div class="d-flex gap-2">
            @if($canEdit)
                <a href="{{ route('postSurgicalNotes.edit', $note) }}" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-pencil"></i> Editar
                </a>
            @endif
            <a href="{{ route('postSurgicalNotes.pdf', $note) }}" target="_blank" class="btn btn-sm btn-outline-danger">
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

    {{-- Encabezado: datos de la cirugía --}}
    <div class="card mb-3 border-0 shadow-sm">
        <div class="card-body small">
            <div class="row g-2">
                <div class="col-md-4">
                    <strong>Fecha:</strong>
                    {{ $note->surgery_date?->format('d/m/Y') ?? '—' }}
                </div>
                <div class="col-md-4">
                    <strong>Hora:</strong>
                    {{ $note->surgery_time ? \Illuminate\Support\Str::of($note->surgery_time)->substr(0, 5) : '—' }}
                </div>
                <div class="col-md-4">
                    <strong>Tipo:</strong>
                    @if($note->surgery_type)
                        <span class="badge {{ $note->surgery_type === 'urgencia' ? 'bg-danger' : 'bg-info text-dark' }}">
                            {{ ucfirst($note->surgery_type) }}
                        </span>
                    @else
                        —
                    @endif
                </div>
                <div class="col-md-4">
                    <strong>Tiempo quirúrgico:</strong> {{ $note->surgical_time ?? '—' }}
                </div>
                <div class="col-md-4">
                    <strong>Sangrado:</strong> {{ $note->bleeding ?? '—' }}
                </div>
                <div class="col-md-4">
                    <strong>Tiempo de isquemia:</strong> {{ $note->ischemia_time ?? 'No aplica' }}
                </div>
                <div class="col-md-6">
                    <strong>Recuento de textiles:</strong>
                    @if($note->textile_count)
                        {{ ucfirst($note->textile_count) }}
                        @if($note->textile_count_detail) — {{ $note->textile_count_detail }} @endif
                    @else
                        —
                    @endif
                </div>
                <div class="col-md-6">
                    <strong>Médico tratante:</strong>
                    Dr(a). {{ $note->attendingDoctor?->fullName() ?? '—' }}
                </div>
            </div>
        </div>
    </div>

    {{-- Equipo quirúrgico --}}
    <div class="card mb-3 border-0 shadow-sm">
        <div class="card-header" style="background:#fce4ec; color:#c2185b; font-weight:600;">
            Equipo quirúrgico
        </div>
        <div class="card-body small">
            <div class="row g-2">
                <div class="col-md-4">
                    <strong>Cirujano:</strong><br>{{ $note->surgeonName() }}
                </div>
                <div class="col-md-4">
                    <strong>Ayudante/Instrumentista:</strong><br>{{ $note->assistantName() }}
                </div>
                <div class="col-md-4">
                    <strong>Anestesiólogo:</strong><br>{{ $note->anesthesiologistName() }}
                </div>
            </div>
        </div>
    </div>

    {{-- Secciones narrativas (sin técnica quirúrgica) --}}
    @foreach(array_diff_key($sections, ['surgical_technique' => true]) as $key => $config)
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

    {{-- Técnica quirúrgica al final --}}
    @if(!empty(trim($note->surgical_technique ?? '')))
        <div class="card mb-3 border-0 shadow-sm">
            <div class="card-header" style="background:#fce4ec; color:#c2185b; font-weight:600;">
                {{ $sections['surgical_technique']['label'] }}
            </div>
            <div class="card-body" style="white-space:pre-wrap;">{{ $note->surgical_technique }}</div>
        </div>
    @endif

    @if($note->filledSectionsCount() === 0)
        <div class="text-center text-muted py-4">
            <em>Esta nota no tiene contenido registrado aún.</em>
        </div>
    @endif

    <p class="text-muted small text-end mt-3">
        Creada por {{ $note->createdBy?->fullName() ?? '—' }} el {{ $note->created_at->format('d/m/Y H:i') }}
        @if($note->updated_at->ne($note->created_at))
            &middot; Actualizada: {{ $note->updated_at->format('d/m/Y H:i') }}
        @endif
    </p>
</div>
@endsection
