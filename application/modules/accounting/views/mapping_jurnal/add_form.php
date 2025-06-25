<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
	<div class="col-xs-12 no-padding"><label class="control-label text-left">Transaksi Jurnal</label></div>
	<div class="col-xs-12 no-padding">
		<select class="form-control det_jurnal_trans" data-required="1">
			<option value="" >-- Pilih --</option>
			<?php foreach ($det_jurnal_trans as $k_djt => $v_djt): ?>
				<option value="<?php echo $v_djt['id']; ?>" ><?php echo strtoupper($v_djt['jurnal_trans']['nama'].' | '.$v_djt['nama']); ?></option>
			<?php endforeach ?>
		</select>
	</div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
	<div class="col-xs-12 no-padding"><label class="control-label text-left">Report Jurnal</label></div>
	<div class="col-xs-12 no-padding">
		<select class="form-control jurnal_report" data-required="1">
			<option value="" >-- Pilih --</option>
			<?php foreach ($jurnal_report as $k_jr => $v_jr): ?>
				<option value="<?php echo $v_jr['id']; ?>" > <?php echo strtoupper($v_jr['nama']); ?> </option>
			<?php endforeach ?>
		</select>
	</div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
	<div class="col-xs-12 no-padding"><label class="control-label text-left">Posisi</label></div>
	<div class="col-xs-12 no-padding">
		<select class="form-control posisi" data-required="1">
			<option value="" >-- Pilih Posisi --</option>
			<option value="db" >DEBET</option>
			<option value="cr" >KREDIT</option>
		</select>
	</div>
</div>
<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
<div class="col-xs-12 no-padding">
	<button type="button" class="btn btn-primary pull-right" onclick="mj.save(this)"><i class="fa fa-save"></i> Simpan</button>
</div>