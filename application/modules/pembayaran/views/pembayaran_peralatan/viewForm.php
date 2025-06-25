<div class="col-xs-12 no-padding">
	<div class="col-xs-6 no-padding" style="padding-right: 5px;">
		<div class="col-xs-3 no-padding"><label class="control-label">Tanggal Bayar</label></div>
		<div class="col-xs-1 no-padding text-center"><label class="control-label">:</label></div>
		<div class="col-xs-8 no-padding"><label class="control-label"><?php echo strtoupper(tglIndonesia($data['tgl_bayar'], '-', ' ', true)) ?></label></div>
	</div>
</div>
<div class="col-xs-12 no-padding">
	<div class="col-xs-6 no-padding" style="padding-right: 5px;">
		<div class="col-xs-3 no-padding"><label class="control-label">Supplier</label></div>
		<div class="col-xs-1 no-padding text-center"><label class="control-label">:</label></div>
		<div class="col-xs-8 no-padding"><label class="control-label"><?php echo $data['nama_supplier']; ?></label></div>
	</div>
	<div class="col-xs-6 no-padding" style="padding-left: 5px;">
		<div class="col-xs-3 no-padding"><label class="control-label">Plasma</label></div>
		<div class="col-xs-1 no-padding text-center"><label class="control-label">:</label></div>
		<div class="col-xs-8 no-padding"><label class="control-label"><?php echo $data['nama_mitra']; ?></label></div>
	</div>
</div>
<div class="col-xs-12 no-padding">
	<div class="col-xs-6 no-padding" style="padding-right: 5px;">
		<div class="col-xs-3 no-padding"><label class="control-label">No. Order</label></div>
		<div class="col-xs-1 no-padding text-center"><label class="control-label">:</label></div>
		<div class="col-xs-8 no-padding"><label class="control-label"><?php echo $data['no_order']; ?></label></div>
	</div>
	<div class="col-xs-6 no-padding" style="padding-left: 5px;">
		<div class="col-xs-3 no-padding"><label class="control-label">No. Faktur</label></div>
		<div class="col-xs-1 no-padding text-center"><label class="control-label">:</label></div>
		<div class="col-xs-8 no-padding">
			<a href="uploads/<?php echo $data['lampiran']; ?>" target="_blank">
				<label class="control-label"><?php echo $data['no_faktur']; ?></label>
			</a>
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding">
	<div class="col-xs-6 no-padding" style="padding-right: 5px;">
		<div class="col-xs-3 no-padding"><label class="control-label">Jumlah Tagihan</label></div>
		<div class="col-xs-1 no-padding text-center"><label class="control-label">:</label></div>
		<div class="col-xs-8 no-padding"><label class="control-label"><?php echo angkaDecimal($data['jml_tagihan']); ?></label></div>
	</div>
</div>
<div class="col-xs-12 no-padding">
	<div class="col-xs-6 no-padding" style="padding-right: 5px;">
		<div class="col-xs-3 no-padding"><label class="control-label">Saldo</label></div>
		<div class="col-xs-1 no-padding text-center"><label class="control-label">:</label></div>
		<div class="col-xs-8 no-padding"><label class="control-label"><?php echo angkaDecimal($data['saldo']); ?></label></div>
	</div>
	<div class="col-xs-6 no-padding" style="padding-left: 5px;">
		<div class="col-xs-3 no-padding"><label class="control-label">Jumlah Bayar</label></div>
		<div class="col-xs-1 no-padding text-center"><label class="control-label">:</label></div>
		<div class="col-xs-8 no-padding"><label class="control-label"><?php echo angkaDecimal($data['jml_bayar']); ?></label></div>
	</div>
</div>
<div class="col-xs-12 no-padding">
	<div class="col-xs-6 no-padding" style="padding-right: 5px;">
		<div class="col-xs-3 no-padding"><label class="control-label">Total Bayar</label></div>
		<div class="col-xs-1 no-padding text-center"><label class="control-label">:</label></div>
		<div class="col-xs-8 no-padding"><label class="control-label"><?php echo angkaDecimal($data['tot_bayar']); ?></label></div>
	</div>
</div>
<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
<div class="col-xs-12 no-padding">
	<small>
		<table class="table table-bordered detail" style="margin-bottom: 0px;">
			<thead>
				<tr>
					<th class="col-xs-4">Barang</th>
					<th class="col-xs-2">Jumlah</th>
					<th class="col-xs-2">Harga</th>
					<th class="col-xs-2">Total</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($data['detail'] as $key => $value): ?>
					<tr>
						<td><?php echo $value['nama_barang']; ?></td>
						<td class="text-right"><?php echo angkaDecimal($value['jumlah']); ?></td>
						<td class="text-right"><?php echo angkaDecimal($value['harga']); ?></td>
						<td class="text-right"><?php echo angkaDecimal($value['total']); ?></td>
					</tr>
				<?php endforeach ?>
			</tbody>
		</table>
	</small>
</div>
<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
<div class="col-xs-12 no-padding">
	<div class="col-xs-6 no-padding" style="padding-right: 5px;">
		<button type="button" class="col-xs-12 btn btn-primary" onclick="pp.changeTabActive(this)" data-id="<?php echo $data['id']; ?>" data-href="action" data-edit="edit"><i class="fa fa-edit"></i> Edit</button>
	</div>
	<div class="col-xs-6 no-padding" style="padding-left: 5px;">
		<button type="button" class="col-xs-12 btn btn-danger" onclick="pp.delete(this)" data-id="<?php echo $data['id']; ?>"><i class="fa fa-trash"></i> Hapus</button>
	</div>
</div>