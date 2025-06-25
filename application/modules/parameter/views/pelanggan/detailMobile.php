<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
	<div class="col-xs-12 no-padding">
		<label class="control-label">Nama Bakul</label>
	</div>
	<div class="col-xs-12 no-padding not-plg-baru" style="padding-left: 10px;">
		<span><?php echo strtoupper($data['nama']); ?></span>
	</div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
	<div class="col-xs-12 no-padding">
		<label class="control-label">Alamat Bakul</label>
	</div>
	<div class="col-xs-12 no-padding" style="padding-left: 10px;">
		<span><?php echo strtoupper($data['alamat']); ?></span>
	</div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
	<div class="col-xs-12 no-padding">
		<label class="control-label">Alamat Usaha</label>
	</div>
	<div class="col-xs-12 no-padding" style="padding-left: 10px;">
		<span><?php echo strtoupper($data['alamat_usaha']); ?></span>
	</div>
</div>
<!-- <div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
	<div class="col-xs-12 no-padding">
		<label class="control-label">Ambilan Terakhir</label>
	</div>
	<div class="col-xs-12 no-padding" style="padding-left: 10px;">
		<span><?php echo strtoupper(tglIndonesia($data['max_tgl_ambil'], '-', ' ')); ?></span><br>
        <span><?php echo angkaRibuan($data['ekor']).' EKOR'; ?></span><br>
        <span><?php echo angkaDecimal($data['tonase']).' KG'; ?></span>
	</div>
</div> -->
<div class="col-xs-12 no-padding">
	<hr style="margin-top: 10px; margin-bottom: 10px;">
</div>
<div class="col-xs-12 no-padding">
	<table class="table table-bordered" style="margin-bottom: 0px;">
		<tbody>
            <tr>
                <td><b>LIST NO TELPON</b></td>
            </tr>
            <?php if ( isset($data['telpon']) && !empty($data['telpon']) ) { ?>
                <?php foreach ($data['telpon'] as $k_telp => $v_telp) { ?>
                    <tr>
                        <td class="text-left"><?php echo $v_telp['nomor']; ?></td>
                    </tr>
                <?php } ?>
            <?php } else { ?>
                <tr>
                    <td>DATA TIDAK DITEMUKAN</td>
                </tr>
            <?php } ?>
		</tbody>
	</table>
</div>
<div class="col-xs-12 no-padding">
	<hr style="margin-top: 10px; margin-bottom: 10px;">
</div>
<div class="col-xs-12 no-padding">
	<table class="table table-bordered" style="margin-bottom: 0px;">
		<tbody>
            <tr>
                <td colspan="3"><b>LIST KUNJUNGAN</b></td>
            </tr>
            <?php if ( isset($data['kunjungan']) && !empty($data['kunjungan']) ) { ?>
                <?php foreach ($data['kunjungan'] as $k_kjg => $v_kjg) { ?>
                    <tr class="head">
                        <td class="col-xs-10"><?php echo $v_kjg['tanggal']; ?></td>
                        <td class="col-xs-2 text-center cursor-p" onclick="plg.collapseRowDetailMobile(this)"><span class="glyphicon glyphicon-chevron-right"></span></td>
                    </tr>
                    <tr class="detail hide">
                        <td colspan="2">
                            <div class="col-xs-12 no-padding">
                                <div class="col-xs-12 no-padding">
                                    <label class="control-label">Catatan :</label>
                                </div>
                                <div class="col-xs-12 no-padding" style="padding-left: 10px;">
                                    <span><?php echo strtoupper($v_kjg['catatan']); ?></span>
                                </div>
                            </div>
                            <div class="col-xs-12 no-padding"><hr style="margin-top: 5px; margin-bottom: 5px;"></div>
                            <div class="col-xs-12 no-padding">
                                <div class="col-xs-6" style="padding-left: 0px;">
                                    <!-- <button type="button" class="col-xs-12 btn btn-default text-center"><span class="glyphicon glyphicon-map-marker"></span></button> -->
                                    <a class="cursor-p col-xs-12 btn btn-default text-center" href="geo:0, 0?z=15&q=<?php echo $v_kjg['lat_long']; ?>" target="_blank"><span class="glyphicon glyphicon-map-marker"></span></a>
                                </div>
                                <div class="col-xs-6" style="padding-right: 0px;">
                                    <!-- <button type="button" class="col-xs-12 btn btn-default text-center"><span class="glyphicon glyphicon-film"></span></button> -->
                                    <a class="cursor-p col-xs-12 btn btn-default text-center" href="uploads/<?php echo $v_kjg['foto_kunjungan']; ?>" target="_blank"><span class="glyphicon glyphicon-camera"></span></a>
                                </div>
                            </div>
                        </td>
                    </tr>
                <?php } ?>
            <?php } else { ?>
                <tr>
                    <td colspan="2">DATA TIDAK DITEMUKAN</td>
                </tr>
            <?php } ?>
		</tbody>
	</table>
</div>