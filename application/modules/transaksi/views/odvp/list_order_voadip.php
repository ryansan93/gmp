<?php if ( count($data) > 0 ): ?>
	<?php echo count($data); ?>
	<?php foreach ($data as $k_data => $v_data): ?>
		<tr class="cursor-p search" ondblclick="odvp.order_voadip_view_form(this)" data-id="<?php echo $v_data['no_order']; ?>" title="Klik 2x untuk liat detail">
			<td class="tanggal"><?php echo strtoupper(tglIndonesia($v_data['tanggal'], '-', ' ')); ?></td>
			<td class="no_order"><?php echo $v_data['no_order']; ?></td>
			<td class="supplier">
				<div class="col-sm-10 no-padding supplier">
					<?php echo strtoupper($v_data['nama_supplier']); ?>
				</div>
			</td>
			<td class="perusahaan">
				<div class="col-sm-10 no-padding perusahaan">
					<?php echo strtoupper($v_data['nama_perusahaan']); ?>
				</div>
				<div class="col-sm-2 no-padding">
					<?php if ( $v_data['status_kirim'] == 0 ): ?>
						<div class="col-sm-4 no-padding text-center cursor-p btn_action">
							<i class="fa fa-edit" title="EDIT" data-id="<?php echo $v_data['no_order']; ?>" onclick="odvp.order_voadip_edit_form(this)" data-href="action"></i>
						</div>
						<div class="col-sm-4 no-padding text-center cursor-p btn_action">
							<i class="fa fa-trash" title="DELETE" onclick="odvp.order_voadip_delete(this)" data-id="<?php echo $v_data['id']; ?>" data-href="action"></i>
						</div>
						<div class="col-sm-4 no-padding text-center cursor-p btn_action">
							<i class="fa fa-list" title="LIST AKTIFITAS" data-id="<?php echo $v_data['id']; ?>" data-jenis="voadip" onclick="odvp.listActivity(this)"></i>
						</div>
					<?php else: ?>
						<div class="col-sm-4 no-padding text-center cursor-p btn_action">
							<i class="fa fa-file" title="DETAIL" data-id="<?php echo $v_data['no_order']; ?>" onclick="odvp.order_voadip_view_form(this)" data-href="action"></i>
						</div>
						<div class="col-sm-4 no-padding text-center cursor-p btn_action">
							<i class="fa fa-edit" title="EDIT" data-id="<?php echo $v_data['no_order']; ?>" onclick="odvp.order_voadip_edit_form(this)" data-href="action"></i>
						</div>
						<div class="col-sm-4 no-padding text-center cursor-p btn_action">
							<i class="fa fa-list" title="LIST AKTIFITAS" data-id="<?php echo $v_data['id']; ?>" data-jenis="voadip" onclick="odvp.listActivity(this)"></i>
						</div>
					<?php endif ?>
				</div>
			</td>
		</tr>
	<?php endforeach ?>
<?php else: ?>
	<tr>
		<td class="text-left" colspan="4">Data tidak ditemukan.</td>
	</tr>
<?php endif ?>