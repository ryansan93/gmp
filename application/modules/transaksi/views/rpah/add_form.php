<div class="panel-body" style="padding-top: 0px;">
    <div class="row new-line">
        <div class="col-sm-12">
            <form class="form-horizontal" role="form">
                <div class="form-group">
                    <div class="col-md-2 no-padding">
                        <label class="control-label">Tgl Panen</label>
                    </div>
                    <div class="col-md-2 no-padding">
                        <div class="input-group date" id="tgl_panen" name="tgl_panen">
                            <input type="text" class="form-control text-center" placeholder="Tanggal" data-required="1" />
                            <span class="input-group-addon">
                                <span class="glyphicon glyphicon-calendar"></span>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                	<div class="col-md-2 no-padding">
                		<label class="control-label">Unit</label>
                	</div>
                	<div class="col-md-3 no-padding">
                		<select class="form-control unit" data-required="1" onchange="rpah.get_data(this)">
                			<option value="">Pilih Unit</option>
                			<?php if ( !empty($unit) ): ?>
                				<?php foreach ($unit as $k_unit => $v_unit): ?>
                					<option value="<?php echo $v_unit['id']; ?>"><?php echo strtoupper($v_unit['nama']); ?></option>
                				<?php endforeach ?>
                			<?php endif ?>
                		</select>
                	</div>
                	<!-- <div class="col-md-7 no-padding text-right"> -->
                        <!-- <label class="control-label tgl_panen" data-tgl="<?php echo next_date(date('Y-m-d')); ?>">Tanggal Panen : <?php echo tglIndonesia( next_date(date('Y-m-d')), '-', ' ', TRUE); ?></label> -->
                        <!-- <label class="control-label tgl_panen" data-tgl="<?php echo '2021-04-08'; ?>">Tanggal Panen : <?php echo tglIndonesia('2021-04-08', '-', ' ', TRUE); ?></label> -->
                		<!-- <label class="control-label tgl_panen" data-tgl="<?php echo (date('Y-m-d')); ?>">Tanggal Panen : <?php echo tglIndonesia( (date('Y-m-d')), '-', ' ', TRUE); ?></label> -->
                	<!-- </div> -->
                </div>
                <div class="form-group">
                	<div class="col-md-2 no-padding">
                		<label class="control-label">Bottom Price</label>
                	</div>
                	<div class="col-md-2 no-padding">
                		<input type="text" class="form-control text-right bottom_price" placeholder="BOTTOM PRICE" data-tipe="integer" data-required="1" />
                	</div>
                </div>
                <div class="form-group">
                	<small>
	                	<table class="table table-bordered tbl_data_konfir header">
	                		<thead>
	                			<tr>
                                    <th class="col-md-1" style="max-width: 3%;">Pilih</th>
	                				<th class="col-md-4">Nama Peternak</th>
	                				<th class="col-md-2">Noreg</th>
	                				<th class="col-md-1">Kandang</th>
	                				<th class="col-md-1">Ekor</th>
                                    <th class="col-md-1">Tonase</th>
	                			</tr>
	                		</thead>
	                		<tbody>
	                			<tr>
	                				<td colspan="6">Data tidak ditemukan.</td>
	                			</tr>
	                		</tbody>
	                	</table>
	                </small>
                </div>
            </form>
        </div>
        <div class="col-md-12 no-padding">
    		<button type="button" class="btn btn-primary pull-right" onclick="rpah.save();"><i class="fa fa-save"></i> Simpan</button>
    	</div>
    </div>
</div>