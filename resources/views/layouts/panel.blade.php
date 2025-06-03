<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">


    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">

    @livewireStyles
</head>

<style>
    @import "https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700";

    .welcome-text {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        text-align: center;
    }

    :root {
        --white: #ffffff;
        --black: #000000;
        --dark: #232830;
        --very-light-pink: #c7c7c7;
        --text-input-field: #f7f7f7;
        --hospital-green: #0d6efd;
        --sm: 14px;
        --md: 16px;
        --lg: 18px:
    }

    * {
        /* font-size:15px; */
    }

    p {
        font-size: 1.1em;
        font-weight: 300;
        line-height: 1.7em;
        color: #999;
    }

    .ocultar-en-impresion {
        display: none;
    }

    .link_styled {
        color: #0d6efd;
        cursor: pointer;
    }

    .checkbox_deploy_container {
        position: relative;
        height: 35px;
        border-radius: 5px;
        padding: 5px 0;
        color: #fff;
        background-color: #0d6efd;
        transition: all 0.3s;
    }

    .text_deploy_formUpdateImage {
        position: absolute;
        left: 20px;
    }

    .checkbox_new_image {
        position: absolute;
        top: 10px;
        right: 10px;
        height: 15px;
        width: 15px;
    }

    .formToUploadImage {
        display: none;
    }

    .formToExistingImage {
        display: none;
    }

    #newImageIcon,
    #existingImageIcon {
        transition: all 0.2s linear;
        margin: 0 10px 0 0;
    }

    .exist-images_container {
        margin: auto;
        display: flex;
        flex-wrap: wrap;
        justify-content: space-around;
        max-width: 1100px;
    }

    .exist-images_input-group {
        position: relative;
        width: 200px;
        height: 200px;
        overflow: hidden;
        filter: brightness(70%);
    }

    .exist-images_input-group:hover {
        filter: brightness(100%);
    }

    .exist-images_radio {
        position: absolute;
        bottom: 10px;
        right: 10px;
        width: 20px;
        height: 20px;
    }

    .image-card_toUpload_container {
        width: 200px;
        height: 200px;
    }

    .exist-img_upload {
        min-width: 100%;
        max-height: 100%;
    }

    .cardSliderContainer {
        position: relative;
    }

    .buttonDeleteCardSlide {
        position: absolute;
        border: none;
        outline: none;
        background: #ff0000;
        color: #ffffff;
        padding: 2px 5px;
        /* border-top-right-radius: 5px; */
        border-radius: 100%;
        top: 7px;
        right: 7px;
    }

    .hiddenContainer {
        display: none;
    }

    .image--card_container {
        position: relative;
        color: #ffffff;
        width: 100%
    }

    .image--card_filter {
        position: absolute;
        top: 0;
        opacity: 0;
        filter: blur(0);
        background-color: #00000053;
        height: 100%;
        width: 100%;
        /* max-width: 1500px; */
        transition: all 0.3s ease-out;
    }

    .image--card_container:hover .image--card_filter {
        opacity: 1;
        transition: all 0.3s ease-out;
    }

    .image--card_container:hover .image--card_image {
        filter: blur(5px);
        transition: all 0.3s ease-out;
    }

    .image--card_image {
        border-radius: 5px;
        width: 100%;
        max-height: 500px;
        object-fit: cover;
    }

    .image--card_text {
        position: absolute;
        font-size: 2rem;
        /* left: calc(50% - 234px); */
        top: calc(50% - 24px);
    }

    @keyframes shake {
        0% {
            transform: rotate(-7deg) translateX(0);
        }

        50% {
            transform: rotate(7deg) translateX(0);
        }

        100% {
            transform: rotate(0deg) translateX(0);
        }
    }

    #the-canvas {
        border: 1px solid black;
        direction: ltr;
    }

    .nav-item.list-group.nav-link-item {
        position: relative;
        transition: all 0.3s linear;
    }

    .nav-item.list-group.nav-link-item:hover {
        background-color: #0d6efd;
        transform: translateX(5px);
        transition: all 0.1s linear;
    }

    .nav-item.list-group.nav-link-item:hover .icon-wrapper i {
        animation: shake 0.15s linear infinite;
    }

    /* Estilos adicionales para el icono */
    .nav-item.list-group.nav-link-item .icon-wrapper {
        display: inline-block;
    }

    .nav-item.list-group.nav-link-item .icon-wrapper i {
        display: inline-block;
    }


    .navbar {
        padding: 15px 10px;
        background: #fff;
        border: none;
        border-radius: 0;
        margin-bottom: 40px;
        box-shadow: 1px 1px 3px rgba(0, 0, 0, 0.1);
    }

    .navbar-btn {
        box-shadow: none;
        outline: none !important;
        border: none;
    }

    .line {
        width: 100%;
        height: 1px;
        border-bottom: 1px dashed #ddd;
        margin: 40px 0;
    }

    .superindice {
        color: red;
    }


    .sidebar_container {
        min-width: 280px;
        width: 280px;
    }

    #sidebar {
        overflow-y: scroll;
        scroll-behavior: smooth;
        height: 100vh;
        position: fixed;
    }

    .wrapper {
        display: flex;
        width: 100%;
        align-items: stretch;
    }



    #content {
        width: 100%;
        padding: 20px;
        min-height: 100vh;
        transition: all 0.3s;
        /* margin-left: 280px; */
        /* z-index: 10; */
    }

    .button_sideBar {
        display: none;
        border: none;
        background: #0d6dfd30;
        border-radius: 5px;
        padding: 0 5px;
    }

    .button_sideBar:active {
        border: solid 1px #0d6dfd;
    }

    .button_close_sideBar {
        display: none;
        border: none;
        background: #0d6dfd30;
        border-radius: 5px;
        padding: 0 5px;
    }


    .table-primary {
        width: 100%;
    }

    .table-primary tr th {
        background: var(--hospital-green);
        color: var(--white);
        padding: 5px;
        font-size: 1.2rem;
        border-right: 1px solid var(--very-light-pink);
        max-width: max-content;
    }

    .table-primary tr th:first-child {
        border-top-left-radius: 6px;
    }

    .table-primary tr th:last-child {
        border-top-right-radius: 6px;
    }

    .table-primary tr td {
        padding: 5px;
        /* min-width: 150px; */
        max-width: max-content;
        border-bottom: 1px solid var(--very-light-pink);
    }

    .table-danger {
        width: 100%;
    }

    .table-danger th {
        background-color: #b02a37;
        color: #ffffff;
        border-right: 1px solid #ffffff;
    }

    .table-danger tr {
        border-bottom: 1px solid #b02a37;
    }

    .table-warning {
        width: 100%;
    }

    .table-warning th {
        background-color: #997404;
        color: #ffffff;
        border-right: 1px solid #fff3cd;
    }

    .table-warning tr {
        border-bottom: 1px solid #997404;
    }

    @media (max-width: 850px) {
        #sidebar {
            height: 100%;
            margin-left: -280px;
            transition: all 0.3s;
        }

        .sidebar_container {
            margin-left: -280px;
            transition: all 0.3s;
        }

        .button_sideBar {
            display: block;
        }

        #content {
            transition: all 0.3s;
            margin-left: 0px;
        }
    }

    /* Estilos para la impresión */
    @media print {
        body {
            margin: 0;
            /* Eliminar márgenes */
            padding: 0;
            /* Eliminar relleno */
        }

        #contentToPrint {
            width: 100%;
            /* Ajustar el ancho al tamaño de la página */
            margin: 0 auto;
            /* Centrar el contenido horizontalmente */
            padding: 20px;
            /* Agregar relleno al contenido */
            box-sizing: border-box;
            /* Incluir el relleno en el ancho total */
            font-size: 12px;
            /* Tamaño de fuente para impresión */
            page-break-inside: avoid;
            /* Evitar saltos de página dentro del contenido */
        }

        /* Agregar estilos específicos según sea necesario */
    }

    .container_words {
        position: relative;
        width: 100%;
        height: calc(100vh - 60px);
        overflow: hidden;
    }

    .word {
        position: absolute;
        font-size: 2rem;
        animation: moveWord linear infinite;
        color: #999
    }

    @keyframes moveWord {
        0% {
            transform: translate(0, 0);
        }

        100% {
            transform: translate(100vw, 100vh);
        }
    }

    .big-welcome {
        font-size: 6rem;
        color: #0d6dfd;
        /* Color naranja para el saludo en español */
    }

    .medium-welcome {
        font-size: 2rem;
    }

    .small-welcome {
        font-size: 1.5rem;
    }

    /* Scroll bar */
    ::-webkit-scrollbar {
        width: 4px;
        /* background-color: #F5F5F5; */
    }

    ::-webkit-scrollbar-track {
        border-radius: 0;
    }

    ::-webkit-scrollbar-thumb {
        border-radius: 0;
        background-color: #0d6efd;
    }




    .login-button:hover {
        background-color: #007bff;
        background-image: radial-gradient(at 30% 30%, rgba(255, 255, 255, 0.15), transparent 50%), radial-gradient(at 90% 20%, rgba(0, 0, 0, 0.1), transparent 50%);
        box-shadow: 0 0.25rem 0.5rem rgba(0, 123, 255, 0.25), 0 0.2rem 1rem rgba(0, 123, 255, 0.15);
    }
</style>

<body>

    <div class="wrapper">
        <div id="sidebar_container" class="sidebar_container">
            <div class="d-flex flex-column flex-shrink-0 p-3 text-white bg-dark" style="width: 280px;" id="sidebar">
                <a href="{{ url('/') }}"
                    class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none text-center">

                    <span class="fs-4">Sistema Examen Compexivo</span>

                </a>
                <hr>
                <h5 class="fs-6 text-secondary">Control</h5>

                <ul class="list-group nav nav-pills flex-column mb-auto list-unstyled ps-0">
                    <li class="nav-item list-group nav-link-item">
                        <a href="{{ route('periodos.') }}" class="nav-link text-white">
                            <span class="icon-wrapper">
                                <i class="bi bi-people"></i></span>
                            Periodos
                        </a>
                    </li>
                    <li class="nav-item list-group nav-link-item">
                        <a href="{{ route('carreras.') }}" class="nav-link text-white">
                            <span class="icon-wrapper">
                                <i class="bi bi-people"></i></span>
                            Carreras
                        </a>
                    </li>

                    <li class="nav-item list-group nav-link-item">
                        <a href="{{ route('estudiantes.') }}" class="nav-link text-white">
                            <span class="icon-wrapper">
                                <i class="bi bi-people"></i></span>
                            Estudiantes
                        </a>
                    </li>

                    <li class="nav-item list-group nav-link-item">
                        <a href="{{ route('rubricas.') }}" class="nav-link text-white">
                            <span class="icon-wrapper">
                                <i class="bi bi-people"></i></span>
                            Rubricas
                        </a>
                    </li>


                    <hr>
                    <h5 class="fs-6 text-secondary">Acceso</h5>
                    {{-- @if (Auth::user()->can('Roles - Seccion')) --}}
                    <li class="nav-item list-group nav-link-item">
                        <a href="{{ route('roles.') }}" class="nav-link text-white">
                            <span class="icon-wrapper">
                                <i class="bi bi-list-columns-reverse"></i></span>
                            Roles
                        </a>
                    </li>
                    {{-- @endif --}}

                    {{-- @if (Auth::user()->can('Permisos - Seccion')) --}}
                    <li class="nav-item list-group nav-link-item">
                        <a href="{{ route('permissions.') }}" class="nav-link text-white">
                            <span class="icon-wrapper">
                                <i class="bi bi-list"></i></span>
                            Permisos
                        </a>
                    </li>
                    {{-- @endif --}}

                    <hr>
                    <h5 class="fs-6 text-secondary">Usuarios</h5>
                    {{-- @if (Auth::user()->can('Usuarios - Seccion')) --}}
                    <li class="nav-item list-group nav-link-item">
                        <a href="{{ route('users.') }}" class="nav-link text-white">
                            <span class="icon-wrapper">
                                <i class="bi bi-person-lines-fill"></i></span>
                            Usuarios
                        </a>
                    </li>
                    {{-- @endif --}}

                    @impersonating($guard = null)
                        <li class="nav-item list-group nav-link-item">
                            <a class="nav-link text-white" href="{{ route('users.exitImpersonate') }}">
                                <span class="icon-wrapper">
                                    <i class="bi bi-escape"></i></span>
                                Salir Impersonate
                            </a>
                        </li>
                    @endImpersonating
                </ul>
                <hr>


                <div class="dropdown">
                    <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle"
                        id="dropdownUser1" data-bs-toggle="dropdown" aria-expanded="false">
                        {{-- <img src="https://github.com/mdo.png" alt="" width="32" height="32"
                            class="rounded-circle me-2"> --}}
                        <strong>{{ Auth::user()->name }}</strong>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-dark text-small shadow" aria-labelledby="dropdownUser1">

                        <li>
                            <a class="dropdown-item"
                                href="{{ route('users.profile', encrypt(Auth::id())) }}">Perfil</a>
                        </li>

                        <li>
                            <hr class="dropdown-divider">
                        </li>

                        <li>
                            <a class="dropdown-item" href="{{ route('logout') }}"
                                onclick="event.preventDefault();
                                             document.getElementById('logout-form').submit();">
                                {{ __('Logout') }}
                            </a>

                            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                @csrf
                            </form>
                        </li>

                    </ul>
                </div>
            </div>
        </div>
        <!-- Page Content  -->
        <div id="content">
            <button id="button_sideBar" class="button_sideBar" onclick="showAdminSidebar()"><i class="bi bi-list"
                    style="font-size:2rem"></i></button>
            <button id="button_close_sideBar" class="button_close_sideBar" onclick="closeAdminSidebar()"><i
                    class="bi bi-x-lg" style="font-size:2rem"></i></button>
            <div id="overlay" class="overlay"></div>
            <div class="pt-3">

                @yield('content')

            </div>
        </div>
    </div>
    <script>
        function showAdminSidebar() {
            document.getElementById("sidebar").style = "margin-left: 0px;";
            document.getElementById("button_sideBar").style = "display:none;";
            document.getElementById("button_close_sideBar").style =
                "margin-left:0px;display:block;";
            document.getElementById("sidebar_container").style = "margin-left:0px;";
        }

        function closeAdminSidebar() {
            document.getElementById("sidebar").style = "margin-left: -280px;";
            document.getElementById("button_sideBar").style = "display:block;";
            document.getElementById("button_close_sideBar").style =
                "margin-left:-280px;display:none;";
            document.getElementById("sidebar_container").style = "margin-left:-280px;";
        }
    </script>
    @livewireScripts

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>
    <script>
        const modales = {};

        // Crear instancias de modales fuera del event listener
        document.addEventListener('DOMContentLoaded', function() {
            const modals = document.querySelectorAll('.modal');
            modals.forEach(modal => {
                const modalId = modal.id;
                if (!modales[modalId]) {
                    modales[modalId] = new bootstrap.Modal(modal);
                }
            });
        });

        window.addEventListener('closeModalByName', (event) => {
            const nameModalAEliminar = event.detail.modalName;

            if (modales[nameModalAEliminar]) {
                modales[nameModalAEliminar].hide();
            } else {
                console.log(`El modal '${nameModalAEliminar}' no existe.`);
            }
        });

        window.addEventListener('openModalByName', (event) => {
            const nameModalAEliminar = event.detail.modalName;

            if (modales[nameModalAEliminar]) {
                modales[nameModalAEliminar].show();
            } else {
                console.log(`El modal '${nameModalAEliminar}' no existe.`);
            }
        });

        window.addEventListener('changeBetweenModals', (event) => {
            const nameModalACerrar = event.detail.modalToClose;
            const nameModalAAbrir = event.detail.modalToOpen;

            if (modales[nameModalACerrar]) {
                modales[nameModalACerrar].hide();
            }
            if (modales[nameModalAAbrir]) {
                modales[nameModalAAbrir].show();
            }
        });

        Livewire.on('fileUploadProgress', (progress) => {
            // Maneja el progreso de carga actualizado
            document.getElementById('progressBar').style.width = progress + '%';
            document.getElementById('progressBar').innerHTML = progress + '%';
        });


        window.addEventListener('printContent', (event) => {
            var div = document.querySelector(`#${event.detail.id}`);
            imprimirElemento(div);
        });



        function validarNumericos(inputElement) {
            const valor = inputElement.value;

            if (/^\d*$/.test(valor) && valor != "") {
                inputElement.classList.remove("is-invalid");
                inputElement.classList.add("is-valid");
            } else {
                inputElement.value = valor.replace(/\D/g, ""); // Eliminar caracteres no numéricos
                inputElement.classList.add("is-invalid");
                inputElement.classList.remove("is-valid");
            }
        }

        function printContent() {
            var div = document.querySelector("#contentToPrint");
            imprimirElemento(div);
        }

        function imprimirElemento(elemento) {
            var ventana = window.open('', 'PRINT', 'height=1000,width=1200');
            ventana.document.write(`
                <html>
                    <head>
                        <meta charset="utf-8">
                        <title>${document.title}</title>
                        <style>
                            *{
                                margin: 0;
                                padding: 0;
                                box-sizing: border-box;
                            }
                            body {
                                font-family: Arial, sans-serif;
                            }
                            .d-flex {
                                display: flex;
                            }
                            .flex-column {
                                flex-direction: column;
                            }
                            .justify-content-center {
                                justify-content: center;
                            }
                            .container {
                                width: 100%;
                                padding-right: 15px;
                                padding-left: 15px;
                                margin-right: auto;
                                margin-left: auto;
                            }
                            .table {
                                width: 100%;
                                margin-bottom: 1rem;
                                color: #212529;
                            }
                            .table-bordered {
                                border-collapse: collapse;
                            }
                            .table-bordered th,
                            .table-bordered td {
                                border: 1px solid #dee2e6;
                                padding: .75rem;
                                vertical-align: top;
                            }
                        </style>
                    </head>
                    <body>
                        ${elemento.innerHTML}
                    </body>
                </html>
            `);
            ventana.document.close();
            ventana.onload = function() {
                ventana.focus();
                ventana.print();
            };
            ventana.onafterprint = function() {
                ventana.close();
            };
        }
    </script>
</body>

</html>
