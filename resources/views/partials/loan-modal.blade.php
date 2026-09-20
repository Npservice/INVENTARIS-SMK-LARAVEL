<div id="loan-modal" class="inventory-modal" aria-hidden="true">
    <div class="inventory-modal-backdrop"></div>
    <section class="inventory-modal-panel loan-modal-panel" role="dialog" aria-modal="true" aria-labelledby="loan-modal-title">
        <div class="inventory-modal-header">
            <div class="inventory-modal-heading">
                <span class="inventory-modal-icon"><i class="fas fa-exchange-alt" aria-hidden="true"></i></span>
                <div><p class="inventory-modal-eyebrow">DATA PEMINJAMAN</p><h2 id="loan-modal-title">Buat peminjaman</h2><p>Isi data peminjam dan jadwal pengembalian.</p></div>
            </div>
            <button type="button" class="inventory-modal-close" data-close-loan-modal aria-label="Tutup modal"><i class="fas fa-times" aria-hidden="true"></i></button>
        </div>
        <form id="loan-form" class="inventory-modal-form">
            <div class="inventory-form-grid loan-form-grid">
                <div class="inventory-field loan-field-wide">
                    <label for="loan-inventory">Barang inventaris <b>*</b></label>
                    <span class="master-select-wrap"><select id="loan-inventory" name="id_inventaris" required disabled><option value="">Memuat barang...</option></select><i class="fas fa-chevron-down" aria-hidden="true"></i></span>
                    <small id="loan-inventory-stock" class="loan-field-hint">Pilih barang untuk melihat stok tersedia.</small>
                </div>
                <label class="inventory-field"><span>Nama peminjam <b>*</b></span><input name="nama_pjm" type="text" maxlength="20" placeholder="Nama peminjam" required></label>
                <div class="inventory-field">
                    <label for="loan-category">Kategori peminjam <b>*</b></label>
                    <span class="master-select-wrap"><select id="loan-category" name="status_pjm" required><option value="">Pilih kategori</option><option value="Guru">Guru</option><option value="Siswa">Siswa</option><option value="Staff">Staff</option></select><i class="fas fa-chevron-down" aria-hidden="true"></i></span>
                </div>
                <label class="inventory-field"><span>Tanggal pinjam <b>*</b></span><input name="tanggal_pinjam" type="date" required></label>
                <label class="inventory-field"><span>Batas kembali <b>*</b></span><input name="tanggal_kembali" type="date" required></label>
                <label class="inventory-field"><span>Waktu pinjam</span><input name="waktu_pinjam" type="time" step="1"></label>
                <label class="inventory-field"><span>Petugas pencatat</span><input id="loan-operator" type="text" value="{{ auth()->user()->name }}" readonly></label>
                <label class="inventory-field loan-field-full"><span>Keterangan</span><textarea name="keterangan_pjm" rows="3" maxlength="70" placeholder="Catatan tambahan (maksimal 70 karakter)"></textarea></label>
            </div>
            <p id="loan-form-error" class="master-form-error" role="alert" hidden></p>
            <div class="inventory-modal-actions"><button type="button" class="inventory-button-secondary" data-close-loan-modal>Batal</button><button type="submit" id="loan-save" class="inventory-button-primary"><i class="fas fa-check" aria-hidden="true"></i> Simpan peminjaman</button></div>
        </form>
    </section>
</div>
