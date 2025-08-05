@extends('layouts.app')

@section('content')
    <div class="container-fluid p-0 m-0">
        <div class="row justify-content-center align-items-center p-0 m-0 w-100">
            <div class="col-md-4 w-100">
                <div class="card shadow-lg border-0 m-0 mx-auto text-dark"
                    style="max-width:450px;width:90%;backdrop-filter: blur(10px);background:rgba(255, 255, 255, 0.63);">
                    <!-- Header con logo y título -->
                    <div class="card-header bg-transparent border-0 text-center py-4">
                        <div class="mb-3">
                            <img src="{{ Storage::url('logos/LOGO-ESPE_500.png') }}" alt="ESPE Logo"
                                style="width:80%;object-fit:contain;">
                        </div>
                        <h2 class="fw-bold text-primary mb-0">Sistema Examen Complexivo</h2>
                        <p class="text-muted mb-0">Ingresa a tu cuenta</p>
                    </div>

                    <div class="card-body px-4 pb-4">
                        <form method="POST" action="{{ route('login') }}">
                            @csrf

                            <!-- Campo Email -->
                            <div class="mb-4">
                                <div class="form-floating">
                                    <input id="email" type="email"
                                        class="form-control @error('email') is-invalid @enderror" name="email"
                                        value="{{ old('email') }}" required autocomplete="email" autofocus
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
                                        required autocomplete="current-password" placeholder="Contraseña">
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
                            </div>

                            <!-- Recordar sesión -->
                            <div class="mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="remember" id="remember"
                                        {{ old('remember') ? 'checked' : '' }}>
                                    <label class="form-check-label text-muted" for="remember">
                                        {{ __('Mantener sesión iniciada') }}
                                    </label>
                                </div>
                            </div>

                            <!-- Botones -->
                            <div class="d-grid gap-2 mb-3">
                                <button type="submit" class="btn btn-primary btn-lg py-3 login-button">
                                    <i class="bi bi-box-arrow-in-right me-2"></i>
                                    {{ __('Iniciar Sesión') }}
                                </button>
                            </div>

                            <!-- Enlace recuperar contraseña -->
                            @if (Route::has('password.request'))
                                <div class="text-center">
                                    <a class="text-decoration-none text-muted" href="{{ route('password.request') }}">
                                        <i class="bi bi-question-circle me-1"></i>
                                        {{ __('¿Olvidaste tu contraseña?') }}
                                    </a>
                                </div>
                            @endif
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Estilos específicos para el login */
        .login-button {
            background: linear-gradient(135deg, #0d6efd 0%, #0056b3 100%);
            border: none;
            font-weight: 600;
            transition: all 0.3s ease;
            border-radius: 0.5rem;
        }

        .login-button:hover {
            background: linear-gradient(135deg, #0056b3 0%, #004085 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(13, 110, 253, 0.3);
        }

        .login-button:active {
            transform: translateY(0);
        }

        .form-floating>label {
            color: #6c757d;
            font-weight: 500;
        }

        .form-control:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
        }

        .card {
            border-radius: 1rem;
            overflow: hidden;
        }

        .form-floating>.form-control:focus~label,
        .form-floating>.form-control:not(:placeholder-shown)~label {
            color: #0d6efd;
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

        /* Mejorar el input de checkbox */
        .form-check-input:checked {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }
    </style>
@endsection
