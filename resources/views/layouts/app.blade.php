<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'CMG') }}</title>

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
        crossorigin="anonymous"
    >
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >

    <style>
        body { background-color: #F5F7FA; }
        .card { border-radius: 14px; }
        .navbar-brand { font-weight: 600; letter-spacing: .5px; }
    </style>

    @stack('styles')
</head>
<body>

@auth
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
    <div class="container">
        <a class="navbar-brand" href="{{ route('home') }}">
            <i class="bi bi-hospital-fill me-1"></i>
            {{ config('app.name', 'CMG') }}
        </a>

        <button class="navbar-toggler" type="button"
                data-bs-toggle="collapse" data-bs-target="#mainNav"
                aria-controls="mainNav" aria-expanded="false">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">

                @if(Auth::user()->isAdmin() || Auth::user()->isNurse())
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('rooms.index') ? 'active fw-semibold' : '' }}"
                       href="{{ route('rooms.index') }}">
                        <i class="bi bi-grid-3x3-gap me-1"></i>Tablero
                    </a>
                </li>
                @endif

                @if(Auth::user()->isAdmin())
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('patients.*') ? 'active fw-semibold' : '' }}"
                       href="{{ route('patients.index') }}">
                        <i class="bi bi-people me-1"></i>Pacientes
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('rooms.create', 'rooms.edit') ? 'active fw-semibold' : '' }}"
                       href="{{ route('rooms.index') }}">
                        <i class="bi bi-door-open me-1"></i>Cuartos
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('users.*') ? 'active fw-semibold' : '' }}"
                       href="{{ route('users.index') }}">
                        <i class="bi bi-person-gear me-1"></i>Usuarios
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('specialties.*') ? 'active fw-semibold' : '' }}"
                       href="{{ route('specialties.index') }}">
                        <i class="bi bi-mortarboard me-1"></i>Especialidades
                    </a>
                </li>
                @endif

                @if(Auth::user()->isDoctor())
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('doctor.*') ? 'active fw-semibold' : '' }}"
                       href="{{ route('doctor.myPatients') }}">
                        <i class="bi bi-clipboard2-pulse me-1"></i>Mis Pacientes
                    </a>
                </li>
                @endif

            </ul>

            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center gap-1" href="#"
                       role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-person-circle fs-5"></i>
                        <span>{{ Auth::user()->name }} {{ Auth::user()->last_name_one }}</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                        <li>
                            <span class="dropdown-item-text text-muted small">
                                {{ Auth::user()->email }}
                            </span>
                        </li>
                        <li><hr class="dropdown-divider my-1"></li>
                        <li>
                            <a class="dropdown-item" href="{{ route('password.change') }}">
                                <i class="bi bi-key me-1"></i>Cambiar contraseña
                            </a>
                        </li>
                        <li><hr class="dropdown-divider my-1"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger">
                                    <i class="bi bi-box-arrow-right me-1"></i>Cerrar sesión
                                </button>
                            </form>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
@endauth

<main>
    <div class="container">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show mt-3 shadow-sm" role="alert">
                <i class="bi bi-check-circle me-1"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show mt-3 shadow-sm" role="alert">
                <i class="bi bi-exclamation-triangle me-1"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('warning'))
            <div class="alert alert-warning alert-dismissible fade show mt-3 shadow-sm" role="alert">
                <i class="bi bi-info-circle me-1"></i>{{ session('warning') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
    </div>

    @yield('content')
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

@stack('scripts')
</body>
</html>
