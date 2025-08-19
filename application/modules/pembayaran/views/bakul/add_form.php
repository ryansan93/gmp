<?php if ( $akses['a_submit'] == 1 ): ?>
	<div class="col-lg-12 no-padding">
		<div class="col-lg-2 no-padding"><label class="control-label text-left">Tgl Rek Koran</label></div>
		<div class="col-lg-1 no-padding" style="max-width: 2%;"><label class="control-label">:</label></div>
		<div class="col-lg-2" style="padding: 0px 30px 0px 0px;">
			<div class="input-group date" id="tglBayar">
		        <input type="text" class="form-control text-center" placeholder="Tanggal" data-required="1" data-val="<?php echo !empty($data_umb) ? $data_umb['tanggal'] : ''; ?>" />
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
						<?php
							$selected = '';
							if ( !empty( $data_umb ) ) {
								if ( $v_perusahaan['kode'] == $data_umb['perusahaan'] ) {
									$selected = 'selected';
								}
							}	
						?>
						<option value="<?php echo $v_perusahaan['kode']; ?>" data-jenismitra="<?php echo $v_perusahaan['jenis_mitra']; ?>" <?php echo $selected; ?> ><?php echo strtoupper($v_perusahaan['nama']); ?></option>
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
						<?php
							$selected = '';
							if ( !empty( $data_umb ) ) {
								if ( $v_plg['nomor'] == $data_umb['pelanggan'] ) {
									$selected = 'selected';
								}
							}	
						?>
						<option value="<?php echo $v_plg['nomor']; ?>" <?php echo $selected; ?> ><?php echo strtoupper($v_plg['nama']).' ('.strtoupper($v_plg['kab_kota']).')'; ?></option>
					<?php endforeach ?>
				<?php endif ?>
			</select>
		</div>
		<div class="col-lg-6 no-padding">
			<button type="button" class="btn btn-primary" onclick="bakul.get_list_do()"><i class="fa fa-search"></i> Tampilkan</button>
		</div>
	</div>
	<div class="col-lg-12"></div>
	<div class="col-lg-12 no-padding">
		<div class="col-lg-2 no-padding"><label class="control-label text-left">Urut Ke / Jumlah Transfer</label></div>
		<!-- <div class="col-lg-2 no-padding"><label class="control-label text-left">Kode Pembayaran</label></div> -->
		<div class="col-lg-1 no-padding" style="max-width: 2%;"><label class="control-label">:</label></div>
		<div class="col-lg-1" style="padding: 0px 1px 0px 0px;">
			<!-- <input type="text" class="form-control text-right jml_transfer" data-tipe="integer" placeholder="Urut" onblur="bakul.hit_total_uang()" data-required="1"> -->
			<select class="form-control urut_tf" data-required="1">
				<option value="1">1</option>
				<option value="2">2</option>
				<option value="3">3</option>
				<option value="4">4</option>
				<option value="5">5</option>
				<option value="6">6</option>
				<option value="7">7</option>
				<option value="8">8</option>
				<option value="9">9</option>
				<option value="10">10</option>
				<option value="11">11</option>
				<option value="12">12</option>
				<option value="13">13</option>
				<option value="14">14</option>
				<option value="15">15</option>
			</select>
			<!-- <input type="text" class="form-control text-center kode_umb" style="padding: 0px 1px 0px 0px;" placeholder="Kode" readonly value="<?php echo !empty($data_umb) ? strtoupper($data_umb['kode']) : ''; ?>"> -->
		</div>
		<div class="col-lg-2" style="padding: 0px 30px 0px 0px;">
			<input type="text" class="form-control text-right jml_transfer" data-tipe="integer" placeholder="Jumlah" onblur="bakul.hit_total_uang()" data-required="1">
		</div>
		<!-- <div class="col-lg-1" style="padding: 0px 30px 0px 0px;">&nbsp;</div> -->
		<div class="col-lg-2 no-padding"><label class="control-label text-left">Bukti Transfer</label></div>
		<div class="col-lg-1 no-padding" style="max-width: 2%;"><label class="control-label">:</label></div>
		<div class="col-lg-3 no-padding">
			<div class="col-lg-12" style="padding: 7px 0px 0px 0px;">
				<label class="">
					<input type="file" onchange="showNameFile(this)" class="file_lampiran" data-required="1" name="" placeholder="Bukti Transfer" data-allowtypes="doc|pdf|docx|jpg|jpeg|png|DOC|PDF|DOCX|JPG|JPEG|PNG" style="display: none;">
					<i class="glyphicon glyphicon-paperclip cursor-p"></i>
				</label>
			</div>
		</div>
	</div>
	<div class="col-lg-12 no-padding">&nbsp;</div>
	<div class="col-lg-12 no-padding">
		<div class="col-lg-2 no-padding"><label class="control-label text-left">Saldo</label></div>
		<div class="col-lg-1 no-padding" style="max-width: 2%;"><label class="control-label">:</label></div>
		<div class="col-lg-2 no-padding">
			<input type="text" class="form-control text-right saldo" data-tipe="decimal" placeholder="Saldo" data-required="1" readonly>
		</div>
	</div>
	<div class="col-lg-12"></div>
	<div class="col-lg-12 no-padding">
		<div class="col-lg-2 no-padding"><label class="control-label text-left">Nilai Pajak</label></div>
		<div class="col-lg-1 no-padding" style="max-width: 2%;"><label class="control-label">:</label></div>
		<div class="col-lg-2 no-padding">
			<input type="text" class="form-control text-right nilai_pajak" placeholder="Nilai" data-tipe="decimal" onblur="bakul.hit_total_uang()" />
		</div>
	</div>
	<div class="col-lg-12"></div>
	<div class="col-lg-12 no-padding">
		<div class="col-lg-2 no-padding"><label class="control-label text-left">Lebih Bayar Non Saldo</label></div>
		<div class="col-lg-1 no-padding" style="max-width: 2%;"><label class="control-label">:</label></div>
		<div class="col-lg-2 no-padding">
			<input type="text" class="form-control text-right lebih_bayar_non_saldo" placeholder="Nilai" data-tipe="decimal" onblur="bakul.hit_total_uang()" />
		</div>
		<div class="col-lg-1" style="padding: 0px 30px 0px 0px;">&nbsp;</div>
		<div class="col-lg-2 no-padding"><label class="control-label text-left">Total Uang</label></div>
		<div class="col-lg-1 no-padding" style="max-width: 2%;"><label class="control-label">:</label></div>
		<div class="col-lg-2 no-padding">
			<input type="text" class="form-control text-right total_uang" data-tipe="decimal" placeholder="Total" data-required="1" readonly>
		</div>
	</div>
	<div class="col-lg-12"></div>
	<div class="col-lg-12 no-padding">
		<div class="col-lg-2 no-padding"><label class="control-label text-left">Total Penyesuaian</label></div>
		<div class="col-lg-1 no-padding" style="max-width: 2%;"><label class="control-label">:</label></div>
		<div class="col-lg-2 no-padding">
			<input type="text" class="form-control text-right total_penyesuaian" data-tipe="decimal" placeholder="Jumlah" data-required="1" readonly>
		</div>
	</div>
	<div class="col-lg-12"></div>
	<div class="col-lg-12 no-padding" style="margin-bottom: 1px;">
		<div class="col-lg-2 no-padding"><label class="control-label text-left">CN</label></div>
		<div class="col-lg-1 no-padding" style="max-width: 2%;"><label class="control-label">:</label></div>
		<div class="col-lg-2 no-padding">
			<input type="text" class="form-control text-right tot_cn" data-tipe="decimal" placeholder="CN" readonly>
		</div>
		<div class="col-lg-3" style="padding: 0px 0px 0px 10px;">
			<button type="button" class="btn btn-default" onclick="bakul.modalPilihCN(this)">Pilih CN</button>
		</div>
	</div>
	<div class="col-lg-12 no-padding" style="margin-bottom: 1px;">
		<div class="col-lg-2 no-padding"><label class="control-label text-left">DN</label></div>
		<div class="col-lg-1 no-padding" style="max-width: 2%;"><label class="control-label">:</label></div>
		<div class="col-lg-2 no-padding">
			<input type="text" class="form-control text-right tot_dn" data-tipe="decimal" placeholder="DN" readonly>
		</div>
		<div class="col-lg-3" style="padding: 0px 0px 0px 10px;">
			<button type="button" class="btn btn-default" onclick="bakul.modalPilihDN(this)">Pilih DN</button>
		</div>
	</div>
	<div class="col-lg-12 no-padding" style="margin-bottom: 1px;">
		<div class="col-lg-2 no-padding"><label class="control-label text-left">Total Nilai</label></div>
		<div class="col-lg-1 no-padding" style="max-width: 2%;"><label class="control-label">:</label></div>
		<div class="col-lg-2 no-padding">
			<input type="text" class="form-control text-right tot_nilai" data-tipe="decimal" placeholder="Jumlah" data-required="1" readonly>
		</div>
		<div class="col-lg-1" style="padding: 0px 30px 0px 0px;">&nbsp;</div>
		<div class="col-lg-2 no-padding"><label class="control-label text-left">Total Tagihan</label></div>
		<div class="col-lg-1 no-padding" style="max-width: 2%;"><label class="control-label">:</label></div>
		<div class="col-lg-2 no-padding">
			<input type="text" class="form-control text-right tot_tagihan" data-tipe="decimal" placeholder="Total" data-required="1" readonly>
		</div>
	</div>
	<div class="col-lg-12 no-padding">
		<div class="col-lg-2 no-padding">&nbsp;</div>
		<div class="col-lg-1 no-padding" style="max-width: 2%;">&nbsp;</div>
		<div class="col-lg-2 no-padding">&nbsp;</div>
		<div class="col-lg-1" style="padding: 0px 30px 0px 0px;">&nbsp;</div>
		<div class="col-lg-2 no-padding"><label class="control-label text-left">Lebih / Kurang</label></div>
		<div class="col-lg-1 no-padding" style="max-width: 2%;"><label class="control-label">:</label></div>
		<div class="col-lg-2 no-padding">
			<input type="text" class="form-control text-right lebih_kurang" data-tipe="decimal" placeholder="Jumlah" data-required="1" disabled="disabled">
		</div>
	</div>
	<div class="col-lg-12 no-padding"><hr></div>
	<div class="col-lg-12 no-padding">
		<small>
			<table class="table table-bordered tbl_list_do" style="margin-bottom: 0px;">
				<thead>
					<tr>
						<th class="text-center" style="width: 6%;">Tgl Invoice</th>
						<th class="col-lg-1 text-center">Plasma</th>
						<th class="text-center" style="width: 7%;">No. SJ</th>
						<th class="text-center" style="width: 7%;">No. Invoice</th>
						<th class="text-center" style="width: 5%;">Ekor</th>
						<th class="text-center" style="width: 5%;">Kg</th>
						<th class="col-lg-1 text-center">CN</th>
						<th class="col-lg-1 text-center">DN</th>
						<th class="col-lg-1 text-center">Nilai</th>
						<th class="col-lg-1 text-center">Tot Tagihan</th>
						<th class="col-lg-1 text-center">Jumlah Bayar</th>
						<th class="col-lg-1 text-center">Penyesuaian</th>
						<th class="col-lg-1 text-center">Sisa Tagihan</th>
						<th class="text-center" style="width: 5%;">Status</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td colspan="14">Data tidak ditemukan.</td>
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