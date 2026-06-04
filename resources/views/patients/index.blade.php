@extends('layouts.app')

@section('content')
<div class="container py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">
            <i class="bi bi-people me-2"></i>Pacientes
        </h4>
        <a href="{{ route('patients.create') }}" class="btn btn-success btn-sm shadow-sm">
            <i class="bi bi-plus-circle me-1"></i>Nuevo paciente
        </a>
    </div>

    {{-- Búsqueda --}}
    <form method="GET" action="{{ route('patients.index') }}" class="mb-4">
        <div class="input-group shadow-sm">
            <span class="input-group-text bg-white border-end-0">
                <i class="bi bi-search text-muted"></i>
            </span>
            <input type="text" name="search" class="form-control border-start-0"
                   placeholder="Buscar por nombre o apellido"
                   value="{{ $search ?? '' }}">
            <button type="submit" class="btn btn-primary">Buscar</button>
            @if($search)
                <a href="{{ route('patients.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-x-lg"></i> Limpiar
                </a>
            @endif
        </div>
    </form>

    <div class="card border-0 shadow-sm">
        @if($patients->isEmpty())
            <div class="card-body text-center py-5 text-muted">
                <i class="bi bi-person-x" style="font-size:2.5rem;"></i>
                <p class="mt-2 mb-0">No se encontraron pacientes.</p>
            </div>
        @else
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Nombre completo</th>
                        <th>Edad</th>
                        <th>Género</th>
                        <th>Estado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($patients as $patient)
                    <tr>
                        <td class="fw-semibold">{{ $patient->fullName() }}</td>
                        <td>{{ $patient->age() }} años</td>
                        <td>{{ $patient->gender === 'M' ? 'Masculino' : 'Femenino' }}</td>
                        <td>
                            @if($patient->currentStay)
                                <span class="badge bg-danger">Hospitalizado</span>
                            @else
                                <span class="badge bg-success">Disponible</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="d-flex justify-content-end gap-1">
                                <a href="{{ route('patients.show', $patient) }}"
                                   class="btn btn-outline-primary btn-sm" title="Ver expediente">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('patients.edit', $patient) }}"
                                   class="btn btn-outline-secondary btn-sm" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                @if(! $patient->currentStay)
                                <button type="button"
                                        class="btn btn-outline-danger btn-sm"
                                        data-bs-toggle="modal"
                                        data-bs-target="#deletePatient{{ $patient->id }}"
                                        title="Eliminar">
                                    <i class="bi bi-trash"></i>
                                </button>
                                @else
                                <button type="button" class="btn btn-outline-danger btn-sm" disabled title="Paciente hospitalizado">
                                    <i class="bi bi-trash"></i>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>

                    {{-- Modal eliminar --}}
                    @if(! $patient->currentStay)
                    <div class="modal fade" id="deletePatient{{ $patient->id }}" tabindex="-1">
                        <div class="modal-dialog modal-sm">
                            <div class="modal-content shadow">
                                <div class="modal-header border-0 pb-0">
                                    <h6 class="modal-title fw-bold text-danger">
                                        <i class="bi bi-exclamation-triangle me-1"></i>Eliminar paciente
                                    </h6>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body py-2">
                                    ¿Eliminar el expediente de <strong>{{ $patient->fullName() }}</strong>?
                                </div>
                                <div class="modal-footer border-0 pt-0">
                                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                                    <form method="POST" action="{{ route('patients.destroy', $patient) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-transparent">
            {{ $patients->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
