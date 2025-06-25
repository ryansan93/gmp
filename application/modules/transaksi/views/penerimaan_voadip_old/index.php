<?php // cetak_r($supplier); ?>
<div class="row content-panel detailed">
	<div class="col-lg-12 detailed">
		<form role="form" class="form-horizontal">
			<div class="col-lg-12" id="penerimaan-voadip">
				<div class="form-group d-flex align-items-center">
					<div class="col-lg-1">Pilih Supplier</div>
					<div class="col-lg-3">
						<select class="form-control supplier" onchange="pv.set_data_order(this)" data-required="1">
							<option value="">-- Pilih Supplier --</option>
							<?php foreach ($supplier as $k_supl => $v_supl): ?>
								<option value="<?php echo $v_supl['nomor']; ?>" ><?php echo $v_supl['nama']; ?></option>
							<?php endforeach ?>
						</select>
					</div>
				</div>
				<div class="form-group">
					<div class="col-lg-12">
						<table class="table table-bordered tb_list_voadip">
							<thead>
								<tr>
									<th class="text-center">No. Order</th>
									<th class="text-center">Tanggal</th>
									<th class="text-center">Tujuan Kirim</th>
									<th class="text-center">Item</th>
									<th class="text-center col-sm-1">Jumlah</th>
									<th class="text-center col-sm-1">No. SJ</th>
									<th class="text-center col-sm-2">Tanggal</th>
									<th class="text-center col-sm-2">Alamat Terima</th>
									<th class="text-center col-sm-1">Terima</th>
									<th class="text-center col-sm-2">Keterangan</th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td colspan="10">Data tidak ditemukan.</td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>
				<div class="form-group">
					<div class="col-lg-12">
						<button type="button" class="btn btn-primary pull-right" onclick="pv.save()"><i class="fa fa-save"></i> Simpan</button>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>