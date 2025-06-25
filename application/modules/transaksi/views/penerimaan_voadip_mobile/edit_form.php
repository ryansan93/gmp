<div class="row detailed">
	<div class="col-lg-12 detailed">
		<hr style="padding-top: 0px; margin-top: 0px;">
		<form role="form" class="form-horizontal">
			<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
				<div class="col-xs-12 no-padding">
					<label class="control-label">Nama Mitra</label>
				</div>
				<div class="col-xs-12 no-padding">
					<select id="select_mitra" data-placeholder="Pilih Mitra" class="form-control selectpicker" data-live-search="true" type="text" data-required="1">
						<option value="">Pilih Mitra</option>
						<?php foreach ($data_mitra as $k_dm => $v_dm): ?>
							<?php
								$selected = null;
								if ( $v_dm['nomor'] == $data['nomor'] ) {
									$selected = 'selected';
								}
							?>
							<option data-tokens="<?php echo $v_dm['nama']; ?>" value="<?php echo $v_dm['nomor']; ?>" <?php echo $selected; ?> ><?php echo strtoupper($v_dm['unit'].' | '.$v_dm['nama']); ?></option>
						<?php endforeach ?>
					</select>
				</div>
			</div>

			<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
				<div class="col-xs-12 no-padding">
					<label class="control-label">No. Reg</label>
				</div>
				<div class="col-xs-12 no-padding">
					<select id="select_noreg" data-placeholder="Pilih No. Reg" class="form-control selectpicker" data-live-search="true" type="text" data-required="1" data-val="<?php echo $data['noreg']; ?>" data-old="<?php echo $data['noreg']; ?>" disabled>
						<option value="">Pilih Noreg</option>
					</select>
				</div>
			</div>

			<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
				<div class="col-xs-12 no-padding">
					<label class="control-label">No. SJ</label>
				</div>
				<div class="col-xs-12 no-padding">
					<select id="select_no_sj" data-placeholder="Pilih No. SJ" class="form-control selectpicker" data-live-search="true" type="text" data-required="1" data-val="<?php echo $data['no_sj']; ?>"  data-tglkirim="<?php echo $data['tgl_kirim']; ?>" data-old="<?php echo $data['no_sj']; ?>" disabled>
						<option value="">Pilih No. SJ</option>
					</select>
				</div>
			</div>

			<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
				<div class="col-xs-12 no-padding">
					<label class="control-label">Asal</label>
				</div>
				<div class="col-xs-12 no-padding">
					<input type="text" class="form-control asal uppercase" placeholder="Asal" data-required="1" value="<?php echo strtoupper($data['asal']); ?>" disabled>
				</div>
			</div>

			<div class="col-xs-6" style="padding: 0px 5px 0px 0px; margin-bottom: 5px;">
				<div class="col-xs-12 no-padding">
					<label class="control-label">No. Polisi</label>
				</div>
				<div class="col-xs-12 no-padding">
					<input type="text" class="form-control nopol uppercase" placeholder="No. Polisi" data-required="1" value="<?php echo strtoupper($data['nopol']); ?>" disabled>
				</div>
			</div>

			<div class="col-xs-6" style="padding: 0px 0px 0px 5px; margin-bottom: 5px;">
				<div class="col-xs-12 no-padding">
					<label class="control-label">Sopir</label>
				</div>
				<div class="col-xs-12 no-padding">
					<input type="text" class="form-control sopir uppercase" placeholder="Sopir" data-required="1" value="<?php echo strtoupper($data['sopir']); ?>" disabled>
				</div>
			</div>

			<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
				<div class="col-xs-12 no-padding">
					<label class="control-label">Ekspedisi</label>
				</div>
				<div class="col-xs-12 no-padding">
					<input type="text" class="form-control ekspedisi uppercase" placeholder="Ekspedisi" data-required="1" value="<?php echo strtoupper($data['ekspedisi']); ?>" disabled>
				</div>
			</div>

			<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
				<div class="col-xs-12 no-padding">
					<label class="control-label">Tanggal Tiba</label>
				</div>
				<div class="col-xs-12 no-padding">
					<div class="input-group date datetimepicker" name="tanggal_tiba" id="tanggal_tiba">
		                <input type="text" class="form-control text-center uppercase" placeholder="Tanggal" data-required="1" data-tgl="<?php echo $data['tiba']; ?>" />
		                <span class="input-group-addon">
		                    <span class="glyphicon glyphicon-calendar"></span>
		                </span>
		            </div>
				</div>
			</div>
		</form>
	</div>
	<div class="col-xs-12 detailed"><br></div>
	<div class="col-xs-12 detailed">
		<form role="form" class="form-horizontal">
			<div class="col-xs-12 no-padding">
				<label class="control-label"><u>Keterangan OBAT</u></label>
			</div>
			<div class="col-xs-12 no-padding">
				<table class="table table-bordered data_brg" style="margin-bottom: 0px;">
					<thead>
						<tr>
							<th class="col-xs-4">Nama</th>
							<th class="col-xs-2">Kirim</th>
							<th class="col-xs-3">Terima</th>
							<th class="col-xs-3">Kondisi</th>
						</tr>
					</thead>
					<tbody>
						<?php if ( !empty($data['data_brg']) ): ?>
							<?php foreach ($data['data_brg'] as $k_db => $v_db): ?>
								<tr class="v-center">
									<td class="text-left brg" data-kode="<?php echo $v_db['kode_brg']; ?>"><?php echo strtoupper($v_db['nama_brg']); ?></td>
									<td class="text-right"><?php echo angkaRibuan($v_db['jml_kirim']); ?></td>
									<td>
										<input type="text" class="form-control text-right jumlah_terima" data-tipe="integer" placeholder="Jumlah" data-required="1" value="<?php echo angkaRibuan($v_db['jml_terima']); ?>">
									</td>
									<td>
										<input type="text" class="form-control kondisi uppercase" placeholder="Kondisi" value="<?php echo strtoupper($v_db['kondisi']); ?>">
									</td>
								</tr>
							<?php endforeach ?>
						<?php else: ?>
							<tr>
								<td colspan="4">Data tidak ditemukan.</td>
							</tr>
						<?php endif ?>
					</tbody>
				</table>
			</div>
		</form>
	</div>
	<div class="col-lg-12 detailed"><hr></div>
	<div class="col-lg-12 detailed">
		<form role="form" class="form-horizontal">
			<div class="col-xs-6 no-padding" style="padding-right: 5px;">
				<button type="button" class="btn btn-primary pull-right col-xs-12" onclick="pvm.edit(this)"><i class="fa fa-save"></i> Simpan Perubahan</button>
			</div>
			<div class="col-xs-6 no-padding" style="padding-left: 5px;">
				<button type="button" class="btn btn-danger pull-right col-xs-12" onclick="pvm.change_tab(this)" data-id="<?php echo $data['no_sj']; ?>" data-noreg="<?php echo $data['noreg']; ?>" data-nomor="<?php echo $data['nomor']; ?>" data-edit="" data-href="transaksi"><i class="fa fa-times"></i> Batal</button>
			</div>
		</form>
	</div>
</div>
