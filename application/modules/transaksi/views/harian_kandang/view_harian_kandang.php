<div class="panel-body">
    <div class="row new-line">
        <div class="col-sm-12">
            <form class="form-horizontal" role="form">
                <div class="form-group">
                    <div class="col-sm-1" >
                        <b>Periode</b>
                    </div>
                    <div class="col-sm-3">
                        <b>:</b> <?php echo tglIndonesia($rdim['mulai'], '-', ' ') . ' s.d ' . tglIndonesia($rdim['selesai'], '-', ' ') ?>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-sm-1" >
                        <b>Noreg</b>
                    </div>
                    <div class="col-sm-2">
                        <b>:</b> <?php echo $data['d_rdim_submit']['noreg']; ?>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-sm-1" >
                        <b>Mitra</b>
                    </div>
                    <div class="col-sm-4">
                        <b>:</b> <?php echo $data['d_rdim_submit']['d_mitra_mapping']['d_mitra']['nama']; ?>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-sm-1" >
                        <b>Populasi</b>
                    </div>
                    <div class="col-sm-1">
                        <b>:</b> <?php echo angkaRibuan($data['d_rdim_submit']['populasi']); ?>
                    </div>
                </div>
                <hr>
                <div class="row new-line">
                    <div class="col-sm-4">
                        <div class="form-group">
                            <div class="col-sm-5" >
                                <b>Tanggal timbang</b>
                            </div>
                            <div class="col-sm-5">
                                <b>:</b> <?php echo tglIndonesia($data['tgl_timbang'], '-', ' ', true); ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-5" >
                                <b>Umur</b>
                            </div>
                            <div class="col-sm-4">
                                <b>:</b> <?php echo angkaRibuan($data['umur']); ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-5" >
                                <b>Jumlah Kematian</b>
                            </div>
                            <div class="col-sm-4">
                                <b>:</b> <?php echo angkaRibuan($data['mati']); ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-5" >
                                <b>BB Rata2</b>
                            </div>
                            <div class="col-sm-4">
                                <b>:</b> <?php echo angkaDecimal($data['bb']); ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <div class="col-sm-4" >
                                <b>Terima Pakan</b>
                            </div>
                            <div class="col-sm-3">
                                <b>:</b> <?php echo angkaRibuan($data['terima_pakan']); ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-4" >
                                <b>Sisa Pakan di Kandang</b>
                            </div>
                            <div class="col-sm-3">
                                <b>:</b> <?php echo angkaRibuan($data['sisa_pakan']); ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-4" >
                                <b>Komentar PIC</b>
                            </div>
                            <div class="col-sm-8">
                                <b>:</b> <?php echo $data['ket']; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="row new-line">
                    <div class="col-sm-4">
                        <table id="tb_sekat" class="table table-hover table-bordered custom_table table-form small">
                            <thead>
                                <tr>
                                    <th class="col-sm-1">Jml sekat</th>
                                    <th class="col-sm-1">BB</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($data['detail'] as $k_detail => $v_detail): ?>
                                    <tr>
                                        <td><?php echo $v_detail['jml_sekat']; ?></td>
                                        <td><?php echo $v_detail['bb']; ?></td>
                                    </tr>
                                <?php endforeach ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>