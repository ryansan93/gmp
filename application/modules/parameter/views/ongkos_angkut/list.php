<?php if (count($datas) > 0): ?>
   <?php foreach ($datas as $key => $val) { ?>
      <?php 
         $resubmit = null;
         if ( $val['g_status'] == 4 ) {
            $resubmit = $val['id'];
         }
      ?>

      <?php 
         $red = null;
         if ( $akses['a_ack'] == 1 ){
            $status = getStatus('submit');
            if ( $val['g_status'] == $status ) {
               $red = 'red';
            }
         } else if ( $akses['a_approve'] == 1 ){
            $status = getStatus('ack');
            if ( $val['g_status'] == $status ) {
               $red = 'red';
            }
         } else {

         }
      ?>
      <tr class="search <?php echo $red; ?>">
         <td class="tanggal"><?php echo tglIndonesia($val['mulai'], '-', ' '); ?></td>
         <td class="nomor"><?php echo $val['nomor']; ?></td>
         <td class="jenis"><?php echo 'OA ' . strtoupper($val['jns_oa']); ?></td>
         <td class="keterangan">
            <div class="col-sm-10 no-padding">
               <?php
                  //    $last_log = $val->logs->last();
                  //    $keterangan = $last_log->deskripsi . ' pada ' . dateTimeFormat( $last_log->waktu );
                  //    echo $keterangan;
                  if ( count($val['logs']) > 0 ) {
                     $last_log = $val['logs'][ count($val['logs']) - 1 ];
                     $keterangan = $last_log['deskripsi'] . ' pada ' . dateTimeFormat( $last_log['waktu'] );
                     echo $keterangan;
                  } else {
                     echo '-';
                  }
               ?>
            </div>
            <div class="col-sm-1 no-padding">
               <?php if ( $akses['a_edit'] == 1 ){ ?>
                  <button id="btn-add" type="button" data-href="action" class="btn btn-primary cursor-p pull-right" title="EDIT" onclick="oa.changeTabActive(this)" data-id="<?php echo $val['id']; ?>" data-resubmit="<?php echo 'EDIT'; ?>" data-mulai="<?php echo $val['mulai']; ?>"> 
                     <i class="fa fa-edit" aria-hidden="true"></i>
                  </button>
               <?php } ?>
            </div>
            <div class="col-sm-1 no-padding">
               <button id="btn-add" type="button" data-href="action" class="btn btn-primary cursor-p pull-right" title="LIHAT" onclick="oa.changeTabActive(this)" data-id="<?php echo $val['id']; ?>" data-resubmit="<?php echo $resubmit; ?>" data-mulai="<?php echo $val['mulai']; ?>"> 
                  <i class="fa fa-file" aria-hidden="true"></i>
               </button>
            </div>
         </td>
      </tr>
   <?php } ?>
<?php else: ?>
   <tr>
      <td colspan="5" class="text-center">Tidak ada ditemukan</td>
   </tr>
<?php endif; ?>
