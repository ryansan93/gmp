<?php
$is_edit   = !empty($data);
$id        = $is_edit ? $data['id']        : '';
$id_group  = $is_edit ? $data['id_group']  : '';
$id_detfitur = $is_edit ? $data['id_detfitur'] : '';
$akses_khusus = $is_edit ? $data['akses_khusus'] : '';
?>
<div class="modal-header">
	<button type="button" class="close" data-dismiss="modal">&times;</button>
	<h4 class="modal-title">
		<i class="fa fa-key"></i>
		<?php echo $is_edit ? 'Edit' : 'Tambah'; ?> Akses Khusus
	</h4>
</div>
<div class="modal-body">
	<input type="hidden" class="ak-id" value="<?php echo $id; ?>">
	<form class="form-horizontal">
		<div class="form-group">
			<label class="col-sm-3 control-label">Group <span class="text-danger">*</span></label>
			<div class="col-sm-8">
				<select class="form-control ak-group" data-required="1">
					<option value="">-- Pilih Group --</option>
					<?php foreach ( $groups as $g ): ?>
						<option value="<?php echo $g['id_group']; ?>"
							<?php echo ($g['id_group'] == $id_group) ? 'selected' : ''; ?>>
							<?php echo $g['id_group']; ?> — <?php echo $g['nama_group']; ?>
						</option>
					<?php endforeach ?>
				</select>
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 control-label">Fitur</label>
			<div class="col-sm-8">
				<select class="form-control ak-fitur">
					<option value="">-- Tidak Spesifik / Semua Fitur --</option>
					<?php foreach ( $fiturs as $f ): ?>
						<option value="<?php echo $f['id_detfitur']; ?>"
							data-path="<?php echo $f['path_detfitur']; ?>"
							<?php echo ($f['id_detfitur'] == $id_detfitur) ? 'selected' : ''; ?>>
							<?php echo $f['nama_detfitur']; ?>
							<?php if ( $f['path_detfitur'] ): ?>
								(<?php echo $f['path_detfitur']; ?>)
							<?php endif ?>
						</option>
					<?php endforeach ?>
				</select>
				<small class="text-muted">Kosongkan jika akses berlaku untuk semua fitur group.</small>
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 control-label">Akses Khusus <span class="text-danger">*</span></label>
			<div class="col-sm-8">
				<input type="text" class="form-control ak-key" data-required="1"
					placeholder="Contoh: rhpp_inti, input harga realisasi sj"
					value="<?php echo htmlspecialchars($akses_khusus); ?>">
				<small class="text-muted">Key akses yang digunakan di aplikasi (huruf kecil, underscore).</small>
			</div>
		</div>
	</form>
</div>
<div class="modal-footer">
	<button type="button" class="btn btn-default" data-dismiss="modal">
		<i class="fa fa-times"></i> Batal
	</button>
	<?php if ( $is_edit ): ?>
		<button type="button" class="btn btn-primary" onclick="aksesKhusus.update()">
			<i class="fa fa-save"></i> Update
		</button>
	<?php else: ?>
		<button type="button" class="btn btn-primary" onclick="aksesKhusus.save()">
			<i class="fa fa-save"></i> Simpan
		</button>
	<?php endif ?>
</div>
