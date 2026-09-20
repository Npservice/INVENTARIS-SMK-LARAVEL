@extends('layouts.app')
@section('title', 'Detail Usulan & Saran')

@section('content')
    <section id="suggestion-show-page" class="suggestion-page" data-api-url="{{ url('/app/kritik-saran') }}" data-suggestion-id="{{ request()->route('kritikSaran') }}" data-index-url="{{ route('usulan') }}">
        <a href="{{ route('usulan') }}" class="inventory-show-back"><i class="fas fa-arrow-left" aria-hidden="true"></i> Kembali ke daftar masukan</a>
        <div class="loan-detail-header">
            <div>
                <p class="loan-eyebrow">Detail masukan</p>
                <h2>Usulan & Saran</h2>
                <p id="suggestion-detail-subtitle">Memuat data...</p>
            </div>
            <div class="loan-detail-actions"><button type="button" id="suggestion-delete" data-permission="kritik-saran.destroy" class="loan-action-danger" disabled><i class="fas fa-trash-alt" aria-hidden="true"></i> Hapus masukan</button></div>
        </div>
        <p id="suggestion-detail-error" class="master-form-error" role="alert" hidden></p>
        <div class="suggestion-detail-grid">
            <article class="master-card suggestion-detail-card">
                <div class="suggestion-detail-icon"><i class="fas fa-user" aria-hidden="true"></i></div>
                <p class="suggestion-detail-label">Pengirim</p>
                <h3 id="suggestion-detail-name">—</h3>
                <p id="suggestion-detail-date" class="suggestion-detail-date">—</p>
            </article>
            <article class="master-card suggestion-detail-card suggestion-detail-content">
                <p class="suggestion-detail-label">Isi usulan atau saran</p>
                <p id="suggestion-detail-message">Memuat isi masukan...</p>
            </article>
        </div>
    </section>
@endsection

@push('scripts')
    <script src="{{ asset('assets/js/suggestions.js') }}"></script>
@endpush
