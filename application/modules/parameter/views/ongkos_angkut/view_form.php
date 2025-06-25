<?php // cetak_r($data); ?>
<div class="row new-line">
    <div class="text-center col-sm-12">
        <input type="hidden" data-id="<?php echo $data['id']; ?>">

        <div class="row">
            <div class="col-sm-12">
                <h5><?php echo $data['nomor'] ?></h5>
                <h5>Tanggal Berlaku : <?php echo tglIndonesia($data['tanggal'], '-', ' ', true) ?> </h5>
            </div>
        </div>
    </div>
</div>

<div class="row new-line">
    <div class="col-sm-12">
        <?php 
            $jns_tarif = 'Ekor';
            $head1 = 'Kediri';
            $head2 = 'Pasuruan';
            if ( trim($data['jns_oa']) == 'pakan' ):
                $head1 = 'Buduran';
                $head2 = 'Gedangan';
                $jns_tarif = 'Kg';
            endif 
        ?>
        <table id="tb_input_standar_performa" class="table table-bordered custom_table">
            <thead>
                <tr>
                    <th class="text-center" rowspan="3">Kabupaten / Kota</th>
                    <th class="text-center" rowspan="3">Kecamatan</th>
                    <th class="text-center" colspan="4">Tarif / <?php echo $jns_tarif; ?></th>
                </tr>
                <tr>
                    <th class="text-center" colspan="2">Lama</th>
                    <th class="text-center" colspan="2">Baru</th>
                </tr>
                <tr>
                    <th class="text-center"><?php echo $head1; ?></th>
                    <th class="text-center"><?php echo $head2; ?></th>
                    <th class="text-center"><?php echo $head1; ?></th>
                    <th class="text-center"><?php echo $head2; ?></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($data['detail'] as $key => $detail) : ?>
                <tr>
                    <td class="col-sm-1 text-center kab"> <?php echo $detail['wilayah'] ?> </td>
                    <td class="col-sm-1 text-center kec"> <?php echo $detail['kecamatan'] ?> </td>
                    <td class="col-sm-1 text-center tarif_lama"> <?php echo angkaDecimal($detail['tarif_lama']) ?> </td>
                    <td class="col-sm-1 text-center tarif_lama2"> <?php echo angkaDecimal($detail['tarif_lama2']) ?> </td>
                    <td class="col-sm-1 text-center tarif_baru"> <?php echo angkaDecimal($detail['tarif_baru']) ?> </td>
                    <td class="col-sm-1 text-center tarif_baru2"> <?php echo angkaDecimal($detail['tarif_baru2']) ?> </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="text-left col-sm-12">
        <div class="col-sm-1 no-padding">Lampiran OA</div>
        <div class="col-sm-10 no-padding">
            <a href="uploads/<?php echo $data['lampiran']['path']; ?>" target="_blank"><?php echo $data['lampiran']['filename']; ?></a>
        </div>
    </div>
    <div class="col-sm-12">
        <div class="col-sm-8 no-padding">
            <p>
                <b><u>Keterangan : </u></b>
                <?php
                    if ( !empty($data['logs']) ) {
                        foreach ($data['logs'] as $key => $log) {
                            $temp[] = '<li class="list">' . $log['deskripsi'] . ' pada ' . dateTimeFormat( $log['waktu'] ) . '</li>';
                        }
                        if ($temp) {
                            echo '<ul>' . implode("", $temp) . '</ul>';
                        }
                    }
                ?>
            </p>
            <?php if (! empty($data['alasan_tolak'])): ?>
            <p>
                <b><u>Alasan Reject :</u></b>
                <ul>
                    <li><?php echo $data['alasan_tolak'] ?></li>
                </ul>
            </p>
            <?php endif; ?>
        </div>

        <div class="col-sm-4 text-right">
            <?php if ( $akses['a_ack'] == 1 ) { ?>
                <?php if ( $data['status'] == getStatus('submit') ){ ?>
                    <button type="button" class="btn btn-primary pull-right" onclick="oa.ack()"><i class="fa fa-check"></i> ACK</button>
                <?php } ?>
            <?php } ?>

            <?php if ( $akses['a_approve'] == 1 ) { ?>
                <?php if ( $data['status'] == getStatus('ack') ){ ?>
                    <button type="button" class="btn btn-primary pull-right" onclick="oa.approve()"><i class="fa fa-check"></i> Approve</button>
                <?php } ?>
            <?php } ?>
        </div>
    </div>
</div>
