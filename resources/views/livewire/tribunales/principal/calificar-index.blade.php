@extends('layouts.panel')
@section('content')
    @livewire('tribunales-calificar', ['tribunalId'=>$tribunalId])
@endsection
