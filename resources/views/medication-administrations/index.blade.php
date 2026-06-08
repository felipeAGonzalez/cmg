@extends('layouts.app')

@php
    $user   = auth()->user();
    $canAct = $user->isAdmin() || $user->isNurse();
@endphp

@section('content')
<div class="container py-4">

    {{-- Encabezado --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body d-flex flex-wrap justify-content-between align-items-start gap-2">
            <div>
                <h4 class="fw-bold mb-1"><i class="bi bi-clipboard-check text-primary me-2"></i>Administraciones</h4>
                <div>
                    <span class="fw-semibold">{{ $stay->patient->fullName() }}</span>
                    <span class="text-muted">· Cuarto {{ $stay->room->number }}</span>
                </div>
            </div>
            <div class="d-flex gap-2">
                @if($canAct && $stay->isActive())
                    <a href="{{ route('medicationAdministrations.create', $stay) }}" class="btn btn-success btn-sm">
                        <i class="bi bi-check-circle me-1"></i>Registrar administración
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

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            @if($administrations->isEmpty())
                <p class="text-muted fst-italic mb-0">Sin administraciones registradas.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Hora</th>
                                <th>Medicamento</th>
                                <th>Dosis admin.</th>
                                <th class="d-none d-md-table-cell">Dosis prescrita</th>
                                <th>Estado</th>
                                <th>Motivo</th>
                                <th>Enfermera</th>
                                @if($canAct)<th class="text-end">Acciones</th>@endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($administrations as $a)
                            <tr>
                                <td class="text-nowrap small">{{ $a->administered_at->format('d/m/Y H:i') }}</td>
                                <td>{{ $a->medicationOrder->medication_name }}</td>
                                <td>{{ $a->actual_dose }}</td>
                                <td class="d-none d-md-table-cell text-muted small">{{ $a->medicationOrder->dose }}</td>
                                <td><span class="badge {{ $a->statusBadgeClass() }}">{{ $a->statusLabel() }}</span></td>
                                <td class="small" style="max-width:220px;">
                                    @if($a->reason)
                                        <span title="{{ $a->reason }}">{{ \Illuminate\Support\Str::limit($a->reason, 40) }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-muted small">{{ $a->recordedBy?->fullName() ?? '—' }}</td>
                                @if($canAct)
                                <td class="text-end text-nowrap">
                                    @if($a->isEditable())
                                        <a href="{{ route('medicationAdministrations.edit', $a) }}"
                                           class="btn btn-sm btn-outline-secondary py-0 px-1" title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-danger py-0 px-1 btn-delete-adm"
                                                data-action="{{ route('medicationAdministrations.destroy', $a) }}" title="Eliminar">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>
                                @endif
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">{{ $administrations->links() }}</div>
            @endif
        </div>
    </div>

</div>

@if($canAct)
<form method="POST" id="deleteAdmForm" class="d-none">
    @csrf
    @method('DELETE')
</form>
@endif
@endsection

@push('scripts')
@if($canAct)
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('deleteAdmForm');
    document.querySelectorAll('.btn-delete-adm').forEach(function (btn) {
        btn.addEventListener('click', function () {
            if (confirm('¿Eliminar esta administración? Esta acción no se puede deshacer.')) {
                form.action = btn.dataset.action;
                form.submit();
            }
        });
    });
});
</script>
@endif
@endpush
