<?php if ( !empty($data) ): ?>
	<?php foreach ($data as $key => $value): ?>
		<tr class="header" data-id="<?php echo $value['kode']; ?>" data-href="action">
			<td class="text-center btn-row"><i class="fa fa-caret-square-o-right btn-collapse cursor-p" style="font-size: 18px;"></i></td>
			<td class="text-center"><?php echo strtoupper(tglIndonesia($value['tanggal'], '-', ' ')); ?></td>
			<td><?php echo strtoupper($value['d_perusahaan']['perusahaan']); ?></td>
			<td><?php echo strtoupper($value['jenis_kredit']); ?></td>
			<td><?php echo strtoupper($value['bank']); ?></td>
			<td><?php echo strtoupper($value['agunan']); ?></td>
			<?php $tanggal = strtoupper(tglIndonesia($value['tanggal'], '-', ' ')); ?>
			<td class="text-center"><?php echo substr($tanggal, 3, strlen($tanggal)); ?></td>
			<td class="text-right"><?php echo angkaDecimal($value['pokok_pinjaman']); ?></td>
			<td class="text-center"><?php echo $value['tenor']; ?></td>
			<td class="text-right"><?php echo angkaDecimal($value['angsuran']); ?></td>
		</tr>
		<tr class="detail" style="display: none;">
			<td colspan="10" style="background-color: #dedede;">
				<table class="table table-bordered" style="margin-bottom: 0px;">
					<thead>
						<tr>
							<th class="col-xs-4">ANGSURAN</th>
							<th class="col-xs-2">JATUH TEMPO</th>
							<th class="col-xs-2">JUMLAH</th>
							<th class="col-xs-2">TANGGAL BAYAR</th>
						</tr>
					</thead>
					<tbody>
						<?php if ( !empty($value['detail']) ): ?>
							<?php foreach ($value['detail'] as $k_det => $v_det): ?>
								<?php if ( !empty($v_det['tgl_bayar']) ): ?>
									<tr>
										<td><?php echo strtoupper('Angsuran Ke '.$v_det['angsuran_ke']); ?></td>
										<td class="text-center"><?php echo strtoupper(tglIndonesia($v_det['tgl_jatuh_tempo'], '-', ' ')); ?></td>
										<td class="text-right"><?php echo angkaDecimal($v_det['jumlah_angsuran']); ?></td>
										<td class="text-center"><?php echo !empty($v_det['tgl_bayar']) ? strtoupper(tglIndonesia($v_det['tgl_bayar'], '-', ' ')) : '-'; ?></td>
									</tr>
								<?php endif ?>
							<?php endforeach ?>
						<?php endif ?>
					</tbody>
				</table>
			</td>
		</tr>
	<?php endforeach ?>
<?php else: ?>
	<tr>
		<td colspan="10">Data tidak di temukan.</td>
	</tr>
<?php endif ?>