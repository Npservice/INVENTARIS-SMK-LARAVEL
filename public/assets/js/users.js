(function ($) {
    'use strict';

    const $page = $('#user-index-page, #user-show-page').first();
    if (!$page.length || typeof Swal === 'undefined') return;

    const isDetail = $page.is('#user-show-page');
    const apiUrl = $page.attr('data-api-url');
    const currentUserId = $page.attr('data-current-user-id');
    const $modal = $('#user-modal');
    const $form = $('#user-form');
    let pageNumber = 1;
    let hasNextPage = false;
    let pendingList = null;
    let searchTimer = null;
    let selectCounter = 0;
    let detailItem = null;
    let editingId = null;

    const roles = { 1: 'Administrator', 2: 'Staf', 3: 'Guru / petugas' };
    const statuses = { Guru: 'Guru', Kepsek: 'Kepala sekolah', Pimpinan: 'Pimpinan', Karyawan: 'Karyawan', Staff: 'Staf' };
    const swalStyle = {
        popup: 'modern-swal-popup', title: 'modern-swal-title',
        htmlContainer: 'modern-swal-html', confirmButton: 'modern-swal-confirm',
        cancelButton: 'modern-swal-cancel',
    };
    const toast = Swal.mixin({
        toast: true, position: 'top-end', showConfirmButton: false,
        timer: 3500, timerProgressBar: true,
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

    function requestOptions() {
        return { dataType: 'json', headers: { Accept: 'application/json' } };
    }

    function writeOptions() {
        return {
            ...requestOptions(), contentType: 'application/json',
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            },
        };
    }

    function formatDate(value) {
        if (!value) return '—';
        const date = new Date(value);
        return Number.isNaN(date.getTime()) ? '—' :
            new Intl.DateTimeFormat('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }).format(date);
    }

    function initials(name) {
        return String(name || '?').trim().split(/\s+/).slice(0, 2).map(part => part.charAt(0)).join('').toUpperCase();
    }

    function closeSelectMenus() {
        $('.master-select-wrap.is-open').removeClass('is-open')
            .find('.master-select-trigger').attr('aria-expanded', 'false');
    }

    function refreshSelect($select) {
        const widget = $select.data('userSelectWidget');
        if (!widget) return;
        const value = String($select.val() ?? '');
        widget.button.find('.master-select-text').text(
            $select.find('option:selected').text() || $select.find('option').first().text()
        );
        widget.menu.empty();
        $select.find('option').each(function () {
            const selected = this.value === value;
            const $option = $('<button>', { type: 'button', role: 'option' })
                .addClass('master-select-option').toggleClass('is-selected', selected)
                .attr('aria-selected', selected ? 'true' : 'false')
                .attr('data-value', this.value).append($('<span>').text(this.textContent));
            if (selected) $option.append($('<i>').addClass('fas fa-check').attr('aria-hidden', 'true'));
            widget.menu.append($option);
        });
    }

    function initSelect($select) {
        const $wrap = $select.closest('.master-select-wrap');
        const id = 'user-select-menu-' + ++selectCounter;
        const label = $select.attr('aria-label') ||
            $('label[for="' + $select.attr('id') + '"]').text().trim() || 'Pilih opsi';
        const $button = $('<button>', {
            type: 'button', 'aria-label': label, 'aria-haspopup': 'listbox',
            'aria-expanded': 'false', 'aria-controls': id,
        }).addClass('master-select-trigger')
            .append($('<span>').addClass('master-select-text'))
            .append($('<i>').addClass('fas fa-chevron-down').attr('aria-hidden', 'true'));
        const $menu = $('<div>', { id: id, role: 'listbox', 'aria-label': label })
            .addClass('master-select-menu');
        $select.attr({ tabindex: '-1', 'aria-hidden': 'true' });
        $wrap.addClass('is-enhanced').append($button, $menu);
        $select.data('userSelectWidget', { button: $button, menu: $menu });
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

    $('#user-index-page .master-select-wrap select, #user-modal .master-select-wrap select')
        .each(function () { initSelect($(this)); });
    $(document).on('click', function (event) {
        if (!$(event.target).closest('.master-select-wrap').length) closeSelectMenus();
    });

    function showListMessage(message) {
        $('#user-table-body').empty().append($('<tr>').append(
            $('<td>').attr('colspan', 6).addClass('master-table-message').text(message)
        ));
    }

    function renderRow(item) {
        const $identity = $('<span>').addClass('user-table-identity')
            .append($('<span>').addClass('user-table-avatar').text(initials(item.name)))
            .append($('<strong>').text(item.name || '—'));
        const role = Number(item.role);
        return $('<tr>')
            .append($('<td>').append($identity))
            .append($('<td>').addClass('user-username-cell').text(item.username || '—'))
            .append($('<td>').text(statuses[item.status] || item.status || '—'))
            .append($('<td>').append($('<span>').addClass('master-badge user-role-badge user-role-' + role).text(roles[role] || '—')))
            .append($('<td>').addClass('user-date-cell').text(formatDate(item.created_at)))
            .append($('<td>').addClass('inventory-action-cell').append(window.AppCan('user.show') ?
                $('<a>', {
                    href: $page.attr('data-detail-base-url') + '/' + encodeURIComponent(item.id),
                    title: 'Detail pengguna', 'aria-label': 'Detail pengguna ' + (item.name || ''),
                }).addClass('inventory-detail-button').append(
                    $('<i>').addClass('fas fa-eye').attr('aria-hidden', 'true')
                ) : null
            ));
    }

    function loadList(targetPage) {
        if (pendingList) pendingList.abort();
        showListMessage('Memuat pengguna dari server...');
        $('#user-summary').text('Memuat data...');
        const request = $.ajax({
            ...requestOptions(), url: apiUrl, method: 'GET',
            data: {
                page: targetPage, size: $('#user-page-size').val(),
                search: $('#user-search').val().trim(),
                role: $('#user-role-filter').val(),
                status: $('#user-status-filter').val(),
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
            const $body = $('#user-table-body').empty();
            response.data.forEach(function (item) { $body.append(renderRow(item)); });
            if (!response.data.length) showListMessage('Belum ada pengguna yang cocok.');
            pageNumber = Number(response.current_page) || targetPage;
            hasNextPage = Boolean(response.next_page_url);
            $('#user-page-number').text(pageNumber);
            $('#user-prev').prop('disabled', !response.prev_page_url);
            $('#user-next').prop('disabled', !hasNextPage);
            $('#user-caption').text(response.data.length ? 'Identitas pengguna yang tersimpan' : 'Belum ada data yang cocok');
            $('#user-summary').text(response.data.length ?
                'Menampilkan ' + (response.from || 1) + '–' + (response.to || response.data.length) + ' pengguna' :
                'Tidak ada pengguna');
        }).fail(function (xhr, status) {
            if (status === 'abort') return;
            showListMessage(apiError(xhr));
            $('#user-summary').text('Gagal memuat data');
            $('#user-prev, #user-next').prop('disabled', true);
        }).always(function () { if (pendingList === request) pendingList = null; });
        pendingList = request;
    }

    function closeModal() {
        $modal.removeClass('is-open').attr('aria-hidden', 'true');
        $('body').removeClass('inventory-modal-open');
        editingId = null;
    }

    function openModal(item) {
        if (!window.AppCan('user.' + (item ? 'update' : 'store'))) return;
        editingId = item ? String(item.id) : null;
        $form[0].reset();
        $('#user-form-error').text('').prop('hidden', true);
        $('#user-modal-title').text(item ? 'Edit pengguna' : 'Tambah pengguna');
        $('#user-save').prop('disabled', false).html('<i class="fas fa-check" aria-hidden="true"></i> ' +
            (item ? 'Simpan perubahan' : 'Simpan pengguna'));
        $form.find('[name="password"], [name="password_confirmation"]').prop('required', !item).attr('type', 'password');
        $('.user-password-toggle').attr('aria-label', 'Tampilkan kata sandi').find('i')
            .removeClass('fa-eye-slash').addClass('fa-eye');
        $('#user-password-required, #user-confirm-required').prop('hidden', !!item);
        $('#user-password-hint').prop('hidden', !item);
        if (item) {
            $form.find('[name="name"]').val(item.name || '');
            $form.find('[name="username"]').val(item.username || '');
            $form.find('[name="status"]').val(item.status || '');
            $form.find('[name="role"]').val(String(item.role || ''));
        }
        $form.find('.master-select-wrap select').each(function () { refreshSelect($(this)); });
        $modal.addClass('is-open').attr('aria-hidden', 'false');
        $('body').addClass('inventory-modal-open');
        $form.find('[name="name"]').trigger('focus');
    }

    $('#user-create').on('click', function () { openModal(null); });
    $('#user-edit').on('click', function () { if (detailItem) openModal(detailItem); });
    $('[data-close-user-modal]').on('click', closeModal);
    $('.user-password-toggle').on('click', function () {
        const $input = $form.find('[name="' + $(this).attr('data-toggle-password') + '"]');
        const visible = $input.attr('type') === 'password';
        $input.attr('type', visible ? 'text' : 'password');
        $(this).attr('aria-label', visible ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi')
            .find('i').toggleClass('fa-eye', !visible).toggleClass('fa-eye-slash', visible);
    });

    $form.on('submit', function (event) {
        event.preventDefault();
        if (!window.AppCan('user.' + (editingId ? 'update' : 'store'))) return;
        for (const field of ['status', 'role']) {
            if (!$form.find('[name="' + field + '"]').val()) {
                $('#user-form-error').text('Pilih jabatan dan peran pengguna.').prop('hidden', false);
                $form.find('[name="' + field + '"]').closest('.master-select-wrap')
                    .find('.master-select-trigger').trigger('focus');
                return;
            }
        }
        if (!this.reportValidity()) return;
        const password = $form.find('[name="password"]').val();
        const confirmation = $form.find('[name="password_confirmation"]').val();
        if (password !== confirmation) {
            $('#user-form-error').text('Konfirmasi kata sandi tidak sesuai.').prop('hidden', false);
            $form.find('[name="password_confirmation"]').trigger('focus');
            return;
        }
        const payload = {
            name: $form.find('[name="name"]').val().trim(),
            username: $form.find('[name="username"]').val().trim(),
            status: $form.find('[name="status"]').val(),
            role: Number($form.find('[name="role"]').val()),
        };
        if (password) {
            payload.password = password;
            payload.password_confirmation = confirmation;
        }
        const wasEditing = Boolean(editingId);
        const $button = $('#user-save').prop('disabled', true);
        $('#user-form-error').text('').prop('hidden', true);
        $.ajax({
            ...writeOptions(),
            url: apiUrl + (wasEditing ? '/' + encodeURIComponent(editingId) : ''),
            method: wasEditing ? 'PUT' : 'POST',
            data: JSON.stringify(payload),
        }).done(function (item) {
            closeModal();
            toast.fire({ icon: 'success', title: wasEditing ? 'Pengguna berhasil diperbarui.' : 'Pengguna berhasil ditambahkan.' });
            if (isDetail) loadDetail();
            else if (item && item.id) window.location.assign($page.attr('data-detail-base-url') + '/' + encodeURIComponent(item.id));
            else loadList(1);
        }).fail(function (xhr) {
            $('#user-form-error').text(apiError(xhr)).prop('hidden', false);
        }).always(function () {
            $button.prop('disabled', false).html('<i class="fas fa-check" aria-hidden="true"></i> ' +
                (wasEditing ? 'Simpan perubahan' : 'Simpan pengguna'));
        });
    });

    function setDetail(key, value) {
        $page.find('[data-user="' + key + '"]').text(value || '—');
    }

    function loadDetail() {
        detailItem = null;
        $('#user-edit, #user-delete').prop('disabled', true);
        $('#user-detail-error').text('').prop('hidden', true);
        $.ajax({
            ...requestOptions(), url: apiUrl + '/' + encodeURIComponent($page.attr('data-user-id')),
            method: 'GET',
        }).done(function (item) {
            detailItem = item;
            $('#user-detail-title').text(item.name || 'Pengguna');
            $('#user-detail-subtitle').text('@' + (item.username || '—'));
            setDetail('name', item.name);
            setDetail('username', item.username);
            setDetail('status', statuses[item.status] || item.status);
            setDetail('role', roles[Number(item.role)]);
            setDetail('created_at', formatDate(item.created_at));
            setDetail('updated_at', formatDate(item.updated_at));
            $('#user-loan-count').text(Array.isArray(item.peminjaman) ? item.peminjaman.length : '—');
            $('#user-maintenance-count').text(Array.isArray(item.perawatan) ? item.perawatan.length : '—');
            $('#user-edit').prop('disabled', false);
            $('#user-delete').prop('disabled', String(item.id) === String(currentUserId));
        }).fail(function (xhr) {
            $('#user-detail-title').text('Detail tidak tersedia');
            $('#user-detail-error').text(apiError(xhr)).prop('hidden', false);
        });
    }

    $('#user-delete').on('click', function () {
        if (!window.AppCan('user.destroy')) return;
        if (!detailItem || String(detailItem.id) === String(currentUserId)) return;
        Swal.fire({
            icon: 'warning', title: 'Hapus pengguna?',
            text: 'Akun ' + (detailItem.name || 'pengguna') + ' akan dihapus.',
            showCancelButton: true, confirmButtonText: 'Ya, hapus',
            cancelButtonText: 'Batal', reverseButtons: true,
            customClass: swalStyle, buttonsStyling: false,
            allowOutsideClick: false, allowEscapeKey: false,
        }).then(function (result) {
            if (!result.isConfirmed) return;
            $('#user-delete').prop('disabled', true);
            $.ajax({
                ...writeOptions(), url: apiUrl + '/' + encodeURIComponent(detailItem.id),
                method: 'DELETE',
            }).done(function () {
                window.location.assign($page.attr('data-index-url'));
            }).fail(function (xhr) {
                $('#user-delete').prop('disabled', false);
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
        $('#user-search').on('input', function () {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(function () { loadList(1); }, 350);
        });
        $('#user-role-filter, #user-status-filter, #user-page-size').on('change', function () { loadList(1); });
        $('#user-reset').on('click', function () {
            clearTimeout(searchTimer);
            $('#user-search').val('');
            $('#user-role-filter, #user-status-filter').val('').each(function () { refreshSelect($(this)); });
            loadList(1);
        });
        $('#user-prev').on('click', function () { if (pageNumber > 1) loadList(pageNumber - 1); });
        $('#user-next').on('click', function () { if (hasNextPage) loadList(pageNumber + 1); });
        loadList(1);
    }
})(jQuery);
