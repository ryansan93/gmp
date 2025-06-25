<?php if ( count($data) > 0 ): ?>
	<?php $idx = 0; ?>
	<?php foreach ($data as $k_data => $v_data): ?>
		<?php $idx++; ?>
		<tr class="cursor-p search data" title="Klik 2x untuk edit data" data-id="<?php echo $v_data['id']; ?>">
			<td><?php echo $idx; ?></td>
			<td class="prs_potongan">
				<span><?php echo angkaDecimal($v_data['prs_potongan']); ?></span>
				<input type="text" class="form-control prs_potongan hide" data-tipe="decimal" value="<?php echo angkaDecimal($v_data['prs_potongan']); ?>">
			</td>
			<td>
				<div class="col-sm-12 no-padding edit">
					<div class="col-sm-6" style="padding: 0px 2px 0px 0px;">
						<button type="button" class="btn btn-success" title="EDIT" onclick="pp.edit_form(this)" style="padding-left: 11px; padding-right: 11px;"><i class="fa fa-edit"></i></button>
					</div>
					<div class="col-sm-6" style="padding: 0px 0px 0px 3px;">
						<button type="button" class="btn btn-danger" title="HAPUS" onclick="pp.delete(this)"><i class="fa fa-trash"></i></button>
					</div>
				</div>
				<div class="col-sm-12 no-padding save_edit hide">
					<div class="col-sm-6" style="padding: 0px 2px 0px 0px;">
						<button type="button" class="btn btn-primary" title="SIMPAN EDIT" onclick="pp.edit(this)" style="padding-left: 11px; padding-right: 11px;"><i class="fa fa-save"></i></button>
					</div>
					<div class="col-sm-6" style="padding: 0px 0px 0px 3px;">
						<button type="button" class="btn btn-danger" title="BATAL EDIT" onclick="pp.batal_edit(this)"><i class="fa fa-times"></i></button>
					</div>
				</div>
			</td>
		</tr>
	<?php endforeach ?>
<?php else: ?>
	<tr>
		<td colspan="3">Data tidak ditemukan.</td>
	</tr>
<?php endif ?>