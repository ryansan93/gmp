<div class="col-xs-12 no-padding">
<div class="col-sm-12 no-padding" style="margin-bottom: 5px;">
        <div class="col-sm-6 no-padding" style="padding-right: 5px;">
            <div class="col-sm-12 no-padding">
                <label>BULAN</label>
            </div>
            <div class="col-sm-12 no-padding">
                <select class="form-control bulan" data-required="1">
                    <?php foreach ($bulan as $key => $value) { ?>
                        <?php
                            $selected = null;
                            if ( $key == (int)substr($periode, 5, 2) ) {
                                $selected = 'selected';
                            }
                        ?>
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
                    <input type="text" class="form-control text-center" placeholder="TAHUN" data-required="1" data-tgl="<?php echo $periode; ?>" />
                    <span class="input-group-addon">
                        <span class="glyphicon glyphicon-calendar"></span>
                    </span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xs-12 no-padding"><hr style="margin-top: 5px; margin-bottom: 5px;"></div>
    <div class="col-sm-12 no-padding" style="margin-bottom: 5px;">
        <small>
            <table class="table table-bordered" style="margin-bottom: 0px;">
                <thead>
                    <tr>
                        <td class="text-right" colspan="2"><b>TOTAL</b></td>
                        <td class="text-right gt_gaji"><b><?php echo angkaDecimal( $data['total']['gt_gaji'] ); ?></b></td>
                        <td class="text-right gt_bpjs_karyawan"><b><?php echo angkaDecimal( $data['total']['gt_bpjs_karyawan'] ); ?></b></td>
                        <td class="text-right gt_potongan_hutang"><b><?php echo angkaDecimal( $data['total']['gt_potongan_hutang'] ); ?></b></td>
                        <td class="text-right gt_pph21_karyawan"><b><?php echo angkaDecimal( $data['total']['gt_pph21_karyawan'] ); ?></b></td>
                        <td class="text-right gt_jumlah_transfer"><b><?php echo angkaDecimal( $data['total']['gt_jumlah_transfer'] ); ?></b></td>
                        <td class="text-right gt_bpjs_perusahaan"><b><?php echo angkaDecimal( $data['total']['gt_bpjs_perusahaan'] ); ?></b></td>
                        <td class="text-right">&nbsp;</td>
                    </tr>
                    <tr>
                        <th class="col-xs-2">Nama Unit</th>
                        <th class="col-xs-1">Perusahaan</th>
                        <th class="col-xs-2">Total Gaji</th>
                        <th class="col-xs-1">BPJS Karyawan</th>
                        <th class="col-xs-1">Potongan Hutang</th>
                        <th class="col-xs-1">PPH 21 Karyawan</th>
                        <th class="col-xs-2">Jumlah Transfer</th>
                        <th class="col-xs-1">BPJS Perusahaan</th>
                        <th class="col-xs-1">Tgl Transfer</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($unit as $k_unit => $v_unit) { ?>
                        <?php $key = strtoupper($v_unit['kode'].'-'.$v_unit['kode_prs']); ?>
                        <tr>
                            <td>
                                <input type="text" class="form-control unit" placeholder="UNIT" value="<?php echo strtoupper($v_unit['nama']); ?>" data-kode="<?php echo strtoupper($v_unit['kode']); ?>" readonly>
                            </td>
                            <td>
                                <input type="text" class="form-control perusahaan" placeholder="PERUSAHAAN" value="<?php echo strtoupper($v_unit['alias_prs']); ?>" data-kode="<?php echo strtoupper($v_unit['kode_prs']); ?>" readonly>
                            </td>
                            <td>
                                <input type="text" class="form-control text-right tot_gaji" data-tipe="decimal" placeholder="TOT GAJI" data-required="1" onblur="gk.htGrandTotal(this)" value="<?php echo angkaDecimal($data[ $key ]['tot_gaji']); ?>">
                            </td>
                            <td>
                                <input type="text" class="form-control text-right bpjs" data-tipe="decimal" placeholder="BPJS" data-required="1" onblur="gk.htGrandTotal(this)" value="<?php echo angkaDecimal($data[ $key ]['bpjs_karyawan']); ?>">
                            </td>
                            <td>
                                <input type="text" class="form-control text-right potongan" data-tipe="decimal" placeholder="POTONGAN" data-required="1" onblur="gk.htGrandTotal(this)" value="<?php echo angkaDecimal($data[ $key ]['pot_hutang']); ?>">
                            </td>
                            <td>
                                <input type="text" class="form-control text-right pph21" data-tipe="decimal" placeholder="PPH 21" data-required="1" onblur="gk.htGrandTotal(this)" value="<?php echo angkaDecimal($data[ $key ]['pph21']); ?>">
                            </td>
                            <td>
                                <input type="text" class="form-control text-right jml_transfer" data-tipe="decimal" placeholder="TRANSFER" readonly value="<?php echo angkaDecimal($data[ $key ]['jml_transfer']); ?>">
                            </td>
                            <td>
                                <input type="text" class="form-control text-right bpjs_perusahaan" data-tipe="decimal" placeholder="BPJS PERUSAHAAN" data-required="1" onblur="gk.htGrandTotal(this)" value="<?php echo angkaDecimal($data[ $key ]['bpjs_perusahaan']); ?>">
                            </td>
                            <td>
                                <div class="date datetimepicker" name="tglTransfer" id="TglTransfer">
                                    <input type="text" class="form-control text-center" placeholder="TANGGAL" data-required="1" data-tgl="<?php echo $data[ $key ]['tgl_transfer']; ?>" />
                                    <!-- <span class="input-group-addon"> -->
                                        <!-- <span class="glyphicon glyphicon-calendar"></span> -->
                                    <!-- </span> -->
                                </div>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </small>
    </div>
    <div class="col-xs-12 no-padding"><hr style="margin-top: 5px; margin-bottom: 5px;"></div>
    <div class="col-xs-12 no-padding">
        <div class="col-xs-6 no-padding" style="padding-right: 5px;">
            <button type="button" class="col-xs-12 btn btn-danger" data-periode="<?php echo $periode; ?>" data-perusahaan="<?php echo $perusahaan; ?>" data-edit="" data-href="action" onclick="gk.changeTabActive(this)"><i class="fa fa-times"></i> Batal</button>
        </div>
        <div class="col-xs-6 no-padding" style="padding-left: 5px;">
            <button type="button" class="col-xs-12 btn btn-primary" data-periode="<?php echo $periode; ?>" data-perusahaan="<?php echo $perusahaan; ?>" onclick="gk.edit(this)"><i class="fa fa-save"></i> Simpan Perubahan</button>
        </div>
    </div>
</div>