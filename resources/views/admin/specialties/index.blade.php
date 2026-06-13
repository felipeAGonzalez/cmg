@extends('layouts.app')

@section('content')
<div class="container py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">
            <i class="bi bi-mortarboard me-2"></i>Especialidades
        </h4>
        <a href="{{ route('specialties.create') }}" class="btn btn-success btn-sm shadow-sm">
            <i class="bi bi-plus-circle me-1"></i>Nueva especialidad
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger border-0 shadow-sm">{{ session('error') }}</div>
    @endif

    <div class="card border-0 shadow-sm">
        @if($specialties->isEmpty())
            <div class="card-body text-center py-5 text-muted">
                <i class="bi bi-mortarboard" style="font-size:2.5rem;"></i>
                <p class="mt-2 mb-0">Aún no hay especialidades registradas.</p>
            </div>
        @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Nombre</th>
                        <th>Estado</th>
                        <th>Médicos asignados</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($specialties as $specialty)
                    <tr>
                        <td class="fw-semibold">{{ $specialty->name }}</td>
                        <td>
                            @if($specialty->is_active)
                                <span class="badge bg-success">Activa</span>
                            @else
                                <span class="badge bg-secondary">Inactiva</span>
                            @endif
                        </td>
                        <td>{{ $specialty->users_count }}</td>
                        <td class="text-end">
                            <div class="d-flex justify-content-end gap-1">
                                <a href="{{ route('specialties.edit', $specialty) }}"
                                   class="btn btn-outline-secondary btn-sm" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form method="POST" action="{{ route('specialties.toggle', $specialty) }}">
                                    @csrf
                                    @if($specialty->is_active)
                                        <button type="submit" class="btn btn-outline-warning btn-sm" title="Desactivar">
                                            <i class="bi bi-pause-circle"></i> Desactivar
                                        </button>
                                    @else
                                        <button type="submit" class="btn btn-outline-success btn-sm" title="Activar">
                                            <i class="bi bi-play-circle"></i> Activar
                                        </button>
                                    @endif
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

    <p class="text-muted small mt-3">
        <i class="bi bi-info-circle me-1"></i>
        Las especialidades no se eliminan para preservar el historial. Una especialidad
        desactivada deja de aparecer al asignar médicos, pero se conserva en los registros existentes.
    </p>
</div>
@endsection
