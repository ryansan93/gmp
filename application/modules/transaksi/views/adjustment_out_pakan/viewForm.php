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
	<div class="col-xs-2 no-padding"><label class="control-label">Tanggal SJ</label></div>
	<div class="col-xs-1 no-padding text-center"><label class="control-label">:</label></div>
	<div class="col-xs-9 no-padding"><label class="control-label"><?php echo strtoupper(tglIndonesia($data['tgl_trans'], '-', ' ', true)); ?></label></div>
</div>
<div class="col-xs-12 no-padding">
	<div class="col-xs-2 no-padding"><label class="control-label">No. SJ</label></div>
	<div class="col-xs-1 no-padding text-center"><label class="control-label">:</label></div>
	<div class="col-xs-9 no-padding"><label class="control-label"><?php echo $data['kode_trans']; ?></label></div>
</div>
<div class="col-xs-12 no-padding">
	<div class="col-xs-2 no-padding"><label class="control-label">Harga</label></div>
	<div class="col-xs-1 no-padding text-center"><label class="control-label">:</label></div>
	<div class="col-xs-9 no-padding"><label class="control-label"><?php echo angkaDecimal($data['harga']); ?></label></div>
</div>
<div class="col-xs-12 no-padding">
	<div class="col-xs-2 no-padding"><label class="control-label">Sisa Stok</label></div>
	<div class="col-xs-1 no-padding text-center"><label class="control-label">:</label></div>
	<div class="col-xs-9 no-padding"><label class="control-label"><?php echo angkaDecimal($data['sisa_stok']); ?></label></div>
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