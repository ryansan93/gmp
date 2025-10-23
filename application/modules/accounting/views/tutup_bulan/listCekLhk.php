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
                        <table class="table table-bordered table-hover" style="margin-bottom: 0px;">
                            <thead>
                                <tr>
                                    <th class="col-xs-1">Noreg</th>
                                    <th class="col-xs-2">Nama Plasma</th>
                                    <th class="col-xs-1">Kandang</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ( !empty($data) ) { ?>
                                    <?php $unit = null; $nik = null; ?>
                                    <?php foreach ($data as $key => $value) { ?>
                                        <?php if ( $value['unit'] != $unit ) { ?>
                                            <tr>
                                                <td colspan="3">
                                                    <b>
                                                        <?php echo $value['unit']; ?>
                                                    </b>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                        <?php if ( $value['nik_ppl'] != $nik ) { ?>
                                            <tr>
                                                <td colspan="3">
                                                    <b>
                                                        <?php echo !empty($value['nik_ppl']) ? $value['nik_ppl'] : '-'; ?> | <?php echo !empty($value['nama_ppl']) ? $value['nama_ppl'] : '-'; ?>
                                                    </b>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                        <tr>
                                            <td><?php echo $value['noreg']; ?></td>
                                            <td><?php echo $value['nama_mitra']; ?></td>
                                            <td><?php echo $value['kandang']; ?></td>
                                        </tr>
                                        <?php $unit = $value['unit']; ?>
                                        <?php $nik = $value['nik_ppl']; ?>

                                        <?php if ( isset($data[ $key+1 ]['unit']) && ($value['unit'] != $data[ $key+1 ]['unit']) ) { ?>
                                            <tr>
                                                <td colspan="3"><br></td>
                                            </tr>
                                        <?php } ?>
                                    <?php } ?>
                                <?php } ?>
                            </tbody>
                        </table>
                    </small>
                </div>
			</form>
		</div>
	</div>
</div>