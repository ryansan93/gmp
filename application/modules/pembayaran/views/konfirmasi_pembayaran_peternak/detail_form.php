<div class="col-xs-12 no-padding">
	<div class="col-xs-2 no-padding"><label class="control-label text-left">Periode Tutup Siklus</label></div>
	<div class="col-xs-10 no-padding"><label class="control-label text-left">: <?php echo $data['periode']; ?></label></div>
</div>
<div class="col-xs-12 no-padding">
	<div class="col-xs-2 no-padding"><label class="control-label text-left">Peternak</label></div>
	<div class="col-xs-10 no-padding"><label class="control-label text-left">: <?php echo strtoupper($data['d_mitra']['nama']); ?></label></div>
</div>
<div class="col-xs-12 no-padding">
	<div class="col-xs-2 no-padding"><label class="control-label text-left">Perusahaan</label></div>
	<div class="col-xs-10 no-padding"><label class="control-label text-left">: <?php echo strtoupper($data['d_perusahaan']['perusahaan']); ?></label></div>
</div>
<div class="col-xs-12 no-padding">
	<div class="col-xs-2 no-padding"><label class="control-label text-left">Tgl Bayar</label></div>
	<div class="col-xs-10 no-padding"><label class="control-label text-left">: <?php echo strtoupper(tglIndonesia($data['tgl_bayar'], '-', ' ', true)); ?></label></div>
</div>
<div class="col-xs-12 no-padding">
	<div class="col-xs-2 no-padding"><label class="control-label text-left">No. Bayar</label></div>
	<div class="col-xs-10 no-padding"><label class="control-label text-left">: <?php echo strtoupper($data['nomor']); ?></label></div>
</div>
<div class="col-xs-12 no-padding">
	<div class="col-xs-2 no-padding"><label class="control-label text-left">Lampiran</label></div>
	<div class="col-xs-10 no-padding"><label class="control-label text-left">: <a href="uploads/<?php echo $data['lampiran']; ?>" target="_blank"><?php echo $data['lampiran']; ?></a></label></div>
</div>
<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
	<small>
		<table class="table table-bordered" style="margin-bottom: 0px;">
			<thead>
				<tr>
					<td colspan="5" class="text-right"><b>Total</b></td>
					<td class="text-right total"><b><?php echo angkaRibuan($data['total']); ?></b></td>
				</tr>
				<tr>
					<th class="col-xs-1">Jenis</th>
					<th class="col-xs-1">No. Invoice</th>
					<th class="col-xs-1">Tgl Doc In</th>
					<th class="col-xs-2">No. Reg</th>
					<th class="col-xs-1">Kandang</th>
					<th class="col-xs-2">Populasi</th>
					<th class="col-xs-3">Sub Total</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($data['detail'] as $k_det => $v_det): ?>
					<?php
						$tgl_docin = '';
                        $noreg = '';
                        $kandang = '';
                        $populasi = '';

                        $jml_noreg = 1;
                        foreach ($v_det['detail2'] as $k_det2 => $v_det2) {
                            $tgl_docin .= tglIndonesia($v_det2['tgl_docin'], '-', ' ');
                            $noreg .= $v_det2['noreg'];
                            $kandang .= $v_det2['kandang'];
                            $populasi .= angkaRibuan($v_det2['populasi']);
                            if ( count($v_det['detail2']) > $jml_noreg ) {
                                $tgl_docin .= '<br>';
                                $noreg .= '<br>';
                                $kandang .= '<br>';
                                $populasi .= '<br>';
                            }

                            $jml_noreg++;
                        }
					?>

					<tr>
						<td class="text-center"><?php echo $v_det['jenis']; ?></td>
						<td><?php echo !empty($data['invoice']) ? $data['invoice'] : '-'; ?></td>
						<td><?php echo $tgl_docin; ?></td>
						<td class="text-center"><?php echo $noreg; ?></td>
						<td class="text-center"><?php echo $kandang; ?></td>
						<td class="text-right"><?php echo $populasi; ?></td>
						<td class="text-right"><?php echo angkaRibuan($v_det['sub_total']); ?></td>
					</tr>
				<?php endforeach ?>
			</tbody>
		</table>
	</small>
</div>
<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
<?php if ( empty($data['d_realisasi']) ): ?>
	<div class="col-xs-12 no-padding">
		<?php if ( $akses['a_edit'] == 1 ): ?>
			<button type="button" class="btn btn-primary pull-right" onclick="kpp.changeTabActive(this)" data-id="<?php echo $data['id']; ?>" data-href="transaksi" data-edit="edit"><i class="fa fa-edit"></i> Edit</button>
		<?php endif ?>
		<?php if ( $akses['a_delete'] == 1 ): ?>
			<button type="button" class="btn btn-danger pull-right" onclick="kpp.delete(this)" data-id="<?php echo $data['id']; ?>" style="margin-right: 10px;"><i class="fa fa-trash"></i> Hapus</button>
		<?php endif ?>
	</div>
<?php endif ?>