@extends('layouts.panel')
@section('content')
    @livewire('tribunal-profile', ['tribunalId' => $tribunalId])
@endsection
