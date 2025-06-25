<div class="col-xs-12 no-padding">
    <div class="col-xs-12 no-padding">
        <div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
			<div class="col-xs-12 no-padding"><label class="control-label">Bank</label></div>
			<div class="col-xs-12 no-padding">
				<select class="form-control bank" data-required="1">
					<?php foreach ($bank as $k_bank => $v_bank): ?>
						<option value="<?php echo $v_bank['coa']; ?>"><?php echo $v_bank['nama_coa']; ?></option>
					<?php endforeach ?>
				</select>
			</div>
		</div>
    </div>
    <div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
        <div class="col-xs-12 no-padding"><label class="control-label">Tanggal</label></div>
        <div class="col-xs-12 no-padding">
            <div class="input-group date datetimepicker" name="tanggal" id="Tanggal">
                <input type="text" class="form-control text-center" placeholder="Tanggal" data-required="1" />
                <span class="input-group-addon">
                    <span class="glyphicon glyphicon-calendar"></span>
                </span>
            </div>
        </div>
    </div>
    <div class="col-xs-12 no-padding">
        <button type="button" class="col-xs-12 btn btn-primary" onclick="chb.getDataHarian()"><i class="fa fa-search"></i> Tampilkan</button>
    </div>
    <div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10pxl"></div>
    <div class="col-xs-12 no-padding btn-tutup-saldo hide" style="margin-bottom: 5px;">
        <button type="button" class="col-xs-12 btn btn-primary" onclick="chb.save()"><i class="fa fa-check"></i> Tutup Saldo Harian</button>
    </div>
    <div class="col-xs-12 no-padding">
        <small>
            <table class="table table-bordered" style="margin-bottom: 0px;">
                <thead>
                    <tr>
                        <td colspan="3" class="text-right"><b>Total</b></td>
                        <td class="tot_debit text-right"><b>0</b></td>
                        <td class="tot_kredit text-right"><b>0</b></td>
                    </tr>
                    <tr>
                        <th class="col-xs-1">Tanggal</th>
                        <th class="col-xs-2">Akun Transaksi</th>
                        <th class="col-xs-5">Keterangan</th>
                        <th class="col-xs-2">Debit</th>
                        <th class="col-xs-2">Kredit</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="5">Data tidak ditemukan.</td>
                    </tr>
                </tbody>
            </table>
        </small>
    </div>
</div>