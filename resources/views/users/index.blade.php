@extends('layouts.app')

@section('content')
<div class="container py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">
            <i class="bi bi-person-gear me-2"></i>Usuarios del sistema
        </h4>
        <a href="{{ route('users.create') }}" class="btn btn-success btn-sm shadow-sm">
            <i class="bi bi-plus-circle me-1"></i>Nuevo usuario
        </a>
    </div>

    {{-- Búsqueda --}}
    <form method="GET" action="{{ route('users.index') }}" class="mb-4">
        <div class="input-group shadow-sm">
            <span class="input-group-text bg-white border-end-0">
                <i class="bi bi-search text-muted"></i>
            </span>
            <input type="text" name="search" class="form-control border-start-0"
                   placeholder="Buscar por nombre o correo"
                   value="{{ $search ?? '' }}">
            <button type="submit" class="btn btn-primary">Buscar</button>
            @if($search)
                <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-x-lg"></i> Limpiar
                </a>
            @endif
        </div>
    </form>

    <div class="card border-0 shadow-sm">
        @if($users->isEmpty())
            <div class="card-body text-center py-5 text-muted">
                <i class="bi bi-person-x" style="font-size:2.5rem;"></i>
                <p class="mt-2 mb-0">No se encontraron usuarios.</p>
            </div>
        @else
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Nombre</th>
                        <th>Correo</th>
                        <th>Rol</th>
                        <th>Céd. prof.</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                    <tr>
                        <td class="fw-semibold">{{ $user->fullName() }}</td>
                        <td class="text-muted">{{ $user->email }}</td>
                        <td>
                            @php
                                $roleLabel = match($user->role) {
                                    'root'   => 'Soporte / Root',
                                    'admin'  => 'Administrador',
                                    'doctor' => 'Médico',
                                    'nurse'  => 'Enfermero/a',
                                    default  => ucfirst($user->role),
                                };
                                $roleBadge = match($user->role) {
                                    'root'   => 'bg-dark',
                                    'admin'  => 'bg-primary',
                                    'doctor' => 'bg-info text-dark',
                                    'nurse'  => 'bg-success',
                                    default  => 'bg-secondary',
                                };
                            @endphp
                            <span class="badge {{ $roleBadge }}">{{ $roleLabel }}</span>
                            @if($user->isDoctor() && $user->specialtiesLabel())
                                <span class="text-muted small">
                                    ({{ $user->specialtiesLabel() }})
                                </span>
                            @endif
                        </td>
                        <td class="text-muted small">{{ $user->professional_license ?? '—' }}</td>
                        <td class="text-end">
                            <div class="d-flex justify-content-end gap-1">
                                <a href="{{ route('users.edit', $user) }}"
                                   class="btn btn-outline-secondary btn-sm" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                @if($user->id !== auth()->id())
                                <button type="button"
                                        class="btn btn-outline-danger btn-sm"
                                        data-bs-toggle="modal"
                                        data-bs-target="#deleteUser{{ $user->id }}"
                                        title="Dar de baja">
                                    <i class="bi bi-trash"></i>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>

                    {{-- Modal eliminar --}}
                    @if($user->id !== auth()->id())
                    <div class="modal fade" id="deleteUser{{ $user->id }}" tabindex="-1">
                        <div class="modal-dialog modal-sm">
                            <div class="modal-content shadow">
                                <div class="modal-header border-0 pb-0">
                                    <h6 class="modal-title fw-bold text-danger">
                                        <i class="bi bi-exclamation-triangle me-1"></i>Dar de baja
                                    </h6>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body py-2">
                                    ¿Dar de baja a <strong>{{ $user->fullName() }}</strong>?
                                    El usuario no podrá iniciar sesión.
                                </div>
                                <div class="modal-footer border-0 pt-0">
                                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                                    <form method="POST" action="{{ route('users.destroy', $user) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">Dar de baja</button>
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
            {{ $users->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
