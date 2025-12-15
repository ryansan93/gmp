<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
	<div class="col-xs-12 no-padding"><label class="control-label">Tanggal Saldo Bank</label></div>
	<div class="col-xs-12 no-padding">
		<div class="input-group date datetimepicker lock_date_fiskal" name="tanggal" id="Tanggal">
			<input type="text" class="form-control text-center" placeholder="Tanggal" data-required="1" data-tglold="<?php echo $data[0]['tanggal']; ?>" data-tgl="<?php echo $data[0]['tanggal']; ?>" />
			<span class="input-group-addon">
				<span class="glyphicon glyphicon-calendar"></span>
			</span>
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
	<div class="col-xs-12 no-padding"><label class="control-label">Total</label></div>
	<div class="col-xs-12 no-padding nilai">
		<input type="text" class="col-xs-12 form-control text-right uppercase" placeholder="Total" value="<?php echo angkaDecimal($data[0]['total']) ?>" disabled>
	</div>
</div>

<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>

<div class="col-xs-12 no-padding">
	<div class="col-xs-12 no-padding" style="overflow-x: auto;">
		<small>
			<table class="table table-bordered tbl_detail" style="margin-bottom: 0px; max-width: 100%; width: 100%;">
				<thead>
					<tr>
						<th class="col-xs-2">COA</th>
						<th class="col-xs-3">Nama COA</th>
						<th class="col-xs-2">Saldo</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($data as $key => $value) { ?>
						<tr>
							<td class="no_coa"><?php echo $value['coa']; ?></td>
							<td><?php echo $value['nama_coa']; ?></td>
							<td>
								<input type="text" class="form-control text-right saldo uppercase" placeholder="Saldo" data-tipe="decimal" maxlength="19" data-required="1" value="<?php echo angkaDecimal($value['saldo_akhir']) ?>" onblur="sb.hitGrandTotal(this)">
							</td>
						</tr>
					<?php } ?>
				</tbody>
			</table>
		</small>
	</div>
</div>

<div class="col-xs-12 no-padding"><hr></div>

<div class="col-xs-12 no-padding">
    <button type="button" class="btn btn-primary pull-right" onclick="sb.edit(this)" data-tanggal="<?php echo $data[0]['tanggal']; ?>" style="margin-left: 5px;">
        <i class="fa fa-save"></i>
        Update
    </button>
    <button type="button" class="btn btn-danger pull-right" onclick="sb.changeTabActive(this)" data-href="action" data-edit="" data-tanggal="<?php echo $data[0]['tanggal']; ?>" style="margin-right: 5px;">
        <i class="fa fa-times"></i>
        Batal
    </button>
</div>