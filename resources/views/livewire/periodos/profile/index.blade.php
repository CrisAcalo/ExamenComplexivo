@extends('layouts.panel')
@section('content')
    @livewire('periodos.profile', ['periodoId' => $periodoId])
@endsection
