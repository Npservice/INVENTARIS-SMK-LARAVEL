<div id="role-modal" class="inventory-modal" aria-hidden="true">
    <div class="inventory-modal-backdrop"></div>
    <section class="inventory-modal-panel role-modal-panel" role="dialog" aria-modal="true" aria-labelledby="role-modal-title">
        <div class="inventory-modal-header">
            <div class="inventory-modal-heading">
                <span class="inventory-modal-icon"><i class="fas fa-user-shield" aria-hidden="true"></i></span>
                <div><p class="inventory-modal-eyebrow">MANAJEMEN AKSES</p><h2 id="role-modal-title">Tambah role</h2><p>Tentukan nama role dan izin fitur yang diberikan.</p></div>
            </div>
            <button type="button" class="inventory-modal-close" data-close-role-modal aria-label="Tutup modal"><i class="fas fa-times" aria-hidden="true"></i></button>
        </div>
        <form id="role-form" class="inventory-modal-form">
            <label class="inventory-field"><span>Nama role <b>*</b></span><input name="name" type="text" maxlength="50" placeholder="Contoh: pengelola sarpras" required></label>
            <p id="role-form-error" class="master-form-error" role="alert" hidden></p>
            <div class="inventory-modal-actions role-modal-actions"><button type="button" class="inventory-button-secondary" data-close-role-modal>Batal</button><button type="submit" id="role-save" class="inventory-button-primary"><i class="fas fa-check" aria-hidden="true"></i> Simpan role</button></div>
            <div class="role-permission-heading"><div><h3>Izin fitur</h3><p>Pilih tindakan yang dapat dilakukan role ini.</p></div><span id="role-selected-count">0 izin dipilih</span></div>
            <div id="role-permission-groups" class="role-permission-groups"><p class="role-permission-message">Memuat daftar izin...</p></div>
        </form>
    </section>
</div>
