@extends('layouts.app')

@section('content')
    <div class="container-fluid p-0 m-0">
        <div class="row justify-content-center align-items-center vh-100 p-0 m-0 w-100">
            <div class="col-md-5 w-100">
                <div class="card shadow-lg border-0 m-0 mx-auto text-dark"
                    style="max-width:500px;width:90%;backdrop-filter: blur(10px);background:rgba(255, 255, 255, 0.63);">
                    <!-- Header con logo y título -->
                    <div class="card-header bg-transparent border-0 text-center py-4">
                        <div class="mb-3">
                            <img src="{{ Storage::url('logos/LOGO-ESPE_500.png') }}" alt="ESPE Logo"
                                style="width:80px;height:80px;object-fit:contain;">
                        </div>
                        <h2 class="fw-bold text-primary mb-0">Crear Cuenta</h2>
                        <p class="text-muted mb-0">Registra una nueva cuenta en el sistema</p>
                    </div>

                    <div class="card-body px-4 pb-4">
                        <form method="POST" action="{{ route('register') }}">
                            @csrf

                            <!-- Campo Nombre -->
                            <div class="mb-4">
                                <div class="form-floating">
                                    <input id="name" type="text"
                                        class="form-control @error('name') is-invalid @enderror" name="name"
                                        value="{{ old('name') }}" required autocomplete="name" autofocus
                                        placeholder="Nombre completo">
                                    <label for="name">
                                        <i class="bi bi-person me-2"></i>{{ __('Nombre Completo') }}
                                    </label>
                                </div>
                                @error('name')
                                    <div class="invalid-feedback d-block">
                                        <i class="bi bi-exclamation-circle me-1"></i>
                                        <strong>{{ $message }}</strong>
                                    </div>
                                @enderror
                            </div>

                            <!-- Campo Email -->
                            <div class="mb-4">
                                <div class="form-floating">
                                    <input id="email" type="email"
                                        class="form-control @error('email') is-invalid @enderror" name="email"
                                        value="{{ old('email') }}" required autocomplete="email"
                                        placeholder="correo@ejemplo.com">
                                    <label for="email">
                                        <i class="bi bi-envelope me-2"></i>{{ __('Correo Electrónico') }}
                                    </label>
                                </div>
                                @error('email')
                                    <div class="invalid-feedback d-block">
                                        <i class="bi bi-exclamation-circle me-1"></i>
                                        <strong>{{ $message }}</strong>
                                    </div>
                                @enderror
                            </div>

                            <!-- Campo Contraseña -->
                            <div class="mb-4">
                                <div class="form-floating">
                                    <input id="password" type="password"
                                        class="form-control @error('password') is-invalid @enderror" name="password"
                                        required autocomplete="new-password" placeholder="Contraseña">
                                    <label for="password">
                                        <i class="bi bi-lock me-2"></i>{{ __('Contraseña') }}
                                    </label>
                                </div>
                                @error('password')
                                    <div class="invalid-feedback d-block">
                                        <i class="bi bi-exclamation-circle me-1"></i>
                                        <strong>{{ $message }}</strong>
                                    </div>
                                @enderror
                                <div class="form-text text-muted mt-2">
                                    <small><i class="bi bi-info-circle me-1"></i>Mínimo 8 caracteres</small>
                                </div>
                            </div>

                            <!-- Campo Confirmar Contraseña -->
                            <div class="mb-4">
                                <div class="form-floating">
                                    <input id="password-confirm" type="password" class="form-control"
                                        name="password_confirmation" required autocomplete="new-password"
                                        placeholder="Confirmar contraseña">
                                    <label for="password-confirm">
                                        <i class="bi bi-shield-check me-2"></i>{{ __('Confirmar Contraseña') }}
                                    </label>
                                </div>
                            </div>

                            <!-- Botones -->
                            <div class="d-grid gap-2 mb-3">
                                <button type="submit" class="btn btn-success btn-lg py-3 register-button">
                                    <i class="bi bi-person-plus me-2"></i>
                                    {{ __('Crear Cuenta') }}
                                </button>
                            </div>

                            <!-- Enlace al login -->
                            <div class="text-center">
                                <p class="text-muted mb-0">
                                    ¿Ya tienes una cuenta?
                                    <a class="text-decoration-none fw-semibold" href="{{ route('login') }}">
                                        <i class="bi bi-box-arrow-in-right me-1"></i>
                                        Iniciar Sesión
                                    </a>
                                </p>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Estilos específicos para el registro */
        .register-button {
            background: linear-gradient(135deg, #198754 0%, #146c43 100%);
            border: none;
            font-weight: 600;
            transition: all 0.3s ease;
            border-radius: 0.5rem;
        }

        .register-button:hover {
            background: linear-gradient(135deg, #146c43 0%, #0f5132 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(25, 135, 84, 0.3);
        }

        .register-button:active {
            transform: translateY(0);
        }

        .form-floating>label {
            color: #6c757d;
            font-weight: 500;
        }

        .form-control:focus {
            border-color: #198754;
            box-shadow: 0 0 0 0.25rem rgba(25, 135, 84, 0.15);
        }

        .card {
            border-radius: 1rem;
            overflow: hidden;
        }

        .form-floating>.form-control:focus~label,
        .form-floating>.form-control:not(:placeholder-shown)~label {
            color: #198754;
        }

        /* Animación sutil para el card */
        .card {
            animation: slideUp 0.6s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Estilos para enlaces */
        a {
            color: #0d6efd;
            transition: color 0.2s ease;
        }

        a:hover {
            color: #0056b3;
        }

        /* Mejorar el texto de ayuda */
        .form-text {
            font-size: 0.875rem;
        }
    </style>
@endsection
