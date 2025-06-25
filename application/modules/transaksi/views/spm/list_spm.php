<div class="form-group d-flex align-items-center">
	<?php if ( count($data_pme) > 0 ): ?>
		<div class="col-lg-1">Ekspedisi</div>
		<div class="col-lg-3">
			<select class="form-control ekspedisi">
				<option value="">-- Pilih Ekspedisi --</option>
				<?php foreach ($list_ekspedisi as $key => $value): ?>
					<option value="<?php echo $value['id']; ?>"><?php echo $value['nama']; ?></option>
				<?php endforeach ?>
			</select>
		</div>
		<div class="col-lg-5 d-flex align-items-center">
			<div class="col-md-3">Total Tonase</div>
			<div class="col-md-3">
				<input type="text" class="form-control text-right total-kg" readonly />
			</div>
			<div class="col-md-1">Kg</div>
			<div class="col-md-3">
				<input type="text" class="form-control text-right total-zak" readonly />
			</div>
			<div class="col-md-1">Zak</div>
		</div>
		<div class="col-lg-3 d-flex align-items-center pull-right">
			<div class="col-md-6">
				<button type="button" class="form-control btn-primary" onclick="spm.save_spm(this)"><i class="fa fa-check"></i> Simpan</button>
			</div>
			<div class="col-md-6">
				<button type="button" class="form-control btn-primary">Cetak SPM</button>
			</div>
		</div>
	<?php else: ?>
		<div class="col-lg-9"></div>
		<div class="col-lg-3 pull-right">
			<div class="col-md-6 pull-right">
				<button type="button" class="form-control btn-primary pull-right" onclick="spm.load_form_cetak_spm()">Cetak SPM</button>
			</div>
		</div>
	<?php endif ?>
</div>
<?php if ( count($data_pme) > 0 ): ?>
	<table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
		<tbody>
			<?php foreach ($data_pme as $k_pme => $v_pme): ?>
				<tr class="v-center">
					<th class="text-center">Kota</th>
					<td class="" colspan="8"><?php echo $v_pme['nama']; ?></td>
					<!-- <td colspan="3"></td>
					<td>
						<button type="button" class="form-control btn-primary"><i class="fa fa-check"></i> Simpan</button>
					</td>
					<td></td>
					<td colspan="2">
						<button type="button" class="form-control btn-primary">Cetak SPM</button>
					</td> -->
				</tr>
				<tr class="v-center">
					<th class="text-center">Ekspedisi</th>
					<th class="text-center">Tgl Kirim</th>
					<th class="text-center">Peternak</th>
					<th class="text-center">Kandang</th>
					<th class="text-center">Alamat</th>
					<th class="text-center">Pakan</th>
					<th class="text-center">Tonase</th>
					<th class="text-center">Zak</th>
					<th class="text-center">
						<span>Pilih</span><br>
						<input type="checkbox" class="<?php echo 'check-pme-all'.$v_pme['id']; ?>" data-target="<?php echo 'check-pme'.$v_pme['id']; ?>" onclick="spm.mark_view_all(this)">
					</th>
				</tr>
				<?php foreach ($v_pme['detail'] as $k_detail => $v_detail): ?>
					<tr class="data" data-idrcnkirim="<?php echo $v_detail['id']; ?>">
						<td><?php echo $v_detail['nama_ekspedisi']; ?></td>
						<td class="text-center"><?php echo tglIndonesia($v_detail['tgl_kirim'], '-', ' '); ?></td>
						<td><?php echo $v_detail['mitra']; ?></td>
						<td class="text-center"><?php echo $v_detail['kandang']; ?></td>
						<td><?php echo $v_detail['alamat']; ?></td>
						<td><?php echo $v_detail['pakan']; ?></td>
						<td class="text-right tonase"><?php echo angkaDecimal($v_detail['tonase']); ?></td>
						<td class="text-right zak"><?php echo angkaRibuan($v_detail['zak']); ?></td>
						<td class="text-center">
							<input type="checkbox" class="<?php echo 'check-pme'.$v_pme['id']; ?>" data-parent="<?php echo 'check-pme-all'.$v_pme['id']; ?>" onclick="spm.mark_view(this)">
						</td>
					</tr>
				<?php endforeach ?>
			<?php endforeach ?>
		</tbody>
	</table>
<?php else: ?>
	<table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
		<tbody>
			<tr>
				<td>Data tidak ditemukan.</td>
			</tr>
		</tbody>
	</table>
<?php endif ?>