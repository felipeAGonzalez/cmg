@extends('layouts.app')

@php
    $user      = auth()->user();
    $canManage = ($user->isAdmin() || $user->isNurse()) && $stay->isActive();
@endphp

@section('content')
<div class="container py-4">

    {{-- ════════ ENCABEZADO ════════ --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body d-flex flex-wrap justify-content-between align-items-start gap-2">
            <div>
                <h4 class="fw-bold mb-1"><i class="bi bi-journal-text text-primary me-2"></i>Notas y registros</h4>
                <div>
                    <span class="fw-semibold">{{ $stay->patient->fullName() }}</span>
                    <span class="text-muted">· {{ $stay->patient->age() }} años · Cuarto {{ $stay->room->number }}</span>
                </div>
            </div>
            <div class="d-flex gap-2">
                @if($canManage)
                    <a href="{{ route('nursingEntries.create', $stay) }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-circle me-1"></i>Nuevo registro
                    </a>
                @endif
                <a href="{{ route('nursingSheets.index', $stay) }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i>Volver a Hojas de Enfermería
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))<div class="alert alert-success border-0 shadow-sm">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger border-0 shadow-sm">{{ session('error') }}</div>@endif

    {{-- ════════ FILTROS POR CATEGORÍA ════════ --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body py-2">
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('nursingEntries.index', $stay) }}"
                   class="btn btn-sm {{ $categoryFilter ? 'btn-outline-secondary' : 'btn-secondary' }}">
                    Todas ({{ $totalCount }})
                </a>
                @foreach($categories as $key => $cfg)
                    <a href="{{ route('nursingEntries.index', ['stay' => $stay, 'category' => $key]) }}"
                       class="btn btn-sm {{ $categoryFilter === $key ? 'btn-primary' : 'btn-outline-primary' }}">
                        <i class="bi {{ $cfg['icon'] }} me-1"></i>{{ $cfg['label'] }} ({{ $countsByCategory[$key] ?? 0 }})
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ════════ TABLA ════════ --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            @if($entries->isEmpty())
                <p class="text-muted fst-italic mb-0">
                    @if($categoryFilter)
                        No hay registros en la categoría «{{ $categories[$categoryFilter]['label'] }}».
                    @else
                        Este paciente aún no tiene notas ni registros de enfermería.
                    @endif
                </p>
            @else
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Hora</th>
                                <th>Turno</th>
                                <th>Categoría</th>
                                <th>Descripción</th>
                                <th>Enfermera</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($entries as $entry)
                            <tr>
                                <td class="text-nowrap small">{{ $entry->recorded_at->format('d/m/Y H:i') }}</td>
                                <td class="text-nowrap small text-muted">{{ $entry->shiftLabel() }}</td>
                                <td>
                                    <span class="badge {{ $entry->categoryBadgeClass() }}">
                                        <i class="bi {{ $entry->categoryIcon() }} me-1"></i>{{ $entry->categoryLabel() }}
                                    </span>
                                </td>
                                <td style="white-space:pre-wrap;">{{ $entry->description }}</td>
                                <td class="text-muted small">{{ $entry->recordedBy?->fullName() ?? '—' }}</td>
                                <td class="text-end text-nowrap">
                                    @if($entry->isEditable() && ($user->isAdmin() || $user->isNurse()))
                                        <a href="{{ route('nursingEntries.edit', $entry) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form method="POST" action="{{ route('nursingEntries.destroy', $entry) }}"
                                              class="d-inline" onsubmit="return confirm('¿Eliminar este registro?');">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                        </form>
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $entries->links() }}
                </div>
            @endif
        </div>
    </div>

</div>{{-- /container --}}
@endsection
