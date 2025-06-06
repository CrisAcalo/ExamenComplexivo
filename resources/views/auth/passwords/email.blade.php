@extends('layouts.app')

@section('content')
<div class="container-fluid p-0 m-0">
    <div class="row justify-content-center align-items-center vh-100">
        <div class="col-md-4">
            <div class="col-md-4 text-center" style='width:100%'>
                <img src="{{ Storage::url('iconos/logo.png') }}" alt="Image" style="width:100%;height:100px;object-fit:contain">
            </div>
            <br>
            <div class="card p-0 m-0" style="background:#00000000">
                <div class="card-header text-bg-light">{{ __('Reset Password') }}</div>

                <div class="card-body" style="backdrop-filter: blur(2px);background:#00000033">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('password.email') }}">
                        @csrf

                        <div class="row mb-3 text-white">
                            <label for="email" class="col-md-4 col-form-label text-md-end">{{ __('Email Address') }}</label>

                            <div class="col-md-6">
                                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>

                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Send Password Reset Link') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
