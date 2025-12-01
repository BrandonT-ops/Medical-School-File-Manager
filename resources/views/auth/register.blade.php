@extends('layouts.app')

@section('title', 'Register')

@section('content')
<div style="max-width: 500px; margin: var(--spacing-xl) auto;">
    <div class="card">
        <div class="card-header">
            <h2>Register</h2>
        </div>
        <div class="card-body" style="padding: var(--spacing-md);">
            <form action="{{ route('register') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label for="name" class="form-label required">Full Name</label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        class="form-control"
                        value="{{ old('name') }}"
                        placeholder="John Doe"
                        required
                        autofocus
                    >
                    <span class="form-help">Enter your full name</span>
                </div>

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
                    >
                    <span class="form-help">We'll never share your email</span>
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
                    <span class="form-help">Minimum 8 characters</span>
                </div>

                <div class="form-group">
                    <label for="password_confirmation" class="form-label required">Confirm Password</label>
                    <input
                        type="password"
                        id="password_confirmation"
                        name="password_confirmation"
                        class="form-control"
                        placeholder="••••••••"
                        required
                    >
                    <span class="form-help">Re-enter your password</span>
                </div>

                <button type="submit" class="btn btn-accent btn-block mt-3">
                    Register →
                </button>
            </form>
        </div>
        <div class="card-footer text-center">
            <p style="margin: 0;">
                Already have an account?
                <a href="{{ route('login') }}" style="font-weight: 700;">Login here</a>
            </p>
        </div>
    </div>
</div>
@endsection
