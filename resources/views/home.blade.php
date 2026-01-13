@extends('layouts.panel')
@section('title', __('Dashboard'))
@section('content')
    <div class="container-fluid">
        <!-- Banner Verde ESPE -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #3d8e72ff 0%, #3da66aff 100%);">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                @if (file_exists(public_path('storage/logos/LOGO-ESPE_500.png')))
                                    <img src="{{ asset('storage/logos/LOGO-ESPE_500.png') }}" alt="Logo ESPE"
                                         style="width: 80px; height: 80px; object-fit: contain;" class="me-3">
                                @else
                                    <div class="bg-white bg-opacity-25 rounded p-3 me-3">
                                        <i class="bi bi-building fs-2 text-white"></i>
                                    </div>
                                @endif
                                <div>
                                    <h1 class="h3 mb-1 fw-bold text-white" style="text-shadow: 2px 2px 4px rgba(0,0,0,0.3);">
                                        SISTEMA DE GESTIÓN DE EXÁMENES COMPLEXIVOS
                                    </h1>
                                    <p class="mb-0 text-white" style="text-shadow: 1px 1px 2px rgba(0,0,0,0.2);">
                                        Gestión integral de tribunales y evaluaciones
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mensaje de seguridad: Cambio de contraseña -->
        @if(!session('password_change_reminder_dismissed'))
            <div class="row mb-3">
                <div class="col-12">
                    <div class="alert alert-info alert-dismissible fade show d-flex align-items-center shadow-sm" role="alert">
                        <i class="bi bi-shield-lock fs-4 me-3"></i>
                        <div class="flex-grow-1">
                            <strong><i class="bi bi-info-circle me-1"></i>Recomendación de seguridad:</strong>
                            Por tu seguridad, te recomendamos cambiar tu contraseña periódicamente.
                            <a href="{{ route('mi.perfil') }}" class="alert-link fw-bold">Ir a mi perfil</a>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"
                                onclick="fetch('{{ route('dismiss.password.reminder') }}', {method: 'POST', headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'}})">
                        </button>
                    </div>
                </div>
            </div>
        @endif

        <!-- Estadísticas Rápidas -->
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-header border-0 py-3" style="background-color: #f8f9fa;">
                        <h5 class="card-title mb-0 fw-bold" style="color: #2d7a5f;">
                            <i class="bi bi-bar-chart me-2"></i>Estadísticas del Sistema
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row text-center g-4">
                            <div class="col-md-4">
                                <div class="p-4 rounded" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
                                    <div class="display-4 fw-bold mb-2" style="color: #3498db;">
                                        {{ \App\Models\Tribunale::count() }}
                                    </div>
                                    <p class="text-muted mb-0 fw-semibold">Tribunales Registrados</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-4 rounded" style="background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);">
                                    <div class="display-4 fw-bold mb-2" style="color: #28a745;">
                                        {{ \App\Models\Tribunale::where('estado', 'ABIERTO')->count() }}
                                    </div>
                                    <p class="text-muted mb-0 fw-semibold">Tribunales Activos</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-4 rounded" style="background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%);">
                                    <div class="display-4 fw-bold mb-2" style="color: #17a2b8;">
                                        {{ \App\Models\Tribunale::where('estado', 'CERRADO')->count() }}
                                    </div>
                                    <p class="text-muted mb-0 fw-semibold">Tribunales Finalizados</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
