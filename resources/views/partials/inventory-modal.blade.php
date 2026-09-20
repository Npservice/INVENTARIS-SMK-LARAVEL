<div id="inventory-modal" class="inventory-modal" aria-hidden="true">
    <div class="inventory-modal-backdrop"></div>
    <section class="inventory-modal-panel" role="dialog" aria-modal="true" aria-labelledby="inventory-modal-title">
        <div class="inventory-modal-header">
            <div class="inventory-modal-heading">
                <span class="inventory-modal-icon"><i class="fas fa-box-open" aria-hidden="true"></i></span>
                <div>
                    <p class="inventory-modal-eyebrow">DATA INVENTARIS</p>
                    <h2 id="inventory-modal-title">Tambah barang baru</h2>
                    <p>Lengkapi informasi barang sesuai data inventaris sekolah.</p>
                </div>
            </div>
            <button type="button" class="inventory-modal-close" data-close-inventory-modal aria-label="Tutup modal">
                <i class="fas fa-times" aria-hidden="true"></i>
            </button>
        </div>

        <form id="inventory-form" class="inventory-modal-form">
            <div class="inventory-form-grid">
                <label class="inventory-field inventory-field-half">
                    <span>Kode inventaris</span>
                    <input id="inventory-kode" type="text" value="Memuat kode..." readonly aria-describedby="inventory-kode-note">
                </label>
                <label class="inventory-field inventory-field-half">
                    <span>Nama barang <b>*</b></span>
                    <input name="nama" type="text" maxlength="40" placeholder="Contoh: Proyektor Epson EB-X49" required>
                </label>
                <div class="inventory-field">
                    <label for="inventory-instansi">Instansi <b>*</b></label>
                    <span class="master-select-wrap">
                        <select id="inventory-instansi" name="instansi_id" required disabled><option value="">Memuat instansi...</option></select>
                        <i class="fas fa-chevron-down" aria-hidden="true"></i>
                    </span>
                </div>
                <div class="inventory-field">
                    <label for="inventory-lokasi">Lokasi <b>*</b></label>
                    <span class="master-select-wrap">
                        <select id="inventory-lokasi" name="lokasi_id" required disabled><option value="">Pilih instansi dahulu</option></select>
                        <i class="fas fa-chevron-down" aria-hidden="true"></i>
                    </span>
                </div>
                <div class="inventory-field">
                    <label for="inventory-jenis">Jenis barang <b>*</b></label>
                    <span class="master-select-wrap">
                        <select id="inventory-jenis" name="jenis_id" required><option value="">Pilih jenis barang</option></select>
                        <i class="fas fa-chevron-down" aria-hidden="true"></i>
                    </span>
                </div>
                <div class="inventory-field">
                    <label for="inventory-kondisi">Kondisi <b>*</b></label>
                    <span class="master-select-wrap">
                        <select id="inventory-kondisi" name="kondisi" required>
                            <option value="">Pilih kondisi</option>
                            <option value="Baik">Baik</option>
                            <option value="Rusak">Rusak</option>
                        </select>
                        <i class="fas fa-chevron-down" aria-hidden="true"></i>
                    </span>
                </div>
                <label class="inventory-field">
                    <span>Tanggal masuk <b>*</b></span>
                    <input name="masuk" type="date" required>
                </label>
                <label class="inventory-field">
                    <span>Jumlah <b>*</b></span>
                    <input name="jumlah" type="number" min="1" step="1" value="1" required>
                </label>
                <div class="inventory-field">
                    <label for="inventory-pendanaan">Pendanaan <b>*</b></label>
                    <span class="master-select-wrap">
                        <select id="inventory-pendanaan" name="pendanaan_id" required><option value="">Pilih pendanaan</option></select>
                        <i class="fas fa-chevron-down" aria-hidden="true"></i>
                    </span>
                </div>
                <label class="inventory-field">
                    <span>Harga beli</span>
                    <input id="inventory-price-display" type="text" inputmode="decimal" autocomplete="off" data-rupiah-input data-rupiah-target="#inventory-price-raw" placeholder="Rp 0">
                    <input id="inventory-price-raw" name="harga_beli" type="hidden">
                </label>
                <label class="inventory-field inventory-field-wide">
                    <span>Keterangan</span>
                    <textarea name="keterangan" rows="3" maxlength="70" placeholder="Catatan tambahan (maksimal 70 karakter)"></textarea>
                </label>
            </div>
            <p id="inventory-kode-note" class="inventory-modal-note"><i class="fas fa-info-circle" aria-hidden="true"></i> Kode ditetapkan otomatis saat data disimpan.</p>
            <p id="inventory-form-error" class="master-form-error" role="alert" hidden></p>
            <div class="inventory-modal-actions">
                <button type="button" class="inventory-button-secondary" data-close-inventory-modal>Batal</button>
                <button type="submit" id="inventory-save" class="inventory-button-primary"><i class="fas fa-check" aria-hidden="true"></i> Simpan barang</button>
            </div>
        </form>
    </section>
</div>
