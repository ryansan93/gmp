<div class="col-sm-12 row">
	<div class="col-sm-6 no-padding d-flex align-items-center">
		<div class="col-sm-3 text-left no-padding">
			<span>Periode</span>
		</div>
		<div class="col-sm-8 d-flex align-items-center">
			<div class="col-md-1"><span>:</span></div>
			<div class="col-md-6 d-flex align-items-center">
				<span>
					<?php
						$tgl_docin = $data['data_rdim_submit']['tgl_docin'];
						$frmt_tgl = tglIndonesia($tgl_docin, '-', ' ', true);

						echo substr($frmt_tgl, 3);
					?>
				</span>
			</div>
		</div>
	</div>
	<div class="col-sm-6 no-padding">
		<div class="col-sm-2 text-left no-padding">
			<span>Tanggal DOC In</span>
		</div>
		<div class="col-sm-8">
			<div class="col-md-1"><span>:</span></div>
			<div class="col-md-8 tgl_docin">
				<span>
					<?php
						$tgl_docin = $data['data_rdim_submit']['tgl_docin'];
						$frmt_tgl = tglIndonesia($tgl_docin, '-', ' ', true);

						echo $frmt_tgl;
					?>
				</span>
			</div>
		</div>
	</div>
</div>
<div class="col-sm-12 d-flex align-items-center row">
	<div class="col-sm-6 no-padding d-flex align-items-center">
		<div class="col-sm-3 text-left no-padding">
			<span>No. Siklus</span>
		</div>
		<div class="col-sm-8 d-flex align-items-center">
			<div class="col-md-1"><span>:</span></div>
			<div class="col-md-6 d-flex align-items-center">
				<span><?php echo $data['noreg']; ?></span>
			</div>
		</div>
	</div>
	<div class="col-sm-6 no-padding">
		<div class="col-sm-2 text-left no-padding">
			<span>Populasi</span>
		</div>
		<div class="col-sm-3">
			<div class="col-md-1"><span>:</span></div>
			<div class="col-md-8 populasi text-right"><span><?php echo angkaRibuan($data['data_rdim_submit']['populasi']); ?></span></div>
		</div>
		<div class="col-md-1 no-padding">
			<span>Ekor</span>
		</div>
	</div>
</div>
<div class="col-md-12 d-flex align-items-center row">
	<div class="col-md-6 no-padding">
		<div class="col-md-3 text-left no-padding">
			<span>Peternak</span>
		</div>
		<div class="col-md-8">
			<div class="col-md-1"><span>:</span></div>
			<div class="col-md-8 mitra">
				<span>
					<?php 
						echo $data['data_rdim_submit']['mitra']['dMitra']['nama']; 
					?>
				</span>
			</div>
		</div>
	</div>
	<div class="col-md-6 no-padding">
		<div class="col-md-2 text-left no-padding">
			<span>Kebutuhan</span>
		</div>
		<div class="col-md-3">
			<div class="col-md-1"><span>:</span></div>
			<div class="col-md-8 kebutuhan_kg text-right"><span><?php echo angkaRibuan($data['data_rdim_submit']['populasi'] * 3); ?></span></div>
		</div>
		<div class="col-md-1 no-padding">
			<span>Kg</span>
		</div>
		<div class="col-md-3">
			<div class="col-md-1"><span>:</span></div>
			<div class="col-md-8 kebutuhan_zak text-right"><span><?php echo angkaRibuan( ($data['data_rdim_submit']['populasi'] * 3) / 50 ); ?></span></div>
		</div>
		<div class="col-md-1 no-padding">
			<span>Zak</span>
		</div>
	</div>
</div>
<div class="col-sm-12 row">
	<div class="col-sm-6 no-padding">
		<div class="col-sm-3 text-left no-padding">
			<span>Supplier</span>
		</div>
		<div class="col-sm-8">
			<div class="col-md-1"><span>:</span></div>
			<div class="col-md-10 supplier">
				<span>
					<?php echo $data['d_supplier']['nama']; ?>
				</span>
			</div>
		</div>
	</div>
</div>
<div class="col-sm-12 no-padding" style="padding-top: 10px;">
	<table class="table table-bordered list_kpm">
		<thead>
			<tr class="v-center">
				<th class="text-center" rowspan="2">Tanggal</th>
				<th class="text-center" rowspan="2">Umur</th>
				<th class="text-center" colspan="6">Pakan (sak)</th>
				<th class="text-center" rowspan="2">Tanggal</th>
				<th class="text-center" rowspan="2">Umur</th>
				<th class="text-center" colspan="6">Pakan (sak)</th>
			</tr>
			<tr class="v-center">
				<th class="text-center">STD (Gram)</th>
				<th class="text-center">Setting (Gram)</th>
				<th class="text-center">Rcn Kirim (Zak)</th>
				<th class="text-center">Tgl Kirim</th>
				<th class="text-center">Terima</th>
				<th class="text-center">Jns Pakan</th>
				<th class="text-center">STD (Gram)</th>
				<th class="text-center">Setting (Gram)</th>
				<th class="text-center">Rcn Kirim (Zak)</th>
				<th class="text-center">Tgl Kirim</th>
				<th class="text-center">Terima</th>
				<th class="text-center">Jns Pakan</th>
			</tr>
		</thead>
		<tbody>
			<?php if ( count($data_sb) > 0 ): ?>
				<?php $bagi = (ceil(count($data_sb)/2)) - 1; ?>
				<?php for ($i=0; $i <= $bagi; $i++) { ?>
					<?php
						$std_kirim_pakan = ($data_sb[$i]['std_kirim_pakan'] == 0) ? null : $data_sb[$i]['std_kirim_pakan'];
						$rcn_kirim = ($data_sb[$i]['rcn_kirim'] == 0) ? null : $data_sb[$i]['rcn_kirim'];

						if ( $i == -1 ) {
							$idx_bagi = $bagi;
						} else if ( $i == 0 ) {
							$idx_bagi = $bagi + 1;
						} else {
							$idx_bagi = $bagi + $i + 1;
						}

						$umur_bagi = isset($data_sb[$idx_bagi]['umur']) ? $data_sb[$idx_bagi]['umur'] : null;
						$tanggal_bagi = isset($data_sb[$idx_bagi]['tanggal']) ? $data_sb[$idx_bagi]['tanggal'] : null;
						$setting_bagi = !empty($data_sb[$idx_bagi]['setting']) ? angkaRibuan($data_sb[$idx_bagi]['setting']) : null;
						$rcn_kirim_bagi = !empty($data_sb[$idx_bagi]['rcn_kirim']) ? angkaDecimal($data_sb[$idx_bagi]['rcn_kirim']) : null;
						$tgl_kirim_bagi = !empty($data_sb[$idx_bagi]['tgl_kirim']) ? tglIndonesia($data_sb[$idx_bagi]['tgl_kirim'], '-', ' ') : null;
						$jns_pakan_bagi = null;

						if ( isset($data_sb[$idx_bagi]) ) {
							$std_kirim_pakan2 = ($data_sb[$idx_bagi]['std_kirim_pakan'] == 0) ? null : $data_sb[$idx_bagi]['std_kirim_pakan'];
							$rcn_kirim_bagi = ($data_sb[$idx_bagi]['rcn_kirim'] == 0) ? null : $data_sb[$idx_bagi]['rcn_kirim'];
							$jns_pakan_bagi = $data_sb[$idx_bagi]['jns_pakan'];
						} else {
							$std_kirim_pakan2 = null;
						}
					?>
					<tr class="data v-center">
						<td class="col-sm-1 text-center"><?php echo tglIndonesia($data_sb[$i]['tanggal'], '-', ' '); ?></td>
						<td class="text-right"><?php echo $data_sb[$i]['umur']; ?></td>
						<td class="text-right" style="width: 50px;"><?php echo $std_kirim_pakan; ?></td>
						<td class="text-right" style="width: 85px;"><?php echo ( !empty($data_sb[$i]['setting']) ) ? angkaRibuan($data_sb[$i]['setting']) : null; ?></td>
						<td class="text-right"><?php echo ( !empty($data_sb[$i]['rcn_kirim']) ) ? angkaDecimal($data_sb[$i]['rcn_kirim']) : null; ?></td>
						<td class="col-sm-1 text-center"><?php echo tglIndonesia($data_sb[$i]['tgl_kirim'], '-', ' '); ?></td>
						<td class="col-sm-1"></td>
						<td class="col-sm-1"><?php echo $data_sb[$i]['jns_pakan']; ?></td>
						<td class="col-sm-1 text-center"><?php echo !empty($tanggal_bagi) ? tglIndonesia($tanggal_bagi, '-', ' ') : null; ?></td>
						<td class="text-right umur2"><?php echo $umur_bagi; ?></td>
						<td class="text-right pakan1" style="width: 50px;"><?php echo $std_kirim_pakan2; ?></td>
						<td class="text-right setting" style="width: 85px;"><?php echo $setting_bagi; ?></td>
						<td class="col-sm-1 text-right rcn_kirim2" style="width: 85px;"><?php echo $rcn_kirim_bagi; ?></td>
						<td class="col-sm-1 text-center">
							<?php if ( !empty($tanggal_bagi) ): ?>
								<?php echo $tgl_kirim_bagi; ?>
							<?php endif ?>
						</td>
						<td class="col-sm-1"></td>
						<td class="col-sm-1"><?php echo $jns_pakan_bagi; ?></td>
					</tr>
				<?php } ?>
			<?php else : ?>
				<tr>
					<td class="text-center" colspan="13">Data Kosong.</td>
				</tr>
			<?php endif ?>
		</tbody>
	</table>
</div>
<div class="col-md-12 no-padding">
	<hr style="margin-top: 0px;">
</div>
<div class="col-md-12 no-padding">
	<button type="button" class="btn btn-primary pull-right update" data-href="action" data-resubmit="edit" data-id="<?php echo $data['id']; ?>" onclick="kpm.changeTabActive(this)"><i class="fa fa-edit"></i> Update</button>
	<button type="button" class="btn btn-danger pull-right delete" data-href="history" data-id="<?php echo $data['id']; ?>" onclick="kpm.delete_kpm(this)" style="margin-right: 10px;"><i class="fa fa-times"></i> Hapus</button>
</div>
