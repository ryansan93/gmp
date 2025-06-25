<div class="col-xs-12 no-padding">
    <div class="col-xs-12 no-padding">
        <div class="col-xs-6 no-padding">
            <div class="col-xs-12 no-padding" style="padding-bottom: 5px;">
                <div class="col-xs-3"><label class="control-label">Perusahaan</label></div>
                <div class="col-xs-1 text-center"><label class="control-label">:</label></div>
                <div class="col-xs-8">
                    <label class="control-label"><?php echo strtoupper( $data[0]['nama_perusahaan'] ); ?></label>
                </div>
            </div>
            <div class="col-xs-12 no-padding" style="padding-bottom: 5px;">
                <div class="col-xs-3"><label class="control-label">Jenis</label></div>
                <div class="col-xs-1 text-center"><label class="control-label">:</label></div>
                <div class="col-xs-8">
                    <label class="control-label"><?php echo strtoupper( $data[0]['jenis'] ); ?></label>
                </div>
            </div>
            <div class="col-xs-12 no-padding" style="padding-bottom: 5px;">
                <div class="col-xs-3"><label class="control-label">No. Polisi</label></div>
                <div class="col-xs-1 text-center"><label class="control-label">:</label></div>
                <div class="col-xs-8">
                    <div class="col-xs-3 no-padding">
                        <label class="control-label"><?php echo strtoupper( $data[0]['nopol'] ); ?></label>
                    </div>
                </div>
            </div>
            <div class="col-xs-12 no-padding" style="padding-bottom: 5px;">
                <div class="col-xs-3"><label class="control-label">Tgl Pembelian</label></div>
                <div class="col-xs-1 text-center"><label class="control-label">:</label></div>
                <div class="col-xs-8">
                    <div class="col-xs-5 no-padding">
                        <label class="control-label"><?php echo strtoupper( tglIndonesia($data[0]['tgl_pembelian'], '-', ' ') ); ?></label>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xs-6 no-padding">
            <div class="col-xs-12 no-padding" style="padding-bottom: 5px;">
                <div class="col-xs-3"><label class="control-label">Merk</label></div>
                <div class="col-xs-1 text-center"><label class="control-label">:</label></div>
                <div class="col-xs-8">
                    <label class="control-label"><?php echo strtoupper( $data[0]['merk'] ); ?></label>
                </div>
            </div>
            <div class="col-xs-12 no-padding" style="padding-bottom: 5px;">
                <div class="col-xs-3"><label class="control-label">Tipe</label></div>
                <div class="col-xs-1 text-center"><label class="control-label">:</label></div>
                <div class="col-xs-8">
                    <div class="col-xs-6 no-padding">
                        <label class="control-label"><?php echo strtoupper( $data[0]['tipe'] ); ?></label>
                    </div>
                </div>
            </div>
            <div class="col-xs-12 no-padding" style="padding-bottom: 5px;">
                <div class="col-xs-3"><label class="control-label">Warna</label></div>
                <div class="col-xs-1 text-center"><label class="control-label">:</label></div>
                <div class="col-xs-8">
                    <div class="col-xs-4 no-padding">
                        <label class="control-label"><?php echo strtoupper( $data[0]['warna'] ); ?></label>
                    </div>
                </div>
            </div>
            <div class="col-xs-12 no-padding" style="padding-bottom: 5px;">
                <div class="col-xs-3"><label class="control-label">Tahun</label></div>
                <div class="col-xs-1 text-center"><label class="control-label">:</label></div>
                <div class="col-xs-8">
                    <div class="col-xs-3 no-padding">
                        <label class="control-label"><?php echo strtoupper( $data[0]['tahun'] ); ?></label>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xs-12 no-padding">&nbsp;</div>
    <div class="col-xs-12 no-padding">
        <div class="col-xs-6 no-padding">
            <div class="col-xs-12 no-padding" style="padding-bottom: 5px;">
                <div class="col-xs-3"><label class="control-label">No. BPKP</label></div>
                <div class="col-xs-1 text-center"><label class="control-label">:</label></div>
                <div class="col-xs-8">
                    <div class="col-xs-6 no-padding">
                        <label class="control-label"><?php echo strtoupper( $data[0]['no_bpkb'] ); ?></label>
                    </div>
                </div>
            </div>
            <div class="col-xs-12 no-padding" style="padding-bottom: 5px;">
                <div class="col-xs-3"><label class="control-label">No. STNK</label></div>
                <div class="col-xs-1 text-center"><label class="control-label">:</label></div>
                <div class="col-xs-8">
                    <div class="col-xs-6 no-padding">
                        <label class="control-label"><?php echo strtoupper( $data[0]['no_stnk'] ); ?></label>
                    </div>
                </div>
            </div>
            <div class="col-xs-12 no-padding" style="padding-bottom: 5px;">
                <div class="col-xs-3"><label class="control-label">Masa Berlaku STNK</label></div>
                <div class="col-xs-1 text-center"><label class="control-label">:</label></div>
                <div class="col-xs-8">
                    <div class="col-xs-5 no-padding">
                        <label class="control-label"><?php echo strtoupper( tglIndonesia($data[0]['masa_berlaku_stnk'], '-', ' ') ); ?></label>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xs-6 no-padding">
            <div class="col-xs-12 no-padding" style="padding-bottom: 5px;">
                <div class="col-xs-3"><label class="control-label">Pajak Tahun Ke 2</label></div>
                <div class="col-xs-1 text-center"><label class="control-label">:</label></div>
                <div class="col-xs-8">
                    <div class="col-xs-5 no-padding">
                        <label class="control-label"><?php echo (!empty($data[0]['pajak_tahun_ke2']) && $data[0]['pajak_tahun_ke2'] <> '') ? strtoupper( tglIndonesia($data[0]['pajak_tahun_ke2'], '-', ' ') ) : '-'; ?></label>
                    </div>
                </div>
            </div>
            <div class="col-xs-12 no-padding" style="padding-bottom: 5px;">
                <div class="col-xs-3"><label class="control-label">Pajak Tahun Ke 3</label></div>
                <div class="col-xs-1 text-center"><label class="control-label">:</label></div>
                <div class="col-xs-8">
                    <div class="col-xs-5 no-padding">
                        <label class="control-label"><?php echo (!empty($data[0]['pajak_tahun_ke3']) && $data[0]['pajak_tahun_ke3'] <> '') ? strtoupper( tglIndonesia($data[0]['pajak_tahun_ke3'], '-', ' ') ) : '-'; ?></label>
                    </div>
                </div>
            </div>
            <div class="col-xs-12 no-padding" style="padding-bottom: 5px;">
                <div class="col-xs-3"><label class="control-label">Pajak Tahun Ke 4</label></div>
                <div class="col-xs-1 text-center"><label class="control-label">:</label></div>
                <div class="col-xs-8">
                    <div class="col-xs-5 no-padding">
                        <label class="control-label"><?php echo (!empty($data[0]['pajak_tahun_ke4']) && $data[0]['pajak_tahun_ke4'] <> '') ? strtoupper( tglIndonesia($data[0]['pajak_tahun_ke4'], '-', ' ') ) : '-'; ?></label>
                    </div>
                </div>
            </div>
            <div class="col-xs-12 no-padding" style="padding-bottom: 5px;">
                <div class="col-xs-3"><label class="control-label">Pajak Tahun Ke 5</label></div>
                <div class="col-xs-1 text-center"><label class="control-label">:</label></div>
                <div class="col-xs-8">
                    <div class="col-xs-5 no-padding">
                        <label class="control-label"><?php echo (!empty($data[0]['pajak_tahun_ke5']) && $data[0]['pajak_tahun_ke5'] <> '') ? strtoupper( tglIndonesia($data[0]['pajak_tahun_ke5'], '-', ' ') ) : '-'; ?></label>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xs-12 no-padding">&nbsp;</div>
    <div class="col-xs-12 no-padding">
        <small>
            <table class="table table-bordered" style="margin-bottom: 0px;">
                <thead>
                    <tr>
                        <th class="col-xs-1">Tgl Serah Terima</th>
                        <th class="col-xs-2">Pemegang Lama</th>
                        <th class="col-xs-1">Unit Lama</th>
                        <th class="col-xs-2">Pemegang Baru</th>
                        <th class="col-xs-1">Unit Baru</th>
                        <th class="col-xs-3">Keterangan</th>
                        <th class="col-xs-1">Dokumen ST</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data as $key => $value) { ?>
                        <tr>
                            <td>
                                <?php echo strtoupper( tglIndonesia($value['tgl_serah_terima'], '-', ' ') ); ?>
                            </td>
                            <td>
                                <?php echo !empty($value['kode_karyawan_lama']) ? strtoupper( $value['nama_karyawan_lama'] ) : '-'; ?>
                            </td>
                            <td>
                                <?php echo !empty($value['kode_unit_lama']) ? strtoupper( $value['nama_unit_lama'] ) : '-'; ?>
                            </td>
                            <td>
                                <?php echo !empty($value['kode_karyawan_baru']) ? strtoupper( $value['nama_karyawan_baru'] ) : '-'; ?>
                            </td>
                            <td>
                                <?php echo !empty($value['kode_unit_baru']) ? strtoupper( $value['nama_unit_baru'] ) : '-'; ?>
                            </td>
                            <td>
                                <?php echo !empty($value['keterangan']) ? strtoupper( $value['keterangan'] ) : '-'; ?>
                            </td>
                            <td>
                                <button type="button" class="col-xs-12 btn btn-default" onclick="window.open('parameter/Kendaraan/cetakDokumenSerahTerima/<?php echo exEncrypt( json_encode(array('id'=>$data[0]['id'],'tgl_serah_terima'=>$value['tgl_serah_terima'])) ); ?>')"><i class="fa fa-print"></i> Dokumen ST</button>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </small>
    </div>
    <div class="col-xs-12 no-padding"><hr></div>
    <div class="col-xs-12 no-padding">
        <button type="button" class="btn btn-primary pull-right" onclick="kend.changeTabActive(this)" data-id="<?php echo $data[0]['id']; ?>" data-edit="edit" data-href="action" ><i class="fa fa-edit"></i> Edit</button>
        <button type="button" class="btn btn-danger pull-right" onclick="kend.delete(this)" data-id="<?php echo $data[0]['id']; ?>" style="margin-right: 5px;"><i class="fa fa-trash"></i> Hapus</button>
    </div>
</div>