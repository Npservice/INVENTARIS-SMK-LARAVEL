@extends('layouts.app')
@section('title', 'Detail Peminjaman')

@section('content')
    <section id="loan-show-page" class="loan-page"
             data-loan-id="{{ request()->route('peminjaman') }}"
             data-api-url="{{ url('/app/peminjaman') }}"
             data-inventory-url="{{ url('/app/inventaris') }}"
             data-inventory-select-url="{{ url('/app/inventaris/select') }}"
             data-index-url="{{ route('peminjaman') }}"
             data-current-user-id="{{ auth()->id() }}"
             data-current-user-name="{{ auth()->user()->name }}">
        <a href="{{ route('peminjaman') }}" class="inventory-show-back"><i class="fas fa-arrow-left" aria-hidden="true"></i> Kembali ke peminjaman</a>

        <div class="loan-detail-header">
            <div>
                <p class="loan-eyebrow">Detail peminjaman</p>
                <h2 data-loan="nama_pjm">Memuat peminjaman...</h2>
                <span class="loan-detail-subtitle" data-loan="kode_invt">—</span>
            </div>
            <div class="loan-detail-actions">
                <a href="{{ route('peminjaman.struk', request()->route('peminjaman')) }}" data-permission="peminjaman.show" target="_blank" rel="noopener noreferrer" class="loan-action-secondary"><i class="fas fa-receipt" aria-hidden="true"></i> Cetak struk</a>
                <button type="button" id="loan-edit" data-permission="peminjaman.update,inventaris.select,inventaris.show" class="loan-action-secondary" disabled><i class="fas fa-pen" aria-hidden="true"></i> Edit</button>
                <button type="button" id="loan-return" data-permission="peminjaman.kembali" class="loan-action-primary" disabled><i class="fas fa-undo-alt" aria-hidden="true"></i> Kembalikan</button>
                <button type="button" id="loan-delete" data-permission="peminjaman.destroy" class="loan-action-danger" disabled><i class="fas fa-trash-alt" aria-hidden="true"></i> Hapus</button>
            </div>
        </div>

        <p id="loan-detail-error" class="master-form-error" role="alert" hidden></p>

        <section class="inventory-show-card" aria-labelledby="loan-info-heading">
            <div class="inventory-show-section-head"><span><i class="fas fa-exchange-alt" aria-hidden="true"></i></span><div><h3 id="loan-info-heading">Informasi peminjaman</h3><p>Data yang tersimpan di server</p></div></div>
            <dl class="inventory-show-grid">
                <div><dt>Barang</dt><dd data-loan="barang">—</dd></div>
                <div><dt>Kode inventaris</dt><dd data-loan="kode_barang">—</dd></div>
                <div><dt>Lokasi</dt><dd data-loan="lokasi">—</dd></div>
                <div><dt>Instansi</dt><dd data-loan="instansi">—</dd></div>
                <div><dt>Peminjam</dt><dd data-loan="peminjam">—</dd></div>
                <div><dt>Kategori</dt><dd data-loan="kategori">—</dd></div>
                <div><dt>Petugas</dt><dd data-loan="petugas">—</dd></div>
                <div><dt>Status</dt><dd data-loan="status">—</dd></div>
                <div><dt>Tanggal pinjam</dt><dd data-loan="tanggal_pinjam">—</dd></div>
                <div><dt>Batas kembali</dt><dd data-loan="tanggal_kembali">—</dd></div>
                <div><dt>Waktu pinjam</dt><dd data-loan="waktu_pinjam">—</dd></div>
                <div><dt>Waktu kembali</dt><dd data-loan="waktu_kembali">—</dd></div>
                <div class="inventory-show-wide"><dt>Keterangan</dt><dd data-loan="keterangan">—</dd></div>
            </dl>
        </section>
    </section>
    @include('partials.loan-modal')
@endsection

@push('scripts')
    <script src="{{ asset('assets/js/loans.js') }}"></script>
@endpush
