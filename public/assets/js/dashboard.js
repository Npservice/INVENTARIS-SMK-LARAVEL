(function ($) {
    'use strict';

    const $page = $('#dashboard-page');
    if (!$page.length) return;

    const formatNumber = new Intl.NumberFormat('id-ID');

    function showLoanMessage(message) {
        $('#dashboard-loan-body').empty().append($('<tr>').append(
            $('<td>').attr('colspan', 4).addClass('master-table-message').text(message)
        ));
    }

    function renderLoan(item) {
        const status = item.status || '—';
        return $('<tr>')
            .append($('<td>').addClass('inventory-name-cell')
                .append($('<strong>').text(item.barang || 'Barang tidak tersedia')))
            .append($('<td>').text(item.peminjam || '—'))
            .append($('<td>').text(item.tanggal || '—'))
            .append($('<td>').append($('<span>').addClass('master-badge ' +
                (status === 'Kembali' ? 'loan-status-returned' : 'loan-status-active')).text(status)));
    }

    $.ajax({
        url: $page.attr('data-dashboard-url'),
        method: 'GET',
        dataType: 'json',
        headers: { Accept: 'application/json' },
    }).done(function (response) {
        if (!response || !response.stats || !Array.isArray(response.peminjaman_terbaru)) {
            showLoanMessage('Format ringkasan server tidak sesuai.');
            $('#dashboard-loan-caption').text('Ringkasan tidak tersedia');
            return;
        }

        const stats = response.stats;
        $('#dashboard-inventory-count').text(formatNumber.format(Number(stats.total_inventaris) || 0));
        $('#dashboard-warehouse-stock').text(formatNumber.format(Number(stats.stok_gudang) || 0));
        $('#dashboard-active-loans').text(formatNumber.format(Number(stats.peminjaman_aktif) || 0));
        $('#dashboard-damaged-count, #dashboard-attention-count')
            .text(formatNumber.format(Number(stats.perlu_perawatan) || 0));

        const $body = $('#dashboard-loan-body').empty();
        response.peminjaman_terbaru.forEach(function (item) { $body.append(renderLoan(item)); });
        if (!response.peminjaman_terbaru.length) showLoanMessage('Belum ada peminjaman tercatat.');
        $('#dashboard-loan-caption').text(response.peminjaman_terbaru.length ?
            'Aktivitas peminjaman yang paling baru' : 'Belum ada aktivitas peminjaman');
    }).fail(function (xhr) {
        const message = xhr.status === 403 ? 'Anda tidak memiliki izin melihat ringkasan.' :
            (xhr.responseJSON && xhr.responseJSON.message) || 'Ringkasan tidak dapat dimuat.';
        showLoanMessage(message);
        $('#dashboard-loan-caption').text('Gagal memuat ringkasan');
    });
})(jQuery);
