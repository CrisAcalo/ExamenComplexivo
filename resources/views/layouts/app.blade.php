<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>
        @hasSection('title')
            @yield('title') |
        @endif {{ config('app.name', 'Laravel') }}
    </title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <!-- Choices.js CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js@10.2.0/public/assets/styles/choices.min.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="icon" href="{{ Storage::url('logos/ITIN_LOGO_SMALL.png') }}" type="image/x-icon">
    <!-- Scripts -->
    @livewireStyles
    <style>
        /* Estilos globales para el sistema */
        body {
            font-family: 'Nunito', sans-serif;
            background: #f8f9fa;
        }

        .contentImagePrincipalContainer {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            /* max-height: calc(100vh - 70px); */
            z-index: 0;
        }

        .contentImagePrincipalContainer img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            filter: brightness(0.85) contrast(1.1);
        }

        /* Estilos mejorados para la navegación */
        .navbar {
            background: linear-gradient(135deg, rgba(68, 68, 68, 0.356) 0%, rgba(255, 255, 255, 0.534) 100%) !important;
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            height: 70px;
        }

        .navbar-brand {
            font-weight: 700;
            color: #fff !important;
        }

        .nav-link {
            font-weight: 500;
            transition: all 0.3s ease;
            border-radius: 0.5rem;
            margin: 0 0.25rem;
            padding: 0.5rem 1rem !important;
        }

        .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
            transform: translateY(-1px);
        }

        .dropdown-menu {
            border: none;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
            border-radius: 0.75rem;
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.95);
        }

        .dropdown-item {
            border-radius: 0.5rem;
            margin: 0.25rem;
            transition: all 0.2s ease;
        }

        .dropdown-item:hover {
            background-color: #f8f9fa;
            transform: translateX(5px);
        }

        /* Mejoras generales para formularios */
        .form-control, .form-select {
            border-radius: 0.5rem;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
            border-color: #86b7fe;
        }

        .btn {
            border-radius: 0.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn:hover {
            transform: translateY(-2px);
        }

        .btn:active {
            transform: translateY(0);
        }

        /* Cards mejorados */
        .card {
            border-radius: 1rem;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.08);
            border: none;
            transition: all 0.3s ease;
        }

        .card:hover {
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.12);
        }

        /* Animaciones sutiles */
        .fade-in {
            animation: fadeIn 0.6s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>

<body>
    <div id="app" style="background:#ffffff;">
        <nav class="navbar sticky-top navbar-expand-md navbar-light shadow-sm mb-4">
            <div class="container" style="height:70px;">
                <!-- Logo y Brand -->
                <div class="navbar-brand d-flex align-items-center">
                    {{-- <img src="{{ Storage::url('logos/LOGO-ESPE_500.png') }}" alt="ESPE Logo"
                        style="width:150px;object-fit:contain;margin-right:12px;"> --}}
                    <div class="d-none d-md-block">
                        <div class="fw-bold text-white" style="font-size: 1.1rem;">Sistema Examen Complexivo</div>
                        <small class="text-light opacity-75">Universidad de las Fuerzas Armadas ESPE</small>
                    </div>
                </div>

                <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse"
                    data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                    aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <i class="bi bi-list text-white fs-4"></i>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ms-auto">
                        <!-- Authentication Links -->
                        @guest
                            @if (Route::has('login'))
                                <li class="nav-item">
                                    <a class="nav-link text-light d-flex align-items-center" href="{{ route('login') }}">
                                        <i class="bi bi-box-arrow-in-right me-2"></i>
                                        {{ __('Iniciar Sesión') }}
                                    </a>
                                </li>
                            @endif

                            @if (Route::has('register'))
                                <li class="nav-item">
                                    <a class="nav-link text-light d-flex align-items-center" href="{{ route('register') }}">
                                        <i class="bi bi-person-plus me-2"></i>
                                        {{ __('Registrarse') }}
                                    </a>
                                </li>
                            @endif
                        @else
                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle text-light d-flex align-items-center"
                                   href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    <i class="bi bi-person-circle me-2 fs-5"></i>
                                    <span class="fw-semibold">{{ Auth::user()->name }}</span>
                                </a>

                                <div class="dropdown-menu dropdown-menu-end mt-2" aria-labelledby="navbarDropdown">
                                    <div class="dropdown-header px-3 py-2">
                                        <small class="text-muted">Sesión activa</small>
                                    </div>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item d-flex align-items-center" href="{{ route('logout') }}"
                                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                        <i class="bi bi-box-arrow-right me-2 text-danger"></i>
                                        {{ __('Cerrar Sesión') }}
                                    </a>

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                                </div>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>
        <div class="contentImagePrincipalContainer">
            <img class="" src="{{ Storage::url('fondos/002-Cotopaxi.jpg') }}" alt="Fondo Ecuador">
        </div>
        <main class="p-0 m-0 fade-in">
            {{-- Contenido Principal --}}
            @yield('content')
        </main>
    </div>
    @livewireScripts
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Choices.js JS -->
    <script src="https://cdn.jsdelivr.net/npm/choices.js@10.2.0/public/assets/scripts/choices.min.js"></script>
    <script type="module">
        const addModal = new bootstrap.Modal('#createDataModal');
        const editModal = new bootstrap.Modal('#updateDataModal');
        window.addEventListener('closeModal', () => {
            addModal.hide();
            editModal.hide();
        })
    </script>
</body>

</html>
