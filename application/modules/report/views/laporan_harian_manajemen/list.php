<div class="col-xs-12 no-padding">
    <div class="col-xs-12 no-padding">Saldo Bank = <?php echo angkaRibuan($data['saldo_bank']); ?></div>
    <div class="col-xs-12 no-padding">Total Trf = <?php echo angkaRibuan($data['tot_trf']); ?></div>
    <!-- <div class="col-xs-12 no-padding">Hutang Pakan = <?php echo angkaRibuan($data['hutang_pakan']); ?></div> -->
    <div class="col-xs-12 no-padding">Hutang Pakan (Jatim) = <?php echo angkaRibuan($data['hutang_pakan_jatim']); ?></div>
    <div class="col-xs-12 no-padding">Hutang Pakan (Jateng) = <?php echo angkaRibuan($data['hutang_pakan_jateng']); ?></div>
    <div class="col-xs-12 no-padding">Hutang DOC = <?php echo angkaRibuan($data['hutang_doc']); ?></div>
    <div class="col-xs-12 no-padding">&nbsp;</div>
    <div class="col-xs-12 no-padding">Total Hutang = <?php echo angkaRibuan($data['tot_hutang']); ?></div>
    <div class="col-xs-12 no-padding">&nbsp;</div>
    <div class="col-xs-12 no-padding">Laba/Rugi sd <?php echo tglIndonesia($tanggal, '-', ' ') ?> = <?php echo '('.angkaRibuan($data['lr_inti_prev']).') + Laba Hari Ini ('.angkaRibuan($data['lr_inti']).') = '.angkaRibuan($data['lr_total']); ?></div>
    <div class="col-xs-12 no-padding">&nbsp;</div>
    <div class="col-xs-12 no-padding"><?php echo angkaRibuan($data['jml_rhpp']).' RHPP / '.angkaRibuan($data['jml_box']).' Box'; ?></div>
    <div class="col-xs-12 no-padding">&nbsp;</div>
    <div class="col-xs-12 no-padding hide">Laba = <?php echo angkaRibuan($data['lr_per_ekor']).' / Ekor'; ?></div>
    <div class="col-xs-12 no-padding">Laba = <?php echo angkaRibuan($data['lr_per_kg']).' / Kg'; ?></div>
    <div class="col-xs-12 no-padding">Harga Rata Ayam = <?php echo angkaRibuan($data['hrg_rata_ayam']); ?></div>
    <div class="col-xs-12 no-padding">Harga Doc = <?php echo angkaRibuan($data['hrg_doc']); ?></div>
</div>