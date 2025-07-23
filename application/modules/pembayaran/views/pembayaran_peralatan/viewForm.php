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
</div>
<div class="col-xs-12 no-padding">
	<div class="col-xs-6 no-padding" style="padding-right: 5px;">
		<div class="col-xs-3 no-padding"><label class="control-label">No. Order</label></div>
		<div class="col-xs-1 no-padding text-center"><label class="control-label">:</label></div>
		<div class="col-xs-8 no-padding"><label class="control-label"><?php echo $data['no_order']; ?></label></div>
	</div>
	<div class="col-xs-6 no-padding" style="padding-left: 5px;">
		<div class="col-xs-3 no-padding"><label class="control-label">Plasma</label></div>
		<div class="col-xs-1 no-padding text-center"><label class="control-label">:</label></div>
		<div class="col-xs-8 no-padding"><label class="control-label"><?php echo $data['nama_mitra']; ?></label></div>
	</div>
</div>
<div class="col-xs-12 no-padding">
	<div class="col-xs-6 no-padding" style="padding-right: 5px;">
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
		<div class="col-xs-3 no-padding"><label class="control-label">Total CN</label></div>
		<div class="col-xs-1 no-padding text-center"><label class="control-label">:</label></div>
		<div class="col-xs-8 no-padding"><label class="control-label"><?php echo '('.angkaDecimal($data['tot_cn']).')'; ?></label></div>
	</div>
</div>
<div class="col-xs-12 no-padding">
	<div class="col-xs-6 no-padding" style="padding-right: 5px;">
		<div class="col-xs-3 no-padding"><label class="control-label">Total DN</label></div>
		<div class="col-xs-1 no-padding text-center"><label class="control-label">:</label></div>
		<div class="col-xs-8 no-padding"><label class="control-label"><?php echo angkaDecimal($data['tot_dn']); ?></label></div>
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
		<div class="col-xs-3 no-padding"><label class="control-label">Total Tagihan</label></div>
		<div class="col-xs-1 no-padding text-center"><label class="control-label">:</label></div>
		<div class="col-xs-8 no-padding"><label class="control-label"><?php echo angkaDecimal($data['tot_tagihan']); ?></label></div>
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
	<small>
		<table class="table table-bordered detail" style="margin-bottom: 0px;">
			<thead>
				<tr>
					<th class="col-xs-1 text-center">No. CN</th>
					<th class="col-xs-1 text-center">Tgl CN</th>
					<th class="col-xs-5 text-center">Ket CN</th>
					<th class="col-xs-2 text-center">Saldo</th>
					<th class="col-xs-2 text-center">Pakai</th>
				</tr>
			</thead>
			<tbody>
				<?php if ( isset($data['cn']) && !empty($data['cn']) ) { ?>
					<?php foreach ($data['cn'] as $k_cn => $v_cn): ?>
						<tr>
							<td><?php echo strtoupper($v_cn['nomor']); ?></td>
							<td><?php echo strtoupper(tglIndonesia($v_cn['tgl_cn'], '-', ' ')); ?></td>
							<td><?php echo strtoupper($v_cn['ket_cn']); ?></td>
							<td class="text-right"><?php echo angkaDecimal($v_cn['saldo']); ?></td>
							<td class="text-right"><?php echo angkaDecimal($v_cn['pakai']); ?></td>
						</tr>
					<?php endforeach ?>
				<?php } else { ?>
					<tr>
						<td colspan="5">Data tidak ditemukan.</td>
					</tr>
				<?php } ?>
			</tbody>
		</table>
	</small>
</div>
<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
<div class="col-xs-12 no-padding">
	<small>
		<table class="table table-bordered detail" style="margin-bottom: 0px;">
			<thead>
				<tr>
					<th class="col-xs-1 text-center">No. DN</th>
					<th class="col-xs-1 text-center">Tgl DN</th>
					<th class="col-xs-5 text-center">Ket DN</th>
					<th class="col-xs-2 text-center">Saldo</th>
					<th class="col-xs-2 text-center">Pakai</th>
				</tr>
			</thead>
			<tbody>
				<?php if ( isset($data['dn']) && !empty($data['dn']) ) { ?>
					<?php foreach ($data['dn'] as $k_dn => $v_dn): ?>
						<tr>
							<td><?php echo strtoupper($v_dn['nomor']); ?></td>
							<td><?php echo strtoupper(tglIndonesia($v_dn['tgl_dn'], '-', ' ')); ?></td>
							<td><?php echo strtoupper($v_dn['ket_dn']); ?></td>
							<td class="text-right"><?php echo angkaDecimal($v_cn['saldo']); ?></td>
							<td class="text-right"><?php echo angkaDecimal($v_cn['pakai']); ?></td>
						</tr>
					<?php endforeach ?>
				<?php } else { ?>
					<tr>
						<td colspan="5">Data tidak ditemukan.</td>
					</tr>
				<?php } ?>
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