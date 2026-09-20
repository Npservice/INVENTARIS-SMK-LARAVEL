(function ($) {
    'use strict';

    const $page = $('#loan-index-page, #loan-show-page').first();
    if (!$page.length || typeof Swal === 'undefined') return;

    const isShowPage = $page.is('#loan-show-page');
    const apiUrl = $page.attr('data-api-url');
    const detailBaseUrl = $page.attr('data-detail-base-url');
    const currentUserId = String($page.attr('data-current-user-id'));
    const currentUserName = $page.attr('data-current-user-name');
    const $modal = $('#loan-modal');
    const $form = $('#loan-form');
    const $formError = $('#loan-form-error');
    const $save = $('#loan-save');
    let currentPage = 1;
    let hasNextPage = false;
    let pendingList = null;
    let searchTimer = null;
    let selectCounter = 0;
    let inventoryToken = 0;
    let editingId = null;
    let editingUserId = null;
    let detailItem = null;
    let inventoryOptions = [];

    const toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3500,
        timerProgressBar: true,
        customClass: { popup: 'modern-toast', title: 'modern-toast-title' },
    });
    const swalStyle = {
        popup: 'modern-swal-popup',
        title: 'modern-swal-title',
        htmlContainer: 'modern-swal-html',
        confirmButton: 'modern-swal-confirm',
        cancelButton: 'modern-swal-cancel',
    };

    function apiError(xhr) {
        const response = xhr && xhr.responseJSON;
        if (xhr && xhr.status === 422 && response && response.errors) {
            return Object.values(response.errors).flat().join(' ');
        }
        if (xhr && [401, 403, 419].includes(xhr.status)) {
            return 'Sesi tidak aktif. Silakan masuk kembali.';
        }
        return response && response.message ? response.message :
            xhr && xhr.message ? xhr.message : 'Permintaan gagal. Silakan coba lagi.';
    }

    function requestOptions() {
        return { dataType: 'json', headers: { Accept: 'application/json' } };
    }

    function writeOptions() {
        return {
            ...requestOptions(),
            contentType: 'application/json',
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            },
        };
    }

    function formatDate(value) {
        if (!value) return '—';
        const date = new Date(String(value).slice(0, 10) + 'T00:00:00Z');
        return Number.isNaN(date.getTime()) ? String(value) :
            new Intl.DateTimeFormat('id-ID', { day: 'numeric', month: 'short', year: 'numeric', timeZone: 'UTC' }).format(date);
    }

    function formatTime(value) {
        return value ? String(value).slice(0, 5) : '—';
    }

    function localDate() {
        const now = new Date();
        return new Date(now.getTime() - now.getTimezoneOffset() * 60000).toISOString().slice(0, 10);
    }

    function normalizeTime(value) {
        if (!value) return null;
        return value.length === 5 ? value + ':00' : value;
    }

    function closeSelectMenus() {
        $('.master-select-wrap.is-open').removeClass('is-open')
            .find('.master-select-trigger').attr('aria-expanded', 'false');
    }

    function refreshSelect($select) {
        const widget = $select.data('loanSelectWidget');
        if (!widget) return;
        const value = String($select.val() ?? '');
        widget.button.find('.master-select-text').text(
            $select.find('option:selected').text() || $select.find('option').first().text()
        );
        widget.button.prop('disabled', $select.prop('disabled'));
        widget.menu.empty();
        $select.find('option').each(function () {
            const selected = String(this.value) === value;
            const $option = $('<button>', { type: 'button', role: 'option' })
                .addClass('master-select-option')
                .toggleClass('is-selected', selected)
                .attr('aria-selected', selected ? 'true' : 'false')
                .attr('data-value', String(this.value))
                .append($('<span>').text(this.textContent));
            if (selected) $option.append($('<i>').addClass('fas fa-check').attr('aria-hidden', 'true'));
            widget.menu.append($option);
        });
    }

    function initSelect($select) {
        const $wrap = $select.closest('.master-select-wrap');
        const id = 'loan-select-menu-' + ++selectCounter;
        const label = $select.attr('aria-label') ||
            $('label[for="' + $select.attr('id') + '"]').text().trim() || 'Pilih opsi';
        const $button = $('<button>', {
            type: 'button',
            'aria-label': label,
            'aria-haspopup': 'listbox',
            'aria-expanded': 'false',
            'aria-controls': id,
        }).addClass('master-select-trigger')
            .append($('<span>').addClass('master-select-text'))
            .append($('<i>').addClass('fas fa-chevron-down').attr('aria-hidden', 'true'));
        const $menu = $('<div>', { id: id, role: 'listbox', 'aria-label': label })
            .addClass('master-select-menu');
        $select.attr({ tabindex: '-1', 'aria-hidden': 'true' });
        $wrap.addClass('is-enhanced').append($button, $menu);
        $select.data('loanSelectWidget', { button: $button, menu: $menu });
        refreshSelect($select);

        $button.on('click', function () {
            if ($wrap.hasClass('is-open')) { closeSelectMenus(); return; }
            closeSelectMenus();
            refreshSelect($select);
            const bounds = $button[0].getBoundingClientRect();
            $wrap.toggleClass('is-dropup', window.innerHeight - bounds.bottom < 230 && bounds.top > 230);
            $wrap.addClass('is-open');
            $button.attr('aria-expanded', 'true');
            $menu.find('.is-selected, .master-select-option').first().trigger('focus');
        });
        $button.on('keydown', function (event) {
            if (['ArrowDown', 'ArrowUp', 'Enter', ' '].includes(event.key)) {
                event.preventDefault();
                if (!$wrap.hasClass('is-open')) $button.trigger('click');
            }
        });
        $menu.on('click', '.master-select-option', function () {
            $select.val($(this).attr('data-value')).trigger('change');
            refreshSelect($select);
            closeSelectMenus();
            $button.trigger('focus');
        });
        $menu.on('keydown', '.master-select-option', function (event) {
            const $options = $menu.find('.master-select-option');
            const index = $options.index(this);
            let next = index;
            if (event.key === 'ArrowDown') next = Math.min(index + 1, $options.length - 1);
            if (event.key === 'ArrowUp') next = Math.max(index - 1, 0);
            if (event.key === 'Home') next = 0;
            if (event.key === 'End') next = $options.length - 1;
            if (next !== index) { event.preventDefault(); $options.eq(next).trigger('focus'); }
            if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); $(this).trigger('click'); }
            if (event.key === 'Escape') { event.preventDefault(); closeSelectMenus(); $button.trigger('focus'); }
            if (event.key === 'Tab') closeSelectMenus();
        });
    }

    $('#loan-index-page .master-select-wrap select, #loan-modal .master-select-wrap select')
        .each(function () { initSelect($(this)); });
    $(document).on('click', function (event) {
        if (!$(event.target).closest('.master-select-wrap').length) closeSelectMenus();
    });

    function loadInventoryOptions() {
        if (!window.AppCan('inventaris.select')) return;
        const $select = $('#loan-inventory').prop('disabled', true);
        refreshSelect($select);
        $.ajax({ ...requestOptions(), url: $page.attr('data-inventory-select-url'), method: 'GET' })
            .done(function (items) {
                if (!Array.isArray(items)) {
                    toast.fire({ icon: 'error', title: 'Format pilihan barang tidak sesuai.' });
                    return;
                }
                inventoryOptions = items;
                const selected = editingId && detailItem ? detailItem.id_inventaris : $select.val();
                $select.empty().append($('<option>').val('').text('Pilih barang inventaris'));
                items.forEach(function (item) {
                    $select.append($('<option>').val(item.id).text(item.nama + ' · ' + item.kode_invt));
                });
                $select.val(selected || '').prop('disabled', false);
                refreshSelect($select);
            }).fail(function (xhr) {
                toast.fire({ icon: 'error', title: 'Gagal memuat barang: ' + apiError(xhr) });
            });
    }

    function loadStock(id) {
        if (!window.AppCan('inventaris.show')) return;
        const token = ++inventoryToken;
        const $hint = $('#loan-inventory-stock');
        if (!id) { $hint.text('Pilih barang untuk melihat stok tersedia.'); return; }
        $hint.text('Memuat stok barang...');
        $.ajax({ ...requestOptions(), url: $page.attr('data-inventory-url') + '/' + encodeURIComponent(id), method: 'GET' })
            .done(function (item) {
                if (token !== inventoryToken) return;
                $hint.text('Stok tersedia: ' + (item.jumlah ?? 0) + ' unit · ' +
                    (item.lokasi && item.lokasi.nama ? item.lokasi.nama : 'Lokasi tidak tersedia'));
            }).fail(function (xhr) {
                if (token !== inventoryToken) return;
                $hint.text('Stok tidak tersedia: ' + apiError(xhr));
            });
    }

    function closeModal() {
        ++inventoryToken;
        $modal.removeClass('is-open').attr('aria-hidden', 'true');
        $('body').removeClass('inventory-modal-open');
        editingId = null;
        editingUserId = null;
    }

    function openModal(item) {
        if (!window.AppCan('peminjaman.' + (item ? 'update' : 'store')) ||
            !window.AppCan('inventaris.select') || !window.AppCan('inventaris.show')) return;
        editingId = item ? String(item.id_pjm) : null;
        editingUserId = item ? String(item.id_user) : currentUserId;
        $form[0].reset();
        $formError.text('').prop('hidden', true);
        $('#loan-modal-title').text(item ? 'Edit peminjaman' : 'Buat peminjaman');
        $save.prop('disabled', false).html('<i class="fas fa-check" aria-hidden="true"></i> ' +
            (item ? 'Simpan perubahan' : 'Simpan peminjaman'));

        if (item) {
            $form.find('[name="id_inventaris"]').val(item.id_inventaris);
            $form.find('[name="nama_pjm"]').val(item.nama_pjm);
            $form.find('[name="status_pjm"]').val(item.status_pjm);
            $form.find('[name="tanggal_pinjam"]').val(String(item.tanggal_pinjam || '').slice(0, 10));
            $form.find('[name="tanggal_kembali"]').val(String(item.tanggal_kembali || '').slice(0, 10));
            $form.find('[name="waktu_pinjam"]').val(item.waktu_pinjam || '');
            $form.find('[name="keterangan_pjm"]').val(item.keterangan_pjm || '');
            $('#loan-operator').val(item.user && item.user.name ? item.user.name : currentUserName);
            loadStock(item.id_inventaris);
        } else {
            $form.find('[name="tanggal_pinjam"]').val(localDate());
            const now = new Date();
            $form.find('[name="waktu_pinjam"]').val(now.toTimeString().slice(0, 8));
            $('#loan-operator').val(currentUserName);
            loadStock('');
        }
        $form.find('.master-select-wrap select').each(function () { refreshSelect($(this)); });
        $modal.addClass('is-open').attr('aria-hidden', 'false');
        $('body').addClass('inventory-modal-open');
        $form.find('[name="nama_pjm"]').trigger('focus');
    }

    $(document).on('click', '[data-open-loan-modal]', function () { openModal(null); });
    $(document).on('click', '[data-close-loan-modal]', closeModal);
    $('#loan-inventory').on('change', function () { loadStock($(this).val()); });

    $form.on('submit', function (event) {
        event.preventDefault();
        if (!window.AppCan('peminjaman.' + (editingId ? 'update' : 'store'))) return;
        for (const field of ['id_inventaris', 'status_pjm']) {
            if (!$form.find('[name="' + field + '"]').val()) {
                $formError.text('Lengkapi pilihan barang dan kategori peminjam.').prop('hidden', false);
                $form.find('[name="' + field + '"]').closest('.master-select-wrap')
                    .find('.master-select-trigger').trigger('focus');
                return;
            }
        }
        if (!this.reportValidity()) return;
        const borrowedAt = $form.find('[name="tanggal_pinjam"]').val();
        const dueAt = $form.find('[name="tanggal_kembali"]').val();
        if (dueAt < borrowedAt) {
            $formError.text('Batas kembali tidak boleh sebelum tanggal pinjam.').prop('hidden', false);
            return;
        }
        const payload = {
            id_user: editingUserId,
            id_inventaris: $form.find('[name="id_inventaris"]').val(),
            nama_pjm: $form.find('[name="nama_pjm"]').val().trim(),
            status_pjm: $form.find('[name="status_pjm"]').val(),
            waktu_pinjam: normalizeTime($form.find('[name="waktu_pinjam"]').val()),
            tanggal_pinjam: borrowedAt,
            tanggal_kembali: dueAt,
            keterangan_pjm: $form.find('[name="keterangan_pjm"]').val().trim() || null,
        };
        const wasEditing = Boolean(editingId);
        $formError.text('').prop('hidden', true);
        $save.prop('disabled', true).text('Menyimpan...');
        $.ajax({
            ...writeOptions(),
            url: editingId ? apiUrl + '/' + encodeURIComponent(editingId) : apiUrl,
            method: editingId ? 'PUT' : 'POST',
            data: JSON.stringify(payload),
        }).done(function (saved) {
            closeModal();
            toast.fire({ icon: 'success', title: 'Peminjaman berhasil disimpan.' });
            if (isShowPage) loadDetail();
            else if (saved && saved.id_pjm) {
                window.location.assign(detailBaseUrl + '/' + encodeURIComponent(saved.id_pjm));
            } else {
                loadList(1);
            }
        }).fail(function (xhr) {
            $formError.text(apiError(xhr)).prop('hidden', false);
        }).always(function () {
            $save.prop('disabled', false).html('<i class="fas fa-check" aria-hidden="true"></i> ' +
                (wasEditing ? 'Simpan perubahan' : 'Simpan peminjaman'));
        });
    });

    function listParams(targetPage) {
        return {
            page: targetPage,
            size: $('#loan-page-size').val(),
            search: $('#loan-search').val().trim(),
            status_pinjam: $('#loan-status-filter').val(),
            status_pjm: $('#loan-category-filter').val(),
            tanggal_pinjam_dari: $('#loan-date-from').val(),
            tanggal_pinjam_sampai: $('#loan-date-to').val(),
        };
    }

    function showListMessage(message) {
        $('#loan-table-body').empty().append($('<tr>').append($('<td>').attr('colspan', 7)
            .addClass('master-table-message').text(message)));
    }

    function statusBadge(status) {
        return $('<span>').addClass('master-badge ' +
            (status === 'Kembali' ? 'loan-status-returned' : 'loan-status-active'))
            .text(status || '—');
    }

    function renderRow(item) {
        const inventory = item.inventaris || {};
        const $name = $('<td>').addClass('inventory-name-cell')
            .append($('<strong>').text(inventory.nama || 'Barang tidak tersedia'))
            .append($('<small>').addClass('inventory-table-subtitle').text(inventory.kode_invt || ''));
        return $('<tr>')
            .append($name)
            .append($('<td>').text(item.nama_pjm || '—'))
            .append($('<td>').text(item.status_pjm || '—'))
            .append($('<td>').text(formatDate(item.tanggal_pinjam)))
            .append($('<td>').text(formatDate(item.tanggal_kembali)))
            .append($('<td>').append(statusBadge(item.status_pinjam)))
            .append($('<td>').addClass('inventory-action-cell').append(window.AppCan('peminjaman.show') ?
                $('<a>', {
                    href: detailBaseUrl + '/' + encodeURIComponent(item.id_pjm),
                    title: 'Detail peminjaman',
                    'aria-label': 'Detail peminjaman ' + (item.nama_pjm || ''),
                }).addClass('inventory-detail-button')
                    .append($('<i>').addClass('fas fa-eye').attr('aria-hidden', 'true')) : null
            ));
    }

    function loadList(targetPage) {
        const from = $('#loan-date-from').val();
        const to = $('#loan-date-to').val();
        if (from && to && from > to) {
            toast.fire({ icon: 'error', title: 'Tanggal akhir tidak boleh sebelum tanggal awal.' });
            return;
        }
        if (pendingList) pendingList.abort();
        showListMessage('Memuat peminjaman dari server...');
        $('#loan-summary').text('Memuat data...');
        const request = $.ajax({ ...requestOptions(), url: apiUrl, method: 'GET', data: listParams(targetPage) })
            .done(function (response) {
                if (!response || !Array.isArray(response.data)) {
                    showListMessage('Format data server tidak sesuai.');
                    return;
                }
                if (!response.data.length && targetPage > 1) {
                    setTimeout(function () { loadList(targetPage - 1); }, 0);
                    return;
                }
                const $body = $('#loan-table-body').empty();
                response.data.forEach(function (item) { $body.append(renderRow(item)); });
                if (!response.data.length) showListMessage('Belum ada peminjaman yang cocok.');
                currentPage = Number(response.current_page) || targetPage;
                hasNextPage = Boolean(response.next_page_url);
                $('#loan-page-number').text(currentPage);
                $('#loan-prev').prop('disabled', !response.prev_page_url);
                $('#loan-next').prop('disabled', !hasNextPage);
                $('#loan-page-count').text(response.data.length);
                $('#loan-page-active').text(response.data.filter(item => item.status_pinjam === 'Dipinjam').length);
                $('#loan-page-returned').text(response.data.filter(item => item.status_pinjam === 'Kembali').length);
                $('#loan-caption').text(response.data.length ? 'Data peminjaman dari server' : 'Belum ada data yang cocok');
                $('#loan-summary').text(response.data.length ?
                    'Menampilkan ' + (response.from || 1) + '–' + (response.to || response.data.length) + ' peminjaman' :
                    'Tidak ada peminjaman');
            }).fail(function (xhr, status) {
                if (status === 'abort') return;
                showListMessage(apiError(xhr));
                $('#loan-summary').text('Gagal memuat data');
                $('#loan-prev, #loan-next').prop('disabled', true);
            }).always(function () { if (pendingList === request) pendingList = null; });
        pendingList = request;
    }

    function setDetail(key, value) {
        $page.find('[data-loan="' + key + '"]').text(value ?? '—');
    }

    function loadDetail() {
        const id = $page.attr('data-loan-id');
        detailItem = null;
        $('#loan-edit, #loan-return, #loan-delete').prop('disabled', true);
        $('#loan-detail-error').text('').prop('hidden', true);
        $.ajax({ ...requestOptions(), url: apiUrl + '/' + encodeURIComponent(id), method: 'GET' })
            .done(function (item) {
                detailItem = item;
                const inventory = item.inventaris || {};
                const location = inventory.lokasi || {};
                setDetail('nama_pjm', item.nama_pjm);
                setDetail('kode_invt', inventory.kode_invt);
                setDetail('barang', inventory.nama);
                setDetail('kode_barang', inventory.kode_invt);
                setDetail('lokasi', location.nama);
                setDetail('instansi', location.instansi && location.instansi.nama);
                setDetail('peminjam', item.nama_pjm);
                setDetail('kategori', item.status_pjm);
                setDetail('petugas', item.user && item.user.name);
                setDetail('status', item.status_pinjam);
                setDetail('tanggal_pinjam', formatDate(item.tanggal_pinjam));
                setDetail('tanggal_kembali', formatDate(item.tanggal_kembali));
                setDetail('waktu_pinjam', formatTime(item.waktu_pinjam));
                setDetail('waktu_kembali', formatTime(item.waktu_kembali));
                setDetail('keterangan', item.keterangan_pjm);
                $('#loan-edit, #loan-delete').prop('disabled', false);
                $('#loan-return').prop('disabled', item.status_pinjam !== 'Dipinjam');
            }).fail(function (xhr) {
                setDetail('nama_pjm', 'Detail tidak tersedia');
                $('#loan-detail-error').text(apiError(xhr)).prop('hidden', false);
            });
    }

    $('#loan-edit').on('click', function () { if (detailItem) openModal(detailItem); });

    $('#loan-return').on('click', function () {
        if (!window.AppCan('peminjaman.kembali')) return;
        if (!detailItem || detailItem.status_pinjam !== 'Dipinjam') return;
        Swal.fire({
            icon: 'question',
            title: 'Tandai sudah kembali?',
            text: 'Stok barang akan bertambah kembali setelah pengembalian dicatat.',
            showCancelButton: true,
            confirmButtonText: 'Ya, kembalikan',
            cancelButtonText: 'Batal',
            reverseButtons: true,
            customClass: swalStyle,
            buttonsStyling: false,
            allowOutsideClick: false,
            allowEscapeKey: false,
        }).then(function (result) {
            if (!result.isConfirmed) return;
            $.ajax({ ...writeOptions(), url: apiUrl + '/' + encodeURIComponent(detailItem.id_pjm) + '/kembali', method: 'POST' })
                .done(function () {
                    toast.fire({ icon: 'success', title: 'Barang berhasil dikembalikan.' });
                    loadDetail();
                }).fail(function (xhr) {
                    Swal.fire({ icon: 'error', title: 'Gagal mengembalikan', text: apiError(xhr),
                        confirmButtonText: 'Tutup', customClass: swalStyle, buttonsStyling: false,
                        allowOutsideClick: false, allowEscapeKey: false });
                });
        });
    });

    $('#loan-delete').on('click', function () {
        if (!window.AppCan('peminjaman.destroy')) return;
        if (!detailItem) return;
        Swal.fire({
            icon: 'warning',
            title: 'Hapus peminjaman?',
            text: 'Catatan peminjaman ' + detailItem.nama_pjm + ' akan dihapus.',
            showCancelButton: true,
            confirmButtonText: 'Ya, hapus',
            cancelButtonText: 'Batal',
            reverseButtons: true,
            customClass: swalStyle,
            buttonsStyling: false,
            allowOutsideClick: false,
            allowEscapeKey: false,
        }).then(function (result) {
            if (!result.isConfirmed) return;
            $.ajax({ ...writeOptions(), url: apiUrl + '/' + encodeURIComponent(detailItem.id_pjm), method: 'DELETE' })
                .done(function () {
                    window.location.assign($page.attr('data-index-url'));
                }).fail(function (xhr) {
                    Swal.fire({ icon: 'error', title: 'Gagal menghapus', text: apiError(xhr),
                        confirmButtonText: 'Tutup', customClass: swalStyle, buttonsStyling: false,
                        allowOutsideClick: false, allowEscapeKey: false });
                });
        });
    });

    $('#loan-search').on('input', function () {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(function () { loadList(1); }, 350);
    });
    $('#loan-status-filter, #loan-category-filter, #loan-page-size').on('change', function () { loadList(1); });
    $('#loan-date-from, #loan-date-to').on('change', function () { loadList(1); });
    $('#loan-reset').on('click', function () {
        clearTimeout(searchTimer);
        $('#loan-search').val('');
        $('#loan-date-from, #loan-date-to').val('');
        $('#loan-status-filter, #loan-category-filter').val('').each(function () { refreshSelect($(this)); });
        loadList(1);
    });
    $('#loan-prev').on('click', function () { if (currentPage > 1) loadList(currentPage - 1); });
    $('#loan-next').on('click', function () { if (hasNextPage) loadList(currentPage + 1); });

    $('#loan-code-find').on('click', function () {
        if (!window.AppCan('peminjaman.find-by-kode') || !window.AppCan('peminjaman.show')) return;
        const code = $('#loan-code-search').val().trim();
        if (!code) { $('#loan-code-search').trigger('focus'); return; }
        const $button = $(this).prop('disabled', true);
        $.ajax({ ...requestOptions(), url: apiUrl + '/find-by-kode', method: 'GET', data: { kode: code } })
            .done(function (item) {
                if (item && item.id_pjm) {
                    window.location.assign(detailBaseUrl + '/' + encodeURIComponent(item.id_pjm));
                }
            }).fail(function (xhr) {
                toast.fire({ icon: 'error', title: apiError(xhr) });
            }).always(function () { $button.prop('disabled', false); });
    });
    $('#loan-code-search').on('keydown', function (event) {
        if (event.key === 'Enter') { event.preventDefault(); $('#loan-code-find').trigger('click'); }
    });

    if (window.AppCan('inventaris.select')) loadInventoryOptions();
    if (isShowPage) loadDetail();
    else loadList(1);
})(jQuery);
