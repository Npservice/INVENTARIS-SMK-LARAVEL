<header class="sticky top-0 z-20 flex h-20 items-center justify-between border-b border-slate-200 bg-white/95 px-5 backdrop-blur sm:px-8 lg:px-10">
    <div class="flex items-center gap-4">
        <label for="menu-toggle" class="cursor-pointer rounded-lg p-2 text-xl text-slate-600 hover:bg-slate-100 lg:hidden" aria-label="Buka menu"><i class="fas fa-bars"></i></label>
        <div><p class="text-xs font-medium text-slate-400">Panel administrasi <span class="mx-1">/</span> {{ trim($__env->yieldContent('title', 'Dashboard')) }}</p><h1 class="mt-0.5 text-lg font-bold tracking-tight text-slate-900">@yield('title', 'Dashboard')</h1></div>
    </div>
    <div class="flex items-center gap-3">
        <span class="hidden rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 sm:inline-flex">● Sistem aktif</span>
        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-indigo-100 text-sm font-bold text-indigo-700">{{ Str::of(auth()->user()->name)->substr(0, 2)->upper() }}</div>
        <div class="hidden text-left sm:block"><p class="text-sm font-semibold text-slate-900">{{ auth()->user()->name }}</p><p class="text-xs text-slate-400">{{ auth()->user()->status }}</p></div>
        <form action="{{ route('logout') }}" method="post">
            @csrf
            <button type="submit" class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 hover:text-red-600" title="Keluar"><i class="fas fa-arrow-right-from-bracket"></i></button>
        </form>
    </div>
</header>
