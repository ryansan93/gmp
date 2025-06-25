<?php 
	$disabled = null;
	if ( $resubmit == '' ) {
		$disabled = 'disabled';
	}
?>

<?php foreach ($data as $key => $v_data) { ?>
	<div class="col-lg-12 no-padding">
		<div class="col-lg-1 no-padding pull-left">
			<h5>Tgl Berlaku : </h5>
		</div>
		<div class="col-lg-2 no-padding action">
		    <!-- <input class="form-control text-center" type="text" value="" data-tipe="date"> -->
		    <div class="input-group date" id="datetimepicker1" name="tanggal-berlaku">
		        <input type="text" class="form-control text-center" data-required="1" <?php echo $disabled; ?> />
		        <span class="input-group-addon">
		            <span class="glyphicon glyphicon-calendar"></span>
		        </span>
		    </div>
		</div>
		<div class="col-lg-9 align-items-center d-flex" style="font-size: 14px;">
			<div class="col-lg-4 no-padding">
				<?php 
					if ( $akses['a_edit'] == 1 ){
						if ( $resubmit != '' ){ 
				?>
							<button id="btn-add" type="button" class="btn btn-primary cursor-p" title="EDIT" onclick="sb.edit(this)"> 
								<i class="fa fa-edit" aria-hidden="true"></i> EDIT
							</button>
				<?php 
						}
					} 
				?>
			</div>
			<div class="col-lg-8 no-padding">
				<span class="pull-right dok_no" data-id="<?php echo $v_data['id']; ?>">Dok : <?php echo $v_data['nomor']; ?></span>
			</div>
		</div>
	</div>
	<table class="table table-bordered table-hover" id="tb_input_standar_budidaya" width="100%" cellspacing="0">
		<thead>
			<tr>
				<th class="text-center">Umur (hari)</th>
				<th class="text-center">Berat Badan (g)</th>
				<th class="text-center">FCR</th>
				<th class="text-center">Daya Hidup (%)</th>
				<th class="text-center">IP</th>
				<th class="text-center">Konsumsi Pakan Perhari (g)</th>
				<?php if ( $resubmit != '' ): ?>
					<th class="text-center">Action</th>
				<?php endif ?>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($v_data['details'] as $key => $v_detail): ?>
				<tr>
					<td class="col-sm-1">
						<input class="form-control text-center" type="text" name="umur" value="<?php echo $v_detail['umur'] ?>" data-tipe="integer" <?php echo $disabled; ?> isedit="1" data-required="1">
					</td>
					<td class="col-sm-1">
						<input class="form-control text-right" type="text" name="bb" value="<?php echo angkaDecimal($v_detail['bb']); ?>" data-tipe="decimal" <?php echo $disabled; ?> isedit="1" data-required="1">
					</td>
					<td class="col-sm-1">
						<input class="form-control text-right" type="text" name="fcr" value="<?php echo angkaDecimalFormat($v_detail['fcr'], 3); ?>" data-tipe="decimal3" <?php echo $disabled; ?> isedit="1" data-required="1">
					</td>
					<td class="col-sm-1">
						<input class="form-control text-right" type="text" name="daya_hidup" value="<?php echo angkaDecimalFormat(($v_detail['daya_hidup'] * 100), 2); ?>" data-tipe="decimal" <?php echo $disabled; ?> isedit="1" data-required="1">
					</td>
					<td class="col-sm-1">
						<input class="form-control text-right" type="text" name="ip" value="<?php echo angkaRibuan($v_detail['IP']); ?>" data-tipe="integer" <?php echo $disabled; ?> isedit="1" data-required="1">
					</td>
					<td class="col-sm-1">
						<input class="form-control text-right" type="text" name="kons_pakan_harian" value="<?php echo angkaRibuan($v_detail['kons_pakan_harian']); ?>" data-tipe="integer" <?php echo $disabled; ?> isedit="1" data-required="1">
					</td>
					<?php if ( $resubmit != '' ): ?>
						<td class="action text-center col-sm-1">
							<button id="btn-add" type="button" class="btn btn-sm btn-primary cursor-p" title="ADD ROW" onclick="sb.addRowTable(this)"><i class="fa fa-plus"></i></button>
							<button id="btn-remove" type="button" class="btn btn-sm btn-danger cursor-p" title="REMOVE ROW" onclick="sb.removeRowTable(this)"><i class="fa fa-minus"></i></button>
						</td>
					<?php endif ?>
				</tr>
			<?php endforeach ?>
		</tbody>
	</table>
	<div class="col-lg-10 no-padding">
		<?php 
			$status = 'SUBMIT';
			if ( $v_data['g_status'] == 2 ) {
				$status = 'ACK';
			} else if ( $v_data['g_status'] == 3 ) {
				$status = 'APPROVE';
			} 
		?>
		<span><b>Status : <?php echo $status; ?></b></span>
	</div>
	<div class="col-lg-2">
		<?php if ( $akses['a_ack'] == 1 ){ 
				if ( $v_data['g_status'] == 1 ) { ?>
					<button id="btn-add" type="button" class="btn btn-primary cursor-p pull-right" title="ACK" onclick="sb.ack(this)" data-id="<?php echo $v_data['id']; ?>"> 
						<i class="fa fa-check" aria-hidden="true"></i> ACK
					</button>
		<?php } 
			} ?>
		<?php if ( $akses['a_approve'] == 1 ){
			if ( $v_data['g_status'] == 2 ) { ?>
			<button id="btn-add" type="button" class="btn btn-primary cursor-p pull-right" title="APPROVE" onclick="sb.approve(this)" data-id="<?php echo $v_data['id']; ?>"> 
				<i class="fa fa-check" aria-hidden="true"></i> APPROVE
			</button>
		<?php } 
			} ?>
	</div>
	<div class="col-sm-8 no-padding">
		<p>
			<b>Keterangan : </b>
			<?php
				foreach ($v_data['logs'] as $log) {
					$temp[] = '<li class="list">' . $log['deskripsi'] . ' pada ' . dateTimeFormat( $log['waktu'] ) . '</li>';
				}
				if ($temp) {
					echo '<ul>' . implode("", $temp) . '</ul>';
				}
			?>
		</p>
	</div>
<?php } ?>