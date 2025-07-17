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
    <!-- Scripts -->
    {{-- @vite(['resources/js/app.js']) --}}
    @livewireStyles
    <style>
        .contentImagePrincipalContainer {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            max-height: 100vh;
            z-index: 0;
        }

        .contentImagePrincipalContainer img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            filter: opacity(1);
        }

        /* Mejoras para Choices.js */
        .choices {
            margin-bottom: 0;
        }

        .choices__inner {
            min-height: 38px;
            padding: 7px 7.5px 3.75px;
            border: 1px solid #ced4da;
            border-radius: 0.375rem;
            background-color: #fff;
            font-size: 1rem;
        }

        .choices__inner:focus {
            border-color: #86b7fe;
            outline: 0;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }

        .choices__list--dropdown {
            z-index: 1050; /* Para que aparezca sobre modales */
            border: 1px solid #ced4da;
            border-radius: 0.375rem;
        }

        .choices__item--selectable {
            padding: 12px;
        }

        .choices__item--selectable:hover {
            background-color: #0d6efd;
            color: white;
        }

        .choices__placeholder {
            color: #6c757d;
        }

        .choices.is-invalid .choices__inner {
            border-color: #dc3545;
        }

        .choices.is-invalid .choices__inner:focus {
            border-color: #dc3545;
            box-shadow: 0 0 0 0.25rem rgba(220, 53, 69, 0.25);
        }

        /* Asegurar que los floating labels funcionen con Choices.js */
        .form-floating > .choices {
            height: calc(3.5rem + 2px);
            padding: 0;
        }

        .form-floating > .choices .choices__inner {
            height: calc(3.5rem + 2px);
            padding: 1rem 0.75rem;
            border-radius: 0.375rem;
        }

        .form-floating > label {
            z-index: 2;
        }
    </style>
</head>

<body>
    <div id="app" style="background:#ffffff;">
        <nav class="navbar fixed-top navbar-expand-md navbar-light shadow-sm text-light"
            style="background:#444444d8;backdrop-filter:blur(5px);">
            <div class="container" style="height:60px;">
                <div class="col-md-4 text-center" style='width:15%'>
                    <img src="{{ Storage::url('iconos/logo.png') }}" alt="Image"
                        style="width:100%;height:100px;object-fit:contain">
                </div>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                    data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                    aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ms-auto">
                        <!-- Authentication Links -->
                        @guest
                            @if (Route::has('login'))
                                <li class="nav-item">
                                    <a class="nav-link text-light" href="{{ route('login') }}">{{ __('Login') }}</a>
                                </li>
                            @endif

                            @if (Route::has('register'))
                                <li class="nav-item">
                                    <a class="nav-link text-light" href="{{ route('register') }}">{{ __('Register') }}</a>
                                </li>
                            @endif
                        @else
                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button"
                                    data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    {{ Auth::user()->name }}
                                </a>

                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                        onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                        {{ __('Logout') }}
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
            <img class="" src="{{ Storage::url('fondos/002-Cotopaxi.jpg') }}" alt="">
        </div>
        <main class="p-0 principalContentLogin m-0">
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
