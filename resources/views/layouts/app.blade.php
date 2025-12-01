<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - {{ $schoolName ?? 'Medical School' }}</title>
    <link rel="stylesheet" href="{{ asset('css/neo-brutal.css') }}">
    @stack('styles')
</head>
<body>
    <!-- Header -->
    <header class="site-header">
        <div class="header-top">
            <div class="header-logo">
                @if(!empty($schoolLogo))
                    <img src="{{ asset('storage/' . $schoolLogo) }}" alt="{{ $schoolName ?? 'Medical School' }}">
                @endif
                <div class="header-brand">
                    <h1>{{ $schoolName ?? 'Medical School' }}</h1>
                    <p>{{ $schoolTagline ?? 'File Management System' }}</p>
                </div>
            </div>
            @auth
            <div class="header-search" style="flex: 1; max-width: 400px; margin: 0 var(--spacing-md);">
                <form action="{{ route('search') }}" method="GET" style="width: 100%;">
                    <input
                        type="text"
                        name="q"
                        class="form-control"
                        placeholder="Search files and folders..."
                        style="width: 100%; margin: 0;"
                    >
                </form>
            </div>
            @endauth
            <div class="header-user">
                @auth
                    <span><strong>{{ auth()->user()->name }}</strong></span>
                    <span class="user-badge">{{ strtoupper(auth()->user()->role) }}</span>
                @endauth
                <button class="mobile-nav-toggle" onclick="toggleNav()">MENU</button>
            </div>
        </div>

        @auth
        <nav class="main-nav" id="mainNav">
            <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">Dashboard</a>
            <a href="{{ route('shared.index') }}" class="nav-link {{ request()->routeIs('shared.*') ? 'active' : '' }}">Shared Files</a>
            <a href="{{ route('folders.index') }}" class="nav-link {{ request()->routeIs('folders.*') ? 'active' : '' }}">My Folders</a>
            <a href="{{ route('files.index') }}" class="nav-link {{ request()->routeIs('files.*') ? 'active' : '' }}">My Files</a>

            @can('access-archive')
            <a href="{{ route('archive.index') }}" class="nav-link {{ request()->routeIs('archive.*') ? 'active' : '' }}">Archive</a>
            @endcan

            @can('admin')
            <a href="{{ route('admin.index') }}" class="nav-link {{ request()->routeIs('admin.*') ? 'active' : '' }}">Admin</a>
            @endcan

            <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                @csrf
                <button type="submit" class="nav-link" style="cursor: pointer; background: #E63946;">Logout</button>
            </form>
        </nav>
        @endauth
    </header>

    <!-- Main Content -->
    <main style="min-height: calc(100vh - 300px); padding: var(--spacing-lg) 0;">
        <div class="container">
            <!-- Flash Messages -->
            @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
            @endif

            @if(session('error'))
            <div class="alert alert-error">
                {{ session('error') }}
            </div>
            @endif

            @if($errors->any())
            <div class="alert alert-error">
                <strong>Please fix the following errors:</strong>
                <ul style="margin: var(--spacing-xs) 0 0 var(--spacing-md);">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            @yield('content')
        </div>
    </main>

    <!-- Footer -->
    <footer class="site-footer">
        <p>{{ $schoolFooter ?? '© 2024 Medical School. All rights reserved.' }}</p>
        <p style="font-size: 0.875rem; margin-top: var(--spacing-xs);">
            Storage Provider: <strong>{{ strtoupper(env('STORAGE_PROVIDER', 'local')) }}</strong>
        </p>
    </footer>

    <script>
        function toggleNav() {
            const nav = document.getElementById('mainNav');
            nav.classList.toggle('active');
        }

        // Close mobile nav when clicking outside
        document.addEventListener('click', function(event) {
            const nav = document.getElementById('mainNav');
            const toggle = document.querySelector('.mobile-nav-toggle');

            if (nav && !nav.contains(event.target) && !toggle.contains(event.target)) {
                nav.classList.remove('active');
            }
        });
    </script>

    @stack('scripts')
</body>
</html>
