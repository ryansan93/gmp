<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
	<div class="col-xs-2 no-padding"><label class="control-label text-left">Periode Terima</label></div>
	<div class="col-xs-10 no-padding"><label class="control-label text-left">: <?php echo strtoupper($data['periode']); ?></label></div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
	<div class="col-xs-2 no-padding"><label class="control-label text-left">Ekspedisi</label></div>
	<div class="col-xs-10 no-padding"><label class="control-label text-left">: <?php echo strtoupper($data['ekspedisi']); ?></label></div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
	<div class="col-xs-2 no-padding"><label class="control-label text-left">Perusahaan</label></div>
	<div class="col-xs-10 no-padding"><label class="control-label text-left">: <?php echo strtoupper($data['nama_perusahaan']); ?></label></div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
	<div class="col-xs-2 no-padding"><label class="control-label text-left">Bank / Rekening</label></div>
	<div class="col-xs-10 no-padding"><label class="control-label text-left">: <?php echo $data['bank'].' / '.$data['rekening']; ?></label></div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
	<div class="col-xs-2 no-padding"><label class="control-label text-left">No. Invoice</label></div>
	<div class="col-xs-10 no-padding"><label class="control-label text-left">: <a href="uploads/<?php echo $data['lampiran']; ?>" target="_blank"><?php echo $data['invoice']; ?></a></label></div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
	<div class="col-xs-2 no-padding"><label class="control-label text-left">Sub Total</label></div>
	<div class="col-xs-10 no-padding"><label class="control-label text-left">: <?php echo angkaDecimal($data['sub_total']); ?></label></div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
	<div class="col-xs-2 no-padding"><label class="control-label text-left">Potongan PPH 23</label></div>
	<div class="col-xs-10 no-padding"><label class="control-label text-left">: <?php echo angkaDecimal($data['potongan_pph_23']); ?></label></div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
	<div class="col-xs-2 no-padding"><label class="control-label text-left">Biaya Materai</label></div>
	<div class="col-xs-10 no-padding"><label class="control-label text-left">: <?php echo angkaDecimal($data['materai']); ?></label></div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
	<div class="col-xs-2 no-padding"><label class="control-label text-left">Grand Total</label></div>
	<div class="col-xs-10 no-padding"><label class="control-label text-left">: <?php echo angkaDecimal($data['total']); ?></label></div>
</div>
<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
	<small>
		<table class="table table-bordered" style="margin-bottom: 0px;">
			<thead>
				<tr>
					<th class="col-xs-1">Tgl Terima</th>
					<th class="col-xs-2">Ekspedisi</th>
					<th class="col-xs-1">No. Polisi</th>
					<th class="col-xs-1">No. SJ</th>
					<th class="col-xs-2">Asal</th>
					<th class="col-xs-2">Tujuan</th>
					<th class="col-xs-1">Sub Total</th>
				</tr>
			</thead>
			<tbody>
				<?php if ( count($data['detail']) > 0 ): ?>
					<?php $grand_total = 0; ?>
					<?php foreach ($data['detail'] as $k_data => $v_data): ?>
						<tr class="search">
							<td class="tgl_mutasi" data-val="<?php echo $v_data['tgl_mutasi']; ?>"><?php echo tglIndonesia($v_data['tgl_mutasi'], '-', ' '); ?></td>
							<td class="ekspedisi" data-val="<?php echo $v_data['ekspedisi']; ?>"><?php echo $v_data['ekspedisi']; ?></td>
							<td class="no_polisi" data-val="<?php echo $v_data['no_polisi']; ?>"><?php echo $v_data['no_polisi']; ?></td>
							<td class="no_sj" data-val="<?php echo $v_data['no_sj']; ?>"><?php echo $v_data['no_sj']; ?></td>
							<td><?php echo $v_data['asal']; ?></td>
							<td><?php echo $v_data['tujuan']; ?></td>
							<td class="text-right sub_total" data-val="<?php echo $v_data['sub_total']; ?>"><?php echo angkaDecimal($v_data['sub_total']); ?></td>
						</tr>

						<?php $grand_total += $v_data['sub_total']; ?>
					<?php endforeach ?>
					<tr>
						<td class="text-right" colspan="6"><b>Total</b></td>
						<td class="text-right grand_total" data-val="<?php echo $grand_total; ?>"><?php echo angkaDecimal($grand_total); ?></td>
					</tr>
				<?php else: ?>
					<tr>
						<td colspan="7">Data tidak ditemukan.</td>
					</tr>
				<?php endif ?>
			</tbody>
		</table>
	</small>
</div>
<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
<?php if ( empty($data['d_realisasi']) ): ?>
	<div class="col-xs-12 no-padding">
		<?php if ( $akses['a_edit'] == 1 ): ?>
			<button type="button" class="btn btn-primary pull-right" onclick="kpoap.changeTabActive(this)" data-id="<?php echo $data['id']; ?>" data-href="transaksi" data-edit="edit"><i class="fa fa-edit"></i> Edit</button>
		<?php endif ?>
		<?php if ( $akses['a_delete'] == 1 ): ?>
			<button type="button" class="btn btn-danger pull-right" onclick="kpoap.delete(this)" data-id="<?php echo $data['id']; ?>" style="margin-right: 10px;"><i class="fa fa-trash"></i> Hapus</button>
		<?php endif ?>
	</div>
<?php endif ?>