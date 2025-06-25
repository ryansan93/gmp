<div class="col-xs-12 no-padding">
	<div class="col-xs-2 no-padding"><label class="control-label">Tanggal Adjust</label></div>
	<div class="col-xs-1 no-padding text-center"><label class="control-label">:</label></div>
	<div class="col-xs-9 no-padding"><label class="control-label"><?php echo strtoupper(tglIndonesia($data['tanggal'], '-', ' ', true)); ?></label></div>
</div>
<div class="col-xs-12 no-padding">
	<div class="col-xs-2 no-padding"><label class="control-label">Gudang</label></div>
	<div class="col-xs-1 no-padding text-center"><label class="control-label">:</label></div>
	<div class="col-xs-9 no-padding"><label class="control-label"><?php echo $data['nama_gudang']; ?></label></div>
</div>
<div class="col-xs-12 no-padding">
	<div class="col-xs-2 no-padding"><label class="control-label">Barang</label></div>
	<div class="col-xs-1 no-padding text-center"><label class="control-label">:</label></div>
	<div class="col-xs-9 no-padding"><label class="control-label"><?php echo $data['nama_barang']; ?></label></div>
</div>
<div class="col-xs-12 no-padding">
	<div class="col-xs-2 no-padding"><label class="control-label">Harga Beli</label></div>
	<div class="col-xs-1 no-padding text-center"><label class="control-label">:</label></div>
	<div class="col-xs-9 no-padding"><label class="control-label"><?php echo angkaDecimal($data['hrg_beli']); ?></label></div>
</div>
<div class="col-xs-12 no-padding">
	<div class="col-xs-2 no-padding"><label class="control-label">Harga Jual</label></div>
	<div class="col-xs-1 no-padding text-center"><label class="control-label">:</label></div>
	<div class="col-xs-9 no-padding"><label class="control-label"><?php echo angkaDecimal($data['hrg_jual']); ?></label></div>
</div>
<div class="col-xs-12 no-padding">
	<div class="col-xs-2 no-padding"><label class="control-label">Jumlah Adjust</label></div>
	<div class="col-xs-1 no-padding text-center"><label class="control-label">:</label></div>
	<div class="col-xs-9 no-padding"><label class="control-label"><?php echo angkaDecimal($data['jumlah']); ?></label></div>
</div>
<div class="col-xs-12 no-padding">
	<div class="col-xs-2 no-padding"><label class="control-label">Keterangan</label></div>
	<div class="col-xs-1 no-padding text-center"><label class="control-label">:</label></div>
	<div class="col-xs-9 no-padding"><label class="control-label"><?php echo $data['keterangan']; ?></label></div>
</div>
<?php if ( $akses['a_delete'] == 1 ) { ?>
	<div class="col-xs-12 no-padding">
		<hr style="margin-top: 10px; margin-bottom: 10px;">
	</div>
	<div class="col-xs-12 no-padding">
		<button type="button" class="col-xs-12 btn btn-danger" onclick="aiv.delete(this)" data-id="<?php echo $data['id']; ?>"><i class="fa fa-trash"></i> HAPUS</button>
	</div>
<?php } ?>