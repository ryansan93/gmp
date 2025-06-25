<?php if ( $akses['a_submit'] == 1 ): ?>
	<div class="col-lg-12 no-padding">
		<div class="col-lg-2 no-padding"><label class="control-label text-left">Tgl Pengakuan Bad Debt</label></div>
		<div class="col-lg-1 no-padding" style="max-width: 2%;"><label class="control-label">:</label></div>
		<div class="col-lg-2" style="padding: 0px 30px 0px 0px;">
			<div class="input-group date" id="tglBayar">
		        <input type="text" class="form-control text-center" placeholder="Tanggal" data-required="1" />
		        <span class="input-group-addon">
		            <span class="glyphicon glyphicon-calendar"></span>
		        </span>
		    </div>
		</div>
	</div>
	<div class="col-lg-12"></div>
	<div class="col-lg-12 no-padding">
		<div class="col-lg-2 no-padding"><label class="control-label text-left">Unit</label></div>
		<div class="col-lg-1 no-padding" style="max-width: 2%;"><label class="control-label">:</label></div>
		<div class="col-lg-3" style="padding: 0px 30px 0px 0px;">
			<select class="unit" name="unit[]" multiple="multiple" width="100%" data-required="1">
				<option value="all" > All </option>
				<?php foreach ($unit as $key => $v_unit): ?>
					<option value="<?php echo $v_unit['kode']; ?>" > <?php echo strtoupper($v_unit['nama']); ?> </option>
				<?php endforeach ?>
			</select>
		</div>
	</div>
	<div class="col-lg-12"></div>
	<div class="col-lg-12 no-padding">
		<div class="col-lg-2 no-padding"><label class="control-label text-left">Perusahaan</label></div>
		<div class="col-lg-1 no-padding" style="max-width: 2%;"><label class="control-label">:</label></div>
		<div class="col-lg-3" style="padding: 0px 30px 0px 0px;">
			<select class="form-control selectpicker perusahaan" data-live-search="true" type="text" data-required="1">
				<option value="">Pilih Perusahaan</option>
				<?php if ( count($perusahaan) > 0 ): ?>
					<?php foreach ($perusahaan as $k_perusahaan => $v_perusahaan): ?>
						<option value="<?php echo $v_perusahaan['kode']; ?>" data-jenismitra="<?php echo $v_perusahaan['jenis_mitra']; ?>"><?php echo strtoupper($v_perusahaan['nama']); ?></option>
					<?php endforeach ?>
				<?php endif ?>
			</select>
		</div>
	</div>
	<div class="col-lg-12"></div>
	<div class="col-lg-12 no-padding">
		<div class="col-lg-2 no-padding"><label class="control-label text-left">Pelanggan</label></div>
		<div class="col-lg-1 no-padding" style="max-width: 2%;"><label class="control-label">:</label></div>
		<div class="col-lg-3" style="padding: 0px 30px 0px 0px;">
			<select class="form-control selectpicker pelanggan" data-live-search="true" type="text" data-required="1">
				<option value="">Pilih Pelanggan</option>
				<?php if ( count($pelanggan) > 0 ): ?>
					<?php foreach ($pelanggan as $k_plg => $v_plg): ?>
						<option value="<?php echo $v_plg['nomor']; ?>"><?php echo strtoupper($v_plg['nama']).' ('.strtoupper($v_plg['kab_kota']).')'; ?></option>
					<?php endforeach ?>
				<?php endif ?>
			</select>
		</div>
		<div class="col-lg-6 no-padding">
			<button type="button" class="btn btn-primary" onclick="bakul.get_list_do()"><i class="fa fa-search"></i> Tampilkan</button>
		</div>
	</div>
	<div class="col-lg-12 hide"></div>
	<div class="col-lg-12 no-padding hide">
		<div class="col-lg-2 no-padding"><label class="control-label text-left">Jumlah Transfer</label></div>
		<div class="col-lg-1 no-padding" style="max-width: 2%;"><label class="control-label">:</label></div>
		<div class="col-lg-2 no-padding">
			<input type="text" class="form-control text-right jml_transfer" data-tipe="integer" placeholder="Jumlah" onblur="bakul.hit_total_uang()" data-required="1">
		</div>
		<div class="col-lg-1" style="padding: 0px 30px 0px 0px;">&nbsp;</div>
		<div class="col-lg-2 no-padding"><label class="control-label text-left">Bukti Transfer</label></div>
		<div class="col-lg-1 no-padding" style="max-width: 2%;"><label class="control-label">:</label></div>
		<div class="col-lg-3 no-padding">
			<div class="col-lg-12" style="padding: 7px 0px 0px 0px;">
				<label class="">
					<input type="file" onchange="showNameFile(this)" class="file_lampiran" name="" placeholder="Bukti Transfer" data-allowtypes="doc|pdf|docx|jpg|jpeg|png|DOC|PDF|DOCX|JPG|JPEG|PNG" style="display: none;">
					<i class="glyphicon glyphicon-paperclip cursor-p"></i>
				</label>
			</div>
		</div>
	</div>
	<div class="col-lg-12 no-padding hide">&nbsp;</div>
	<div class="col-lg-12 no-padding hide">
		<div class="col-lg-2 no-padding"><label class="control-label text-left">Saldo</label></div>
		<div class="col-lg-1 no-padding" style="max-width: 2%;"><label class="control-label">:</label></div>
		<div class="col-lg-2 no-padding">
			<input type="text" class="form-control text-right saldo" data-tipe="decimal" placeholder="Saldo" data-required="1" readonly>
		</div>
		<div class="col-lg-1" style="padding: 0px 30px 0px 0px;">&nbsp;</div>
		<div class="col-lg-2 no-padding"><label class="control-label text-left">Total Uang</label></div>
		<div class="col-lg-1 no-padding" style="max-width: 2%;"><label class="control-label">:</label></div>
		<div class="col-lg-2 no-padding">
			<input type="text" class="form-control text-right total" data-tipe="decimal" placeholder="Total" data-required="1" readonly>
		</div>
	</div>
	<div class="col-lg-12 hide"></div>
	<div class="col-lg-12 no-padding hide">
		<div class="col-lg-2 no-padding"><label class="control-label text-left">Total Penyesuaian</label></div>
		<div class="col-lg-1 no-padding" style="max-width: 2%;"><label class="control-label">:</label></div>
		<div class="col-lg-2 no-padding">
			<input type="text" class="form-control text-right total_penyesuaian" data-tipe="decimal" placeholder="Jumlah" data-required="1" readonly>
		</div>
	</div>
	<div class="col-lg-12 hide"></div>
	<div class="col-lg-12 no-padding hide">
		<div class="col-lg-2 no-padding"><label class="control-label text-left">Nilai Pajak</label></div>
		<div class="col-lg-1 no-padding" style="max-width: 2%;"><label class="control-label">:</label></div>
		<div class="col-lg-2 no-padding">
			<input type="text" class="form-control text-right nilai_pajak" placeholder="Nilai" data-tipe="decimal" onblur="bakul.hit_total_uang()" />
		</div>
	</div>
	<div class="col-lg-12"></div>
	<div class="col-lg-12 no-padding">
		<div class="col-lg-2 no-padding"><label class="control-label text-left">Jumlah Tagihan</label></div>
		<div class="col-lg-1 no-padding" style="max-width: 2%;"><label class="control-label">:</label></div>
		<div class="col-lg-2 no-padding">
			<input type="text" class="form-control text-right jml_bayar" data-tipe="decimal" placeholder="Jumlah" data-required="1" readonly>
		</div>
		<div class="col-lg-1 hide" style="padding: 0px 30px 0px 0px;">&nbsp;</div>
		<div class="col-lg-2 hide no-padding"><label class="control-label text-left">Lebih / Kurang</label></div>
		<div class="col-lg-1 hide no-padding" style="max-width: 2%;"><label class="control-label">:</label></div>
		<div class="col-lg-2 hide no-padding">
			<input type="text" class="form-control text-right lebih_kurang" data-tipe="decimal" placeholder="Jumlah" data-required="1" disabled="disabled">
		</div>
	</div>
	<div class="col-lg-12 no-padding"><hr></div>
	<div class="col-lg-12 no-padding">
		<small>
			<table class="table table-bordered tbl_list_do" style="margin-bottom: 0px;">
				<thead>
					<tr>
						<th class="col-lg-1 text-center">Tanggal Panen</th>
						<th class="col-lg-1 text-center">Plasma</th>
						<th class="col-lg-1 text-center">No. DO</th>
						<th class="col-lg-1 text-center">No. SJ</th>
						<th class="text-center" style="width: 5%;">Ekor</th>
						<th class="text-center" style="width: 5%;">Kg</th>
						<th class="text-center" style="width: 7%;">Harga</th>
						<th class="col-lg-1 text-center">Total</th>
						<th class="col-lg-1 text-center">Sudah Bayar</th>
						<th class="col-lg-1 text-center">Jumlah Bayar</th>
						<th class="col-lg-1 text-center">Penyesuaian</th>
						<th class="text-center" style="width: 5%;">Status</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td colspan="12">Data tidak ditemukan.</td>
					</tr>
				</tbody>
			</table>
		</small>
	</div>
	<div class="col-lg-12 no-padding"><hr></div>
	<div class="col-lg-12 no-padding">
		<button type="button" class="btn btn-primary pull-right" onclick="bakul.save()"><i class="fa fa-save"></i> Simpan</button>
	</div>
<?php else: ?>
	<h3>Detail Pembayaran</h3>
<?php endif ?>