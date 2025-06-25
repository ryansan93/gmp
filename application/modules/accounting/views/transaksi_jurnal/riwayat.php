<form class="form-horizontal">
	<div class="col-xs-12 no-padding">
		<button id="btn-add" type="button" data-href="action" class="btn btn-primary cursor-p col-xs-12" title="ADD" onclick="tj.changeTabActive(this)"> 
			<i class="fa fa-plus" aria-hidden="true"></i> ADD
		</button>
	</div>
</form>
<div class="col-xs-12 search left-inner-addon no-padding"><hr style="margin-top: 5px; margin-bottom: 5px;"></div>
<div class="col-xs-12 search left-inner-addon" style="padding: 0px 0px 5px 0px;">
	<i class="glyphicon glyphicon-search"></i><input class="form-control" type="search" data-table="tbl_riwayat" placeholder="Search" onkeyup="filter_all(this)">
</div>
<small>
	<span>* Klik untuk melihat detail</span>
	<table class="table table-bordered tbl_riwayat" style="margin-bottom: 0px;">
		<thead>
			<tr>
				<th class="col-xs-6">Nama</th>
				<th class="col-xs-4">Detail</th>
				<th class="col-xs-2">Status</th>
				<!-- <th class="col-xs-1">Action</th> -->
			</tr>
		</thead>
		<tbody>
			<?php if ( !empty($data) ): ?>
				<?php foreach ($data as $k_data => $v_data): ?>
					<tr class="cursor-p" onclick="tj.changeTabActive(this)" data-href="action" data-edit="" data-id="<?php echo $v_data['id']; ?>">
						<td><?php echo $v_data['nama']; ?></td>
						<td>
							<?php if ( !empty($v_data['detail']) ): ?>
								<?php
									$val = '';
									$idx_det = 1;
									foreach ($v_data['detail'] as $k_det => $v_det) {
										$val .= $v_det['nama'];
										if ( $idx_det < count($v_data['detail']) ) {
											$val .= '<br>';
										}
									}

									echo $val;
								?>
							<?php else: ?>
								-
							<?php endif ?>
						</td>
						<td>
							<?php echo ($v_data['mstatus'] == 1) ? 'AKTIF' : 'NON AKTIF'; ?>
						</td>
						<!-- <td>
							<div class="col-xs-6 text-center no-padding">
								<button type="button" class="btn btn-primary" onclick="tj.changeTabActive(this)" data-href="action" data-edit="" data-id="<?php echo $v_data['id']; ?>"><i class="fa fa-file"></i></button>
							</div>
							<div class="col-xs-6 text-center no-padding">
								<button type="button" class="btn btn-danger" onclick="tj.delete(this)" data-kode="<?php echo $v_data['id']; ?>"><i class="fa fa-trash"></i></button>
							</div>
						</td> -->
					</tr>	
				<?php endforeach ?>
			<?php else: ?>
				<tr>
					<td colspan="3">Data tidak ditemukan.</td>
				</tr>
			<?php endif ?>
		</tbody>
	</table>
</small>