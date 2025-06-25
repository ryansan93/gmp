<!DOCTYPE html>
<html>
<head>
	<style type="text/css">
		.str { mso-number-format:\@; }
		.decimal_number_format { mso-number-format: "\#\,\#\#0.00"; }
		.number_format { mso-number-format: "\#\,\#\#0"; }
		table.bordered thead tr th, table.bordered thead tr td, table.bordered tbody tr th, table.bordered tbody tr td {
			border: 1px solid black;
		}
		.decimal_number_format_bordered { 
			mso-number-format: "\#\,\#\#0.00";
			border: 1px solid black;
		}
	</style>
</head>
<body>
    <div><b>RIWAYAT PERFORMANCE PLASMA</b></div>
    <div><br></div>
	<div>
        <table class="bordered">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Kandang</th>
                    <th>Nama Plasma</th>
                    <th>Periode</th>
                    <th>Tgl Chick In</th>
                    <th>DOC</th>
                    <th>Ekor</th>
                    <th>Umur</th>
                    <th>Deplesi</th>
                    <th>FCR</th>
                    <th>BW</th>
                    <th>IP</th>
                    <th>Pdpt Plasma</th>
                    <th>Pdpt Plasma / Ekor</th>
                </tr>
            </thead>
            <tbody>
            <?php if ( !empty($data) && count($data) > 0 ) { ?>
                <?php foreach ($data as $k_unit => $v_unit) { ?>
                    <tr>
                        <td colspan="14"><b><?php echo strtoupper($v_unit['nama_unit']); ?></b></td>
                    </tr>
                    <?php foreach ($v_unit['mitra'] as $k_mtr => $v_mtr) { ?>
                        <?php $no = 1; ?>
                        <?php foreach ($v_mtr['detail'] as $k_det => $v_det) { ?>
                            <tr>
                                <td class="str"><?php echo $no; ?></td>
                                <td class="str"><?php echo !empty($v_det['kandang']) ? $v_det['kandang'] : '-'; ?></td>
                                <td class="str"><?php echo strtoupper($v_mtr['nama']); ?></td>
                                <td class="str">-</td>
                                <td class="str"><?php echo $v_det['tgl_chick_in']; ?></td>
                                <td class="str"><?php echo strtoupper($v_det['barang']); ?></td>
                                <td class="number_format" align="right"><?php echo ($v_det['ekor']); ?></td>
                                <td class="decimal_number_format" align="right"><?php echo round($v_det['umur'], 2); ?></td>
                                <td class="decimal_number_format" align="right"><?php echo ($v_det['deplesi']); ?></td>
                                <td class="decimal_number_format" align="right"><?php echo ($v_det['fcr']); ?></td>
                                <td class="decimal_number_format" align="right"><?php echo ($v_det['bb']); ?></td>
                                <td class="decimal_number_format" align="right"><?php echo ($v_det['ip']); ?></td>
                                <td class="decimal_number_format" align="right"><?php echo ($v_det['pdpt_plasma']); ?></td>
                                <td class="decimal_number_format" align="right"><?php echo round($v_det['pdpt_plasma_per_ekor'], 2); ?></td>
                            </tr>
                            <?php $no++; ?>
                        <?php } ?>
                        <tr>
                            <td colspan="14"></td>
                        </tr>
                    <?php } ?>
                <?php } ?>
            <?php } else { ?>
                <tr>
                    <td colspan="14">Data tidak ditemukan.</td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
	</div>
</body>
</html>