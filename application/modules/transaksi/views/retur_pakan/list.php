<?php if ( !empty($data) && count($data) > 0 ): ?>
	<?php foreach ($data as $k => $val): ?>
		<tr class="search">
			<td class="text-center tgl_retur"><?php echo strtoupper(tglIndonesia( $val['tgl_retur'], '-', ' ' )); ?></td>
			<td class="text-center no_order"><?php echo $val['no_order']; ?></td>
			<td class="text-center no_retur"><?php echo $val['no_retur']; ?></td>
			<td class="asal"><?php echo $val['asal']; ?></td>
			<td class="tujuan"><?php echo $val['tujuan']; ?></td>
			<td>
				<div class="col-sm-6 no-padding text-center cursor-p btn_action">
					<i class="fa fa-file" title="VIEW" onclick="rp.changeTabActive(this)" data-href="rp" data-id="<?php echo $val['id']; ?>" data-edit=""></i>
				</div>
				<!-- <div class="col-sm-4 no-padding text-center cursor-p btn_action">
					<i class="fa fa-trash" title="DELETE" data-id="<?php echo $val['id']; ?>" onclick="rp.delete(this)"></i>
				</div> -->
				<div class="col-sm-6 no-padding text-center cursor-p btn_action">
					<i class="fa fa-list" title="LIST AKTIFITAS" data-id="<?php echo $val['id']; ?>" onclick="rp.listActivity(this)"></i>
				</div>
			</td>
		</tr>
	<?php endforeach ?>
<?php else: ?>
	<tr>
		<td colspan="6">Data tidak ditemukan.</td>
	</tr>
<?php endif ?>