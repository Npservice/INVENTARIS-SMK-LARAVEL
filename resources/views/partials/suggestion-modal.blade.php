<div id="suggestion-modal" class="inventory-modal" aria-hidden="true">
    <div class="inventory-modal-backdrop"></div>
    <section class="inventory-modal-panel suggestion-modal-panel" role="dialog" aria-modal="true" aria-labelledby="suggestion-modal-title">
        <div class="inventory-modal-header">
            <div class="inventory-modal-heading">
                <span class="inventory-modal-icon"><i class="fas fa-comment-dots" aria-hidden="true"></i></span>
                <div><p class="inventory-modal-eyebrow">MASUKAN SEKOLAH</p><h2 id="suggestion-modal-title">Tambah masukan</h2><p>Catat usulan atau saran yang diterima.</p></div>
            </div>
            <button type="button" class="inventory-modal-close" data-close-suggestion-modal aria-label="Tutup modal"><i class="fas fa-times" aria-hidden="true"></i></button>
        </div>
        <form id="suggestion-form" class="inventory-modal-form">
            <div class="suggestion-form-fields">
                <label class="inventory-field"><span>Nama pengirim <b>*</b></span><input name="nama" type="text" maxlength="100" placeholder="Masukkan nama pengirim" required></label>
                <label class="inventory-field"><span>Isi usulan atau saran <b>*</b></span><textarea name="kritik_saran" rows="6" placeholder="Tuliskan isi masukan secara jelas" required></textarea></label>
            </div>
            <p id="suggestion-form-error" class="master-form-error" role="alert" hidden></p>
            <div class="inventory-modal-actions"><button type="button" class="inventory-button-secondary" data-close-suggestion-modal>Batal</button><button type="submit" id="suggestion-save" class="inventory-button-primary"><i class="fas fa-check" aria-hidden="true"></i> Simpan masukan</button></div>
        </form>
    </section>
</div>
