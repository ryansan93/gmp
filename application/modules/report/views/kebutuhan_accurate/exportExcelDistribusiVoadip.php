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
	<div>
        <table class="bordered">
            <thead>
                <tr>
                    <th>No. Batch</th>
                    <th>Tanggal Panen</th>
                    <th>Jenis Pengiriman</th>
                    <th>Unit</th>
                    <th>Nama Plasma/Peternak</th>
                    <th>Kandang</th>
                    <th>Periode</th>
                    <th>No. SJ</th>
                    <th>Kode Barang</th>
                    <th>Kuantitas</th>
                    <th>Satuan Kuantitas</th>
                    <th>Gudang</th>
                    <th>Departemen</th>
                    <th>Kode Proyek</th>
                </tr>
            </thead>
            <tbody>
                <?php if ( !empty($data) && count($data) > 0 ) { ?>
                    <?php foreach ($data as $key => $value) { ?>
                        <tr>
                            <td class="str"><?php echo !empty($value['no_batch']) ? $value['no_batch'] : '-'; ?></td>
                            <td class=""><?php echo !empty($value['tanggal_panen']) ? $value['tanggal_panen'] : '-'; ?></td>
                            <td class="str"><?php echo $value['jenis_pengiriman']; ?></td>
                            <td class="str"><?php echo $value['unit']; ?></td>
                            <td class="str"><?php echo $value['nama_plasma']; ?></td>
                            <td class="str"><?php echo $value['kandang']; ?></td>
                            <td class=""><?php echo $value['periode']; ?></td>
                            <td class="str"><?php echo $value['no_sj']; ?></td>
                            <td class="str"><?php echo $value['kode_barang']; ?></td>
                            <td class="decimal_number_format"><?php echo $value['kuantitas']; ?></td>
                            <td class="str"><?php echo $value['satuan_kuantitas']; ?></td>
                            <td class="str"><?php echo $value['gudang']; ?></td>
                            <td class="str"><?php echo !empty($value['departemen']) ? $value['departemen'] : '-'; ?></td>
                            <td class="str"><?php echo !empty($value['kode_proyek']) ? $value['kode_proyek'] : '-'; ?></td>
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