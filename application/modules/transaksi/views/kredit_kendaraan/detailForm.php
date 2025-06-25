<div class="col-xs-12 no-padding" style="padding-bottom: 10px;">
	<div class="col-xs-7 no-padding" style="padding-right: 15px;">
		<div class="col-xs-2 no-padding"><label class="control-label">ID</label></div>
		<div class="col-xs-4 no-padding">
			<label class="control-label">: <?php echo $data['kode']; ?></label>
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding" style="padding-bottom: 10px;">
	<div class="col-xs-7 no-padding" style="padding-right: 15px;">
		<div class="col-xs-2 no-padding"><label class="control-label">Tanggal</label></div>
		<div class="col-xs-4 no-padding">
			<label class="control-label">: <?php echo strtoupper(tglIndonesia($data['tanggal'], '-', ' ')); ?></label>
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding" style="padding-bottom: 10px;">
	<div class="col-xs-7 no-padding" style="padding-right: 15px;">
		<div class="col-xs-2 no-padding"><label class="control-label">Perusahaan</label></div>
		<div class="col-xs-6 no-padding">
			<label class="control-label">: <?php echo strtoupper($data['d_perusahaan']['perusahaan']); ?></label>
		</div>
	</div>
	<div class="col-xs-5 no-padding" style="padding-left: 5px;">
		<div class="col-xs-4 no-padding"><label class="control-label">Harga</label></div>
		<div class="col-xs-6 no-padding">
			<label class="control-label">: <?php echo angkaDecimal($data['harga']); ?></label>
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding" style="padding-bottom: 10px;">
	<div class="col-xs-7 no-padding" style="padding-right: 15px;">
		<div class="col-xs-2 no-padding"><label class="control-label">Merk & Jenis</label></div>
		<div class="col-xs-10 no-padding">
			<label class="control-label">: <?php echo strtoupper($data['merk_jenis']); ?></label>
		</div>
	</div>
	<div class="col-xs-5 no-padding" style="padding-left: 5px;">
		<div class="col-xs-4 no-padding"><label class="control-label">DP & Angsuran ke 1</label></div>
		<div class="col-xs-6 no-padding">
			<label class="control-label">: <?php echo angkaDecimal($data['dp']); ?></label>
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding" style="padding-bottom: 10px;">
	<div class="col-xs-7 no-padding" style="padding-right: 15px;">
		<div class="col-xs-2 no-padding"><label class="control-label">Warna</label></div>
		<div class="col-xs-4 no-padding">
			<label class="control-label">: <?php echo strtoupper($data['warna']); ?></label>
		</div>
	</div>
	<div class="col-xs-5 no-padding" style="padding-left: 5px;">
		<div class="col-xs-4 no-padding"><label class="control-label">Tgl Bayar</label></div>
		<div class="col-xs-6 no-padding">
			<label class="control-label">
				: <a href="uploads/<?php echo $data['lampiran']; ?>" target="_blank"><?php echo strtoupper(tglIndonesia($data['tgl_bayar'], '-', ' ')); ?></a>
			</label>
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding" style="padding-bottom: 10px;">
	<div class="col-xs-7 no-padding" style="padding-right: 15px;">
		<div class="col-xs-2 no-padding"><label class="control-label">Tahun</label></div>
		<div class="col-xs-2 no-padding">
			<label class="control-label">: <?php echo strtoupper($data['tahun']); ?></label>
		</div>
	</div>
	<div class="col-xs-5 no-padding" style="padding-left: 5px;">
		<div class="col-xs-4 no-padding"><label class="control-label">Angsuran Per Bulan</label></div>
		<div class="col-xs-6 no-padding">
			<label class="control-label">: <?php echo angkaDecimal($data['angsuran']); ?></label>
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding" style="padding-bottom: 10px;">
	<div class="col-xs-7 no-padding" style="padding-right: 15px;">
		<div class="col-xs-2 no-padding"><label class="control-label">Unit</label></div>
		<div class="col-xs-4 no-padding">
			<label class="control-label">: <?php echo strtoupper($data['d_unit']['nama']); ?></label>
		</div>
	</div>
	<div class="col-xs-5 no-padding" style="padding-left: 5px;">
		<div class="col-xs-4 no-padding"><label class="control-label">Tenor (Bulan)</label></div>
		<div class="col-xs-2 no-padding">
			<label class="control-label">: <?php echo strtoupper($data['tenor']); ?></label>
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding" style="padding-bottom: 10px;">
	<div class="col-xs-7 no-padding" style="padding-right: 15px;">
		<div class="col-xs-2 no-padding"><label class="control-label">Peruntukan</label></div>
		<div class="col-xs-8 no-padding">
			<label class="control-label">: <?php echo strtoupper($data['d_peruntukan']['nama']); ?></label>
		</div>
	</div>
	<div class="col-xs-5 no-padding" style="padding-left: 5px;">
		<div class="col-xs-4 no-padding"><label class="control-label">Tgl Jatuh Tempo</label></div>
		<div class="col-xs-6 no-padding">
			<label class="control-label">: <?php echo substr(strtoupper(tglIndonesia($data['tgl_jatuh_tempo'], '-', ' ')), 0, 2); ?></label>
		</div>
	</div>
</div>
<?php
	$hide_bpkb = 'hide';
	if ( $data['lunas'] == 1 ) {
		$hide_bpkb = null;
	}
?>
<div class="col-xs-12 no-padding <?php echo $hide_bpkb; ?>">
	<div class="col-xs-12 no-padding"><hr style="margin-top: 5px; margin-bottom: 5px;"></div>
	<div class="col-xs-7 no-padding" style="padding-right: 15px;">
		<div class="col-xs-2 no-padding"><label class="control-label">BPKB</label></div>
		<div class="col-xs-8 no-padding">
			<label class="control-label">:</label>
			<button type="button" class="btn btn-default" onclick="kk.modalBpkb(this)" data-kode="<?php echo $data['kode']; ?>">BPKB</button>
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
<div class="col-xs-12 no-padding">
	<small>
		<table class="table table-bordered tbl_angsuran" style="margin-bottom: 0px;">
			<thead>
				<tr>
					<th class="col-xs-3">ANGSURAN</th>
					<th class="col-xs-2">JATUH TEMPO</th>
					<th class="col-xs-2">JUMLAH</th>
					<th class="col-xs-2">TANGGAL BAYAR</th>
					<th class="col-xs-1">LAMPIRAN</th>
					<th class="col-xs-2"></th>
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
						<td><?php echo 'ANGSURAN KE '.$v_det['angsuran_ke']; ?></td>
						<td class="tgl_jatuh_tempo" data-val="<?php echo $v_det['tgl_jatuh_tempo']; ?>"><?php echo strtoupper(tglIndonesia($v_det['tgl_jatuh_tempo'], '-', ' ', true)); ?></td>
						<td class="text-right jumlah" data-val="<?php echo $v_det['jumlah_angsuran']; ?>"><?php echo angkaRibuan($v_det['jumlah_angsuran']); ?></td>
						<td>
							<div class="input-group date tgl_bayar">
				                <input type="text" class="form-control uppercase text-center" placeholder="Tanggal" <?php echo $disabled; ?> data-val="<?php echo $v_det['tgl_bayar']; ?>" data-edit="<?php echo $edit; ?>" />
				                <span class="input-group-addon">
				                    <span class="glyphicon glyphicon-calendar"></span>
				                </span>
				            </div>
						</td>
						<td class="text-center">
							<div class="col-xs-12 no-padding attachment">
								<?php
									$hide_lampiran = 'hide';
									$path_lampiran = null;
									if ( !empty($v_det['lampiran']) ) {
										$hide_lampiran = null;
										$path_lampiran = $v_det['lampiran'];
									}

									// $hide_ipt_lampiran = 'hide';
									// if ( $row_isi == 0 && $hide_div_edit == 'hide' ) {
									// 	// $hide_lampiran = null;
									// 	$hide_ipt_lampiran = null;
									// }

									// echo $row_isi;
									// echo '<br>';
									// echo 'save | '.$hide_div_save;
									// echo '<br>';
									// echo 'edit | '.$hide_div_edit;
								?>
								<a name="dokumen" class="text-right <?php echo $hide_lampiran; ?>" target="_blank" style="padding-right: 10px;" href="uploads/<?php echo $path_lampiran; ?>">
									<i class="fa fa-file" style="font-size: 16px;"></i>
								</a>
								<!-- <label class="control-label <?php echo $hide_ipt_lampiran; ?>">
									<input style="display: none;" class="file_lampiran no-check lampiran_angsuran" type="file" data-name="name" onchange="kk.showNameFile(this, 0)" data-key="<?php echo 'ANGSURAN KE '.$v_det['angsuran_ke']; ?>" />
									<i class="fa fa-paperclip cursor-p text-center" title="Lampiran" style="font-size: 20px;"></i> 
								</label> -->
							</div>
						</td>
						<td>
							<?php if ( $akses['a_edit'] == 1 ): ?>
								<div class="col-xs-12 no-padding act_edit <?php echo $hide_div_edit; ?>">
									<button type="button" class="col-xs-12 btn btn-success" onclick="kk.editDetail(this)">Edit</button>
								</div>
								<div class="col-xs-12 no-padding act_save <?php echo $hide_div_save; ?>">
									<div class="col-xs-6 no-padding save <?php echo $hide_save; ?>" style="padding-right: 3px;">
										<button type="button" class="col-xs-12 btn btn-primary" onclick="kk.saveDetail(this)"><i class="fa fa-check"></i></button>
									</div>
									<div class="col-xs-6 no-padding batal <?php echo $hide_batal; ?>" style="padding-left: 3px;">
										<button type="button" class="col-xs-12 btn btn-danger" onclick="kk.batalDetail(this)"><i class="fa fa-times"></i></button>
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
		<button type="button" class="btn btn-primary pull-right" onclick="kk.changeTabActive(this)" data-href="action" data-edit="edit" data-id="<?php echo $data['kode']; ?>"><i class="fa fa-edit"></i> Edit</button>
	<?php endif ?>
	<?php // if ( $akses['a_delete'] == 1 ): ?>
		<button type="button" class="btn btn-danger pull-right" onclick="kk.delete(this)" data-id="<?php echo $data['kode']; ?>" style="margin-right: 10px;"><i class="fa fa-trash"></i> Hapus</button>
	<?php // endif ?>
</div>