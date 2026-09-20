@extends('layouts.app')
@section('title', 'Pengguna')

@section('content')
    <section id="user-index-page" class="user-page" data-api-url="{{ url('/app/user') }}" data-detail-base-url="{{ url('/user') }}" data-current-user-id="{{ auth()->id() }}">
        <div class="loan-heading">
            <div>
                <p class="loan-eyebrow">Akses sistem</p>
                <h2>Pengguna</h2>
                <p>Kelola identitas dan peran pengguna sistem inventaris.</p>
            </div>
            <button type="button" id="user-create" data-permission="user.store" class="inventory-header-button inventory-header-primary"><i class="fas fa-user-plus" aria-hidden="true"></i> Tambah pengguna</button>
        </div>

        <section class="inventory-filter-card" aria-label="Filter pengguna">
            <div class="inventory-filter-heading">
                <div><h3>Filter pengguna</h3><p>Cari berdasarkan nama atau username.</p></div>
                <button type="button" id="user-reset"><i class="fas fa-undo-alt" aria-hidden="true"></i> Reset filter</button>
            </div>
            <div class="user-filter-grid">
                <label class="inventory-field"><span>Cari pengguna</span><input id="user-search" type="search" placeholder="Nama atau username..."></label>
                <div class="inventory-field"><label for="user-role-filter">Peran</label><span class="master-select-wrap"><select id="user-role-filter"><option value="">Semua peran</option><option value="1">Administrator</option><option value="2">Staf</option><option value="3">Guru / petugas</option></select><i class="fas fa-chevron-down" aria-hidden="true"></i></span></div>
                <div class="inventory-field"><label for="user-status-filter">Jabatan</label><span class="master-select-wrap"><select id="user-status-filter"><option value="">Semua jabatan</option><option value="Guru">Guru</option><option value="Kepsek">Kepala sekolah</option><option value="Pimpinan">Pimpinan</option><option value="Karyawan">Karyawan</option><option value="Staff">Staf</option></select><i class="fas fa-chevron-down" aria-hidden="true"></i></span></div>
            </div>
        </section>

        <section class="master-card user-list-card">
            <div class="master-card-header">
                <div><h3>Daftar pengguna</h3><p id="user-caption">Memuat data dari server...</p></div>
                <span class="master-select-wrap master-select-compact"><select id="user-page-size" aria-label="Jumlah per halaman"><option value="10">10 baris</option><option value="15" selected>15 baris</option><option value="25">25 baris</option></select><i class="fas fa-chevron-down" aria-hidden="true"></i></span>
            </div>
            <div class="master-table-scroll">
                <table class="master-table user-table">
                    <thead><tr><th>Pengguna</th><th>Username</th><th>Jabatan</th><th>Peran</th><th>Terdaftar</th><th class="master-action-heading">Aksi</th></tr></thead>
                    <tbody id="user-table-body"><tr><td colspan="6" class="master-table-message">Memuat pengguna...</td></tr></tbody>
                </table>
            </div>
            <div class="master-table-footer"><span id="user-summary">Memuat data...</span><div class="master-pagination"><button type="button" id="user-prev" aria-label="Halaman sebelumnya" disabled><i class="fas fa-chevron-left" aria-hidden="true"></i></button><span id="user-page-number" class="master-page-number">1</span><button type="button" id="user-next" aria-label="Halaman berikutnya" disabled><i class="fas fa-chevron-right" aria-hidden="true"></i></button></div></div>
        </section>
    </section>
    @include('partials.user-modal')
@endsection

@push('scripts')
    <script src="{{ asset('assets/js/users.js') }}"></script>
@endpush
