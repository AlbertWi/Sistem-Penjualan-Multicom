@extends('layouts.catalog')

@section('title', 'Login - ' . config('app.name'))

@section('content')
<div class="container">
    <div class="auth-container">
        <h3>Login</h3>
        @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        <form method="POST" action="{{ route('ecom.login') }}">
            @csrf

            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-primary btn-block">Login</button>

            <div class="text-center mt-3">
                Belum punya akun?
                <a href="{{ route('ecom.register') }}" class="auth-link">Daftar</a>
            </div>
        </form>
    </div>
</div>
@endsection