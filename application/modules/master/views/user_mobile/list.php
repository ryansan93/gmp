<?php if ( count($list) > 0 ): ?>
	<?php foreach ($list as $key => $val) { ?>
		<tr class="cursor-p search" data-id="<?php echo $val['id']; ?>">
			<td class="col-sm-2"><?php echo strtoupper($val['nama']); ?></td>
			<td class="col-sm-2"><?php echo strtoupper($val['jabatan']); ?></td>
			<td class="col-sm-1"><?php echo strtoupper($val['username']); ?></td>
			<td class="col-sm-1"><?php echo strtoupper($val['password']); ?></td>
			<td class="col-sm-1"><?php echo $val['status']==0 ? 'NON AKTIF' : 'AKTIF'; ?></td>
			<td class="text-center col-sm-1">
				<?php if ( $akses['a_edit'] == 1 ): ?>
					<button id="btn-edit" type="button" class="btn btn-sm btn-primary cursor-p" title="EDIT" onclick="um.edit_form(this)"> 
						<i class="fa fa-pencil-square-o" aria-hidden="true"></i> 
					</button>
				<?php endif ?>
				<?php if ( $akses['a_delete'] == 1 ): ?>
					<button id="btn-delete" type="button" class="btn btn-sm btn-danger cursor-p" title="DELETE" onclick="um.delete(this)"> 
						<i class="fa fa-trash" aria-hidden="true"></i> 
					</button>
				<?php endif ?>
			</td>
		</tr>
	<?php } ?>
<?php else: ?>
	<tr>
		<td colspan="6">Data tidak ditemukan.</td>
	</tr>
<?php endif ?>