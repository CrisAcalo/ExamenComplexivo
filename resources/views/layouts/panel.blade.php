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

    <link rel="icon" href="{{ Storage::url('logos/ITIN_LOGO_SMALL.png') }}" type="image/x-icon">

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
        --dark: #1a3d2e;
        --very-light-pink: #c7c7c7;
        --text-input-field: #f7f7f7;
        --hospital-green: #2d7a4f;
        --espe-green: #2d7a4f;
        --espe-green-dark: #1a4d30;
        --espe-green-light: #4a9d6f;
        --sm: 14px;
        --md: 16px;
        --lg: 18px;

        /* Variables de Bootstrap personalizadas - Tema verde ESPE */
        --bs-info: #2d7a4f;
        --bs-info-rgb: 45, 122, 79;

        /* Variables completas para todos los elementos info */
        --bs-info-bg-subtle: rgba(45, 122, 79, 0.125);
        --bs-info-border-subtle: rgba(45, 122, 79, 0.375);
        --bs-info-text-emphasis: #1a4d30;

        /* Variables para botones y badges */
        --bs-btn-color: #fff;
        --bs-btn-bg: #2d7a4f;
        --bs-btn-border-color: #2d7a4f;
        --bs-btn-hover-color: #fff;
        --bs-btn-hover-bg: #1a4d30;
        --bs-btn-hover-border-color: #1a4d30;
        --bs-btn-focus-shadow-rgb: 45, 122, 79;
        --bs-btn-active-color: #fff;
        --bs-btn-active-bg: #1a4d30;
        --bs-btn-active-border-color: #1a4d30;
        --bs-btn-active-shadow: inset 0 3px 5px rgba(0, 0, 0, 0.125);
        --bs-btn-disabled-color: #fff;
        --bs-btn-disabled-bg: #2d7a4f;
        --bs-btn-disabled-border-color: #2d7a4f;
    }

    /* Sobrescribir clases específicas de Bootstrap para color verde ESPE */
    .bg-info {
        background-color: #2d7a4f !important;
    }

    .badge.bg-info {
        background-color: #2d7a4f !important;
        color: #fff !important;
    }

    .btn-info {
        background-color: #2d7a4f !important;
        border-color: #2d7a4f !important;
        color: #fff !important;
    }

    .btn-info:hover {
        background-color: #1a4d30 !important;
        border-color: #1a4d30 !important;
        color: #fff !important;
    }

    .text-info {
        color: #2d7a4f !important;
    }

    .alert-info {
        background-color: rgba(45, 122, 79, 0.125) !important;
        border-color: rgba(45, 122, 79, 0.375) !important;
        color: #1a4d30 !important;
    }

    * {
        /* font-size:15px; */
    }

    /* Prevenir scroll en toda la página */
    html, body {
        overflow: hidden;
        height: 100vh;
        margin: 0;
        padding: 0;
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
        color: #2d7a4f;
        cursor: pointer;
    }

    .checkbox_deploy_container {
        position: relative;
        height: 35px;
        border-radius: 5px;
        padding: 5px 0;
        color: #fff;
        background-color: #2d7a4f;
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

    /* Botón flotante de configuración (si se usa) */
    .config-float-btn {
        position: fixed;
        bottom: 20px;
        right: 20px;
        width: 56px;
        height: 56px;
        border-radius: 50%;
        background: linear-gradient(135deg, #2d7a5f 0%, #1a4d30 100%);
        color: white;
        border: none;
        box-shadow: 0 4px 12px rgba(45, 122, 79, 0.4);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        cursor: pointer;
        transition: all 0.3s ease;
        z-index: 1000;
    }

    .config-float-btn:hover {
        transform: scale(1.1) rotate(90deg);
        box-shadow: 0 6px 20px rgba(45, 122, 79, 0.6);
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
        overflow: hidden;
        height: 100vh;
        position: fixed;
        box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        border-right: 1px solid #e0e0e0;
        background-image: url('{{ Storage::url("fondos/sidebar.png") }}');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        display: flex;
        flex-direction: column;
    }

    #sidebar::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(180deg, rgba(255,255,255,0.90) 0%, rgba(248,249,250,0.90) 100%);
        z-index: 0;
    }

    #sidebar > * {
        position: relative;
        z-index: 1;
    }

    #sidebar::-webkit-scrollbar {
        width: 6px;
    }

    #sidebar::-webkit-scrollbar-thumb {
        background-color: #2d7a5f;
        border-radius: 3px;
    }

    #sidebar::-webkit-scrollbar-track {
        background-color: rgba(240, 240, 240, 0.5);
    }

    /* Header del Sidebar */
    .sidebar-header {
        padding: 5px;
        border-bottom: 1px solid #e0e0e0;
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(10px);
    }

    .sidebar-title-text {
        font-size: 30px;
        font-weight: 700;
        color: #333;
        letter-spacing: 0.5px;
    }

    /* Categorías del menú */
    .menu-category {
        font-size: 11px;
        font-weight: 600;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 15px 20px 8px;
        margin-top: 10px;
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(5px);
    }

    .menu-category:first-of-type {
        margin-top: 0;
    }

    /* Items del menú */
    .sidebar-menu {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .sidebar-menu-item {
        margin: 2px 10px;
    }

    .sidebar-menu-link {
        display: flex;
        align-items: center;
        padding: 12px 15px;
        color: #333;
        text-decoration: none;
        border-radius: 8px;
        transition: all 0.2s ease;
        font-size: 14px;
    }

    .sidebar-menu-link:hover {
        background: linear-gradient(135deg, rgba(232, 245, 233, 0.9) 0%, rgba(200, 230, 201, 0.9) 100%);
        color: #2d7a5f;
        transform: translateX(3px);
        text-decoration: none;
        backdrop-filter: blur(10px);
    }

    .sidebar-menu-link.active {
        background: linear-gradient(135deg, rgba(61, 142, 114, 0.15) 0%, rgba(61, 166, 106, 0.15) 100%);
        color: #2d7a5f;
        font-weight: 600;
        border-left: 4px solid #3d8e72ff;
        padding-left: 11px;
        box-shadow: 0 2px 4px rgba(61, 142, 114, 0.1);
    }

    .sidebar-menu-link.active i {
        color: #3d8e72ff;
    }

    .sidebar-menu-link i {
        font-size: 18px;
        margin-right: 12px;
        min-width: 20px;
        text-align: center;
    }

    /* Badges en el sidebar */
    .sidebar-badge {
        font-size: 10px;
        padding: 3px 8px;
        border-radius: 12px;
        font-weight: 500;
        margin: 2px;
    }


    .wrapper {
        display: flex;
        width: 100%;
        height: 100vh;
        align-items: stretch;
        overflow: hidden;
    }



    #content {
        width: 100%;
        padding: 0 20px 20px 20px;
        height: calc(100vh - 64px);
        overflow-y: auto;
        overflow-x: hidden;
        transition: all 0.3s;
        background: #f5f7fa;
        margin-top: 64px;
        position: relative;
    }

    .button_sideBar {
        display: none;
        border: none;
        background: linear-gradient(135deg, #2d7a5f 0%, #1a4d30 100%);
        color: white;
        border-radius: 8px;
        padding: 8px 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        transition: all 0.2s ease;
    }

    .button_sideBar:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    }

    .button_sideBar:active {
        transform: translateY(0);
        box-shadow: 0 2px 6px rgba(0,0,0,0.15);
    }

    .button_close_sideBar {
        display: none;
        border: none;
        background: rgba(0,0,0,0.5);
        color: white;
        border-radius: 8px;
        padding: 8px 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.3);
        transition: all 0.2s ease;
    }

    .button_close_sideBar:hover {
        background: rgba(0,0,0,0.7);
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
            height: 100vh;
            margin-left: -280px;
            transition: all 0.3s;
            z-index: 1000;
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
            height: calc(100vh - 64px);
            overflow-y: auto;
        }

        .top-user-header {
            left: 0;
            width: 100%;
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
        color: #2d7a4f;
        /* Color verde ESPE para el saludo */
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
        background-color: #2d7a4f;
    }


    .login-button:hover {
        background-color: #2d7a4f;
        background-image: radial-gradient(at 30% 30%, rgba(255, 255, 255, 0.15), transparent 50%), radial-gradient(at 90% 20%, rgba(0, 0, 0, 0.1), transparent 50%);
        box-shadow: 0 0.25rem 0.5rem rgba(45, 122, 79, 0.25), 0 0.2rem 1rem rgba(45, 122, 79, 0.15);
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
        background-color: #2d7a4f !important;
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

    /* Top header con información del usuario */
    .top-user-header {
        background: white;
        border-bottom: 1px solid #e0e0e0;
        padding: 8px 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        position: fixed;
        top: 0;
        left: 280px;
        right: 0;
        z-index: 999;
        height: 61px;
        display: flex;
        align-items: center;
    }

    .top-user-header .dropdown-toggle::after {
        display: none;
    }

    .top-user-header .dropdown-menu {
        border: none;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    .top-user-header .dropdown-item:hover {
        background-color: #f8f9fa;
    } 

    .pin-active {
        color: #2d7a4f !important;
        transform: rotate(45deg);
    }
    
    .pin-inactive {
        color: #6c757d !important;
        transform: rotate(0deg);
    }    

    .sidebar-pinned {
        position: fixed !important;
    }
    
    .sidebar-unpinned {
        position: absolute !important;
        margin-left: -280px !important;
        transition: margin-left 0.3s ease;
    }
    
    .sidebar-unpinned:hover {
        margin-left: 0px !important;
    }

    .content-expanded {
        margin-left: 0px !important;
        width: 100% !important;
    }       
</style>

<body>

    <div class="wrapper">
        <div id="sidebar_container" class="sidebar_container">
            <div class="d-flex flex-column flex-shrink-0" style="width: 280px;" id="sidebar">
                <!-- Header del Sidebar -->
                <div class="sidebar-header">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            @if (file_exists(public_path('storage/logos/LOGO-ESPE_lg.png')))
                                <img src="{{ asset('storage/logos/LOGO-ESPE_lg.png') }}" alt="Logo ESPE"
                                     style="width: 100px; height: 50px; object-fit: contain; margin-right: 10px;">
                            @else
                                <img src="{{ Storage::url('logos/LOGO-ITIN.png') }}" alt="Logo"
                                     style="width: 45px; height: auto; margin-right: 10px;">
                            @endif
                            <span class="sidebar-title-text">MENÚ</span>
                        </div>
                        <i id="pinIcon" class="bi bi-pin-angle-fill" onclick="toggleSidebarPin()" 
                           style="color: #2d7a4f; font-size: 16px; cursor: pointer; transition: all 0.3s ease;" 
                           title="Pin/Unpin Menu"></i>
                    </div>
                </div>

                {{-- Mostrar rol del usuario --}}
                @php
                    use App\Helpers\ContextualAuth;

                    // Obtener información contextual del usuario UNA SOLA VEZ
                    $userContextInfo = ContextualAuth::getUserContextInfo(auth()->user());

                    // Variables para roles contextuales
                    $esDirectorCarrera = $userContextInfo['carreras_director']->isNotEmpty();
                    $esDocenteApoyo = $userContextInfo['carreras_apoyo']->isNotEmpty();
                    $esMiembroTribunal = $userContextInfo['tribunales']->isNotEmpty();
                    $esCalificadorGeneral = $userContextInfo['calificador_general']->isNotEmpty();

                    // Construir información de carreras asignadas
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

                    // Asignaciones como Calificador General
                    $calificadorGeneralAsignaciones = $userContextInfo['calificador_general'];
                @endphp

                <!-- Información de Rol Global -->
                <div class="py-3 px-3" style="border-bottom: 1px solid #e0e0e0; background: rgba(255, 255, 255, 0.7); backdrop-filter: blur(5px);">
                    @if (Auth::user()->hasRole('Super Admin'))
                        <div class="mb-0">
                            <span class="badge bg-danger text-white w-100 py-2" style="font-size: 12px;">
                                <i class="bi bi-shield-fill-check me-1"></i>Super Administrador
                            </span>
                        </div>
                    @elseif (Auth::user()->hasRole('Director de Carrera'))
                        <div class="mb-0">
                            <span class="badge bg-success text-white w-100 py-2" style="font-size: 12px;">
                                <i class="bi bi-person-badge me-1"></i>Director de Carrera
                            </span>
                        </div>
                    @elseif (Auth::user()->hasRole('Docente de Apoyo'))
                        <div class="mb-0">
                            <span class="badge bg-info text-white w-100 py-2" style="font-size: 12px;">
                                <i class="bi bi-person-check me-1"></i>Docente de Apoyo
                            </span>
                        </div>
                    @else
                        <div class="mb-0">
                            <span class="badge bg-secondary text-white w-100 py-2" style="font-size: 12px;">
                                <i class="bi bi-person me-1"></i>Docente
                            </span>
                        </div>
                    @endif
                </div>

                <!-- Contenido del Menú -->
                <div style="flex: 1; overflow-y: auto;">
                    <!-- Categoría MAIN -->
                    <div class="menu-category"></div>
                    <ul class="sidebar-menu">
                        <li class="sidebar-menu-item">
                            <a href="{{ route('home') }}" class="sidebar-menu-link {{ Request::routeIs('home') ? 'active' : '' }}">
                                <i class="bi bi-house-door"></i>
                                <span>Principal</span>
                            </a>
                        </li>
                    </ul>

                    <!-- Categoría GESTIÓN -->
                    <div class="menu-category">GESTIÓN</div>
                    <ul class="sidebar-menu">
                    @php
                        // Verificar si es Super Admin o Administrador
                        $isSuperAdminOrAdmin = ContextualAuth::isSuperAdminOrAdmin(Auth::user());

                        // Verificar permisos específicos usando las variables ya definidas
                        $puedeGestionarPeriodos = Auth::user()->can('gestionar periodos');
                        $puedeVerPeriodos = $puedeGestionarPeriodos || $esDirectorCarrera || $esDocenteApoyo;
                        $puedeGestionarCarreras = Auth::user()->can('gestionar carreras');

                        // ESTUDIANTES: Solo Director/Apoyo - NO Super Admin
                        $puedeVerEstudiantes = !$isSuperAdminOrAdmin && (
                            Auth::user()->can('ver listado estudiantes') ||
                            Auth::user()->can('gestionar estudiantes') ||
                            $esDirectorCarrera ||
                            $esDocenteApoyo
                        );

                        // RÚBRICAS: Solo Super Admin - NO Director/Apoyo
                        $puedeVerRubricas = $isSuperAdminOrAdmin && (
                            Auth::user()->can('ver rubricas') ||
                            Auth::user()->can('gestionar rubricas') ||
                            Auth::user()->can('gestionar plantillas rubricas')
                        );

                        // TRIBUNALES: Solo Director/Apoyo/Docentes - NO Super Admin
                        $puedeVerTribunales = !$isSuperAdminOrAdmin && (
                            Auth::user()->can('ver listado tribunales') ||
                            $esDirectorCarrera ||
                            $esDocenteApoyo ||
                            $esMiembroTribunal ||
                            $esCalificadorGeneral
                        );
                    @endphp

                        {{-- Períodos: Super Admin, Administrador, Director, Apoyo --}}
                        @if ($puedeVerPeriodos)
                            <li class="sidebar-menu-item">
                                <a href="{{ route('periodos.') }}" class="sidebar-menu-link {{ Request::is('periodos*') ? 'active' : '' }}">
                                    <i class="bi bi-calendar3"></i>
                                    <span>Períodos</span>
                                </a>
                            </li>
                        @endif

                        {{-- Carreras: Solo Super Admin y Administrador --}}
                        @if ($puedeGestionarCarreras)
                            <li class="sidebar-menu-item">
                                <a href="{{ route('carreras.') }}" class="sidebar-menu-link {{ Request::is('carreras*') ? 'active' : '' }}">
                                    <i class="bi bi-mortarboard"></i>
                                    <span>Carreras</span>
                                </a>
                            </li>
                        @endif

                        {{-- Estudiantes: Solo Director/Apoyo - NO Super Admin --}}
                        @if ($puedeVerEstudiantes)
                            <li class="sidebar-menu-item">
                                <a href="{{ route('estudiantes.') }}" class="sidebar-menu-link {{ Request::is('estudiantes*') ? 'active' : '' }}">
                                    <i class="bi bi-people"></i>
                                    <span>Estudiantes</span>
                                </a>
                            </li>
                        @endif

                        {{-- Rúbricas: Solo Super Admin - NO Director/Apoyo --}}
                        @if ($puedeVerRubricas)
                            <li class="sidebar-menu-item">
                                <a href="{{ route('rubricas.') }}" class="sidebar-menu-link {{ Request::is('rubricas*') ? 'active' : '' }}">
                                    <i class="bi bi-grid-3x3-gap"></i>
                                    <span>Rúbricas</span>
                                </a>
                            </li>
                        @endif

                        {{-- Calificación: Solo Director/Apoyo/Docentes - NO Super Admin --}}
                        @if ($puedeVerTribunales)
                            <li class="sidebar-menu-item">
                                <a href="{{ route('tribunales.principal') }}" class="sidebar-menu-link {{ Request::is('tribunales*') ? 'active' : '' }}">
                                    <i class="bi bi-clipboard-check"></i>
                                    <span>Calificación</span>
                                </a>
                            </li>
                        @endif

                        {{-- Mis Actas Firmadas: Para presidentes de tribunales --}}
                        @if (!$isSuperAdminOrAdmin && $esMiembroTribunal && Auth::user()->can('subir acta firmada mi tribunal (presidente)'))
                            <li class="sidebar-menu-item">
                                <a href="{{ route('actas-firmadas.index') }}" class="sidebar-menu-link {{ Request::is('actas-firmadas') ? 'active' : '' }}">
                                    <i class="bi bi-file-earmark-arrow-up"></i>
                                    <span>Mis Actas Firmadas</span>
                                </a>
                            </li>
                        @endif

                        {{-- Actas Firmadas: Para Director/Apoyo --}}
                        @if (!$isSuperAdminOrAdmin && ($esDirectorCarrera || $esDocenteApoyo) && Auth::user()->can('descargar actas firmadas'))
                            <li class="sidebar-menu-item">
                                <a href="{{ route('actas-firmadas-descarga.index') }}" class="sidebar-menu-link {{ Request::is('actas-firmadas-descarga') ? 'active' : '' }}">
                                    <i class="bi bi-file-earmark-check"></i>
                                    <span>Actas Firmadas</span>
                                </a>
                            </li>
                        @endif

                        {{-- Docentes: Director/Apoyo pueden gestionar docentes --}}
                        @if (!$isSuperAdminOrAdmin && ($esDirectorCarrera || $esDocenteApoyo))
                            <li class="sidebar-menu-item">
                                <a href="{{ route('users.') }}" class="sidebar-menu-link {{ Request::is('users*') || Request::is('docentes*') ? 'active' : '' }}">
                                    <i class="bi bi-people-fill"></i>
                                    <span>Docentes</span>
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
                            <li class="sidebar-menu-item">
                                <div class="alert alert-warning py-2 px-3 mb-2 mx-2" style="font-size: 12px;">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Sin asignaciones activas
                                </div>
                            </li>
                        @endif
                    </ul>

                    {{-- Roles y Permisos: Solo Super Admin --}}
                    @if (Auth::user()->roles->where('name', 'Super Admin')->isNotEmpty())
                        <div class="menu-category">ADMINISTRACIÓN</div>
                        <ul class="sidebar-menu">
                            <li class="sidebar-menu-item">
                                <a href="{{ route('roles.') }}" class="sidebar-menu-link {{ Request::is('roles*') ? 'active' : '' }}">
                                    <i class="bi bi-shield-check"></i>
                                    <span>Roles</span>
                                </a>
                            </li>
                            <li class="sidebar-menu-item">
                                <a href="{{ route('permissions.') }}" class="sidebar-menu-link {{ Request::is('permissions*') ? 'active' : '' }}">
                                    <i class="bi bi-key"></i>
                                    <span>Permisos</span>
                                </a>
                            </li>
                            <li class="sidebar-menu-item">
                                <a href="{{ route('plantillas_acta_word.index') }}" class="sidebar-menu-link {{ Request::is('plantillas-acta-word*') ? 'active' : '' }}">
                                    <i class="bi bi-file-earmark-word"></i>
                                    <span>Plantillas Acta Word</span>
                                </a>
                            </li>
                        </ul>
                    @endif

                    {{-- Docentes: Super Admin y Administrador --}}
                    @if (ContextualAuth::isSuperAdminOrAdmin(Auth::user()))
                        <div class="menu-category">GESTIÓN DE USUARIOS</div>
                        <ul class="sidebar-menu">
                            <li class="sidebar-menu-item">
                                <a href="{{ route('users.') }}" class="sidebar-menu-link {{ Request::is('users*') || Request::is('docentes*') ? 'active' : '' }}">
                                    <i class="bi bi-people-fill"></i>
                                    <span>Docentes</span>
                                </a>
                            </li>
                        </ul>
                    @endif

                    @impersonating($guard = null)
                        <div class="menu-category">SESIÓN</div>
                        <ul class="sidebar-menu">
                            <li class="sidebar-menu-item">
                                <a class="sidebar-menu-link" href="{{ route('users.exitImpersonate') }}">
                                    <i class="bi bi-box-arrow-left"></i>
                                    <span>Salir Impersonate</span>
                                </a>
                            </li>
                        </ul>
                    @endImpersonating
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

            <!-- Header Horizontal con Usuario -->
            <div class="top-user-header">
                <div class="d-flex justify-content-end align-items-center w-100">
                    <div class="dropdown">
                        <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle"
                           id="dropdownUserHeader" data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="rounded-circle bg-gradient d-flex align-items-center justify-content-center me-2"
                                 style="width: 40px; height: 40px; background: linear-gradient(135deg, #2d7a5f 0%, #3498db 100%);">
                                <i class="bi bi-person-fill text-white"></i>
                            </div>
                            <div class="text-end me-2">
                                <div class="fw-semibold" style="font-size: 14px; color: #333;">
                                    {{ Auth::user()->name }} {{ Auth::user()->lastname }}
                                </div>
                                @php
                                    $user = Auth::user();
                                    $contextInfo = \App\Helpers\ContextualAuth::getUserContextInfo($user);
                                    $rolesContextuales = [];

                                    // Director de Carrera
                                    foreach ($contextInfo['carreras_director'] as $carrera) {
                                        $rolesContextuales[] = 'Director - ' . $carrera->carrera->nombre . ' (' . $carrera->periodo->codigo_periodo . ')';
                                    }

                                    // Docente de Apoyo
                                    foreach ($contextInfo['carreras_apoyo'] as $carrera) {
                                        $rolesContextuales[] = 'Docente de Apoyo - ' . $carrera->carrera->nombre . ' (' . $carrera->periodo->codigo_periodo . ')';
                                    }

                                    // Calificador General
                                    foreach ($contextInfo['calificador_general'] as $calificador) {
                                        $rolesContextuales[] = 'Calificador General - ' . $calificador->carreraPeriodo->carrera->nombre . ' (' . $calificador->carreraPeriodo->periodo->codigo_periodo . ')';
                                    }

                                    // Miembro de Tribunal (agrupar por carrera)
                                    $tribunalesAgrupados = [];
                                    foreach ($contextInfo['tribunales'] as $tribunal) {
                                        if ($tribunal->tribunal && $tribunal->tribunal->carrerasPeriodo) {
                                            $cp = $tribunal->tribunal->carrerasPeriodo;
                                            $key = $cp->carrera->nombre;
                                            if (!isset($tribunalesAgrupados[$key])) {
                                                $tribunalesAgrupados[$key] = [
                                                    'carrera' => $cp->carrera->nombre,
                                                    'periodo' => $cp->periodo->codigo_periodo,
                                                    'roles' => []
                                                ];
                                            }
                                            $rol = ucwords(strtolower($tribunal->status));
                                            if (!in_array($rol, $tribunalesAgrupados[$key]['roles'])) {
                                                $tribunalesAgrupados[$key]['roles'][] = $rol;
                                            }
                                        }
                                    }
                                    foreach ($tribunalesAgrupados as $tb) {
                                        $roles = implode('/', $tb['roles']);
                                        $rolesContextuales[] = $roles . ' - ' . $tb['carrera'] . ' (' . $tb['periodo'] . ')';
                                    }
                                @endphp

                                @if(count($rolesContextuales) > 0)
                                    {{-- Mostrar roles contextuales específicos --}}
                                    @foreach($rolesContextuales as $rol)
                                        <small class="text-muted d-block" style="font-size: 11px; line-height: 1.4;">
                                            {{ $rol }}
                                        </small>
                                    @endforeach
                                @else
                                    {{-- Si no tiene roles contextuales, verificar si tiene roles globales --}}
                                    @if($user->hasRole('Director de Carrera'))
                                        <small class="text-muted" style="font-size: 11px;">
                                            Director de Carrera (sin asignaciones activas)
                                        </small>
                                    @elseif($user->hasRole('Docente de Apoyo'))
                                        <small class="text-muted" style="font-size: 11px;">
                                            Docente de Apoyo (sin asignaciones activas)
                                        </small>
                                    @else
                                        <small class="text-muted" style="font-size: 11px;">
                                            Sin asignaciones contextuales
                                        </small>
                                    @endif
                                @endif
                            </div>
                            <i class="bi bi-chevron-down text-muted"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0"
                            aria-labelledby="dropdownUserHeader"
                            style="min-width: 220px; margin-top: 10px;">
                            <li class="px-3 py-2 border-bottom">
                                <small class="text-muted d-block">Sesión iniciada como</small>
                                <strong class="d-block text-truncate">{{ Auth::user()->email }}</strong>
                            </li>
                            <li>
                                <a class="dropdown-item py-2" href="{{ route('mi.perfil') }}">
                                    <i class="bi bi-person-circle me-2 text-primary"></i>Mi Perfil
                                </a>
                            </li>
                            <li><hr class="dropdown-divider my-1"></li>
                            <li>
                                <a class="dropdown-item py-2 text-danger" href="{{ route('logout') }}"
                                   onclick="event.preventDefault(); document.getElementById('logout-form-header').submit();">
                                    <i class="bi bi-box-arrow-right me-2"></i>Cerrar Sesión
                                </a>
                                <form id="logout-form-header" action="{{ route('logout') }}" method="POST" class="d-none">
                                    @csrf
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

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

    <script>
        // Variable para controlar el estado del pin
        let sidebarPinned = true; // Por defecto está pinned
        
        function toggleSidebarPin() {
            const sidebar = document.getElementById("sidebar");
            const sidebarContainer = document.getElementById("sidebar_container");
            const content = document.getElementById("content");
            const pinIcon = document.getElementById("pinIcon");
            
            if (sidebarPinned) {
                // Desanclar el sidebar
                sidebar.classList.add('sidebar-unpinned');
                sidebar.classList.remove('sidebar-pinned');
                content.classList.add('content-expanded');
                pinIcon.classList.add('pin-inactive');
                pinIcon.classList.remove('pin-active');
                pinIcon.className = pinIcon.className.replace('bi-pin-angle-fill', 'bi-pin-angle');
                pinIcon.title = "Pin Menu";
                sidebarPinned = false;
            } else {
                // Anclar el sidebar
                sidebar.classList.add('sidebar-pinned');
                sidebar.classList.remove('sidebar-unpinned');
                content.classList.remove('content-expanded');
                pinIcon.classList.add('pin-active');
                pinIcon.classList.remove('pin-inactive');
                pinIcon.className = pinIcon.className.replace('bi-pin-angle', 'bi-pin-angle-fill');
                pinIcon.title = "Unpin Menu";
                sidebarPinned = true;
            }
        }
        
        // Inicializar el estado por defecto
        document.addEventListener('DOMContentLoaded', function() {
            const pinIcon = document.getElementById("pinIcon");
            pinIcon.classList.add('pin-active');
        });

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
    @stack('scripts')
</body>

</html>
