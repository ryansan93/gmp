<div class="row detailed">
	<div class="col-lg-12 detailed">
		<hr style="padding-top: 0px; margin-top: 0px;">
		<form role="form" class="form-horizontal">
			<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
				<div class="col-xs-2 no-padding">
					<label class="control-label">No. Transaksi</label>
				</div>
				<div class="col-xs-10 no-padding">
					<label class="control-label">: <?php echo strtoupper($data['nomor']); ?></label>
				</div>
			</div>

			<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
				<div class="col-xs-2 no-padding">
					<label class="control-label">Nama Mitra</label>
				</div>
				<div class="col-xs-10 no-padding">
					<label class="control-label">: <?php echo strtoupper($data['d_mitra']['nama']); ?></label>
				</div>
			</div>

			<div class="col-xs-12 no-padding" style="margin-bottom: 5px; padding-right: 5px;">
				<div class="col-xs-2 no-padding">
					<label class="control-label">Tanggal</label>
				</div>
				<div class="col-xs-10 no-padding">
		            <label class="control-label">: <?php echo tglIndonesia($data['tanggal'], '-', ' ', true); ?></label>
				</div>
	        </div>

			<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
				<div class="col-xs-12 no-padding">
					<label class="control-label">Total</label>
				</div>
				<div class="col-xs-12 no-padding">
					<input type="text" class="form-control text-right total" data-tipe="decimal" placeholder="Total" data-required="1" value="<?php echo angkaDecimal($data['total']); ?>" readonly>
				</div>
			</div>

			<!-- <div class="col-xs-6 no-padding" style="margin-bottom: 5px; padding-left: 5px;">
				<div class="col-xs-12 no-padding">
					<label class="control-label">Bayar</label>
				</div>
				<div class="col-xs-12 no-padding">
					<input type="text" class="form-control text-right bayar" data-tipe="decimal" placeholder="Bayar" data-required="1"  onblur="pp.hit_total()" value="<?php echo angkaDecimal($data['bayar']); ?>" readonly>
				</div>
			</div>

			<div class="col-xs-6 no-padding" style="margin-bottom: 5px; padding-left: 5px;">
				<div class="col-xs-12 no-padding">
					<label class="control-label">Sisa Bayar</label>
				</div>
				<div class="col-xs-12 no-padding">
					<input type="text" class="form-control text-right sisa_bayar" data-tipe="decimal" placeholder="Sisa Bayar" data-required="1" value="<?php echo angkaDecimal($data['sisa']); ?>" readonly>
				</div>
			</div> -->
		</form>
	</div>
	<div class="col-xs-12 detailed"><br></div>
	<div class="col-xs-12 detailed">
		<form role="form" class="form-horizontal">
			<div class="col-xs-12 no-padding">
				<table class="table table-bordered data_brg" style="margin-bottom: 0px;">
					<thead>
						<tr>
							<th class="col-xs-3">Nama Peralatan</th>
							<th class="col-xs-2">Jumlah</th>
							<th class="col-xs-2">Harga</th>
							<th class="col-xs-2">Sub Total</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($data['detail'] as $k_det => $v_det): ?>
							<tr class="v-center">
								<td>
									<?php echo strtoupper($v_det['d_barang']['nama']); ?>
								</td>
								<td class="text-right">
									<?php echo angkaRibuan($v_det['jumlah']); ?>
								</td>
								<td class="text-right">
									<?php echo angkaDecimal($v_det['harga']); ?>
								</td>
								<td class="text-right sub_total"><?php echo angkaDecimal($v_det['total']); ?></td>
							</tr>
						<?php endforeach ?>
					</tbody>
				</table>
			</div>
		</form>
	</div>
	<div class="col-lg-12 detailed"><hr></div>
	<div class="col-xs-12 detailed">
		<form role="form" class="form-horizontal">
			<div class="col-xs-6 no-padding" style="padding-right: 5px;">
				<button type="button" class="btn btn-primary col-xs-12" onclick="pp.change_tab(this)" data-id="<?php echo $data['id']; ?>" data-edit="edit" data-href="transaksi"><i class="fa fa-edit"></i> Edit</button>
			</div>
			<div class="col-xs-6 no-padding" style="padding-left: 5px;">
				<button type="button" class="btn btn-danger col-xs-12" onclick="pp.delete(this)" data-id="<?php echo $data['id']; ?>"><i class="fa fa-trash"></i> Hapus</button>
			</div>
		</form>
	</div>
</div>
