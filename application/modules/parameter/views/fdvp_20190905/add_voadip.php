<tr class="row_data">
	<td>
		<select class="form-control" id="kategori" data-required="1">
			<option class="empty" value="">Pilih Kategori</option>
			<?php foreach ($kategori as $key => $value): ?>
				<option value="<?php echo $key; ?>" ><?php echo $value; ?></option>
			<?php endforeach ?>
		</select>
	</td>
	<td>
		<input class="form-control" type="text" id="kode" readonly>
	</td>
	<td>
		<input class="form-control" type="text" id="nama_voadip" data-required="1">
	</td>
	<td>
		<input class="form-control" type="text" id="kode_item_sup" data-required="1">
	</td>
	<td>
		<select class="form-control" id="supplier" data-required="1">
			<option class="empty" value="">Pilih Supplier</option>
			<?php foreach ($list_supplier as $key => $v_supl): ?>
				<option value="<?php echo $v_supl['nip']; ?>" > <?php echo $v_supl['nama']; ?> </option>
			<?php endforeach ?>
		</select>
	</td>
	<td>
		<input class="form-control text-right" type="text" id="dosis" data-tipe="decimal" data-required="1">
	</td>
	<td>
		<input class="form-control text-right" type="text" id="isi" data-tipe="integer" data-required="1">
	</td>
	<td>
		<input class="form-control" type="text" id="satuan" data-required="1">
	</td>
	<td>
		<select class="form-control" id="bentuk_voadip" data-required="1">
			<option class="empty" value="">Pilih Bentuk</option>
			<?php foreach ($bentuk as $key => $value): ?>
				<option value="<?php echo $key ?>" ><?php echo $value; ?></option>
			<?php endforeach ?>
		</select>
	</td>
	<td>
		<input class="form-control text-right" type="text" id="masa_simpan" data-tipe="integer" data-required="1">
	</td>
	<td>
		<input class="form-control" type="text" id="status" readonly>
		<div class="btn-ctrl">
			<span onclick="fdvp.addRowChild(this)" class="btn_add_row_2x"></span>
			<span onclick="fdvp.removeRowChild(this)" class="btn_del_row_2x"></span>
		</div>
	</td>
</tr>