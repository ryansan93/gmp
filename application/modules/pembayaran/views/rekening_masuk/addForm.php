<div class="modal-header">
	<span class="modal-title"><b>TAMBAH DATA</b></span>
	<button type="button" class="close" data-dismiss="modal">&times;</button>
</div>
<div class="modal-body" style="padding-bottom: 0px;">
	<div class="row detailed">
		<div class="col-xs-12 detailed no-padding">
			<form role="form" class="form-horizontal">
				<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
					<div class="col-xs-4 no-padding">No. Pembayaran</div>
					<div class="col-xs-1 no-padding text-center">:</div>
					<div class="col-xs-7 no-padding">
						<input type="text" class="form-control" placeholder="Nomor" readonly />
					</div>
				</div>
				<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
					<div class="col-xs-4 no-padding">Tanggal</div>
					<div class="col-xs-1 no-padding text-center">:</div>
					<div class="col-xs-7 no-padding">
						<div class="input-group date" id="tanggal">
					        <input type="text" class="form-control text-center" data-required="1" placeholder="Tanggal" />
					        <span class="input-group-addon">
					            <span class="glyphicon glyphicon-calendar"></span>
					        </span>
					    </div>
					</div>
				</div>
				<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
					<div class="col-xs-4 no-padding">Perusahaan</div>
					<div class="col-xs-1 no-padding text-center">:</div>
					<div class="col-xs-7 no-padding">
						<select class="form-control perusahaan" data-required="1">
                            <option value="">-- Pilih Perusahaan --</option>
                            <?php foreach ($perusahaan as $key => $value) { ?>
                                <option value="<?php echo $value['kode']; ?>"><?php echo strtoupper($value['nama']); ?></option>
                            <?php } ?>
                        </select>
					</div>
				</div>
				<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
					<div class="col-xs-4 no-padding">Bakul</div>
					<div class="col-xs-1 no-padding text-center">:</div>
					<div class="col-xs-7 no-padding">
                        <select class="form-control pelanggan">
                            <option value="">-- Pilih Pelanggan --</option>
                            <?php foreach ($pelanggan as $key => $value) { ?>
                                <option value="<?php echo $value['kode']; ?>"><?php echo strtoupper($value['nama'].' ('.$value['kab_kota'].')'); ?></option>
                            <?php } ?>
                        </select>
					</div>
				</div>
                <div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
					<div class="col-xs-4 no-padding">Jumlah Transfer</div>
                    <div class="col-xs-1 no-padding text-center">:</div>
					<div class="col-xs-7 no-padding">
                        <input type="text" class="form-control text-right jml_transfer" data-required="1" data-tipe="decimal" placeholder="Rp.">
                    </div>
				</div>
				<div class="col-xs-12 no-padding">
					<div class="col-xs-4 no-padding">Keterangan</div>
                    <div class="col-xs-1 no-padding text-center">:</div>
					<div class="col-xs-7 no-padding">
                        <textarea class="form-control ket" placeholder="KETERANGAN" data-required="1"></textarea>
                    </div>
				</div>
				<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
				<div class="col-xs-12 no-padding">
					<button type="button" class="btn btn-primary pull-right" onclick="rm.save()"><i class="fa fa-save"></i> Simpan</button>
				</div>
			</form>
		</div>
	</div>
</div>