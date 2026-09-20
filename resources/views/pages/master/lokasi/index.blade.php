@extends('layouts.app')
@section('title', 'Master Lokasi')

@section('content')
    <section class="master-page space-y-7" data-master-resource="lokasi" data-master-url="{{ url('/app/lokasi') }}" data-instansi-url="{{ url('/app/instansi/select') }}">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[.18em] text-indigo-600">Master Data</p>
                <h2 class="mt-2 text-3xl font-bold tracking-tight text-slate-900">Lokasi</h2>
                <p class="mt-2 text-sm text-slate-500">Atur tempat penyimpanan dan penempatan barang pada setiap instansi.</p>
            </div>
            <button type="button" data-permission="lokasi.store,instansi.select" class="master-add-button inline-flex items-center justify-center rounded-xl bg-indigo-600 px-5 py-3 text-sm font-semibold text-white hover:bg-indigo-700">
                <i class="fas fa-plus" aria-hidden="true"></i>&nbsp; Tambah lokasi
            </button>
        </div>

        <div class="master-info-card">
            <span class="master-info-icon"><i class="fas fa-map-marker-alt" aria-hidden="true"></i></span>
            <div><strong>Lokasi barang</strong><p>Setiap lokasi terhubung ke satu instansi dan dapat ditandai sebagai gudang.</p></div>
        </div>

        <section class="master-card">
            <div class="master-card-header">
                <div><h3>Daftar lokasi</h3><p>Data dimuat langsung dari server</p></div>
                <div class="master-toolbar">
                    <label class="master-search"><i class="fas fa-search" aria-hidden="true"></i><input type="search" placeholder="Cari lokasi..." aria-label="Cari lokasi"></label>
                    <span class="master-select-wrap master-select-compact">
                        <select class="master-page-size" aria-label="Jumlah per halaman">
                            <option value="10">10 baris</option>
                            <option value="15" selected>15 baris</option>
                            <option value="25">25 baris</option>
                        </select>
                        <i class="fas fa-chevron-down" aria-hidden="true"></i>
                    </span>
                </div>
            </div>
            <div class="master-table-scroll">
                <table class="master-table"><thead><tr><th>Nama lokasi</th><th>Instansi</th><th>Tipe</th><th class="master-action-heading">Aksi</th></tr></thead><tbody></tbody></table>
            </div>
            <div class="master-table-footer"><span class="master-page-summary">Memuat data...</span><div class="master-pagination"><button type="button" class="master-prev" aria-label="Halaman sebelumnya"><i class="fas fa-chevron-left"></i></button><span class="master-page-number">1</span><button type="button" class="master-next" aria-label="Halaman berikutnya"><i class="fas fa-chevron-right"></i></button></div></div>
        </section>

        <div class="master-dialog" aria-hidden="true">
            <div class="master-dialog-backdrop"></div>
            <section class="master-dialog-panel" role="dialog" aria-modal="true" aria-labelledby="master-lokasi-title">
                <div class="master-dialog-head"><div><p>MASTER DATA</p><h2 id="master-lokasi-title" class="master-dialog-title">Tambah lokasi</h2></div><button type="button" class="master-dialog-close" aria-label="Tutup"><i class="fas fa-times"></i></button></div>
                <form class="master-form">
                    <label class="master-field"><span>Nama lokasi <b>*</b></span><input name="nama" maxlength="20" placeholder="Contoh: Ruang Kelas A" required></label>
                    <div class="master-field">
                        <label for="master-instansi-select">Instansi <b>*</b></label>
                        <span class="master-select-wrap">
                            <select id="master-instansi-select" name="instansi_id" required>
                                <option value="">Pilih instansi</option>
                            </select>
                            <i class="fas fa-chevron-down" aria-hidden="true"></i>
                        </span>
                    </div>
                    <label class="master-checkbox"><input type="checkbox" name="is_gudang" value="1"><span>Tandai sebagai gudang</span></label>
                    <p class="master-form-error" role="alert" hidden></p>
                    <div class="master-dialog-actions"><button type="button" class="master-cancel">Batal</button><button type="submit" class="master-save">Simpan lokasi</button></div>
                </form>
            </section>
        </div>
    </section>
@endsection

@push('scripts')
    <script src="{{ asset('assets/js/master-data.js') }}"></script>
@endpush
