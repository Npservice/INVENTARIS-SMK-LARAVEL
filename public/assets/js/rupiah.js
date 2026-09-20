(function ($) {
    'use strict';

    function grouped(digits) {
        return digits.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    function decimalParts(value) {
        if (value === null || value === undefined || value === '') return null;
        const match = String(value).trim().match(/^(\d+)(?:\.(\d{1,2}))?$/);
        if (!match) return null;
        return {
            integer: match[1].replace(/^0+(?=\d)/, ''),
            fraction: (match[2] || '').padEnd(2, '0'),
        };
    }

    function format(value) {
        const parts = decimalParts(value);
        if (!parts) return '—';
        return 'Rp ' + grouped(parts.integer) +
            (parts.fraction === '00' ? '' : ',' + parts.fraction);
    }

    function set(input, value) {
        const $input = $(input);
        const $raw = $($input.attr('data-rupiah-target'));
        const parts = decimalParts(value);
        $raw.val(parts ? parts.integer + '.' + parts.fraction : '');
        $input.val(parts ? format(parts.integer + '.' + parts.fraction) : '');
    }

    function parseTyped(value) {
        const cleaned = String(value).replace(/^\s*Rp\s*/i, '').replace(/[^\d,]/g, '');
        const comma = cleaned.indexOf(',');
        const integer = (comma < 0 ? cleaned : cleaned.slice(0, comma))
            .replace(/\D/g, '').replace(/^0+(?=\d)/, '');
        const fraction = comma < 0 ? '' : cleaned.slice(comma + 1).replace(/\D/g, '').slice(0, 2);
        if (!integer && !fraction) return { display: '', raw: '' };
        return {
            display: 'Rp ' + grouped(integer || '0') + (comma < 0 ? '' : ',' + fraction),
            raw: (integer || '0') + '.' + fraction.padEnd(2, '0'),
        };
    }

    $(document).on('input', '[data-rupiah-input]', function () {
        const before = this.value.slice(0, this.selectionStart || 0).replace(/[\d,]/g, '').length;
        const tokensBefore = (this.value.slice(0, this.selectionStart || 0).match(/[\d,]/g) || []).length;
        const parsed = parseTyped(this.value);
        this.value = parsed.display;
        $(this.getAttribute('data-rupiah-target')).val(parsed.raw);
        if (!parsed.display) return;
        let tokens = 0;
        let position = parsed.display.length;
        for (let i = 0; i < parsed.display.length; i++) {
            if (/[\d,]/.test(parsed.display[i])) tokens++;
            if (tokens === tokensBefore) { position = i + 1; break; }
        }
        if (tokensBefore === 0) position = Math.min(3, parsed.display.length);
        if (before && position < 3) position = 3;
        this.setSelectionRange(position, position);
    });

    $(document).on('blur', '[data-rupiah-input]', function () {
        const raw = $(this.getAttribute('data-rupiah-target')).val();
        this.value = raw ? format(raw) : '';
    });

    window.Rupiah = { format: format, set: set };
})(jQuery);
