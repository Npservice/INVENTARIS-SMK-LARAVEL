(function ($) {
    'use strict';

    const $page = $('#inventory-show-page');
    if (!$page.length || typeof Swal === 'undefined') return;
    if (!window.AppCan('perawatan.index')) return;

    const itemId = String($page.attr('data-item-id'));
    const apiUrl = $page.attr('data-care-url');
    const currentUserId = String($page.attr('data-current-user-id'));
    const currentUserName = $page.attr('data-current-user-name');
    const $modal = $('#inventory-care-modal');
    const $form = $('#inventory-care-form');
    const $body = $('#inventory-care-body');
    const $error = $('#inventory-care-error');
    const $save = $('#inventory-care-save');
    const rows = new Map();
    let page = 1;
    let nextPage = false;
    let editingId = null;
    let editingUserId = null;
    let pending = null;

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

    function apiError(xhr) {
        const response = xhr && xhr.responseJSON;
        if (xhr && xhr.status === 422 && response && response.errors) {
            return Object.values(response.errors).flat().join(' ');
        }
        if (xhr && [401, 403, 419].includes(xhr.status)) {
            return 'Sesi tidak aktif. Silakan masuk kembali.';
        }
        return response && response.message ? response.message : 'Permintaan gagal. Silakan coba lagi.';
    }

    function formatDate(value) {
        if (!value) return '—';
        const date = new Date(String(value).slice(0, 10) + 'T00:00:00Z');
        return Number.isNaN(date.getTime()) ? String(value) :
            new Intl.DateTimeFormat('id-ID', { day: 'numeric', month: 'short', year: 'numeric', timeZone: 'UTC' }).format(date);
    }

    function formatMoney(value) {
        return window.Rupiah.format(value);
    }

    function showMessage(message) {
        $body.empty().append($('<tr>').append($('<td>').attr('colspan', 6)
            .addClass('master-table-message').text(message)));
    }

    function actionButton(icon, title, action, id) {
        return $('<button>', { type: 'button', title: title })
            .addClass(action === 'delete' ? 'inventory-row-delete' : 'inventory-detail-button')
            .attr('data-care-action', action)
            .attr('data-care-id', id)
            .attr('aria-label', title)
            .append($('<i>').addClass('fas ' + icon).attr('aria-hidden', 'true'));
    }

    function renderRow(care) {
        const $actions = $('<td>').addClass('inventory-action-cell');
        if (window.AppCan('perawatan.show') && window.AppCan('perawatan.update')) {
            $actions.append(actionButton('fa-pen', 'Edit perawatan', 'edit', care.id));
        }
        if (window.AppCan('perawatan.destroy')) {
            $actions.append(actionButton('fa-trash-alt', 'Hapus perawatan', 'delete', care.id));
        }
        return $('<tr>')
            .append($('<td>').text(formatDate(care.tanggal_perawatan)))
            .append($('<td>').append($('<span>').addClass('master-badge')
                .text(care.status_perawatan || '—')))
            .append($('<td>').text(care.user && care.user.name ? care.user.name : '—'))
            .append($('<td>').text(formatMoney(care.biaya)))
            .append($('<td>').text(care.keterangan || '—'))
            .append($actions);
    }

    function loadCare(targetPage) {
        if (pending) pending.abort();
        showMessage('Memuat riwayat perawatan...');
        $('#inventory-care-summary').text('Memuat data...');
        const request = $.ajax({
            ...requestOptions(),
            url: apiUrl,
            method: 'GET',
            data: { inventaris_id: itemId, page: targetPage, size: 10 },
        }).done(function (response) {
            if (!response || !Array.isArray(response.data)) {
                showMessage('Format data perawatan tidak sesuai.');
                return;
            }
            if (!response.data.length && targetPage > 1) {
                loadCare(targetPage - 1);
                return;
            }
            page = Number(response.current_page) || targetPage;
            nextPage = Boolean(response.next_page_url);
            rows.clear();
            $body.empty();
            response.data.forEach(function (care) {
                rows.set(String(care.id), care);
                $body.append(renderRow(care));
            });
            if (!response.data.length) showMessage('Belum ada riwayat perawatan.');
            $('#inventory-care-page').text(page);
            $('#inventory-care-prev').prop('disabled', !response.prev_page_url);
            $('#inventory-care-next').prop('disabled', !nextPage);
            $('#inventory-care-summary').text(response.data.length ?
                'Menampilkan ' + (response.from || 1) + '–' + (response.to || response.data.length) + ' perawatan' :
                'Belum ada perawatan');
        }).fail(function (xhr, status) {
            if (status === 'abort') return;
            showMessage('Gagal memuat riwayat: ' + apiError(xhr));
            $('#inventory-care-summary').text('Data tidak tersedia');
        }).always(function () { if (pending === request) pending = null; });
        pending = request;
    }

    function closeModal() {
        $modal.removeClass('is-open').attr('aria-hidden', 'true');
        $('body').removeClass('inventory-modal-open');
        editingId = null;
        editingUserId = null;
    }

    function openModal(care) {
        if (!window.AppCan('perawatan.' + (care ? 'update' : 'store'))) return;
        editingId = care ? String(care.id) : null;
        editingUserId = care ? String(care.user_id) : currentUserId;
        $form[0].reset();
        $error.text('').prop('hidden', true);
        $('#inventory-care-modal-title').text(care ? 'Edit perawatan' : 'Tambah perawatan');
        $save.prop('disabled', false).html('<i class="fas fa-check" aria-hidden="true"></i> ' +
            (care ? 'Simpan perubahan' : 'Simpan perawatan'));

        if (care) {
            $form.find('[name="tanggal_perawatan"]').val(String(care.tanggal_perawatan || '').slice(0, 10));
            $form.find('[name="status_perawatan"]').val(care.status_perawatan || '');
            window.Rupiah.set('#inventory-care-cost-display', care.biaya);
            $form.find('[name="keterangan"]').val(care.keterangan || '');
            $('#inventory-care-user').val(care.user && care.user.name ? care.user.name : currentUserName);
        } else {
            window.Rupiah.set('#inventory-care-cost-display', '');
            const now = new Date();
            $form.find('[name="tanggal_perawatan"]').val(
                new Date(now.getTime() - now.getTimezoneOffset() * 60000).toISOString().slice(0, 10)
            );
            $('#inventory-care-user').val(currentUserName);
        }
        $form.find('[name="status_perawatan"]').trigger('inventory:refresh-select');
        $modal.addClass('is-open').attr('aria-hidden', 'false');
        $('body').addClass('inventory-modal-open');
        $form.find('[name="tanggal_perawatan"]').trigger('focus');
    }

    $('#inventory-care-add').on('click', function () { openModal(null); });
    $(document).on('click', '[data-close-care-modal]', closeModal);
    $('#inventory-care-prev').on('click', function () { if (page > 1) loadCare(page - 1); });
    $('#inventory-care-next').on('click', function () { if (nextPage) loadCare(page + 1); });

    $body.on('click', '[data-care-action="edit"]', function () {
        if (!window.AppCan('perawatan.show') || !window.AppCan('perawatan.update')) return;
        const id = String($(this).attr('data-care-id'));
        $.ajax({ ...requestOptions(), url: apiUrl + '/' + encodeURIComponent(id), method: 'GET' })
            .done(function (care) {
                if (String(care.inventaris_id) !== itemId) return;
                openModal(care);
            }).fail(function (xhr) {
                toast.fire({ icon: 'error', title: 'Gagal memuat perawatan: ' + apiError(xhr) });
            });
    });

    $body.on('click', '[data-care-action="delete"]', function () {
        if (!window.AppCan('perawatan.destroy')) return;
        const care = rows.get(String($(this).attr('data-care-id')));
        if (!care) return;
        Swal.fire({
            icon: 'warning',
            title: 'Hapus perawatan?',
            text: 'Catatan perawatan tanggal ' + formatDate(care.tanggal_perawatan) + ' akan dihapus.',
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
            $.ajax({ ...writeOptions(), url: apiUrl + '/' + encodeURIComponent(care.id), method: 'DELETE' })
                .done(function () {
                    toast.fire({ icon: 'success', title: 'Perawatan berhasil dihapus.' });
                    loadCare(page);
                }).fail(function (xhr) {
                    Swal.fire({ icon: 'error', title: 'Gagal menghapus', text: apiError(xhr),
                        confirmButtonText: 'Tutup', customClass: swalStyle, buttonsStyling: false,
                        allowOutsideClick: false, allowEscapeKey: false });
                });
        });
    });

    $form.on('submit', function (event) {
        event.preventDefault();
        if (!window.AppCan('perawatan.' + (editingId ? 'update' : 'store'))) return;
        if (!$form.find('[name="status_perawatan"]').val()) {
            $error.text('Pilih status perawatan.').prop('hidden', false);
            $form.find('.master-select-trigger').trigger('focus');
            return;
        }
        if (!this.reportValidity()) return;

        const cost = $form.find('[name="biaya"]').val();
        const payload = {
            inventaris_id: itemId,
            user_id: editingUserId,
            tanggal_perawatan: $form.find('[name="tanggal_perawatan"]').val(),
            status_perawatan: $form.find('[name="status_perawatan"]').val(),
            biaya: cost === '' ? null : cost,
            keterangan: $form.find('[name="keterangan"]').val().trim() || null,
        };
        $error.text('').prop('hidden', true);
        $save.prop('disabled', true).text('Menyimpan...');
        const wasEditing = Boolean(editingId);
        $.ajax({
            ...writeOptions(),
            url: editingId ? apiUrl + '/' + encodeURIComponent(editingId) : apiUrl,
            method: editingId ? 'PUT' : 'POST',
            data: JSON.stringify(payload),
        }).done(function () {
            closeModal();
            toast.fire({ icon: 'success', title: 'Perawatan berhasil disimpan.' });
            loadCare(wasEditing ? page : 1);
        }).fail(function (xhr) {
            $error.text(apiError(xhr)).prop('hidden', false);
        }).always(function () {
            $save.prop('disabled', false).html('<i class="fas fa-check" aria-hidden="true"></i> ' +
                (wasEditing ? 'Simpan perubahan' : 'Simpan perawatan'));
        });
    });

    $page.on('inventory:detail-loaded', function () {
        $('#inventory-care-add').prop('disabled', false);
    });
    $page.on('inventory:detail-error', function () {
        $('#inventory-care-add').prop('disabled', true);
    });
    loadCare(1);
})(jQuery);
