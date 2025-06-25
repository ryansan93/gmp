<!-- <?php if ( !empty($data) && count($data) > 0 ) { ?>
    <?php foreach ($data as $key => $value) { ?>
        <?php foreach ($value['detail'] as $k_det => $v_det) { ?>
            <tr class="cursor-p data">
                <td><?php echo strtoupper($v_det['nama_perusahaan']); ?></td>
                <td><?php echo strtoupper($v_det['nama_ekspedisi']); ?></td>
                <td><?php echo strtoupper($v_det['nama_npwp']); ?></td>
                <td><?php echo strtoupper($v_det['npwp']); ?></td>
                <td class="text-right ongkos_truk" data-val="<?php echo $v_det['sub_total']; ?>"><?php echo angkaDecimal($v_det['sub_total']); ?></td>
                <td class="text-right"><?php echo angkaDecimal($v_det['potongan_pph_23_prs']); ?></td>
                <td class="text-right pot_pajak" data-val="<?php echo $v_det['potongan_pph_23']; ?>"><?php echo angkaDecimal($v_det['potongan_pph_23']); ?></td>
                <td class="text-right tot_stlh_pajak" data-val="<?php echo $v_det['total']; ?>"><?php echo angkaDecimal($v_det['total']); ?></td>
                <td><?php echo $v_det['alamat']; ?></td>
                <td><?php echo $v_det['no_telp']; ?></td>
                <td><?php echo !empty($v_det['no_skb']) ? strtoupper($v_det['no_skb']) : '-'; ?></td>
                <td><?php echo !empty($v_det['tgl_habis_skb']) ? strtoupper(tglIndonesia($v_det['tgl_habis_skb'], '-', ' ')) : '-'; ?></td>
            </tr>
        <?php } ?>
    <?php } ?>
<?php } else { ?>
    <tr>
        <td colspan="12">Data tidak ditemukan.</td>
    </tr>
<?php } ?> -->

<?php if ( !empty($data) && count($data) > 0 ) { ?>
    <?php foreach ($data as $key => $value) { ?>
        <?php foreach ($value['detail'] as $k_det => $v_det) { ?>
            <?php 
                $pdpt_belum_pajak = angkaDecimal($v_det['pdpt_belum_pajak']);
                $pdpt_sudah_pajak = angkaDecimal($v_det['pdpt_sudah_pajak']);
            ?>
            <tr class="cursor-p data">
                <td><?php echo strtoupper($value['perusahaan']); ?></td>
                <td><?php echo strtoupper($v_det['no_ekspedisi']); ?></td>
                <td><?php echo strtoupper($v_det['ekspedisi']); ?></td>
                <td><?php echo strtoupper($v_det['ktp']); ?></td>
                <td><?php echo strtoupper($v_det['npwp']); ?></td>
                <td><?php echo strtoupper($v_det['alamat']); ?></td>
                <td><?php echo strtoupper($v_det['kab_kota']); ?></td>
                <td><?php echo strtoupper($v_det['prov']); ?></td>
                <td><?php echo $v_det['no_telp']; ?></td>
                <td class="text-right pendapatan" data-val="<?php echo $v_det['pdpt_belum_pajak']; ?>"><?php echo $pdpt_belum_pajak; ?></td>
                <td class="text-right"><?php echo angkaDecimal($v_det['prs_potongan_pajak']); ?></td>
                <td class="text-right pot_pajak" data-val="<?php echo $v_det['potongan_pajak']; ?>"><?php echo angkaDecimal($v_det['potongan_pajak']); ?></td>
                <td class="text-right pend_stlh_pajak" data-val="<?php echo $v_det['pdpt_sudah_pajak']; ?>"><?php echo $pdpt_sudah_pajak; ?></td>
                <td class="text-right transfer" data-val="<?php echo $v_det['transfer']; ?>"><?php echo angkaDecimal($v_det['transfer']); ?></td>
                <td><?php echo strtoupper($v_det['invoice']); ?></td>
                <td><?php echo strtoupper($value['unit']); ?></td>
                <td><?php echo strtoupper(tglIndonesia($v_det['tgl_bayar'], '-', ' ')); ?></td>
                <td><?php echo !empty($v_det['no_skb']) ? strtoupper($v_det['no_skb']) : '-'; ?></td>
                <td><?php echo !empty($v_det['tgl_habis_skb']) ? strtoupper(tglIndonesia($v_det['tgl_habis_skb'], '-', ' ')) : '-'; ?></td>
            </tr>
        <?php } ?>
    <?php } ?>
<?php } else { ?>
    <tr>
        <td colspan="18">Data tidak ditemukan.</td>
    </tr>
<?php } ?>