<!DOCTYPE html>
<html>
<head>
	<style type="text/css">
		.str { mso-number-format:\@; }
		.decimal_number_format { mso-number-format: "\#\,\#\#0.00"; }
		.number_format { mso-number-format: "\#\,\#\#0"; }
		table.bordered thead tr th, table.bordered thead tr td, table.bordered tbody tr th, table.bordered tbody tr td {
			border: 1px solid black;
		}
		.decimal_number_format_bordered { 
			mso-number-format: "\#\,\#\#0.00";
			border: 1px solid black;
		}
	</style>
</head>
<body>
	<div>
        <table class="bordered">
            <thead>
                <tr>
                    <td class="text-right" colspan="9"><b>TOTAL</b></td>
                    <td class="text-right decimal_number_format"><b><?php echo $total['tot_pendapatan']; ?></b></td>
                    <td></td>
                    <td class="text-right decimal_number_format"><b><?php echo $total['tot_pot_pajak']; ?></b></td>
                    <td class="text-right decimal_number_format"><b><?php echo $total['tot_pdpt_stlh_pajak']; ?></b></td>
                    <td class="text-right decimal_number_format"><b><?php echo $total['tot_transfer']; ?></b></td>
                    <td colspan="4"></td>
                </tr>
                <tr>
                    <th>PERUSAHAAN</th>
                    <th>NO. PLASMA</th>
                    <th>NAMA PLASMA</th>
                    <th>KDG</th>
                    <th>NO. KTP</th>
                    <th>NO. NPWP</th>
                    <th>ALAMAT</th>
                    <th>KAB / KOTA</th>
                    <th>PROVINSI</th>
                    <th>NO. HP</th>
                    <th>NO. INVOICE</th>
                    <th>PENDAPATAN</th>
                    <th>POT PAJAK (%)</th>
                    <th>POT PAJAK (Rp.)</th>
                    <th>PEND STLH PAJAK</th>
                    <th>TRANSFER</th>
                    <th>UNIT</th>
                    <th>TGL RHPP</th>
                    <th>NO. SKB</th>
                    <th>TGL HABIS BERLAKU</th>
                </tr>
            </thead>
            <tbody>
                <?php if ( !empty($data) && count($data) > 0 ) { ?>
                    <?php foreach ($data as $key => $value) { ?>
                        <?php foreach ($value['detail'] as $k_det => $v_det) { ?>
                            <?php 
                                $class_pdpt_peternak_belum_pajak = '';
                                $pdpt_peternak_belum_pajak = null;
                                if ($v_det['pdpt_peternak_belum_pajak'] > 0) {
                                    $pdpt_peternak_belum_pajak = angkaDecimal($v_det['pdpt_peternak_belum_pajak']);
                                } else {
                                    $class_pdpt_peternak_belum_pajak = 'red';
                                    $pdpt_peternak_belum_pajak = '('.angkaDecimal(abs($v_det['pdpt_peternak_belum_pajak'])).')';
                                }

                                $class_pdpt_peternak_sudah_pajak = '';
                                $pdpt_peternak_sudah_pajak = null;
                                if ($v_det['pdpt_peternak_sudah_pajak'] > 0) {
                                    $pdpt_peternak_sudah_pajak = angkaDecimal($v_det['pdpt_peternak_sudah_pajak']);
                                } else {
                                    $class_pdpt_peternak_sudah_pajak = 'red';
                                    $pdpt_peternak_sudah_pajak = '('.angkaDecimal(abs($v_det['pdpt_peternak_sudah_pajak'])).')';
                                }
                            ?>
                            <tr class="cursor-p data">
                                <td class="str"><?php echo strtoupper($value['perusahaan']); ?></td>
                                <td class="str"><?php echo strtoupper($v_det['no_mitra']); ?></td>
                                <td class="str"><?php echo strtoupper($v_det['mitra']); ?></td>
                                <td class="str"><?php echo strtoupper($v_det['kandang']); ?></td>
                                <td class="str"><?php echo strtoupper($v_det['ktp']); ?></td>
                                <td class="str"><?php echo strtoupper($v_det['npwp']); ?></td>
                                <td class="str"><?php echo strtoupper($v_det['alamat']); ?></td>
                                <td class="str"><?php echo strtoupper($v_det['kab_kota']); ?></td>
                                <td class="str"><?php echo strtoupper($v_det['prov']); ?></td>
                                <td class="str"><?php echo $v_det['no_telp']; ?></td>
                                <td class="str"><?php echo !empty($v_det['invoice']) ? $v_det['invoice'] : '-'; ?></td>
                                <td class="text-right decimal_number_format"><?php echo $v_det['pdpt_peternak_belum_pajak']; ?></td>
                                <td class="text-right decimal_number_format"><?php echo $v_det['prs_potongan_pajak']; ?></td>
                                <td class="text-right decimal_number_format"><?php echo $v_det['potongan_pajak']; ?></td>
                                <td class="text-right decimal_number_format"><?php echo $v_det['pdpt_peternak_sudah_pajak']; ?></td>
                                <td class="text-right decimal_number_format"><?php echo $v_det['transfer']; ?></td>
                                <td class="str"><?php echo strtoupper($value['unit']); ?></td>
                                <td><?php echo strtoupper($v_det['tgl_rhpp']); ?></td>
                                <td class="str"><?php echo !empty($v_det['no_skb']) ? strtoupper($v_det['no_skb']) : '-'; ?></td>
                                <td><?php echo !empty($v_det['tgl_habis_skb']) ? strtoupper($v_det['tgl_habis_skb']) : '-'; ?></td>
                            </tr>
                        <?php } ?>
                    <?php } ?>
                <?php } else { ?>
                    <tr>
                        <td colspan="20">Data tidak ditemukan.</td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
	</div>
</body>
</html>