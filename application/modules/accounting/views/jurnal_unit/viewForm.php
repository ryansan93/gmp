<div class="col-xs-12 no-padding">
	<div class="col-xs-1 no-padding"><label class="control-label">Tanggal</label></div>
	<div class="col-xs-1 no-padding text-center" style="max-width: 2%;"><label class="control-label">:</label></div>
	<div class="col-xs-10 no-padding"><label class="control-label"><?php echo strtoupper(tglIndonesia($data['tanggal'], '-', ' ')); ?></label></div>
</div>
<div class="col-xs-12 no-padding">
	<div class="col-xs-1 no-padding"><label class="control-label">Unit</label></div>
	<div class="col-xs-1 no-padding text-center" style="max-width: 2%;"><label class="control-label">:</label></div>
	<?php
		$unit = null;
		if ( !empty($data['nama_unit']) ) {
			$unit = str_replace('kab ', '', $data['nama_unit']);
        	$unit = str_replace('kota ', '', $unit);
		} else {
			if ( $data['unit'] == 'pusat_gml' ) {
				$unit = strtoupper('pusat gemilang');
			} else if ( $data['unit'] == 'pusat' ) {
				$unit = strtoupper('pusat gemuk');
			} else if ( $data['unit'] == 'pusat_ma' ) {
				$unit = strtoupper('pusat ma');
			}
		}
	?>
	<div class="col-xs-10 no-padding"><label class="control-label"><?php echo strtoupper($unit); ?></label></div>
</div>
<div class="col-xs-12 no-padding">
	<div class="col-xs-1 no-padding"><label class="control-label">Perusahaan</label></div>
	<div class="col-xs-1 no-padding text-center" style="max-width: 2%;"><label class="control-label">:</label></div>
	<div class="col-xs-10 no-padding"><label class="control-label"><?php echo strtoupper($data['nama_perusahaan']); ?></label></div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
	<div class="col-xs-1 no-padding"><label class="control-label">Transaksi</label></div>
	<div class="col-xs-1 no-padding text-center" style="max-width: 2%;"><label class="control-label">:</label></div>
	<div class="col-xs-10 no-padding"><label class="control-label"><?php echo strtoupper($data['nama_jurnal_trans']); ?></label></div>
</div>
<?php if ( isset($data['plasma']) && !empty($data['plasma']) ): ?>
	<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
		<div class="col-xs-1 no-padding"><label class="control-label">Plasma</label></div>
		<div class="col-xs-1 no-padding text-center" style="max-width: 2%;"><label class="control-label">:</label></div>
		<div class="col-xs-10 no-padding"><label class="control-label"><?php echo strtoupper($data['plasma']['tgl_terima'].' | '.'KDG : '.$data['plasma']['kandang'].' | '.$data['plasma']['nama_mitra']); ?></label></div>
	</div>
<?php endif ?>
<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
	<small>
		<table class="table table-bordered" style="margin-bottom: 0px;">
			<thead>
				<tr>
					<th class="text-center col-xs-12">Detail Transaksi</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($data['detail'] as $k_det => $v_det): ?>
					<tr>
						<td style="padding: 10px;">
							<div class="col-xs-12 no-padding">
								<div class="col-xs-2 no-padding"><label class="control-label" style="padding-top: 0px;">Tanggal</label></div>
								<div class="col-xs-1 no-padding text-center" style="max-width: 2%;"><label class="control-label" style="padding-top: 0px;">:</label></div>
								<div class="col-xs-9 no-padding"><label class="control-label" style="padding-top: 0px;"><?php echo strtoupper(tglIndonesia($v_det['tanggal'], '-', ' ')); ?></label></div>
							</div>
							<div class="col-xs-12 no-padding">
								<div class="col-xs-2 no-padding"><label class="control-label">Detail Transaksi</label></div>
								<div class="col-xs-1 no-padding text-center" style="max-width: 2%;"><label class="control-label">:</label></div>
								<div class="col-xs-9 no-padding"><label class="control-label"><?php echo strtoupper($v_det['nama_det_jurnal_trans']); ?></label></div>
							</div>
							<div class="col-xs-12 no-padding">
								<div class="col-xs-2 no-padding"><label class="control-label">PiC</label></div>
								<div class="col-xs-1 no-padding text-center" style="max-width: 2%;"><label class="control-label">:</label></div>
								<div class="col-xs-9 no-padding"><label class="control-label"><?php echo strtoupper($v_det['pic']); ?></label></div>
							</div>
							<div class="col-xs-12 no-padding hide">
								<div class="col-xs-2 no-padding"><label class="control-label">Rek Asal</label></div>
								<div class="col-xs-1 no-padding text-center" style="max-width: 2%;"><label class="control-label">:</label></div>
								<div class="col-xs-9 no-padding sumber_coa"><label class="control-label"><?php echo strtoupper($v_det['asal']); ?></label></div>
							</div>
							<div class="col-xs-12 no-padding hide">
								<div class="col-xs-2 no-padding"><label class="control-label">Rek Tujuan</label></div>
								<div class="col-xs-1 no-padding text-center" style="max-width: 2%;"><label class="control-label">:</label></div>
								<div class="col-xs-9 no-padding tujuan_coa"><label class="control-label"><?php echo strtoupper($v_det['tujuan']); ?></label></div>
							</div>
							<div class="col-xs-12 no-padding">
								<div class="col-xs-2 no-padding"><label class="control-label">Nominal</label></div>
								<div class="col-xs-1 no-padding text-center" style="max-width: 2%;"><label class="control-label">:</label></div>
								<div class="col-xs-9 no-padding"><label class="control-label"><?php echo angkaDecimal($v_det['nominal']); ?></label></div>
							</div>
							<div class="col-xs-12 no-padding">
								<div class="col-xs-2 no-padding"><label class="control-label">Keterangan</label></div>
								<div class="col-xs-1 no-padding text-center" style="max-width: 2%;"><label class="control-label">:</label></div>
								<div class="col-xs-9 no-padding"><label class="control-label"><?php echo angkaDecimal($v_det['keterangan']); ?></label></label></div>
							</div>
						</td>
					</tr>
				<?php endforeach ?>
			</tbody>
		</table>
	</small>
</div>
<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
<div class="col-xs-12 no-padding">
	<div class="col-xs-6 no-padding" style="padding-right: 5px;">
		<button type="button" class="col-xs-12 btn btn-danger pull-right" onclick="ju.delete(this)" data-id="<?php echo $data['id']; ?>"><i class="fa fa-trash"></i> Hapus</button>
	</div>
	<div class="col-xs-6 no-padding" style="padding-left: 5px;">
		<button type="button" class="col-xs-12 btn btn-primary pull-right" onclick="ju.changeTabActive(this)" data-id="<?php echo $data['id']; ?>" data-href="action" data-edit="edit"><i class="fa fa-edit"></i> Edit</button>
	</div>
</div>