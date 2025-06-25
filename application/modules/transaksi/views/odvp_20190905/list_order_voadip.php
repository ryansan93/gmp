<?php if ( count($data) > 0 ): ?>
	<?php echo count($data); ?>
	<?php foreach ($data as $k_data => $v_data): ?>
		<tr>
			<td><?php echo tglIndonesia($v_data['tanggal'], '-', ' ', true); ?></td>
			<td><?php echo $v_data['no_order']; ?></td>
			<td>
				<div class="col-sm-10 no-padding">
					<?php echo $v_data['d_supplier']['nama']; ?>
				</div>
				<div class="col-sm-2">
                    <button id="btn-view" type="button" data-href="action" class="btn btn-primary cursor-p pull-right" title="EDIT" onclick="odvp.order_voadip_edit_form(this)" data-id="<?php echo $v_data['no_order']; ?>"> 
                        <i class="fa fa-edit" aria-hidden="true"></i>
                    </button>
                    <button id="btn-view" type="button" data-href="action" class="btn btn-primary cursor-p pull-right" title="LIHAT" onclick="odvp.order_voadip_view_form(this)" data-id="<?php echo $v_data['no_order']; ?>" style="margin-right: 10px;"> 
                        <i class="fa fa-file" aria-hidden="true"></i>
                    </button>
				</div>
			</td>
		</tr>
	<?php endforeach ?>
<?php else: ?>
	<tr>
		<td class="text-left" colspan="3">Data tidak ditemukan.</td>
	</tr>
<?php endif ?>