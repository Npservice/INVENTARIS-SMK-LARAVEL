@extends('layouts.app')
@section('title', 'Usulan & Saran')

@section('content')
    <section id="suggestion-index-page" class="suggestion-page" data-api-url="{{ url('/app/kritik-saran') }}" data-detail-base-url="{{ url('/usulan-saran') }}">
        <div class="loan-heading">
            <div>
                <p class="loan-eyebrow">Masukan sekolah</p>
                <h2>Usulan & Saran</h2>
                <p>Kelola masukan mengenai fasilitas dan kebutuhan sekolah.</p>
            </div>
            <button type="button" id="suggestion-create" class="inventory-header-button inventory-header-primary"><i class="fas fa-plus" aria-hidden="true"></i> Tambah masukan</button>
        </div>

        <section class="master-card suggestion-list-card">
            <div class="master-card-header">
                <div><h3>Daftar masukan</h3><p id="suggestion-caption">Memuat data dari server...</p></div>
                <div class="suggestion-list-tools">
                    <label class="suggestion-search" for="suggestion-search"><i class="fas fa-search" aria-hidden="true"></i><input id="suggestion-search" type="search" placeholder="Cari nama atau isi masukan" aria-label="Cari nama atau isi masukan"></label>
                    <span class="master-select-wrap master-select-compact"><select id="suggestion-page-size" aria-label="Jumlah per halaman"><option value="10">10 baris</option><option value="15" selected>15 baris</option><option value="25">25 baris</option></select><i class="fas fa-chevron-down" aria-hidden="true"></i></span>
                </div>
            </div>
            <div class="master-table-scroll">
                <table class="master-table suggestion-table">
                    <thead><tr><th>Pengirim</th><th>Isi masukan</th><th>Tanggal masuk</th><th class="master-action-heading">Aksi</th></tr></thead>
                    <tbody id="suggestion-table-body"><tr><td colspan="4" class="master-table-message">Memuat masukan...</td></tr></tbody>
                </table>
            </div>
            <div class="master-table-footer"><span id="suggestion-summary">Memuat data...</span><div class="master-pagination"><button type="button" id="suggestion-prev" aria-label="Halaman sebelumnya" disabled><i class="fas fa-chevron-left" aria-hidden="true"></i></button><span id="suggestion-page-number" class="master-page-number">1</span><button type="button" id="suggestion-next" aria-label="Halaman berikutnya" disabled><i class="fas fa-chevron-right" aria-hidden="true"></i></button></div></div>
        </section>
    </section>
    @include('partials.suggestion-modal')
@endsection

@push('scripts')
    <script src="{{ asset('assets/js/suggestions.js') }}"></script>
@endpush
