<?php if ( !empty($data) && count($data) > 0 ): ?>
    <?php foreach ($data as $key => $value) { ?>
        <tr class="cursor-p">
            <td class="page0"><?php echo $value['unit']; ?></td>
            <td class="page0"><?php echo $value['noreg']; ?></td>
            <td class="page0"><?php echo strtoupper($value['nama']); ?></td>
            <td class="page0"><?php echo strtoupper(tglIndonesia($value['tgl_chick_in'], '-', ' ')); ?></td>
            <td class="text-right page0"><?php echo angkaDecimal($value['populasi']); ?></td>
            <td class="text-right page1"><?php echo angkaDecimal($value['sa_pkn']); ?></td>
            <td class="text-right page1"><?php echo angkaDecimal($value['beli_pkn']); ?></td>
            <td class="text-right page1"><?php echo angkaDecimal($value['mutasi_msk_pkn']); ?></td>
            <td class="text-right page1"><?php echo angkaDecimal($value['mutasi_klwr_pkn']); ?></td>
            <td class="text-right page1"><?php echo angkaDecimal(0); ?></td>
            <td class="text-right page1"><?php echo ($value['pemakaian_pkn'] >= 0) ? angkaDecimal($value['pemakaian_pkn']) : '('.angkaDecimal(abs($value['pemakaian_pkn'])).')'; ?></td>
            <td class="text-right page1"><?php echo ($value['sisa_pkn'] >= 0) ? angkaDecimal($value['sisa_pkn']) : '('.angkaDecimal(abs($value['sisa_pkn'])).')'; ?></td>
            <td class="text-right page2"><?php echo angkaDecimal($value['sa_ovk']); ?></td>
            <td class="text-right page2"><?php echo angkaDecimal($value['beli_ovk']); ?></td>
            <td class="text-right page2"><?php echo angkaDecimal($value['mutasi_msk_ovk']); ?></td>
            <td class="text-right page2"><?php echo angkaDecimal($value['mutasi_klwr_ovk']); ?></td>
            <td class="text-right page2"><?php echo angkaDecimal(0); ?></td>
            <td class="text-right page2"><?php echo ($value['pemakaian_ovk'] >= 0) ? angkaDecimal($value['pemakaian_ovk']) : '('.angkaDecimal(abs($value['pemakaian_ovk'])).')'; ?></td>
            <td class="text-right page3"><?php echo angkaDecimal($value['sa_doc']); ?></td>
            <td class="text-right page3"><?php echo angkaDecimal($value['beli_doc']); ?></td>
            <td class="text-right page3"><?php echo angkaDecimal($value['mutasi_msk_doc']); ?></td>
            <td class="text-right page3"><?php echo angkaDecimal($value['mutasi_klwr_doc']); ?></td>
            <td class="text-right page3"><?php echo angkaDecimal($value['koreksi_doc']); ?></td>
            <td class="text-right page3"><?php echo ($value['pemakaian_doc'] >= 0) ? angkaDecimal($value['pemakaian_doc']) : '('.angkaDecimal(abs($value['pemakaian_doc'])).')'; ?></td>
            <td class="text-right page4"><?php echo angkaDecimal($value['sa_oa']); ?></td>
            <td class="text-right page4"><?php echo angkaDecimal($value['beli_oa']); ?></td>
            <td class="text-right page4"><?php echo angkaDecimal($value['mutasi_msk_oa']); ?></td>
            <td class="text-right page4"><?php echo angkaDecimal($value['mutasi_klwr_oa']); ?></td>
            <td class="text-right page4"><?php echo angkaDecimal(0); ?></td>
            <td class="text-right page4"><?php echo ($value['pemakaian_oa'] >= 0) ? angkaDecimal($value['pemakaian_oa']) : '('.angkaDecimal(abs($value['pemakaian_oa'])).')'; ?></td>
            <td class="text-right page0"><?php echo ($value['pdpt_peternak'] >= 0) ? angkaDecimal($value['pdpt_peternak']) : '('.angkaDecimal(abs($value['pdpt_peternak'])).')'; ?></td>
            <td class="text-right page0"><?php echo ($value['total'] >= 0) ? angkaDecimal($value['total']) : '('.angkaDecimal(abs($value['total'])).')'; ?></td>
        </tr>
    <?php } ?>
<?php else: ?>
	<tr>
        <td colspan="28">Data tidak ditemukan.</td>
    </tr>
<?php endif ?>