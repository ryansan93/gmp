<?php if ( !empty($data) ): ?>
	<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
		<div class="col-xs-12 no-padding">
			<label class="control-label">Tanggal Terima</label>
		</div>
		<div class="col-xs-12 no-padding">
			<div class="input-group date datetimepicker" name="tglTerima" id="TglTerima">
		        <input type="text" class="form-control text-center" placeholder="Tanggal" data-required="1" />
		        <span class="input-group-addon">
		            <span class="glyphicon glyphicon-calendar"></span>
		        </span>
		    </div>
		</div>
	</div>

	<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
		<div class="col-xs-12 no-padding">
			<label class="control-label">Nama PiC</label>
		</div>
		<div class="col-xs-12 no-padding">
			<input type="text" class="col-xs-12 form-control uppercase" placeholder="Nama PiC" data-required="1" readonly value="<?php echo $data['nama_pic'] ?>">
		</div>
	</div>

	<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
		<div class="col-xs-12 no-padding">
			<label class="control-label">Branch</label>
		</div>
		<div class="col-xs-12 no-padding">
			<input type="text" class="col-xs-12 form-control uppercase" placeholder="Branch" data-required="1" readonly value="<?php echo $data['branch']['nama']; ?>">
		</div>
	</div>

	<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
		<div class="col-xs-12 no-padding">
			<label class="control-label">Supplier</label>
		</div>
		<div class="col-xs-12 no-padding">
			<input type="text" class="col-xs-12 form-control uppercase" placeholder="Supplier" data-required="1" readonly value="<?php echo $data['supplier']['nama']; ?>">
		</div>
	</div>

	<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
		<div class="col-xs-12 no-padding">
			<label class="control-label">Tgl Beli</label>
		</div>
		<div class="col-xs-12 no-padding">
			<input type="text" class="col-xs-12 form-control uppercase" placeholder="Supplier" data-required="1" readonly value="<?php echo tglIndonesia($data['tgl_beli'], '-', ' '); ?>">
		</div>
	</div>

	<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
		<div class="col-xs-12 no-padding">
			<label class="control-label">No. Faktur</label>
		</div>
		<div class="col-xs-12 no-padding no_faktur">
			<input type="text" class="col-xs-12 form-control uppercase" placeholder="No. Faktur" data-required="1" readonly value="<?php echo $data['no_faktur']; ?>">
		</div>
	</div>

	<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>

	<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
		<small>
			<table class="table table-bordered tbl_detail" style="margin-bottom: 0px;">
				<thead>
					<tr>
						<th class="col-xs-1">Group</th>
						<th class="col-xs-2">Item</th>
						<th class="col-xs-1">Satuan</th>
						<th class="col-xs-1">Jumlah</th>
						<th class="col-xs-1">Jumlah Terima</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($data['detail'] as $k_det => $v_det): ?>
						<tr class="data" data-item="<?php echo $v_det['item_kode']; ?>" data-harga="<?php echo $v_det['harga']; ?>">
							<td>
								<input type="text" class="form-control group uppercase" placeholder="Group" data-required="1" readonly value="<?php echo $v_det['item']['group']['nama']; ?>">
							</td>
							<td>
								<input type="text" class="form-control item uppercase" placeholder="Item" data-required="1" readonly value="<?php echo $v_det['item']['nama']; ?>">
							</td>
							<td>
								<input type="text" class="form-control text-center satuan uppercase" placeholder="Satuan" data-required="1" readonly value="<?php echo $v_det['item']['satuan']; ?>">
							</td>
							<td>
								<input type="text" class="form-control text-right jumlah uppercase" placeholder="Jumlah" data-tipe="decimal" data-required="1" readonly value="<?php echo angkaDecimal($v_det['jumlah']); ?>">
							</td>
							<td>
								<input type="text" class="form-control text-right jumlah_terima uppercase" placeholder="Jumlah Terima" data-tipe="decimal" data-required="1" maxlength="10">
							</td>
						</tr>
					<?php endforeach ?>
				</tbody>
			</table>
		</small>
	</div>

	<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
		<div class="col-xs-12 no-padding">
			<label class="control-label">Keterangan</label>
		</div>
		<div class="col-xs-12 no-padding">
			<textarea class="form-control keterangan" disabled><?php echo strtoupper($data['keterangan']); ?></textarea>
		</div>
	</div>
<?php else: ?>
	<div class="col-xs-12 no-padding">
		<div class="col-xs-12 no-padding">
			<label class="control-label">Data tidak ditemukan.</label>
		</div>
	</div>
<?php endif ?>