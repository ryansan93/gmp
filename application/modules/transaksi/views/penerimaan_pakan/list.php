<?php if ( !empty($data) && count($data) > 0 ): ?>
	<?php foreach ($data as $k => $v_data): ?>
		<tr class="search">
			<td class="text-center no_sj"><?php echo $v_data['no_sj']; ?></td>
			<td class="text-center tgl_terima"><?php echo strtoupper(tglIndonesia($v_data['tgl_terima'], '-', ' ')); ?></td>
			<td class="asal"><?php echo $v_data['asal']; ?></td>
			<td class="tujuan"><?php echo $v_data['tujuan']; ?></td>
			<td class="text-center nopol"><?php echo $v_data['nopol']; ?></td>
			<td>
				<div class="col-sm-6 no-padding text-center cursor-p btn_action">
					<i class="fa fa-file" title="VIEW" data-id="<?php echo $v_data['id']; ?>" data-edit="" onclick="pp.changeTabActive(this)" data-href="penerimaan"></i>
				</div>
				<div class="col-sm-6 no-padding text-center cursor-p btn_action">
					<i class="fa fa-list" title="LIST AKTIFITAS" data-id="<?php echo $v_data['id']; ?>" onclick="pp.listActivity(this)"></i>
				</div>
			</td>
		</tr>
	<?php endforeach ?>
<?php else: ?>
	<tr>
		<td colspan="6">Data tidak ditemukan.</td>
	</tr>
<?php endif ?>