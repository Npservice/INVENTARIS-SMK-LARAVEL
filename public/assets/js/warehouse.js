(function ($) {
    'use strict';

    const $page = $('#warehouse-page');
    if (!$page.length || typeof Swal === 'undefined') return;

    const apiUrl = $page.attr('data-api-url');
    const $body = $('#warehouse-table-body');
    const $modal = $('#warehouse-stock-modal');
    const $form = $('#warehouse-stock-form');
    const $save = $('#warehouse-stock-save');
    const $error = $('#warehouse-stock-error');
    let currentPage = 1;
    let hasNextPage = false;
    let pendingList = null;
    let searchTimer = null;
    let locationToken = 0;
    let currentItem = null;
    let selectCounter = 0;

    const toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3500,
        timerProgressBar: true,
        customClass: { popup: 'modern-toast', title: 'modern-toast-title' },
    });

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

    function closeSelectMenus() {
        $page.find('.master-select-wrap.is-open').removeClass('is-open')
            .find('.master-select-trigger').attr('aria-expanded', 'false');
    }

    function refreshSelect($select) {
        const widget = $select.data('warehouseSelectWidget');
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
        const id = 'warehouse-select-menu-' + ++selectCounter;
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
        $select.data('warehouseSelectWidget', { button: $button, menu: $menu });
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
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                $(this).trigger('click');
            }
            if (event.key === 'Escape') {
                event.preventDefault();
                closeSelectMenus();
                $button.trigger('focus');
            }
            if (event.key === 'Tab') closeSelectMenus();
        });
    }

    $page.find('.master-select-wrap select').each(function () { initSelect($(this)); });
    $(document).on('click', function (event) {
        if (!$(event.target).closest('.master-select-wrap').length) closeSelectMenus();
    });

    function fillSelect($select, items, placeholder) {
        $select.empty().append($('<option>').val('').text(placeholder));
        items.forEach(function (item) {
            $select.append($('<option>').val(item.id).text(item.nama));
        });
        $select.val('').prop('disabled', false);
        refreshSelect($select);
    }

    function loadInstansi() {
        const $select = $('#warehouse-instansi').prop('disabled', true);
        refreshSelect($select);
        $.ajax({ ...requestOptions(), url: $page.attr('data-instansi-url'), method: 'GET' })
            .done(function (items) {
                if (!Array.isArray(items)) {
                    toast.fire({ icon: 'error', title: 'Format instansi tidak sesuai.' });
                    return;
                }
                fillSelect($select, items, 'Semua instansi');
            }).fail(function (xhr) {
                toast.fire({ icon: 'error', title: 'Gagal memuat instansi: ' + apiError(xhr) });
            });
    }

    function loadLocations(instansiId) {
        if (!window.AppCan('lokasi.select')) return;
        const token = ++locationToken;
        const $select = $('#warehouse-lokasi').prop('disabled', true).empty()
            .append($('<option>').val('').text('Memuat lokasi gudang...'));
        refreshSelect($select);
        $.ajax({
            ...requestOptions(),
            url: $page.attr('data-lokasi-url'),
            method: 'GET',
            data: instansiId ? { instansi_id: instansiId } : {},
        }).done(function (items) {
            if (token !== locationToken) return;
            if (!Array.isArray(items)) {
                toast.fire({ icon: 'error', title: 'Format lokasi tidak sesuai.' });
                return;
            }
            const warehouses = items.filter(item => item.is_gudang === true || item.is_gudang === 1);
            fillSelect($select, warehouses, 'Semua lokasi gudang');
            $('#warehouse-location-count').text(warehouses.length);
        }).fail(function (xhr) {
            if (token !== locationToken) return;
            toast.fire({ icon: 'error', title: 'Gagal memuat lokasi: ' + apiError(xhr) });
        });
    }

    function params(targetPage) {
        return {
            page: targetPage,
            size: $('#warehouse-page-size').val(),
            gudang: 1,
            search: $('#warehouse-search').val().trim(),
            instansi_id: $('#warehouse-instansi').val(),
            lokasi_id: $('#warehouse-lokasi').val(),
            kondisi: $('#warehouse-kondisi').val(),
        };
    }

    function showMessage(message) {
        $body.empty().append($('<tr>').append($('<td>').attr('colspan', 8)
            .addClass('master-table-message').text(message)));
    }

    function instansiBadgeColor(id) {
        let hash = 0;
        for (const character of String(id || '')) {
            hash = (hash * 31 + character.charCodeAt(0)) % 5;
        }
        return 'warehouse-instansi-color-' + hash;
    }

    function renderRow(item) {
        const location = item.lokasi || {};
        const instansi = location.instansi || {};
        const $action = $('<td>').addClass('inventory-action-cell');
        if (window.AppCan('inventaris.show')) $action.append($('<a>', {
                href: $page.attr('data-detail-base-url') + '/' + encodeURIComponent(item.id),
                title: 'Detail barang',
                'aria-label': 'Detail ' + item.nama,
            }).addClass('inventory-detail-button')
                .append($('<i>').addClass('fas fa-eye').attr('aria-hidden', 'true')));
        if (window.AppCan('inventaris.update') && window.AppCan('inventaris.show')) $action.append($('<button>', {
                type: 'button',
                title: 'Update stok',
                'aria-label': 'Update stok ' + item.nama,
            }).addClass('warehouse-stock-button')
                .attr('data-stock-id', item.id)
                .append($('<i>').addClass('fas fa-pen').attr('aria-hidden', 'true'))
                .append(document.createTextNode(' Update stok')));

        return $('<tr>')
            .append($('<td>').addClass('inventory-code-cell').text(item.kode_invt || '—'))
            .append($('<td>').addClass('inventory-name-cell').text(item.nama || '—'))
            .append($('<td>').text(item.jenis && item.jenis.nama ? item.jenis.nama : '—'))
            .append($('<td>').text(location.nama || '—'))
            .append($('<td>').append(instansi.nama ?
                $('<span>').addClass('warehouse-instansi-badge ' + instansiBadgeColor(instansi.id || location.instansi_id))
                    .text(instansi.nama) :
                document.createTextNode('—')))
            .append($('<td>').addClass('warehouse-quantity-cell').text(item.jumlah ?? '—'))
            .append($('<td>').append($('<span>')
                .addClass(item.kondisi === 'Baik' ? 'master-badge inventory-good' : 'master-badge inventory-damaged')
                .text(item.kondisi || '—')))
            .append($action);
    }

    function loadList(targetPage) {
        if (pendingList) pendingList.abort();
        showMessage('Memuat barang gudang...');
        $('#warehouse-summary').text('Memuat data...');
        const request = $.ajax({ ...requestOptions(), url: apiUrl, method: 'GET', data: params(targetPage) })
            .done(function (response) {
                if (!response || !Array.isArray(response.data)) {
                    showMessage('Format data server tidak sesuai.');
                    return;
                }
                if (!response.data.length && targetPage > 1) {
                    setTimeout(function () { loadList(targetPage - 1); }, 0);
                    return;
                }
                $body.empty();
                response.data.forEach(function (item) { $body.append(renderRow(item)); });
                if (!response.data.length) showMessage('Belum ada barang di gudang yang cocok.');
                currentPage = Number(response.current_page) || targetPage;
                hasNextPage = Boolean(response.next_page_url);
                $('#warehouse-page-number').text(currentPage);
                $('#warehouse-prev').prop('disabled', !response.prev_page_url);
                $('#warehouse-next').prop('disabled', !hasNextPage);
                $('#warehouse-visible-items').text(response.data.length);
                $('#warehouse-total-units').text(response.data.reduce((sum, item) => sum + Number(item.jumlah || 0), 0));
                $('#warehouse-caption').text(response.data.length ? 'Barang dari lokasi gudang' : 'Belum ada barang yang cocok');
                $('#warehouse-summary').text(response.data.length ?
                    'Menampilkan ' + (response.from || 1) + '–' + (response.to || response.data.length) + ' barang' :
                    'Tidak ada barang');
            }).fail(function (xhr, status) {
                if (status === 'abort') return;
                showMessage(apiError(xhr));
                $('#warehouse-summary').text('Gagal memuat data');
                $('#warehouse-prev, #warehouse-next').prop('disabled', true);
            }).always(function () { if (pendingList === request) pendingList = null; });
        pendingList = request;
    }

    function closeModal() {
        $modal.removeClass('is-open').attr('aria-hidden', 'true');
        $('body').removeClass('inventory-modal-open');
        currentItem = null;
    }

    function openStock(id) {
        if (!window.AppCan('inventaris.update') || !window.AppCan('inventaris.show')) return;
        $.ajax({ ...requestOptions(), url: apiUrl + '/' + encodeURIComponent(id), method: 'GET' })
            .done(function (item) {
                if (!item.lokasi || !item.lokasi.is_gudang) {
                    toast.fire({ icon: 'error', title: 'Barang ini tidak lagi berada di gudang.' });
                    loadList(currentPage);
                    return;
                }
                currentItem = item;
                $('#warehouse-stock-name').text(item.nama || 'Barang');
                $('#warehouse-stock-code').text(item.kode_invt || '');
                $('#warehouse-stock-quantity').val(item.jumlah);
                $error.text('').prop('hidden', true);
                $modal.addClass('is-open').attr('aria-hidden', 'false');
                $('body').addClass('inventory-modal-open');
                $('#warehouse-stock-quantity').trigger('focus');
            }).fail(function (xhr) {
                toast.fire({ icon: 'error', title: 'Gagal memuat barang: ' + apiError(xhr) });
            });
    }

    $body.on('click', '[data-stock-id]', function () { openStock($(this).attr('data-stock-id')); });
    $(document).on('click', '[data-close-warehouse-stock]', closeModal);

    $form.on('submit', function (event) {
        event.preventDefault();
        if (!window.AppCan('inventaris.update') || !window.AppCan('inventaris.show')) return;
        if (!currentItem || !this.reportValidity()) return;
        const quantity = Number($('#warehouse-stock-quantity').val());
        if (!Number.isInteger(quantity) || quantity < 1) {
            $error.text('Jumlah stok harus bilangan bulat minimal 1.').prop('hidden', false);
            return;
        }
        const itemId = currentItem.id;
        $save.prop('disabled', true).text('Menyimpan...');
        $error.text('').prop('hidden', true);
        $.ajax({ ...requestOptions(), url: apiUrl + '/' + encodeURIComponent(itemId), method: 'GET' })
            .then(function (fresh) {
                if (!fresh.lokasi || !fresh.lokasi.is_gudang) {
                    throw new Error('Barang ini tidak lagi berada di gudang.');
                }
                const payload = {
                    nama: fresh.nama,
                    jenis_id: fresh.jenis_id,
                    lokasi_id: fresh.lokasi_id,
                    masuk: fresh.masuk ? String(fresh.masuk).slice(0, 10) : '',
                    kondisi: fresh.kondisi,
                    pendanaan_id: fresh.pendanaan_id,
                    jumlah: quantity,
                    harga_beli: fresh.harga_beli,
                    keterangan: fresh.keterangan,
                };
                return $.ajax({
                    url: apiUrl + '/' + encodeURIComponent(itemId),
                    method: 'PUT',
                    dataType: 'json',
                    contentType: 'application/json',
                    data: JSON.stringify(payload),
                    headers: {
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    },
                });
            }).done(function () {
            closeModal();
            toast.fire({ icon: 'success', title: 'Jumlah stok berhasil diperbarui.' });
            loadList(currentPage);
        }).fail(function (xhr) {
            $error.text(apiError(xhr)).prop('hidden', false);
        }).always(function () {
            $save.prop('disabled', false).html('<i class="fas fa-check" aria-hidden="true"></i> Simpan stok');
        });
    });

    $('#warehouse-search').on('input', function () {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(function () { loadList(1); }, 350);
    });
    $('#warehouse-instansi').on('change', function () {
        loadLocations($(this).val());
        loadList(1);
    });
    $('#warehouse-lokasi, #warehouse-kondisi, #warehouse-page-size').on('change', function () { loadList(1); });
    $('#warehouse-reset').on('click', function () {
        clearTimeout(searchTimer);
        $('#warehouse-search').val('');
        $('#warehouse-instansi, #warehouse-kondisi').val('').each(function () { refreshSelect($(this)); });
        loadLocations('');
        loadList(1);
    });
    $('#warehouse-prev').on('click', function () { if (currentPage > 1) loadList(currentPage - 1); });
    $('#warehouse-next').on('click', function () { if (hasNextPage) loadList(currentPage + 1); });

    if (window.AppCan('instansi.select')) loadInstansi();
    else $('#warehouse-instansi').closest('.inventory-field').remove();
    if (window.AppCan('lokasi.select')) loadLocations('');
    else $('#warehouse-lokasi').closest('.inventory-field').remove();
    loadList(1);
})(jQuery);
