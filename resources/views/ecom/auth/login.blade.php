@extends('layouts.catalog')

@section('content')
<div class="container col-md-4 mt-5">
    <h3>Login</h3>

    <form method="POST" action="{{ route('ecom.login') }}">
        @csrf

        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>

        <button class="btn btn-primary w-100">Login</button>

        <div class="text-center mt-3">
            Belum punya akun?
            <a href="{{ route('ecom.register') }}">Daftar</a>
        </div>
    </form>
</div>
@endsection
