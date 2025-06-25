<div class="col-xs-12 no-padding">
    <div class="col-sm-12 no-padding" style="margin-bottom: 5px;">
        <div class="col-sm-12 no-padding">
            <div class="col-sm-1 no-padding">
                <label>PERIODE</label>
            </div>
            <div class="col-sm-11 no-padding">
                <label>: <?php echo $bulan[ (int)substr($periode, 5, 2) ].' '.substr($periode, 0, 4); ?></label>
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
                            <td><?php echo strtoupper($v_unit['nama']); ?></td>
                            <td><?php echo strtoupper($v_unit['alias_prs']); ?></td>
                            <td class="text-right"><?php echo isset($data[ $key ]) ? angkaDecimal($data[ $key ]['tot_gaji']) : 0; ?></td>
                            <td class="text-right"><?php echo isset($data[ $key ]) ? angkaDecimal($data[ $key ]['bpjs_karyawan']) : 0; ?></td>
                            <td class="text-right"><?php echo isset($data[ $key ]) ? angkaDecimal($data[ $key ]['pot_hutang']) : 0; ?></td>
                            <td class="text-right"><?php echo isset($data[ $key ]) ? angkaDecimal($data[ $key ]['pph21']) : 0; ?></td>
                            <td class="text-right"><?php echo isset($data[ $key ]) ? angkaDecimal($data[ $key ]['jml_transfer']) : 0; ?></td>
                            <td class="text-right"><?php echo isset($data[ $key ]) ? angkaDecimal($data[ $key ]['bpjs_perusahaan']) : 0; ?></td>
                            <td><?php echo isset($data[ $key ]) ? strtoupper(tglIndonesia($data[ $key ]['tgl_transfer'], '-', ' ')) : '-'; ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </small>
    </div>
    <div class="col-xs-12 no-padding"><hr style="margin-top: 5px; margin-bottom: 5px;"></div>
    <div class="col-xs-12 no-padding">
        <div class="col-xs-6 no-padding" style="padding-right: 5px;">
            <button type="button" class="col-xs-12 btn btn-danger" data-periode="<?php echo $periode; ?>" data-perusahaan="<?php echo $perusahaan; ?>" onclick="gk.delete(this)"><i class="fa fa-trash"></i> Hapus</button>
        </div>
        <div class="col-xs-6 no-padding" style="padding-left: 5px;">
            <button type="button" class="col-xs-12 btn btn-primary" data-periode="<?php echo $periode; ?>" data-perusahaan="<?php echo $perusahaan; ?>" data-edit="edit" data-href="action" onclick="gk.changeTabActive(this)"><i class="fa fa-edit"></i> Edit</button>
        </div>
    </div>
</div>