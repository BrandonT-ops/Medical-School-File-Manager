@extends('layouts.app')

@section('title', 'Login')

@section('content')
<div style="max-width: 500px; margin: var(--spacing-xl) auto;">
    <div class="card">
        <div class="card-header">
            <h2>Login</h2>
        </div>
        <div class="card-body" style="padding: var(--spacing-md);">
            <form action="{{ route('login') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label for="email" class="form-label required">Email Address</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        class="form-control"
                        value="{{ old('email') }}"
                        placeholder="your.email@example.com"
                        required
                        autofocus
                    >
                    <span class="form-help">Enter your registered email address</span>
                </div>

                <div class="form-group">
                    <label for="password" class="form-label required">Password</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-control"
                        placeholder="••••••••"
                        required
                    >
                    <span class="form-help">Enter your password</span>
                </div>

                <div class="form-check">
                    <input
                        type="checkbox"
                        id="remember"
                        name="remember"
                        class="form-check-input"
                    >
                    <label for="remember">Remember me</label>
                </div>

                <button type="submit" class="btn btn-primary btn-block mt-3">
                    Login →
                </button>
            </form>
        </div>
        <div class="card-footer text-center">
            <p style="margin: 0;">
                Don't have an account?
                <a href="{{ route('register') }}" style="font-weight: 700;">Register here</a>
            </p>
        </div>
    </div>
</div>
@endsection
