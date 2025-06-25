<div class="form-group d-flex align-items-center">
	<div class="col-lg-12 d-flex align-items-center no-padding">
		<div class="col-lg-2 text-left">Jenis Pengiriman</div>
		<div class="col-lg-2">
			: <label class="control-label"><?php echo strtoupper($data['jenis_kirim']); ?></label>
		</div>
		<div class="col-lg-2"></div>
		<div class="col-lg-2">Ongkos Angkut</div>
		<div class="col-lg-2">
			: <label class="control-label"><?php echo angkaDecimal($data['ongkos_angkut']); ?></label>
		</div>
	</div>
</div>
<div class="form-group d-flex align-items-center">
	<div class="col-lg-12 d-flex align-items-center no-padding">
		<div class="col-lg-2 text-left">No. Order</div>
		<div class="col-lg-2">
			: <label class="control-label"><?php echo strtoupper($data['no_order']); ?></label>
		</div>
	</div>
</div>
<?php
	$hide = 'hide';
	if ( $data['jenis_kirim'] == 'opks' ) {
		$hide = null;
	}
?>
<div class="form-group d-flex align-items-center <?php echo $hide; ?>">
	<div class="col-lg-12 d-flex align-items-center no-padding">
		<div class="col-lg-2">Perusahaan</div>
		<div class="col-lg-10">
			: <label class="control-label"><?php echo strtoupper($data_ov['nama_prs']); ?></label>
		</div>
	</div>
</div>
<div class="form-group d-flex align-items-center">
	<div class="col-lg-12 d-flex align-items-center no-padding">
		<div class="col-lg-2">Asal</div>
		<div class="col-lg-10">
			: <label class="control-label"><?php echo strtoupper($asal); ?></label>
		</div>
	</div>
</div>
<div class="form-group d-flex align-items-center">
	<div class="col-lg-12 d-flex align-items-center no-padding">
		<div class="col-lg-2">Tujuan</div>
		<div class="col-lg-10">
			: <label class="control-label"><?php echo strtoupper($tujuan); ?></label>
		</div>
	</div>
</div>
<div class="form-group d-flex align-items-center">
	<div class="col-lg-12 d-flex align-items-center no-padding">
		<div class="col-lg-2">Rencana Kirim</div>
		<div class="col-lg-2">
			: <label class="control-label"><?php echo strtoupper(tglIndonesia($data['tgl_kirim'], '-', ' ')); ?></label>
		</div>
		<div class="col-lg-2"></div>
		<div class="col-lg-2">Ekspedisi</div>
		<div class="col-lg-3">
			: <label class="control-label"><?php echo strtoupper($data['ekspedisi']); ?></label>
		</div>
	</div>
</div>
<div class="form-group d-flex align-items-center">
	<div class="col-lg-12 d-flex align-items-center no-padding">
		<div class="col-lg-2">Tgl Kirim</div>
		<div class="col-lg-2">
			: <label class="control-label"><?php echo strtoupper(tglIndonesia($data['tgl_kirim'], '-', ' ')); ?></label>
		</div>
		<div class="col-lg-2"></div>
		<div class="col-lg-2">No. Polisi</div>
		<div class="col-lg-2">
			: <label class="control-label"><?php echo strtoupper($data['no_polisi']); ?></label>
		</div>
	</div>
</div>
<div class="form-group d-flex align-items-center">
	<div class="col-lg-12 d-flex align-items-center no-padding">
		<div class="col-lg-2">No. SJ</div>
		<div class="col-lg-3">
			: <label class="control-label"><?php echo strtoupper($data['no_sj']); ?></label>
		</div>
		<div class="col-lg-1"></div>
		<div class="col-lg-2">Sopir</div>
		<div class="col-lg-2">
			: <label class="control-label"><?php echo strtoupper($data['sopir']); ?></label>
		</div>
	</div>
</div>
<div class="form-group d-flex align-items-center">
	<div class="col-lg-12"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
</div>
<div class="form-group d-flex align-items-center">
	<div class="col-lg-12 d-flex align-items-center">
		<table class="table table-bordered table-hover tbl_detail_brg" style="margin-bottom: 0px;">
			<thead>
				<tr>
					<th class="col-lg-2">Jenis OVK</th>
					<th class="col-lg-2">Jumlah</th>
					<th class="col-lg-2">Kondisi</th>
				</tr>
			</thead>
			<tbody>
				<?php $jml_detail = 0; ?>
				<?php foreach ($data['detail'] as $k_det => $v_det): ?>
					<tr>
						<td>
							<?php 
								foreach ($voadip as $k_voadip => $v_voadip) {
									if ( $v_voadip['kode'] == $v_det['item'] ) {
										echo $v_voadip['nama'];
									}
								}
							?>
						</td>
						<td>
							<?php echo angkaDecimal($v_det['jumlah']) ?>
						</td>
						<td>
							<?php echo $v_det['kondisi']; ?>
						</td>
					</tr>
				<?php endforeach ?>
			</tbody>
		</table>
	</div>
</div>
<div class="form-group d-flex align-items-center">
	<div class="col-lg-12"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
</div>
<?php if ( !$terima ): ?>
	<div class="form-group d-flex align-items-center">
		<div class="col-lg-12">
			<button type="button" class="btn btn-primary cursor-p pull-right" title="ADD" style="margin-left: 5px;" data-id="<?php echo $data['id']; ?>" onclick="pv.changeTabActive(this)" data-href="pengiriman" data-resubmit="edit"> 
				<i class="fa fa-edit" aria-hidden="true"></i> Edit
			</button>
			<button type="button" class="btn btn-danger cursor-p pull-right" title="ADD" style="margin-right: 5px;" data-id="<?php echo $data['id']; ?>" onclick="pv.delete(this)"> 
				<i class="fa fa-trash" aria-hidden="true"></i> Hapus
			</button>
		</div>
	</div>
<?php endif ?>