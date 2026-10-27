(function () {
    'use strict';

    var rupiah = function (n) {
        return 'Rp ' + Math.round(n).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    };

    // ---- Tema gelap / terang ----
    var root = document.documentElement;
    document.querySelectorAll('#themeBtn, [data-theme-toggle]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var next = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
            root.setAttribute('data-theme', next);
            try { localStorage.setItem('theme', next); } catch (e) {}
        });
    });

    // ---- Menu sidebar di HP ----
    var menuBtn = document.getElementById('menuBtn');
    var scrim = document.getElementById('scrim');
    if (menuBtn) menuBtn.addEventListener('click', function () { document.body.classList.toggle('nav-open'); });
    if (scrim) scrim.addEventListener('click', function () { document.body.classList.remove('nav-open'); });

    // ---- Alert: tombol tutup + hilang otomatis ----
    document.querySelectorAll('.alert').forEach(function (el) {
        var close = el.querySelector('.close');
        var hide = function () {
            el.style.transition = 'opacity .3s, transform .3s';
            el.style.opacity = '0';
            el.style.transform = 'translateY(-6px)';
            setTimeout(function () { el.remove(); }, 320);
        };
        if (close) close.addEventListener('click', hide);
        if (el.hasAttribute('data-autoclose')) setTimeout(hide, 5000);
    });

    // ---- Hitung mundur angka pada kartu statistik ----
    document.querySelectorAll('[data-count]').forEach(function (el) {
        var target = parseFloat(el.getAttribute('data-count')) || 0;
        var money = el.hasAttribute('data-money');
        var fmt = function (v) { return money ? rupiah(v) : Math.round(v).toLocaleString('id-ID'); };
        if (target === 0 || window.matchMedia('(prefers-reduced-motion: reduce)').matches) { el.textContent = fmt(target); return; }
        var start = null, dur = 900;
        function step(ts) {
            if (start === null) start = ts;
            var p = Math.min((ts - start) / dur, 1);
            var eased = 1 - Math.pow(1 - p, 3);
            el.textContent = fmt(target * eased);
            if (p < 1) requestAnimationFrame(step);
        }
        requestAnimationFrame(step);
    });

    // ---- Total otomatis pada form penjualan / pembelian ----
    var lineForm = document.querySelector('[data-line-form]');
    if (lineForm) {
        var mode = lineForm.getAttribute('data-line-form'); // "sale" | "purchase"
        var rows = lineForm.querySelectorAll('tbody tr');
        var totalEl = lineForm.querySelector('[data-live-total]');

        var recalc = function () {
            var total = 0;
            rows.forEach(function (row) {
                var sel = row.querySelector('select');
                var qty = row.querySelector('input[data-qty]');
                var cost = row.querySelector('input[data-cost]');
                var sub = row.querySelector('[data-sub]');
                var opt = sel.options[sel.selectedIndex];
                var q = parseFloat(qty.value) || 0;
                var unit = 0;
                if (mode === 'sale') unit = parseFloat(opt && opt.getAttribute('data-price')) || 0;
                else unit = parseFloat(cost.value) || 0;
                var s = sel.value ? q * unit : 0;
                total += s;
                sub.textContent = s > 0 ? rupiah(s) : '-';
            });
            if (totalEl) totalEl.textContent = rupiah(total);
        };

        rows.forEach(function (row) {
            var sel = row.querySelector('select');
            var cost = row.querySelector('input[data-cost]');
            sel.addEventListener('change', function () {
                // Pembelian: isi otomatis harga beli dari data produk bila masih kosong
                if (mode === 'purchase' && cost && !cost.value && sel.value) {
                    var opt = sel.options[sel.selectedIndex];
                    cost.value = opt.getAttribute('data-cost') || '';
                }
                recalc();
            });
            row.querySelectorAll('input').forEach(function (i) { i.addEventListener('input', recalc); });
        });
        recalc();
    }
})();
