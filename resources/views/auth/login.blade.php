<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Masuk - Inventaris SMK Annur</title>
<link rel="stylesheet" href="{{ asset('assets/css/app.css') }}">
<link rel="stylesheet" href="{{ asset('assets/fontawesome/css/all.min.css') }}">
</head>
<body class="min-h-screen bg-slate-50 font-sans text-slate-700 antialiased">
<div class="grid min-h-screen lg:grid-cols-2">
<div class="hidden flex-col justify-between bg-slate-900 p-12 text-white lg:flex">
<a href="{{ route('dashboard') }}" class="flex items-center gap-3">
<span class="flex h-11 w-11 items-center justify-center rounded-xl bg-indigo-500 text-xl font-black">I</span>
<span class="text-lg font-bold">Inventaris SMK Annur</span>
</a>
<div class="max-w-xl">
<span class="rounded-full border border-white/20 px-4 py-2 text-xs font-semibold tracking-wide text-indigo-200">SISTEM INVENTARIS SEKOLAH</span>
<h1 class="mt-7 text-5xl font-bold leading-tight tracking-tight">Kelola aset sekolah dengan lebih mudah.</h1>
<p class="mt-5 text-base leading-8 text-slate-300">Semua inventaris, peminjaman, dan laporan dalam satu ruang kerja yang tertata.</p>
</div>
<p class="text-sm text-slate-400">&copy; {{ date('Y') }} SMK Annur</p>
</div>
<main class="flex items-center justify-center px-6 py-12 sm:px-12">
<div class="w-full max-w-md">
<div class="mb-10 flex items-center gap-3 lg:hidden">
<span class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-600 font-black text-white">I</span>
<strong class="text-slate-900">Inventaris SMK Annur</strong>
</div>
<p class="text-xs font-bold uppercase tracking-[.2em] text-indigo-600">Selamat datang</p>
<h2 class="mt-3 text-3xl font-bold tracking-tight text-slate-900">Masuk ke akun Anda</h2>
<p class="mt-3 text-sm leading-6 text-slate-500">Gunakan akun yang diberikan oleh administrator sekolah.</p>
@if ($errors->any())
<div class="mt-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-600">
{{ $errors->first() }}
</div>
@endif
<form class="mt-9 space-y-5" action="{{ route('login') }}" method="post">
@csrf
<label class="block text-sm font-semibold text-slate-700">Nama pengguna<input type="text" name="username" value="{{ old('username') }}" autocomplete="username" placeholder="Masukkan nama pengguna" class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-4 py-3.5 text-sm font-normal outline-none focus:border-indigo-500" required autofocus>
</label>
<label class="block text-sm font-semibold text-slate-700">Kata sandi<input type="password" name="password" autocomplete="current-password" placeholder="Masukkan kata sandi" class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-4 py-3.5 text-sm font-normal outline-none focus:border-indigo-500" required>
</label>
<div class="flex items-center justify-between text-sm">
<label class="flex items-center gap-2 text-slate-500">
<input type="checkbox" name="remember" class="rounded border-slate-300"> Ingat saya</label>
</div>
<button type="submit" class="w-full rounded-xl bg-indigo-600 px-5 py-3.5 text-sm font-bold text-white shadow-lg shadow-indigo-100 hover:bg-indigo-700">Masuk ke dashboard <i class="fas fa-arrow-right"></i></button>
</form>
</div>
</main>
</div>
</body>
</html>

