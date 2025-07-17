@extends('layouts.app')

@section('content')
    <div class="container-fluid p-0 m-0">
        <div class="row justify-content-center align-items-center vh-100 p-0 m-0 w-100">
            <div class="col-md-4 w-100">
                {{-- <div class="col-md-4 text-center" style='width:100%'>
                    <img src="{{ Storage::url('iconos/logo.png') }}" alt="Image"
                        style="width:100%;height:100px;object-fit:contain">
                </div> --}}
                <div class="card py-4 px-3 m-0 mx-auto text-light" style="background:#444444d8;max-width:450px;width:90%">
                    <div class="text-center text-light fw-bold" style="font-size: 80px; z-index: 100;">
                        <h1>Login</h1>
                    </div>
                    <div class="card-body" style="backdrop-filter: blur(2px);">
                        <div class="row mb-8 ">
                            <div class="col-md-10 ">
                                <form method="POST" action="{{ route('login') }}">
                                    @csrf
                                    <div class="row mb-6">
                                        <label for="email"
                                            class="col-md-4 col-form-label text-md-end text-white">{{ __('Correo') }}</label>

                                        <div class="col-md-8 p-0 m-0">
                                            <input id="email" type="email"
                                                class="form-control @error('email') is-invalid @enderror" name="email"
                                                value="{{ old('email') }}" required autocomplete="email" autofocus>

                                            @error('email')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                    <br>

                                    <div class="row mb-6">
                                        <label for="password"
                                            class="col-md-4 col-form-label text-md-end text-white">{{ __('Contraseña') }}</label>

                                        <div class="col-md-8 p-0 m-0">
                                            <input id="password" type="password"
                                                class="form-control @error('password') is-invalid @enderror" name="password"
                                                required autocomplete="current-password">

                                            @error('password')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                    <br>

                                    <div class="row mb-4">
                                        <div class="col-md-8 offset-md-4">
                                            <div class="form-check text-white">
                                                <input class="form-check-input" style="height:20px; width:20px;"
                                                    type="checkbox" name="remember" id="remember"
                                                    {{ old('remember') ? 'checked' : '' }}>
                                                <label class="form-check-label"for="remember">{{ __('Recordar') }}</label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mb-0">
                                        <div class="col-md-10 offset-md-2">
                                            <button type="submit" class="btn btn-primary">{{ __('Login') }}</button>

                                            @if (Route::has('password.request'))
                                                <a class="btn btn-link text-white" href="{{ route('password.request') }}">
                                                    {{ __('¿Olvidaste tu contraseña?') }}
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
