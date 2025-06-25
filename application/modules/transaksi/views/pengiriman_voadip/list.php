<?php if ( !empty($data) && count($data) > 0 ): ?>
	<?php foreach ($data as $k => $v_data): ?>
		<tr class="search">
			<td class="text-center no_order"><?php echo $v_data['no_order']; ?></td>
			<td class="text-center tgl_kirim"><?php echo strtoupper(tglIndonesia($v_data['tgl_kirim'], '-', ' ')); ?></td>
			<td class="asal"><?php echo $v_data['asal']; ?></td>
			<td class="tujuan"><?php echo $v_data['tujuan']; ?></td>
			<td class="text-center nopol"><?php echo $v_data['nopol']; ?></td>
			<td class="text-center"><?php echo !empty($v_data['tgl_terima']) ? strtoupper(tglIndonesia($v_data['tgl_terima'], '-', ' ')) : 'BELUM'; ?></td>
			<td>
				<!-- <div class="col-sm-4 no-padding text-center cursor-p btn_action">
					<i class="fa fa-edit" title="EDIT" data-id="<?php echo $v_data['id']; ?>" onclick="pv.changeTabActive(this)" data-href="pengiriman"></i>
				</div>
				<div class="col-sm-4 no-padding text-center cursor-p btn_action">
					<i class="fa fa-trash" title="DELETE" data-id="<?php echo $v_data['id']; ?>" onclick="pv.delete(this)"></i>
				</div> -->
				<div class="col-sm-6 no-padding text-center cursor-p btn_action">
					<i class="fa fa-file" title="VIEW" data-id="<?php echo $v_data['id']; ?>" onclick="pv.changeTabActive(this)" data-href="pengiriman"></i>
				</div>
				<div class="col-sm-6 no-padding text-center cursor-p btn_action">
					<i class="fa fa-list" title="LIST AKTIFITAS" data-id="<?php echo $v_data['id']; ?>" onclick="pv.listActivity(this)"></i>
				</div>
			</td>
		</tr>
	<?php endforeach ?>
<?php else: ?>
	<tr>
		<td colspan="7">Data tidak ditemukan.</td>
	</tr>
<?php endif ?>