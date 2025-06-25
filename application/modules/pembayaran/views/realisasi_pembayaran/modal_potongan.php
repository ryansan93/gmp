<div class="modal-header no-padding">
	<span class="modal-title"><b>POTONGAN PEMBAYARAN</b></span>
</div>
<div class="modal-body" style="padding-bottom: 0px;">
	<div class="row detailed">
		<div class="col-xs-12 detailed no-padding">
			<table class="table table-bordered" style="margin-bottom: 0px;">
				<thead>
					<tr>
						<th class="col-xs-2 text-center">No. COA</th>
						<th class="col-xs-6 text-center">Nama Potongan</th>
						<th class="col-xs-4 text-center">Nominal</th>
					</tr>
				</thead>
				<tbody>
					<?php if ( !empty($data) ): ?>
						<?php foreach ($data as $k_data => $v_data): ?>
							<tr class="data" data-id="<?php echo $v_data['id']; ?>">
								<td class="no_coa"><?php echo $v_data['no_coa']; ?></td>
								<td class="nama"><?php echo $v_data['nama']; ?></td>
								<td>
                                    <input type="text" class="form-control text-right nominal" data-tipe="decimal" placeholder="Nominal">
                                </td>
							</tr>
						<?php endforeach ?>
					<?php else: ?>
						<tr>
							<td colspan="3">Data tidak ditemukan.</td>
						</tr>
					<?php endif ?>
				</tbody>
			</table>
		</div>
		<div class="col-xs-12 detailed no-padding">
			<hr style="margin-top: 10px; margin-bottom: 10px;">
		</div>
		<div class="col-xs-12 detailed no-padding">
			<div class="col-xs-6 no-padding" style="padding-right: 5px;">
				<button type="button" class="col-xs-12 btn btn-danger" data-dismiss="modal"><i class="fa fa-times"></i> Batal</button>
			</div>
			<div class="col-xs-6 no-padding" style="padding-left: 5px;">
				<button type="button" class="col-xs-12 btn btn-primary" onclick="rp.simpanPotongan(this)"><i class="fa fa-check"></i> Simpan Potongan</button>
			</div>
		</div>
	</div>
</div>