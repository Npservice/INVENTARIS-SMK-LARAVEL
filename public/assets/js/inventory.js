(function ($) {
    'use strict';

    const $page = $('#inventory-page, #inventory-show-page').first();

    if (!$page.length || typeof Swal === 'undefined') {
        return;
    }

    const apiUrl = $page.attr('data-api-url');
    const isShowPage = $page.is('#inventory-show-page');
    const $formModal = $('#inventory-modal');
    const $form = $('#inventory-form');
    const $tableBody = $('#inventory-table tbody');
    const $formError = $('#inventory-form-error');
    const $saveButton = $('#inventory-save');
    const rows = new Map();
    const lookups = { jenis: [], lokasi: [], instansi: [], pendanaan: [] };

    let currentPage = 1;
    let hasNextPage = false;
    let pendingList = null;
    let searchTimer = null;
    let editingId = null;
    let detailItem = null;
    let selectCounter = 0;
    let locationFilterToken = 0;
    let formLocationToken = 0;
    let nextKodeToken = 0;

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
            dataType: 'json',
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

    function formatMoney(value) {
        return window.Rupiah.format(value);
    }

    function closeSelectMenus() {
        $('.master-select-wrap.is-open').removeClass('is-open')
            .find('.master-select-trigger').attr('aria-expanded', 'false');
    }

    function refreshSelect($select) {
        const widget = $select.data('inventorySelectWidget');
        if (!widget) return;

        const value = String($select.val() ?? '');
        widget.button.find('.master-select-text').text(
            $select.find('option:selected').text() || $select.find('option').first().text()
        );
        widget.button.prop('disabled', $select.prop('disabled'));
        widget.menu.empty();

        $select.find('option').each(function () {
            if (this.hidden) return;
            const selected = String(this.value) === value;
            const $option = $('<button>', {
                type: 'button',
                role: 'option',
                'aria-selected': selected ? 'true' : 'false',
            }).addClass('master-select-option').toggleClass('is-selected', selected)
                .prop('disabled', this.disabled)
                .attr('data-value', String(this.value))
                .append($('<span>').text(this.textContent));

            if (selected) {
                $option.append($('<i>').addClass('fas fa-check').attr('aria-hidden', 'true'));
            }

            widget.menu.append($option);
        });
    }

    function initSelect($select) {
        const $wrap = $select.closest('.master-select-wrap');
        const id = 'inventory-select-menu-' + ++selectCounter;
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
        $select.data('inventorySelectWidget', { button: $button, menu: $menu });
        $select.on('inventory:refresh-select', function () { refreshSelect($select); });
        refreshSelect($select);

        $button.on('click', function () {
            if ($wrap.hasClass('is-open')) {
                closeSelectMenus();
                return;
            }

            closeSelectMenus();
            refreshSelect($select);
            const bounds = $button[0].getBoundingClientRect();
            $wrap.toggleClass('is-dropup', window.innerHeight - bounds.bottom < 230 && bounds.top > 230);
            $wrap.addClass('is-open');
            $button.attr('aria-expanded', 'true');
            $menu.find('.is-selected:not(:disabled), .master-select-option:not(:disabled)').first().trigger('focus');
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
            const $options = $menu.find('.master-select-option:not(:disabled)');
            const index = $options.index(this);
            let next = index;

            if (event.key === 'ArrowDown') next = Math.min(index + 1, $options.length - 1);
            if (event.key === 'ArrowUp') next = Math.max(index - 1, 0);
            if (event.key === 'Home') next = 0;
            if (event.key === 'End') next = $options.length - 1;

            if (next !== index) {
                event.preventDefault();
                $options.eq(next).trigger('focus');
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

            if (event.key === 'Tab') closeSelectMenus();
        });
    }

    $('#inventory-page .master-select-wrap select, #inventory-modal .master-select-wrap select, #inventory-care-modal .master-select-wrap select').each(function () {
        initSelect($(this));
    });

    $(document).on('click', function (event) {
        if (!$(event.target).closest('.master-select-wrap').length) closeSelectMenus();
    });

    async function fetchSelect(url, params) {
        const response = await $.ajax({ ...requestOptions(), url: url, method: 'GET', data: params || {} });
        if (!Array.isArray(response)) throw new Error('Format pilihan tidak sesuai.');
        return response;
    }

    function lookupLabel(key, item) {
        if (key !== 'lokasi') return item.nama;
        const instansi = lookups.instansi.find(entry => entry.id === item.instansi_id);
        return item.nama + (instansi ? ' · ' + instansi.nama : '');
    }

    function fillLookupSelect($select, items, placeholder, labeler, selected) {
        $select.empty().append($('<option>').val('').text(placeholder));
        items.forEach(function (item) {
            $select.append($('<option>').val(item.id).text(labeler(item)));
        });
        $select.val(selected || '');
        $select.prop('disabled', false);
        refreshSelect($select);
    }

    function loadLookup(key, url) {
        const $filter = $('#inventory-filter-' + key);
        const $field = $form.find('[name="' + key + '_id"]');
        $filter.prop('disabled', true);
        if (key !== 'lokasi') $field.prop('disabled', true);
        refreshSelect($filter);
        refreshSelect($field);

        fetchSelect(url).then(function (items) {
            lookups[key] = items;
            const labeler = item => lookupLabel(key, item);
            const filterValue = $filter.val();
            const formValue = editingId && detailItem ? detailItem[key + '_id'] : $field.val();

            if (key !== 'lokasi' || !$('#inventory-filter-instansi').val()) {
                fillLookupSelect($filter, items, 'Semua ' + key, labeler, filterValue);
            }
            if (key !== 'lokasi') fillLookupSelect($field, items, 'Pilih ' + key, labeler, formValue);
        }).catch(function (xhr) {
            toast.fire({ icon: 'error', title: 'Gagal memuat ' + key + ': ' + apiError(xhr) });
        });
    }

    ['jenis', 'lokasi', 'pendanaan'].forEach(function (key) {
        if (window.AppCan(key + '.select')) {
            loadLookup(key, $page.attr('data-' + key + '-url'));
        } else {
            $('#inventory-filter-' + key).closest('.inventory-field').remove();
        }
    });

    const $instansiFilter = $('#inventory-filter-instansi');
    const $instansiField = $form.find('[name="instansi_id"]');
    $instansiFilter.prop('disabled', true);
    refreshSelect($instansiFilter);
    if (window.AppCan('instansi.select')) fetchSelect($page.attr('data-instansi-url')).then(function (items) {
        lookups.instansi = items;
        fillLookupSelect($instansiFilter, items, 'Semua instansi', item => item.nama, '');
        fillLookupSelect($instansiField, items, 'Pilih instansi', item => item.nama, '');
        if ($formModal.hasClass('is-open') && editingId && detailItem) {
            selectFormInstansi(detailItem);
        }
        if (lookups.lokasi.length) {
            const $filter = $('#inventory-filter-lokasi');
            fillLookupSelect($filter, lookups.lokasi, 'Semua lokasi', item => lookupLabel('lokasi', item), $filter.val());
        }
    }).catch(function (xhr) {
        toast.fire({ icon: 'error', title: 'Gagal memuat instansi: ' + apiError(xhr) });
    });
    else $('#inventory-filter-instansi').closest('.inventory-field').remove();

    function refreshLocationFilter(instansiId) {
        if (!window.AppCan('lokasi.select')) return;
        const token = ++locationFilterToken;
        const $filter = $('#inventory-filter-lokasi');
        $filter.prop('disabled', true).empty()
            .append($('<option>').val('').text('Memuat lokasi...'));
        refreshSelect($filter);

        fetchSelect($page.attr('data-lokasi-url'), instansiId ? { instansi_id: instansiId } : {})
            .then(function (items) {
                if (token !== locationFilterToken) return;
                fillLookupSelect($filter, items, 'Semua lokasi', item => lookupLabel('lokasi', item), '');
            }).catch(function (xhr) {
                if (token !== locationFilterToken) return;
                $filter.empty().append($('<option>').val('').text('Gagal memuat lokasi'));
                refreshSelect($filter);
                toast.fire({ icon: 'error', title: 'Gagal memuat lokasi: ' + apiError(xhr) });
            });
    }

    function refreshFormLocation(instansiId, selectedId) {
        if (!window.AppCan('lokasi.select')) return;
        const token = ++formLocationToken;
        const $field = $form.find('[name="lokasi_id"]');
        $field.prop('disabled', true).empty().append(
            $('<option>').val('').text(instansiId ? 'Memuat lokasi...' : 'Pilih instansi dahulu')
        );
        refreshSelect($field);
        if (!instansiId) return;

        fetchSelect($page.attr('data-lokasi-url'), { instansi_id: instansiId })
            .then(function (items) {
                if (token !== formLocationToken || $instansiField.val() !== instansiId) return;
                fillLookupSelect($field, items, items.length ? 'Pilih lokasi' : 'Belum ada lokasi',
                    item => item.nama, selectedId);
            }).catch(function (xhr) {
                if (token !== formLocationToken) return;
                $field.empty().append($('<option>').val('').text('Gagal memuat lokasi'));
                refreshSelect($field);
                toast.fire({ icon: 'error', title: 'Gagal memuat lokasi: ' + apiError(xhr) });
            });
    }

    function selectFormInstansi(item) {
        const instansiId = item.lokasi && item.lokasi.instansi_id ||
            (lookups.lokasi.find(lokasi => lokasi.id === item.lokasi_id) || {}).instansi_id || '';
        $instansiField.val(instansiId);
        refreshSelect($instansiField);
        refreshFormLocation(instansiId, item.lokasi_id);
    }

    function listParams(page) {
        const params = {
            page: page,
            size: $('#inventory-page-size').val(),
            search: $('#inventory-search').val().trim(),
            instansi_id: $('#inventory-filter-instansi').val(),
            lokasi_id: $('#inventory-filter-lokasi').val(),
            jenis_id: $('#inventory-filter-jenis').val(),
            pendanaan_id: $('#inventory-filter-pendanaan').val(),
            kondisi: $('#inventory-filter-kondisi').val(),
        };

        if ($('#inventory-filter-gudang').is(':checked')) params.gudang = 1;
        return params;
    }

    function showListMessage(message) {
        $tableBody.empty().append(
            $('<tr>').append($('<td>').attr('colspan', 7).addClass('master-table-message').text(message))
        );
    }

    function renderRow(item) {
        const location = item.lokasi && item.lokasi.nama ? item.lokasi.nama : '—';
        const instansi = item.lokasi && item.lokasi.instansi ? item.lokasi.instansi.nama : null;
        const $row = $('<tr>');

        $row.append($('<td>').addClass('inventory-code-cell').text(item.kode_invt || '—'));
        $row.append($('<td>').addClass('inventory-name-cell').text(item.nama || '—'));
        $row.append($('<td>').text(item.jenis && item.jenis.nama ? item.jenis.nama : '—'));
        $row.append($('<td>').append($('<span>').text(location)));

        if (instansi) {
            $row.find('td:last').append($('<small>').addClass('inventory-table-subtitle').text(instansi));
        }

        $row.append($('<td>').text(item.jumlah ?? '—'));
        $row.append($('<td>').append(
            $('<span>').addClass(item.kondisi === 'Baik' ? 'master-badge inventory-good' : 'master-badge inventory-damaged')
                .text(item.kondisi || '—')
        ));
        const $actions = $('<td>').addClass('inventory-action-cell');
        if (window.AppCan('inventaris.show')) $actions.append(
            $('<button>', { type: 'button', title: 'Detail' }).addClass('inventory-detail-button')
                .attr('data-inventory-id', item.id)
                .attr('aria-label', 'Detail ' + (item.nama || 'barang'))
                .append($('<i>').addClass('fas fa-eye').attr('aria-hidden', 'true'))
        );
        if (window.AppCan('inventaris.show')) $actions.append(
            $('<a>', {
                href: $page.attr('data-label-base-url') + '/' + encodeURIComponent(item.id) + '/label',
                target: '_blank',
                rel: 'noopener noreferrer',
                title: 'Cetak label',
            }).addClass('inventory-label-button')
                .attr('aria-label', 'Cetak label ' + (item.nama || 'barang'))
                .append($('<i>').addClass('fas fa-barcode').attr('aria-hidden', 'true'))
        );
        if (window.AppCan('inventaris.destroy')) $actions.append(
            $('<button>', { type: 'button', title: 'Hapus' }).addClass('inventory-row-delete')
                .attr('data-delete-inventory-id', item.id)
                .attr('aria-label', 'Hapus ' + (item.nama || 'barang'))
                .append($('<i>').addClass('fas fa-trash-alt').attr('aria-hidden', 'true'))
        );
        $row.append($actions);

        return $row;
    }

    function renderPagination(response) {
        currentPage = Number(response.current_page) || currentPage;
        hasNextPage = Boolean(response.next_page_url);
        $('#inventory-page-number').text(currentPage);
        $('#inventory-prev').prop('disabled', !response.prev_page_url);
        $('#inventory-next').prop('disabled', !hasNextPage);

        const count = response.data.length;
        const start = count ? response.from || (currentPage - 1) * Number($('#inventory-page-size').val()) + 1 : 0;
        const end = count ? response.to || start + count - 1 : 0;
        $('#inventory-page-summary').text(count ? 'Menampilkan ' + start + '–' + end + ' barang' : 'Tidak ada barang');
        $('#inventory-list-caption').text(count ? 'Data barang dari server' : 'Belum ada data yang cocok');
    }

    function loadList(page) {
        if (pendingList) pendingList.abort();
        showListMessage('Memuat inventaris dari server...');
        $('#inventory-page-summary').text('Memuat data...');

        pendingList = $.ajax({ ...requestOptions(), url: apiUrl, method: 'GET', data: listParams(page) })
            .done(function (response) {
                if (!response || !Array.isArray(response.data)) {
                    showListMessage('Format data dari server tidak sesuai.');
                    return;
                }

                if (!response.data.length && page > 1) {
                    setTimeout(function () { loadList(page - 1); }, 0);
                    return;
                }

                rows.clear();
                $tableBody.empty();
                response.data.forEach(function (item) {
                    rows.set(String(item.id), item);
                    $tableBody.append(renderRow(item));
                });
                if (!response.data.length) showListMessage('Belum ada barang yang cocok.');
                renderPagination(response);
            }).fail(function (xhr, status) {
                if (status === 'abort') return;
                showListMessage(apiError(xhr));
                $('#inventory-page-summary').text('Gagal memuat data');
                $('#inventory-list-caption').text('Periksa koneksi atau sesi login');
                $('#inventory-prev, #inventory-next').prop('disabled', true);
            }).always(function () {
                pendingList = null;
            });
    }

    function setDetail(field, value) {
        $page.find('[data-detail="' + field + '"]').text(value ?? '—');
    }

    function openDetail(id) {
        if (!isShowPage) return;
        detailItem = null;
        $('#inventory-detail-error').prop('hidden', true);
        $('#inventory-edit, #inventory-delete').prop('disabled', true);
        setDetail('nama', 'Memuat detail...');
        ['kode_invt', 'jenis', 'lokasi', 'instansi', 'pendanaan', 'masuk', 'kondisi', 'jumlah', 'harga_beli', 'keterangan']
            .forEach(key => setDetail(key, '—'));

        $.ajax({ ...requestOptions(), url: apiUrl + '/' + encodeURIComponent(id), method: 'GET' })
            .done(function (item) {
                detailItem = item;
                setDetail('nama', item.nama);
                setDetail('kode_invt', item.kode_invt);
                setDetail('jenis', item.jenis && item.jenis.nama);
                setDetail('lokasi', item.lokasi && item.lokasi.nama);
                setDetail('instansi', item.lokasi && item.lokasi.instansi && item.lokasi.instansi.nama);
                setDetail('pendanaan', item.pendanaan && item.pendanaan.nama);
                setDetail('masuk', formatDate(item.masuk));
                setDetail('kondisi', item.kondisi);
                setDetail('jumlah', item.jumlah);
                setDetail('harga_beli', formatMoney(item.harga_beli));
                setDetail('keterangan', item.keterangan || '—');
                $('#inventory-edit, #inventory-delete').prop('disabled', false);
                $page.trigger('inventory:detail-loaded', [item]);
            }).fail(function (xhr) {
                setDetail('nama', 'Detail tidak tersedia');
                $('#inventory-detail-error').text(apiError(xhr)).prop('hidden', false);
                $page.trigger('inventory:detail-error', [xhr]);
            });
    }

    function openForm(item) {
        if (!window.AppCan('inventaris.' + (item ? 'update' : 'store'))) return;
        if (['jenis.select', 'lokasi.select', 'instansi.select', 'pendanaan.select']
            .some(permission => !window.AppCan(permission))) return;
        if (!item && !window.AppCan('inventaris.next-kode')) return;
        const kodeToken = ++nextKodeToken;
        editingId = item ? String(item.id) : null;
        $form[0].reset();
        $formError.text('').prop('hidden', true);
        $('#inventory-modal-title').text(item ? 'Edit barang' : 'Tambah barang baru');
        $saveButton.prop('disabled', false).html('<i class="fas fa-check" aria-hidden="true"></i> ' + (item ? 'Simpan perubahan' : 'Simpan barang'));

        if (item) {
            $('#inventory-kode').val(item.kode_invt || 'Kode tidak tersedia');
            ['nama', 'jumlah', 'keterangan'].forEach(function (field) {
                $form.find('[name="' + field + '"]').val(item[field] ?? '');
            });
            window.Rupiah.set('#inventory-price-display', item.harga_beli);
            $form.find('[name="masuk"]').val(item.masuk ? String(item.masuk).slice(0, 10) : '');
            ['jenis_id', 'pendanaan_id', 'kondisi'].forEach(function (field) {
                $form.find('[name="' + field + '"]').val(item[field] ?? '');
            });
            if (lookups.instansi.length) selectFormInstansi(item);
            else refreshFormLocation('', '');
        } else {
            $('#inventory-kode').val('Memuat kode...');
            window.Rupiah.set('#inventory-price-display', '');
            $.ajax({ ...requestOptions(), url: $page.attr('data-next-kode-url'), method: 'GET' })
                .done(function (response) {
                    if (kodeToken !== nextKodeToken) return;
                    $('#inventory-kode').val(response && response.kode_invt || 'Kode belum tersedia');
                }).fail(function (xhr) {
                    if (kodeToken !== nextKodeToken) return;
                    $('#inventory-kode').val('Kode belum tersedia');
                    toast.fire({ icon: 'error', title: 'Gagal memuat kode: ' + apiError(xhr) });
                });
            const now = new Date();
            const localDate = new Date(now.getTime() - now.getTimezoneOffset() * 60000).toISOString().slice(0, 10);
            $form.find('[name="masuk"]').val(localDate);
            refreshFormLocation('', '');
        }

        $form.find('.master-select-wrap select').each(function () { refreshSelect($(this)); });
        $formModal.addClass('is-open').attr('aria-hidden', 'false');
        $('body').addClass('inventory-modal-open');
        $form.find('[name="nama"]').trigger('focus');
    }

    function closeForm() {
        ++formLocationToken;
        ++nextKodeToken;
        $formModal.removeClass('is-open').attr('aria-hidden', 'true');
        $('body').removeClass('inventory-modal-open');
        editingId = null;
    }

    $(document).on('click', '[data-open-inventory-modal]', function () { openForm(null); });
    $(document).on('click', '[data-close-inventory-modal]', closeForm);
    $instansiField.on('change', function () {
        refreshFormLocation($(this).val(), '');
    });
    $tableBody.on('click', '[data-inventory-id]', function () {
        window.location.assign($page.attr('data-show-base-url') + '/' + encodeURIComponent($(this).attr('data-inventory-id')));
    });
    $tableBody.on('click', '[data-delete-inventory-id]', function () {
        if (!window.AppCan('inventaris.destroy')) return;
        const item = rows.get(String($(this).attr('data-delete-inventory-id')));
        if (item) confirmDelete(item);
    });

    $('#inventory-edit').on('click', function () {
        if (detailItem) openForm(detailItem);
    });

    function confirmDelete(item) {
        if (!window.AppCan('inventaris.destroy')) return;
        Swal.fire({
            icon: 'warning',
            title: 'Hapus barang?',
            text: '"' + item.nama + '" akan dihapus dari inventaris.',
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

            $.ajax({ ...writeOptions(), url: apiUrl + '/' + encodeURIComponent(item.id), method: 'DELETE' })
                .done(function () {
                    if (isShowPage) {
                        window.location.assign($page.attr('data-index-url'));
                        return;
                    }
                    toast.fire({ icon: 'success', title: 'Barang berhasil dihapus.' });
                    loadList(currentPage);
                }).fail(function (xhr) {
                    Swal.fire({ icon: 'error', title: 'Gagal menghapus', text: apiError(xhr), confirmButtonText: 'Tutup', customClass: swalStyle, buttonsStyling: false, allowOutsideClick: false, allowEscapeKey: false });
                });
        });
    }

    $('#inventory-delete').on('click', function () {
        if (detailItem) confirmDelete(detailItem);
    });

    $form.on('submit', function (event) {
        event.preventDefault();
        if (!window.AppCan('inventaris.' + (editingId ? 'update' : 'store'))) return;

        for (const field of ['jenis_id', 'instansi_id', 'lokasi_id', 'pendanaan_id', 'kondisi']) {
            const $select = $form.find('[name="' + field + '"]');
            if (!$select.val()) {
                $formError.text('Lengkapi semua pilihan wajib sebelum menyimpan.').prop('hidden', false);
                $select.closest('.master-select-wrap').find('.master-select-trigger').trigger('focus');
                return;
            }
        }

        if (!this.reportValidity()) return;

        const price = $form.find('[name="harga_beli"]').val();
        const note = $form.find('[name="keterangan"]').val().trim();
        const payload = {
            nama: $form.find('[name="nama"]').val().trim(),
            jenis_id: $form.find('[name="jenis_id"]').val(),
            lokasi_id: $form.find('[name="lokasi_id"]').val(),
            masuk: $form.find('[name="masuk"]').val(),
            kondisi: $form.find('[name="kondisi"]').val(),
            pendanaan_id: $form.find('[name="pendanaan_id"]').val(),
            jumlah: Number($form.find('[name="jumlah"]').val()),
            harga_beli: price === '' ? null : price,
            keterangan: note || null,
        };

        $formError.text('').prop('hidden', true);
        $saveButton.prop('disabled', true).text('Menyimpan...');
        const wasEditing = Boolean(editingId);

        $.ajax({
            ...writeOptions(),
            url: editingId ? apiUrl + '/' + encodeURIComponent(editingId) : apiUrl,
            method: editingId ? 'PUT' : 'POST',
            data: JSON.stringify(payload),
        }).done(function () {
            closeForm();
            toast.fire({ icon: 'success', title: 'Barang berhasil disimpan.' });
            if (isShowPage) openDetail($page.attr('data-item-id'));
            else loadList(currentPage);
        }).fail(function (xhr) {
            $formError.text(apiError(xhr)).prop('hidden', false);
        }).always(function () {
            $saveButton.prop('disabled', false).html(
                '<i class="fas fa-check" aria-hidden="true"></i> ' +
                (wasEditing ? 'Simpan perubahan' : 'Simpan barang')
            );
        });
    });

    $(document).on('keydown', function (event) {
        if (event.key !== 'Escape') return;
        if ($('.master-select-wrap.is-open').length) {
            closeSelectMenus();
            return;
        }
    });

    $('#inventory-search').on('input', function () {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(function () { loadList(1); }, 350);
    });

    $('#inventory-filter-instansi').on('change', function () {
        refreshLocationFilter($(this).val());
        loadList(1);
    });

    $('#inventory-filter-lokasi, #inventory-filter-jenis, #inventory-filter-pendanaan, #inventory-filter-kondisi, #inventory-page-size, #inventory-filter-gudang')
        .on('change', function () { loadList(1); });

    $('#inventory-reset-filter').on('click', function () {
        clearTimeout(searchTimer);
        $('#inventory-search').val('');
        $('#inventory-filter-gudang').prop('checked', false);
        $('#inventory-filter-instansi, #inventory-filter-lokasi, #inventory-filter-jenis, #inventory-filter-pendanaan, #inventory-filter-kondisi')
            .val('').each(function () { refreshSelect($(this)); });
        refreshLocationFilter('');
        loadList(1);
    });

    $('#inventory-export').on('click', function () {
        const params = listParams(currentPage);
        delete params.page;
        delete params.size;
        window.location.assign(apiUrl + '/export?' + $.param(params));
    });

    $('#inventory-import').on('click', function () {
        $('#inventory-import-file').val('').trigger('click');
    });

    $('#inventory-import-file').on('change', function () {
        const file = this.files && this.files[0];
        if (!file) return;

        const payload = new FormData();
        payload.append('file', file);
        const $button = $('#inventory-import').prop('disabled', true);

        $.ajax({
            url: apiUrl + '/import',
            method: 'POST',
            data: payload,
            processData: false,
            contentType: false,
            dataType: 'json',
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            },
        }).done(function (result) {
            Swal.fire({
                icon: 'success',
                title: 'Import selesai',
                text: (result.imported || 0) + ' data masuk, ' + (result.skipped || 0) + ' dilewati.',
                confirmButtonText: 'Tutup',
                customClass: swalStyle,
                buttonsStyling: false,
                allowOutsideClick: false,
                allowEscapeKey: false,
            });
            loadList(1);
        }).fail(function (xhr) {
            Swal.fire({
                icon: 'error',
                title: 'Gagal mengimpor',
                text: apiError(xhr),
                confirmButtonText: 'Tutup',
                customClass: swalStyle,
                buttonsStyling: false,
                allowOutsideClick: false,
                allowEscapeKey: false,
            });
        }).always(function () {
            $button.prop('disabled', false);
            $('#inventory-import-file').val('');
        });
    });

    $('#inventory-prev').on('click', function () {
        if (currentPage > 1) loadList(currentPage - 1);
    });

    $('#inventory-next').on('click', function () {
        if (hasNextPage) loadList(currentPage + 1);
    });

    if (isShowPage) openDetail($page.attr('data-item-id'));
    else loadList(1);

    if (!isShowPage && window.AppCan('inventaris.store') &&
        new URLSearchParams(window.location.search).get('create') === '1') {
        openForm(null);
    }
})(jQuery);
