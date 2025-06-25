<?php foreach ($list as $key => $val) { ?>
	<tr class="head cursor-p search" title="Klik untuk melihat detail" data-val="0">
		<td class="col-sm-2 id_fitur"><?php echo $val['id_fitur']; ?></td>
		<td><?php echo $val['nama_fitur']; ?></td>
		<td class="col-sm-2 text-center">
			<?php if ( $akses['a_edit'] == 1 ): ?>
				<button id="btn-edit" type="button" class="btn btn-sm btn-primary cursor-p" title="EDIT" onclick="fitur.edit_form(this)"> 
					<i class="fa fa-pencil-square-o" aria-hidden="true"></i> 
				</button>
			<?php endif ?>
			<?php if ( $akses['a_delete'] == 1 ): ?>
				<button id="btn-delete" type="button" class="btn btn-sm btn-danger cursor-p" title="DELETE" onclick="fitur.delete(this)"> 
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
						<th class="col-sm-2">Kode</th>
						<th class="col-sm-2">Nama</th>
						<th>Path Fitur</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($val['detail_fitur'] as $key => $d_val): ?>
						<tr>
							<td><?php echo $d_val['id_detfitur']; ?></td>
							<td><?php echo $d_val['nama_detfitur']; ?></td>
							<td><?php echo $d_val['path_detfitur']; ?></td>
						</tr>
					<?php endforeach ?>
				</tbody>
			</table>
		</td>
	</tr>
<?php } ?>