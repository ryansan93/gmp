<div class="modal-header header">
	<span class="modal-title">Form Pindah Perusahaan</span>
	<button type="button" class="close" data-dismiss="modal">&times;</button>
</div>
<!-- style="padding-left: 0px; padding-right: 0px;"> -->
<div class="modal-body body"
	<div class="col-xs-12 no-padding">
		<div class="col-xs-12 no-padding">
			<div class="col-xs-4 no-padding">
				<label class="label-control">Nama</label>
			</div>
			<div class="col-xs-8 no-padding">
				: <?php echo strtoupper( $data['nama'] ); ?>
			</div>
		</div>
        <div class="col-xs-12 no-padding">
			<div class="col-xs-4 no-padding">
				<label class="label-control">Unit</label>
			</div>
			<div class="col-xs-8 no-padding">
				: <?php echo strtoupper( $data['kode_unit'] ); ?>
			</div>
		</div>
        <div class="col-xs-12 no-padding">
			<div class="col-xs-4 no-padding">
				<label class="label-control">Perusahaan</label>
			</div>
			<div class="col-xs-8 no-padding">
				: <?php echo strtoupper( $data['nama_prs'] ); ?>
			</div>
		</div>
        <div class="col-xs-12 no-padding">
			<div class="col-xs-12 no-padding">
				<label class="label-control">Pindah Perusahaan Di</label>
			</div>
			<div class="col-xs-12 no-padding">
				<select class="form-control perusahaan" data-required="1">
                    <option>-- Pilih Perusahaan --</option>
                    <?php foreach ($perusahaan as $key => $value) { ?>
                        <?php if ( $data['kode_prs'] != $value['kode'] ) { ?>
                            <option value="<?php echo $value['kode']; ?>"><?php echo strtoupper($value['nama']); ?></option>
                        <?php } ?>
                    <?php } ?>
                </select>
			</div>
		</div>
        <div class="col-xs-12 no-padding">
            <hr style="margin-top: 10px; margin-bottom: 10px;">
        </div>
		<div class="col-xs-12 no-padding">
            <div class="col-xs-6 no-padding" style="padding-right: 5px;">
                <button type="button" class="col-xs-12 btn btn-danger" onclick="ptk.batalPindah(this)"><i class="fa fa-times"></i> Batal</button>
            </div>
            <div class="col-xs-6 no-padding" style="padding-left: 5px;">
                <button type="button" class="col-xs-12 btn btn-primary" onclick="ptk.pindahPerusahaan(this)" data-id="<?php echo $data['id']; ?>" data-np="<?php echo strtoupper($data['nama']); ?>"><i class="fa fa-save"></i> Pindah</button>
            </div>
		</div>
	</div>
</div>