@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>Lista de Roles</h2>

        <ul>
            @foreach ($roles as $role)
                <li>{{ $role->name }}</li>
            @endforeach
        </ul>
    </div>
@endsection
