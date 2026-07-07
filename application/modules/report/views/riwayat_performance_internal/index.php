<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Report Performa Internal</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            background: #f4f6f9;
            color: #1c2733;
            font-size: 13px;
        }
        .topbar {
            background: #ff9d00;      /* orange sesuai form login (.btn-theme) */
            color: #3a2a00;
            padding: 0 16px;
            height: 52px;
            font-size: 16px;
            font-weight: 700;
            position: sticky;
            top: 0;
            z-index: 3;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }
        .topbar .tb-title { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .topbar form { display: flex; align-items: center; gap: 6px; flex: 0 0 auto; margin: 0; }
        .topbar form label { font-size: 12px; font-weight: 600; }
        .topbar input[type=date] {
            font-family: inherit; font-size: 13px; padding: 5px 8px;
            border: none; border-radius: 6px; color: #1c2733; background: #fff;
        }
        .wrap { max-width: 620px; margin: 0 auto; padding: 12px; }

        .farm-title {
            font-weight: 700;
            font-size: 14px;
            margin: 14px 0 6px;
            padding: 6px 0 4px;
            border-bottom: 2px solid #1f6f43;
            text-transform: uppercase;
            position: sticky;
            top: 52px;            /* tepat di bawah topbar */
            background: #f4f6f9;
            z-index: 2;
        }

        .kdg-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 1px 6px rgba(0, 0, 0, .09);
            padding: 14px 16px;
            margin-bottom: 13px;
        }
        .kdg-label {
            font-weight: 700;
            font-size: 15px;
            color: #1f6f43;
            padding-bottom: 9px;
            margin-bottom: 12px;
            border-bottom: 1px solid #edf0f3;
        }

        /* card sebagai link ke halaman detail */
        a.kdg-card-link { display: block; text-decoration: none; color: inherit; }
        a.kdg-card-link:hover .kdg-card { box-shadow: 0 2px 12px rgba(0, 0, 0, .14); }
        a.kdg-card-link:active .kdg-card { transform: scale(.995); }
        .kdg-chevron { float: right; color: #c4ccd4; font-weight: 700; font-size: 18px; line-height: 1; }

        /* overlay detail (numpuk di atas list) */
        .detail-overlay {
            position: fixed; top: 0; right: 0; bottom: 0; left: 0;
            background: #f4f6f9; z-index: 50;
            display: flex; flex-direction: column;
            transform: translateX(100%);
            transition: transform .22s ease;
            visibility: hidden;
        }
        .detail-overlay.open { transform: translateX(0); visibility: visible; }
        .do-topbar {
            background: #ff9d00; color: #3a2a00; padding: 12px 16px; font-size: 16px; font-weight: 700;
            display: flex; align-items: center; gap: 10px; flex: 0 0 auto;
        }
        .do-back { color: #3a2a00; font-size: 24px; line-height: 1; cursor: pointer; user-select: none; }
        .do-content { flex: 1 1 auto; overflow-y: auto; -webkit-overflow-scrolling: touch; }
        .do-loading { text-align: center; color: #93a0ae; padding: 30px 12px; font-style: italic; }

        /* loading skeleton (shimmer) — dipakai list & overlay detail */
        .sk { max-width: 620px; margin: 0 auto; padding: 12px; }
        .sk-card { background: #fff; border-radius: 12px; box-shadow: 0 1px 6px rgba(0,0,0,.09); padding: 14px 16px; margin-bottom: 12px; }
        .sk-line {
            height: 12px; border-radius: 6px; margin: 9px 0; background: #e9edf1;
            background-image: linear-gradient(90deg, #e9edf1, #f4f7f9, #e9edf1);
            background-size: 600px 100%; background-repeat: no-repeat;
            animation: sk-shimmer 1.2s infinite linear;
        }
        .sk-line.sk-block { height: 46px; }
        .sk-title {
            height: 15px; width: 55%; border-radius: 6px; margin: 18px 0 10px;
            background: #dfe5ec;
            background-image: linear-gradient(90deg, #dfe5ec, #eef2f6, #dfe5ec);
            background-size: 600px 100%; background-repeat: no-repeat;
            animation: sk-shimmer 1.2s infinite linear;
        }
        @keyframes sk-shimmer { 0% { background-position: -300px 0; } 100% { background-position: 300px 0; } }

        .load-error { text-align: center; color: #93a0ae; padding: 26px 12px; }
        .load-error a { color: #1f6f43; font-weight: 600; text-decoration: none; }

        /* info: grid 2 kolom, label mini seragam + nilai tegas */
        .kdg-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 11px 14px;
            padding-bottom: 13px;
            margin-bottom: 13px;
            border-bottom: 1px solid #edf0f3;
        }
        .kdg-info > div {
            display: flex;
            flex-direction: column;
        }
        .kdg-info span {
            font-size: 10px;
            color: #93a0ae;
            text-transform: uppercase;
            letter-spacing: .4px;
            margin-bottom: 3px;
        }
        .kdg-info b {
            font-size: 13.5px;
            font-weight: 600;
            color: #23303d;
        }

        .kdg-metrics {
            display: flex;
            background: #f5f8fa;
            border-radius: 10px;
            overflow: hidden;
        }
        .kdg-metrics .metric {
            flex: 1;
            text-align: center;
            padding: 10px 2px;
        }
        /* pemisah hairline antar-tile (box-shadow: tak menambah lebar) */
        .kdg-metrics .metric + .metric { box-shadow: -1px 0 0 #e6ecf0; }
        .kdg-metrics .metric span {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 22px;          /* muat 2 baris -> angka semua sejajar */
            font-size: 10px;
            line-height: 1.15;
            color: #93a0ae;
            text-transform: uppercase;
            letter-spacing: .2px;
        }
        .kdg-metrics .metric b {
            display: block;
            font-size: 16px;
            font-weight: 700;
            margin-top: 4px;
            color: #16202b;
        }

        .kdg-ket { margin-top: 12px; }
        .kdg-ket-label {
            font-size: 10px;
            color: #8a97a5;
            text-transform: uppercase;
            letter-spacing: .3px;
            margin-bottom: 4px;
        }
        .kdg-ket-text {
            font-size: 12.5px;
            color: #2c3a48;
            line-height: 1.5;
            white-space: pre-wrap;      /* pertahankan enter & spasi sesuai ketikan */
            word-break: break-word;
            background: #f9fafb;
            border-radius: 6px;
            padding: 8px 10px;
        }

        .kdg-metrics .metric b.std-below { color: #dc3545; }   /* merah: performa di bawah standar */
        .kdg-metrics .metric b.std-above { color: #0d6efd; }   /* biru:  performa di atas  standar */

        /* legenda warna metrik */
        .legend {
            display: flex; flex-wrap: wrap; gap: 7px 16px;
            background: #fff; border-radius: 10px;
            box-shadow: 0 1px 6px rgba(0, 0, 0, .09);
            padding: 10px 14px; margin-bottom: 13px;
            font-size: 11.5px; color: #4a5a6a;
        }
        .legend .lg { display: flex; align-items: center; gap: 6px; }
        .legend .dot { width: 11px; height: 11px; border-radius: 50%; flex: 0 0 auto; }
        .legend .dot.red { background: #dc3545; }
        .legend .dot.blue { background: #0d6efd; }
        .legend .dot.black { background: #16202b; }

        .empty-note {
            background: #fff8e1;
            border: 1px solid #f0e0a0;
            color: #8a6d0b;
            padding: 8px 12px;
            border-radius: 6px;
            margin: 10px 0 4px;
            font-size: 12px;
        }
        @media print {
            .topbar, .empty-note { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>
    <div class="topbar">
        <span class="tb-title">Report Performa Internal</span>
        <form method="get" action="<?php echo base_url('report/RiwayatPerformanceInternal'); ?>" onsubmit="return false;">
            <label for="tgl">s/d</label>
            <input type="date" id="tgl" name="tgl" value="<?php echo htmlspecialchars($tgl); ?>" max="<?php echo date('Y-m-d'); ?>" onchange="pfReload(this.value)">
        </form>
    </div>
    <div class="wrap">
        <div class="legend">
            <div class="lg"><span class="dot red"></span>Lebih buruk dari standar</div>
            <div class="lg"><span class="dot blue"></span>Lebih baik dari standar</div>
            <div class="lg"><span class="dot black"></span>Sesuai / tanpa standar</div>
        </div>
        <div id="reportWrap"></div>
    </div>

    <div class="detail-overlay" id="detailOverlay" aria-hidden="true">
        <div class="do-topbar">
            <span class="do-back" onclick="pfClose()">&#8249;</span>
            <span>Detail LHK</span>
        </div>
        <div class="do-content" id="detailContent"></div>
    </div>

    <script>
    (function () {
        var reportWrap = document.getElementById('reportWrap');
        var overlay    = document.getElementById('detailOverlay');
        var content    = document.getElementById('detailContent');
        var isOpen     = false;
        var currentTgl = <?php echo json_encode($tgl); ?>;
        var basePath   = window.location.pathname;   // path controller saat ini

        /* ---------- skeleton ---------- */
        function skCard() {
            return '<div class="sk-card">' +
                '<div class="sk-line" style="width:58%"></div>' +
                '<div class="sk-line" style="width:88%"></div>' +
                '<div class="sk-line sk-block"></div>' +
            '</div>';
        }
        function listSkeleton() {
            var group = '<div class="sk-title"></div>' + skCard() + skCard();
            return group + group + group;
        }
        function detailSkeleton() {
            var item = '<div class="sk-card"><div class="sk-line" style="width:45%"></div><div class="sk-line sk-block"></div></div>';
            return '<div class="sk">' +
                '<div class="sk-card"><div class="sk-line" style="width:55%"></div><div class="sk-line"></div><div class="sk-line" style="width:85%"></div></div>' +
                item + item + item +
            '</div>';
        }

        /* ---------- daftar (list) via AJAX ---------- */
        function bindCardLinks() {
            var links = reportWrap.querySelectorAll('a.kdg-card-link');
            for (var i = 0; i < links.length; i++) {
                links[i].addEventListener('click', function (e) {
                    e.preventDefault();
                    openDetail(this.getAttribute('href'));
                });
            }
        }

        function loadList(tgl) {
            reportWrap.innerHTML = listSkeleton();
            var url = basePath + '?fragment=1&tgl=' + encodeURIComponent(tgl);
            fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(function (r) { return r.text(); })
                .then(function (html) { reportWrap.innerHTML = html; bindCardLinks(); })
                .catch(function () {
                    reportWrap.innerHTML =
                        '<div class="load-error">Gagal memuat data. ' +
                        '<a href="#" onclick="pfRetry();return false;">Coba lagi</a></div>';
                });
        }

        // ganti tanggal: perbarui URL (biar refresh/bookmark tetap), lalu re-fetch tanpa reload
        window.pfReload = function (tgl) {
            if (!tgl) { return; }
            currentTgl = tgl;
            history.replaceState(null, '', basePath + '?tgl=' + encodeURIComponent(tgl));
            loadList(tgl);
        };
        window.pfRetry = function () { loadList(currentTgl); };

        /* ---------- overlay detail ---------- */
        function openDetail(url) {
            content.innerHTML = detailSkeleton();
            overlay.classList.add('open');
            overlay.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
            isOpen = true;

            // dorong 1 history state supaya tombol back HP menutup overlay (bukan reload list)
            history.pushState({ pfDetail: true }, '', url);

            var sep = url.indexOf('?') > -1 ? '&' : '?';
            fetch(url + sep + 'ajax=1', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(function (r) { return r.text(); })
                .then(function (html) { content.innerHTML = html; content.scrollTop = 0; })
                .catch(function () { content.innerHTML = '<div class="do-loading">Gagal memuat data.</div>'; });
        }

        function hideOverlay() {
            overlay.classList.remove('open');
            overlay.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
            isOpen = false;
        }

        // tombol back di dalam overlay -> lewat history supaya konsisten dgn back HP
        window.pfClose = function () {
            if (isOpen) { history.back(); }
        };

        // tombol back HP / browser -> tutup overlay bila terbuka
        window.addEventListener('popstate', function () {
            if (isOpen) { hideOverlay(); }
        });

        /* ---------- init ---------- */
        loadList(currentTgl);
    })();
    </script>
</body>
</html>
