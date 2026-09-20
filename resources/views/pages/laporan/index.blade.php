@extends('layouts.app')
@section('title', 'Laporan')

@section('content')
    @php
        $months = [['Apr', 54], ['Mei', 68], ['Jun', 61], ['Jul', 88], ['Agu', 74], ['Sep', 96]];
    @endphp

    <div class="space-y-7">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[.18em] text-indigo-600">Analisis</p>
                <h2 class="mt-2 text-3xl font-bold tracking-tight text-slate-900">Laporan</h2>
                <p class="mt-2 text-sm text-slate-500">Ringkasan inventaris dan aktivitas peminjaman sekolah.</p>
            </div>
            <button type="button" data-demo-action="Ekspor laporan akan tersedia setelah data terhubung."
                    class="inline-flex items-center justify-center rounded-xl bg-indigo-600 px-5 py-3 text-sm font-semibold text-white hover:bg-indigo-700">
                <i class="fas fa-download" aria-hidden="true"></i>&nbsp; Unduh laporan
            </button>
        </div>

        <div class="grid gap-4 sm:grid-cols-3">
            @foreach ([['Total inventaris', '440', 'fas fa-boxes'], ['Barang tersedia', '428', 'fas fa-check-circle'], ['Sedang dipinjam', '12', 'fas fa-exchange-alt']] as $metric)
                <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <p class="text-sm text-slate-500">{{ $metric[0] }}</p>
                        <i class="{{ $metric[2] }} text-indigo-600" aria-hidden="true"></i>
                    </div>
                    <p class="mt-3 text-3xl font-bold text-slate-900">{{ $metric[1] }}</p>
                    <p class="mt-1 text-xs text-slate-400">Periode berjalan</p>
                </article>
            @endforeach
        </div>

        <div class="grid gap-6 xl:grid-cols-[1.6fr_1fr]">
            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="font-bold text-slate-900">Aktivitas inventaris</h3>
                <p class="mt-1 text-xs text-slate-500">Ilustrasi tren enam bulan terakhir</p>
                <div class="mt-7 grid grid-cols-6 items-end gap-3 border-b border-slate-100 pb-3">
                    @foreach ($months as [$month, $height])
                        <div class="rounded-t-lg bg-indigo-100 p-2" style="height: {{ $height * 2 }}px">
                            <div class="h-full rounded-md bg-indigo-500"></div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-3 grid grid-cols-6 gap-3 text-center text-xs text-slate-400">
                    @foreach ($months as [$month, $height])
                        <span>{{ $month }}</span>
                    @endforeach
                </div>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="font-bold text-slate-900">Komposisi aset</h3>
                <p class="mt-1 text-xs text-slate-500">Perkiraan sebaran jenis barang</p>
                <div class="mt-6 space-y-5">
                    @foreach ([['Elektronik', '52%'], ['Perabotan', '31%'], ['Peralatan', '17%']] as $category)
                        <div>
                            <div class="flex justify-between text-sm">
                                <span class="font-medium text-slate-700">{{ $category[0] }}</span>
                                <strong class="text-slate-900">{{ $category[1] }}</strong>
                            </div>
                            <div class="report-progress"><span style="width: {{ $category[1] }}"></span></div>
                        </div>
                    @endforeach
                </div>
                <p class="mt-6 text-xs leading-5 text-slate-400">Angka pada halaman ini merupakan contoh tampilan dan belum bersumber dari database.</p>
            </section>
        </div>
    </div>
@endsection
