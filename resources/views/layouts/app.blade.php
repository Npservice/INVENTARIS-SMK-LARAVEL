<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — Inventaris SMK Annur</title>
    <link rel="stylesheet" href="{{ asset('assets/css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/fontawesome/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/sweetalert2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/ui.css') }}">
</head>
<body class="min-h-screen bg-slate-50 font-sans text-slate-700 antialiased">
    <input id="menu-toggle" type="checkbox" class="peer sr-only">
    <label for="menu-toggle" class="fixed inset-0 z-30 hidden bg-slate-950/50 peer-checked:block lg:peer-checked:hidden" aria-label="Tutup menu"></label>
    @include('partials.sidebar')
    <div class="min-h-screen lg:pl-64">
        @include('partials.header')
        <main class="mx-auto max-w-[1600px] px-5 py-7 sm:px-8 lg:px-10 lg:py-9">
            @yield('content')
        </main>
        @if (request()->routeIs('inventaris.index', 'inventaris.show'))
            @include('partials.inventory-modal')
        @endif
        <footer class="mx-auto max-w-[1600px] px-5 pb-8 text-xs text-slate-400 sm:px-8 lg:px-10">© {{ date('Y') }} Inventaris SMK Annur. Dibuat untuk pengelolaan yang lebih mudah.</footer>
    </div>
    <script src="{{ asset('assets/js/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/js/sweetalert2.all.min.js') }}"></script>
    <script>window.AppPermissions = @json(auth()->user()->getAllPermissions()->pluck('name')->values());</script>
    <script src="{{ asset('assets/js/ui.js') }}"></script>
    <script src="{{ asset('assets/js/rupiah.js') }}"></script>
    @stack('scripts')
</body>
</html>
