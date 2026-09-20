<div id="user-modal" class="inventory-modal" aria-hidden="true">
    <div class="inventory-modal-backdrop"></div>
    <section class="inventory-modal-panel user-modal-panel" role="dialog" aria-modal="true" aria-labelledby="user-modal-title">
        <div class="inventory-modal-header">
            <div class="inventory-modal-heading">
                <span class="inventory-modal-icon"><i class="fas fa-user-cog" aria-hidden="true"></i></span>
                <div><p class="inventory-modal-eyebrow">DATA PENGGUNA</p><h2 id="user-modal-title">Tambah pengguna</h2><p>Isi identitas, jabatan, dan akses pengguna.</p></div>
            </div>
            <button type="button" class="inventory-modal-close" data-close-user-modal aria-label="Tutup modal"><i class="fas fa-times" aria-hidden="true"></i></button>
        </div>
        <form id="user-form" class="inventory-modal-form">
            <div class="user-form-grid">
                <label class="inventory-field"><span>Nama lengkap <b>*</b></span><input name="name" type="text" maxlength="20" placeholder="Nama pengguna" required></label>
                <label class="inventory-field"><span>Username <b>*</b></span><input name="username" type="text" maxlength="15" autocomplete="off" placeholder="Username untuk masuk" required></label>
                <div class="inventory-field"><label for="user-form-status">Jabatan <b>*</b></label><span class="master-select-wrap"><select id="user-form-status" name="status" required><option value="">Pilih jabatan</option><option value="Guru">Guru</option><option value="Kepsek">Kepala sekolah</option><option value="Pimpinan">Pimpinan</option><option value="Karyawan">Karyawan</option><option value="Staff">Staf</option></select><i class="fas fa-chevron-down" aria-hidden="true"></i></span></div>
                <div class="inventory-field"><label for="user-form-role">Peran <b>*</b></label><span class="master-select-wrap"><select id="user-form-role" name="role" required><option value="">Pilih peran</option><option value="1">Administrator</option><option value="2">Staf</option><option value="3">Guru / petugas</option></select><i class="fas fa-chevron-down" aria-hidden="true"></i></span></div>
                <label class="inventory-field"><span>Kata sandi <b id="user-password-required">*</b></span><span class="user-password-field"><input name="password" type="password" minlength="6" autocomplete="new-password" placeholder="Minimal 6 karakter"><button type="button" class="user-password-toggle" data-toggle-password="password" aria-label="Tampilkan kata sandi"><i class="fas fa-eye" aria-hidden="true"></i></button></span></label>
                <label class="inventory-field"><span>Konfirmasi kata sandi <b id="user-confirm-required">*</b></span><span class="user-password-field"><input name="password_confirmation" type="password" minlength="6" autocomplete="new-password" placeholder="Ulangi kata sandi"><button type="button" class="user-password-toggle" data-toggle-password="password_confirmation" aria-label="Tampilkan konfirmasi kata sandi"><i class="fas fa-eye" aria-hidden="true"></i></button></span></label>
            </div>
            <p id="user-password-hint" class="user-form-hint" hidden>Kosongkan kata sandi jika tidak ingin mengubahnya.</p>
            <p id="user-form-error" class="master-form-error" role="alert" hidden></p>
            <div class="inventory-modal-actions"><button type="button" class="inventory-button-secondary" data-close-user-modal>Batal</button><button type="submit" id="user-save" class="inventory-button-primary"><i class="fas fa-check" aria-hidden="true"></i> Simpan pengguna</button></div>
        </form>
    </section>
</div>
