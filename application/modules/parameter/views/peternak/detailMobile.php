<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
	<div class="col-xs-12 no-padding">
		<label class="control-label">Nama Plasma</label>
	</div>
	<div class="col-xs-12 no-padding not-plg-baru" style="padding-left: 10px;">
		<span><?php echo strtoupper($data['nama']); ?></span>
	</div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
	<div class="col-xs-12 no-padding">
		<label class="control-label">Alamat Plasma</label>
	</div>
	<div class="col-xs-12 no-padding" style="padding-left: 10px;">
		<span><?php echo strtoupper($data['alamat']); ?></span>
	</div>
</div>
<div class="col-xs-12 no-padding">
	<hr style="margin-top: 10px; margin-bottom: 10px;">
</div>
<div class="col-xs-12 no-padding">
	<table class="table table-bordered" style="margin-bottom: 0px;">
		<tbody>
            <tr>
                <td style="background-color: #ededed;"><b>LIST NO TELPON</b></td>
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
                <td colspan="4" style="background-color: #ededed;"><b>LIST KANDANG</b></td>
            </tr>
            <tr>
                <td class="col-xs-1"><b>NO</b></td>
                <td class="col-xs-3"><b>KAPASITAS</b></td>
                <td class="col-xs-5"><b>CHICK IN TERAKHIR</b></td>
                <td class="col-xs-2"></td>
            </tr>
            <?php if ( isset($data['kandang']) && !empty($data['kandang']) ) { ?>
                <?php foreach ($data['kandang'] as $k_kdg => $v_kdg) { ?>
                    <tr class="head">
                        <td><?php echo $v_kdg['kandang']; ?></td>
                        <td class="text-left"><?php echo angkaRibuan($v_kdg['kapasitas']); ?></td>
                        <td class="text-left"><?php echo strtoupper(tglIndonesia($v_kdg['max_tgl_chickin'], '-', ' ')); ?></td>
                        <td class="text-center cursor-p" onclick="ptk.collapseRowDetailMobile(this)"><span class="glyphicon glyphicon-chevron-right"></span></td>
                    </tr>
                    <tr class="detail hide">
                        <td colspan="4">
                            <div class="col-xs-12 no-padding">
                                <div class="col-xs-6" style="padding-left: 0px;">
                                    <!-- <button type="button" class="col-xs-12 btn btn-default text-center"><span class="glyphicon glyphicon-map-marker"></span></button> -->
                                    <?php if ( empty($v_kdg['posisi']) ) { ?>
                                        <a class="cursor-p col-xs-12 btn btn-default text-center" target="_blank" disabled>TIDAK ADA GPS</a>
                                    <?php } else { ?>
                                        <a class="cursor-p col-xs-12 btn btn-default text-center" href="geo:0, 0?z=15&q=<?php echo $v_kdg['posisi']; ?>" target="_blank" <?php echo empty($v_kdg['posisi']) ? 'disabled' : null; ?>><span class="glyphicon glyphicon-map-marker"></span></a>
                                    <?php } ?>
                                </div>
                                <div class="col-xs-6" style="padding-right: 0px;">
                                    <!-- <button type="button" class="col-xs-12 btn btn-default text-center"><span class="glyphicon glyphicon-film"></span></button> -->
                                    <?php if ( empty($v_kdg['foto']) ) { ?>
                                        <a class="cursor-p col-xs-12 btn btn-default text-center" target="_blank" disabled>TIDAK ADA FOTO</a>
                                    <?php } else { ?>
                                        <a class="cursor-p col-xs-12 btn btn-default text-center" href="uploads/<?php echo $v_kdg['foto']; ?>" target="_blank" <?php empty($v_kdg['foto']) ? 'disabled' : null; ?>><span class="glyphicon glyphicon-camera"></span></a>
                                    <?php } ?>
                                </div>
                            </div>
                        </td>
                    </tr>
                <?php } ?>
            <?php } else { ?>
                <tr>
                    <td colspan="4">DATA TIDAK DITEMUKAN</td>
                </tr>
            <?php } ?>
		</tbody>
	</table>
</div>