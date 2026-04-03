<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <script>
        (function () {
            try {
                var storedTheme = localStorage.getItem('theme');
                var systemDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
                var isDark = storedTheme ? storedTheme === 'dark' : systemDark;
                document.documentElement.setAttribute('data-theme', isDark ? 'dark' : 'light');
                if (isDark) {
                    document.documentElement.classList.add('dark-mode');
                }
            } catch (e) {
                document.documentElement.setAttribute('data-theme', 'light');
            }
        })();
    </script>
    
    {{-- Título --}}
    <title>{{ config('app.name') }}{{ $title ?? '' }}@yield('title')</title>
    
    {{-- Favicon --}}
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}" />

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,300;0,400;0,500;0,700;1,400&display=swap" rel="stylesheet">

    {{-- Estilos Globales --}}
    <link rel="stylesheet" href="{{ asset('adminlte/plugins/fontawesome-free/css/all.css') }}">
    <link rel="stylesheet" href="{{ asset('adminlte/dist/css/adminlte.min.css') }}">
    <link rel="stylesheet" href="{{ asset('adminlte/plugins/overlayScrollbars/css/OverlayScrollbars.min.css') }}">
    
    {{-- Estilos Personalizados --}}
    <link rel="stylesheet" href="{{ asset('css/style-nav.css') }}">
    <link rel="stylesheet" href="{{ asset('css/_dropdown.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin-theme.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin-responsive.css') }}">
    <link rel="stylesheet" href="{{ asset('css/accessibility.css') }}">

    {{-- MEJORA VISUAL Y FUNCIONAL: CSS "DEEP DARK" & RESPONSIVIDAD --}}
    <style>
        :root {
            --font-family-sans-serif: 'Roboto', -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Helvetica Neue', Arial, sans-serif;
            --ui-bg: #f4f6f9;
            --ui-surface: #ffffff;
            --ui-surface-2: #f8f9fa;
            --ui-border: #dee2e6;
            --ui-text: #212529;
            --ui-text-muted: #6c757d;
            --ui-primary: #007bff;
            --ui-hover: rgba(0, 0, 0, 0.03);
        }

        html[data-theme='dark'] {
            --ui-bg: #2b3035;
            --ui-surface: #343a40;
            --ui-surface-2: #3f474e;
            --ui-border: #56606a;
            --ui-text: #f1f3f5;
            --ui-text-muted: #ced4da;
            --ui-primary: #3b8bff;
            --ui-hover: rgba(255, 255, 255, 0.08);
        }

        /* --- 1. Tipografía y Scrollbar --- */
        body {
            font-family: 'Roboto', -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.5;
        }
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: #f4f6f9; }
        ::-webkit-scrollbar-thumb { background: #adb5bd; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #6c757d; }

        /* --- 2. Navbar Flotante y Avatar --- */
        .main-header { border-bottom: 0 !important; box-shadow: 0 0 15px rgba(0,0,0,0.05); }
        
        .user-avatar-circle {
            width: 35px; height: 35px;
            background-color: #007bff; color: white;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-weight: bold; font-size: 18px;
            border: 2px solid #ffffff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-transform: uppercase;
        }
        .nav-link.user-link { display: flex; align-items: center; height: 100%; }

        /* --- 3. Sidebar Logo --- */
        .brand-link {
            display: flex !important; align-items: center; justify-content: center;
            flex-direction: column; padding: 15px 10px !important; text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1) !important;
        }
        .brand-link .brand-image { float: none !important; margin: 0 auto 5px auto !important; opacity: 1 !important; }
        .brand-text { font-size: 1.1rem; font-weight: 700 !important; text-transform: uppercase; }

        /* --- 4. Responsividad Mejorada (Móviles) --- */
        @media (max-width: 576px) {
            .user-name-responsive { display: none !important; } /* Ocultar nombre largo en móviles */
            .main-header .navbar-nav .nav-link { padding-right: 0.5rem; padding-left: 0.5rem; }
        }

        /* =========================================================
           PARCHE PROFUNDO DE MODO OSCURO (Deep Dark Mode Fixes)
           ========================================================= */

        /* A. Scrollbar en modo oscuro */
        body.dark-mode ::-webkit-scrollbar-track { background: #454d55; }
        body.dark-mode ::-webkit-scrollbar-thumb { background: #6c757d; }

        /* B. Fondos y Contenedores */
        html.dark-mode body .content-wrapper,
        html.dark-mode body .main-footer,
        html.dark-mode body .main-header,
        body.dark-mode .content-wrapper,
        body.dark-mode .main-footer,
        body.dark-mode .main-header {
            background-color: var(--ui-surface) !important;
            color: var(--ui-text);
        }

        html[data-theme='dark'] .main-header.navbar-white {
            background-color: var(--ui-bg) !important;
            border-color: var(--ui-border) !important;
            color: var(--ui-text) !important;
        }
        html[data-theme='dark'] .main-header .nav-link,
        html[data-theme='dark'] .main-header .navbar-brand,
        html[data-theme='dark'] .main-header .text-muted {
            color: var(--ui-text-muted) !important;
        }

        /* C. Cards (Tarjetas) */
        html.dark-mode body .card,
        body.dark-mode .card {
            background-color: var(--ui-surface-2);
            color: var(--ui-text);
            border: 1px solid var(--ui-border);
            box-shadow: 0 0 1px rgba(255,255,255,.125), 0 1px 3px rgba(0,0,0,.2);
        }
        body.dark-mode .card-header { border-bottom: 1px solid var(--ui-border); }

        /* D. Modales y Dropdowns */
        body.dark-mode .modal-content,
        body.dark-mode .dropdown-menu {
            background-color: var(--ui-surface);
            color: var(--ui-text);
            border: 1px solid var(--ui-border);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.5);
        }
        body.dark-mode .dropdown-item { color: var(--ui-text); }
        body.dark-mode .dropdown-item:hover { background-color: var(--ui-hover); color: var(--ui-text); }
        body.dark-mode .dropdown-divider { border-top-color: var(--ui-border); }
        
        body.dark-mode .modal-header,
        body.dark-mode .modal-footer { border-color: var(--ui-border); }
        body.dark-mode .close { color: #fff; text-shadow: none; opacity: 0.8; }

        /* E. TABLAS Y DATATABLES (OPTIMIZADO) */
        body.dark-mode .table {
            background-color: transparent !important;
            color: #fff;
        }
        body.dark-mode .table-bordered {
            border: 1px solid var(--ui-border);
        }
        body.dark-mode .table-bordered td, 
        body.dark-mode .table-bordered th { 
            border-color: var(--ui-border) !important; 
        }
        body.dark-mode .thead-dark th {
            background-color: #23272b;
            border-color: #454d55;
        }
        /* Zebra Striping oscuro */
        body.dark-mode .table-striped tbody tr:nth-of-type(odd) { 
            background-color: rgba(255,255,255,0.05) !important; 
        }
        /* Hover oscuro */
        body.dark-mode .table-hover tbody tr:hover { 
            background-color: rgba(255,255,255,0.1) !important; 
            color: #fff;
        }
        /* Textos de DataTables (Info, Search, Length) */
        body.dark-mode .dataTables_wrapper .dataTables_length,
        body.dark-mode .dataTables_wrapper .dataTables_filter,
        body.dark-mode .dataTables_wrapper .dataTables_info,
        body.dark-mode .dataTables_wrapper .dataTables_processing,
        body.dark-mode .dataTables_wrapper .dataTables_paginate {
            color: #fff !important;
        }

        /* F. Inputs, Selects y Elementos de Formulario */
        html.dark-mode body .form-control,
        html.dark-mode body .custom-select,
        html.dark-mode body .select2-selection,
        html.dark-mode body .select2-selection__rendered,
        html.dark-mode body .select2-dropdown,
        html.dark-mode body .input-group-text,
        body.dark-mode .form-control,
        body.dark-mode .custom-select,
        body.dark-mode .select2-selection,
        body.dark-mode .select2-selection__rendered,
        body.dark-mode .select2-dropdown,
        body.dark-mode .input-group-text {
            background-color: var(--ui-surface) !important;
            color: var(--ui-text) !important;
            border-color: var(--ui-border) !important;
        }
        body.dark-mode .form-control:focus {
            background-color: var(--ui-surface-2) !important;
            border-color: var(--ui-primary) !important;
        }
        /*
         * Bootstrap .custom-select dibuja la flecha con SVG fill=#343a40.
         * En oscuro el fondo es ~#343a40, la flecha queda invisible.
         * Forzar chevron claro y mantener posición/tamaño del icono.
         */
        html.dark-mode body .custom-select,
        body.dark-mode .custom-select {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 4 5'%3e%3cpath fill='%23dee2e6' d='M2 0L0 2h4zm0 5L0 3h4z'/%3e%3c/svg%3e") !important;
            background-repeat: no-repeat !important;
            background-position: right 0.75rem center !important;
            background-size: 8px 10px !important;
        }
        body.dark-mode .select2-search__field { background-color: var(--ui-surface-2) !important; color: var(--ui-text) !important; }
        body.dark-mode .text-dark { color: var(--ui-text) !important; }

        /* G. PAGINACIÓN (OPTIMIZADO) */
        body.dark-mode .page-item .page-link {
            background-color: var(--ui-surface) !important;
            border-color: var(--ui-border) !important;
            color: var(--ui-text) !important;
        }
        body.dark-mode .page-item .page-link:hover { 
            background-color: var(--ui-hover) !important; 
            color: var(--ui-text) !important;
        }
        body.dark-mode .page-item.active .page-link { 
            background-color: var(--ui-primary) !important; 
            border-color: var(--ui-primary) !important; 
            color: #fff !important;
        }
        body.dark-mode .page-item.disabled .page-link { 
            background-color: var(--ui-surface-2) !important; 
            border-color: var(--ui-border) !important; 
            color: var(--ui-text-muted) !important; 
        }

        /* =========================================
           DISEÑO DEL MENÚ DE USUARIO (NAVBAR DROPDOWN)
           ========================================= */
        
        /* 1. Fondo Azul Mejorado (Gradiente) */
        .user-header.bg-primary {
            /* Solución al solapamiento: Altura automática */
            height: auto !important; 
            min-height: 175px;
            padding: 20px 15px !important;
            
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%) !important;
            border-top-left-radius: 4px;
            border-top-right-radius: 4px;
        }
        
        /* 2. Avatar en el menú */
        .user-header .user-avatar-circle {
            border: 3px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 4px 6px rgba(0,0,0,0.3);
            background-color: rgba(255,255,255,0.1);
        }

        /* 3. Texto del header en modo oscuro */
        body.dark-mode .user-header p { color: #fff !important; }
        
        /* 4. Footer del menú (Botones) */
        .user-footer {
            background-color: #f8f9fa !important;
            padding: 15px;
            border-bottom-left-radius: 4px;
            border-bottom-right-radius: 4px;
        }
        body.dark-mode .user-footer {
            background-color: var(--ui-surface-2) !important;
            border-top: 1px solid var(--ui-border);
        }

        /* 5. Botones Perfil/Salir en modo oscuro */
        .user-footer .btn-default {
            background-color: #ffffff;
            border-color: #ddd;
            color: #444;
            font-weight: 600;
        }
        .user-footer .btn-default:hover { background-color: #e9ecef; }

        body.dark-mode .user-footer .btn-default {
            background-color: var(--ui-surface);
            color: var(--ui-text);
            border: 1px solid var(--ui-border);
        }
        body.dark-mode .user-footer .btn-default:hover {
            background-color: var(--ui-hover);
            border-color: var(--ui-border);
        }
        
        /* 6. Textos Muted (Fecha) en modo oscuro */
        body.dark-mode .text-muted { color: var(--ui-text-muted) !important; }

    </style>
    
    @stack('styles')
    @livewireStyles
</head>

<body class="hold-transition sidebar-mini layout-fixed text-sm">
    <a href="#main-content" class="skip-to-content">Ir al contenido principal</a>

    {{-- SCRIPT ANTI-PARPADEO --}}
    <script>
        (function () {
            var isDark = document.documentElement.getAttribute('data-theme') === 'dark';
            document.body.classList.toggle('dark-mode', isDark);
            var nav = document.querySelector('.main-header');
            if (nav) {
                nav.classList.toggle('navbar-dark', isDark);
                nav.classList.toggle('navbar-white', !isDark);
                nav.classList.toggle('navbar-light', !isDark);
            }
        })();
    </script>

    <div class="wrapper">

        {{-- NAVBAR SUPERIOR --}}
        <nav class="main-header navbar navbar-expand navbar-white navbar-light shadow-sm border-0">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#" role="button" aria-label="Mostrar u ocultar menú lateral"><i class="fas fa-bars" aria-hidden="true"></i></a>
                </li>
            </ul>

            <span class="text-muted ml-2 d-none d-sm-inline-block">
                <i class="far fa-calendar-alt mr-1"></i> {{ now()->format('d/m/Y') }} 
                <span id="reloj" class="font-weight-bold ml-1"></span>
            </span>

            <ul class="navbar-nav ml-auto">
                {{-- PANTALLA COMPLETA (documento) --}}
                <li class="nav-item">
                    <a class="nav-link" href="#" role="button" id="fullscreenToggle" title="Ver en pantalla completa" aria-label="Ver en pantalla completa">
                        <i class="fas fa-expand" id="fullscreenIcon" aria-hidden="true"></i>
                    </a>
                </li>
                {{-- BOTÓN MODO OSCURO / CLARO --}}
                <li class="nav-item">
                    <a class="nav-link" href="#" role="button" id="darkModeTrigger" title="Cambiar apariencia" aria-label="Cambiar entre modo claro y oscuro">
                        <i class="fas fa-moon" aria-hidden="true"></i>
                    </a>
                </li>
                
                @auth
                    @can('read panel notifications')
                        @livewire('admin.notifications.bell')
                    @endcan
                @endauth

                {{-- USUARIO (MEJORADO) --}}
                <li class="nav-item dropdown user user-menu">
                    <a href="#" class="nav-link dropdown-toggle user-link d-flex align-items-center" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" aria-label="Menú de usuario: {{ auth()->user()->name }}">
                        {{-- Avatar pequeño --}}
                        <div class="user-avatar-circle mr-2 shadow-sm" style="width: 30px; height: 30px; font-size: 14px; border: 2px solid white;" aria-hidden="true">
                            {{ substr(auth()->user()->name, 0, 1) }}
                        </div>
                        <span class="hidden-xs font-weight-bold text-dark user-name-responsive mr-1" style="color: inherit !important;">
                            {{ auth()->user()->name }}
                        </span>
                        <i class="fas fa-chevron-down opacity-50" style="font-size: 10px;" aria-hidden="true"></i>
                    </a>
                    
                    <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right border-0 shadow-lg mt-2 p-0">
                        {{-- Header Azul / Gris Oscuro --}}
                        <li class="user-header bg-primary d-flex flex-column justify-content-center align-items-center">
                            <div class="user-avatar-circle shadow elevation-2 mb-2" style="width: 75px; height: 75px; font-size: 32px; border: 3px solid rgba(255,255,255,0.3); flex-shrink: 0;">
                                {{ substr(auth()->user()->name, 0, 1) }}
                            </div>
                            
                            {{-- Información de texto --}}
                            <p class="mb-0 text-wrap text-center" style="line-height: 1.2;">
                                <span class="font-weight-bold" style="font-size: 1.1rem;">
                                    {{ auth()->user()->name }} {{ auth()->user()->ap_paterno }}
                                </span>
                                <small class="d-block mt-1 font-weight-light opacity-75">
                                    {{ auth()->user()->roles->first()->name ?? 'Usuario' }}
                                </small>
                            </p>
                            
                            <div class="mt-2 small opacity-75" style="color: inherit;">
                                <i class="far fa-calendar-check mr-1"></i> Miembro desde {{ auth()->user()->created_at->translatedFormat('M. Y') }}
                            </div>
                        </li>
                        
                        {{-- Footer Botones --}}
                        <li class="user-footer">
                            <div class="row">
                                <div class="col-6">
                                    <a href="{{ route('profile') }}" class="btn btn-default btn-flat btn-block shadow-sm rounded">
                                        <i class="fas fa-user-cog text-primary mr-1"></i> Perfil
                                    </a>
                                </div>
                                <div class="col-6">
                                    <form id="logout-form" action="{{ route('logout') }}" method="POST">
                                        @csrf
                                        <button class="btn btn-default btn-flat btn-block shadow-sm rounded">
                                            <i class="fas fa-sign-out-alt text-danger mr-1"></i> Salir
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </li>
                    </ul>
                </li>
            </ul>
        </nav>

        {{-- SIDEBAR --}}
        <aside class="main-sidebar sidebar-dark-primary elevation-4">
            <a href="{{ auth()->user()->can('read services') ? route('home') : route('profile') }}" class="brand-link">
                <img id="img-logo-nav" src="{{ asset('adminlte/img/logo.png') }}" alt="Logo" class="brand-image">
                <span class="brand-text">HelpDesk <b>ECD</b></span>
            </a>

            <div class="sidebar">
                <div class="user-panel mt-3 pb-3 mb-3 d-flex align-items-center border-bottom-0">
                    <div class="image">
                        <div class="user-avatar-circle" style="width: 35px; height: 35px; font-size: 16px; background-color: #4b545c; border: none;">
                             {{ substr(auth()->user()->name, 0, 1) }}
                        </div>
                    </div>
                    <div class="info pl-2">
                        <a href="#" class="d-block font-weight-bold text-wrap" style="line-height: 1.2;">
                            {{ auth()->user()->name }} <br> {{ auth()->user()->ap_paterno }}
                        </a>
                    </div>
                </div>
                @include('admin.partials.nav')
            </div>
        </aside>

        {{-- CONTENIDO PRINCIPAL --}}
        <div class="content-wrapper">
            <div class="content-header">
                @yield('header')
            </div>
            <div class="content" id="main-content" tabindex="-1">
                @if (session()->has('flash'))
                    <div class="alert alert-success alert-dismissible fade show shadow-sm border-0" role="alert">
                        <i class="fas fa-check-circle mr-2"></i> {{ session('flash') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                @endif
                @if (session()->has('message'))
                    <div class="alert alert-success alert-dismissible fade show shadow-sm border-0" role="alert">
                        <i class="fas fa-check-circle mr-2"></i> {{ session('message') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                @endif
                @if (session()->has('error'))
                    <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0" role="alert">
                        <i class="fas fa-exclamation-triangle mr-2"></i> {{ session('error') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                @endif
                @if(isset($errors) && $errors->any())
                    <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0" role="alert">
                        <i class="fas fa-exclamation-circle mr-2"></i> {{ $errors->first() }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                @endif
                @isset($passwordExpiryBanner)
                    @if($passwordExpiryBanner)
                        <div class="alert alert-warning alert-dismissible fade show shadow-sm border-0" role="alert">
                            <i class="fas fa-key mr-2"></i>
                            <strong>Contraseña</strong>:
                            caduca el {{ $passwordExpiryBanner['expires_at']->format('d/m/Y') }}.
                            <a href="{{ $passwordExpiryBanner['profile_url'] }}" class="alert-link font-weight-bold">Cámbiala en tu perfil</a>.
                            <button type="button" class="close" data-dismiss="alert" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                        </div>
                    @endif
                @endisset

                {{-- Livewire 3 (página completa con config layout) usa $slot; vistas clásicas usan @section('content') --}}
                @isset($slot)
                    {{ $slot }}
                @else
                    @yield('content')
                @endisset
            </div>
        </div>
        
        <footer class="main-footer">
            <div class="float-right d-none d-sm-inline"><b>Ver.</b> 3.0</div>
            <strong>&copy; {{ now()->year }} ECD S.A. DE C.V.</strong>
        </footer>
    </div>

    {{-- Modal: aviso de cierre de sesión por inactividad (5 min antes de los 20 min) --}}
    @auth
    <div class="modal fade" id="inactivityModal" tabindex="-1" role="dialog" aria-labelledby="inactivityModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-warning">
                <div class="modal-header alert-warning mb-0">
                    <h5 class="modal-title" id="inactivityModalLabel">
                        <i class="fas fa-clock mr-2"></i> Aviso de inactividad
                    </h5>
                </div>
                <div class="modal-body">
                    <p class="mb-0">Tu sesión se cerrará por inactividad en <strong>5 minutos</strong>. Si no haces nada, tendrás que volver a iniciar sesión.</p>
                    <p class="mb-0 mt-2 text-muted small">Haz clic en <strong>Continuar con sesión</strong> para seguir trabajando.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="inactivityContinueBtn">
                        <i class="fas fa-check mr-1"></i> Continuar con sesión
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endauth

    {{-- SCRIPTS --}}
    <script src="{{ asset('adminlte/plugins/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('adminlte/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js') }}"></script>
    <script src="{{ asset('adminlte/dist/js/adminlte.js') }}"></script>
    @livewireScripts
    @stack('scripts')
    <script src="{{ asset('js/sistema/reloj.js') }}"></script>
    @stack('modals')

    {{-- LÓGICA DE TEMA (MODO OSCURO + GRÁFICAS) --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var body = document.body;
            var nav = document.querySelector('.main-header');
            var trigger = document.getElementById('darkModeTrigger');
            var icon = trigger ? trigger.querySelector('i') : null;
            var media = window.matchMedia ? window.matchMedia('(prefers-color-scheme: dark)') : null;

            function updateCharts(isDark) {
                if (typeof Chart !== 'undefined' && Chart.defaults && Chart.defaults.global) {
                    Chart.defaults.global.defaultFontColor = isDark ? '#ced4da' : '#495057';
                    if (Chart.helpers && Chart.instances) {
                        Chart.helpers.each(Chart.instances, function (instance) {
                            instance.update(0);
                        });
                    }
                }
            }

            function setTheme(isDark, persist) {
                document.documentElement.setAttribute('data-theme', isDark ? 'dark' : 'light');
                document.documentElement.classList.toggle('dark-mode', isDark);
                body.classList.toggle('dark-mode', isDark);

                if (nav) {
                    nav.classList.toggle('navbar-dark', isDark);
                    nav.classList.toggle('navbar-white', !isDark);
                    nav.classList.toggle('navbar-light', !isDark);
                }

                if (icon) {
                    icon.classList.toggle('fa-sun', isDark);
                    icon.classList.toggle('fa-moon', !isDark);
                }

                updateCharts(isDark);

                if (persist) {
                    localStorage.setItem('theme', isDark ? 'dark' : 'light');
                }
            }

            var saved = localStorage.getItem('theme');
            var isDark = saved ? saved === 'dark' : !!(media && media.matches);
            setTheme(isDark, false);

            if (trigger) {
                trigger.addEventListener('click', function (e) {
                    e.preventDefault();
                    setTheme(!body.classList.contains('dark-mode'), true);
                });
            }

            if (media && typeof media.addEventListener === 'function') {
                media.addEventListener('change', function (evt) {
                    if (!localStorage.getItem('theme')) {
                        setTheme(!!evt.matches, false);
                    }
                });
            }

            if (window.jQuery) {
                $('.user.user-menu > .dropdown-toggle').on('show.bs.dropdown', function () {
                    this.setAttribute('aria-expanded', 'true');
                }).on('hide.bs.dropdown', function () {
                    this.setAttribute('aria-expanded', 'false');
                });
            }

            var fsBtn = document.getElementById('fullscreenToggle');
            var fsIcon = document.getElementById('fullscreenIcon');
            if (fsBtn) {
                function getFullscreenElement() {
                    return document.fullscreenElement || document.webkitFullscreenElement || document.msFullscreenElement;
                }
                function updateFullscreenUi() {
                    var on = !!getFullscreenElement();
                    if (fsIcon) {
                        fsIcon.className = on ? 'fas fa-compress' : 'fas fa-expand';
                    }
                    var label = on ? 'Salir de pantalla completa' : 'Ver en pantalla completa';
                    fsBtn.setAttribute('title', label);
                    fsBtn.setAttribute('aria-label', label);
                }
                fsBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    var doc = document;
                    if (getFullscreenElement()) {
                        if (doc.exitFullscreen) {
                            doc.exitFullscreen();
                        } else if (doc.webkitExitFullscreen) {
                            doc.webkitExitFullscreen();
                        } else if (doc.msExitFullscreen) {
                            doc.msExitFullscreen();
                        }
                    } else {
                        var el = document.documentElement;
                        var p = null;
                        if (el.requestFullscreen) {
                            p = el.requestFullscreen();
                        } else if (el.webkitRequestFullscreen) {
                            el.webkitRequestFullscreen();
                        } else if (el.msRequestFullscreen) {
                            el.msRequestFullscreen();
                        }
                        if (p && typeof p.catch === 'function') {
                            p.catch(function () {});
                        }
                    }
                });
                document.addEventListener('fullscreenchange', updateFullscreenUi);
                document.addEventListener('webkitfullscreenchange', updateFullscreenUi);
                document.addEventListener('MSFullscreenChange', updateFullscreenUi);
            }
        });
    </script>

    {{-- Cierre de sesión por inactividad: aviso a los 15 min, cierre a los 20 min --}}
    @auth
    <script>
        (function() {
            var IDLE_WARN_MS = 15 * 60 * 1000;   // 15 min: mostrar aviso
            var IDLE_LOGOUT_MS = 5 * 60 * 1000;  // 5 min más: redirigir a logout (total 20 min)
            var keepaliveUrl = "{{ route('session.keepalive') }}";
            var logoutUrl = "{{ route('session.logout-inactivity') }}";

            var idleTimer = null;
            var countdownTimer = null;
            var modalShown = false;

            function resetIdleTimer() {
                if (idleTimer) clearTimeout(idleTimer);
                if (countdownTimer) clearTimeout(countdownTimer);
                idleTimer = setTimeout(showInactivityModal, IDLE_WARN_MS);
                modalShown = false;
            }

            function showInactivityModal() {
                modalShown = true;
                var $modal = $('#inactivityModal');
                if ($modal.length) {
                    $modal.modal('show');
                    countdownTimer = setTimeout(function() {
                        $modal.modal('hide');
                        window.location.href = logoutUrl;
                    }, IDLE_LOGOUT_MS);
                }
            }

            function continueSession() {
                if (countdownTimer) clearTimeout(countdownTimer);
                $('#inactivityModal').modal('hide');
                var xhr = new XMLHttpRequest();
                xhr.open('GET', keepaliveUrl, true);
                xhr.withCredentials = true;
                xhr.onload = function() {
                    resetIdleTimer();
                };
                xhr.send();
            }

            $(document).ready(function() {
                resetIdleTimer();
                $(document).on('mousemove keydown click scroll touchstart', function() {
                    if (!modalShown) resetIdleTimer();
                });
                $('#inactivityContinueBtn').on('click', function() {
                    continueSession();
                });
            });
        })();
    </script>
    @endauth
</body>
</html>