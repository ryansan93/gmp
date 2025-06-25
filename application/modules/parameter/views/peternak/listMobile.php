<?php if ( !empty($data) && count($data) > 0 ): ?>
    <?php $no = 1; ?>
	<?php foreach ($data as $k_data => $v_data): ?>
		<tr>
			<td><?php echo $no; ?></td>
			<td><?php echo $v_data['perusahaan']; ?></td>
			<td><?php echo $v_data['nim']; ?></td>
			<td>
				<div class="col-xs-12 no-padding">
					<b><?php echo strtoupper($v_data['nama']); ?></b>
				</div>
				<div class="col-xs-12 no-padding" style="border-bottom: 1px solid #dedede;"></div>
				<?php if ( empty($v_data['max_tgl_chickin']) ) { ?>
					<div class="col-xs-12 no-padding">
						BELUM ADA CHICK IN
					</div>
				<?php } else { ?>
					<div class="col-xs-12 no-padding">
						CHICK IN TERAKHIR
					</div>
					<div class="col-xs-12 no-padding">
						<b><?php echo strtoupper(tglIndonesia($v_data['max_tgl_chickin'], '-', ' ')); ?></b>
					</div>
				<?php } ?>
			</td>
			<td>
				<button type="button" class="btn btn-primary col-xs-12" onclick="ptk.detailMobile(this)" data-id="<?php echo $v_data['nomor']; ?>" data-edit="" data-href="action">
					<i class="fa fa-file"></i>
				</button>
			</td>
		</tr>
        <?php $no++; ?>
	<?php endforeach ?>
<?php else: ?>
	<tr>
		<td colspan="5">Data tidak ditemukan.</td>
	</tr>
<?php endif ?>