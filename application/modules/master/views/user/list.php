<?php foreach ($list as $key => $val) { ?>
	<tr class="head cursor-p search" title="Klik untuk lihat detail" data-val="0">
		<td class="id_user col-sm-1" data-status="<?php echo $val['status_user']; ?>"><?php echo $val['id_user']; ?></td>
		<td class="col-sm-2"><?php echo $val['detail_user']['nama_detuser']; ?></td>
		<td class="col-sm-1"><?php echo $val['detail_user']['username_detuser']; ?></td>
		<td class="col-sm-1"><?php echo substr($val['detail_user']['pass_detuser'],0 ,10) . '.....'; ?></td>
		<td class="col-sm-1"><?php echo $val['status_user']==0 ? 'TIDAK AKTIF' : 'AKTIF'; ?></td>
		<td class="text-center col-sm-1">
			<?php if ( $akses['a_edit'] == 1 ): ?>
				<button id="btn-edit" type="button" class="btn btn-sm btn-primary cursor-p" title="EDIT" onclick="user.edit_form(this)"> 
					<i class="fa fa-pencil-square-o" aria-hidden="true"></i> 
				</button>
			<?php endif ?>
			<?php if ( $akses['a_delete'] == 1 ): ?>
				<button id="btn-delete" type="button" class="btn btn-sm btn-danger cursor-p" title="DELETE" onclick="user.delete(this)"> 
					<i class="fa fa-trash" aria-hidden="true"></i> 
				</button>
			<?php endif ?>
		</td>
	</tr>
	<tr class="det hide">
		<td colspan="7">
			<table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
				<thead>
					<tr>
						<th class="col-sm-1">Aktif</th>
						<th class="col-sm-1">Non AKtif</th>
						<th class="col-sm-2">E-mail</th>
						<th class="col-sm-1">Telp</th>
						<th class="col-sm-1">Avatar</th>
						<th class="col-sm-1">Group</th>
						<th class="col-sm-2">Keterangan</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td><?php echo $val['detail_user']['aktif_detuser']; ?></td>
						<td><?php echo empty($val['detail_user']['nonaktif_detuser']) ? '-' : $val['detail_user']['nonaktif_detuser']; ?></td>
						<td><?php echo $val['detail_user']['email_detuser']; ?></td>
						<td><?php echo $val['detail_user']['telp_detuser']; ?></td>
						<td><a href="uploads/<?php echo $val['detail_user']['avatar_detuser']; ?>" target="_blank"><?php echo $val['detail_user']['avatar_detuser']; ?></a></td>
						<td><?php echo $val['detail_user']['data_group']['nama_group']; ?></td>
						<td>
							<?php echo 'Edit by ' . $val['detail_user']['useredit_detuser'] . ' at ' . $val['detail_user']['edit_detuser']; ?>
						</td>
					</tr>
					<?php if ( $akses['a_edit'] == 1 ): ?>
						<tr>
							<td colspan="7">
								<button id="btn-delete" type="button" class="btn btn-danger cursor-p" title="RESET PASSWORD" onclick="user.reset_password(this)"> 
									Reset Password
								</button>
							</td>
						</tr>
					<?php endif ?>
				</tbody>
			</table>
		</td>
	</tr>
<?php } ?>