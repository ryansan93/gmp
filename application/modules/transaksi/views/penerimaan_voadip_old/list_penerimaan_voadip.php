<?php if ( count($data) > 0 ): ?>
	<?php // cetak_r($data); ?>
	<?php foreach ($data as $k_data => $v_data): ?>
		<?php $index = 0; ?>
		<?php foreach ($v_data['detail'] as $k_detail => $v_detail): ?>
			<?php $index++; ?>
			<tr class="v-center data">
				<?php if ( $index == 1 ): ?>
					<td class="grey text-center no_order" rowspan="<?php echo count($v_data['detail']); ?>"><?php echo $v_data['no_order']; ?></td>
					<td class="grey text-center tgl_order" rowspan="<?php echo count($v_data['detail']); ?>" data-tanggal="<?php echo $v_data['tanggal']; ?>" ><?php echo tglIndonesia($v_data['tanggal'], '-', ' ', true); ?></td>
				<?php endif ?>
				<td class="tujuan_kirim" data-id="<?php echo $v_detail['id_tujuan_kirim'] ?>" data-kirimke="<?php echo $v_detail['kirim_ke']; ?>"><?php echo strtoupper($v_detail['tujuan_kirim']); ?></td>
				<td class="barang" data-kode="<?php echo $v_detail['kode_item']; ?>"><?php echo $v_detail['item']; ?></td>
				<td class="text-right">
					<input type="text" class="form-control text-right jumlah" data-tipe="integer" maxlength="10" value="<?php echo angkaRibuan($v_detail['jumlah']); ?>" data-required="1">
				</td>
				<td>
					<input type="text" class="form-control no_sj" placeholder="No. SJ" data-required="1">
				</td>
				<td>
					<div class="input-group date col-md-12 datetimepicker" id="datetimepicker1" name="tgl_terima">
				        <input type="text" class="form-control text-center" placeholder="Tanggal Terima" data-required="1" />
				        <span class="input-group-addon">
				            <span class="glyphicon glyphicon-calendar"></span>
				        </span>
				    </div>
				</td>
				<td>
					<input type="text" class="form-control alamat_terima" placeholder="Alamat" value="<?php echo $v_detail['alamat'] ?>" data-required="1">
				</td>
				<td>
					<select class="form-control kondisi" data-required="1">
						<option value="">-- Pilih Kondisi --</option>
						<option value="baik">Baik</option>
						<option value="rusak">Rusak</option>
					</select>
				</td>
				<td>
					<input type="text" class="form-control ket" placeholder="Keterangan">
				</td>
			</tr>
		<?php endforeach ?>
	<?php endforeach ?>
<?php else: ?>
	<tr>
		<td colspan="10">Data tidak ditemukan.</td>
	</tr>
<?php endif ?>