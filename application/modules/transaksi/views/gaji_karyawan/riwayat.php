<?php if ( $akses['a_submit'] == 1 ): ?>
    <div class="col-xs-12 no-padding">
        <button id="btn-add" type="button" data-href="action" class="btn btn-success cursor-p col-xs-12" title="ADD" onclick="gk.changeTabActive(this)"> 
            <i class="fa fa-plus" aria-hidden="true"></i> ADD
        </button>
    </div>
    <div class="col-xs-12 search left-inner-addon no-padding"><hr style="margin-top: 5px; margin-bottom: 5px;"></div>
<?php endif ?>
<div class="col-xs-12 no-padding">
    <div class="col-sm-12 no-padding" style="margin-bottom: 10px;">
        <div class="col-sm-6 no-padding" style="padding-right: 5px;">
            <div class="col-sm-12 no-padding">
                <label>BULAN</label>
            </div>
            <div class="col-sm-12 no-padding">
                <select class="form-control bulan" data-required="1">
                    <option value="all">ALL</option>
                    <?php foreach ($bulan as $key => $value) { ?>
                        <option value="<?php echo $key; ?>"><?php echo $value; ?></option>
                    <?php } ?>
                </select>
            </div>
        </div>
        <div class="col-sm-6 no-padding" style="padding-left: 5px;">
            <div class="col-sm-12 no-padding">
                <label>TAHUN</label>
            </div>
            <div class="col-sm-12 no-padding">
                <div class="input-group date datetimepicker" name="tahun" id="tahun">
                    <input type="text" class="form-control text-center" placeholder="TAHUN" data-required="1" />
                    <span class="input-group-addon">
                        <span class="glyphicon glyphicon-calendar"></span>
                    </span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-12 no-padding" style="margin-bottom: 10px;">
        <div class="col-sm-12 no-padding">
            <label>PERUSAHAAN</label>
        </div>
        <div class="col-sm-12 no-padding">
            <select class="col-sm-12 form-control perusahaan" data-required="1">
                <option value="">Pilih Perusahaan</option>
                <?php if ( count($perusahaan) > 0 ): ?>
                    <?php foreach ($perusahaan as $k_prs => $v_prs): ?>
                        <?php 
                            $text_perusahaan = '';

                            $perusahaan_old = null;
                            foreach ($v_prs['detail'] as $k_det => $v_det) {
                                if ( !empty($perusahaan_old) ) {
                                    $text_perusahaan .= ', ';
                                }
                                $text_perusahaan .= $v_det['nama'];

                                $perusahaan_old = $v_det['nama'];
                            } 
                        ?>
                        <option value="<?php echo $v_prs['kode_gabung_perusahaan']; ?>"><?php echo strtoupper($text_perusahaan); ?></option>
                    <?php endforeach ?>
                <?php endif ?>
            </select>
        </div>
    </div>
</div>
<div class="col-xs-12 no-padding" style="margin-top: 5px; margin-bottom: 5px;">
    <button type="button" class="btn btn-primary col-xs-12" title="ADD" onclick="gk.getLists()"> 
        <i class="fa fa-search" aria-hidden="true"></i> Tampilkan
    </button>
</div>
<div class="col-xs-12 search left-inner-addon no-padding"><hr style="margin-top: 5px; margin-bottom: 5px;"></div>
<div class="col-xs-12 search left-inner-addon" style="padding: 0px 0px 5px 0px;">
	<i class="glyphicon glyphicon-search"></i><input class="form-control" type="search" data-table="tbl_riwayat" placeholder="Search" onkeyup="filter_all(this)">
</div>
<small>
	<span>Klik pada baris untuk melihat detail.</span>
	<table class="table table-bordered tbl_riwayat" style="margin-bottom: 0px;">
		<thead>
			<tr>
				<th class="col-xs-1">Bulan</th>
				<th class="col-xs-2">Total Gaji</th>
				<th class="col-xs-1">BPJS Karyawan</th>
				<th class="col-xs-2">Potongan Hutang</th>
				<th class="col-xs-2">PPH 21 Karyawan</th>
				<th class="col-xs-2">Jumlah Transfer</th>
				<th class="col-xs-1">BPJS Perusahaan</th>
				<th class="col-xs-1">Tgl Transfer</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td colspan="8">Data tidak ditemukan.</td>
			</tr>
		</tbody>
	</table>
</small>