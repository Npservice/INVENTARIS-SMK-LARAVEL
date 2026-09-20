<div id="warehouse-stock-modal" class="inventory-modal" aria-hidden="true">
    <div class="inventory-modal-backdrop"></div>
    <section class="inventory-modal-panel warehouse-stock-panel" role="dialog" aria-modal="true" aria-labelledby="warehouse-stock-title">
        <div class="inventory-modal-header">
            <div class="inventory-modal-heading">
                <span class="inventory-modal-icon"><i class="fas fa-layer-group" aria-hidden="true"></i></span>
                <div>
                    <p class="inventory-modal-eyebrow">BARANG GUDANG</p>
                    <h2 id="warehouse-stock-title">Update stok</h2>
                    <p>Perbarui jumlah unit yang tersimpan di gudang.</p>
                </div>
            </div>
            <button type="button" class="inventory-modal-close" data-close-warehouse-stock aria-label="Tutup modal"><i class="fas fa-times" aria-hidden="true"></i></button>
        </div>
        <form id="warehouse-stock-form" class="inventory-modal-form">
            <div class="warehouse-stock-item"><strong id="warehouse-stock-name">Memuat barang...</strong><span id="warehouse-stock-code">—</span></div>
            <label class="inventory-field">
                <span>Jumlah stok terbaru <b>*</b></span>
                <input id="warehouse-stock-quantity" name="jumlah" type="number" min="1" step="1" required>
            </label>
            <p id="warehouse-stock-error" class="master-form-error" role="alert" hidden></p>
            <div class="inventory-modal-actions">
                <button type="button" class="inventory-button-secondary" data-close-warehouse-stock>Batal</button>
                <button type="submit" id="warehouse-stock-save" class="inventory-button-primary"><i class="fas fa-check" aria-hidden="true"></i> Simpan stok</button>
            </div>
        </form>
    </section>
</div>
