@extends('layouts.app')

@section('content')
<div class="container py-4">

    {{-- Encabezado --}}
    <div class="text-center mb-4">
        <h1 class="fw-bold mb-1" style="font-size:2rem;">Disponibilidad de Camas</h1>
        <div style="width:70px;height:4px;background:#1976D2;border-radius:2px;margin:0 auto 8px;"></div>
        <p class="text-muted mb-0">Centro Médico Guadalupano</p>
    </div>

    {{-- Estadísticas --}}
    <div class="row g-3 mb-4">
        <div class="col-4">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="text-muted small mb-1">Total de cuartos</div>
                <div class="fw-bold fs-4">{{ $total }}</div>
            </div>
        </div>
        <div class="col-4">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="text-muted small mb-1">Disponibles</div>
                <div class="fw-bold fs-4 text-success">{{ $available }}</div>
            </div>
        </div>
        <div class="col-4">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="text-muted small mb-1">Ocupados</div>
                <div class="fw-bold fs-4 text-danger">{{ $occupied }}</div>
            </div>
        </div>
    </div>

    {{-- Búsqueda --}}
    <form method="GET" action="{{ route('rooms.index') }}" class="mb-4">
        <div class="input-group shadow-sm">
            <span class="input-group-text bg-white border-end-0">
                <i class="bi bi-search text-muted"></i>
            </span>
            <input type="text" name="search" class="form-control border-start-0"
                   placeholder="Buscar por número de cuarto o nombre del paciente"
                   value="{{ $search ?? '' }}">
            <button type="submit" class="btn btn-primary px-4">Buscar</button>
            @if($search)
                <a href="{{ route('rooms.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-x-lg"></i> Limpiar
                </a>
            @endif
        </div>
    </form>

    {{-- Botón agregar cuarto (solo admin) --}}
    @if(Auth::user()->isAdmin())
    <div class="d-flex justify-content-end mb-3">
        <a href="{{ route('rooms.create') }}" class="btn btn-success btn-sm shadow-sm">
            <i class="bi bi-plus-circle me-1"></i>Agregar cuarto
        </a>
    </div>
    @endif

    {{-- Grid de cuartos --}}
    <div class="row g-3">
        @forelse($displayRooms as $room)
            @php $isAvailable = $room->isAvailable(); @endphp

            <div class="col-6 col-sm-4 col-md-3">
                {{-- Card clickeable vía JS para permitir botones internos --}}
                <div class="room-card h-100 text-center p-3 shadow-sm"
                     style="background:{{ $isAvailable ? '#E8F5E9' : '#FFEBEE' }}; border-radius:14px; cursor:pointer;"
                     onclick="window.location='{{ $isAvailable ? route('stays.create', $room) : route('stays.show', $room) }}'">

                    <i class="bi bi-hospital fs-1" style="color:#1976D2;"></i>
                    <div class="fw-bold mt-2 mb-1">Cuarto {{ $room->number }}</div>

                    @if($isAvailable)
                        <div class="fw-bold small" style="color:#2E7D32;">Disponible</div>
                    @else
                        <div class="fw-bold small" style="color:#C62828;">Ocupado</div>
                        <div class="text-muted mt-1" style="font-size:.78rem; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                            {{ $room->currentStay->patient->fullName() }}
                        </div>
                    @endif

                    @if(Auth::user()->isAdmin())
                    <div class="mt-2 d-flex justify-content-center gap-1" onclick="event.stopPropagation()">
                        <a href="{{ route('rooms.edit', $room) }}"
                           class="btn btn-outline-secondary btn-sm py-0 px-2"
                           title="Editar">
                            <i class="bi bi-pencil"></i>
                        </a>
                        @if($isAvailable)
                        <button type="button"
                                class="btn btn-outline-danger btn-sm py-0 px-2"
                                data-bs-toggle="modal"
                                data-bs-target="#deleteRoom{{ $room->id }}"
                                title="Eliminar">
                            <i class="bi bi-trash"></i>
                        </button>
                        @else
                        <button type="button" class="btn btn-outline-danger btn-sm py-0 px-2" disabled title="Cuarto ocupado">
                            <i class="bi bi-trash"></i>
                        </button>
                        @endif
                    </div>
                    @endif
                </div>
            </div>

            {{-- Modal de confirmación de eliminación --}}
            @if(Auth::user()->isAdmin() && $isAvailable)
            <div class="modal fade" id="deleteRoom{{ $room->id }}" tabindex="-1">
                <div class="modal-dialog modal-sm">
                    <div class="modal-content shadow">
                        <div class="modal-header border-0 pb-0">
                            <h6 class="modal-title fw-bold text-danger">
                                <i class="bi bi-exclamation-triangle me-1"></i>Eliminar cuarto
                            </h6>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body py-2">
                            ¿Eliminar el <strong>Cuarto {{ $room->number }}</strong>? Esta acción no se puede deshacer.
                        </div>
                        <div class="modal-footer border-0 pt-0">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                            <form method="POST" action="{{ route('rooms.destroy', $room) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            @endif

        @empty
            <div class="col-12">
                <div class="alert alert-info text-center border-0 shadow-sm">
                    <i class="bi bi-info-circle me-1"></i>
                    No se encontraron cuartos con el criterio de búsqueda.
                </div>
            </div>
        @endforelse
    </div>
</div>
@endsection

@push('styles')
<style>
.room-card {
    transition: transform .2s ease, box-shadow .2s ease;
}
.room-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 24px rgba(0,0,0,.12) !important;
}
</style>
@endpush
