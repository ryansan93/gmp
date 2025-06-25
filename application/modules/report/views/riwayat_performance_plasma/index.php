<div class="row content-panel">
	<div class="col-xs-12 detailed">
		<form role="form" class="form-horizontal">
			<div class="col-xs-12 no-padding">
                <div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
                    <div class="col-xs-12 no-padding"><label class="label-control">Unit</label></div>
                    <div class="col-xs-12 no-padding">
                        <select class="form-control unit" data-required="1" multiple="multiple">
                            <option value="all">All</option>
                            <?php if ( !empty($unit) ): ?>
                                <?php foreach ($unit as $k_unit => $v_unit): ?>
                                    <option value="<?php echo $v_unit['kode']; ?>"><?php echo strtoupper($v_unit['nama']); ?></option>
                                <?php endforeach ?>
                            <?php endif ?>
                        </select>
                    </div>
                </div>
                <div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
                    <div class="col-xs-12 no-padding"><label class="label-control">Plasma</label></div>
                    <div class="col-xs-12 no-padding">
                        <select class="form-control mitra" data-required="1" multiple="multiple">
                            <option class="all" value="all">All</option>
                            <?php if ( !empty($mitra) ): ?>
                                <?php foreach ($mitra as $k_mtr => $v_mtr): ?>
                                    <option class="<?php echo $v_mtr['kode_unit']; ?>" value='<?php echo json_encode(array("nomor" => $v_mtr['nomor'], "no_kdg" => (int)$v_mtr['no_kdg'])); ?>' ><?php echo strtoupper($v_mtr['kode_perusahaan']).' | '.strtoupper($v_mtr['kode_unit']).' | '.strtoupper($v_mtr['nama_mitra']).' (KDG : '.(int)$v_mtr['no_kdg'].')'; ?></option>
                                <?php endforeach ?>
                            <?php endif ?>
                        </select>
                    </div>
                </div>
                <div class="col-xs-12 no-padding">
                    <button id="btn-tampil" type="button" data-href="action" class="col-xs-12 btn btn-primary cursor-p" title="TAMPIL" onclick="rpp.getLists(this)"><i class="fa fa-search"></i> Tampilkan</button>
                </div>
			</div>
			<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
			<div class="col-xs-12 no-padding">
				<small>
					<table class="table table-bordered" style="margin-bottom: 0px;">
						<thead>
							<tr>
								<th style="width: 3%;">No.</th>
								<th style="width: 5%;">Kandang</th>
								<th style="width: 17%;">Nama Plasma</th>
								<th style="width: 5%;">Periode</th>
								<th style="width: 10%;">Tgl Chick In</th>
								<th style="width: 14%;">DOC</th>
								<th style="width: 5%;">Ekor</th>
								<th style="width: 5%;">Umur</th>
								<th style="width: 5%;">Deplesi</th>
								<th style="width: 5%;">FCR</th>
								<th style="width: 5%;">BW</th>
								<th style="width: 5%;">IP</th>
								<th style="width: 10%;">Pdpt Plasma</th>
								<th style="width: 6%;">Pdpt Plasma / Ekor</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td colspan="14">Data tidak ditemukan.</td>
							</tr>
						</tbody>
					</table>
				</small>
			</div>
            <div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
			<div class="col-xs-12 no-padding">
                <button id="btn-tampil" type="button" data-href="action" class="btn btn-default cursor-p pull-right" title="EXPORT" onclick="rpp.excryptParams()"><i class="fa fa-file-excel-o"></i> Export</button>
            </div>
		</form>
	</div>
</div>