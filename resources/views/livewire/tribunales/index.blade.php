@extends('layouts.panel')
@section('content')
<div class="container-fluid p-0">
    <div class="row justify-content-center p-0">
        <div class="col-md-12">
            @livewire('tribunales', ['carreraPeriodoId' => $carreraPeriodoId])
        </div>
    </div>
</div>
@endsection
