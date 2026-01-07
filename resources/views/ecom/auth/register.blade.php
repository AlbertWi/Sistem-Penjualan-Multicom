@extends('layouts.catalog')

@section('title', 'Register - ' . config('app.name'))

@section('content')
<div class="container">
    <div class="auth-container">
        <h3>Register</h3>

        <form method="POST" action="{{ route('customer.register') }}">
            @csrf

            <div class="form-group">
                <label>Nama</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="form-group">
                <label>No Hp</label>
                <input type="phone" name="phone" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>

            <div class="form-group">
                <label>Konfirmasi Password</label>
                <input type="password" name="password_confirmation" class="form-control" required>
            </div>

            <button type="submit" href="{{ route('customer.login') }}" class="btn btn-success btn-block">Daftar</button>

            <div class="text-center mt-3">
                Sudah punya akun?
                <a href="{{ route('customer.login') }}" class="auth-link">Login</a>
            </div>
        </form>
    </div>
</div>
@endsection