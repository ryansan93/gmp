<?php if ( count($data) > 0 ): ?>
	<?php foreach ($data as $k => $val): ?>
		<tr class="v-center">
			<td class="text-center tanggal"><?php echo strtoupper(tglIndonesia($val['tgl_trans'], '-', ' ')); ?></td>
			<td class="text-left supplier"><?php echo strtoupper($val['nama_supplier']); ?></td>
			<td class="text-left perusahaan"><?php echo strtoupper($val['nama_perusahaan']); ?></td>
			<td class="text-center rcn_kirim"><?php echo strtoupper(tglIndonesia($val['rcn_kirim'], '-', ' ')); ?></td>
			<td class="text-center no_order"><?php echo $val['no_order'] ?></td>
			<td>
				<?php if ( $val['status_kirim'] == 0 ): ?>
					<div class="col-sm-4 no-padding text-center cursor-p btn_action">
						<i class="fa fa-edit" title="EDIT" onclick="odvp.order_pakan_edit_form(this)" data-id="<?php echo $val['no_order']; ?>" data-href="action"></i>
					</div>
					<div class="col-sm-4 no-padding text-center cursor-p btn_action">
						<i class="fa fa-trash" title="DELETE" data-id="<?php echo $val['id']; ?>" onclick="odvp.order_pakan_delete(this)" data-href="action"></i>
					</div>
					<div class="col-sm-4 no-padding text-center cursor-p btn_action">
						<i class="fa fa-list" title="LIST AKTIFITAS" data-id="<?php echo $val['id']; ?>" data-jenis="pakan" onclick="odvp.listActivity(this)"></i>
					</div>
				<?php else: ?>
					<div class="col-sm-4 no-padding text-center cursor-p btn_action">
						<i class="fa fa-file" title="DETAIL" onclick="odvp.order_pakan_view_form(this)" data-id="<?php echo $val['no_order']; ?>" data-href="action"></i>
					</div>
					<div class="col-sm-4 no-padding text-center cursor-p btn_action">
						<i class="fa fa-edit" title="EDIT" onclick="odvp.order_pakan_edit_form(this)" data-id="<?php echo $val['no_order']; ?>" data-href="action"></i>
					</div>
					<div class="col-sm-4 no-padding text-center cursor-p btn_action">
						<i class="fa fa-list" title="LIST AKTIFITAS" data-id="<?php echo $val['id']; ?>" data-jenis="pakan" onclick="odvp.listActivity(this)"></i>
					</div>
				<?php endif ?>
			</td>
		</tr>
	<?php endforeach ?>
<?php else: ?>
	<tr>
		<td class="text-left" colspan="6">Data tidak ditemukan.</td>
	</tr>
<?php endif ?>