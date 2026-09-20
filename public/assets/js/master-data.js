(function ($) {
    'use strict';

    const $page = $('.master-page');

    if (!$page.length || typeof Swal === 'undefined') {
        return;
    }

    const resource = $page.data('master-resource');
    const apiUrl = $page.data('master-url');
    const instansiUrl = $page.data('instansi-url');
    const $tableBody = $page.find('.master-table tbody');
    const $dialog = $page.find('.master-dialog');
    const $form = $page.find('.master-form');
    const $error = $page.find('.master-form-error');
    const $save = $page.find('.master-save');
    const label = {
        instansi: 'instansi',
        lokasi: 'lokasi',
        jenis: 'jenis',
        pendanaan: 'pendanaan',
    }[resource];
    const rows = new Map();

    let currentPage = 1;
    let hasNextPage = false;
    let editingId = null;
    let pendingRequest = null;
    let searchTimer = null;

    const toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3500,
        timerProgressBar: true,
        customClass: {
            popup: 'modern-toast',
            title: 'modern-toast-title',
        },
    });

    const swalStyle = {
        popup: 'modern-swal-popup',
        title: 'modern-swal-title',
        htmlContainer: 'modern-swal-html',
        confirmButton: 'modern-swal-confirm',
        cancelButton: 'modern-swal-cancel',
    };

    let selectCounter = 0;

    function closeSelectMenus() {
        $page.find('.master-select-wrap.is-open').each(function () {
            const $wrap = $(this);
            $wrap.removeClass('is-open');
            $wrap.find('.master-select-trigger').attr('aria-expanded', 'false');
        });
    }

    function refreshCustomSelect($select) {
        const widget = $select.data('masterSelectWidget');

        if (!widget) {
            return;
        }

        const selectedValue = String($select.val() ?? '');
        const selectedText = $select.find('option:selected').text() || $select.find('option').first().text();
        widget.button.find('.master-select-text').text(selectedText);
        widget.button.prop('disabled', $select.prop('disabled'));
        widget.menu.empty();

        $select.find('option').each(function () {
            if (this.hidden) {
                return;
            }

            const value = String(this.value);
            const selected = value === selectedValue;
            const $option = $('<button>', {
                type: 'button',
                role: 'option',
                'aria-selected': selected ? 'true' : 'false',
            }).addClass('master-select-option').toggleClass('is-selected', selected)
                .prop('disabled', this.disabled).attr('data-value', value)
                .append($('<span>').text(this.textContent));

            if (selected) {
                $option.append($('<i>').addClass('fas fa-check').attr('aria-hidden', 'true'));
            }

            widget.menu.append($option);
        });
    }

    function initCustomSelect($select) {
        const $wrap = $select.closest('.master-select-wrap');
        const menuId = 'master-select-menu-' + ++selectCounter;
        const labelText = $select.attr('aria-label') ||
            $('label[for="' + $select.attr('id') + '"]').text().trim() || 'Pilih opsi';
        const $button = $('<button>', {
            type: 'button',
            'aria-label': labelText,
            'aria-haspopup': 'listbox',
            'aria-expanded': 'false',
            'aria-controls': menuId,
        }).addClass('master-select-trigger')
            .append($('<span>').addClass('master-select-text'))
            .append($('<i>').addClass('fas fa-chevron-down').attr('aria-hidden', 'true'));
        const $menu = $('<div>', { id: menuId, role: 'listbox', 'aria-label': labelText })
            .addClass('master-select-menu');

        $select.attr({ tabindex: '-1', 'aria-hidden': 'true' });
        $wrap.addClass('is-enhanced').append($button, $menu);
        $select.data('masterSelectWidget', { button: $button, menu: $menu });
        refreshCustomSelect($select);

        $button.on('click', function () {
            if ($wrap.hasClass('is-open')) {
                closeSelectMenus();
                return;
            }

            closeSelectMenus();
            refreshCustomSelect($select);
            const bounds = $button[0].getBoundingClientRect();
            $wrap.toggleClass('is-dropup', window.innerHeight - bounds.bottom < 230 && bounds.top > 230);
            $wrap.addClass('is-open');
            $button.attr('aria-expanded', 'true');
            $menu.find('.is-selected:not(:disabled), .master-select-option:not(:disabled)').first().trigger('focus');
        });

        $button.on('keydown', function (event) {
            if (['ArrowDown', 'ArrowUp', 'Enter', ' '].includes(event.key)) {
                event.preventDefault();
                if (!$wrap.hasClass('is-open')) {
                    $button.trigger('click');
                }
            }
        });

        $menu.on('click', '.master-select-option', function () {
            $select.val($(this).attr('data-value')).trigger('change');
            refreshCustomSelect($select);
            closeSelectMenus();
            $button.trigger('focus');
        });

        $menu.on('keydown', '.master-select-option', function (event) {
            const $options = $menu.find('.master-select-option:not(:disabled)');
            const index = $options.index(this);
            let nextIndex = index;

            if (event.key === 'ArrowDown') nextIndex = Math.min(index + 1, $options.length - 1);
            if (event.key === 'ArrowUp') nextIndex = Math.max(index - 1, 0);
            if (event.key === 'Home') nextIndex = 0;
            if (event.key === 'End') nextIndex = $options.length - 1;

            if (nextIndex !== index) {
                event.preventDefault();
                $options.eq(nextIndex).trigger('focus');
            }

            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                $(this).trigger('click');
            }

            if (event.key === 'Escape') {
                event.preventDefault();
                event.stopPropagation();
                closeSelectMenus();
                $button.trigger('focus');
            }

            if (event.key === 'Tab') {
                closeSelectMenus();
            }
        });
    }

    $page.find('.master-select-wrap select').each(function () {
        initCustomSelect($(this));
    });

    $(document).on('click', function (event) {
        if (!$(event.target).closest('.master-select-wrap').length) {
            closeSelectMenus();
        }
    });

    function apiError(xhr) {
        const data = xhr.responseJSON;

        if (xhr.status === 422 && data && data.errors) {
            return Object.values(data.errors).flat().join(' ');
        }

        if (xhr.status === 401 || xhr.status === 403 || xhr.status === 419 ||
            xhr.status === 200 && (xhr.getResponseHeader('content-type') || '').includes('text/html')) {
            return 'Sesi tidak aktif. Silakan masuk kembali untuk mengelola data.';
        }

        return data && data.message ? data.message :
            xhr && xhr.message ? xhr.message : 'Permintaan gagal. Silakan coba lagi.';
    }

    function showMessage(message) {
        const columns = resource === 'lokasi' ? 4 : 2;
        $tableBody.empty().append(
            $('<tr>').append($('<td>').attr('colspan', columns).addClass('master-table-message').text(message))
        );
    }

    function iconButton(icon, title, action, id, danger) {
        const $button = $('<button>', {
            type: 'button',
            title: title,
            'aria-label': title,
        }).addClass('master-icon-button').attr('data-action', action).attr('data-id', id);

        if (danger) {
            $button.addClass('is-danger');
        }

        return $button.append($('<i>').addClass(icon).attr('aria-hidden', 'true'));
    }

    function renderRow(item) {
        const $row = $('<tr>');
        $row.append($('<td>').text(item.nama || '—'));

        if (resource === 'lokasi') {
            $row.append($('<td>').text(item.instansi && item.instansi.nama ? item.instansi.nama : '—'));
            $row.append($('<td>').append(
                $('<span>').addClass(item.is_gudang ? 'master-badge' : 'master-badge is-muted')
                    .text(item.is_gudang ? 'Gudang' : 'Lokasi biasa')
            ));
        }

        const $actions = $('<div>').addClass('master-row-actions');
        if (window.AppCan(resource + '.update') &&
            (resource !== 'lokasi' || window.AppCan('instansi.select'))) {
            $actions.append(iconButton('fas fa-pen', 'Ubah ' + label, 'edit', item.id, false));
        }
        if (window.AppCan(resource + '.destroy')) {
            $actions.append(iconButton('fas fa-trash-alt', 'Hapus ' + label, 'delete', item.id, true));
        }
        $row.append($('<td>').append($actions));

        return $row;
    }

    function renderPagination(response) {
        currentPage = Number(response.current_page) || currentPage;
        hasNextPage = Boolean(response.next_page_url);

        $page.find('.master-page-number').text(currentPage);
        $page.find('.master-prev').prop('disabled', !response.prev_page_url);
        $page.find('.master-next').prop('disabled', !hasNextPage);

        const count = Array.isArray(response.data) ? response.data.length : 0;
        const start = count ? response.from || (currentPage - 1) * Number($page.find('.master-page-size').val()) + 1 : 0;
        const end = count ? response.to || start + count - 1 : 0;
        $page.find('.master-page-summary').text(count ? 'Menampilkan ' + start + '–' + end + ' data' : 'Tidak ada data');
    }

    function loadData(pageNumber) {
        if (pendingRequest) {
            pendingRequest.abort();
        }

        showMessage('Memuat data dari server...');
        $page.find('.master-page-summary').text('Memuat data...');

        pendingRequest = $.ajax({
            url: apiUrl,
            method: 'GET',
            dataType: 'json',
            headers: { Accept: 'application/json' },
            data: {
                search: $page.find('.master-search input').val().trim(),
                size: $page.find('.master-page-size').val(),
                page: pageNumber,
            },
        }).done(function (response) {
            rows.clear();
            $tableBody.empty();

            if (!response || !Array.isArray(response.data)) {
                showMessage('Format data dari server tidak sesuai.');
                return;
            }

            response.data.forEach(function (item) {
                rows.set(String(item.id), item);
                $tableBody.append(renderRow(item));
            });

            if (!response.data.length) {
                if (pageNumber > 1) {
                    setTimeout(function () { loadData(pageNumber - 1); }, 0);
                    return;
                }

                showMessage('Belum ada data yang cocok.');
            }

            renderPagination(response);
        }).fail(function (xhr, status) {
            if (status === 'abort') {
                return;
            }

            showMessage(apiError(xhr));
            $page.find('.master-page-summary').text('Gagal memuat data');
            $page.find('.master-prev, .master-next').prop('disabled', true);
        }).always(function () {
            pendingRequest = null;
        });
    }

    async function loadInstansiOptions(selectedId) {
        if (resource !== 'lokasi') {
            return;
        }

        const $select = $form.find('[name="instansi_id"]');
        $select.prop('disabled', true).empty().append($('<option>').val('').text('Memuat instansi...'));
        refreshCustomSelect($select);

        try {
            const options = await $.ajax({
                url: instansiUrl,
                method: 'GET',
                dataType: 'json',
                headers: { Accept: 'application/json' },
            });

            if (!Array.isArray(options)) {
                throw new Error('Format pilihan instansi tidak sesuai.');
            }

            $select.empty().append($('<option>').val('').text('Pilih instansi'));
            options.forEach(function (item) {
                $select.append($('<option>').val(item.id).text(item.nama));
            });
            $select.val(selectedId || '');
            $select.prop('disabled', false);
            refreshCustomSelect($select);

            if (!options.length) {
                $error.text('Buat data instansi terlebih dahulu sebelum menambah lokasi.').prop('hidden', false);
            }
        } catch (xhr) {
            $select.empty().append($('<option>').val('').text('Gagal memuat instansi'));
            $error.text(apiError(xhr)).prop('hidden', false);
            refreshCustomSelect($select);
        }
    }

    async function openDialog(item) {
        if (!window.AppCan(resource + (item ? '.update' : '.store'))) return;
        if (resource === 'lokasi' && !window.AppCan('instansi.select')) return;
        editingId = item ? String(item.id) : null;
        $form[0].reset();
        $form.find('.master-select-wrap select').each(function () {
            refreshCustomSelect($(this));
        });
        $error.text('').prop('hidden', true);
        $page.find('.master-dialog-title').text((item ? 'Ubah ' : 'Tambah ') + label);
        $save.text(item ? 'Simpan perubahan' : 'Simpan ' + label);
        $dialog.addClass('is-open').attr('aria-hidden', 'false');
        $('body').addClass('inventory-modal-open');

        if (item) {
            $form.find('[name="nama"]').val(item.nama);
        }

        if (resource === 'lokasi') {
            $form.find('[name="is_gudang"]').prop('checked', Boolean(item && item.is_gudang));
            await loadInstansiOptions(item ? item.instansi_id : null);
        }

        $form.find('[name="nama"]').trigger('focus');
    }

    function closeDialog() {
        $dialog.removeClass('is-open').attr('aria-hidden', 'true');
        $('body').removeClass('inventory-modal-open');
        editingId = null;
    }

    $page.on('click', '.master-add-button', function () {
        openDialog(null);
    });

    $page.on('click', '.master-dialog-close, .master-cancel', closeDialog);

    $page.on('click', '[data-action="edit"]', function () {
        const item = rows.get(String($(this).attr('data-id')));
        if (item) {
            openDialog(item);
        }
    });

    $page.on('click', '[data-action="delete"]', function () {
        if (!window.AppCan(resource + '.destroy')) return;
        const item = rows.get(String($(this).attr('data-id')));
        if (!item) {
            return;
        }

        Swal.fire({
            icon: 'warning',
            title: 'Hapus ' + label + '?',
            text: 'Data "' + item.nama + '" akan dihapus dari server.',
            showCancelButton: true,
            confirmButtonText: 'Ya, hapus',
            cancelButtonText: 'Batal',
            reverseButtons: true,
            customClass: swalStyle,
            buttonsStyling: false,
            allowOutsideClick: false,
            allowEscapeKey: false,
        }).then(function (result) {
            if (!result.isConfirmed) {
                return;
            }

            $.ajax({
                url: apiUrl + '/' + encodeURIComponent(item.id),
                method: 'DELETE',
                dataType: 'json',
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                },
            }).done(function () {
                toast.fire({ icon: 'success', title: 'Data berhasil dihapus.' });
                loadData(currentPage);
            }).fail(function (xhr) {
                Swal.fire({ icon: 'error', title: 'Gagal menghapus', text: apiError(xhr), customClass: swalStyle, confirmButtonText: 'Tutup', buttonsStyling: false, allowOutsideClick: false, allowEscapeKey: false });
            });
        });
    });

    $form.on('submit', function (event) {
        event.preventDefault();
        if (!window.AppCan(resource + (editingId ? '.update' : '.store'))) return;

        const $instansiSelect = $form.find('[name="instansi_id"]');

        if ($instansiSelect.length && !$instansiSelect.val()) {
            $error.text('Pilih instansi terlebih dahulu.').prop('hidden', false);
            $instansiSelect.closest('.master-select-wrap').find('.master-select-trigger').trigger('focus');
            return;
        }

        if (!this.reportValidity()) {
            return;
        }

        const payload = { nama: $form.find('[name="nama"]').val().trim() };

        if (resource === 'lokasi') {
            payload.instansi_id = $form.find('[name="instansi_id"]').val();
            payload.is_gudang = $form.find('[name="is_gudang"]').is(':checked') ? 1 : 0;
        }

        $error.text('').prop('hidden', true);
        $save.prop('disabled', true).text('Menyimpan...');

        $.ajax({
            url: editingId ? apiUrl + '/' + encodeURIComponent(editingId) : apiUrl,
            method: editingId ? 'PUT' : 'POST',
            dataType: 'json',
            contentType: 'application/json',
            data: JSON.stringify(payload),
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            },
        }).done(function () {
            closeDialog();
            toast.fire({ icon: 'success', title: 'Data ' + label + ' berhasil disimpan.' });
            loadData(currentPage);
        }).fail(function (xhr) {
            $error.text(apiError(xhr)).prop('hidden', false);
        }).always(function () {
            $save.prop('disabled', false).text(editingId ? 'Simpan perubahan' : 'Simpan ' + label);
        });
    });

    $page.find('.master-search input').on('input', function () {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(function () {
            loadData(1);
        }, 350);
    });

    $page.find('.master-page-size').on('change', function () {
        loadData(1);
    });

    $page.find('.master-prev').on('click', function () {
        if (currentPage > 1) {
            loadData(currentPage - 1);
        }
    });

    $page.find('.master-next').on('click', function () {
        if (hasNextPage) {
            loadData(currentPage + 1);
        }
    });

    loadData(1);
})(jQuery);
