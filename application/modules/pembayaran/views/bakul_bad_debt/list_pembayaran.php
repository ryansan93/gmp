<?php if ( !empty($data) && count($data) > 0 ): ?>
	<?php $grand_total = 0; ?>
	<?php foreach ($data as $k_data => $v_data): ?>
		<tr class="data search">
			<td class="text-center"><?php echo tglIndonesia($v_data['tgl_bayar'], '-', ' '); ?></td>
			<td class="text-left"><?php echo strtoupper($v_data['perusahaan']); ?></td>
			<td class="text-left"><?php echo strtoupper($v_data['pelanggan']); ?></td>
			<td class="text-right jml_transfer"><?php echo angkaRibuan($v_data['jml_transfer']); ?></td>
			<td class="text-left">
				<a href="uploads/<?php echo $v_data['lampiran_transfer']; ?>" target="_blank">
					<?php 
						if ( strlen($v_data['lampiran_transfer']) > 25 ) {
							echo substr($v_data['lampiran_transfer'], 0, 25).'.....';
						} else {
							echo $v_data['lampiran_transfer'];
						}
					?>
				</a>
			</td>
			<td class="text-left">
				<?php echo $v_data['log']['deskripsi'].' '.tglIndonesia(substr($v_data['log']['waktu'], 0, 10), '-', ' ').' '.substr($v_data['log']['waktu'], 11, 5); ?>
			</td>
			<td>
				<div class="col-lg-12 no-padding">
					<div class="col-lg-12 text-center" style="padding-left: 5px; padding-right: 5px;">
						<button type="button" class="btn btn-primary col-lg-12" onclick="bakul.changeTabActive(this)" data-href="action" data-id="<?php echo $v_data['id']; ?>" data-resubmit=""><i class="fa fa-file-o"></i></button>
					</div>
					<!-- <?php if ( $akses['a_delete'] == 1 ): ?>
						<div class="col-lg-6 text-center" style="padding-left: 5px; padding-right: 5px;">
							<button type="button" class="btn btn-danger" onclick="bakul.delete(this)" data-id="<?php echo $v_data['id']; ?>"><i class="fa fa-times"></i></button>
						</div>
					<?php endif ?> -->
				</div>
			</td>
		</tr>
		<?php $grand_total += $v_data['jml_transfer']; ?>
	<?php endforeach ?>
	<tr>
		<td class="text-right" colspan="3"><b>GRAND TOTAL</b></td>
		<td class="text-right grand_total"><b><?php echo angkaRibuan($grand_total) ?></b></td>
		<td class="text-left" colspan="3"></td>
	</tr>
<?php else: ?>
	<tr>
		<td colspan="7">Data tidak ditemukan.</td>
	</tr>
<?php endif ?>