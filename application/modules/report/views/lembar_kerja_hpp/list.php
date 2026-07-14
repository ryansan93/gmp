<?php if ( !empty($data) && count($data) > 0 ): ?>
    <?php foreach ($data as $key => $value) { ?>
        <tr class="cursor-p">
            <td class="page0" title="UNIT"><?php echo $value['unit']; ?></td>
            <td class="page0" title="NOREG"><?php echo $value['noreg']; ?></td>
            <td class="page0" title="PLASMA"><?php echo strtoupper($value['nama']); ?></td>
            <td class="page0 text-center" title="JENIS MITRA"><?php echo !empty($value['jenis_mitra']) ? strtoupper($value['jenis_mitra']) : '-'; ?></td>
            <td class="page0" title="TGL CI"><?php echo strtoupper(tglIndonesia($value['tgl_chick_in'], '-', ' ')); ?></td>
            <td class="text-right page0" title="POPULASI"><?php echo angkaDecimal($value['populasi']); ?></td>
            <!-- SALDO AWAL = rumus Saldo Akhir (Tersedia - Dijual), dihitung sebelum start_date -->
            <td class="text-right page1" title="SALDO AWAL DOC"><?php echo ($value['sa_awal_doc'] >= 0) ? angkaDecimal($value['sa_awal_doc']) : '('.angkaDecimal(abs($value['sa_awal_doc'])).')'; ?></td>
            <td class="text-right page1" title="SALDO AWAL PAKAN"><?php echo ($value['sa_awal_pakan'] >= 0) ? angkaDecimal($value['sa_awal_pakan']) : '('.angkaDecimal(abs($value['sa_awal_pakan'])).')'; ?></td>
            <td class="text-right page1" title="SALDO AWAL OVK"><?php echo ($value['sa_awal_ovk'] >= 0) ? angkaDecimal($value['sa_awal_ovk']) : '('.angkaDecimal(abs($value['sa_awal_ovk'])).')'; ?></td>
            <td class="text-right page1" title="SALDO AWAL OA"><?php echo ($value['sa_awal_oa'] >= 0) ? angkaDecimal($value['sa_awal_oa']) : '('.angkaDecimal(abs($value['sa_awal_oa'])).')'; ?></td>
            <td class="text-right page1" title="SALDO AWAL BL"><?php echo ($value['sa_awal_bl'] >= 0) ? angkaDecimal($value['sa_awal_bl']) : '('.angkaDecimal(abs($value['sa_awal_bl'])).')'; ?></td>
            <td class="text-right page1" title="SALDO AWAL BTL"><?php echo ($value['sa_awal_btl'] >= 0) ? angkaDecimal($value['sa_awal_btl']) : '('.angkaDecimal(abs($value['sa_awal_btl'])).')'; ?></td>
            <td class="text-right page1" title="SALDO AWAL RHPP"><?php echo ($value['sa_awal_rhpp'] >= 0) ? angkaDecimal($value['sa_awal_rhpp']) : '('.angkaDecimal(abs($value['sa_awal_rhpp'])).')'; ?></td>
            <td class="text-right page1" title="SALDO AWAL TOTAL"><?php echo ($value['sa_awal_total'] >= 0) ? angkaDecimal($value['sa_awal_total']) : '('.angkaDecimal(abs($value['sa_awal_total'])).')'; ?></td>
            <!-- PRODUKSI -->
            <td class="text-right page2" title="PRODUKSI DOC"><?php echo ($value['produksi_doc'] >= 0) ? angkaDecimal($value['produksi_doc']) : '('.angkaDecimal(abs($value['produksi_doc'])).')'; ?></td>
            <td class="text-right page2" title="PRODUKSI PAKAN"><?php echo ($value['produksi_pakan'] >= 0) ? angkaDecimal($value['produksi_pakan']) : '('.angkaDecimal(abs($value['produksi_pakan'])).')'; ?></td>
            <td class="text-right page2" title="PRODUKSI OVK"><?php echo ($value['produksi_ovk'] >= 0) ? angkaDecimal($value['produksi_ovk']) : '('.angkaDecimal(abs($value['produksi_ovk'])).')'; ?></td>
            <td class="text-right page2" title="PRODUKSI OA"><?php echo ($value['produksi_oa'] >= 0) ? angkaDecimal($value['produksi_oa']) : '('.angkaDecimal(abs($value['produksi_oa'])).')'; ?></td>
            <td class="text-right page2" title="PRODUKSI BL"><?php echo ($value['produksi_bl'] >= 0) ? angkaDecimal($value['produksi_bl']) : '('.angkaDecimal(abs($value['produksi_bl'])).')'; ?></td>
            <td class="text-right page2" title="PRODUKSI BTL"><?php echo ($value['produksi_btl'] >= 0) ? angkaDecimal($value['produksi_btl']) : '('.angkaDecimal(abs($value['produksi_btl'])).')'; ?></td>
            <td class="text-right page2" title="PRODUKSI RHPP"><?php echo ($value['produksi_rhpp'] >= 0) ? angkaDecimal($value['produksi_rhpp']) : '('.angkaDecimal(abs($value['produksi_rhpp'])).')'; ?></td>
            <td class="text-right page2" title="PRODUKSI TOTAL"><?php echo ($value['produksi_total'] >= 0) ? angkaDecimal($value['produksi_total']) : '('.angkaDecimal(abs($value['produksi_total'])).')'; ?></td>
            <!-- TERSEDIA -->
            <td class="text-right page3" title="TERSEDIA DOC"><?php echo ($value['tersedia_doc'] >= 0) ? angkaDecimal($value['tersedia_doc']) : '('.angkaDecimal(abs($value['tersedia_doc'])).')'; ?></td>
            <td class="text-right page3" title="TERSEDIA PAKAN"><?php echo ($value['tersedia_pakan'] >= 0) ? angkaDecimal($value['tersedia_pakan']) : '('.angkaDecimal(abs($value['tersedia_pakan'])).')'; ?></td>
            <td class="text-right page3" title="TERSEDIA OVK"><?php echo ($value['tersedia_ovk'] >= 0) ? angkaDecimal($value['tersedia_ovk']) : '('.angkaDecimal(abs($value['tersedia_ovk'])).')'; ?></td>
            <td class="text-right page3" title="TERSEDIA OA"><?php echo ($value['tersedia_oa'] >= 0) ? angkaDecimal($value['tersedia_oa']) : '('.angkaDecimal(abs($value['tersedia_oa'])).')'; ?></td>
            <td class="text-right page3" title="TERSEDIA BL"><?php echo ($value['tersedia_bl'] >= 0) ? angkaDecimal($value['tersedia_bl']) : '('.angkaDecimal(abs($value['tersedia_bl'])).')'; ?></td>
            <td class="text-right page3" title="TERSEDIA BTL"><?php echo ($value['tersedia_btl'] >= 0) ? angkaDecimal($value['tersedia_btl']) : '('.angkaDecimal(abs($value['tersedia_btl'])).')'; ?></td>
            <td class="text-right page3" title="TERSEDIA RHPP"><?php echo ($value['tersedia_rhpp'] >= 0) ? angkaDecimal($value['tersedia_rhpp']) : '('.angkaDecimal(abs($value['tersedia_rhpp'])).')'; ?></td>
            <td class="text-right page3" title="TERSEDIA TOTAL"><?php echo ($value['tersedia_total'] >= 0) ? angkaDecimal($value['tersedia_total']) : '('.angkaDecimal(abs($value['tersedia_total'])).')'; ?></td>
            <!-- BUKU BALIK CAD RHPP (menyusul) -->
            <td class="text-right page4">-</td>
            <!-- STOCK TERSEDIA = sisa_stok (saldo_awal_stok - kematian periode berjalan) -->
            <td class="text-right page4" title="STOCK TERSEDIA"><?php echo ($value['sisa_stok'] >= 0) ? angkaDecimal($value['sisa_stok']) : '('.angkaDecimal(abs($value['sisa_stok'])).')'; ?></td>
            <!-- TERJUAL = ekor panen di periode berjalan -->
            <td class="text-right page4" title="TERJUAL"><?php echo ($value['terjual'] >= 0) ? angkaDecimal($value['terjual']) : '('.angkaDecimal(abs($value['terjual'])).')'; ?></td>
            <!-- PERSENTASE = Terjual / Stock Tersedia x 100 -->
            <td class="text-right page4" title="PERSENTASE"><?php echo (($value['persentase_dijual'] >= 0) ? angkaDecimal($value['persentase_dijual']) : '('.angkaDecimal(abs($value['persentase_dijual'])).')').' %'; ?></td>
            <!-- START/END DATE PROPORSI (kosong kalau sudah panen sebelum bulan terpilih) -->
            <td class="text-center page4" title="START DATE PROPORSI"><?php echo !empty($value['start_date_proporsi']) ? strtoupper(tglIndonesia($value['start_date_proporsi'], '-', ' ')) : '-'; ?></td>
            <td class="text-center page4" title="END DATE PROPORSI"><?php echo !empty($value['end_date_proporsi']) ? strtoupper(tglIndonesia($value['end_date_proporsi'], '-', ' ')) : '-'; ?></td>
            <!-- HARI -->
            <td class="text-right page4" title="HARI"><?php echo ($value['hari'] >= 0) ? angkaDecimal($value['hari']) : '('.angkaDecimal(abs($value['hari'])).')'; ?></td>
            <!-- PROPORSI = hari x populasi -->
            <td class="text-right page4" title="PROPORSI"><?php echo ($value['proporsi'] >= 0) ? angkaDecimal($value['proporsi']) : '('.angkaDecimal(abs($value['proporsi'])).')'; ?></td>
            <!-- DIJUAL = persentase (Terjual / Stock Tersedia) x nilai Tersedia -->
            <td class="text-right page5" title="DIJUAL DOC"><?php echo ($value['dijual_doc'] >= 0) ? angkaDecimal($value['dijual_doc']) : '('.angkaDecimal(abs($value['dijual_doc'])).')'; ?></td>
            <td class="text-right page5" title="DIJUAL PAKAN"><?php echo ($value['dijual_pakan'] >= 0) ? angkaDecimal($value['dijual_pakan']) : '('.angkaDecimal(abs($value['dijual_pakan'])).')'; ?></td>
            <td class="text-right page5" title="DIJUAL OVK"><?php echo ($value['dijual_ovk'] >= 0) ? angkaDecimal($value['dijual_ovk']) : '('.angkaDecimal(abs($value['dijual_ovk'])).')'; ?></td>
            <td class="text-right page5" title="DIJUAL OA"><?php echo ($value['dijual_oa'] >= 0) ? angkaDecimal($value['dijual_oa']) : '('.angkaDecimal(abs($value['dijual_oa'])).')'; ?></td>
            <td class="text-right page5" title="DIJUAL BL"><?php echo ($value['dijual_bl'] >= 0) ? angkaDecimal($value['dijual_bl']) : '('.angkaDecimal(abs($value['dijual_bl'])).')'; ?></td>
            <td class="text-right page5" title="DIJUAL BTL"><?php echo ($value['dijual_btl'] >= 0) ? angkaDecimal($value['dijual_btl']) : '('.angkaDecimal(abs($value['dijual_btl'])).')'; ?></td>
            <td class="text-right page5" title="DIJUAL RHPP"><?php echo ($value['dijual_rhpp'] >= 0) ? angkaDecimal($value['dijual_rhpp']) : '('.angkaDecimal(abs($value['dijual_rhpp'])).')'; ?></td>
            <td class="text-right page5" title="DIJUAL TOTAL"><?php echo ($value['dijual_total'] >= 0) ? angkaDecimal($value['dijual_total']) : '('.angkaDecimal(abs($value['dijual_total'])).')'; ?></td>
            <!-- SALDO AKHIR = Tersedia - Dijual -->
            <td class="text-right page6" title="SALDO AKHIR DOC"><?php echo ($value['saldo_akhir_doc'] >= 0) ? angkaDecimal($value['saldo_akhir_doc']) : '('.angkaDecimal(abs($value['saldo_akhir_doc'])).')'; ?></td>
            <td class="text-right page6" title="SALDO AKHIR PAKAN"><?php echo ($value['saldo_akhir_pakan'] >= 0) ? angkaDecimal($value['saldo_akhir_pakan']) : '('.angkaDecimal(abs($value['saldo_akhir_pakan'])).')'; ?></td>
            <td class="text-right page6" title="SALDO AKHIR OVK"><?php echo ($value['saldo_akhir_ovk'] >= 0) ? angkaDecimal($value['saldo_akhir_ovk']) : '('.angkaDecimal(abs($value['saldo_akhir_ovk'])).')'; ?></td>
            <td class="text-right page6" title="SALDO AKHIR OA"><?php echo ($value['saldo_akhir_oa'] >= 0) ? angkaDecimal($value['saldo_akhir_oa']) : '('.angkaDecimal(abs($value['saldo_akhir_oa'])).')'; ?></td>
            <td class="text-right page6" title="SALDO AKHIR BL"><?php echo ($value['saldo_akhir_bl'] >= 0) ? angkaDecimal($value['saldo_akhir_bl']) : '('.angkaDecimal(abs($value['saldo_akhir_bl'])).')'; ?></td>
            <td class="text-right page6" title="SALDO AKHIR BTL"><?php echo ($value['saldo_akhir_btl'] >= 0) ? angkaDecimal($value['saldo_akhir_btl']) : '('.angkaDecimal(abs($value['saldo_akhir_btl'])).')'; ?></td>
            <td class="text-right page6" title="SALDO AKHIR RHPP"><?php echo ($value['saldo_akhir_rhpp'] >= 0) ? angkaDecimal($value['saldo_akhir_rhpp']) : '('.angkaDecimal(abs($value['saldo_akhir_rhpp'])).')'; ?></td>
            <td class="text-right page6" title="SALDO AKHIR TOTAL"><?php echo ($value['saldo_akhir_total'] >= 0) ? angkaDecimal($value['saldo_akhir_total']) : '('.angkaDecimal(abs($value['saldo_akhir_total'])).')'; ?></td>
        </tr>
    <?php } ?>
<?php else: ?>
	<tr>
        <td colspan="54">Data tidak ditemukan.</td>
    </tr>
<?php endif ?>