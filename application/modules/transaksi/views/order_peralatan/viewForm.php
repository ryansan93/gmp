<div class="col-xs-12 no-padding">
	<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
		<div class="col-xs-2 no-padding">
			<label class="label-control">No. Order</label>
		</div>
		<div class="col-xs-1 no-padding text-center">
			<label class="label-control">:</label>
		</div>
		<div class="col-xs-9 no-padding">
			<label class="label-control"><?php echo $data['no_order']; ?></label>
		</div>
	</div>
	<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
		<div class="col-xs-2 no-padding">
			<label class="label-control">Tanggal Order</label>
		</div>
		<div class="col-xs-1 no-padding text-center">
			<label class="label-control">:</label>
		</div>
		<div class="col-xs-9 no-padding">
			<label class="label-control"><?php echo tglIndonesia( $data['tgl_order'], '-', ' ', true ); ?></label>
		</div>
	</div>
	<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
		<div class="col-xs-2 no-padding">
			<label class="label-control">Mitra</label>
		</div>
		<div class="col-xs-1 no-padding text-center">
			<label class="label-control">:</label>
		</div>
		<div class="col-xs-9 no-padding">
			<label class="label-control"><?php echo $data['nama_mitra']; ?></label>
		</div>
	</div>
	<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
		<div class="col-xs-2 no-padding">
			<label class="label-control">Supplier</label>
		</div>
		<div class="col-xs-1 no-padding text-center">
			<label class="label-control">:</label>
		</div>
		<div class="col-xs-9 no-padding">
			<label class="label-control"><?php echo $data['nama_supplier']; ?></label>
		</div>
	</div>
	<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
		<div class="col-xs-2 no-padding">
			<label class="label-control">Grand Total</label>
		</div>
		<div class="col-xs-1 no-padding text-center">
			<label class="label-control">:</label>
		</div>
		<div class="col-xs-9 no-padding">
			<label class="label-control"><?php echo angkaRibuan($data['grand_total']); ?></label>
		</div>
	</div>
	<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
	<div class="col-xs-12 no-padding">
		<small>
			<table class="table table-bordered tbl_data" style="margin-bottom: 0px;">
				<thead>
					<tr>
						<th class="col-xs-4">Barang</th>
						<th class="col-xs-2">Jumlah</th>
						<th class="col-xs-2">Harga</th>
						<th class="col-xs-2">Total</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($data['detail'] as $k_det => $v_det): ?>
						<tr>
							<td><?php echo $v_det['nama_barang']; ?></td>
							<td class="text-right"><?php echo angkaDecimal($v_det['jumlah']); ?></td>
							<td class="text-right"><?php echo angkaDecimal($v_det['harga']); ?></td>
							<td class="text-right"><?php echo angkaDecimal($v_det['total']); ?></td>
						</tr>
					<?php endforeach ?>
				</tbody>
			</table>
		</small>
	</div>
	<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
	<div class="col-xs-12 no-padding">
		<div class="col-xs-12 no-padding">
			<div class="col-xs-6 no-padding" style="padding-right: 5px;">
				<button type="button" class="col-xs-12 btn btn-primary" onclick="op.changeTabActive(this)" data-id="<?php echo $data['id']; ?>" data-href="action" data-edit="edit"><i class="fa fa-edit"></i> Edit</button>
			</div>
			<div class="col-xs-6 no-padding" style="padding-left: 5px;">
				<button type="button" class="col-xs-12 btn btn-danger" onclick="op.delete(this)" data-id="<?php echo $data['id']; ?>"><i class="fa fa-trash"></i> Hapus</button>
			</div>
		</div>
	</div>
</div>