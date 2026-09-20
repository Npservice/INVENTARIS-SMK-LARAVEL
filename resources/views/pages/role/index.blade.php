@extends('layouts.app')
@section('title', 'Role & Permission')

@section('content')
    <section id="role-index-page" class="role-page" data-api-url="{{ url('/app/role') }}" data-permission-url="{{ url('/app/permission') }}" data-detail-base-url="{{ url('/role-permission') }}">
        <div class="loan-heading">
            <div>
                <p class="loan-eyebrow">Manajemen akses</p>
                <h2>Role & Permission</h2>
                <p>Atur kelompok peran dan izin untuk fitur sistem.</p>
            </div>
            <button type="button" id="role-create" data-permission="role.store,permission.index" class="inventory-header-button inventory-header-primary"><i class="fas fa-plus" aria-hidden="true"></i> Tambah role</button>
        </div>

        <section class="master-card role-list-card">
            <div class="master-card-header">
                <div><h3>Daftar role</h3><p id="role-caption">Memuat data dari server...</p></div>
                <div class="role-list-tools">
                    <label class="suggestion-search" for="role-search"><i class="fas fa-search" aria-hidden="true"></i><input id="role-search" type="search" placeholder="Cari nama role" aria-label="Cari nama role"></label>
                    <span class="master-select-wrap master-select-compact"><select id="role-page-size" aria-label="Jumlah per halaman"><option value="10">10 baris</option><option value="15" selected>15 baris</option><option value="25">25 baris</option></select><i class="fas fa-chevron-down" aria-hidden="true"></i></span>
                </div>
            </div>
            <div class="master-table-scroll">
                <table class="master-table role-table">
                    <thead><tr><th>Role</th><th>Jumlah pengguna</th><th>Jumlah izin</th><th class="master-action-heading">Aksi</th></tr></thead>
                    <tbody id="role-table-body"><tr><td colspan="4" class="master-table-message">Memuat role...</td></tr></tbody>
                </table>
            </div>
            <div class="master-table-footer"><span id="role-summary">Memuat data...</span><div class="master-pagination"><button type="button" id="role-prev" aria-label="Halaman sebelumnya" disabled><i class="fas fa-chevron-left" aria-hidden="true"></i></button><span id="role-page-number" class="master-page-number">1</span><button type="button" id="role-next" aria-label="Halaman berikutnya" disabled><i class="fas fa-chevron-right" aria-hidden="true"></i></button></div></div>
        </section>
    </section>
    @include('partials.role-modal')
@endsection

@push('scripts')
    <script src="{{ asset('assets/js/roles.js') }}"></script>
@endpush
