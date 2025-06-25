<div class="row">
	<div class="col-xs-12">
		<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
			<div class="col-xs-6 no-padding" style="padding-right: 5px;">
				<div class="col-xs-12 no-padding"><label class="control-label">Unit</label></div>
				<div class="col-xs-12 no-padding">
					<select class="form-control unit" data-required="1">
						<option value="all">ALL</option>
						<option value="pusat">PUSAT</option>
						<option value="pusat_gml">PUSAT GEMILANG</option>
						<option value="pusat_ma">PUSAT MA</option>
						<?php foreach ($unit as $k_unit => $v_unit): ?>
							<?php
								$selected = null;
								if ( !empty($kode_unit) ) {
									if ( $kode_unit == $v_unit['kode'] ) {
										$selected = 'selected';
									}
								}
							?>
							<option value="<?php echo $v_unit['kode']; ?>" <?php echo $selected; ?> ><?php echo $v_unit['nama']; ?></option>
						<?php endforeach ?>
					</select>
				</div>
			</div>
			<div class="col-xs-6 no-padding" style="padding-left: 5px;">
				<div class="col-xs-12 no-padding"><label class="control-label">Perusahaan</label></div>
				<div class="col-xs-12 no-padding">
					<select class="form-control perusahaan" data-required="1">
						<?php foreach ($perusahaan as $k_perusahaan => $v_perusahaan): ?>
							<?php
								$selected = null;
								if ( !empty($kode_perusahaan) ) {
									if ( $kode_perusahaan == $v_perusahaan['kode'] ) {
										$selected = 'selected';
									}
								}
							?>
							<option value="<?php echo $v_perusahaan['kode']; ?>" <?php echo $selected; ?> ><?php echo $v_perusahaan['nama_perusahaan']; ?></option>
						<?php endforeach ?>
					</select>
				</div>
			</div>
		</div>
		<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
			<div class="col-xs-12 no-padding"><label class="control-label">Periode</label></div>
			<div class="col-xs-12 no-padding">
				<div class="input-group date datetimepicker" id="periode">
			        <input type="text" class="form-control text-center" placeholder="PERIODE" data-required="1" data-tgl="<?php echo $periode; ?>" />
			        <span class="input-group-addon">
			            <span class="glyphicon glyphicon-calendar"></span>
			        </span>
			    </div>
			</div>
		</div>
		<div class="col-xs-12 no-padding">
			<div class="col-xs-12 no-padding">
				<button type="button" class="col-xs-12 btn btn-primary" onclick="kk.getLists()"><i class="fa fa-search"></i> Tampilkan</button>
			</div>
		</div>
		<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
		<div class="col-xs-12 no-padding">
			<small>
				<table class="table table-bordered" style="margin-bottom: 0px;">
					<thead>
						<tr>
							<th class="col-xs-1">Tanggal</th>
							<th class="col-xs-2">Akun Transaksi</th>
							<th class="col-xs-1">PIC</th>
							<th class="col-xs-3">Keterangan</th>
							<th class="col-xs-1">Masuk</th>
							<th class="col-xs-1">Keluar</th>
							<th class="col-xs-1">Saldo</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td colspan="7">Data tidak ditemukan.</td>
						</tr>
					</tbody>
				</table>
			</small>
		</div>
		<div class="col-xs-12 no-padding btn-tutup-bulan hide" data-status="0">
			<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
			<div class="col-xs-12 no-padding">
				<?php if ( $akses['a_submit'] == 1 ) { ?>
					<button type="button" class="col-xs-12 btn btn-success submit" data-status="0" onclick="kk.save()"><i class="fa fa-check"></i> Tutup Bulan</button>
				<?php } ?>
				<?php if ( $akses['a_ack'] == 1 ) { ?>
					<button type="button" class="col-xs-12 btn btn-primary ack" data-status="0" onclick="kk.ack()"><i class="fa fa-check"></i> ACK</button>
				<?php } ?>
			</div>
		</div>
	</div>
</div>