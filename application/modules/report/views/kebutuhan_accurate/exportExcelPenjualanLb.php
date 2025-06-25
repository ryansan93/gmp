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
                    <th>Kode Bakul</th>
                    <th>NIK Bakul</th>
                    <th>Nama Bakul</th>
                    <th>Alamat Bakul</th>
                    <th>No. Faktur</th>
                    <th>Tanggal Panen</th>
                    <th>Tanggal RHPP</th>
                    <th>No. Nota (No. SJ)</th>
                    <th>Kode Barang (Ayam)</th>
                    <th>Deskripsi Barang (Ayam)</th>
                    <th>Kuantitas</th>
                    <th>Harga Per Satuan Kuantitas</th>
                    <th>Jumlah Ekor</th>
                    <th>Periode (Tgl Chick In)</th>
                    <th>Departemen (Kota Unit)</th>
                    <th>NIM</th>
                    <th>NIK</th>
                    <th>Nama Plasma</th>
                    <th>Kandang Plasma</th>
                    <th>NPWP Plasma</th>
                </tr>
            </thead>
            <tbody>
                <?php if ( !empty($data) && count($data) > 0 ) { ?>
                    <?php foreach ($data as $key => $value) { ?>
                        <tr>
                            <td class="str"><?php echo $value['kode_bakul']; ?></td>
                            <td class="str"><?php echo $value['nik_bakul']; ?></td>
                            <td class="str"><?php echo $value['nama_bakul']; ?></td>
                            <td class="str"><?php echo $value['alamat_bakul']; ?></td>
                            <td class="str"><?php echo $value['no_faktur']; ?></td>
                            <td class=""><?php echo $value['tanggal_panen']; ?></td>
                            <td class=""><?php echo !empty($value['tanggal_rhpp']) ? $value['tanggal_rhpp'] : '-'; ?></td>
                            <td class="str"><?php echo !empty($value['no_nota']) ? $value['no_nota'] : '-'; ?></td>
                            <td class="str"><?php echo $value['kode_barang']; ?></td>
                            <td class="str"><?php echo $value['deskripsi_barang']; ?></td>
                            <td class="decimal_number_format"><?php echo ($value['kuantitas']); ?></td>
                            <td class="number_format"><?php echo ($value['harga_per_satuan_kuantitas']); ?></td>
                            <td class="number_format"><?php echo ($value['jumlah_ekor']); ?></td>
                            <td class="str"><?php echo $value['periode']; ?></td>
                            <td class="str"><?php echo $value['unit']; ?></td>
                            <td class="str"><?php echo $value['nim']; ?></td>
                            <td class="str"><?php echo $value['nik']; ?></td>
                            <td class="str"><?php echo $value['nama_plasma']; ?></td>
                            <td class="str"><?php echo $value['kandang_plasma']; ?></td>
                            <td class="str"><?php echo $value['npwp_plasma']; ?></td>
                        </tr>
                    <?php } ?>
                <?php } else { ?>
                    <tr>
                        <td colspan="20">Data tidak ditemukan.</td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
	</div>
</body>
</html>