<?php
    // helper tampilan (guarded: nama sama dipakai view lain dalam modul ini)
    if (!function_exists('_v')) {
        function _v($val) {
            return ($val === null || $val === '') ? '-' : $val;
        }
    }
    // angka ribuan: 12000 -> 12.000
    if (!function_exists('_num')) {
        function _num($val) {
            return ($val === null || $val === '') ? '-' : number_format((float)$val, 0, ',', '.');
        }
    }
    // angka metrik ala Indonesia: ribuan '.', desimal ',' ; $dec = jumlah desimal
    if (!function_exists('_ang')) {
        function _ang($val, $dec = 0) {
            if ($val === null || $val === '' || !is_numeric($val)) return '-';
            return number_format((float)$val, $dec, ',', '.');
        }
    }
    // tanggal Indonesia: 2026-06-29 -> 29 Juni 2026
    if (!function_exists('_tgl')) {
        function _tgl($val) {
            if ($val === null || $val === '') return '-';
            $ts = strtotime(substr($val, 0, 10));
            if ($ts === false) return $val;
            $bulan = array(1=>'Januari','Februari','Maret','April','Mei','Juni',
                           'Juli','Agustus','September','Oktober','November','Desember');
            return date('j', $ts) . ' ' . $bulan[(int)date('n', $ts)] . ' ' . date('Y', $ts);
        }
    }
    // angka minus -> dalam kurung: "-12,5" jadi "(12,5)". '-' tunggal (kosong) dibiarkan.
    if (!function_exists('_minParen')) {
        function _minParen($str) {
            return (is_string($str) && strlen($str) > 1 && $str[0] === '-')
                ? '(' . substr($str, 1) . ')'
                : $str;
        }
    }
    // standar comparison: merah jika performa kurang, biru jika lebih
    if (!function_exists('_stdCls')) {
        function _stdCls($actual, $std, $higherIsBetter = true) {
            if ($actual === null || $std === null || $actual === '' || $std === '') return '';
            $a = (float)$actual;
            $s = (float)$std;
            if ($higherIsBetter) {
                if ($a < $s) return 'std-below';
                if ($a > $s) return 'std-above';
            } else {
                if ($a > $s) return 'std-below';
                if ($a < $s) return 'std-above';
            }
            return '';
        }
    }
?>
<?php if (!empty($data)) : ?>
    <?php foreach ($data as $farm) : ?>

        <div class="farm-title"><?php echo $farm['nama']; ?></div>

        <?php foreach ($farm['kandang'] as $k) : ?>
            <?php $has_detail = !empty($k['noreg']); ?>
            <?php if ($has_detail) : ?>
            <a class="kdg-card-link" href="<?php echo base_url('report/RiwayatPerformanceInternal/detail/' . $k['db'] . '/' . $k['mitra'] . '/' . $k['no_kandang']) . '?tgl=' . urlencode($tgl); ?>">
            <?php endif; ?>
            <div class="kdg-card">
                <div class="kdg-label"><?php echo _v($k['label']); ?><?php if ($has_detail) : ?><span class="kdg-chevron">&rsaquo;</span><?php endif; ?></div>

                <div class="kdg-info">
                    <div><span>Populasi</span><b><?php echo _num($k['populasi']); ?></b></div>
                    <div><span>Tgl Chick In</span><b><?php echo _tgl($k['tgl_chick_in']); ?></b></div>
                    <div><span>PPL</span><b><?php echo _v($k['ppl']); ?></b></div>
                    <div><span>Supervisor</span><b><?php echo _v($k['supervisor']); ?></b></div>
                </div>

                <div class="kdg-metrics">
                    <div class="metric"><span>Umur</span><b><?php echo _ang($k['umur']); ?></b></div>
                    <div class="metric"><span>BW</span><b class="<?php echo _stdCls($k['bw'], $k['std_bw']); ?>"><?php echo _minParen(angkaDecimalFormat($k['bw'], 3)); ?></b></div>
                    <div class="metric"><span>DG</span><b class="<?php echo _stdCls($k['dg'], $k['std_dg']); ?>"><?php echo _minParen(angkaDecimal($k['dg'])); ?></b></div>
                    <div class="metric"><span>FCR</span><b class="<?php echo _stdCls($k['fcr'], $k['std_fcr'], false); ?>"><?php echo _minParen(angkaDecimalFormat($k['fcr'], 3)); ?></b></div>
                    <div class="metric"><span>Ekor Mati</span><b><?php echo _minParen(_ang($k['ekor_mati'])); ?></b></div>
                    <div class="metric"><span>IP</span><b class="<?php echo _stdCls($k['ip'], $k['std_ip']); ?>"><?php echo _minParen(angkaDecimalFormat($k['ip'], 2)); ?></b></div>
                </div>

                <div class="kdg-ket">
                    <div class="kdg-ket-label">Keterangan</div>
                    <div class="kdg-ket-text"><?php echo ($k['keterangan'] === null || $k['keterangan'] === '') ? '-' : htmlspecialchars($k['keterangan']); ?></div>
                </div>
            </div>
            <?php if ($has_detail) : ?>
            </a>
            <?php endif; ?>
        <?php endforeach; ?>

    <?php endforeach; ?>
<?php else : ?>
    <p>Tidak ada data farm.</p>
<?php endif; ?>
