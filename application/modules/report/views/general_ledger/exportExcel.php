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
    <div><b>LAPORAN GL (BUKU BESAR)</b></div>
    <div>
        <table>
            <tbody>
                <tr>
                    <td><b>Periode : </b></td>
                    <td><b><?php echo $periode; ?></b></td>
                </tr>
            </tbody>
        </table>
    </div>
	<div>
        <table class="bordered">
            <thead>
                <tr>
                    <th class="text-center col-xs-1">No. COA</th>
                    <th class="text-center col-xs-1">Unit</th>
                    <th class="text-center col-xs-2">Nama COA</th>
                    <th class="text-center col-xs-1">Saldo Awal</th>
                    <th class="text-center col-xs-1">Debet</th>
                    <th class="text-center col-xs-1">Kredit</th>
                    <th class="text-center col-xs-1">Saldo Akhir</th>
                </tr>
            </thead>
            <tbody>
                <?php if ( !empty($data) && count($data) > 0 ) { ?>
                    <?php foreach ($data as $key => $value) { ?>
                        <tr>
                            <td class="str"><?php echo strtoupper($value['no_coa']); ?></td>
                            <td class="str"><?php echo strtoupper($value['nama_coa']); ?></td>
                            <td class="str"><?php echo strtoupper($value['lap']); ?></td>
                            <td class="str"><?php echo strtoupper($value['coa_pos']); ?></td>
                            <td class="decimal_number_format"><?php echo (0); ?></td>
                            <td class="decimal_number_format"><?php echo (0); ?></td>
                            <td class="decimal_number_format"><?php echo ($value['debet']); ?></td>
                            <td class="decimal_number_format"><?php echo ($value['kredit']); ?></td>
                            <!-- <td class="decimal_number_format"><?php echo (0); ?></td>
                            <td class="decimal_number_format"><?php echo (0); ?></td> -->
                            <td class="decimal_number_format"><?php echo ($value['saldo_akhir_debet'] >= 0) ? ($value['saldo_akhir_debet']) : '('.(abs($value['saldo_akhir_debet'])).')'; ?></td>
                            <td class="decimal_number_format"><?php echo ($value['saldo_akhir_kredit'] >= 0) ? ($value['saldo_akhir_kredit']) : '('.(abs($value['saldo_akhir_kredit'])).')'; ?></td>
                            <td class="decimal_number_format"><?php echo ($value['lr_debet'] >= 0) ? ($value['lr_debet']) : '('.(abs($value['lr_debet'])).')'; ?></td>
                            <td class="decimal_number_format"><?php echo ($value['lr_kredit'] >= 0) ? ($value['lr_kredit']) : abs($value['lr_kredit']); ?></td>
                            <td class="decimal_number_format"><?php echo ($value['neraca_debet'] >= 0) ? ($value['neraca_debet']) : '('.(abs($value['neraca_debet'])).')'; ?></td>
                            <td class="decimal_number_format"><?php echo ($value['neraca_kredit'] >= 0) ? ($value['neraca_kredit']) : abs($value['neraca_kredit']); ?></td>
                        </tr>
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