@extends('layouts.app')
@section('title', 'Detail Inventaris')

@section('content')
    <section id="inventory-show-page" class="space-y-6"
             data-item-id="{{ request()->route('inventaris') }}"
             data-api-url="{{ url('/app/inventaris') }}"
             data-care-url="{{ url('/app/perawatan') }}"
             data-current-user-id="{{ auth()->id() }}"
             data-current-user-name="{{ auth()->user()->name }}"
             data-index-url="{{ route('inventaris.index') }}"
             data-jenis-url="{{ url('/app/jenis/select') }}"
             data-lokasi-url="{{ url('/app/lokasi/select') }}"
             data-instansi-url="{{ url('/app/instansi/select') }}"
             data-pendanaan-url="{{ url('/app/pendanaan/select') }}">
        <a href="{{ route('inventaris.index') }}" class="inventory-show-back"><i class="fas fa-arrow-left" aria-hidden="true"></i> Kembali ke inventaris</a>

        <div class="inventory-show-header">
            <div>
                <p class="inventory-show-eyebrow">DETAIL INVENTARIS</p>
                <h2 data-detail="nama">Memuat barang...</h2>
                <span class="inventory-show-code" data-detail="kode_invt">—</span>
            </div>
            <div class="inventory-show-actions">
                <a href="{{ route('inventaris.label', request()->route('inventaris')) }}" data-permission="inventaris.show" target="_blank" rel="noopener noreferrer" class="inventory-show-label"><i class="fas fa-barcode" aria-hidden="true"></i> Cetak label</a>
                <button type="button" id="inventory-edit" data-permission="inventaris.update,jenis.select,lokasi.select,instansi.select,pendanaan.select" class="inventory-detail-edit" disabled><i class="fas fa-pen" aria-hidden="true"></i> Edit barang</button>
                <button type="button" id="inventory-delete" data-permission="inventaris.destroy" class="inventory-detail-delete" disabled><i class="fas fa-trash-alt" aria-hidden="true"></i> Hapus</button>
            </div>
        </div>

        <p id="inventory-detail-error" class="master-form-error" role="alert" hidden></p>

        <section class="inventory-show-card" aria-labelledby="inventory-data-heading">
            <div class="inventory-show-section-head"><span><i class="fas fa-box-open" aria-hidden="true"></i></span><div><h3 id="inventory-data-heading">Informasi barang</h3><p>Data inventaris yang tersimpan di server</p></div></div>
            <dl class="inventory-show-grid">
                <div><dt>Jenis barang</dt><dd data-detail="jenis">—</dd></div>
                <div><dt>Instansi</dt><dd data-detail="instansi">—</dd></div>
                <div><dt>Lokasi</dt><dd data-detail="lokasi">—</dd></div>
                <div><dt>Kondisi</dt><dd data-detail="kondisi">—</dd></div>
                <div><dt>Tanggal masuk</dt><dd data-detail="masuk">—</dd></div>
                <div><dt>Jumlah</dt><dd data-detail="jumlah">—</dd></div>
                <div><dt>Pendanaan</dt><dd data-detail="pendanaan">—</dd></div>
                <div><dt>Harga beli</dt><dd data-detail="harga_beli">—</dd></div>
                <div class="inventory-show-wide"><dt>Keterangan</dt><dd data-detail="keterangan">—</dd></div>
            </dl>
        </section>

        <section class="inventory-show-card" data-permission="perawatan.index" aria-labelledby="inventory-care-heading">
            <div class="inventory-show-section-head inventory-care-heading"><span><i class="fas fa-tools" aria-hidden="true"></i></span><div><h3 id="inventory-care-heading">Riwayat perawatan</h3><p>Perawatan yang terkait dengan barang ini</p></div><button type="button" id="inventory-care-add" data-permission="perawatan.store" class="inventory-care-add" disabled><i class="fas fa-plus" aria-hidden="true"></i> Tambah perawatan</button></div>
            <div class="master-table-scroll">
                <table class="master-table inventory-care-table">
                    <thead><tr><th>Tanggal</th><th>Status</th><th>Petugas</th><th>Biaya</th><th>Keterangan</th><th class="master-action-heading">Aksi</th></tr></thead>
                    <tbody id="inventory-care-body"><tr><td colspan="6" class="master-table-message">Memuat riwayat perawatan...</td></tr></tbody>
                </table>
            </div>
            <div class="inventory-care-footer"><span id="inventory-care-summary">Memuat data...</span><div class="master-pagination"><button type="button" id="inventory-care-prev" aria-label="Halaman sebelumnya" disabled><i class="fas fa-chevron-left"></i></button><span id="inventory-care-page" class="master-page-number">1</span><button type="button" id="inventory-care-next" aria-label="Halaman berikutnya" disabled><i class="fas fa-chevron-right"></i></button></div></div>
        </section>
    </section>
    @include('partials.perawatan-modal')
@endsection

@push('scripts')
    <script src="{{ asset('assets/js/inventory.js') }}"></script>
    <script src="{{ asset('assets/js/perawatan.js') }}"></script>
@endpush
