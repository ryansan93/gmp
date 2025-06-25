<div class="col-xs-12 no-padding" style="padding-bottom: 10px;">
	<div class="col-xs-7 no-padding" style="padding-right: 15px;">
		<div class="col-xs-4 no-padding"><label class="control-label">ID</label></div>
		<div class="col-xs-4 no-padding">
			<label class="control-label">: <?php echo $data['kode']; ?></label>
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding" style="padding-bottom: 10px;">
	<div class="col-xs-7 no-padding" style="padding-right: 15px;">
		<div class="col-xs-4 no-padding"><label class="control-label">Tanggal</label></div>
		<div class="col-xs-4 no-padding">
			<label class="control-label">: <?php echo strtoupper(tglIndonesia($data['tanggal'], '-', ' ')); ?></label>
		</div>
	</div>
	<div class="col-xs-5 no-padding" style="padding-left: 5px;">
		<div class="col-xs-5 no-padding"><label class="control-label">Pokok Pinjaman</label></div>
		<div class="col-xs-6 no-padding">
			<label class="control-label">: <?php echo angkaDecimal($data['pokok_pinjaman']); ?></label>
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding" style="padding-bottom: 10px;">
	<div class="col-xs-7 no-padding" style="padding-right: 15px;">
		<div class="col-xs-4 no-padding"><label class="control-label">Perusahaan</label></div>
		<div class="col-xs-6 no-padding">
			<label class="control-label">: <?php echo strtoupper($data['d_perusahaan']['perusahaan']); ?></label>
		</div>
	</div>
	<div class="col-xs-5 no-padding" style="padding-left: 5px;">
		<div class="col-xs-5 no-padding"><label class="control-label">Bunga</label></div>
		<div class="col-xs-6 no-padding">
			<label class="control-label">: <?php echo angkaDecimal($data['bunga']); ?></label>
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding" style="padding-bottom: 10px;">
	<div class="col-xs-7 no-padding" style="padding-right: 15px;">
		<div class="col-xs-4 no-padding"><label class="control-label">Jenis Kredit</label></div>
		<div class="col-xs-8 no-padding">
			<label class="control-label">: <?php echo strtoupper($data['jenis_kredit']); ?></label>
		</div>
	</div>
	<div class="col-xs-5 no-padding" style="padding-left: 5px;">
		<div class="col-xs-5 no-padding"><label class="control-label">Bunga Per Tahun (%)</label></div>
		<div class="col-xs-4 no-padding">
			<label class="control-label">: <?php echo angkaDecimal($data['bunga_per_tahun']); ?></label>
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding" style="padding-bottom: 10px;">
	<div class="col-xs-7 no-padding" style="padding-right: 15px;">
		<div class="col-xs-4 no-padding"><label class="control-label">Bank Pemberi Pinjaman</label></div>
		<div class="col-xs-8 no-padding">
			<label class="control-label">: <?php echo strtoupper($data['bank']); ?></label>
		</div>
	</div>
	<div class="col-xs-5 no-padding" style="padding-left: 5px;">
		<div class="col-xs-5 no-padding"><label class="control-label">Tenor (Bulan)</label></div>
		<div class="col-xs-4 no-padding">
			<label class="control-label">: <?php echo $data['tenor']; ?></label>
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding" style="padding-bottom: 10px;">
	<div class="col-xs-7 no-padding" style="padding-right: 15px;">
		<div class="col-xs-4 no-padding"><label class="control-label">Agunan</label></div>
		<div class="col-xs-8 no-padding">
			<label class="control-label">: <?php echo strtoupper($data['agunan']); ?></label>
		</div>
	</div>
	<div class="col-xs-5 no-padding" style="padding-left: 5px;">
		<div class="col-xs-5 no-padding"><label class="control-label">Pokok + Bunga</label></div>
		<div class="col-xs-6 no-padding">
			<label class="control-label">: <?php echo angkaDecimal($data['angsuran']); ?></label>
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding" style="padding-bottom: 10px;">
	<div class="col-xs-7 no-padding" style="padding-right: 15px;">
		<div class="col-xs-4 no-padding"><label class="control-label">No. Dokumen</label></div>
		<div class="col-xs-8 no-padding">
			<label class="control-label">: <?php echo strtoupper($data['no_dokumen']); ?></label>
		</div>
	</div>
	<div class="col-xs-5 no-padding" style="padding-left: 5px;">
		<div class="col-xs-5 no-padding"><label class="control-label">Tgl Jatuh Tempo</label></div>
		<div class="col-xs-6 no-padding">
			<label class="control-label">: <?php echo strtoupper(tglIndonesia($data['tgl_jatuh_tempo'], '-', ' ')); ?></label>
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
<div class="col-xs-12 no-padding">
	<small>
		<table class="table table-bordered tbl_angsuran" style="margin-bottom: 0px;">
			<thead>
				<tr>
					<th colspan="2">ANGSURAN</th>
					<th class="col-xs-1">POKOK</th>
					<th class="col-xs-1">BUNGA</th>
					<th class="col-xs-2">JATUH TEMPO</th>
					<th class="col-xs-2">TANGGAL BAYAR</th>
					<th class="col-xs-1"></th>
				</tr>
			</thead>
			<tbody>
				<?php $row_isi = 0; ?>
				<?php foreach ($data['detail'] as $k_det => $v_det): ?>
					<?php 
						$edit = null;
						$disabled = null;
						$hide_div_edit = null;
						$hide_div_save = null;
						$hide_save = null;
						$hide_batal = null;
						if ( !empty($v_det['tgl_bayar']) ) {
							$disabled = 'disabled';
							$hide_div_save = 'hide';
							$edit = 'edit';
						} else {
							$disabled = 'disabled';
							$hide_div_edit = 'hide';
							if ( $row_isi == 0 ) {
								$disabled = null;
								$hide_batal = 'hide';
								$edit = 'edit';
							} else {
								$hide_div_edit = 'hide';
								$hide_div_save = 'hide';
							}
						}
					?>

					<tr class="data" data-kode="<?php echo $data['kode']; ?>" data-no="<?php echo $v_det['angsuran_ke']; ?>">
						<td class="col-xs-1"><?php echo 'ANGSURAN KE '.$v_det['angsuran_ke']; ?></td>
						<td class="col-xs-1 text-right jumlah" data-val="<?php echo $v_det['jumlah_angsuran']; ?>"><?php echo angkaRibuan($v_det['jumlah_angsuran']); ?></td>
						<td class="text-right pokok" data-val="<?php echo $v_det['jumlah_angsuran_pokok']; ?>"><?php echo angkaRibuan($v_det['jumlah_angsuran_pokok']); ?></td>
						<td class="text-right bunga" data-val="<?php echo $v_det['jumlah_angsuran_bunga']; ?>"><?php echo angkaRibuan($v_det['jumlah_angsuran_bunga']); ?></td>
						<td class="col-xs-1 tgl_jatuh_tempo" data-val="<?php echo $v_det['tgl_jatuh_tempo']; ?>"><?php echo strtoupper(tglIndonesia($v_det['tgl_jatuh_tempo'], '-', ' ', true)); ?></td>
						<td>
							<div class="input-group date tgl_bayar">
				                <input type="text" class="form-control uppercase text-center" placeholder="Tanggal" <?php echo $disabled; ?> data-val="<?php echo $v_det['tgl_bayar']; ?>" data-edit="<?php echo $edit; ?>" />
				                <span class="input-group-addon">
				                    <span class="glyphicon glyphicon-calendar"></span>
				                </span>
				            </div>
						</td>
						<td>
							<?php if ( $akses['a_edit'] == 1 ): ?>
								<div class="col-xs-12 no-padding act_edit <?php echo $hide_div_edit; ?>">
									<button type="button" class="col-xs-12 btn btn-success" onclick="kb.editDetail(this)">Edit</button>
								</div>
								<div class="col-xs-12 no-padding act_save <?php echo $hide_div_save; ?>">
									<div class="col-xs-6 no-padding save <?php echo $hide_save; ?>" style="padding-right: 3px;">
										<button type="button" class="col-xs-12 btn btn-primary" onclick="kb.saveDetail(this)"><i class="fa fa-check" style="margin-left: -2px;"></i></button>
									</div>
									<div class="col-xs-6 no-padding batal <?php echo $hide_batal; ?>" style="padding-left: 3px;">
										<button type="button" class="col-xs-12 btn btn-danger" onclick="kb.batalDetail(this)"><i class="fa fa-times" style="margin-left: -1px;"></i></button>
									</div>
								</div>
							<?php endif ?>
						</td>

						<?php
							if ( empty($v_det['tgl_bayar']) ) {
								$row_isi++;
							}
						?>
					</tr>
				<?php endforeach ?>
			</tbody>
		</table>
	</small>
</div>
<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
<div class="col-xs-12 no-padding">
	<?php if ( $akses['a_edit'] == 1 ): ?>
		<button type="button" class="btn btn-primary pull-right" onclick="kb.changeTabActive(this)" data-href="action" data-edit="edit" data-id="<?php echo $data['kode']; ?>"><i class="fa fa-edit"></i> Edit</button>
	<?php endif ?>
	<?php if ( $akses['a_delete'] == 1 ): ?>
		<button type="button" class="btn btn-danger pull-right" onclick="kb.delete(this)" data-id="<?php echo $data['kode']; ?>" style="margin-right: 10px;"><i class="fa fa-trash"></i> Hapus</button>
	<?php endif ?>
</div>