@extends('layouts.panel')

@section('content')
    {{-- Pasa el ID de la rúbrica al componente Livewire si está presente --}}
    @livewire('rubricas.create', ['rubricaId' => $id ?? null])
@endsection
