<?php if ( count($data) > 0 ): ?>
	<?php $no = 0; ?>
	<?php foreach ($data as $k_data => $v_data): ?>
		<?php $no++; ?>
		<tr>
			<td><?php echo angkaRibuan($no); ?></td>
			<td><?php echo tglIndonesia($v_data['tgl_berlaku'], '-', ' '); ?></td>
			<td>
				<div class="col-sm-11 no-padding">
					<?php echo angkaRibuan($v_data['biaya_opr']); ?>
				</div>
				<div class="col-sm-1 no-padding">
					<button type="button" class="btn btn-primary col-sm-12" title="EDIT" data-id="<?php echo $v_data['id']; ?>" onclick="bo.edit_form(this)" style="padding: 0px;"><i class="fa fa-edit"></i></button>
				</div>
			</td>
		</tr>
	<?php endforeach ?>
<?php else: ?>
	<tr>
		<td colspan="3">Data tidak ditemukan.</td>
	</tr>
<?php endif ?>