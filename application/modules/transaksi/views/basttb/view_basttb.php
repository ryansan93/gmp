<div class="panel-body no-padding">
	<div class="panel-body">
		<div class="row new-line">
			<div class="col-sm-12">
				<div class="col-md-1 text-left no-padding">
					<h5>Periode RDIM</h5>
				</div>
				<div class="col-md-2">
					<h5>:&nbsp&nbsp&nbsp<?php echo substr(tglIndonesia($data['dRdimSubmit']['tgl_docin'], '-', ', ', true), 4, 20) ?></h5>
				</div>
			</div>
		</div>
		<div class="row new-line">
			<div class="col-sm-12">
				<div class="col-md-1 text-left no-padding">
					<h5>Noreg</h5>
				</div>
				<div class="col-md-2">
					<h5>:&nbsp&nbsp&nbsp<?php echo $data['noreg']; ?></h5>
				</div>
			</div>
		</div>
		<div class="row new-line">
			<div class="col-sm-12">
				<div class="col-md-1 text-left no-padding">
					<h5>Nama Mitra</h5>
				</div>
				<div class="col-md-4">
					<h5>:&nbsp&nbsp&nbsp<?php echo $data['dRdimSubmit']['dMitraMapping']['dMitra']['nama']; ?></h5>
				</div>
			</div>
		</div>
		<hr>
		<div class="row new-line">
			<div class="col-sm-12">
				<div class="col-md-1 text-left no-padding">
					<h5>Tanggal Terima</h5>
				</div>
				<div class="col-md-2">
					<h5>:&nbsp&nbsp&nbsp<?php echo tglIndonesia($data['tgl_terima'], '-', ' ', true); ?></h5>
				</div>
			</div>
		</div>
		<div class="row new-line">
			<div class="col-sm-12">
				<div class="col-md-1 text-left no-padding">
					<h5>No SJ</h5>
				</div>
				<div class="col-md-2">
					<h5>:&nbsp&nbsp&nbsp<?php echo $data['no_sj']; ?></h5>
				</div>
			</div>
		</div>
		<div class="row new-line">
			<div class="col-sm-12">
				<div class="col-md-1 text-left no-padding">
					<h5>Keterangan SJ</h5>
				</div>
				<div class="col-md-5">
					<h5>:&nbsp&nbsp&nbsp<?php echo $data['ket_sj']; ?></h5>
				</div>
			</div>
		</div>
		<hr>
		<div class="row new-line">
			<div class="col-sm-12">
				<small>
					<table class="table table-bordered custom_table">
						<thead>
							<tr>
								<th class="text-center" colspan="2">Jumlah SJ</th>
								<th class="text-center" colspan="6">Jumlah Terima</th>
								<th class="text-center" colspan="2">Selisih</th>
								<th class="text-center" rowspan="2">Keterangan</th>
							</tr>
							<tr>
								<th class="text-center col-sm-1">Box</th>
								<th class="text-center col-sm-1">Ekor</th>
								<th class="text-center col-sm-1">Box</th>
								<th class="text-center col-sm-1">Ekor</th>
								<th class="text-center col-sm-1">Mati</th>
								<th class="text-center col-sm-1">Afkir</th>
								<th class="text-center col-sm-1">Stok Awal</th>
								<th class="text-center col-sm-1">BB</th>
								<th class="text-center col-sm-1">+/-</th>
								<th class="text-center col-sm-1">%</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td class="text-right"><?php echo angkaRibuan($data['sj_box']); ?></td>
								<td class="text-right"><?php echo angkaRibuan($data['sj_ekor']); ?></td>
								<td class="text-right"><?php echo angkaRibuan($data['terima_box']); ?></td>
								<td class="text-right"><?php echo angkaRibuan($data['terima_ekor']); ?></td>
								<td class="text-right"><?php echo angkaRibuan($data['terima_mati']); ?></td>
								<td class="text-right"><?php echo angkaRibuan($data['terima_afkir']); ?></td>
								<td class="text-right"><?php echo angkaRibuan($data['terima_awal']); ?></td>
								<td class="text-right"><?php echo angkaRibuan($data['terima_bb']); ?></td>
								<td class="text-right"><?php echo angkaRibuan($data['selisih_ekor']); ?></td>
								<td class="text-right"><?php echo angkaDecimal( ($data['selisih_ekor'] / $data['sj_ekor']) * 100 ); ?></td>
								<td><?php echo $data['ket_terima']; ?></td>
							</tr>
						</tbody>
					</table>
				</small>
			</div>
		</div>
	</div>
</div>
<!-- <div class="col-lg-12 text-right">
	<button type="button" class="btn btn-primary edit" data-href="action" data-resubmit="edit" data-id="<?php echo $data['id']; ?>" onclick="basttb.changeTabActive(this)"><i class="fa fa-edit"></i> Edit</button>
</div> -->