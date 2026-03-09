<div class="modal-header">
	<span class="modal-title"><b>PEMBAYARAN</b></span>
	<button type="button" class="close" data-dismiss="modal">&times;</button>
</div>
<div class="modal-body" style="padding-bottom: 0px;">
	<div class="row detailed">
		<div class="col-xs-12 detailed no-padding">
			<form role="form" class="form-horizontal">
				<div class="col-xs-12 no-padding">
					<div class="col-xs-3 no-padding">TRANSAKSI</div>
					<div class="col-xs-9 no-padding">: <?php echo strtoupper($data['jenis_transaksi']); ?></div>
				</div>
                <div class="col-xs-12 no-padding">
					<div class="col-xs-3 no-padding">SUPPLIER</div>
					<div class="col-xs-9 no-padding">: <?php echo strtoupper($data['nama_supl']); ?></div>
				</div>
				<div class="col-xs-12 no-padding">
					<div class="col-xs-3 no-padding">NO. REKENING</div>
					<div class="col-xs-9 no-padding">: <?php echo strtoupper($data['no_rek']); ?></div>
				</div>
				<div class="col-xs-12 no-padding">
					<div class="col-xs-3 no-padding">ATAS NAMA</div>
					<div class="col-xs-9 no-padding">: <?php echo strtoupper($data['atas_nama']); ?></div>
				</div>
				<div class="col-xs-12 no-padding">
					<div class="col-xs-3 no-padding">BANK</div>
					<div class="col-xs-9 no-padding">: <?php echo strtoupper($data['bank']); ?></div>
				</div>
                <div class="col-xs-12 no-padding">
					<div class="col-xs-3 no-padding">TGL PENGAJUAN</div>
					<div class="col-xs-9 no-padding">: <?php echo strtoupper(tglIndonesia($data['tgl_pengajuan'], '-', ' ')); ?></div>
				</div>
                <div class="col-xs-12 no-padding">
					<div class="col-xs-3 no-padding">JML PENGAJUAN TF</div>
					<div class="col-xs-9 no-padding">: <b><?php echo strtoupper(angkaDecimal($data['jml_transfer'])); ?></b></div>
				</div>
                <div class="col-xs-12 no-padding">
					<div class="col-xs-3 no-padding">LAMPIRAN PENGAJUAN</div>
					<div class="col-xs-9 no-padding">: <a href="uploads/<?php echo $data['lampiran']; ?>" target="_blank"><?php echo $data['lampiran']; ?></a></div>
				</div>
                <div class="col-xs-12 no-padding">
					<div class="col-xs-3 no-padding">DI AJUKAN OLEH</div>
					<div class="col-xs-9 no-padding">: <?php echo strtoupper($data['deskripsi'].' '.$data['waktu']); ?></div>
				</div>
				<div class="col-xs-12 no-padding">
                    <hr style="margin-top: 10px; margin-bottom: 10px;">
				</div>
                <div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
					<div class="col-xs-3 no-padding"><b>KODE TRANSAKSI</b></div>
					<div class="col-xs-9 no-padding">: <?php echo strtoupper($data['kode_trans']); ?></div>
				</div>
                <div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
					<div class="col-xs-3 no-padding"><b>TGL PEMBAYARAN</b></div>
					<div class="col-xs-9 no-padding">: <?php echo strtoupper(tglIndonesia($data['tgl_bayar'], '-', ' ')); ?></div>
				</div>
                <div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
					<div class="col-xs-3 no-padding"><b>NO BUKTI PEMBAYARAN</b></div>
					<div class="col-xs-9 no-padding">: <?php echo strtoupper($data['kode_trans']); ?></div>
				</div>
                <div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
					<div class="col-xs-3 no-padding"><b>LAMPIRAN PEMBAYARAN</b></div>
					<div class="col-xs-9 no-padding">
						
						<?php if(count($attachment) > 0){ ?>

							<div class="flex flex-row gap-2">
								<span>:</span>							
								<?php foreach($attachment as $file){ ?>
									<a style="text-decoration:none;" href="<?php echo base_url() . 'uploads/'. $file['name_file_old']; ?>" target="_blank">
										<button type="button" class="flex items-center justify-center border border-gray-300 rounded p-1 hover:bg-gray-100" style="width:auto;">
											<?php echo strtoupper(htmlspecialchars($file['name_file_old'])); ?>
										</button>
									</a>
								<?php } ?>	
							</div>
							
						<?php } else { ?>

							<?php if ( !empty($data['lampiran_realisasi']) ) { ?>
								: <a href="uploads/<?php echo $data['lampiran_realisasi']; ?>" target="_blank"><?php echo $data['lampiran_realisasi']; ?></a>
							<?php } else { ?>
								: -
							<?php } ?>

						<?php } ?>

					</div>
				</div>
                <div class="col-xs-12 no-padding">
					<div class="col-xs-3 no-padding"><b>KETERANGAN PEMBAYARAN</b></div>
					<div class="col-xs-9 no-padding">: <?php echo strtoupper($data['ket_realisasi']); ?></div>
				</div>
				<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
				<div class="col-xs-12 no-padding lock_btn_fiskal" data-date="<?php echo substr($data['tgl_bayar'], 0, 10); ?>" style="margin-top: 5px;">
                    <div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
                        <button type="button" class="col-xs-12 btn btn-default" onclick="vp.printPreview(this)" data-id="<?php echo exEncrypt($data['id']); ?>"><i class="fa fa-print"></i> Cetak</button>
                    </div>
					<?php if ( $data['verifikasi'] == 1 ) { ?>
						<div class="col-xs-6 no-padding" style="padding-right: 5px;">
							<button type="button" class="col-xs-12 btn btn-danger" onclick="vp.delete(this)" data-id="<?php echo $data['id']; ?>" data-table="<?php echo $data['tbl_name']; ?>"><i class="fa fa-trash"></i> Hapus</button>
						</div>
						<div class="col-xs-6 no-padding" style="padding-left: 5px;">
							<button type="button" class="col-xs-12 btn btn-primary" onclick="vp.formRealisasiBayarEdit(this)" data-id="<?php echo $data['id']; ?>" data-table="<?php echo $data['tbl_name']; ?>"><i class="fa fa-edit"></i> Edit</button>
						</div>
					<?php } ?>
				</div>
			</form>
		</div>
	</div>
</div>