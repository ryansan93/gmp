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
			: <label class="control-label"><?php echo $data['no_order']; ?></label>
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
			: <label class="control-label"><?php echo strtoupper($data_op['nama_prs']); ?></label>
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
		<?php
			$hide_non_opkp = '';
			$hide_opkp = 'hide';
			if ( $data['jenis_kirim'] == 'opkp' ) {
				$hide_non_opkp = 'hide';
				$hide_opkp = '';
			}
		?>

		<table class="table table-bordered table-hover tbl_detail_brg non_opkp <?php echo $hide_non_opkp; ?>" style="margin-bottom: 0px;">
			<thead>
				<tr>
					<th class="col-lg-2">Jenis Pakan</th>
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
								foreach ($pakan as $k_pakan => $v_pakan) {
									if ( $v_pakan['kode'] == $v_det['item'] ) {
										echo $v_pakan['nama'];
									}
								} 
							?>
						</td>
						<td class="text-right">
							<?php echo angkaRibuan($v_det['jumlah']) ?>
						</td>
						<td>
							<?php echo $v_det['kondisi']; ?>
						</td>
					</tr>
				<?php endforeach ?>
			</tbody>
		</table>

		<table class="table table-bordered table-hover tbl_detail_brg opkp <?php echo $hide_opkp; ?>" style="margin-bottom: 0px;">
			<thead>
				<tr>
					<th class="col-lg-2">No. SJ Asal</th>
					<th class="col-lg-2">Jenis Pakan</th>
					<th class="col-lg-2">Jumlah</th>
					<th class="col-lg-2">Kondisi</th>
				</tr>
			</thead>
			<tbody>
				<?php $jml_detail = 0; ?>
				<?php foreach ($data['detail'] as $k_det => $v_det): ?>
					<?php $barang = null; ?>
					<tr>
						<td>
							<?php echo $v_det['no_sj_asal']; ?>
							<?php 
								foreach ($no_sj_asal as $k_nsa => $v_nsa) {
									if ( $v_nsa['no_sj'] == $v_det['no_sj_asal'] ) {
										$barang = $v_nsa['barang'];
									}
								} 
							?>
							</select>
						</td>
						<td>
							<?php if ( !empty($barang) ): ?>
								<?php 
									foreach ($pakan as $k_pakan => $v_pakan) {
										if ( $v_pakan['kode'] == $v_det['item'] ) {
											echo strtoupper($v_pakan['nama']);
										}
									}
								?>
							<?php endif ?>
						</td>
						<td class="text-right">
							<?php echo angkaRibuan($v_det['jumlah']) ?>
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
<div class="form-group d-flex align-items-center">
	<div class="col-lg-12">
		<button type="button" class="btn btn-default cursor-p pull-right" title="ADD" style="margin-left: 5px;" data-id="<?php echo $data['id']; ?>" onclick="window.open('transaksi/PengirimanPakan/cetak_nota_kiriman/<?php echo $data['id']; ?>')"> 
			<i class="fa fa-print" aria-hidden="true"></i> Cetak
		</button>
		<?php if ( !$terima ): ?>
			<div class="pull-right" style="border-right: 1px solid black;">
				<button type="button" class="btn btn-primary cursor-p pull-right" title="ADD" style="margin-right: 5px;" data-id="<?php echo $data['id']; ?>" onclick="pp.changeTabActive(this)" data-href="pengiriman" data-resubmit="edit"> 
					<i class="fa fa-edit" aria-hidden="true"></i> Edit
				</button>
			</div>
			<button type="button" class="btn btn-danger cursor-p pull-right" title="ADD" style="margin-right: 5px;" data-id="<?php echo $data['id']; ?>" onclick="pp.delete(this)"> 
				<i class="fa fa-trash" aria-hidden="true"></i> Hapus
			</button>
		<?php endif ?>
	</div>
</div>