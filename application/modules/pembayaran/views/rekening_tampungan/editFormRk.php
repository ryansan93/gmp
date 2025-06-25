<div class="modal-header no-padding" style="padding-bottom: 10px;">
	<span class="modal-title"><b>REKENING KELUAR</b></span>
</div>
<div class="modal-body" style="padding-bottom: 0px;">
	<div class="row detailed">
		<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
			<div class="col-xs-12 no-padding"><label class="control-label text-left">Tanggal</label></div>
			<div class="col-xs-12 no-padding">
				<div class="input-group date" id="tgl_rk">
			        <input type="text" class="form-control text-center" data-required="1" placeholder="Tanggal" data-val="<?php echo $data['tanggal']; ?>" />
			        <span class="input-group-addon">
			            <span class="glyphicon glyphicon-calendar"></span>
			        </span>
			    </div>
			</div>
		</div>
		<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
			<div class="col-xs-12 no-padding"><label class="control-label text-left">Perusahaan</label></div>
			<div class="col-xs-12 no-padding">
				<select class="form-control perusahaan" width="100%" data-required="1">
					<option value="">-- Pilih Perusahaan --</option>
					<?php if ( count($perusahaan) > 0 ): ?>
						<?php foreach ($perusahaan as $k_perusahaan => $v_perusahaan): ?>
							<?php
								$selected = null;
								if ( $v_perusahaan['kode'] == $data['perusahaan'] ) {
									$selected = 'selected';
								}
							?>
							<option value="<?php echo $v_perusahaan['kode']; ?>" <?php echo $selected; ?> ><?php echo strtoupper($v_perusahaan['nama']); ?></option>
						<?php endforeach ?>
					<?php endif ?>
				</select>
			</div>
		</div>
		<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
			<div class="col-xs-12 no-padding"><label class="control-label text-left">Pelanggan</label></div>
			<div class="col-xs-12 no-padding">
				<select class="form-control pelanggan" width="100%" data-required="1">
					<option value="">-- Pilih Pelanggan --</option>
					<?php if ( count($pelanggan) > 0 ): ?>
						<?php foreach ($pelanggan as $k_plg => $v_plg): ?>
							<?php
								$selected = null;
								if ( $v_plg['nomor'] == $data['no_pelanggan'] ) {
									$selected = 'selected';
								}
							?>
							<option value="<?php echo $v_plg['nomor']; ?>" <?php echo $selected; ?> ><?php echo strtoupper($v_plg['nama']).' ('.strtoupper($v_plg['kab_kota']).')'; ?></option>
						<?php endforeach ?>
					<?php endif ?>
				</select>
			</div>
		</div>
		<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
			<div class="col-xs-12 no-padding"><label class="control-label text-left">Nominal (Rp.)</label></div>
			<div class="col-xs-12 no-padding">
				<input type="text" class="form-control text-right nominal" data-required="1" placeholder="Nominal" data-tipe="decimal" value="<?php echo angkaDecimal($data['nominal']); ?>" />
			</div>
		</div>
		<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
			<div class="col-xs-12 no-padding"><label class="control-label text-left">Lampiran</label></div>
			<div class="col-lg-12 no-padding">
				<?php
					$hide = 'hide';
					$attrHref = null;
					if ( !empty($data['lampiran']) ) {
						$hide = null;
						$attrHref = 'href="uploads/'.$data['lampiran'].'"';
					}
				?>

				<a class="<?php echo $hide; ?>" target="_blank"><?php echo $data['lampiran']; ?></a>
				<label class="">
					<input type="file" onchange="showNameFile(this)" class="file_lampiran" placeholder="Bukti Rekening Masuk" data-allowtypes="pdf|PDF|jpg|JPG|jpeg|JPEG|png|PNG" style="display: none;">
					<i class="glyphicon glyphicon-paperclip cursor-p"></i>
				</label>
			</div>
		</div>
		<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
			<div class="col-xs-12 no-padding"><label class="control-label text-left">Keterangan</label></div>
			<div class="col-xs-12 no-padding">
				<textarea class="form-control keterangan" placeholder="Keterangan"><?php echo $data['keterangan']; ?></textarea>
			</div>
		</div>
		<div class="col-md-12 no-padding" style="margin-top: 5px;">
			<div class="col-lg-12 no-padding text-left"><label class="control-label">Jenis</label></div>
	        <div class="col-lg-12">
	            <div class="radio" style="margin-top: 0px;">
					<label><input type="radio" name="optradio" value="1" <?php echo $data['jenis'] == 1 ? 'checked' : ''; ?>>Saldo Pelanggan</label>
				</div>
				<div class="radio" style="margin-bottom: 0px;">
					<label><input type="radio" name="optradio" value="0" <?php echo $data['jenis'] == 0 ? 'checked' : ''; ?>>Pengembalian Ke Rekening Pelanggan</label>
				</div>
	        </div>
		</div>
		<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
		<div class="col-xs-12 no-padding">
			<div class="col-xs-6 no-padding" style="padding-right: 5px;">
				<button type="button" class="col-xs-12 btn btn-danger" onclick="rt.viewFormRk(this)" data-kode="<?php echo $data['kode']; ?>"><i class="fa fa-times"></i> Batal</button>
			</div>
			<div class="col-xs-6 no-padding" style="padding-left: 5px;">
				<button type="button" class="col-xs-12 btn btn-primary" onclick="rt.editRk(this)" data-kode="<?php echo $data['kode']; ?>"><i class="fa fa-save"></i> Simpan Perubahan</button>
			</div>
		</div>
	</div>
</div>