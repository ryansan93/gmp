<div style="display:flex; flex-direction:row; gap:10px;">
    <label style="width:100px">TANGGAL</label>
    <label style="">:</label>
    <label style=""><?php echo tglIndonesia($data_header['tanggal'], "-", " "); ?></label>
</div>


<div style="display:flex; flex-direction:row; gap:10px;">
    <label style="width:100px;">UNIT</label>
    <label style="">:</label>
    <?php
        $map_unit = array_column($unit, 'nama', 'kode');
    ?>
    <label style=""><?php echo $map_unit[$data_header['unit']] ?? '-';?></label>
</div>

<div style="display:flex; flex-direction:row; gap:10px;">
    <label style="width:100px;">UMUR</label>
    <label style="">:</label>
    <label style=""><?php echo $data_header['umur']; ?></label>
</div>


<table class="table table-bordered">
    <thead>
        <tr>
            <th class="text-center">No. Reg</th>
            <th class="text-center">Plasma</th>
            <th class="text-center">Jumlah Ekor</th>
            <th class="text-center">Ekor Mati</th>
            <th class="text-center">Sisa Ekor</th>
            <th class="text-center">BB</th>
            <th class="text-center">Tonase</th>
        </tr>
    </thead>

    <tbody>

       <?php foreach($data_detail as $d){ ?>
            <?php $map_plasma = array_column($plasma, 'nama', 'noreg'); ?>

            <?php if (trim($data_header['value']) == angkaDecimal($d['bb'])) { ?>
                <tr>
                    <td><?php echo $d['noreg'] ?></td>
                    <td><?php echo $map_plasma[$d['noreg']] ?? '-'; ?></td>
                    <td class="text-right"><?php echo angkaRibuan($d['jml_ekor']) ?></td>
                    <td class="text-right"><?php echo angkaRibuan($d['ekor_mati']) ?></td>
                    <td class="text-right"><?php echo angkaRibuan($d['sisa_ekor']) ?></td>
                    <td class="text-right"><?php echo angkaDecimal($d['bb']) ?></td>
                    <td class="text-right"><?php echo angkaRibuan($d['tonase']) ?></td>
                </tr>
            <?php } else { ?>
            
                <?php if ($data_header['all'] == 1){; ?>
                    <tr>
                        <td><?php echo $d['noreg'] ?></td>
                        <td><?php echo $map_plasma[$d['noreg']] ?? '-'; ?></td>
                        <td class="text-right"><?php echo angkaRibuan($d['jml_ekor']) ?></td>
                        <td class="text-right"><?php echo angkaRibuan($d['ekor_mati']) ?></td>
                        <td class="text-right"><?php echo angkaRibuan($d['sisa_ekor']) ?></td>
                        <td class="text-right"><?php echo angkaDecimal($d['bb']) ?></td>
                        <td class="text-right"><?php echo angkaRibuan($d['tonase']) ?></td>
                    </tr>
                <?php } ?>

            <?php } ?>
            

        <?php } ?>
    </tbody>
</table>