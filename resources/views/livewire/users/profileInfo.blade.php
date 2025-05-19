@extends('layouts.panel')

@section('content')
    @php
        $userId = $user->id;
        $name = $user->name;
        $email = $user->email;
    @endphp
    @livewire('users.profile', ['userId' => $userId, 'user' => $user, 'name' => $name, 'email' => $email, 'roles' => $roles])
@endsection
