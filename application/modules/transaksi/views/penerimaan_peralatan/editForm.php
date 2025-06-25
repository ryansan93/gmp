<div class="col-xs-12 no-padding">
	<div class="col-xs-12 no-padding"><label class="control-label text-left">Tanggal Terima</label></div>
	<div class="input-group date" name="tglTerima" id="TglTerima">
        <input type="text" class="form-control text-center" data-required="1" placeholder="Tanggal Terima" data-tgl="<?php echo $data['tgl_terima']; ?>" />
        <span class="input-group-addon">
            <span class="glyphicon glyphicon-calendar"></span>
        </span>
    </div>
</div>
<div class="col-xs-12 no-padding">
	<div class="col-xs-6 no-padding" style="padding-right: 5px;">
		<div class="col-xs-12 no-padding"><label class="control-label text-left">Supplier</label></div>
		<div class="col-xs-12 no-padding">
			<select class="form-control supplier" data-required="1">
				<option value="">-- Pilih Supplier --</option>
				<?php if ( isset($supplier) && !empty($supplier) ): ?>
					<?php foreach ($supplier as $k => $val): ?>
						<?php
							$selected = null;
							if ( $val['nomor'] == $data['supplier'] ) {
								$selected = 'selected';
							}
						?>
						<option value="<?php echo $val['nomor']; ?>" <?php echo $selected; ?> ><?php echo strtoupper($val['nama']); ?></option>
					<?php endforeach ?>
				<?php endif ?>
			</select>
		</div>
	</div>
	<div class="col-xs-6 no-padding" style="padding-left: 5px;">
		<div class="col-xs-12 no-padding"><label class="control-label text-left">Plasma</label></div>
	    <div class="col-xs-12 no-padding">
	    	<input type="text" class="form-control text-left mitra" data-required="1" placeholder="Plasma" value="<?php echo $data['nama_mitra']; ?>" readonly />
	    </div>
	</div>
</div>
<div class="col-xs-12 no-padding">
	<div class="col-xs-6 no-padding" style="padding-right: 5px;">
		<div class="col-xs-12 no-padding"><label class="control-label text-left">No. Order</label></div>
		<div class="col-xs-12 no-padding">
			<select class="form-control no_order" data-required="1" data-val="<?php echo $data['no_order']; ?>">
				<option value="">-- Pilih No. Order --</option>
			</select>
		</div>
	</div>
	<div class="col-xs-6 no-padding" style="padding-left: 5px;">
		<div class="col-xs-12 no-padding"><label class="control-label text-left">No. SJ</label></div>
		<div class="col-xs-10 no-padding">
			<input type="text" class="form-control text-right no_sj" data-required="1" placeholder="No. SJ" value="<?php echo $data['no_sj']; ?>" />
		</div>
		<div class="col-xs-2 no-padding" style="padding-left: 10px;">
			<div class="col-xs-12 text-right" style="padding: 7px 0px 0px 0px;">
				<a name="dokumen" class="" href="uploads/<?php echo $data['lampiran']; ?>" target="_blank" style="padding-right: 10px;"><i class="fa fa-file"></i></a>
				<label class="">
					<input type="file" onchange="pp.showNameFile(this)" class="file_lampiran" name="" placeholder="Bukti Transfer" data-allowtypes="pdf|jpg|jpeg|png" style="display: none;">
					<i class="glyphicon glyphicon-paperclip cursor-p"></i>
				</label>
			</div>
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
<div class="col-xs-12 no-padding">
	<small>
		<table class="table table-bordered detail" style="margin-bottom: 0px;">
			<thead>
				<tr>
					<th class="col-xs-4">Barang</th>
					<th class="col-xs-2">Jumlah Kirim</th>
					<th class="col-xs-2">Jumlah Terima</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($data['detail'] as $key => $value): ?>
					<tr>
						<td class="barang" data-kode="<?php echo $value['kode_barang']; ?>"><?php echo $value['nama_barang']; ?></td>
						<td class="text-right jml_kirim"><?php echo angkaDecimal($value['jml_kirim']); ?></td>
						<td class="text-right">
							<input type="text" class="form-control text-right jml_terima" data-required="1" data-tipe="decimal" placeholder="Terima" value="<?php echo angkaDecimal($value['jml_terima']); ?>">
						</td>
					</tr>
				<?php endforeach ?>
			</tbody>
		</table>
	</small>
</div>
<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
<div class="col-xs-12 no-padding">
	<div class="col-xs-6 no-padding" style="padding-right: 5px;">
		<button type="button" class="col-xs-12 btn btn-primary" onclick="pp.edit(this)" data-id="<?php echo $data['id']; ?>"><i class="fa fa-save"></i> Simpan Perubahan</button>
	</div>
	<div class="col-xs-6 no-padding" style="padding-left: 5px;">
		<button type="button" class="col-xs-12 btn btn-danger" onclick="pp.changeTabActive(this)" data-id="<?php echo $data['id']; ?>" data-href="action"><i class="fa fa-times"></i> Batal</button>
	</div>
</div>