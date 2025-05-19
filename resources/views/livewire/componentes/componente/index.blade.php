@extends('layouts.panel')
@section('content')
    @livewire('componentes.componente.view', ['componenteId' => $componenteId])
@endsection
