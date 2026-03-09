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
					<div class="col-xs-12 no-padding"><b>TGL PEMBAYARAN</b></div>
					<div class="col-xs-12 no-padding">
                        <div class="input-group date lock_date_fiskal" id="tglBayar" data-val="<?php echo $data['tgl_bayar']; ?>">
					        <input type="text" class="form-control text-center" data-required="1" placeholder="Tanggal" />
					        <span class="input-group-addon">
					            <span class="glyphicon glyphicon-calendar"></span>
					        </span>
					    </div>
                    </div>
				</div>
                <div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
					<div class="col-xs-12 no-padding"><b>NO BUKTI PEMBAYARAN</b></div>
					<div class="col-xs-12 no-padding">
                        <input type="text" class="form-control no_bukti" placeholder="NO. BUKTI" value="<?php echo $data['kode_trans']; ?>">
                    </div>
				</div>
                <div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
					<div class="col-xs-12 no-padding">
						<div class="row gap-2" style="margin-left:2px;">
							<b>LAMPIRAN PEMBAYARAN</b>
							<div onclick="vp.addRowLampiran_edit()" style="margin-left:10px; height:20px; width:20px; background-color:#9BDE8A; border-radius:2px; text-align:center; cursor:pointer;">
								<i class="fa fa-plus"></i>
							</div>					
						</div>
					</div>
					<div class="col-xs-12 no-padding">

						<div class="attachment-area" style="display:flex; flex-direction:column; gap:5px; margin-top:10px;">
							
							<?php if (count($attachment) > 0) {?>
								<?php foreach($attachment as $file){?>
									<div class="file-form" id_file="<?php echo $file["id"] ?>" style="display:flex; flex-direction:row; gap:5px">
										<a style="text-decoration:none;" href="<?php echo base_url() . 'uploads/'. $file['file_name']; ?>" target="_blank">
											<button type="button" class="name-file-button flex items-center justify-center border border-gray-300 rounded p-1 hover:bg-gray-100" style="width:auto;">
												<?php echo strtoupper(htmlspecialchars($file['name_file_old'])); ?>
											</button>
										</a>

										<input type="file" class="file_lampiran" data-old-file="<?php echo $file['file_name']; ?>" data-old-name="<?php echo htmlspecialchars($file['name_file_old']); ?>" onchange="vp.get_lampiran(this, event)" style="display:none;">
										<button type="button" class="btn btn-sm btn-warning" onclick="vp.edit_lampiran(this, event)">
											<i class="glyphicon glyphicon-paperclip cursor-p"></i>
										</button>

										<button type="button" onclick="vp.removeRowLampiran(this, event)" class="btn btn-remove btn-sm btn-danger">
											<i class="fa fa-trash"></i>
										</button>
									</div>
								<?php } ?>							
							
							<?php } else { ?>							
							
								<div class="row file-form" style="margin-left:2px;">
									<?php if ( !empty($data['lampiran_realisasi']) ) { ?>
										<a href="uploads/<?php echo $data['lampiran_realisasi']; ?>" target="_blank"><?php echo $data['lampiran_realisasi']; ?></a>
									<?php } ?>

									<!-- < ?php if (count($attachment) < 1){?>
									<label class="">
										<input type="file" onchange="showNameFile(this)" class="file_lampiran" name="" placeholder="Bukti Transfer" data-allowtypes="pdf|PDF|jpg|JPG|jpeg|JPEG|png|PNG" style="display: none;">
										<i class="glyphicon glyphicon-paperclip cursor-p"></i>
									</label>
									< ?php } ?>
								</div> -->

							<?php } ?>							
							

						</div>


                    </div>
				</div>
                <div class="col-xs-12 no-padding">
					<div class="col-xs-12 no-padding"><b>KETERANGAN PEMBAYARAN</b></div>
					<div class="col-xs-12 no-padding">
                        <textarea class="form-control ket_bayar uppercase" placeholder="Keterangan" data-required="1"><?php echo $data['ket_realisasi']; ?></textarea>
                    </div>
				</div>
				<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
				<div class="col-xs-12 no-padding">
                    <button type="button" class="col-xs-12 btn btn-primary pull-right" onclick="vp.edit(this)" data-id="<?php echo $data['id']; ?>" data-table="<?php echo $data['tbl_name']; ?>"><i class="fa fa-save"></i> Simpan Perubahan</button>
				</div>
			</form>
		</div>
	</div>
</div>