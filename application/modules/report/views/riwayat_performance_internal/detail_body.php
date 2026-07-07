<?php
    if (!function_exists('_v')) {
        function _v($val) { return ($val === null || $val === '') ? '-' : $val; }
    }
    if (!function_exists('_num')) {
        function _num($val) { return ($val === null || $val === '') ? '-' : number_format((float)$val, 0, ',', '.'); }
    }
    if (!function_exists('_tgl')) {
        function _tgl($val) {
            if ($val === null || $val === '') return '-';
            $ts = strtotime(substr($val, 0, 10));
            if ($ts === false) return $val;
            $b = array(1=>'Januari','Februari','Maret','April','Mei','Juni',
                       'Juli','Agustus','September','Oktober','November','Desember');
            return date('j', $ts) . ' ' . $b[(int)date('n', $ts)] . ' ' . date('Y', $ts);
        }
    }
    if (!function_exists('_minParen')) {
        function _minParen($str) {
            // angka minus -> dalam kurung: "-12,5" jadi "(12,5)". '-' tunggal (kosong) dibiarkan.
            return (is_string($str) && strlen($str) > 1 && $str[0] === '-')
                ? '(' . substr($str, 1) . ')'
                : $str;
        }
    }
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
<style>
    .d-content { max-width: 620px; margin: 0 auto; padding: 12px; animation: d-fade .22s ease; }
    @keyframes d-fade { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: none; } }
    .d-content .head-card { background: #fff; border-radius: 12px; box-shadow: 0 1px 6px rgba(0,0,0,.09); padding: 14px 16px; margin-bottom: 14px; }
    .d-content .hc-title { font-weight: 700; font-size: 15px; color: #1f6f43; padding-bottom: 9px; margin-bottom: 12px; border-bottom: 1px solid #edf0f3; }
    .d-content .hc-info { display: grid; grid-template-columns: 1fr 1fr; gap: 11px 14px; }
    .d-content .hc-info > div { display: flex; flex-direction: column; }
    .d-content .hc-info span { font-size: 10px; color: #93a0ae; text-transform: uppercase; letter-spacing: .4px; margin-bottom: 3px; }
    .d-content .hc-info b { font-size: 13.5px; font-weight: 600; color: #23303d; }

    .d-content .list-title { font-weight: 700; font-size: 12px; text-transform: uppercase; letter-spacing: .3px; color: #4a5a6a; margin: 6px 2px 10px; }

    .d-content .lhk-item { background: #fff; border-radius: 10px; box-shadow: 0 1px 5px rgba(0,0,0,.08); padding: 12px 14px; margin-bottom: 10px; }
    .d-content .lhk-head { display: flex; justify-content: space-between; align-items: baseline; padding-bottom: 9px; margin-bottom: 11px; border-bottom: 1px solid #edf0f3; }
    .d-content .lhk-umur { font-size: 11px; color: #7a8794; text-transform: uppercase; letter-spacing: .3px; }
    .d-content .lhk-umur b { font-size: 16px; color: #1f6f43; margin-left: 5px; }
    .d-content .lhk-tgl { font-size: 12.5px; font-weight: 600; color: #23303d; }

    .d-content .lhk-metrics { display: flex; background: #f5f8fa; border-radius: 8px; overflow: hidden; }
    .d-content .lhk-metrics .m { flex: 1; text-align: center; padding: 8px 2px; }
    .d-content .lhk-metrics .m + .m { box-shadow: -1px 0 0 #e6ecf0; }
    .d-content .lhk-metrics .m span {
        display: flex; align-items: center; justify-content: center;
        min-height: 22px; font-size: 10px; line-height: 1.15;
        color: #93a0ae; text-transform: uppercase; letter-spacing: .2px;
    }
    .d-content .lhk-metrics .m b { display: block; font-size: 15px; font-weight: 700; margin-top: 3px; color: #16202b; }

    .d-content .lhk-ket { margin-top: 11px; }
    .d-content .lhk-ket > span { display: block; font-size: 10px; color: #93a0ae; text-transform: uppercase; letter-spacing: .3px; margin-bottom: 4px; }
    .d-content .lhk-ket .txt {
        font-size: 12.5px; color: #2c3a48; line-height: 1.5;
        white-space: pre-wrap; word-break: break-word;
        background: #f9fafb; border-radius: 6px; padding: 8px 10px;
    }

    .d-content .empty { background: #fff; border-radius: 10px; box-shadow: 0 1px 5px rgba(0,0,0,.08); padding: 20px; text-align: center; color: #93a0ae; font-style: italic; }
    .lhk-metrics .m b.std-below { color: #dc3545; }
    .lhk-metrics .m b.std-above { color: #0d6efd; }

    .d-content .legend {
        display: flex; flex-wrap: wrap; gap: 7px 16px;
        background: #fff; border-radius: 10px; box-shadow: 0 1px 5px rgba(0,0,0,.08);
        padding: 10px 14px; margin-bottom: 14px; font-size: 11.5px; color: #4a5a6a;
    }
    .d-content .legend .lg { display: flex; align-items: center; gap: 6px; }
    .d-content .legend .dot { width: 11px; height: 11px; border-radius: 50%; flex: 0 0 auto; }
    .d-content .legend .dot.red { background: #dc3545; }
    .d-content .legend .dot.blue { background: #0d6efd; }
    .d-content .legend .dot.black { background: #16202b; }
</style>
<div class="d-content">
    <div class="head-card">
        <div class="hc-title"><?php echo _v($label); ?></div>
        <div class="hc-info">
            <div><span>Noreg</span><b><?php echo _v($info['noreg']); ?></b></div>
            <div><span>Populasi</span><b><?php echo _num($info['populasi']); ?></b></div>
            <div><span>Tgl Chick In</span><b><?php echo _tgl($info['tgl_chick_in']); ?></b></div>
            <div><span>PPL</span><b><?php echo _v($info['ppl']); ?></b></div>
            <div><span>Supervisor</span><b><?php echo _v($info['supervisor']); ?></b></div>
            <div><span>Data s/d</span><b><?php echo isset($tgl) ? _tgl($tgl) : '-'; ?></b></div>
        </div>
    </div>

    <div class="legend">
        <div class="lg"><span class="dot red"></span>Di bawah standar</div>
        <div class="lg"><span class="dot blue"></span>Di atas standar</div>
        <div class="lg"><span class="dot black"></span>Sesuai / tanpa standar</div>
    </div>

    <div class="list-title">Riwayat LHK</div>

    <?php if (!empty($lhk)) : ?>
        <?php foreach ($lhk as $l) : ?>
            <div class="lhk-item">
                <div class="lhk-head">
                    <div class="lhk-umur">Umur<b><?php echo _v($l['umur']); ?></b></div>
                    <div class="lhk-tgl"><?php echo _tgl($l['tanggal']); ?></div>
                </div>

                <div class="lhk-metrics">
                    <div class="m"><span>BW</span><b class="<?php echo _stdCls($l['bb'], $l['std_bw']); ?>"><?php echo _minParen(angkaDecimalFormat($l['bb'], 3)); ?></b></div>
                    <div class="m"><span>DG</span><b class="<?php echo _stdCls($l['dg'], $l['std_dg']); ?>"><?php echo ($l['dg'] === null) ? '-' : _minParen(angkaDecimalFormat($l['dg'], 3)); ?></b></div>
                    <div class="m"><span>FCR</span><b class="<?php echo _stdCls($l['fcr'], $l['std_fcr'], false); ?>"><?php echo _minParen(angkaDecimalFormat($l['fcr'], 3)); ?></b></div>
                    <div class="m"><span>IP</span><b class="<?php echo _stdCls($l['ip'], $l['std_ip']); ?>"><?php echo _minParen(angkaDecimalFormat($l['ip'], 2)); ?></b></div>
                    <div class="m"><span>Ekor Mati</span><b><?php echo _minParen(_v($l['ekor_mati'])); ?></b></div>
                </div>

                <div class="lhk-ket">
                    <span>Keterangan</span>
                    <div class="txt"><?php echo ($l['keterangan'] === null || $l['keterangan'] === '') ? '-' : htmlspecialchars($l['keterangan']); ?></div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else : ?>
        <div class="empty">Belum ada data LHK untuk noreg ini.</div>
    <?php endif; ?>
</div>
