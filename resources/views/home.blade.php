@extends('layouts.panel')
@section('title', __('Dashboard'))
@section('content')
    <div class="container-fluid">
        <!-- Header de Bienvenida -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card bg-primary text-white">
                    <div class="card-body p-4">
                        <div class="row align-items-center">
                            <div class="col-md-2 text-center">
                                @if (file_exists(public_path('storage/logos/LOGO-ESPE_500.png')))
                                    <img src="{{ asset('storage/logos/LOGO-ESPE_500.png') }}" alt="Logo ESPE"
                                         class="img-fluid" style="width: 250px; filter: brightness(0) invert(1);">
                                @else
                                    <div class="bg-white bg-opacity-25 rounded p-4">
                                        <i class="bi bi-building fs-1"></i>
                                    </div>
                                @endif
                            </div>
                            <div class="col-md-10">
                                <h1 class="h2 mb-2">Sistema de Gestión de Exámenes Complexivos</h1>
                                <h2 class="h4 mb-3 opacity-75">Universidad de las Fuerzas Armadas ESPE</h2>
                                <p class="mb-0 fs-5">
                                    Bienvenido al sistema integral para la administración y gestión de tribunales de exámenes complexivos.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Estadísticas Rápidas -->
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-bar-chart me-2"></i>Estadísticas del Sistema
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-md-4 mb-3">
                                <div class="border-end">
                                    <div class="display-4 text-primary fw-bold">{{ \App\Models\Tribunale::count() }}</div>
                                    <p class="text-muted mb-0">Tribunales Registrados</p>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="border-end">
                                    <div class="display-4 text-success fw-bold">{{ \App\Models\Tribunale::where('estado', 'ABIERTO')->count() }}</div>
                                    <p class="text-muted mb-0">Tribunales Activos</p>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="display-4 text-info fw-bold">{{ \App\Models\Tribunale::where('estado', 'CERRADO')->count() }}</div>
                                <p class="text-muted mb-0">Tribunales Finalizados</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
