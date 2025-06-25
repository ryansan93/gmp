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
                    <th>DO</th>
                    <th>Unit</th>
                    <th>No. Invoice Supplier</th>
                    <th>No. Invoice</th>
                    <th>Tgl Invoice</th>
                    <th>Tgl Kedatangan</th>
                    <th>Kode Supplier</th>
                    <th>Nama Supplier</th>
                    <th>Kode Barang</th>
                    <th>Nama Barang</th>
                    <th>Kuantitas Barang</th>
                    <th>Satuan Barang</th>
                    <th>Jumlah Box</th>
                    <th>Harga Satuan Barang</th>
                    <th>Total Invoice</th>
                    <th>Unit - Nama Plasma - Kandang</th>
                    <th>Periode Plasma</th>
                    <th>Keterangan di Bagian kolom Deskripsi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ( !empty($data) && count($data) > 0 ) { ?>
                    <?php foreach ($data as $key => $value) { ?>
                        <tr>
                            <td class="str"><?php echo strtoupper($value['perusahaan_alias']); ?></td>
                            <td class="str"><?php echo strtoupper($value['nama_unit']); ?></td>
                            <td class="str"><?php echo strtoupper($value['no_sj']); ?></td>
                            <td class="str"><?php echo strtoupper($value['no_form']); ?></td>
                            <td class=""><?php echo substr($value['tanggal'], 0, 10); ?></td>
                            <td class=""><?php echo substr($value['periode'], 0, 10); ?></td>
                            <td class="str"><?php echo strtoupper($value['kode_supplier']); ?></td>
                            <td class="str"><?php echo strtoupper($value['nama_supplier']); ?></td>
                            <td class="str"><?php echo strtoupper($value['kode_barang']); ?></td>
                            <td class="str"><?php echo strtoupper($value['nama_barang']); ?></td>
                            <td class="number_format"><?php echo $value['kuantitas']; ?></td>
                            <td class="str"><?php echo strtoupper($value['satuan_kuantitas']); ?></td>
                            <td class="number_format"><?php echo $value['jumlah_box']; ?></td>
                            <td class="decimal_number_format"><?php echo $value['harga_per_satuan_kuantitas']; ?></td>
                            <td class="decimal_number_format"><?php echo ($value['kuantitas'] * $value['harga_per_satuan_kuantitas']); ?></td>
                            <td class="str"><?php echo strtoupper($value['unit'].'-'.$value['nama_plasma'].'-'.$value['kandang_plasma']); ?></td>
                            <td class="number_format"><?php echo $value['periode_chickin']; ?></td>
                            <td class="str"><?php echo strtoupper($value['unit'].'-'.$value['nama_plasma'].'-'.$value['kandang_plasma'].' Periode '.$value['periode_chickin']); ?></td>
                        </tr>
                    <?php } ?>
                <?php } else { ?>
                    <tr>
                        <td colspan="18">Data tidak ditemukan.</td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
	</div>
</body>
</html>