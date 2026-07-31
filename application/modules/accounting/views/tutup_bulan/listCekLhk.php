<div class="modal-header">
	<span class="modal-title"><b>LIST BELUM INPUT LHK AKHIR BULAN</b></span>
	<button type="button" class="close" data-dismiss="modal">&times;</button>
</div>
<div class="modal-body" style="padding-bottom: 0px;">
	<div class="row detailed">
		<div class="col-xs-12 detailed no-padding">
			<form role="form" class="form-horizontal">
                <div class="col-xs-12 no-padding">
                    <small>
                        <?php
                            $grouped = array();
                            if ( !empty($data) ) {
                                foreach ($data as $value) {
                                    $grouped[ $value['unit'] ][] = $value;
                                }
                            }
                        ?>
                        <?php if ( !empty($grouped) ) { ?>
                            <?php foreach ($grouped as $unit => $rows) { ?>
                                <div class="unit-lhk-block" data-unit="<?php echo $unit; ?>" style="margin-bottom: 15px;">
                                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 5px;">
                                        <b><?php echo $unit; ?></b>
                                        <span>
                                            <button type="button" class="btn btn-xs btn-default btn-export-unit-image" data-unit="<?php echo $unit; ?>">
                                                <i class="fa fa-camera"></i> Export Gambar
                                            </button>
                                            <button type="button" class="btn btn-xs btn-success btn-wa-unit-image" data-unit="<?php echo $unit; ?>">
                                                <i class="fa fa-whatsapp"></i> Kirim WA
                                            </button>
                                        </span>
                                    </div>
                                    <table class="table table-bordered table-hover" style="margin-bottom: 0px; background-color: #fff;">
                                        <thead>
                                            <tr>
                                                <th class="col-xs-1">Noreg</th>
                                                <th class="col-xs-2">Nama Plasma</th>
                                                <th class="col-xs-1">Kandang</th>
                                                <th class="col-xs-1">Tgl LHK Terakhir</th>
                                                <th class="col-xs-1">Umur LHK Terakhir</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $nik = null; ?>
                                            <?php foreach ($rows as $value) { ?>
                                                <?php if ( $value['nik_ppl'] != $nik ) { ?>
                                                    <tr>
                                                        <td colspan="5">
                                                            <b>
                                                                <?php echo !empty($value['nik_ppl']) ? $value['nik_ppl'] : '-'; ?> | <?php echo !empty($value['nama_ppl']) ? strtoupper($value['nama_ppl']) : '-'; ?>
                                                            </b>
                                                        </td>
                                                    </tr>
                                                <?php } ?>
                                                <tr>
                                                    <td><?php echo $value['noreg']; ?></td>
                                                    <td><?php echo $value['nama_mitra']; ?></td>
                                                    <td><?php echo $value['kandang']; ?></td>
                                                    <td><?php echo tglIndonesia($value['tgl_lhk_terakhir'], '-', ' '); ?></td>
                                                    <td><?php echo $value['umur']; ?></td>
                                                </tr>
                                                <?php $nik = $value['nik_ppl']; ?>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php } ?>
                        <?php } ?>
                    </small>
                </div>
			</form>
		</div>
	</div>
</div>
