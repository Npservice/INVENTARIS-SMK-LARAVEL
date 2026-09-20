(function ($) {
    'use strict';

    const $page = $('#role-index-page, #role-show-page').first();
    if (!$page.length || typeof Swal === 'undefined') return;

    const isDetail = $page.is('#role-show-page');
    const apiUrl = $page.attr('data-api-url');
    const permissionUrl = $page.attr('data-permission-url');
    const $modal = $('#role-modal');
    const $form = $('#role-form');
    let pageNumber = 1;
    let hasNextPage = false;
    let pendingList = null;
    let searchTimer = null;
    let detailItem = null;
    let editingId = null;
    let allPermissions = null;

    const groupLabels = {
        instansi: 'Instansi', lokasi: 'Lokasi', jenis: 'Jenis', pendanaan: 'Pendanaan',
        inventaris: 'Inventaris', perawatan: 'Perawatan', peminjaman: 'Peminjaman',
        user: 'Pengguna', 'kritik-saran': 'Usulan & Saran', role: 'Role', permission: 'Permission',
    };
    const actionLabels = {
        select: 'Pilihan data', index: 'Lihat daftar', show: 'Lihat detail',
        store: 'Tambah', update: 'Ubah', destroy: 'Hapus',
        'next-kode': 'Buat kode', export: 'Ekspor', 'import-template': 'Template impor',
        import: 'Impor', 'find-by-kode': 'Cari kode', kembali: 'Pengembalian',
    };
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

    function splitPermission(name) {
        const dot = name.lastIndexOf('.');
        return dot < 0 ? [name, name] : [name.slice(0, dot), name.slice(dot + 1)];
    }

    function groupPermissions(items) {
        const groups = {};
        items.forEach(function (item) {
            const name = typeof item === 'string' ? item : item.name;
            if (!name) return;
            const [resource, action] = splitPermission(name);
            if (!groups[resource]) groups[resource] = [];
            groups[resource].push({ name: name, action: action });
        });
        return groups;
    }

    function groupTitle(resource) {
        return groupLabels[resource] || resource.replace(/-/g, ' ');
    }

    function actionTitle(action) {
        return actionLabels[action] || action.replace(/-/g, ' ');
    }

    function updateSelectedCount() {
        $('#role-selected-count').text($('#role-permission-groups input:checked').length + ' izin dipilih');
        $('#role-permission-groups .role-group').each(function () {
            const total = $(this).find('input[type="checkbox"]').length;
            const checked = $(this).find('input[type="checkbox"]:checked').length;
            $(this).find('.role-group-count').text(checked + '/' + total);
        });
    }

    function renderPermissionEditor(selectedNames) {
        const $container = $('#role-permission-groups').empty();
        if (!Array.isArray(allPermissions)) {
            $container.append($('<p>').addClass('role-permission-message').text('Daftar izin belum tersedia.'));
            return;
        }
        const selected = new Set(selectedNames || []);
        const groups = groupPermissions(allPermissions);
        Object.entries(groups).forEach(function ([resource, permissions]) {
            const $group = $('<section>').addClass('role-group');
            const $head = $('<div>').addClass('role-group-head')
                .append($('<div>').append($('<strong>').text(groupTitle(resource)))
                    .append($('<span>').addClass('role-group-count')))
                .append($('<button>', { type: 'button' }).addClass('role-group-toggle').text('Pilih semua'));
            const $items = $('<div>').addClass('role-group-items');
            permissions.forEach(function (permission) {
                const $checkbox = $('<input>', { type: 'checkbox', name: 'permissions[]' })
                    .val(permission.name).prop('checked', selected.has(permission.name));
                $items.append($('<label>').addClass('role-permission-option')
                    .append($checkbox)
                    .append($('<span>').addClass('role-checkbox-visual').append($('<i>').addClass('fas fa-check').attr('aria-hidden', 'true')))
                    .append($('<span>').addClass('role-permission-option-text')
                        .append($('<strong>').text(actionTitle(permission.action)))
                        .append($('<small>').text(permission.name))));
            });
            $group.append($head, $items);
            $container.append($group);
        });
        if (!Object.keys(groups).length) {
            $container.append($('<p>').addClass('role-permission-message').text('Belum ada izin yang tersedia.'));
        }
        updateSelectedCount();
    }

    function loadPermissions(selectedNames) {
        if (!window.AppCan('permission.index')) return;
        if (Array.isArray(allPermissions)) {
            renderPermissionEditor(selectedNames);
            return;
        }
        $('#role-permission-groups').html('<p class="role-permission-message">Memuat daftar izin...</p>');
        $('#role-save').prop('disabled', true);
        $.ajax({ ...requestOptions(), url: permissionUrl, method: 'GET' })
            .done(function (items) {
                if (!Array.isArray(items)) {
                    $('#role-permission-groups').html('<p class="role-permission-message">Format daftar izin tidak sesuai.</p>');
                    return;
                }
                allPermissions = items;
                renderPermissionEditor(selectedNames);
                $('#role-save').prop('disabled', false);
            }).fail(function (xhr) {
                $('#role-permission-groups').empty().append(
                    $('<p>').addClass('role-permission-message').text(apiError(xhr))
                );
            });
    }

    $('#role-permission-groups').on('change', 'input[type="checkbox"]', updateSelectedCount);
    $('#role-permission-groups').on('click', '.role-group-toggle', function () {
        const $group = $(this).closest('.role-group');
        const $boxes = $group.find('input[type="checkbox"]');
        const selectAll = $boxes.filter(':checked').length !== $boxes.length;
        $boxes.prop('checked', selectAll);
        updateSelectedCount();
    });

    function closeModal() {
        $modal.removeClass('is-open').attr('aria-hidden', 'true');
        $('body').removeClass('inventory-modal-open');
        editingId = null;
    }

    function openModal(item) {
        if (!window.AppCan('role.' + (item ? 'update' : 'store')) || !window.AppCan('permission.index')) return;
        editingId = item ? String(item.id) : null;
        $form[0].reset();
        $('#role-form-error').text('').prop('hidden', true);
        $('#role-modal-title').text(item ? 'Edit role' : 'Tambah role');
        $('#role-save').text(item ? 'Simpan perubahan' : 'Simpan role');
        if (item) $form.find('[name="name"]').val(item.name || '');
        loadPermissions(item && Array.isArray(item.permissions) ? item.permissions.map(p => p.name) : []);
        $modal.addClass('is-open').attr('aria-hidden', 'false');
        $('body').addClass('inventory-modal-open');
        $form.find('[name="name"]').trigger('focus');
    }

    $('#role-create').on('click', function () { openModal(null); });
    $('#role-edit').on('click', function () { if (detailItem) openModal(detailItem); });
    $('[data-close-role-modal]').on('click', closeModal);

    $form.on('submit', function (event) {
        event.preventDefault();
        if (!window.AppCan('role.' + (editingId ? 'update' : 'store'))) return;
        if (!this.reportValidity() || !Array.isArray(allPermissions)) return;
        const wasEditing = Boolean(editingId);
        const $button = $('#role-save').prop('disabled', true);
        $('#role-form-error').text('').prop('hidden', true);
        $.ajax({
            ...writeOptions(),
            url: apiUrl + (wasEditing ? '/' + encodeURIComponent(editingId) : ''),
            method: wasEditing ? 'PUT' : 'POST',
            data: JSON.stringify({
                name: $form.find('[name="name"]').val().trim(),
                permissions: $('#role-permission-groups input:checked').map(function () { return this.value; }).get(),
            }),
        }).done(function (item) {
            closeModal();
            toast.fire({ icon: 'success', title: wasEditing ? 'Role berhasil diperbarui.' : 'Role berhasil ditambahkan.' });
            if (isDetail) loadDetail();
            else if (item && item.id) window.location.assign($page.attr('data-detail-base-url') + '/' + encodeURIComponent(item.id));
            else loadList(1);
        }).fail(function (xhr) {
            $('#role-form-error').text(apiError(xhr)).prop('hidden', false);
        }).always(function () {
            $button.prop('disabled', !Array.isArray(allPermissions));
        });
    });

    function showListMessage(message) {
        $('#role-table-body').empty().append($('<tr>').append(
            $('<td>').attr('colspan', 4).addClass('master-table-message').text(message)
        ));
    }

    function renderRow(item) {
        const count = Array.isArray(item.permissions) ? item.permissions.length : 0;
        return $('<tr>')
            .append($('<td>').addClass('role-name-cell')
                .append($('<span>').addClass('role-table-icon').append($('<i>').addClass('fas fa-user-shield').attr('aria-hidden', 'true')))
                .append($('<strong>').text(item.name || '—')))
            .append($('<td>').text(item.users_count ?? '—'))
            .append($('<td>').append($('<span>').addClass('master-badge').text(count + ' izin')))
            .append($('<td>').addClass('inventory-action-cell').append(window.AppCan('role.show') ?
                $('<a>', {
                    href: $page.attr('data-detail-base-url') + '/' + encodeURIComponent(item.id),
                    title: 'Detail role', 'aria-label': 'Detail role ' + (item.name || ''),
                }).addClass('inventory-detail-button').append(
                    $('<i>').addClass('fas fa-eye').attr('aria-hidden', 'true')
                ) : null
            ));
    }

    function loadList(targetPage) {
        if (pendingList) pendingList.abort();
        showListMessage('Memuat role dari server...');
        $('#role-summary').text('Memuat data...');
        const request = $.ajax({
            ...requestOptions(), url: apiUrl, method: 'GET',
            data: { page: targetPage, size: $('#role-page-size').val(), search: $('#role-search').val().trim() },
        }).done(function (response) {
            if (!response || !Array.isArray(response.data)) {
                showListMessage('Format data server tidak sesuai.');
                return;
            }
            if (!response.data.length && targetPage > 1) {
                setTimeout(function () { loadList(targetPage - 1); }, 0);
                return;
            }
            const $body = $('#role-table-body').empty();
            response.data.forEach(function (item) { $body.append(renderRow(item)); });
            if (!response.data.length) showListMessage('Belum ada role yang cocok.');
            pageNumber = Number(response.current_page) || targetPage;
            hasNextPage = Boolean(response.next_page_url);
            $('#role-page-number').text(pageNumber);
            $('#role-prev').prop('disabled', !response.prev_page_url);
            $('#role-next').prop('disabled', !hasNextPage);
            $('#role-caption').text(response.data.length ? 'Peran dan jumlah izin yang tersedia' : 'Belum ada data yang cocok');
            $('#role-summary').text(response.data.length ?
                'Menampilkan ' + (response.from || 1) + '–' + (response.to || response.data.length) + ' role' :
                'Tidak ada role');
        }).fail(function (xhr, status) {
            if (status === 'abort') return;
            showListMessage(apiError(xhr));
            $('#role-summary').text('Gagal memuat data');
            $('#role-prev, #role-next').prop('disabled', true);
        }).always(function () { if (pendingList === request) pendingList = null; });
        pendingList = request;
    }

    function renderDetailPermissions(items) {
        const $container = $('#role-detail-permissions').empty();
        const groups = groupPermissions(items);
        Object.entries(groups).forEach(function ([resource, permissions]) {
            const $group = $('<section>').addClass('role-detail-group');
            $group.append($('<h4>').text(groupTitle(resource)));
            const $items = $('<div>').addClass('role-detail-items');
            permissions.forEach(function (permission) {
                $items.append($('<span>').addClass('role-detail-badge')
                    .append($('<i>').addClass('fas fa-check').attr('aria-hidden', 'true'))
                    .append($('<span>').text(actionTitle(permission.action)))
                    .attr('title', permission.name));
            });
            $container.append($group.append($items));
        });
        if (!Object.keys(groups).length) {
            $container.append($('<p>').addClass('role-permission-message').text('Belum ada izin pada role ini.'));
        }
        $('#role-permission-count').text(items.length + ' izin diberikan');
    }

    function loadDetail() {
        detailItem = null;
        $('#role-edit, #role-delete').prop('disabled', true);
        $('#role-detail-error').text('').prop('hidden', true);
        $.ajax({
            ...requestOptions(), url: apiUrl + '/' + encodeURIComponent($page.attr('data-role-id')),
            method: 'GET',
        }).done(function (item) {
            detailItem = item;
            $('#role-detail-title').text(item.name || 'Role');
            $('#role-detail-subtitle').text('Kelompok akses sistem');
            renderDetailPermissions(Array.isArray(item.permissions) ? item.permissions : []);
            $('#role-edit, #role-delete').prop('disabled', false);
        }).fail(function (xhr) {
            $('#role-detail-title').text('Detail tidak tersedia');
            $('#role-detail-error').text(apiError(xhr)).prop('hidden', false);
        });
    }

    $('#role-delete').on('click', function () {
        if (!window.AppCan('role.destroy')) return;
        if (!detailItem) return;
        Swal.fire({
            icon: 'warning', title: 'Hapus role?',
            text: 'Role ' + (detailItem.name || '') + ' akan dihapus.',
            showCancelButton: true, confirmButtonText: 'Ya, hapus', cancelButtonText: 'Batal',
            reverseButtons: true, customClass: swalStyle, buttonsStyling: false,
            allowOutsideClick: false, allowEscapeKey: false,
        }).then(function (result) {
            if (!result.isConfirmed) return;
            $('#role-delete').prop('disabled', true);
            $.ajax({ ...writeOptions(), url: apiUrl + '/' + encodeURIComponent(detailItem.id), method: 'DELETE' })
                .done(function () { window.location.assign($page.attr('data-index-url')); })
                .fail(function (xhr) {
                    $('#role-delete').prop('disabled', false);
                    Swal.fire({
                        icon: 'error', title: 'Gagal menghapus', text: apiError(xhr),
                        confirmButtonText: 'Tutup', customClass: swalStyle,
                        buttonsStyling: false, allowOutsideClick: false, allowEscapeKey: false,
                    });
                });
        });
    });

    function initPageSizeSelect() {
        const $select = $('#role-page-size');
        const $wrap = $select.closest('.master-select-wrap');
        const $button = $('<button>', {
            type: 'button', 'aria-label': 'Jumlah per halaman',
            'aria-haspopup': 'listbox', 'aria-expanded': 'false',
        }).addClass('master-select-trigger')
            .append($('<span>').addClass('master-select-text'))
            .append($('<i>').addClass('fas fa-chevron-down').attr('aria-hidden', 'true'));
        const $menu = $('<div>', { role: 'listbox', 'aria-label': 'Jumlah per halaman' }).addClass('master-select-menu');
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

        function close() { $wrap.removeClass('is-open'); $button.attr('aria-expanded', 'false'); }
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
            refresh(); close(); $button.trigger('focus');
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

    if (isDetail) {
        loadDetail();
    } else {
        initPageSizeSelect();
        $('#role-search').on('input', function () {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(function () { loadList(1); }, 350);
        });
        $('#role-page-size').on('change', function () { loadList(1); });
        $('#role-prev').on('click', function () { if (pageNumber > 1) loadList(pageNumber - 1); });
        $('#role-next').on('click', function () { if (hasNextPage) loadList(pageNumber + 1); });
        loadList(1);
    }
})(jQuery);
