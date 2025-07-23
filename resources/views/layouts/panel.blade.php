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
    <!-- Estilos personalizados para formularios -->
    <style>
        /* Estilos para labels */
        .form-label {
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #495057;
        }

        /* Estilos para indicadores de campos requeridos */
        .text-danger {
            color: #dc3545 !important;
        }

        /* Mejorar espaciado en modales */
        .modal-body .mb-3:last-child {
            margin-bottom: 0 !important;
        }

        /* Estilos para selects */
        .form-select {
            min-height: 38px;
        }

        .form-select:focus {
            border-color: #86b7fe;
            outline: 0;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }
    </style>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">
    {{-- importar el resources/css/app.css --}}
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">

    @stack('styles')
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
        --lg: 18px;

        /* Variables de Bootstrap personalizadas */
        /* Color info original: #0dcaf0 - Nuevo color más oscuro */
        --bs-info: #0ea5e9;
        --bs-info-rgb: 14, 165, 233;

        /* Variables completas para todos los elementos info */
        --bs-info-bg-subtle: rgba(14, 165, 233, 0.125);
        --bs-info-border-subtle: rgba(14, 165, 233, 0.375);
        --bs-info-text-emphasis: #0369a1;

        /* Variables para botones y badges */
        --bs-btn-color: #fff;
        --bs-btn-bg: #0ea5e9;
        --bs-btn-border-color: #0ea5e9;
        --bs-btn-hover-color: #fff;
        --bs-btn-hover-bg: #0284c7;
        --bs-btn-hover-border-color: #0284c7;
        --bs-btn-focus-shadow-rgb: 14, 165, 233;
        --bs-btn-active-color: #fff;
        --bs-btn-active-bg: #0369a1;
        --bs-btn-active-border-color: #0369a1;
        --bs-btn-active-shadow: inset 0 3px 5px rgba(0, 0, 0, 0.125);
        --bs-btn-disabled-color: #fff;
        --bs-btn-disabled-bg: #0ea5e9;
        --bs-btn-disabled-border-color: #0ea5e9;
    }

    /* Sobrescribir clases específicas de Bootstrap para color info */
    .bg-info {
        background-color: #0ea5e9 !important;
    }

    .badge.bg-info {
        background-color: #0ea5e9 !important;
        color: #fff !important;
    }

    .btn-info {
        background-color: #0ea5e9 !important;
        border-color: #0ea5e9 !important;
        color: #fff !important;
    }

    .btn-info:hover {
        background-color: #0284c7 !important;
        border-color: #0284c7 !important;
        color: #fff !important;
    }

    .text-info {
        color: #0ea5e9 !important;
    }

    .alert-info {
        background-color: rgba(14, 165, 233, 0.125) !important;
        border-color: rgba(14, 165, 233, 0.375) !important;
        color: #0369a1 !important;
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

    .choices {
        margin-bottom: 0;
        position: relative;
    }

    .choices__inner {
        min-height: 40px;
        padding: 7.5px 7.5px 3.75px;
        border: 1px solid #ced4da;
        border-radius: 0.375rem;
        background-color: #fff;
        font-size: 1rem;
        line-height: 1.5;
        color: #495057;
    }

    .choices__inner:focus-within {
        border-color: #86b7fe;
        outline: 0;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }

    .choices.is-invalid .choices__inner {
        border-color: #dc3545;
        box-shadow: 0 0 0 0.25rem rgba(220, 53, 69, 0.25);
    }

    .choices__list--dropdown {
        border: 1px solid #ced4da;
        border-radius: 0.375rem;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        z-index: 1050;
    }

    .choices__item--selectable {
        padding: 8px 12px;
    }

    .choices__item--selectable:hover {
        background-color: #f8f9fa;
    }

    .choices__item--highlighted {
        background-color: #0d6efd !important;
        color: white !important;
    }

    .choices__placeholder {
        color: #6c757d;
        opacity: 1;
    }

    .choices__input {
        color: #495057;
        background-color: transparent;
        margin: 0;
        padding: 0;
    }

    .choices__input:focus {
        outline: none;
    }

    /* Estilos para labels */
    .form-label {
        margin-bottom: 0.5rem;
        font-weight: 600;
        color: #495057;
    }

    /* Cuando el select tiene valor o está enfocado */
    .form-floating>.choices.is-focused+label,
    .form-floating>.choices:not(.choices--disabled)+label {
        opacity: 0.65;
        transform: scale(0.85) translateY(-0.5rem) translateX(0.15rem);
    }

    /* Para selects normales (no floating) */
    .choices:not(.form-floating .choices) {
        margin-bottom: 1rem;
    }

    .choices:not(.form-floating .choices) .choices__inner {
        min-height: 38px;
        padding: 7px 7.5px 3.75px;
    }

    /* Estilo mejorado para el dropdown */
    .choices__list--dropdown .choices__item--selectable {
        border-bottom: 1px solid #f8f9fa;
    }

    .choices__list--dropdown .choices__item--selectable:last-child {
        border-bottom: none;
    }

    .choices__input {
        background-color: transparent;
    }

    /* Estilos para elementos deshabilitados */
    .choices__item--disabled {
        color: #6c757d;
        background-color: #f8f9fa;
        cursor: not-allowed;
    }

    /* Mejoras para la búsqueda */
    .choices__input--cloned {
        background-color: transparent;
        border: none;
        outline: none;
        box-shadow: none;
    }

    .sidebar-logo{
        width: 40%;
        height: auto;
        margin: 15px auto 15px;
        border-radius: 5px;
    }
</style>

<body>

    <div class="wrapper">
        <div id="sidebar_container" class="sidebar_container">
            <div class="d-flex flex-column flex-shrink-0 p-3 text-white bg-dark" style="width: 280px;" id="sidebar">
                <a href="{{ url('/') }}"
                    class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none text-center">
                    <span class="fs-4">Sistema Examen Complexivo</span>
                </a>

                <img class="sidebar-logo" src="{{Storage::url('logos/LOGO-ITIN.png')}}"  alt="">

                {{-- Mostrar rol del usuario --}}
                <div class="text-center mb-2">
                    <small class="text-muted">
                        @php
                            use App\Helpers\ContextualAuth;

                            // Obtener información contextual del usuario
                            $userContext = ContextualAuth::getUserContextInfo(auth()->user());
                            $esDirectorCarrera = $userContext['carreras_director']->isNotEmpty();
                            $esDocenteApoyo = $userContext['carreras_apoyo']->isNotEmpty();
                            $esMiembroTribunal = $userContext['tribunales']->isNotEmpty();
                            $esCalificadorGeneral = $userContext['calificador_general']->isNotEmpty();
                        @endphp

                        @if (ContextualAuth::isSuperAdminOrAdmin(Auth::user()))
                            @if (Auth::user()->roles->where('name', 'Super Admin')->isNotEmpty())
                                <span class="badge bg-danger">Super Admin</span>
                            @else
                                <span class="badge bg-warning text-dark">Administrador</span>
                            @endif
                        @else
                            {{-- Mostrar todas las asignaciones contextuales que apliquen --}}
                            @if ($esDirectorCarrera)
                                <span class="badge bg-success">Director de Carrera</span>
                            @endif
                            @if ($esDocenteApoyo)
                                <span class="badge bg-info">Docente de Apoyo</span>
                            @endif
                            @if ($esCalificadorGeneral)
                                <span class="badge bg-warning text-dark">Calificador General</span>
                            @endif
                            @if ($esMiembroTribunal)
                                <span class="badge bg-primary">Miembro Tribunal</span>
                            @endif

                            {{-- Si no tiene asignaciones contextuales, mostrar rol base --}}
                            @if (!$esDirectorCarrera && !$esDocenteApoyo && !$esCalificadorGeneral && !$esMiembroTribunal)
                                <span class="badge bg-secondary">Docente</span>
                            @endif
                        @endif
                    </small>
                </div>
                <hr>
                <h5 class="fs-6 text-secondary">Control</h5>

                <ul class="list-group nav nav-pills flex-column mb-auto list-unstyled ps-0">
                    @php
                        // Verificar roles contextuales para el menú usando ContextualAuth
                        $userContext = ContextualAuth::getUserContextInfo(auth()->user());
                        $esDirectorCarrera = $userContext['carreras_director']->isNotEmpty();
                        $esDocenteApoyo = $userContext['carreras_apoyo']->isNotEmpty();
                        $esMiembroTribunal = $userContext['tribunales']->isNotEmpty();
                        $esCalificadorGeneral = $userContext['calificador_general']->isNotEmpty();

                        // Verificar permisos específicos
                        $puedeGestionarPeriodos = Auth::user()->can('gestionar periodos');
                        $puedeVerPeriodos = $puedeGestionarPeriodos || $esDirectorCarrera || $esDocenteApoyo;
                        $puedeGestionarCarreras = Auth::user()->can('gestionar carreras');
                        $puedeVerEstudiantes =
                            Auth::user()->can('ver listado estudiantes') ||
                            Auth::user()->can('gestionar estudiantes') ||
                            $esDirectorCarrera ||
                            $esDocenteApoyo;
                        $puedeVerRubricas =
                            Auth::user()->can('ver rubricas') ||
                            Auth::user()->can('gestionar rubricas') ||
                            Auth::user()->can('gestionar plantillas rubricas') ||
                            $esDirectorCarrera ||
                            $esDocenteApoyo;
                        $puedeVerTribunales =
                            Auth::user()->can('ver listado tribunales') ||
                            $esDirectorCarrera ||
                            $esDocenteApoyo ||
                            $esMiembroTribunal ||
                            $esCalificadorGeneral;
                    @endphp

                    {{-- Períodos: Usuarios con permisos específicos o asignaciones contextuales --}}
                    @if ($puedeVerPeriodos)
                        <li class="nav-item list-group nav-link-item">
                            <a href="{{ route('periodos.') }}" class="nav-link text-white">
                                <span class="icon-wrapper">
                                    <i class="bi bi-calendar3"></i></span>
                                Períodos
                            </a>
                        </li>
                    @endif

                    {{-- Carreras: Solo usuarios con permiso específico --}}
                    @if ($puedeGestionarCarreras)
                        <li class="nav-item list-group nav-link-item">
                            <a href="{{ route('carreras.') }}" class="nav-link text-white">
                                <span class="icon-wrapper">
                                    <i class="bi bi-mortarboard"></i></span>
                                Carreras
                            </a>
                        </li>
                    @endif

                    {{-- Estudiantes: Usuarios con permisos o asignaciones contextuales --}}
                    @if ($puedeVerEstudiantes)
                        <li class="nav-item list-group nav-link-item">
                            <a href="{{ route('estudiantes.') }}" class="nav-link text-white">
                                <span class="icon-wrapper">
                                    <i class="bi bi-people"></i></span>
                                Estudiantes
                            </a>
                        </li>
                    @endif

                    {{-- Rúbricas: Usuarios con permisos o asignaciones contextuales --}}
                    @if ($puedeVerRubricas)
                        <li class="nav-item list-group nav-link-item">
                            <a href="{{ route('rubricas.') }}" class="nav-link text-white">
                                <span class="icon-wrapper">
                                    <i class="bi bi-grid-3x3-gap"></i></span>
                                Rúbricas
                            </a>
                        </li>
                    @endif

                    {{-- Tribunales: Usuarios con permisos o asignaciones contextuales --}}
                    @if ($puedeVerTribunales)
                        <li class="nav-item list-group nav-link-item">
                            <a href="{{ route('tribunales.principal') }}" class="nav-link text-white">
                                <span class="icon-wrapper">
                                    <i class="bi bi-person-lines-fill"></i></span>
                                Tribunales
                            </a>
                        </li>
                    @endif

                    {{-- Mensaje para usuarios sin acceso --}}
                    @if (
                        !$puedeGestionarPeriodos &&
                            !$puedeGestionarCarreras &&
                            !$puedeVerEstudiantes &&
                            !$puedeVerRubricas &&
                            !$puedeVerTribunales)
                        <li class="nav-item">
                            <div class="alert alert-info py-2 px-3 mb-2">
                                <small>
                                    <i class="bi bi-info-circle"></i>
                                    No tienes asignaciones activas actualmente.
                                </small>
                            </div>
                        </li>
                    @endif


                    {{-- Roles y Permisos: Solo Super Admin --}}
                    @if (Auth::user()->roles->where('name', 'Super Admin')->isNotEmpty())
                        <hr>
                        <h5 class="fs-6 text-secondary">Acceso</h5>
                        <li class="nav-item list-group nav-link-item">
                            <a href="{{ route('roles.') }}" class="nav-link text-white">
                                <span class="icon-wrapper">
                                    <i class="bi bi-list-columns-reverse"></i></span>
                                Roles
                            </a>
                        </li>
                        <li class="nav-item list-group nav-link-item">
                            <a href="{{ route('permissions.') }}" class="nav-link text-white">
                                <span class="icon-wrapper">
                                    <i class="bi bi-list"></i></span>
                                Permisos
                            </a>
                        </li>
                    @endif


                    {{-- Usuarios: Super Admin y Administrador --}}
                    @if (ContextualAuth::isSuperAdminOrAdmin(Auth::user()))
                        <hr>
                        <h5 class="fs-6 text-secondary">Usuarios</h5>
                        <li class="nav-item list-group nav-link-item">
                            <a href="{{ route('users.') }}" class="nav-link text-white">
                                <span class="icon-wrapper">
                                    <i class="bi bi-person-lines-fill"></i></span>
                                Usuarios
                            </a>
                        </li>
                    @endif

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

                {{-- Información contextual del usuario --}}
                @php
                    // Usar ContextualAuth para obtener información contextual del usuario
                    $userContextInfo = ContextualAuth::getUserContextInfo(Auth::user());
                    $carrerasAsignadas = collect();

                    // Agregar asignaciones como Director
                    foreach ($userContextInfo['carreras_director'] as $carreraDirector) {
                        $carrerasAsignadas->push([
                            'texto' =>
                                $carreraDirector->carrera->nombre . ' - ' . $carreraDirector->periodo->codigo_periodo,
                            'tipo' => 'Director',
                        ]);
                    }

                    // Agregar asignaciones como Docente de Apoyo
                    foreach ($userContextInfo['carreras_apoyo'] as $carreraApoyo) {
                        $carrerasAsignadas->push([
                            'texto' => $carreraApoyo->carrera->nombre . ' - ' . $carreraApoyo->periodo->codigo_periodo,
                            'tipo' => 'Apoyo',
                        ]);
                    }

                    // Asignaciones en tribunales
                    $tribunalesAsignados = $userContextInfo['tribunales'];
                    $esMiembroTribunal = $tribunalesAsignados->isNotEmpty();

                    // Asignaciones como Calificador General
                    $calificadorGeneralAsignaciones = $userContextInfo['calificador_general'];
                @endphp

                @if ($carrerasAsignadas->isNotEmpty() || $esMiembroTribunal || $calificadorGeneralAsignaciones->isNotEmpty())
                    <div class="text-center mb-3">
                        <small class="text-muted">
                            <p class="text-light fw-bold"><strong>Contexto Actual:</strong></p>

                            {{-- Mostrar asignaciones como Director/Apoyo --}}
                            @if ($carrerasAsignadas->isNotEmpty())
                                @foreach ($carrerasAsignadas->take(3) as $carrera)
                                    <span
                                        class="badge bg-{{ $carrera['tipo'] == 'Director' ? 'success' : 'info' }} mb-1 d-block text-wrap">
                                        {{ $carrera['tipo'] }}: {{ $carrera['texto'] }}
                                    </span>
                                @endforeach
                                @if ($carrerasAsignadas->count() > 3)
                                    <span class="text-muted">... y {{ $carrerasAsignadas->count() - 3 }} más</span>
                                @endif
                            @endif

                            {{-- Mostrar asignaciones como Calificador General --}}
                            @if ($calificadorGeneralAsignaciones->isNotEmpty())
                                @if ($carrerasAsignadas->isNotEmpty())
                                    <p class="text-light fw-bold"><small>También:</small><br></p>
                                @endif
                                @foreach ($calificadorGeneralAsignaciones->take(2) as $calificador)
                                    <span class="badge bg-warning text-dark mb-1 d-block text-wrap">
                                        Calificador General: {{ $calificador->carreraPeriodo->carrera->nombre }} -
                                        {{ $calificador->carreraPeriodo->periodo->codigo_periodo }}
                                    </span>
                                @endforeach
                                @if ($calificadorGeneralAsignaciones->count() > 2)
                                    <span class="text-muted">... y {{ $calificadorGeneralAsignaciones->count() - 2 }}
                                        más</span>
                                @endif
                            @endif

                            {{-- Mostrar asignaciones como Miembro de Tribunal --}}
                            @if ($esMiembroTribunal)
                                @if ($carrerasAsignadas->isNotEmpty() || $calificadorGeneralAsignaciones->isNotEmpty())
                                    <br><small class="text-muted">También:</small><br>
                                @endif
                                <span class="badge bg-primary mb-1 d-block">
                                    Miembro en {{ $tribunalesAsignados->count() }} tribunal(es)
                                </span>
                            @endif
                        </small>
                    </div>
                @elseif(!ContextualAuth::isSuperAdminOrAdmin(Auth::user()))
                    <div class="text-center mb-3">
                        <small class="text-muted">
                            <strong>Contexto:</strong><br>
                            <span class="badge bg-warning text-dark">Sin asignaciones actuales</span>
                        </small>
                    </div>
                @endif

                <hr>


                <div class="dropdown">
                    <a href="#"
                        class="d-flex align-items-center text-white text-decoration-none dropdown-toggle text-break"
                        id="dropdownUser1" data-bs-toggle="dropdown" aria-expanded="false"
                        style="white-space: normal;">
                        <strong class="text-break" style="word-break: break-word;">{{ Auth::user()->name }}</strong>
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

        function initializeSpecificPopovers(container) {
            const popoverTriggerList = [].slice.call(container.querySelectorAll('[data-bs-toggle="popover"]'));
            popoverTriggerList.forEach(function(popoverTriggerEl) {
                // Solo inicializar si no tiene ya una instancia de popover
                if (!bootstrap.Popover.getInstance(popoverTriggerEl)) {
                    new bootstrap.Popover(popoverTriggerEl, {
                        sanitize: false, // Ya discutimos la seguridad de esto
                        // container: 'body' // Opcional: A veces ayuda con problemas de z-index o clipping
                    });
                }
            });
        }

        document.addEventListener('livewire:load', function() {
            initializeSpecificPopovers(document); // Inicializar en la carga inicial para todo el documento
        });

        Livewire.hook('message.processed', (message, component) => {
            // Después de que Livewire actualice el DOM, buscar nuevos popovers o re-evaluar
            // El contenedor 'component.el' es el elemento raíz del componente Livewire que se actualizó
            if (component && component.el) {
                initializeSpecificPopovers(component.el);
            } else {
                initializeSpecificPopovers(document); // Fallback por si acaso
            }
        });
    </script>

    @stack('scripts')
</body>

</html>
