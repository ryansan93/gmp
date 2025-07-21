<div class="modal-header header">
	<span class="modal-title">View COA</span>
	<button type="button" class="close" data-dismiss="modal">&times;</button>
</div>
<div class="modal-body body">
	<div class="row">
		<div class="col-lg-12 no-padding">
			<table class="table no-border">
				<tbody>
					<tr class="hide">
						<td class="col-md-3">				
							<label class="control-label">Perusahaan</label>
						</td>
						<td class="col-md-9">
							<label class="control-label">: <?php echo strtoupper($data['d_perusahaan']['perusahaan']); ?></label>
						</td>
					</tr>
					<tr class="hide">
						<td class="col-md-3">				
							<label class="control-label">Unit</label>
						</td>
						<td class="col-md-9">
							<label class="control-label">: <?php echo !empty($data['id_unit']) ? strtoupper($data['id_unit']) : '-'; ?></label>
						</td>
					</tr>
					<tr>
						<td class="col-md-3">
							<label class="control-label">No. COA</label>
						</td>
						<td class="col-md-9">
							<label class="control-label">: <?php echo strtoupper($data['coa']); ?></label>
						</td>
					</tr>
					<tr>
						<td class="col-md-3">
							<label class="control-label">Nama COA</label>
						</td>
						<td class="col-md-9">
							<label class="control-label">: <?php echo strtoupper($data['nama_coa']); ?></label>
						</td>
					</tr>
					<tr>
						<td class="col-md-3">
							<label class="control-label">Golongan 1</label>
						</td>
						<td class="col-md-9">
							<label class="control-label">: <?php echo !empty($data['gol1']) ? strtoupper($data['gol1']) : '-'; ?></label>
						</td>
					</tr>
					<tr>
						<td class="col-md-3">
							<label class="control-label">Golongan 2</label>
						</td>
						<td class="col-md-9">
							<label class="control-label">: <?php echo !empty($data['gol2']) ? strtoupper($data['gol2']) : '-'; ?></label>
						</td>
					</tr>
					<tr>
						<td class="col-md-3">
							<label class="control-label">Golongan 3</label>
						</td>
						<td class="col-md-9">
							<label class="control-label">: <?php echo !empty($data['gol3']) ? strtoupper($data['gol3']) : '-'; ?></label>
						</td>
					</tr>
					<tr>
						<td class="col-md-3">
							<label class="control-label">Golongan 4</label>
						</td>
						<td class="col-md-9">
							<label class="control-label">: <?php echo !empty($data['gol4']) ? strtoupper($data['gol4']) : '-'; ?></label>
						</td>
					</tr>
					<tr>
						<td class="col-md-3">
							<label class="control-label">Golongan 5</label>
						</td>
						<td class="col-md-9">
							<label class="control-label">: <?php echo !empty($data['gol5']) ? strtoupper($data['gol5']) : '-'; ?></label>
						</td>
					</tr>
					<tr><td colspan="2"><hr></td></tr>
					<tr>
						<td class="col-md-3">
							<label class="control-label">Laporan</label>
						</td>
						<td class="col-md-9">
							<label class="control-label">: <?php echo ($data['lap'] == 'N') ? 'NERACA' : 'LABA / RUGI'; ?></label>
						</td>
					</tr>
					<tr>
						<td class="col-md-3">
							<label class="control-label">Posisi COA</label>
						</td>
						<td class="col-md-9">
							<label class="control-label">: <?php echo ($data['coa_pos'] == 'D') ? 'DEBIT' : 'KREDIT'; ?></label>
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
				<button type="button" class="btn btn-primary pull-right" onclick="coa.edit_form(this)" data-id="<?php echo $data['id']; ?>">
					<i class="fa fa-edit"></i>
					Edit
				</button>
			<?php endif ?>
			<?php if ( $akses['a_delete'] == 1 ): ?>
				<button type="button" class="btn btn-danger pull-right" onclick="coa.delete(this)" data-id="<?php echo $data['id']; ?>" style="margin-right: 10px;">
					<i class="fa fa-times"></i>
					Hapus
				</button>
			<?php endif ?>
		</div>
	</div>
</div>