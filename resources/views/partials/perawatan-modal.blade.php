<div id="inventory-care-modal" class="inventory-modal" aria-hidden="true">
    <div class="inventory-modal-backdrop"></div>
    <section class="inventory-modal-panel inventory-care-modal-panel" role="dialog" aria-modal="true" aria-labelledby="inventory-care-modal-title">
        <div class="inventory-modal-header">
            <div class="inventory-modal-heading">
                <span class="inventory-modal-icon"><i class="fas fa-tools" aria-hidden="true"></i></span>
                <div><p class="inventory-modal-eyebrow">RIWAYAT PERAWATAN</p><h2 id="inventory-care-modal-title">Tambah perawatan</h2><p>Catat perawatan untuk barang inventaris ini.</p></div>
            </div>
            <button type="button" class="inventory-modal-close" data-close-care-modal aria-label="Tutup form perawatan"><i class="fas fa-times" aria-hidden="true"></i></button>
        </div>
        <form id="inventory-care-form" class="inventory-modal-form">
            <div class="inventory-form-grid">
                <label class="inventory-field">
                    <span>Tanggal perawatan <b>*</b></span>
                    <input name="tanggal_perawatan" type="date" required>
                </label>
                <div class="inventory-field">
                    <label for="inventory-care-status">Status <b>*</b></label>
                    <span class="master-select-wrap">
                        <select id="inventory-care-status" name="status_perawatan" required>
                            <option value="">Pilih status</option>
                            <option value="Proses">Proses</option>
                            <option value="Selesai">Selesai</option>
                        </select>
                        <i class="fas fa-chevron-down" aria-hidden="true"></i>
                    </span>
                </div>
                <label class="inventory-field">
                    <span>Biaya</span>
                    <input id="inventory-care-cost-display" type="text" inputmode="decimal" autocomplete="off" data-rupiah-input data-rupiah-target="#inventory-care-cost-raw" placeholder="Rp 0">
                    <input id="inventory-care-cost-raw" name="biaya" type="hidden">
                </label>
                <label class="inventory-field">
                    <span>Petugas</span>
                    <input id="inventory-care-user" type="text" value="{{ auth()->user()->name }}" readonly>
                </label>
                <label class="inventory-field inventory-field-wide">
                    <span>Keterangan</span>
                    <textarea name="keterangan" rows="3" maxlength="70" placeholder="Catatan perawatan (maksimal 70 karakter)"></textarea>
                </label>
            </div>
            <p id="inventory-care-error" class="master-form-error" role="alert" hidden></p>
            <div class="inventory-modal-actions">
                <button type="button" class="inventory-button-secondary" data-close-care-modal>Batal</button>
                <button type="submit" id="inventory-care-save" class="inventory-button-primary"><i class="fas fa-check" aria-hidden="true"></i> Simpan perawatan</button>
            </div>
        </form>
    </section>
</div>
