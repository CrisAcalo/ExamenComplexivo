@extends('layouts.panel')

@section('title', 'Gestionar Plan de Evaluación') {{-- Título para la pestaña del navegador --}}

@section('content')
    <div class="container-fluid p-0">
        {{-- Pasamos el carreraPeriodoId que viene del controlador al componente Livewire --}}
        @livewire('plan-evaluacion-manager', ['carreraPeriodoId' => $carreraPeriodoId])
    </div>
@endsection
