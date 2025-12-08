<?php if ( !empty($data) && count($data) > 0 ): ?>
	<?php 
        $idx_unit = 0;
    ?>
    <?php $page = 1; ?>
    <tr>
        <th class="page0 text-center" rowspan="2">UMUR</th>
        <?php $idx_unit = 0; ?>
        <?php foreach ($unit as $k_unit => $v_unit) { ?>
            <?php if ( $v_unit['kode'] != 'JTM' && $v_unit['kode'] != 'PST' ) { ?>
                <th class="page<?php echo $page; ?> text-center" colspan="4"><?php echo strtoupper($v_unit['nama']); ?></th>
                <?php $idx_unit++; ?>
            <?php } ?>

            <?php
                if ( $idx_unit == 5 ) {
                    $idx_unit = 0;
                    $page++;
                }
            ?>
        <?php } ?>
    </tr>
    <?php $page = 1; ?>
    <?php $idx_unit = 0; ?>
    <tr>
        <?php foreach ($unit as $k_unit => $v_unit) { ?>
            <?php if ( $v_unit['kode'] != 'JTM' && $v_unit['kode'] != 'PST' ) { ?>
                <th class="page<?php echo $page; ?> text-center">BW MIN</th>
                <th class="page<?php echo $page; ?> text-center">BW MAX</th>
                <th class="page<?php echo $page; ?> text-center">BW RATA</th>
                <th class="page<?php echo $page; ?> text-center">JML EKOR</th>
                <?php $idx_unit++; ?>
            <?php } ?>

            <?php
                if ( $idx_unit == 5 ) {
                    $idx_unit = 0;
                    $page++;
                }
            ?>
        <?php } ?>
    </tr>

    <?php $arr_total = null; ?>
    
    <?php for ($i=0; $i <= 50; $i++) { ?>
        <tr>
            <td class="page0 text-right"><?php echo $i ?></td>
            <?php $page = 1; ?>
            <?php $idx_unit = 0; ?>
            <?php foreach ($unit as $k_unit => $v_unit) { ?>
                <?php if ( $v_unit['kode'] != 'JTM' && $v_unit['kode'] != 'PST' ) { ?>
                    <?php if ( isset($data[ $v_unit['kode'].'|'.$i ]) ) { ?>
                        <td class="page<?php echo $page; ?> text-right"><?php echo angkaDecimal($data[ $v_unit['kode'].'|'.$i ]['min_bw']); ?></td>
                        <td class="page<?php echo $page; ?> text-right"><?php echo angkaDecimal($data[ $v_unit['kode'].'|'.$i ]['max_bw']); ?></td>
                        <td class="page<?php echo $page; ?> text-right"><?php echo angkaDecimal($data[ $v_unit['kode'].'|'.$i ]['rata_bw']); ?></td>
                        <td class="page<?php echo $page; ?> text-right"><?php echo angkaRibuan($data[ $v_unit['kode'].'|'.$i ]['total_ekor']); ?></td>
                    <?php } else { ?>
                        <td class="page<?php echo $page; ?>"></td>
                        <td class="page<?php echo $page; ?>"></td>
                        <td class="page<?php echo $page; ?>"></td>
                        <td class="page<?php echo $page; ?>"></td>
                    <?php } ?>

                    <?php
                        if ( isset($arr_total[ $v_unit['kode'] ]) ) {
                            $arr_total[ $v_unit['kode'] ] += isset($data[ $v_unit['kode'].'|'.$i ]) ? $data[ $v_unit['kode'].'|'.$i ]['total_ekor'] : 0;
                        } else {
                            $arr_total[ $v_unit['kode'] ] = isset($data[ $v_unit['kode'].'|'.$i ]) ? $data[ $v_unit['kode'].'|'.$i ]['total_ekor'] : 0;
                        }
                    ?>

                    <?php $idx_unit++; ?>
                <?php } ?>

                <?php
                    if ( $idx_unit == 5 ) {
                        $idx_unit = 0;
                        $page++;
                    }
                ?>
            <?php } ?>
        </tr>
    <?php } ?>

    <tr>
        <td class="page0 text-right"></td>
        <?php $page = 1; ?>
        <?php $idx_unit = 0; ?>
        <?php foreach ($unit as $k_unit => $v_unit) { ?>
            <?php if ( $v_unit['kode'] != 'JTM' && $v_unit['kode'] != 'PST' ) { ?>
                <td class="page<?php echo $page; ?> text-right"></td>
                <td class="page<?php echo $page; ?> text-right"></td>
                <td class="page<?php echo $page; ?> text-right"></td>
                <td class="page<?php echo $page; ?> text-right"><b><?php echo angkaRibuan($arr_total[ $v_unit['kode'] ]); ?></b></td>

                <?php $idx_unit++; ?>
            <?php } ?>
            
            <?php
                if ( $idx_unit == 5 ) {
                    $idx_unit = 0;
                    $page++;
                }
            ?>
        <?php } ?>
    </tr>
<?php else: ?>
	<tr>
		<td colspan="16">Data tidak ditemukan.</td>
	</tr>
<?php endif ?>