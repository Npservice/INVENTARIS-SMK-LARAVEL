@php
    $menu = [
        ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'fas fa-th-large', 'permission' => 'dashboard.index'],
        ['label' => 'Inventaris', 'route' => 'inventaris.index', 'icon' => 'fas fa-boxes', 'permission' => 'inventaris.index'],
        ['label' => 'Gudang', 'route' => 'gudang', 'icon' => 'fas fa-warehouse', 'permission' => 'inventaris.index'],
        ['label' => 'Peminjaman', 'route' => 'peminjaman', 'icon' => 'fas fa-exchange-alt', 'permission' => 'peminjaman.index'],
        ['label' => 'Usulan & Saran', 'route' => 'usulan', 'icon' => 'fas fa-comment-dots', 'permission' => 'kritik-saran.index'],
    ];
@endphp
<aside class="fixed inset-y-0 left-0 z-40 flex w-64 -translate-x-full flex-col border-r border-slate-200 bg-white transition-transform duration-200 peer-checked:translate-x-0 lg:translate-x-0">
    <a href="{{ route('dashboard') }}" class="flex h-20 items-center gap-3 border-b border-slate-100 px-6">
        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-600 text-xl font-black text-white shadow-lg shadow-indigo-200">I</span>
        <span><strong class="block text-sm font-extrabold tracking-tight text-slate-900">Inventaris</strong><small class="block text-xs font-medium text-slate-400">SMK Annur</small></span>
    </a>
    <div class="flex-1 overflow-y-auto px-3 py-6">
        <p class="px-4 pb-3 text-[10px] font-bold uppercase tracking-[.18em] text-slate-400">Menu utama</p>
        <nav class="space-y-1" aria-label="Navigasi utama">
            @foreach ($menu as $item)
                @can($item['permission'])
                <a href="{{ route($item['route']) }}" class="flex items-center gap-3 rounded-xl px-4 py-2 text-sm font-medium transition {{ request()->routeIs($item['route']) ? 'bg-indigo-50 font-semibold text-indigo-700' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">
                    <span class="w-6 text-center" aria-hidden="true"><i class="{{ $item['icon'] }}"></i></span>
                    <span>{{ $item['label'] }}</span>
                </a>
                @endcan
            @endforeach
        </nav>
        @canany(['instansi.index', 'lokasi.index', 'jenis.index', 'pendanaan.index'])
        <div class="master-nav-section">
            <p class="master-nav-heading">Master Data</p>
            <nav class="space-y-1" aria-label="Master Data">
                @foreach ([
                    ['Instansi', 'master.instansi', 'fas fa-school', 'instansi.index'],
                    ['Lokasi', 'master.lokasi', 'fas fa-map-marker-alt', 'lokasi.index'],
                    ['Jenis', 'master.jenis', 'fas fa-tags', 'jenis.index'],
                    ['Pendanaan', 'master.pendanaan', 'fas fa-wallet', 'pendanaan.index'],
                ] as [$label, $route, $icon, $permission])
                    @can($permission)
                    <a href="{{ route($route) }}" class="flex items-center gap-3 rounded-xl px-4 py-2 text-sm font-medium transition {{ request()->routeIs($route) ? 'bg-indigo-50 font-semibold text-indigo-700' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">
                        <span class="w-6 text-center" aria-hidden="true"><i class="{{ $icon }}"></i></span>
                        <span>{{ $label }}</span>
                    </a>
                    @endcan
                @endforeach
            </nav>
        </div>
        @endcanany
        @canany(['user.index', 'role.index'])
        <div class="master-nav-section">
            <p class="master-nav-heading">Manajemen Akses</p>
            <nav class="space-y-1" aria-label="Manajemen Akses">
                @foreach ([
                    ['Pengguna', 'user', 'fas fa-users', 'user.index'],
                    ['Role & Permission', 'role.index', 'fas fa-user-shield', 'role.index'],
                ] as [$label, $route, $icon, $permission])
                    @can($permission)
                    <a href="{{ route($route) }}" class="flex items-center gap-3 rounded-xl px-4 py-2 text-sm font-medium transition {{ request()->routeIs($route, $route === 'user' ? 'user.show' : 'role.show') ? 'bg-indigo-50 font-semibold text-indigo-700' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">
                        <span class="w-6 text-center" aria-hidden="true"><i class="{{ $icon }}"></i></span>
                        <span>{{ $label }}</span>
                    </a>
                    @endcan
                @endforeach
            </nav>
        </div>
        @endcanany
    </div>
    <div class="border-t border-slate-100 p-4">
        <div class="rounded-xl bg-slate-50 p-4"><p class="text-xs font-bold text-slate-800">Butuh bantuan?</p><p class="mt-1 text-xs leading-5 text-slate-500">Hubungi admin sarana dan prasarana sekolah.</p></div>
        <a href="{{ route('login') }}" class="mt-3 flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium text-slate-500 hover:bg-slate-50"><i class="fas fa-sign-in-alt"></i><span>Masuk / ganti akun</span></a>
    </div>
</aside>
