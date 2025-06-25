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
                    <th>DO</th>
                    <th>Unit</th>
                    <th>Unit - Nama Plasma - Periode</th>
                    <th>Periode</th>
                    <th>No. Invoice</th>
                    <th>Tgl Invoice</th>
                    <th>Kode Pelanggan (NIK)</th>
                    <th>Nama Pelanggan</th>
                    <th>No. Nota Timbang</th>
                    <th>Kode Barang</th>
                    <th>Nama Barang</th>
                    <th>Kuantitas Barang (Kg)</th>
                    <th>Jumlah Ekor</th>
                    <th>Harga Satuan Barang</th>
                    <th>Total Invoice</th>
                    <th>Keterangan di Bagian kolom Deskripsi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ( !empty($data) && count($data) > 0 ) { ?>
                    <?php foreach ($data as $key => $value) { ?>
                        <tr>
                            <td class="str"><?php echo strtoupper($value['perusahaan_alias']); ?></td>
                            <td class="str"><?php echo strtoupper($value['unit']); ?></td>
                            <td class="str"><?php echo strtoupper($value['unit'].'-'.$value['nama_plasma'].'-'.$value['kandang_plasma']); ?></td>
                            <td class="number_format"><?php echo $value['periode_chickin']; ?></td>
                            <td class="str"><?php echo !empty($value['no_nota']) ? $value['no_nota'] : '-'; ?></td>
                            <td class=""><?php echo $value['tanggal_panen']; ?></td>
                            <td class="str"><?php echo $value['nik_bakul']; ?></td>
                            <td class="str"><?php echo $value['nama_bakul']; ?></td>
                            <td class="str"></td>
                            <td class="str"><?php echo $value['kode_barang']; ?></td>
                            <td class="str"><?php echo $value['deskripsi_barang']; ?></td>
                            <td class="decimal_number_format"><?php echo ($value['kuantitas']); ?></td>
                            <td class="number_format"><?php echo ($value['jumlah_ekor']); ?></td>
                            <td class="number_format"><?php echo ($value['harga_per_satuan_kuantitas']); ?></td>
                            <td class="decimal_number_format"><?php echo ($value['kuantitas'] * $value['harga_per_satuan_kuantitas']); ?></td>
                            <td class="str"><?php echo strtoupper($value['unit'].'-'.$value['nama_plasma'].'-'.$value['kandang_plasma'].' Periode '.$value['periode_chickin']); ?></td>
                        </tr>
                    <?php } ?>
                <?php } else { ?>
                    <tr>
                        <td colspan="16">Data tidak ditemukan.</td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
	</div>
</body>
</html>