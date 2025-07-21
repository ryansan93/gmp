<?php if ( !empty($data) && count($data) > 0 ): ?>
	<?php foreach ($data as $k_data => $v_data): ?>
		<tr class="search">
			<td><?php echo strtoupper($v_data['nama']); ?></td>
			<td><?php echo strtoupper($v_data['alamat']); ?></td>
			<td class="text-center"><?php echo strtoupper($v_data['jenis']); ?></td>
			<td class="text-center"><?php echo strtoupper($v_data['d_unit']['nama']); ?></td>
			<td class="text-center"><?php echo strtoupper($v_data['d_perusahaan']['perusahaan']); ?></td>
			<td><?php echo strtoupper($v_data['penanggung_jawab']); ?></td>
			<td>
				<div class="col-sm-12 no-padding">
					<div class="col-sm-12" style="padding: 0px 0px 0px 0px;">
						<button type="button" class="btn btn-primary col-sm-12" title="EDIT" data-id="<?php echo $v_data['id']; ?>" onclick="gudang.edit_form(this)"><i class="fa fa-edit"></i></button>
					</div>
				</div>
			</td>
		</tr>
	<?php endforeach ?>
<?php else: ?>
	<tr>
		<td colspan="7">Data tidak ditemukan.</td>
	</tr>
<?php endif ?>