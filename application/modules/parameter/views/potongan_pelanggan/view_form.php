<div class="col-md-12">
	<form class="form form-horizontal" role="form">
		<div name="data-mitra">
			<div class="form-group align-items-center d-flex">
				<label class="label-control col-sm-2">Pelanggan</label>
				<div class="col-sm-10">
					<label class="label-control">: <?php echo strtoupper($data['d_pelanggan']['nama']); ?></label>
				</div>
			</div>
			<div class="form-group align-items-center d-flex">
				<label class="label-control col-sm-2">Potongan (%)</label>
				<div class="col-sm-10">
					<label class="label-control">: <?php echo angkaDecimal($data['potongan_persen']); ?></label>
				</div>
			</div>
			<div class="form-group align-items-center d-flex">
				<label class="label-control col-sm-2">Mulai</label>
				<div class="col-sm-10">
					<label class="label-control">: <?php echo tglIndonesia($data['start_date'], '-', ' ', true); ?></label>
				</div>
			</div>
			<div class="form-group align-items-center d-flex">
				<label class="label-control col-sm-2">Berakhir</label>
				<div class="col-sm-10">
					<label class="label-control">: <?php echo tglIndonesia($data['end_date'], '-', ' ', true); ?></label>
				</div>
			</div>
			<div class="form-group align-items-center d-flex">
				<label class="label-control col-sm-2">Aktif</label>
				<div class="col-sm-10">
					<label class="label-control">: <?php echo ($data['aktif'] == 1) ? 'AKTIF' : 'NON AKTIF'; ?></label>
				</div>
			</div>
		</div>
	</form>
	<hr>
	<div class="col-md-12 no-padding">
		<button type="button" class="btn btn-large btn-primary pull-right" onclick="pp.changeTabActive(this)" data-href="action" data-id="<?php echo $data['id']; ?>" data-resubmit="edit"><i class="fa fa-edit"></i> Edit</button>
		<button type="button" class="btn btn-large btn-danger pull-right" onclick="pp.delete(this)" data-id="<?php echo $data['id']; ?>" style="margin-right: 10px;"><i class="fa fa-times"></i> Hapus</button>
	</div>
</div>