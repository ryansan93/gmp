<div class="modal-header header">
	<span class="modal-title">View Sumber / Tujuan Jurnal</span>
	<button type="button" class="close" data-dismiss="modal">&times;</button>
</div>
<div class="modal-body body">
	<div class="row">
		<div class="col-lg-12 no-padding">
			<table class="table no-border">
				<tbody>
					<tr>
						<td class="col-md-3">				
							<label class="control-label">Jurnal Trans</label>
						</td>
						<td class="col-md-9">
							<label class="control-label">: <?php echo strtoupper($data['jurnal_trans']['nama']); ?></label>
						</td>
					</tr>
					<tr>
						<td class="col-md-3">				
							<label class="control-label">Nama</label>
						</td>
						<td class="col-md-9">
							<label class="control-label">: <?php echo strtoupper($data['nama']); ?></label>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<div class="col-sm-12 no-padding">
			<hr style="margin-top: 0px;">
		</div>
		<div class="col-sm-6 no-padding" style="padding-left: 8px;">
			<p>
                <b><u>Keterangan : </u></b>
                <?php
                    if ( !empty($data['logs']) ) {
                        foreach ($data['logs'] as $key => $log) {
                            $temp[] = '<li class="list">' . $log['deskripsi'] . ' pada ' . dateTimeFormat( $log['waktu'] ) . '</li>';
                        }
                        if ($temp) {
                            echo '<ul>' . implode("", $temp) . '</ul>';
                        }
                    }
                ?>
            </p>
		</div>
		<div class="col-sm-6 no-padding" style="padding-right: 8px;">
			<?php if ( $akses['a_edit'] == 1 ): ?>
				<button type="button" class="btn btn-primary pull-right" onclick="stj.edit_form(this)" data-id="<?php echo $data['id']; ?>">
					<i class="fa fa-edit"></i>
					Edit
				</button>
			<?php endif ?>
			<?php if ( $akses['a_delete'] == 1 ): ?>
				<button type="button" class="btn btn-danger pull-right" onclick="stj.delete(this)" data-id="<?php echo $data['id']; ?>" style="margin-right: 10px;">
					<i class="fa fa-times"></i>
					Hapus
				</button>
			<?php endif ?>
		</div>
	</div>
</div>