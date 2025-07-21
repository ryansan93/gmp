<div class="modal-header header">
	<span class="modal-title">Terima DOC Dikandang</span>
	<button type="button" class="close" data-dismiss="modal">&times;</button>
</div>
<div class="modal-body body">
	<div class="row">
		<div class="col-lg-12 detailed">
			<input type="hidden" data-terima="<?php echo $data_terima_doc['no_terima']; ?>" data-version="<?php echo $data_terima_doc['version']; ?>">
			<form role="form" class="form-horizontal">
				<div class="form-group d-flex align-items-center">
					<div class="col-lg-2">No. BBM</div>
					<div class="col-lg-4">
						<span>:</span>
						<span><b><?php echo $data_terima_doc['no_bbm']; ?></b></span>
					</div>
				</div>
				<div class="form-group d-flex align-items-center">
					<div class="col-lg-2">No. Order</div>
					<div class="col-lg-4">
						<span>:</span>
						<span><b><?php echo $data_terima_doc['order_doc']['no_order']; ?></b></span>
					</div>
					<div class="col-lg-2">No. SJ</div>
					<div class="col-lg-3">
						<span>:</span>
						<span>
							<b>
								<a href="<?php echo 'uploads/'.$data_terima_doc['path']; ?>" target="_blank"><?php echo $data_terima_doc['no_sj']; ?></a>
							</b>
						</span>
					</div>
				</div>
				<div class="form-group d-flex align-items-center">
					<div class="col-lg-12 nama_mitra"><b>Nama Mitra + Populasi</b></div>
				</div>
				<div class="form-group d-flex align-items-center">
					<div class="col-lg-3">Tgl Kirim DOC</div>
					<div class="col-lg-4">
						<span>:</span>
						<span><?php echo dateTimeFormat($data_terima_doc['kirim'], '-', ' ', true); ?></span>
					</div>
				</div>
				<div class="form-group d-flex align-items-center">
					<div class="col-lg-3">Tgl Tiba Kandang</div>
					<div class="col-lg-4">
						<span>:</span>
						<span><?php echo dateTimeFormat($data_terima_doc['datang'], '-', ' ', true); ?></span>
					</div>
				</div>
				<div class="form-group d-flex align-items-center">
					<div class="col-lg-3">No Polisi</div>
					<div class="col-lg-3">
						<span>:</span>
						<span><?php echo $data_terima_doc['nopol']; ?></span>
					</div>
				</div>
				<div class="form-group d-flex align-items-center">
					<div class="col-lg-3">Supplier</div>
					<div class="col-lg-5">
						<?php 
							$nama_supl = null;
							foreach ($supplier as $k_supl => $v_supl) {
								if ( $v_supl['nomor'] == $data_terima_doc['supplier'] ) {
									$nama_supl = $v_supl['nama'];
								}
							} 
						?>
						<span>:</span>
						<span><?php echo $nama_supl; ?></span>
					</div>
				</div>
				<div class="form-group d-flex align-items-center">
					<div class="col-lg-3">DOC</div>
					<div class="col-lg-4">
						<?php 
							$nama_doc = null;
							foreach ($data_doc as $k_doc => $v_doc) {
								if ( $v_doc['kode'] == $data_terima_doc['order_doc']['item'] ) {
									$nama_doc = $v_doc['nama'];
								}
							}
						?>
						<span>:</span>
						<span><?php echo $nama_doc; ?></span>
					</div>
				</div>
				<div class="form-group d-flex align-items-center">
					<div class="col-lg-3">Jenis Box</div>
					<div class="col-lg-3">
						<span>:</span>	
						<span>PLASTIK</span>
					</div>
				</div>
				<div class="form-group d-flex align-items-center">
					<div class="col-lg-3">Jumlah</div>
					<div class="col-lg-2">
						<span>:</span>
						<span><?php echo angkaRibuan($data_terima_doc['jml_ekor']); ?></span>
					</div>
					<div class="col-sm-1">Ekor</div>
				</div>
				<div class="form-group d-flex align-items-center">
					<div class="col-lg-3"></div>
					<div class="col-lg-2">
						<span>:</span>
						<span><?php echo angkaRibuan($data_terima_doc['jml_box']); ?></span>
					</div>
					<div class="col-sm-1">Box</div>
				</div>
				<div class="form-group align-items-center hide">
					<div class="col-lg-3">Harga</div>
					<div class="col-lg-4">
						<span>:</span>
						<span><?php echo 'Rp. '.angkaDecimal($data_terima_doc['harga']); ?></span>
					</div>
				</div>
				<div class="form-group align-items-center hide">
					<div class="col-lg-3">Total</div>
					<div class="col-lg-4">
						<span>:</span>
						<span><?php echo 'Rp. '.angkaDecimal($data_terima_doc['total']); ?></span>
					</div>
				</div>
				<div class="form-group d-flex align-items-center">
					<div class="col-lg-3">Kondisi</div>
					<div class="col-lg-3">
						<span>:</span>
						<span><?php echo $data_terima_doc['kondisi']; ?></span>
					</div>
				</div>
				<div class="form-group d-flex align-items-center">
					<div class="col-lg-3">Uniformity (%)</div>
					<div class="col-lg-3">
						<span>:</span>
						<span><?php echo angkaDecimal($data_terima_doc['uniformity']); ?></span>
					</div>
				</div>
				<div class="form-group d-flex align-items-center">
					<div class="col-lg-3">Keterangan</div>
					<div class="col-lg-8">
						<span>:</span>
						<span><?php echo empty($data_terima_doc['keterangan']) ? '-' : $data_terima_doc['keterangan']; ?></span>
					</div>
				</div>
				<div class="form-group d-flex align-items-center">
					<div class="col-lg-12">
						<hr style="margin-top: 5px; margin-bottom: 5px;">
					</div>
				</div>
				<div class="form-group d-flex align-items-center">
					<div class="col-lg-10">
						<div class="col-md-12 no-padding">
							<div class="col-lg-3 no-padding pull-left">
								<label class="control-label" style="padding-top: 0px;"><u>Aktifitas</u></label>
							</div>
						</div>
						<div class="col-md-12 no-padding">
							<div class="col-lg-12 no-padding pull-left">
								<ul>
									<?php foreach ($data_terima_doc['logs'] as $k_log => $v_log): ?>
										<li><?php echo strtoupper( $v_log['deskripsi'].' pada '.tglIndonesia(substr($v_log['waktu'], 0, 10), '-', ' ', true).' '.substr($v_log['waktu'], 11, 5) ) ?></li>
									<?php endforeach ?>
								</ul>
							</div>
						</div>
					</div>
					<div class="col-lg-2">
						<button type="button" class="btn btn-danger pull-right" data-id="<?php echo $data_terima_doc['id']; ?>" onclick="odvp.delete_terima_doc(this)"><i class="fa fa-trash"></i> Hapus</button>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>