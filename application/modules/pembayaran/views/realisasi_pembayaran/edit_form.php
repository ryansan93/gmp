<form class="form-horizontal">
	<div class="col-xs-12 no-padding">
		<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
			<div class="col-xs-12 no-padding"><label class="control-label text-left">Tanggal Nota</label></div>
			<div class="col-xs-5 no-padding">
				<div class="input-group date" id="start_date_bayar">
			        <input type="text" class="form-control text-center" data-required="1" placeholder="Start Date" data-tgl="<?php echo $data['start_date']; ?>" />
			        <span class="input-group-addon">
			            <span class="glyphicon glyphicon-calendar"></span>
			        </span>
			    </div>
			</div>
			<div class="col-xs-2 no-padding text-center"><label class="control-label text-left">s/d</label></div>
			<div class="col-xs-5 no-padding">
				<div class="input-group date" id="end_date_bayar">
			        <input type="text" class="form-control text-center" data-required="1" placeholder="End Date" data-tgl="<?php echo $data['end_date']; ?>" />
			        <span class="input-group-addon">
			            <span class="glyphicon glyphicon-calendar"></span>
			        </span>
			    </div>
			</div>
		</div>
	</div>
	<div class="col-xs-12 no-padding" style="margin-bottom: 5px; padding: 0px 5px 0px 0px;">
		<div class="col-xs-12 no-padding"><label class="control-label text-left">Jenis Pembayaran</label></div>
		<div class="col-xs-12 no-padding">
			<select class="jenis_pembayaran" width="100%" data-required="1">
				<option data-tokens="plasma" value="plasma" <?php echo (stristr($data['jenis_pembayaran'], 'plasma') !== false) ? 'selected' : null; ?> >PLASMA</option>
				<option data-tokens="supplier" value="supplier" <?php echo (stristr($data['jenis_pembayaran'], 'supplier') !== false) ? 'selected' : null; ?> >SUPPLIER</option>
			</select>
		</div>
	</div>
	<div class="col-xs-12 search left-inner-addon no-padding"><hr style="margin-top: 5px; margin-bottom: 5px;"></div>
	<?php
		$doc = null;
		$voadip = null;
		$pakan = null;
		$plasma = null;
		foreach ($data['jenis_transaksi'] as $k => $v) {
			if ( stristr($v, 'doc') !== false ) {
				$doc = 'selected';
			}
			if ( stristr($v, 'voadip') !== false ) {
				$voadip = 'selected';
			}
			if ( stristr($v, 'pakan') !== false ) {
				$pakan = 'selected';
			}
			if ( stristr($v, 'plasma') !== false ) {
				$plasma = 'selected';
			}
		}
	?>
	<div class="col-xs-12 no-padding jenis supplier">
		<div class="col-xs-12 no-padding" style="margin-bottom: 5px; padding: 0px 5px 0px 0px;">
			<div class="col-xs-12 no-padding"><label class="control-label text-left">Jenis Transaksi</label></div>
			<div class="col-xs-12 no-padding">
				<select class="jenis_transaksi" multiple="multiple" width="100%" data-required="1">
					<!-- <option value="all">All</option> -->
					<option data-tokens="doc" value="doc" <?php echo $doc; ?> >DOC</option>
					<option data-tokens="ovk" value="voadip" <?php echo $voadip; ?> >OVK</option>
					<option data-tokens="pakan" value="pakan" <?php echo $pakan; ?> >PAKAN</option>
				</select>
			</div>
		</div>
		<div class="col-xs-12 no-padding" style="margin-bottom: 5px; padding: 0px 5px 0px 0px;">
			<div class="col-xs-12 no-padding"><label class="control-label text-left">Supplier</label></div>
			<div class="col-xs-12 no-padding">
				<select class="supplier" width="100%" data-required="1">
					<?php foreach ($supplier as $k => $val): ?>
						<?php
							$selected = null;
							if ( $val['nomor'] == $data['supplier'] ) {
								$selected = 'selected';
							}
						?>
						<option data-tokens="<?php echo $val['nama']; ?>" value="<?php echo $val['nomor']; ?>" <?php echo $selected; ?> ><?php echo strtoupper($val['nama']); ?></option>
					<?php endforeach ?>
				</select>
			</div>
		</div>
	</div>
	<div class="col-xs-12 no-padding jenis plasma">
		<div class="col-xs-12 no-padding" style="margin-bottom: 5px; padding: 0px 0px 0px 0px;">
			<div class="col-xs-12 no-padding"><label class="control-label text-left">Jenis Transaksi</label></div>
			<div class="col-xs-12 no-padding">
				<select class="jenis_transaksi" multiple="multiple" width="100%" data-required="1">
					<!-- <option value="all">All</option> -->
					<option data-tokens="peternak" value="peternak" <?php echo $plasma; ?> >PLASMA</option>
				</select>
			</div>
		</div>
		<div class="col-xs-6 no-padding" style="margin-bottom: 5px; padding: 0px 5px 0px 0px;">
			<div class="col-xs-12 no-padding"><label class="control-label text-left">Unit</label></div>
			<div class="col-xs-12 no-padding">
				<select class="unit" multiple="multiple" width="100%" data-required="1">
					<option value="all">All</option>
					<?php foreach ($unit as $key => $v_unit): ?>
						<option value="<?php echo $v_unit['kode']; ?>" > <?php echo strtoupper($v_unit['nama']); ?> </option>
					<?php endforeach ?>
				</select>
			</div>
		</div>
		<div class="col-xs-6 no-padding" style="margin-bottom: 5px; padding: 0px 0px 0px 5px;">
			<div class="col-xs-12 no-padding"><label class="control-label text-left">Peternak</label></div>
			<div class="col-xs-12 no-padding">
				<select class="mitra" multiple="multiple" width="100%" data-required="1" data-val="<?php echo $data['peternak']; ?>">
				</select>
			</div>
		</div>
	</div>
	<div class="col-xs-12 search left-inner-addon no-padding"><hr style="margin-top: 5px; margin-bottom: 5px;"></div>
	<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
		<div class="col-xs-12 no-padding"><label class="control-label text-left">Perusahaan</label></div>
		<div class="col-xs-12 no-padding">
			<select class="perusahaan_non_multiple" width="100%" data-required="1">
				<?php foreach ($perusahaan as $k => $val): ?>
					<?php
						$selected = null;
						if ( $val['kode'] == $data['perusahaan'] ) {
							$selected = 'selected';
						}
					?>
					<option data-tokens="<?php echo $val['nama']; ?>" value="<?php echo $val['kode']; ?>" <?php echo $selected; ?> ><?php echo strtoupper($val['nama']); ?></option>
				<?php endforeach ?>
			</select>
		</div>
	</div>
	<div class="col-xs-12 no-padding" style="margin-top: 5px; margin-bottom: 5px;">
		<button id="btn-get-lists" type="button" class="btn btn-primary cursor-p col-xs-12" title="ADD" onclick="rp.get_data_rencana_bayar()"> 
			<i class="fa fa-search" aria-hidden="true"></i> Tampilkan Rencana Bayar
		</button>
	</div>
</form>
<div class="col-xs-12 search left-inner-addon no-padding"><hr style="margin-top: 5px; margin-bottom: 5px;"></div>
<div class="col-xs-12 search left-inner-addon" style="padding: 0px 0px 5px 0px;">
	<i class="glyphicon glyphicon-search"></i><input class="form-control" type="search" data-table="tbl_transaksi" placeholder="Search" onkeyup="filter_all(this)">
</div>
<small>
	<table class="table table-bordered tbl_transaksi" style="margin-bottom: 0px;">
		<thead>
			<tr>
				<th class="col-xs-1">Tgl Rcn Bayar</th>
				<th class="col-xs-1">Transaksi</th>
				<th class="col-xs-1">No. Bayar / No. Invoice</th>
				<th class="col-xs-1">Unit</th>
				<th class="col-xs-1">Periode</th>
				<th class="col-xs-3">Nama Penerima</th>
				<th class="col-xs-1">Tagihan</th>
				<th class="col-xs-1">Bayar</th>
				<th class="col-xs-1">Sisa</th>
				<th class="col-xs-1 text-center">
					<input type="checkbox" class="cursor-p check_all" data-target="check" checked>
				</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($data['detail'] as $k_det => $v_det): ?>
				<tr>
					<td><?php echo tglIndonesia($v_det['tgl_rcn_bayar'], '-', ' '); ?></td>
					<td class="transaksi" data-val="<?php echo $v_det['transaksi']; ?>"><?php echo $v_det['transaksi']; ?></td>
					<td class="no_bayar" data-val="<?php echo $v_data['no_bayar']; ?>"><?php echo (isset($v_det['no_invoice']) && !empty($v_det['no_invoice'])) ? $v_det['no_invoice'] : $v_det['no_bayar']; ?></td>
					<td><?php echo (isset($v_det['kode_unit']) && !empty($v_det['kode_unit'])) ? $v_det['kode_unit'] : '-'; ?></td>
					<td><?php echo $v_det['periode']; ?></td>
					<td><?php echo $v_det['nama_penerima']; ?></td>
					<td class="text-right"><?php echo angkaDecimal($v_det['tagihan']); ?></td>
					<td class="text-right"><?php echo angkaDecimal($v_det['bayar']); ?></td>
					<td class="text-right tagihan" data-val="<?php echo $v_det['jumlah']; ?>"><?php echo angkaDecimal($v_det['jumlah']); ?></td>
					<td class="text-center">
						<input type="checkbox" class="cursor-p check" target="check" checked>
					</td>
				</tr>
			<?php endforeach ?>
		</tbody>
	</table>
</small>
<div class="col-xs-12 no-padding" style="margin-top: 5px;">
	<button id="btn-add" type="button" data-href="transaksi" class="btn btn-primary cursor-p col-xs-12" title="ADD" onclick="rp.submit(this)" data-id="<?php echo $data['id']; ?>"> 
		<i class="fa fa-check" aria-hidden="true"></i> Update Realisasi
	</button>
</div>