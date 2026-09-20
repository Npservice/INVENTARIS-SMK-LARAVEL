@extends('layouts.app')
@section('title', 'Detail Role & Permission')

@section('content')
    <section id="role-show-page" class="role-page" data-api-url="{{ url('/app/role') }}" data-permission-url="{{ url('/app/permission') }}" data-role-id="{{ request()->route('role') }}" data-index-url="{{ route('role.index') }}">
        <a href="{{ route('role.index') }}" class="inventory-show-back"><i class="fas fa-arrow-left" aria-hidden="true"></i> Kembali ke daftar role</a>
        <div class="loan-detail-header">
            <div>
                <p class="loan-eyebrow">Detail role</p>
                <h2 id="role-detail-title">Memuat role...</h2>
                <span id="role-detail-subtitle" class="loan-detail-subtitle">—</span>
            </div>
            <div class="loan-detail-actions">
                <button type="button" id="role-edit" data-permission="role.update,permission.index" class="loan-action-secondary" disabled><i class="fas fa-pen" aria-hidden="true"></i> Edit izin</button>
                <button type="button" id="role-delete" data-permission="role.destroy" class="loan-action-danger" disabled><i class="fas fa-trash-alt" aria-hidden="true"></i> Hapus</button>
            </div>
        </div>
        <p id="role-detail-error" class="master-form-error" role="alert" hidden></p>
        <section class="master-card role-detail-card">
            <div class="master-card-header"><div><h3>Izin role</h3><p id="role-permission-count">Memuat izin...</p></div></div>
            <div id="role-detail-permissions" class="role-detail-permissions"></div>
        </section>
    </section>
    @include('partials.role-modal')
@endsection

@push('scripts')
    <script src="{{ asset('assets/js/roles.js') }}"></script>
@endpush
