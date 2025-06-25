<div class="col-sm-12 row d-flex align-items-center">
	<?php
		$tgl_docin = $data['data_rdim_submit']['tgl_docin'];
		$frmt_tgl = tglIndonesia($tgl_docin, '-', ' ', true);
	?>
	<div class="col-sm-6 no-padding d-flex align-items-center">
		<div class="col-sm-3 text-left no-padding">
			<span>Periode</span>
		</div>
		<div class="col-sm-8 d-flex align-items-center">
			<div class="col-md-1"><span>:</span></div>
			<div class="col-md-6 d-flex align-items-center">
				<div class="input-group date" id="datetimepicker1" name="tanggal-berlaku" data-tgl="<?php echo $tgl_docin; ?>">
			        <input type="text" class="form-control text-center" data-required="1" onblur="kpm.get_noreg()" placeholder="Tanggal" />
			        <span class="input-group-addon">
			            <span class="glyphicon glyphicon-calendar"></span>
			        </span>
			    </div>
			</div>
		</div>
	</div>
	<div class="col-sm-6 no-padding">
		<div class="col-sm-2 text-left no-padding">
			<span>Tanggal DOC In</span>
		</div>
		<div class="col-sm-8">
			<div class="col-md-1"><span>:</span></div>
			<div class="col-md-8 tgl_docin"><span><?php echo $frmt_tgl; ?></span></div>
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
				<select class="form-control noreg" onchange="kpm.set_data_rdim(this)" data-noreg="<?php echo $data['noreg']; ?>" data-required="1">
					<option value="">-- Pilih No. Siklus --</option>
				</select>
			</div>
		</div>
	</div>
	<div class="col-sm-6 no-padding">
		<div class="col-sm-2 text-left no-padding">
			<span>Populasi</span>
		</div>
		<div class="col-sm-3">
			<div class="col-md-1"><span>:</span></div>
			<div class="col-md-8 populasi text-right"><span>-</span></div>
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
			<div class="col-md-8 mitra"><span>-</span></div>
		</div>
	</div>
	<div class="col-md-6 no-padding">
		<div class="col-md-2 text-left no-padding">
			<span>Kebutuhan</span>
		</div>
		<div class="col-md-3">
			<div class="col-md-1"><span>:</span></div>
			<div class="col-md-8 kebutuhan_kg text-right"><span>-</span></div>
		</div>
		<div class="col-md-1 no-padding">
			<span>Kg</span>
		</div>
		<div class="col-md-3">
			<div class="col-md-1"><span>:</span></div>
			<div class="col-md-8 kebutuhan_zak text-right"><span>-</span></div>
		</div>
		<div class="col-md-1 no-padding">
			<span>Zak</span>
		</div>
	</div>
</div>
<div class="col-sm-12 row d-flex align-items-center">
	<div class="col-sm-6 no-padding d-flex align-items-center">
		<div class="col-sm-3 text-left no-padding">
			<span>Supplier</span>
		</div>
		<div class="col-sm-8 d-flex align-items-center">
			<div class="col-md-1"><span>:</span></div>
			<div class="col-md-8 d-flex align-items-center">
				<select class="form-control supplier">
					<option value="">Supplier</option>
					<?php foreach ($supplier as $k_supl => $v_supl): ?>
						<?php
							$selected = null;
							if ( $data['supplier'] == $v_supl['nomor'] ) {
								$selected = 'selected';
							}
						?>
						<option value="<?php echo $v_supl['nomor']; ?>" <?php echo $selected; ?> ><?php echo $v_supl['nama']; ?></option>
					<?php endforeach ?>
				</select>
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
				<th class="text-center" colspan="6">Pakan <span class="nama_pakan"></span></th>
				<th class="text-center" rowspan="2">Tanggal</th>
				<th class="text-center" rowspan="2">Umur</th>
				<th class="text-center" colspan="6">Pakan <span class="nama_pakan"></span></th>
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
			<tr>
				<td class="text-center" colspan="15">Data Kosong.</td>
			</tr>
		</tbody>
	</table>
</div>
<div class="col-sm-12 no-padding">
	<hr style="margin-top: 0px;">
</div>
<div class="col-sm-12 no-padding">
	<button type="button" class="btn btn-primary pull-right edit" data-href="action" data-id="<?php echo $data['id']; ?>" onclick="kpm.edit_kpm(this)"><i class="fa fa-save"></i> Edit</button>
	<button type="button" class="btn btn-danger pull-right batal" data-href="action" data-id="<?php echo $data['id']; ?>" data-resubmit="" onclick="kpm.changeTabActive(this)" style="margin-right: 10px;" ><i class="fa fa-times"></i> Batal</button>
</div>