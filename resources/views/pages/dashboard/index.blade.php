@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
    <section id="dashboard-page" class="dashboard-page" data-dashboard-url="{{ url('/app/dashboard') }}">
        <div class="dashboard-hero">
            <div class="dashboard-hero-copy">
                <span class="dashboard-hero-kicker"><i class="fas fa-chart-line" aria-hidden="true"></i> Ringkasan operasional</span>
                <h2>Selamat datang, {{ auth()->user()->name }}.</h2>
                <p>Pantau inventaris, stok gudang, peminjaman, dan perawatan barang sekolah.</p>
                <div class="dashboard-hero-actions">
                    <a href="{{ route('inventaris.index') }}" data-permission="inventaris.index" class="dashboard-hero-primary"><i class="fas fa-boxes" aria-hidden="true"></i> Buka inventaris</a>
                    <a href="{{ route('peminjaman') }}" data-permission="peminjaman.index" class="dashboard-hero-secondary"><i class="fas fa-exchange-alt" aria-hidden="true"></i> Lihat peminjaman</a>
                </div>
            </div>
            <div class="dashboard-hero-mark" aria-hidden="true"><i class="fas fa-school"></i></div>
        </div>

        <section class="dashboard-metrics" aria-label="Ringkasan data">
            <article class="dashboard-metric"><div class="dashboard-metric-icon is-indigo"><i class="fas fa-boxes" aria-hidden="true"></i></div><div><p>Unit inventaris</p><strong id="dashboard-inventory-count">—</strong><small>Seluruh unit tercatat</small></div></article>
            <article class="dashboard-metric"><div class="dashboard-metric-icon is-blue"><i class="fas fa-warehouse" aria-hidden="true"></i></div><div><p>Stok gudang</p><strong id="dashboard-warehouse-stock">—</strong><small>Unit tersedia di gudang</small></div></article>
            <article class="dashboard-metric"><div class="dashboard-metric-icon is-amber"><i class="fas fa-exchange-alt" aria-hidden="true"></i></div><div><p>Peminjaman aktif</p><strong id="dashboard-active-loans">—</strong><small>Transaksi belum kembali</small></div></article>
            <article class="dashboard-metric"><div class="dashboard-metric-icon is-green"><i class="fas fa-tools" aria-hidden="true"></i></div><div><p>Perlu perawatan</p><strong id="dashboard-damaged-count">—</strong><small>Barang berkondisi rusak</small></div></article>
        </section>

        <div class="dashboard-content-grid">
            <section class="master-card dashboard-activity-card">
                <div class="master-card-header">
                    <div><h3>Peminjaman terbaru</h3><p id="dashboard-loan-caption">Memuat aktivitas dari server...</p></div>
                    <a href="{{ route('peminjaman') }}" data-permission="peminjaman.index" class="dashboard-card-link">Lihat semua <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
                </div>
                <div class="master-table-scroll">
                    <table class="master-table dashboard-loan-table">
                        <thead><tr><th>Barang</th><th>Peminjam</th><th>Tanggal pinjam</th><th>Status</th></tr></thead>
                        <tbody id="dashboard-loan-body"><tr><td colspan="4" class="master-table-message">Memuat peminjaman...</td></tr></tbody>
                    </table>
                </div>
            </section>

            <div class="dashboard-side-column">
                <section class="master-card dashboard-condition-card">
                    <div class="dashboard-side-heading"><span class="dashboard-side-icon"><i class="fas fa-clipboard-check" aria-hidden="true"></i></span><div><h3>Kondisi barang</h3><p>Berdasarkan data inventaris</p></div></div>
                    <div class="dashboard-attention"><strong id="dashboard-attention-count">—</strong><p>Barang berkondisi rusak perlu ditinjau dan dicatat perawatannya.</p></div>
                    <a href="{{ route('inventaris.index') }}" data-permission="inventaris.index" class="dashboard-inline-link">Lihat data inventaris <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
                </section>
                <section class="master-card dashboard-shortcuts-card">
                    <div class="dashboard-side-heading"><span class="dashboard-side-icon"><i class="fas fa-bolt" aria-hidden="true"></i></span><div><h3>Akses cepat</h3><p>Pekerjaan yang sering digunakan</p></div></div>
                    <div class="dashboard-shortcuts">
                        <a href="{{ route('gudang') }}" data-permission="inventaris.index"><span><i class="fas fa-warehouse" aria-hidden="true"></i></span><div><strong>Gudang</strong><small>Pantau stok barang</small></div><i class="fas fa-chevron-right" aria-hidden="true"></i></a>
                        <a href="{{ route('peminjaman') }}" data-permission="peminjaman.index"><span><i class="fas fa-exchange-alt" aria-hidden="true"></i></span><div><strong>Peminjaman</strong><small>Catat dan pantau transaksi</small></div><i class="fas fa-chevron-right" aria-hidden="true"></i></a>
                        <a href="{{ route('usulan') }}" data-permission="kritik-saran.index"><span><i class="fas fa-comment-dots" aria-hidden="true"></i></span><div><strong>Usulan & Saran</strong><small>Tinjau masukan sekolah</small></div><i class="fas fa-chevron-right" aria-hidden="true"></i></a>
                    </div>
                </section>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script src="{{ asset('assets/js/dashboard.js') }}"></script>
@endpush
