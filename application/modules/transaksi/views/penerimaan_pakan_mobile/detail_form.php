<div class="row detailed">
	<div class="col-lg-12 detailed">
		<hr style="padding-top: 0px; margin-top: 0px;">
		<form role="form" class="form-horizontal">
			<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
				<div class="col-xs-4 no-padding">
					<label class="control-label">Nama Mitra</label>
				</div>
				<div class="col-xs-8 no-padding">
					<label class="control-label">: <?php echo strtoupper($data['mitra']); ?></label>
				</div>
			</div>

			<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
				<div class="col-xs-4 no-padding">
					<label class="control-label">No. Reg</label>
				</div>
				<div class="col-xs-8 no-padding">
					<label class="control-label">: <?php echo strtoupper($data['noreg']); ?></label>
				</div>
			</div>

			<div class="col-xs-12 no-padding no_sj" data-val="<?php echo $data['no_sj']; ?>" style="margin-bottom: 5px;">
				<div class="col-xs-4 no-padding">
					<label class="control-label">No. SJ</label>
				</div>
				<div class="col-xs-8 no-padding">
					<label class="control-label">: <?php echo strtoupper($data['no_sj']); ?></label>
				</div>
			</div>

			<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
				<div class="col-xs-12 no-padding">
					<label class="control-label">Asal</label>
				</div>
				<div class="col-xs-12 no-padding">
					<input type="text" class="form-control asal uppercase" placeholder="Asal" data-required="1" value="<?php echo strtoupper($data['asal']) ?>" disabled>
				</div>
			</div>

			<div class="col-xs-6" style="padding: 0px 5px 0px 0px; margin-bottom: 5px;">
				<div class="col-xs-12 no-padding">
					<label class="control-label">No. Polisi</label>
				</div>
				<div class="col-xs-12 no-padding">
					<input type="text" class="form-control nopol uppercase" placeholder="No. Polisi" data-required="1" value="<?php echo strtoupper($data['nopol']) ?>" disabled>
				</div>
			</div>

			<div class="col-xs-6" style="padding: 0px 0px 0px 5px; margin-bottom: 5px;">
				<div class="col-xs-12 no-padding">
					<label class="control-label">Sopir</label>
				</div>
				<div class="col-xs-12 no-padding">
					<input type="text" class="form-control sopir uppercase" placeholder="Sopir" data-required="1" value="<?php echo strtoupper($data['sopir']) ?>" disabled>
				</div>
			</div>

			<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
				<div class="col-xs-12 no-padding">
					<label class="control-label">Ekspedisi</label>
				</div>
				<div class="col-xs-12 no-padding">
					<input type="text" class="form-control ekspedisi uppercase" placeholder="Ekspedisi" data-required="1" value="<?php echo strtoupper($data['ekspedisi']) ?>" disabled>
				</div>
			</div>

			<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
				<div class="col-xs-12 no-padding">
					<label class="control-label">Tanggal Tiba</label>
				</div>
				<div class="col-xs-12 no-padding">
					<input type="text" class="form-control text-center" value="<?php echo strtoupper(tglIndonesia($data['tiba'], '-', ' ', true)); ?>" disabled>
				</div>
			</div>
		</form>
	</div>
	<div class="col-xs-12 detailed"><br></div>
	<div class="col-xs-12 detailed">
		<form role="form" class="form-horizontal">
			<div class="col-xs-12 no-padding">
				<label class="control-label"><u>Keterangan PAKAN</u></label>
			</div>
			<div class="col-xs-12 no-padding">
				<table class="table table-bordered data_brg" style="margin-bottom: 0px;">
					<thead>
						<tr>
							<th class="col-xs-4">Nama</th>
							<th class="col-xs-2">Kirim</th>
							<th class="col-xs-3">Terima</th>
							<th class="col-xs-3">Kondisi</th>
						</tr>
					</thead>
					<tbody>
						<?php if ( !empty($data['data_brg']) ): ?>
							<?php foreach ($data['data_brg'] as $k_db => $v_db): ?>
								<tr class="v-center">
									<td><?php echo strtoupper($v_db['nama_brg']); ?></td>
									<td class="text-right"><?php echo angkaRibuan($v_db['jml_kirim']); ?></td>
									<td class="text-right"><?php echo angkaRibuan($v_db['jml_terima']); ?></td>
									<td><?php echo !empty($v_db['kondisi']) ? strtoupper($v_db['kondisi']) : '-'; ?></td>
								</tr>
							<?php endforeach ?>
						<?php else: ?>
							<tr class="v-center">
								<td colspan="4">Data tidak ditemukan.</td>
							</tr>
						<?php endif ?>
					</tbody>
				</table>
			</div>
		</form>
	</div>
	<!-- <div class="col-lg-12 detailed"><hr></div>
	<div class="col-xs-12 detailed">
		<form role="form" class="form-horizontal">
			<div class="col-xs-6 no-padding" style="padding-right: 5px;">
				<button type="button" class="btn btn-primary col-xs-12" onclick="ppm.change_tab(this)" data-id="<?php echo $data['no_sj']; ?>" data-noreg="<?php echo $data['noreg']; ?>" data-nomor="<?php echo $data['nomor']; ?>" data-edit="edit" data-href="transaksi"><i class="fa fa-edit"></i> Edit</button>
			</div>
			<div class="col-xs-6 no-padding" style="padding-left: 5px;">
				<button type="button" class="btn btn-danger col-xs-12" onclick="ppm.delete()"><i class="fa fa-trash"></i> Hapus</button>
			</div>
		</form>
	</div> -->
</div>
