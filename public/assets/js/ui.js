(function ($) {
    'use strict';

    const permissions = new Set(window.AppPermissions || []);
    window.AppCan = function (permission) { return permissions.has(permission); };
    $('[data-permission]').each(function () {
        const required = String($(this).attr('data-permission')).split(',').map(value => value.trim());
        if (required.every(window.AppCan)) $(this).addClass('permission-allowed');
        else $(this).remove();
    });

    const Toast = typeof Swal !== 'undefined' ? Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 4200,
        timerProgressBar: true,
        customClass: {
            popup: 'modern-toast',
            title: 'modern-toast-title',
        },
    }) : null;

    $('.master-table-scroll, .overflow-x-auto').each(function () {
        if (!$(this).find('table').length) return;
        if (!this.hasAttribute('tabindex')) this.setAttribute('tabindex', '0');
        if (!this.hasAttribute('aria-label')) this.setAttribute('aria-label', 'Gulir tabel');
    });

    $(document).on('input', '[data-table-search]', function () {
        const query = $(this).val().trim().toLocaleLowerCase('id');
        const table = document.getElementById($(this).attr('data-table-search'));

        if (!table) {
            return;
        }

        $(table).find('tbody tr').each(function () {
            $(this).toggle($(this).text().toLocaleLowerCase('id').includes(query));
        });
    });

    $(document).on('click', '[data-demo-action]', function () {
        if (Toast) {
            Toast.fire({
                icon: 'info',
                title: $(this).attr('data-demo-action'),
            });
        }
    });

})(jQuery);
