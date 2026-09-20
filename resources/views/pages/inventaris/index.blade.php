@extends('layouts.app')
@section('title', 'Inventaris')

@section('content')
    <section id="inventory-page" class="space-y-7"
             data-api-url="{{ url('/app/inventaris') }}"
             data-show-base-url="{{ url('/inventaris') }}"
             data-label-base-url="{{ url('/inventaris') }}"
             data-next-kode-url="{{ url('/app/inventaris/next-kode') }}"
             data-jenis-url="{{ url('/app/jenis/select') }}"
             data-lokasi-url="{{ url('/app/lokasi/select') }}"
             data-instansi-url="{{ url('/app/instansi/select') }}"
             data-pendanaan-url="{{ url('/app/pendanaan/select') }}">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[.18em] text-indigo-600">Aset sekolah</p>
                <h2 class="mt-2 text-3xl font-bold tracking-tight text-slate-900">Inventaris</h2>
                <p class="mt-2 text-sm text-slate-500">Kelola barang, lokasi, kondisi, dan sumber pendanaan dalam satu daftar.</p>
            </div>
            <div class="inventory-header-actions">
                <button type="button" id="inventory-export" data-permission="inventaris.export" class="inventory-header-button inventory-header-secondary">
                    <i class="fas fa-file-export" aria-hidden="true"></i> Export
                </button>
                <a href="{{ url('/app/inventaris/import-template') }}" data-permission="inventaris.import-template" class="inventory-header-button inventory-header-secondary" download>
                    <i class="fas fa-file-download" aria-hidden="true"></i> Unduh template
                </a>
                <button type="button" id="inventory-import" data-permission="inventaris.import" class="inventory-header-button inventory-header-secondary">
                    <i class="fas fa-file-import" aria-hidden="true"></i> Import
                </button>
                <input id="inventory-import-file" type="file" accept=".xlsx,.xls" hidden aria-label="Pilih file Excel inventaris">
                <button type="button" data-open-inventory-modal data-permission="inventaris.store,inventaris.next-kode,jenis.select,lokasi.select,instansi.select,pendanaan.select" class="inventory-header-button inventory-header-primary">
                    <i class="fas fa-plus" aria-hidden="true"></i> Tambah barang
                </button>
            </div>
        </div>

        <div class="master-info-card">
            <span class="master-info-icon"><i class="fas fa-boxes" aria-hidden="true"></i></span>
            <div>
                <strong>Data inventaris langsung dari server</strong>
                <p>Gunakan pencarian dan filter untuk menemukan barang. Kode inventaris dibuat otomatis saat disimpan.</p>
            </div>
        </div>

        <section class="inventory-filter-card" aria-label="Filter inventaris">
            <div class="inventory-filter-heading">
                <div><h3>Filter barang</h3><p>Hasil pencarian diproses oleh server</p></div>
                <button type="button" id="inventory-reset-filter"><i class="fas fa-undo-alt" aria-hidden="true"></i> Reset filter</button>
            </div>
            <div class="inventory-filter-grid">
                <label class="inventory-field">
                    <span>Cari barang atau kode</span>
                    <input id="inventory-search" type="search" placeholder="Ketik nama atau kode...">
                </label>
                <div class="inventory-field">
                    <label for="inventory-filter-instansi">Instansi</label>
                    <span class="master-select-wrap">
                        <select id="inventory-filter-instansi"><option value="">Semua instansi</option></select>
                        <i class="fas fa-chevron-down" aria-hidden="true"></i>
                    </span>
                </div>
                <div class="inventory-field">
                    <label for="inventory-filter-lokasi">Lokasi</label>
                    <span class="master-select-wrap">
                        <select id="inventory-filter-lokasi"><option value="">Semua lokasi</option></select>
                        <i class="fas fa-chevron-down" aria-hidden="true"></i>
                    </span>
                </div>
                <div class="inventory-field">
                    <label for="inventory-filter-jenis">Jenis</label>
                    <span class="master-select-wrap">
                        <select id="inventory-filter-jenis"><option value="">Semua jenis</option></select>
                        <i class="fas fa-chevron-down" aria-hidden="true"></i>
                    </span>
                </div>
                <div class="inventory-field">
                    <label for="inventory-filter-pendanaan">Pendanaan</label>
                    <span class="master-select-wrap">
                        <select id="inventory-filter-pendanaan"><option value="">Semua pendanaan</option></select>
                        <i class="fas fa-chevron-down" aria-hidden="true"></i>
                    </span>
                </div>
                <div class="inventory-field">
                    <label for="inventory-filter-kondisi">Kondisi</label>
                    <span class="master-select-wrap">
                        <select id="inventory-filter-kondisi">
                            <option value="">Semua kondisi</option>
                            <option value="Baik">Baik</option>
                            <option value="Rusak">Rusak</option>
                        </select>
                        <i class="fas fa-chevron-down" aria-hidden="true"></i>
                    </span>
                </div>
            </div>
            <label class="inventory-filter-check"><input id="inventory-filter-gudang" type="checkbox"><span>Hanya tampilkan barang di gudang</span></label>
        </section>

        <section class="master-card">
            <div class="master-card-header">
                <div><h3>Daftar inventaris</h3><p id="inventory-list-caption">Memuat data dari server...</p></div>
                <span class="master-select-wrap master-select-compact">
                    <select id="inventory-page-size" aria-label="Jumlah per halaman">
                        <option value="10">10 baris</option>
                        <option value="15" selected>15 baris</option>
                        <option value="25">25 baris</option>
                    </select>
                    <i class="fas fa-chevron-down" aria-hidden="true"></i>
                </span>
            </div>
            <div class="master-table-scroll">
                <table id="inventory-table" class="master-table inventory-data-table">
                    <thead><tr>
                        <th>Kode</th><th>Nama barang</th><th>Jenis</th><th>Lokasi</th>
                        <th>Jumlah</th><th>Kondisi</th><th class="master-action-heading">Aksi</th>
                    </tr></thead>
                    <tbody></tbody>
                </table>
            </div>
            <div class="master-table-footer">
                <span id="inventory-page-summary">Memuat data...</span>
                <div class="master-pagination">
                    <button type="button" id="inventory-prev" aria-label="Halaman sebelumnya"><i class="fas fa-chevron-left"></i></button>
                    <span id="inventory-page-number" class="master-page-number">1</span>
                    <button type="button" id="inventory-next" aria-label="Halaman berikutnya"><i class="fas fa-chevron-right"></i></button>
                </div>
            </div>
        </section>

    </section>
@endsection

@push('scripts')
    <script src="{{ asset('assets/js/inventory.js') }}"></script>
@endpush
