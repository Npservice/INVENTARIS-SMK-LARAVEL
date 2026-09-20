@extends('layouts.app')
@section('title', 'Peminjaman')

@section('content')
    <section id="loan-index-page" class="loan-page"
             data-api-url="{{ url('/app/peminjaman') }}"
             data-inventory-url="{{ url('/app/inventaris') }}"
             data-inventory-select-url="{{ url('/app/inventaris/select') }}"
             data-detail-base-url="{{ url('/peminjaman') }}"
             data-current-user-id="{{ auth()->id() }}"
             data-current-user-name="{{ auth()->user()->name }}">
        <div class="loan-heading">
            <div>
                <p class="loan-eyebrow">Aktivitas barang</p>
                <h2>Peminjaman</h2>
                <p>Catat peminjaman dan pantau pengembalian barang sekolah.</p>
            </div>
            <button type="button" data-open-loan-modal data-permission="peminjaman.store,inventaris.select,inventaris.show" class="inventory-header-button inventory-header-primary"><i class="fas fa-plus" aria-hidden="true"></i> Buat peminjaman</button>
        </div>

        <div class="loan-metrics">
            <article class="loan-metric"><span><i class="fas fa-list" aria-hidden="true"></i></span><div><strong id="loan-page-count">—</strong><p>Catatan</p></div></article>
            <article class="loan-metric"><span><i class="fas fa-clock" aria-hidden="true"></i></span><div><strong id="loan-page-active">—</strong><p>Sedang dipinjam</p></div></article>
            <article class="loan-metric"><span><i class="fas fa-check-circle" aria-hidden="true"></i></span><div><strong id="loan-page-returned">—</strong><p>Sudah kembali</p></div></article>
        </div>

        <section class="inventory-filter-card" aria-label="Filter peminjaman">
            <div class="inventory-filter-heading">
                <div><h3>Filter peminjaman</h3><p>Hasil pencarian diproses oleh server</p></div>
                <button type="button" id="loan-reset"><i class="fas fa-undo-alt" aria-hidden="true"></i> Reset filter</button>
            </div>
            <div class="loan-filter-grid">
                <label class="inventory-field"><span>Cari peminjam atau barang</span><input id="loan-search" type="search" placeholder="Nama, barang, atau kode..."></label>
                <div class="inventory-field"><label for="loan-status-filter">Status barang</label><span class="master-select-wrap"><select id="loan-status-filter"><option value="">Semua status</option><option value="Dipinjam">Dipinjam</option><option value="Kembali">Kembali</option></select><i class="fas fa-chevron-down" aria-hidden="true"></i></span></div>
                <div class="inventory-field"><label for="loan-category-filter">Kategori peminjam</label><span class="master-select-wrap"><select id="loan-category-filter"><option value="">Semua kategori</option><option value="Guru">Guru</option><option value="Siswa">Siswa</option><option value="Staff">Staff</option></select><i class="fas fa-chevron-down" aria-hidden="true"></i></span></div>
                <div class="loan-date-range" role="group" aria-label="Rentang tanggal pinjam">
                    <label class="inventory-field" for="loan-date-from"><span>Tanggal pinjam dari</span><input id="loan-date-from" type="date"></label>
                    <label class="inventory-field" for="loan-date-to"><span>Tanggal pinjam sampai</span><input id="loan-date-to" type="date"></label>
                </div>
            </div>
        </section>

        <section class="loan-code-card" data-permission="peminjaman.find-by-kode,peminjaman.show" aria-label="Cari pinjaman aktif berdasarkan kode">
            <span class="loan-code-icon"><i class="fas fa-barcode" aria-hidden="true"></i></span>
            <div><strong>Pencarian peminjaman aktif</strong><p>Masukkan kode inventaris untuk membuka transaksi yang belum dikembalikan.</p></div>
            <div class="loan-code-controls"><input id="loan-code-search" type="text" placeholder="Contoh: INV/SMKUN/0001" aria-label="Kode inventaris"><button type="button" id="loan-code-find" data-permission="peminjaman.find-by-kode">Cari transaksi</button></div>
        </section>

        <section class="master-card">
            <div class="master-card-header">
                <div><h3>Riwayat peminjaman</h3><p id="loan-caption">Memuat data dari server...</p></div>
                <span class="master-select-wrap master-select-compact"><select id="loan-page-size" aria-label="Jumlah per halaman"><option value="10">10 baris</option><option value="15" selected>15 baris</option><option value="25">25 baris</option></select><i class="fas fa-chevron-down" aria-hidden="true"></i></span>
            </div>
            <div class="master-table-scroll">
                <table class="master-table loan-table">
                    <thead><tr><th>Barang</th><th>Peminjam</th><th>Kategori</th><th>Mulai</th><th>Batas kembali</th><th>Status</th><th class="master-action-heading">Aksi</th></tr></thead>
                    <tbody id="loan-table-body"><tr><td colspan="7" class="master-table-message">Memuat peminjaman...</td></tr></tbody>
                </table>
            </div>
            <div class="master-table-footer"><span id="loan-summary">Memuat data...</span><div class="master-pagination"><button type="button" id="loan-prev" aria-label="Halaman sebelumnya" disabled><i class="fas fa-chevron-left" aria-hidden="true"></i></button><span id="loan-page-number" class="master-page-number">1</span><button type="button" id="loan-next" aria-label="Halaman berikutnya" disabled><i class="fas fa-chevron-right" aria-hidden="true"></i></button></div></div>
        </section>
    </section>
    @include('partials.loan-modal')
@endsection

@push('scripts')
    <script src="{{ asset('assets/js/loans.js') }}"></script>
@endpush
