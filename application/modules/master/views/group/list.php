<?php foreach ($list as $key => $val) { ?>
	<tr class="head cursor-p search" title="Klik untuk melihat detail" data-val="0">
		<td class="col-sm-2 id_group"><?php echo $val['id_group']; ?></td>
		<td><?php echo $val['nama_group']; ?></td>
		<td class="col-sm-2 text-center">
			<?php if ( $akses['a_edit'] == 1 ): ?>
				<button id="btn-edit" type="button" class="btn btn-sm btn-primary cursor-p" title="EDIT" onclick="group.edit_form(this)"> 
					<i class="fa fa-pencil-square-o" aria-hidden="true"></i> 
				</button>
			<?php endif ?>
			<?php if ( $akses['a_delete'] == 1 ): ?>
				<button id="btn-delete" type="button" class="btn btn-sm btn-danger cursor-p" title="DELETE" onclick="group.delete(this)"> 
					<i class="fa fa-trash" aria-hidden="true"></i> 
				</button>
			<?php endif ?>
		</td>
	</tr>
	<tr class="det hide">
		<td colspan="3">
			<table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
				<thead>
					<tr>
						<th class="col-sm-1">Kode</th>
						<th class="col-sm-2">Nama</th>
						<th class="col-sm-2">Path Fitur</th>
						<th class="col-sm-1">View</th>
						<th class="col-sm-1">Submit</th>
						<th class="col-sm-1">Edit</th>
						<th class="col-sm-1">Delete</th>
						<th class="col-sm-1">Ack</th>
						<th class="col-sm-1">Approve</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($val['detail_group'] as $key => $d_val): ?>
						<tr>
							<td><?php echo $d_val['detail_fitur']['id_detfitur']; ?></td>
							<td><?php echo $d_val['detail_fitur']['nama_detfitur']; ?></td>
							<td><?php echo $d_val['detail_fitur']['path_detfitur']; ?></td>
							<td class="text-center">
								<?php if ( $d_val['a_view'] == 1 ) { ?>
									<i class="fa fa-check" aria-hidden="true"></i>
								<?php } else { ?>
									<i class="fa fa-minus" aria-hidden="true"></i>
								<?php } ?>
							</td>
							<td class="text-center">
								<?php if ( $d_val['a_submit'] == 1 ) { ?>
									<i class="fa fa-check" aria-hidden="true"></i>
								<?php } else { ?>
									<i class="fa fa-minus" aria-hidden="true"></i>
								<?php } ?>
							</td>
							<td class="text-center">
								<?php if ( $d_val['a_edit'] == 1 ) { ?>
									<i class="fa fa-check" aria-hidden="true"></i>
								<?php } else { ?>
									<i class="fa fa-minus" aria-hidden="true"></i>
								<?php } ?>
							</td>
							<td class="text-center">
								<?php if ( $d_val['a_delete'] == 1 ) { ?>
									<i class="fa fa-check" aria-hidden="true"></i>
								<?php } else { ?>
									<i class="fa fa-minus" aria-hidden="true"></i>
								<?php } ?>
							</td>
							<td class="text-center">
								<?php if ( $d_val['a_ack'] == 1 ) { ?>
									<i class="fa fa-check" aria-hidden="true"></i>
								<?php } else { ?>
									<i class="fa fa-minus" aria-hidden="true"></i>
								<?php } ?>
							</td>
							<td class="text-center">
								<?php if ( $d_val['a_approve'] == 1 ) { ?>
									<i class="fa fa-check" aria-hidden="true"></i>
								<?php } else { ?>
									<i class="fa fa-minus" aria-hidden="true"></i>
								<?php } ?>
							</td>
						</tr>
					<?php endforeach ?>
				</tbody>
			</table>
		</td>
	</tr>
<?php } ?>