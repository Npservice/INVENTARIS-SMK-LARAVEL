@extends('layouts.app')
@section('title', 'Gudang')

@section('content')
    <section id="warehouse-page" class="warehouse-page"
             data-api-url="{{ url('/app/inventaris') }}"
             data-instansi-url="{{ url('/app/instansi/select') }}"
             data-lokasi-url="{{ url('/app/lokasi/select') }}"
             data-detail-base-url="{{ url('/inventaris') }}">
        <div class="warehouse-heading">
            <div>
                <p class="warehouse-eyebrow">Persediaan sekolah</p>
                <h2>Gudang</h2>
                <p>Pantau dan perbarui jumlah barang pada lokasi yang ditandai sebagai gudang.</p>
            </div>
        </div>

        <div class="warehouse-metrics">
            <article class="warehouse-metric"><span><i class="fas fa-boxes" aria-hidden="true"></i></span><div><strong id="warehouse-visible-items">—</strong><p>Jenis barang di halaman ini</p></div></article>
            <article class="warehouse-metric"><span><i class="fas fa-layer-group" aria-hidden="true"></i></span><div><strong id="warehouse-total-units">—</strong><p>Unit di halaman ini</p></div></article>
            <article class="warehouse-metric"><span><i class="fas fa-warehouse" aria-hidden="true"></i></span><div><strong id="warehouse-location-count">—</strong><p>Lokasi gudang terdaftar</p></div></article>
        </div>

        <section class="inventory-filter-card" aria-label="Filter gudang">
            <div class="inventory-filter-heading">
                <div><h3>Filter barang gudang</h3><p>Hasil pencarian diambil dari server</p></div>
                <button type="button" id="warehouse-reset"><i class="fas fa-undo-alt" aria-hidden="true"></i> Reset filter</button>
            </div>
            <div class="warehouse-filter-grid">
                <label class="inventory-field"><span>Cari barang atau kode</span><input id="warehouse-search" type="search" placeholder="Ketik nama atau kode..."></label>
                <div class="inventory-field">
                    <label for="warehouse-instansi">Instansi</label>
                    <span class="master-select-wrap"><select id="warehouse-instansi"><option value="">Semua instansi</option></select><i class="fas fa-chevron-down" aria-hidden="true"></i></span>
                </div>
                <div class="inventory-field">
                    <label for="warehouse-lokasi">Lokasi gudang</label>
                    <span class="master-select-wrap"><select id="warehouse-lokasi"><option value="">Semua lokasi gudang</option></select><i class="fas fa-chevron-down" aria-hidden="true"></i></span>
                </div>
                <div class="inventory-field">
                    <label for="warehouse-kondisi">Kondisi</label>
                    <span class="master-select-wrap"><select id="warehouse-kondisi"><option value="">Semua kondisi</option><option value="Baik">Baik</option><option value="Rusak">Rusak</option></select><i class="fas fa-chevron-down" aria-hidden="true"></i></span>
                </div>
            </div>
        </section>

        <section class="master-card">
            <div class="master-card-header">
                <div><h3>Daftar barang gudang</h3><p id="warehouse-caption">Memuat data dari server...</p></div>
                <span class="master-select-wrap master-select-compact">
                    <select id="warehouse-page-size" aria-label="Jumlah per halaman"><option value="10">10 baris</option><option value="15" selected>15 baris</option><option value="25">25 baris</option></select>
                    <i class="fas fa-chevron-down" aria-hidden="true"></i>
                </span>
            </div>
            <div class="master-table-scroll warehouse-table-scroll" tabindex="0" aria-label="Gulir daftar barang gudang">
                <table class="master-table warehouse-table">
                    <thead><tr><th>Kode</th><th>Nama barang</th><th>Jenis</th><th>Lokasi</th><th>Instansi</th><th>Jumlah</th><th>Kondisi</th><th class="master-action-heading">Aksi</th></tr></thead>
                    <tbody id="warehouse-table-body"><tr><td colspan="8" class="master-table-message">Memuat barang gudang...</td></tr></tbody>
                </table>
            </div>
            <div class="master-table-footer">
                <span id="warehouse-summary">Memuat data...</span>
                <div class="master-pagination"><button type="button" id="warehouse-prev" aria-label="Halaman sebelumnya" disabled><i class="fas fa-chevron-left" aria-hidden="true"></i></button><span id="warehouse-page-number" class="master-page-number">1</span><button type="button" id="warehouse-next" aria-label="Halaman berikutnya" disabled><i class="fas fa-chevron-right" aria-hidden="true"></i></button></div>
            </div>
        </section>
    </section>
    @include('partials.warehouse-stock-modal')
@endsection

@push('scripts')
    <script src="{{ asset('assets/js/warehouse.js') }}"></script>
@endpush
