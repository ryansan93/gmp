<div class="modal-header">
	<span class="modal-title"><b>PINDAH PERUSAHAAN PIUTANG</b></span>
	<button type="button" class="close" data-dismiss="modal">&times;</button>
</div>
<div class="modal-body">
	<div class="row detailed">
        <div class="col-xs-12">
            <div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
                <div class="col-xs-2 no-padding"><label class="label-control">Perusahaan Asal</label></div>
                <div class="col-xs-10 no-padding">
                    <label class="label-control">: <?php echo strtoupper($data['nama_perusahaan_asal']); ?></label>
                </div>
            </div>
            <div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
                <div class="col-xs-2 no-padding"><label class="label-control">Plasma Asal</label></div>
                <div class="col-xs-10 no-padding">
                    <label class="label-control">: <?php echo strtoupper($data['kode_mitra_asal'].' | '.$data['nama_mitra_asal']); ?></label>
                </div>
            </div>
            <div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
                <div class="col-xs-2 no-padding"><label class="label-control">Perusahaan Tujuan</label></div>
                <div class="col-xs-10 no-padding">
                    <label class="label-control">: <?php echo strtoupper($data['nama_perusahaan_tujuan']); ?></label>
                </div>
            </div>
            <div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
                <div class="col-xs-2 no-padding"><label class="label-control">Plasma Tujuan</label></div>
                <div class="col-xs-10 no-padding">
                    <label class="label-control">: <?php echo strtoupper($data['kode_mitra_tujuan'].' | '.$data['nama_mitra_tujuan']); ?></label>
                </div>
            </div>
            <div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
                <div class="col-xs-2 no-padding"><label class="label-control">Nominal Hutang (Rp.)</label></div>
                <div class="col-xs-10 no-padding">
                    <label class="label-control">: <?php echo angkaDecimal($data['nominal_piutang']); ?></label>
                </div>
            </div>
            <div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
                <div class="col-xs-2 no-padding"><label class="label-control">Sudah Bayar (Rp.)</label></div>
                <div class="col-xs-10 no-padding">
                    <label class="label-control">: <?php echo angkaDecimal($data['nominal_bayar']); ?></label>
                </div>
            </div>
            <div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
                <div class="col-xs-2 no-padding"><label class="label-control">Sisa Hutang (Rp.)</label></div>
                <div class="col-xs-10 no-padding">
                    <label class="label-control">: <?php echo angkaDecimal($data['sisa_piutang']); ?></label>
                </div>
            </div>
            <div class="col-xs-12 no-padding"><hr style="margin-top: 5px; margin-bottom: 5px;"></div>
            <div class="col-xs-12 no-padding">
                <div class="col-xs-6 no-padding" style="padding-right: 5px;">
                    <button type="button" class="col-xs-12 btn btn-danger" onclick="pm.pindahPerusahaanBatal(this)"><i class="fa fa-times"></i> Batal</button>
                </div>
                <div class="col-xs-6 no-padding" style="padding-left: 5px;">
                    <button type="button" class="col-xs-12 btn btn-primary" onclick="pm.pindahPerusahaan(this)"><i class="fa fa-save"></i> Pindah Perusahaan</button>
                </div>
            </div>
        </div>
    </div>
</div>