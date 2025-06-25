<div class="modal-header header">
	<span class="modal-title">Order DOC</span>
	<button type="button" class="close" data-dismiss="modal">&times;</button>
</div>
<div class="modal-body body">
	<div class="row">
		<div class="col-lg-12 detailed">
			<input type="hidden" data-noreg="">

			<form role="form" class="form-horizontal">
				<div class="form-group d-flex align-items-center">
					<div class="col-lg-6 nama_mitra"><b>Nama Mitra + Populasi</b></div>
				</div>
				<div class="form-group d-flex align-items-center">
					<div class="col-lg-4">No Order</div>
					<div class="col-lg-3">
						<span>:</span>
						<span><?php echo $data_order_doc['no_order']; ?></span>
					</div>
				</div>
				<div class="form-group d-flex align-items-center">
					<div class="col-lg-4">Rencana Tiba Kandang</div>
					<div class="col-lg-4">
						<span>:</span>
						<span><?php echo tglIndonesia($data_order_doc['rencana_tiba'], '-', ' '); ?></span>
					</div>
				</div>
				<div class="form-group d-flex align-items-center">
					<div class="col-lg-4">Supplier</div>
					<div class="col-lg-5">
						<?php 
							$nama_supl = '';
							foreach ($supplier as $k_supl => $v_supl){
								if ( $v_supl['nomor'] == $data_order_doc['supplier'] ) {
									$nama_supl = $v_supl['nama'];
								}
							}
						?>
						<span>:</span>
						<span><?php echo $nama_supl; ?></span>
					</div>
				</div>
				<div class="form-group d-flex align-items-center">
					<div class="col-lg-4">DOC</div>
					<div class="col-lg-4">
						<?php
							$nama_doc = '';
							foreach ($data_doc as $k_doc => $v_doc){
								if ( $v_doc['kode'] == $data_order_doc['item'] ) {
									$nama_doc = $v_doc['nama'];
								}
							}
						?>
						<span>:</span>
						<span><?php echo $nama_doc; ?></span>
					</div>
				</div>
				<div class="form-group d-flex align-items-center">
					<div class="col-lg-4">Jenis Box</div>
					<div class="col-lg-3">
						<span>:</span>
						<span>PLASTIK</span>
					</div>
				</div>
				<div class="form-group d-flex align-items-center">
					<div class="col-lg-4">Jumlah</div>
					<div class="col-lg-2">
						<span>:</span>
						<span><?php echo angkaRibuan($data_order_doc['jml_ekor']); ?></span>
					</div>
					<div class="col-sm-1">Ekor</div>
				</div>
				<div class="form-group d-flex align-items-center">
					<div class="col-lg-4"></div>
					<div class="col-lg-2">
						<span>:</span>
						<span><?php echo angkaRibuan($data_order_doc['jml_box']); ?></span>
					</div>
					<div class="col-sm-1">Box</div>
				</div>
				<div class="form-group d-flex align-items-center">
					<div class="col-lg-4">Keterangan</div>
					<div class="col-lg-8">
						<span>:</span>
						<span><?php echo $data_order_doc['keterangan']; ?></span>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>