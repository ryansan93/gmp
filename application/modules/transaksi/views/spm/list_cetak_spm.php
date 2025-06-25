<?php if ( !empty($data) ): ?>
	<?php foreach ($data as $k_data => $v_data): ?>
		<tr class="v-center">
			<td class="col-md-2"><?php echo $v_data['no_spm']; ?></td>
			<td class="col-md-2 text-center"><?php echo tglIndonesia($v_data['tgl_spm'], '-', ' '); ?></td>
			<td class="col-md-1 text-right"><?php echo angkaDecimal($v_data['total_spm']); ?></td>
			<td class="col-md-1 text-right"><?php echo angkaRibuan($v_data['zak_spm']); ?></td>
			<td class="col-md-5"><?php echo $v_data['ekspedisi']['nama']; ?></td>
			<td class="col-md-1 text-center">
				<?php
					$no_spm = exEncrypt($v_data['no_spm']);
					$url = "transaksi/SPM/cetak_spm/".$no_spm;
				?>
				<a href="<?php echo $url; ?>" target="_blank" data-href="<?php echo $url; ?>" data-nospm="<?php echo $v_data['no_spm']; ?>" class="btn btn-primary" onclick="spm.cetak_spm(this)"><i class="fa fa-print"></i> Cetak</a>
			</td>
		</tr>
	<?php endforeach ?>
<?php else: ?>
	<tr>
		<td colspan="6">Data tidak ditemukan.</td>
	</tr>
<?php endif ?>