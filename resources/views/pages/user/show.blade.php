@extends('layouts.app')
@section('title', 'Detail Pengguna')

@section('content')
    <section id="user-show-page" class="user-page" data-api-url="{{ url('/app/user') }}" data-user-id="{{ request()->route('user') }}" data-index-url="{{ route('user') }}" data-current-user-id="{{ auth()->id() }}">
        <a href="{{ route('user') }}" class="inventory-show-back"><i class="fas fa-arrow-left" aria-hidden="true"></i> Kembali ke pengguna</a>
        <div class="loan-detail-header">
            <div>
                <p class="loan-eyebrow">Detail pengguna</p>
                <h2 id="user-detail-title">Memuat pengguna...</h2>
                <span id="user-detail-subtitle" class="loan-detail-subtitle">—</span>
            </div>
            <div class="loan-detail-actions">
                <button type="button" id="user-edit" data-permission="user.update" class="loan-action-secondary" disabled><i class="fas fa-pen" aria-hidden="true"></i> Edit</button>
                <button type="button" id="user-delete" data-permission="user.destroy" class="loan-action-danger" disabled><i class="fas fa-trash-alt" aria-hidden="true"></i> Hapus</button>
            </div>
        </div>
        <p id="user-detail-error" class="master-form-error" role="alert" hidden></p>
        <section class="inventory-show-card" aria-labelledby="user-info-heading">
            <div class="inventory-show-section-head"><span><i class="fas fa-user" aria-hidden="true"></i></span><div><h3 id="user-info-heading">Informasi pengguna</h3><p>Data identitas dan peran yang tersimpan</p></div></div>
            <dl class="inventory-show-grid">
                <div><dt>Nama lengkap</dt><dd data-user="name">—</dd></div>
                <div><dt>Username</dt><dd data-user="username">—</dd></div>
                <div><dt>Jabatan</dt><dd data-user="status">—</dd></div>
                <div><dt>Peran</dt><dd data-user="role">—</dd></div>
                <div><dt>Terdaftar</dt><dd data-user="created_at">—</dd></div>
                <div><dt>Terakhir diperbarui</dt><dd data-user="updated_at">—</dd></div>
            </dl>
        </section>
        <div class="user-history-grid">
            <section class="master-card user-history-card"><div class="user-history-icon"><i class="fas fa-exchange-alt" aria-hidden="true"></i></div><div><strong id="user-loan-count">—</strong><p>Catatan peminjaman</p></div></section>
            <section class="master-card user-history-card"><div class="user-history-icon"><i class="fas fa-tools" aria-hidden="true"></i></div><div><strong id="user-maintenance-count">—</strong><p>Catatan perawatan</p></div></section>
        </div>
    </section>
    @include('partials.user-modal')
@endsection

@push('scripts')
    <script src="{{ asset('assets/js/users.js') }}"></script>
@endpush
