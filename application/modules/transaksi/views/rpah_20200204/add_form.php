<div class="panel-body">
    <div class="row new-line">
        <div class="col-sm-12">
            <form class="form-horizontal" role="form">
                <div class="form-group">
                	<div class="col-md-2 no-padding">
                		<label class="control-label">Unit</label>
                	</div>
                	<div class="col-md-3 no-padding">
                		<select class="form-control unit">
                			<option value="">Pilih Unit</option>
                			<?php if ( !empty($unit) ): ?>
                				<?php foreach ($unit as $k_unit => $v_unit): ?>
                					<option value="<?php echo $v_unit['id']; ?>"><?php echo $v_unit['nama']; ?></option>
                				<?php endforeach ?>
                			<?php endif ?>
                		</select>
                	</div>
                	<div class="col-md-7 no-padding text-right">
                		<label class="control-label">Tanggal Panen : <?php echo tglIndonesia( next_date(date('Y-m-d')), '-', ' ', TRUE); ?></label>
                	</div>
                </div>
                <div class="form-group">
                	<div class="col-md-2 no-padding">
                		<label class="control-label">Bottom Price</label>
                	</div>
                	<div class="col-md-2 no-padding">
                		<input type="text" class="form-control text-right bottom_price" data-tipe="integer" />
                	</div>
                </div>
                <div class="form-group">
                	<small>
	                	<table class="table table-bordered header">
	                		<thead>
	                			<tr>
	                				<th class="col-md-4">Nama Peternak</th>
	                				<th class="col-md-2">Noreg</th>
	                				<th class="col-md-1">Kandang</th>
	                				<th class="col-md-1">Tonase</th>
	                				<th class="col-md-1">Ekor</th>
	                			</tr>
	                		</thead>
	                		<tbody>
	                			<tr class="head">
	                				<td></td>
	                				<td></td>
	                				<td></td>
	                				<td></td>
	                				<td></td>
	                			</tr>
	                			<tr class="detail">
	                				<td colspan="5" style="padding-right: 40px;">
		                				<table class="table table-bordered detail">
		                					<thead>
		                						<tr>
		                							<th class="col-md-4">Nama Pelanggan</th>
		                							<th class="col-md-2">Outstanding</th>
		                							<th class="col-md-1">Tonase</th>
		                							<th class="col-md-1">Ekor</th>
		                							<th class="col-md-1">BB</th>
		                							<th class="col-md-1">Harga</th>
	                							</tr>
		                					</thead>
		                					<tbody>
		                						<tr>
		                							<td>
		                								<input type="text" class="form-control pelanggan">
		                							</td>
		                							<td>
		                								<input type="text" class="form-control outstanding">
		                							</td>
		                							<td>
		                								<input type="text" class="form-control text-right tonase" data-tipe="decimal">
		                							</td>
		                							<td>
		                								<input type="text" class="form-control text-right ekor" data-tipe="integer">
		                							</td>
		                							<td>
		                								<input type="text" class="form-control text-right bb" data-tipe="decimal" readonly>
		                							</td>
		                							<td>
		                								<input type="text" class="form-control text-right harga" data-tipe="integer">
		                								<div class="btn-ctrl">
															<span onclick="rpah.removeRow(this)" class="btn_del_row_2x"></span>
															<span onclick="rpah.addRow(this)" class="btn_add_row_2x"></span>
														</div>
		                							</td>
		                						</tr>
		                					</tbody>
		                				</table>
		                			</td>
	                			</tr>
	                		</tbody>
	                	</table>
	                </small>
                </div>
            </form>
        </div>
        <div class="col-md-12 no-padding">
    		<button type="button" class="btn btn-primary pull-right"><i class="fa fa-save"></i> Simpan</button>
    	</div>
    </div>
</div>