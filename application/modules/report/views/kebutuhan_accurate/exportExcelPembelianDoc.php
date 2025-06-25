<!DOCTYPE html>
<html>
<head>
	<style type="text/css">
		.str { mso-number-format:\@; }
		.decimal_number_format { mso-number-format: "\#\,\#\#0.00"; }
		.number_format { mso-number-format: "\#\,\#\#0"; }
        .date_format { mso-number-format:"mm/dd/yyyy"; }
        .datetime_format { mso-number-format:"m/d/yy\ h:mm\ AM/PM"; }
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
                    <th>Pembelian</th>
                    <th>Tanggal</th>
                    <th>Perusahaan</th>
                    <th>Kode Supplier</th>
                    <th>Nama Supplier</th>
                    <th>NIK</th>
                    <th>NPWP</th>
                    <th>No. Form (No Order)</th>
                    <th>No. Faktur Pembelian</th>
                    <th>Tanggal Faktur</th>
                    <th>Tanggal Pengiriman</th>
                    <th>Kode Barang</th>
                    <th>Deskripsi/Nama Barang</th>
                    <th>Kuantitas</th>
                    <th>Satuan Kuantitas</th>
                    <th>Harga per Satuan Kuantitas</th>
                    <th>Jumlah Box</th>
                    <th>Departemen (Kota Unit)</th>
                    <th>NIK Plasma</th>
                    <th>NPWP Plasma</th>
                    <th>Nama Plasma</th>
                    <th>Kandang Plasma</th>
                    <th>Periode (Tgl Chick In)</th>
                </tr>
            </thead>
            <tbody>
                <?php if ( !empty($data) && count($data) > 0 ) { ?>
                    <?php foreach ($data as $key => $value) { ?>
                        <tr>
                            <td class="str"><?php echo 'DOC'; ?></td>
                            <td class=""><?php echo substr($value['tanggal'], 0, 10); ?></td>
                            <td class="str"><?php echo $value['perusahaan']; ?></td>
                            <td class="str"><?php echo $value['kode_supplier']; ?></td>
                            <td class="str"><?php echo $value['nama_supplier']; ?></td>
                            <td class="str"><?php echo $value['nik']; ?></td>
                            <td class="str"><?php echo $value['npwp']; ?></td>
                            <td class="str"><?php echo $value['no_form']; ?></td>
                            <td class="str"><?php echo !empty($value['no_faktur_pembelian']) ? $value['no_faktur_pembelian'] : '-'; ?></td>
                            <td class=""><?php echo !empty($value['tanggal_faktur']) ? substr($value['tanggal_faktur'], 0, 10) : '-'; ?></td>
                            <td class=""><?php echo !empty($value['tanggal_pengiriman']) ? substr($value['tanggal_pengiriman'], 0, 10) : '-'; ?></td>
                            <td class="str"><?php echo $value['kode_barang']; ?></td>
                            <td class="str"><?php echo $value['nama_barang']; ?></td>
                            <td class="number_format"><?php echo $value['kuantitas']; ?></td>
                            <td class="str"><?php echo $value['satuan_kuantitas']; ?></td>
                            <td class="decimal_number_format"><?php echo $value['harga_per_satuan_kuantitas']; ?></td>
                            <td class="number_format"><?php echo $value['jumlah_box']; ?></td>
                            <td class="str"><?php echo $value['unit']; ?></td>
                            <td class="str"><?php echo $value['nik_plasma']; ?></td>
                            <td class="str"><?php echo $value['npwp_plasma']; ?></td>
                            <td class="str"><?php echo $value['nama_plasma']; ?></td>
                            <td class="str"><?php echo $value['kandang_plasma']; ?></td>
                            <td class=""><?php echo substr($value['periode'], 0, 10); ?></td>
                        </tr>
                    <?php } ?>
                <?php } else { ?>
                    <tr>
                        <td colspan="23">Data tidak ditemukan.</td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
	</div>
</body>
</html>