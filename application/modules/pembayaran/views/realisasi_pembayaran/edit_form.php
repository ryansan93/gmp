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
				<option data-tokens="plasma" value="plasma" <?php echo $data['jenis_pembayaran'] == 'plasma' ? 'selected' : null; ?> >PLASMA</option>
				<option data-tokens="supplier" value="supplier" <?php echo $data['jenis_pembayaran'] == 'supplier' ? 'selected' : null; ?> >SUPPLIER</option>
				<option data-tokens="ekspedisi" value="ekspedisi" <?php echo $data['jenis_pembayaran'] == 'ekspedisi' ? 'selected' : null; ?> >EKSPEDISI</option>
			</select>
		</div>
	</div>
	<?php
		$hide_plasma = 'hide';
		$required_plasma = 0;
		$hide_supplier = 'hide';
		$required_supplier = 0;
		$hide_ekspedisi = 'hide';
		$required_ekspedisi = 0;

		if ($data['jenis_pembayaran'] == 'plasma') {
			$hide_plasma = null;
			$required_plasma = 1;
		}

		if ($data['jenis_pembayaran'] == 'supplier') {
			$hide_supplier = null;
			$required_supplier = 1;
		}

		if ($data['jenis_pembayaran'] == 'ekspedisi') {
			$hide_ekspedisi = null;
			$required_ekspedisi = 1;
		}
	?>
	<div class="col-xs-12 search left-inner-addon no-padding"><hr style="margin-top: 5px; margin-bottom: 5px;"></div>
	<div class="col-xs-12 no-padding jenis supplier <?php echo $hide_supplier; ?>">
		<div class="col-xs-12 no-padding" style="margin-bottom: 5px; padding: 0px 5px 0px 0px;">
			<div class="col-xs-12 no-padding"><label class="control-label text-left">Jenis Transaksi</label></div>
			<div class="col-xs-12 no-padding">
				<select class="jenis_transaksi" multiple="multiple" width="100%" data-required="<?php echo $required_supplier; ?>">
					<option data-tokens="doc" value="doc" <?php echo ('doc' == $data['jenis_transaksi']) ? 'selected' : null; ?> >DOC</option>
					<option data-tokens="ovk" value="voadip" <?php echo ('voadip' == $data['jenis_transaksi']) ? 'selected' : null; ?> >OVK</option>
					<option data-tokens="pakan" value="pakan" <?php echo ('pakan' == $data['jenis_transaksi']) ? 'selected' : null; ?> >PAKAN</option>
				</select>
			</div>
		</div>
		<div class="col-xs-12 no-padding" style="margin-bottom: 5px; padding: 0px 5px 0px 0px;">
			<div class="col-xs-12 no-padding"><label class="control-label text-left">Supplier</label></div>
			<div class="col-xs-12 no-padding">
				<select class="supplier" width="100%" data-required="<?php echo $required_supplier; ?>">
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
		<div class="col-xs-12 no-padding jns_trans ovk hide" style="margin-bottom: 5px; padding: 0px 5px 0px 0px;">
			<div class="col-xs-12 no-padding"><label class="control-label text-left">Unit</label></div>
			<div class="col-xs-12 no-padding">
				<select class="unit_ovk" multiple="multiple" width="100%">
					<option value="all">All</option>
					<?php foreach ($unit as $key => $v_unit): ?>
						<?php 
							$selected = null;
							if ( !empty($kode_unit) && in_array($v_unit['kode'], $kode_unit) ) {
								$selected = 'selected';
							}
						?>
						<option value="<?php echo $v_unit['kode']; ?>" <?php echo $selected; ?> > <?php echo strtoupper($v_unit['nama']); ?> </option>
					<?php endforeach ?>
				</select>
			</div>
		</div>
	</div>
	<div class="col-xs-12 no-padding jenis plasma <?php echo $hide_plasma; ?>">
		<div class="col-xs-12 no-padding" style="margin-bottom: 5px; padding: 0px 0px 0px 0px;">
			<div class="col-xs-12 no-padding"><label class="control-label text-left">Jenis Transaksi</label></div>
			<div class="col-xs-12 no-padding">
				<select class="jenis_transaksi" multiple="multiple" width="100%" data-required="<?php echo $required_plasma; ?>">
					<option data-tokens="peternak" value="peternak" <?php echo ('peternak' == $data['jenis_transaksi']) ? 'selected' : null; ?> >PLASMA</option>
				</select>
			</div>
		</div>
		<div class="col-xs-6 no-padding" style="margin-bottom: 5px; padding: 0px 5px 0px 0px;">
			<div class="col-xs-12 no-padding"><label class="control-label text-left">Unit</label></div>
			<div class="col-xs-12 no-padding">
				<select class="unit" multiple="multiple" width="100%" data-required="<?php echo $required_plasma; ?>">
					<option value="all">All</option>
					<?php foreach ($unit as $key => $v_unit): ?>
						<?php 
							$selected = null;
							if ( !empty($kode_unit) && in_array($v_unit['kode'], $kode_unit) ) {
								$selected = 'selected';
							}
						?>
						<option value="<?php echo $v_unit['kode']; ?>" <?php echo $selected; ?> > <?php echo strtoupper($v_unit['nama']); ?> </option>
					<?php endforeach ?>
				</select>
			</div>
		</div>
		<div class="col-xs-6 no-padding" style="margin-bottom: 5px; padding: 0px 0px 0px 5px;">
			<div class="col-xs-12 no-padding"><label class="control-label text-left">Peternak</label></div>
			<div class="col-xs-12 no-padding">
				<select class="mitra" multiple="multiple" width="100%" data-required="<?php echo $required_plasma; ?>" data-val="<?php echo $data['peternak']; ?>">
				</select>
			</div>
		</div>
	</div>
	<div class="col-xs-12 no-padding jenis ekspedisi <?php echo $hide_ekspedisi; ?>">
		<div class="col-xs-12 no-padding" style="margin-bottom: 5px; padding: 0px 5px 0px 0px;">
			<div class="col-xs-12 no-padding"><label class="control-label text-left">Jenis Transaksi</label></div>
			<div class="col-xs-12 no-padding">
				<select class="jenis_transaksi" multiple="multiple" width="100%" data-required="<?php echo $required_ekspedisi; ?>">
					<?php 
						$selected = null;
						if ( 'oa pakan' == $data['jenis_transaksi']) {
							$selected = 'selected';
						}
					?>
					<option data-tokens="oa_pakan" value="oa pakan" <?php echo ('oa pakan' == $data['jenis_transaksi']) ? 'selected' : null; ?> >OA PAKAN</option>
				</select>
			</div>
		</div>
		<div class="col-xs-12 no-padding" style="margin-bottom: 5px; padding: 0px 5px 0px 0px;">
			<div class="col-xs-12 no-padding"><label class="control-label text-left">Ekspedisi</label></div>
			<div class="col-xs-12 no-padding">
				<select class="ekspedisi" width="100%" data-required="<?php echo $required_ekspedisi; ?>">
					<?php foreach ($ekspedisi as $k => $val): ?>
						<?php 
							$selected = null;
							if ( $val['nomor'] == $data['ekspedisi']) {
								$selected = 'selected';
							}
						?>
						<option data-tokens="<?php echo $val['nama']; ?>" value="<?php echo $val['nomor']; ?>" <?php echo $selected; ?> ><?php echo strtoupper($val['nama']); ?></option>
					<?php endforeach ?>
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
						if ( $val['kode'] == $data['perusahaan']) {
							$selected = 'selected';
						}
					?>
					<option data-tokens="<?php echo $val['nama']; ?>" value="<?php echo $val['kode']; ?>" <?php echo $selected; ?> ><?php echo strtoupper($val['nama']); ?></option>
				<?php endforeach ?>
			</select>
		</div>
	</div>
	<div class="col-xs-12 no-padding" style="margin-top: 5px; margin-bottom: 5px;">
		<button id="btn-get-lists" type="button" class="btn btn-primary cursor-p col-xs-12" title="ADD" onclick="rp.get_data_rencana_bayar(this)" data-id="<?php echo $data['id']; ?>"> 
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
				<td colspan="6"><b>Total</b></td>
				<td class="text-right total_tagihan"><b>0</b></td>
				<td class="text-right total_dn"><b>0</b></td>
				<td class="text-right total_cn"><b>0</b></td>
				<td class="text-right total_transfer"><b>0</b></td>
				<td class="text-right total_bayar"><b>0</b></td>
				<td class="text-right total_sisa"><b>0</b></td>
				<td class="text-right"></td>
			</tr>
			<tr>
				<th style="width: 6%;">Tgl Rcn Bayar</th>
				<th class="col-xs-1">Transaksi</th>
				<th class="col-xs-1">No. Bayar / No. Invoice</th>
				<th style="width: 3%;">Unit</th>
				<th style="width: 6%;">Periode</th>
				<th class="col-xs-2">Nama Penerima</th>
				<th class="col-xs-1">Tagihan</th>
				<th class="col-xs-1">DN</th>
				<th class="col-xs-1">CN</th>
				<th class="col-xs-1">Transfer</th>
				<th class="col-xs-1">Bayar</th>
				<th class="col-xs-1">Sisa</th>
				<th style="width: 2%;" class="text-center">
					<input type="checkbox" class="cursor-p check_all" data-target="check">
				</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td colspan="13">Data tidak ditemukan.</td>
			</tr>
		</tbody>
	</table>
</small>
<div class="col-xs-12 no-padding" style="margin-top: 5px;">
	<button id="btn-add" type="button" data-href="transaksi" class="btn btn-primary cursor-p col-xs-12" title="ADD" onclick="rp.submit(this)" data-id="<?php echo $data['id']; ?>"> 
		<i class="fa fa-check" aria-hidden="true"></i> Update Realisasi
	</button>
</div>