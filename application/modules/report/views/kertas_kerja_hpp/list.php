<?php if ( !empty($data) && count($data) > 0 ): ?>
	<?php 
        $kode_kas = null; 
        $idx_kas = 0;
        $saldo = 0;

        $saldo_kas = 0;

        $tot_debet_kas = 0;
        $tot_kredit_kas = 0;

        $gt_debet = 0;
        $gt_kredit = 0;
        $gt_saldo = 0;
    ?>
    <?php foreach ($data as $key => $value) { ?>
        <?php if ( $kode_kas <> $value['kas'] ) { ?>
            <tr class="abu">
                <td colspan="10">
                    <div class="col-xs-12 no-padding">
                        <div class="col-xs-1 no-padding"><label class="label-control">Bank</label></div>
                        <div class="col-xs-1 no-padding" style="max-width: 1%;"><label class="label-control">:</label></div>
                        <div class="col-xs-10 no-padding"><label class="label-control"><?php echo strtoupper($value['kas'].' | '.$value['nama_kas']); ?></label></div>
                    </div>
                </td>
            </tr>
            <tr>
				<td class="col-xs-1 text-center"><b>Tanggal</b></td>
				<td class="col-xs-1 text-center"><b>No</b></td>
				<td class="col-xs-5 text-center"><b>Keterangan</b></td>
				<td class="col-xs-1 text-center"><b>Masuk</b></td>
				<td class="col-xs-1 text-center"><b>Keluar</b></td>
				<td class="col-xs-2 text-center"><b>Saldo</b></td>
			</tr>
            <?php 
                $idx_kas = 0;
                $saldo = 0;
                $kode_kas = $value['kas'];
                
                $tot_debet_kas = 0;
                $tot_kredit_kas = 0;
            ?>
        <?php } ?>

        <?php 
            $tanggal = !empty($value['tanggal']) ? (($value['tanggal'] < '2000-01-01') ? null : $value['tanggal']) : null;
            $kode_trans = $value['kode'];
            $keterangan = $value['keterangan'];

            $debet = $value['debet'];
            $kredit = $value['kredit'];
            $saldo = ($saldo+$debet)-$kredit;

            $tot_debet_kas += $debet;
            $tot_kredit_kas += $kredit;

            $gt_debet += $debet;
            $gt_kredit += $kredit;
        ?>
        <?php if ( $idx_kas == 0 ) { ?>
            <?php if ( stristr($value['keterangan'], 'saldo awal') === false ) { ?>
                <tr>
                    <td></td>
                    <td></td>
                    <td><?php echo 'Saldo Awal'; ?></td>
                    <td class="text-right"><?php echo angkaDecimal(0); ?></td>
                    <td class="text-right"><?php echo angkaDecimal(0); ?></td>
                    <td class="text-right"><?php echo angkaDecimal(0); ?></td>
                </tr>
            <?php } ?>
        <?php } ?>
        <tr>
            <td><?php echo !empty($tanggal) ? tglIndonesia($tanggal, '-', ' ') : ''; ?></td>
            <td><?php echo !empty($kode_trans) ? $kode_trans : ''; ?></td>
            <td><?php echo $keterangan; ?></td>
            <td class="text-right"><?php echo angkaDecimal($debet); ?></td>
            <td class="text-right"><?php echo angkaDecimal($kredit); ?></td>
            <td class="text-right"><?php echo ($saldo >= 0) ? angkaDecimal($saldo) : '('.angkaDecimal(abs($saldo)).')'; ?></td>
        </tr>
        <?php if ( !empty($kode_kas) && $kode_kas <> $data[$key+1]['kas'] ) { ?>
            <?php // $gt_saldo += $saldo_gdg; $gt_jml_saldo += $jml_saldo_gdg; ?>
            <tr class="biru">
                <td colspan="3"><b>Saldo</b></td>
                <td class="text-right"><b><?php echo ($gt_debet >= 0) ? angkaDecimal($gt_debet) : '('.angkaDecimal(abs($gt_debet)).')'; ?></b></td>
                <td class="text-right"><b><?php echo ($gt_kredit >= 0) ? angkaDecimal($gt_kredit) : '('.angkaDecimal(abs($gt_kredit)).')'; ?></b></td>
                <td class="text-right"><b><?php echo ($saldo >= 0) ? angkaDecimal($saldo) : '('.angkaDecimal(abs($saldo)).')'; ?></b></td>
            </tr>
            <tr>
                <td colspan="10"></td>
            </tr>
        <?php } ?>
        <?php $idx_kas++; ?>
    <?php } ?>
<?php else: ?>
	<tr>
		<td colspan="6">Data tidak ditemukan.</td>
	</tr>
<?php endif ?>