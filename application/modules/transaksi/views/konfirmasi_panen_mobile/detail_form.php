<div class="row detailed">
	<div class="col-xs-12 detailed">
		<hr style="padding-top: 0px; margin-top: 0px;">
		<form role="form" class="form-horizontal">
			<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
				<div class="col-xs-4 no-padding">
					<label class="control-label">Nama Mitra</label>
				</div>
				<div class="col-xs-8 no-padding">
					<label class="control-label">: <?php echo $data['mitra']; ?></label>
				</div>
			</div>

			<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
				<div class="col-xs-4 no-padding">
					<label class="control-label">No. Reg</label>
				</div>
				<div class="col-xs-8 no-padding">
					<label class="control-label">: <?php echo $data['noreg']; ?></label>
				</div>
			</div>

			<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
				<div class="col-xs-4 no-padding">
					<label class="control-label">Tanggal Panen</label>
				</div>
				<div class="col-xs-8 no-padding">
					<label class="control-label">: <?php echo tglIndonesia($data['tgl_panen'], '-', ' '); ?></label>
				</div>
			</div>

			<div class="col-xs-6" style="padding: 0px 5px 0px 0px; margin-bottom: 5px;">
				<div class="col-xs-12 no-padding">
					<label class="control-label">Umur</label>
				</div>
				<div class="col-xs-12 no-padding">
					<input type="text" class="form-control umur text-right" placeholder="Umur" data-tipe="integer" data-required="1" value="<?php echo $data['umur']; ?>" disabled>
				</div>
			</div>

			<div class="col-xs-6" style="padding: 0px 0px 0px 5px; margin-bottom: 5px;">
				<div class="col-xs-12 no-padding">
					<label class="control-label">Populasi</label>
				</div>
				<div class="col-xs-12 no-padding">
					<input type="text" class="form-control populasi text-right" placeholder="Populasi" data-tipe="integer" data-required="1" value="<?php echo angkaRibuan($data['populasi']); ?>" disabled>
				</div>
			</div>

			<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
				<div class="col-xs-12 no-padding">
					<label class="control-label">BB Rata2</label>
				</div>
				<div class="col-xs-12 no-padding">
					<input type="text" class="form-control text-right bb_rata2" placeholder="BB" data-tipe="decimal" data-required="1" value="<?php echo angkaDecimal($data['bb']); ?>" disabled />
				</div>
			</div>

			<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
				<div class="col-xs-12 no-padding">
					<label class="control-label">Total Sekat</label>
				</div>
				<div class="col-xs-12 no-padding">
					<input type="text" class="form-control text-right tot_sekat" placeholder="Total" data-tipe="decimal" data-required="1" value="<?php echo angkaDecimal($data['total']); ?>" readonly />
				</div>
			</div>
		</form>
	</div>
	<div class="col-xs-12 detailed"><br></div>
	<div class="col-xs-12 detailed">
		<form role="form" class="form-horizontal">
			<div class="col-xs-12 no-padding">
				<label class="control-label"><u>Data Sekat</u></label>
			</div>
			<div class="col-xs-12 no-padding">
				<table class="table table-bordered data_sekat" style="margin-bottom: 0px;">
					<thead>
						<tr>
							<th class="col-xs-1">No</th>
							<th class="col-xs-6">Jumlah</th>
							<th class="col-xs-4">BB</th>
						</tr>
					</thead>
					<tbody>
						<?php $idx = 1; $jml_sekat = 0; $tot_jumlah = 0; $tot_bb = 0; ?>
						<?php foreach ($data['detail'] as $k_det => $v_det): ?>
							<tr class="v-center">
								<td class="text-center no_urut"><?php echo $idx; ?></td>
								<td class="text-right"><?php echo angkaRibuan($v_det['jumlah']); ?></td>
								<td class="text-right"><?php echo angkaDecimal($v_det['bb']); ?></td>
							</tr>
							<?php 
								$tot_jumlah += $v_det['jumlah']; 
								$tot_bb += $v_det['bb']; 

								$idx++;
							?>
						<?php endforeach ?>
					</tbody>
					<tfoot>
						<tr>
							<td><b>Total</b></td>
							<td class="text-right tot_jumlah"><b><?php echo angkaRibuan($tot_jumlah); ?></b></td>
							<td class="text-right tot_bb"><!-- <b><?php echo angkaDecimal($tot_bb); ?></b> --></td>
						</tr>
					</tfoot>
				</table>
			</div>
		</form>
	</div>
	<div class="col-xs-12 detailed"><hr></div>
	<?php if ( $data['edit'] == 1 ): ?>
		<div class="col-xs-12 detailed">
			<form role="form" class="form-horizontal">
				<div class="col-xs-6 no-padding" style="padding-right: 5px">
					<button type="button" class="btn btn-primary pull-right col-xs-12" onclick="kpm.change_tab(this)" style="width: 100%;" data-id="<?php echo $data['id']; ?>" data-edit="edit" data-href="transaksi"><i class="fa fa-edit"></i> Edit</button>
				</div>
				<div class="col-xs-6 no-padding" style="padding-left: 5px">
					<button type="button" class="btn btn-danger pull-right col-xs-12" onclick="kpm.delete(this)" style="width: 100%;" data-id="<?php echo $data['id']; ?>" data-edit="edit" data-href="transaksi"><i class="fa fa-trash"></i> Hapus</button>
				</div>
			</form>
		</div>
	<?php endif ?>
</div>
