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
                    <th>Kode Supplier</th>
                    <th>Nama Supplier</th>
                    <th>Tanggal Retur</th>
                    <th>Kode Barang</th>
                    <th>Nama Barang</th>
                    <th>Kuantitas</th>
                    <th>Satuan Kuantitas</th>
                    <th>Deskripsi Penyebab Retur</th>
                </tr>
            </thead>
            <tbody>
                <?php if ( !empty($data) && count($data) > 0 ) { ?>
                    <?php foreach ($data as $key => $value) { ?>
                        <tr>
                            <td class="str"><?php echo $value['kode_supplier']; ?></td>
                            <td class="str"><?php echo $value['nama_supplier']; ?></td>
                            <td class=""><?php echo $value['tanggal_retur']; ?></td>
                            <td class="str"><?php echo $value['kode_barang']; ?></td>
                            <td class="str"><?php echo $value['nama_barang']; ?></td>
                            <td class="decimal_number_format"><?php echo $value['kuantitas']; ?></td>
                            <td class="str"><?php echo $value['satuan_kuantitas']; ?></td>
                            <td class="str"><?php echo $value['deskripsi_penyebab_retur']; ?></td>
                        </tr>
                    <?php } ?>
                <?php } else { ?>
                    <tr>
                        <td colspan="8">Data tidak ditemukan.</td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
	</div>
</body>
</html>