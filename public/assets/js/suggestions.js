(function ($) {
    'use strict';

    const $page = $('#suggestion-index-page, #suggestion-show-page').first();
    if (!$page.length || typeof Swal === 'undefined') return;

    const isDetail = $page.is('#suggestion-show-page');
    const apiUrl = $page.attr('data-api-url');
    const $modal = $('#suggestion-modal');
    let pageNumber = 1;
    let hasNextPage = false;
    let pendingList = null;
    let searchTimer = null;
    let detailItem = null;

    const swalStyle = {
        popup: 'modern-swal-popup',
        title: 'modern-swal-title',
        htmlContainer: 'modern-swal-html',
        confirmButton: 'modern-swal-confirm',
        cancelButton: 'modern-swal-cancel',
    };
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
        if (xhr && xhr.status === 403) return 'Anda tidak memiliki izin untuk tindakan ini.';
        if (xhr && [401, 419].includes(xhr.status)) return 'Sesi tidak aktif. Silakan masuk kembali.';
        return response && response.message ? response.message : 'Permintaan gagal. Silakan coba lagi.';
    }

    function formatDate(value) {
        if (!value) return '—';
        const date = new Date(value);
        return Number.isNaN(date.getTime()) ? '—' :
            new Intl.DateTimeFormat('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }).format(date);
    }

    function showListMessage(message) {
        $('#suggestion-table-body').empty().append(
            $('<tr>').append($('<td>').attr('colspan', 4).addClass('master-table-message').text(message))
        );
    }

    function renderRow(item) {
        const message = String(item.kritik_saran || '');
        const preview = message.length > 120 ? message.slice(0, 117).trimEnd() + '…' : message;
        return $('<tr>')
            .append($('<td>').addClass('suggestion-name-cell').text(item.nama || '—'))
            .append($('<td>').append($('<span>').addClass('suggestion-message-preview').text(preview || '—')))
            .append($('<td>').addClass('suggestion-date-cell').text(formatDate(item.created_at)))
            .append($('<td>').addClass('inventory-action-cell').append(window.AppCan('kritik-saran.show') ?
                $('<a>', {
                    href: $page.attr('data-detail-base-url') + '/' + encodeURIComponent(item.id),
                    title: 'Detail masukan',
                    'aria-label': 'Detail masukan dari ' + (item.nama || 'pengirim'),
                }).addClass('inventory-detail-button').append(
                    $('<i>').addClass('fas fa-eye').attr('aria-hidden', 'true')
                ) : null
            ));
    }

    function loadList(targetPage) {
        if (pendingList) pendingList.abort();
        showListMessage('Memuat masukan dari server...');
        $('#suggestion-summary').text('Memuat data...');
        const request = $.ajax({
            url: apiUrl,
            method: 'GET',
            dataType: 'json',
            headers: { Accept: 'application/json' },
            data: {
                page: targetPage,
                size: $('#suggestion-page-size').val(),
                search: $('#suggestion-search').val().trim(),
            },
        }).done(function (response) {
            if (!response || !Array.isArray(response.data)) {
                showListMessage('Format data server tidak sesuai.');
                return;
            }
            if (!response.data.length && targetPage > 1) {
                setTimeout(function () { loadList(targetPage - 1); }, 0);
                return;
            }
            const $body = $('#suggestion-table-body').empty();
            response.data.forEach(function (item) { $body.append(renderRow(item)); });
            if (!response.data.length) showListMessage('Belum ada masukan yang cocok.');
            pageNumber = Number(response.current_page) || targetPage;
            hasNextPage = Boolean(response.next_page_url);
            $('#suggestion-page-number').text(pageNumber);
            $('#suggestion-prev').prop('disabled', !response.prev_page_url);
            $('#suggestion-next').prop('disabled', !hasNextPage);
            $('#suggestion-caption').text(response.data.length ? 'Masukan yang tercatat di sistem' : 'Belum ada data yang cocok');
            $('#suggestion-summary').text(response.data.length ?
                'Menampilkan ' + (response.from || 1) + '–' + (response.to || response.data.length) + ' masukan' :
                'Tidak ada masukan');
        }).fail(function (xhr, status) {
            if (status === 'abort') return;
            showListMessage(apiError(xhr));
            $('#suggestion-summary').text('Gagal memuat data');
            $('#suggestion-prev, #suggestion-next').prop('disabled', true);
        }).always(function () {
            if (pendingList === request) pendingList = null;
        });
        pendingList = request;
    }

    function initPageSizeSelect() {
        const $select = $('#suggestion-page-size');
        const $wrap = $select.closest('.master-select-wrap');
        const $button = $('<button>', {
            type: 'button', 'aria-label': 'Jumlah per halaman',
            'aria-haspopup': 'listbox', 'aria-expanded': 'false',
        }).addClass('master-select-trigger')
            .append($('<span>').addClass('master-select-text'))
            .append($('<i>').addClass('fas fa-chevron-down').attr('aria-hidden', 'true'));
        const $menu = $('<div>', { role: 'listbox', 'aria-label': 'Jumlah per halaman' })
            .addClass('master-select-menu');
        $select.attr({ tabindex: '-1', 'aria-hidden': 'true' });
        $wrap.addClass('is-enhanced').append($button, $menu);

        function refresh() {
            $button.find('.master-select-text').text($select.find('option:selected').text());
            $menu.empty();
            $select.find('option').each(function () {
                const selected = this.value === $select.val();
                const $option = $('<button>', { type: 'button', role: 'option' })
                    .addClass('master-select-option').toggleClass('is-selected', selected)
                    .attr('aria-selected', selected ? 'true' : 'false')
                    .attr('data-value', this.value).append($('<span>').text(this.textContent));
                if (selected) $option.append($('<i>').addClass('fas fa-check').attr('aria-hidden', 'true'));
                $menu.append($option);
            });
        }

        function close() {
            $wrap.removeClass('is-open');
            $button.attr('aria-expanded', 'false');
        }

        refresh();
        $button.on('click', function () {
            if ($wrap.hasClass('is-open')) { close(); return; }
            refresh();
            const bounds = $button[0].getBoundingClientRect();
            $wrap.toggleClass('is-dropup', window.innerHeight - bounds.bottom < 230 && bounds.top > 230);
            $wrap.addClass('is-open');
            $button.attr('aria-expanded', 'true');
            $menu.find('.is-selected').trigger('focus');
        });
        $button.on('keydown', function (event) {
            if (['ArrowDown', 'ArrowUp', 'Enter', ' '].includes(event.key)) {
                event.preventDefault();
                if (!$wrap.hasClass('is-open')) $button.trigger('click');
            }
        });
        $menu.on('click', '.master-select-option', function () {
            $select.val($(this).attr('data-value')).trigger('change');
            refresh();
            close();
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
            if (event.key === 'Escape') { event.preventDefault(); close(); $button.trigger('focus'); }
            if (event.key === 'Tab') close();
        });
        $(document).on('click', function (event) {
            if (!$(event.target).closest($wrap).length) close();
        });
    }

    function closeModal() {
        $modal.removeClass('is-open').attr('aria-hidden', 'true');
        $('body').removeClass('inventory-modal-open');
    }

    $('#suggestion-create').on('click', function () {
        $('#suggestion-form')[0].reset();
        $('#suggestion-form-error').text('').prop('hidden', true);
        $modal.addClass('is-open').attr('aria-hidden', 'false');
        $('body').addClass('inventory-modal-open');
        $('#suggestion-form [name="nama"]').trigger('focus');
    });
    $('[data-close-suggestion-modal]').on('click', closeModal);

    $('#suggestion-form').on('submit', function (event) {
        event.preventDefault();
        if (!this.reportValidity()) return;
        const $button = $('#suggestion-save').prop('disabled', true);
        $('#suggestion-form-error').text('').prop('hidden', true);
        $.ajax({
            url: apiUrl,
            method: 'POST',
            dataType: 'json',
            contentType: 'application/json',
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            },
            data: JSON.stringify({
                nama: $('#suggestion-form [name="nama"]').val().trim(),
                kritik_saran: $('#suggestion-form [name="kritik_saran"]').val().trim(),
            }),
        }).done(function () {
            closeModal();
            toast.fire({ icon: 'success', title: 'Masukan berhasil disimpan.' });
            loadList(1);
        }).fail(function (xhr) {
            $('#suggestion-form-error').text(apiError(xhr)).prop('hidden', false);
        }).always(function () { $button.prop('disabled', false); });
    });

    function loadDetail() {
        $.ajax({
            url: apiUrl + '/' + encodeURIComponent($page.attr('data-suggestion-id')),
            method: 'GET',
            dataType: 'json',
            headers: { Accept: 'application/json' },
        }).done(function (item) {
            detailItem = item;
            $('#suggestion-detail-name').text(item.nama || '—');
            $('#suggestion-detail-date').text(formatDate(item.created_at));
            $('#suggestion-detail-subtitle').text('Masukan dari ' + (item.nama || 'pengirim'));
            $('#suggestion-detail-message').text(item.kritik_saran || '—');
            $('#suggestion-delete').prop('disabled', false);
        }).fail(function (xhr) {
            $('#suggestion-detail-subtitle').text('Data tidak tersedia');
            $('#suggestion-detail-message').text('Masukan tidak dapat dimuat.');
            $('#suggestion-detail-error').text(apiError(xhr)).prop('hidden', false);
        });
    }

    $('#suggestion-delete').on('click', function () {
        if (!window.AppCan('kritik-saran.destroy')) return;
        if (!detailItem) return;
        Swal.fire({
            icon: 'warning',
            title: 'Hapus masukan?',
            text: 'Masukan dari ' + (detailItem.nama || 'pengirim') + ' akan dihapus.',
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
            $('#suggestion-delete').prop('disabled', true);
            $.ajax({
                url: apiUrl + '/' + encodeURIComponent(detailItem.id),
                method: 'DELETE',
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                },
            }).done(function () {
                window.location.assign($page.attr('data-index-url'));
            }).fail(function (xhr) {
                $('#suggestion-delete').prop('disabled', false);
                Swal.fire({
                    icon: 'error', title: 'Gagal menghapus', text: apiError(xhr),
                    confirmButtonText: 'Tutup', customClass: swalStyle,
                    buttonsStyling: false, allowOutsideClick: false, allowEscapeKey: false,
                });
            });
        });
    });

    if (isDetail) {
        loadDetail();
    } else {
        initPageSizeSelect();
        $('#suggestion-search').on('input', function () {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(function () { loadList(1); }, 350);
        });
        $('#suggestion-page-size').on('change', function () { loadList(1); });
        $('#suggestion-prev').on('click', function () { if (pageNumber > 1) loadList(pageNumber - 1); });
        $('#suggestion-next').on('click', function () { if (hasNextPage) loadList(pageNumber + 1); });
        loadList(1);
    }
})(jQuery);
