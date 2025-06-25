<div class="col-xs-12 no-padding">
    <div class="col-xs-12 no-padding">
        <div class="col-xs-6 no-padding">
            <div class="col-xs-12 no-padding" style="padding-bottom: 5px;">
                <div class="col-xs-3"><label class="control-label">Perusahaan</label></div>
                <div class="col-xs-1 text-center"><label class="control-label">:</label></div>
                <div class="col-xs-8">
                    <select class="form-control perusahaan" data-required="1">
                        <option value="">-- Pilih Perusahaan --</option>
                        <?php foreach ($perusahaan as $key => $value) { ?>
                            <?php
                                $selected = null;
                                if ( $value['kode'] == $data[0]['kode_perusahaan'] ) {
                                    $selected = 'selected';
                                }    
                            ?>
                            <option value="<?php echo $value['kode']; ?>" <?php echo $selected; ?> ><?php echo strtoupper( $value['perusahaan'] ); ?></option>
                        <?php } ?>
                    </select>
                </div>
            </div>
            <div class="col-xs-12 no-padding" style="padding-bottom: 5px;">
                <div class="col-xs-3"><label class="control-label">Jenis</label></div>
                <div class="col-xs-1 text-center"><label class="control-label">:</label></div>
                <div class="col-xs-8">
                    <select class="form-control jenis" data-required="1">
                        <option value="">-- Pilih Jenis --</option>
                        <?php foreach ($jenis as $key => $value) { ?>
                            <?php
                                $selected = null;
                                if ( $key == $data[0]['kode_jenis'] ) {
                                    $selected = 'selected';
                                }    
                            ?>
                            <option value="<?php echo $key; ?>" <?php echo $selected; ?> ><?php echo strtoupper( $value ); ?></option>
                        <?php } ?>
                    </select>
                </div>
            </div>
            <div class="col-xs-12 no-padding" style="padding-bottom: 5px;">
                <div class="col-xs-3"><label class="control-label">No. Polisi</label></div>
                <div class="col-xs-1 text-center"><label class="control-label">:</label></div>
                <div class="col-xs-8">
                    <div class="col-xs-3 no-padding">
                        <input type="text" class="form-control nopol uppercase" data-required="1" placeholder="No. Polisi" value="<?php echo $data[0]['nopol']; ?>">
                    </div>
                </div>
            </div>
            <div class="col-xs-12 no-padding" style="padding-bottom: 5px;">
                <div class="col-xs-3"><label class="control-label">Tgl Pembelian</label></div>
                <div class="col-xs-1 text-center"><label class="control-label">:</label></div>
                <div class="col-xs-8">
                    <div class="col-xs-5 no-padding">
                        <div class="input-group date datetimepicker" name="tglPembelian" id="TglPembelian">
                            <input type="text" class="form-control text-center uppercase" placeholder="Tanggal" data-required="1" data-tgl="<?php echo $data[0]['tgl_pembelian']; ?>" />
                            <span class="input-group-addon">
                                <span class="glyphicon glyphicon-calendar"></span>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xs-6 no-padding">
            <div class="col-xs-12 no-padding" style="padding-bottom: 5px;">
                <div class="col-xs-3"><label class="control-label">Merk</label></div>
                <div class="col-xs-1 text-center"><label class="control-label">:</label></div>
                <div class="col-xs-8">
                    <input type="text" class="form-control merk uppercase" data-required="1" placeholder="Merk" value="<?php echo $data[0]['merk']; ?>">
                </div>
            </div>
            <div class="col-xs-12 no-padding" style="padding-bottom: 5px;">
                <div class="col-xs-3"><label class="control-label">Tipe</label></div>
                <div class="col-xs-1 text-center"><label class="control-label">:</label></div>
                <div class="col-xs-8">
                    <div class="col-xs-6 no-padding">
                        <input type="text" class="form-control tipe uppercase" data-required="1" placeholder="Tipe" value="<?php echo $data[0]['tipe']; ?>">
                    </div>
                </div>
            </div>
            <div class="col-xs-12 no-padding" style="padding-bottom: 5px;">
                <div class="col-xs-3"><label class="control-label">Warna</label></div>
                <div class="col-xs-1 text-center"><label class="control-label">:</label></div>
                <div class="col-xs-8">
                    <div class="col-xs-4 no-padding">
                        <input type="text" class="form-control warna uppercase" data-required="1" placeholder="Warna" value="<?php echo $data[0]['warna']; ?>">
                    </div>
                </div>
            </div>
            <div class="col-xs-12 no-padding" style="padding-bottom: 5px;">
                <div class="col-xs-3"><label class="control-label">Tahun</label></div>
                <div class="col-xs-1 text-center"><label class="control-label">:</label></div>
                <div class="col-xs-8">
                    <div class="col-xs-3 no-padding">
                        <input type="text" class="form-control tahun uppercase" data-required="1" placeholder="Tahun" value="<?php echo $data[0]['tahun']; ?>">
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
                        <input type="text" class="form-control no_bpkb uppercase" data-required="1" placeholder="No. BPKP" value="<?php echo $data[0]['no_bpkb']; ?>">
                    </div>
                </div>
            </div>
            <div class="col-xs-12 no-padding" style="padding-bottom: 5px;">
                <div class="col-xs-3"><label class="control-label">No. STNK</label></div>
                <div class="col-xs-1 text-center"><label class="control-label">:</label></div>
                <div class="col-xs-8">
                    <div class="col-xs-6 no-padding">
                        <input type="text" class="form-control no_stnk uppercase" data-required="1" placeholder="No. STNK" value="<?php echo $data[0]['no_stnk']; ?>">
                    </div>
                </div>
            </div>
            <div class="col-xs-12 no-padding" style="padding-bottom: 5px;">
                <div class="col-xs-3"><label class="control-label">Masa Berlaku STNK</label></div>
                <div class="col-xs-1 text-center"><label class="control-label">:</label></div>
                <div class="col-xs-8">
                    <div class="col-xs-5 no-padding">
                        <div class="input-group date datetimepicker" name="masaBerlakuStnk" id="MasaBerlakuStnk">
                            <input type="text" class="form-control text-center uppercase" placeholder="Tanggal" data-required="1" data-tgl="<?php echo $data[0]['masa_berlaku_stnk']; ?>" />
                            <span class="input-group-addon">
                                <span class="glyphicon glyphicon-calendar"></span>
                            </span>
                        </div>
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
                        <div class="input-group date datetimepicker" name="pajakTahunKe2" id="PajakTahunKe2">
                            <input type="text" class="form-control text-center uppercase" placeholder="Tanggal" data-tgl="<?php echo $data[0]['pajak_tahun_ke2']; ?>" />
                            <span class="input-group-addon">
                                <span class="glyphicon glyphicon-calendar"></span>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xs-12 no-padding" style="padding-bottom: 5px;">
                <div class="col-xs-3"><label class="control-label">Pajak Tahun Ke 3</label></div>
                <div class="col-xs-1 text-center"><label class="control-label">:</label></div>
                <div class="col-xs-8">
                    <div class="col-xs-5 no-padding">
                        <div class="input-group date datetimepicker" name="pajakTahunKe3" id="PajakTahunKe3">
                            <input type="text" class="form-control text-center uppercase" placeholder="Tanggal" data-tgl="<?php echo $data[0]['pajak_tahun_ke3']; ?>" />
                            <span class="input-group-addon">
                                <span class="glyphicon glyphicon-calendar"></span>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xs-12 no-padding" style="padding-bottom: 5px;">
                <div class="col-xs-3"><label class="control-label">Pajak Tahun Ke 4</label></div>
                <div class="col-xs-1 text-center"><label class="control-label">:</label></div>
                <div class="col-xs-8">
                    <div class="col-xs-5 no-padding">
                        <div class="input-group date datetimepicker" name="pajakTahunKe4" id="PajakTahunKe4">
                            <input type="text" class="form-control text-center uppercase" placeholder="Tanggal" data-tgl="<?php echo $data[0]['pajak_tahun_ke4']; ?>" />
                            <span class="input-group-addon">
                                <span class="glyphicon glyphicon-calendar"></span>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xs-12 no-padding" style="padding-bottom: 5px;">
                <div class="col-xs-3"><label class="control-label">Pajak Tahun Ke 5</label></div>
                <div class="col-xs-1 text-center"><label class="control-label">:</label></div>
                <div class="col-xs-8">
                    <div class="col-xs-5 no-padding">
                        <div class="input-group date datetimepicker" name="pajakTahunKe5" id="PajakTahunKe5">
                            <input type="text" class="form-control text-center uppercase" placeholder="Tanggal" data-tgl="<?php echo $data[0]['pajak_tahun_ke5']; ?>" />
                            <span class="input-group-addon">
                                <span class="glyphicon glyphicon-calendar"></span>
                            </span>
                        </div>
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
                        <th class="col-xs-1">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data as $k_data => $v_data) { ?>
                        <tr>
                            <td>
                                <input type="text" class="form-control text-center uppercase" placeholder="Tanggal" name="tglSerahTerima" id="TglSerahTerima" data-required="1" data-tgl="<?php echo $v_data['tgl_serah_terima']; ?>" />
                            </td>
                            <td>
                                <select class="form-control pemegang_lama">
                                    <option value="">-- Pilih Karyawan --</option>
                                    <?php foreach ($karyawan as $key => $value) { ?>
                                        <?php
                                            $selected = null;
                                            if ( $value['nik'] == $v_data['kode_karyawan_lama'] ) {
                                                $selected = 'selected';
                                            }    
                                        ?>
                                        <option value="<?php echo $value['nik']; ?>" <?php echo $selected; ?> ><?php echo strtoupper( $value['nama'] ); ?></option>
                                    <?php } ?>
                                </select>
                            </td>
                            <td>
                                <select class="form-control unit_lama">
                                    <option value="">-- Pilih Unit --</option>
                                    <?php foreach ($unit as $key => $value) { ?>
                                        <?php
                                            $selected = null;
                                            if ( $value['kode'] == $v_data['kode_unit_lama'] ) {
                                                $selected = 'selected';
                                            }    
                                        ?>
                                        <option value="<?php echo $value['kode']; ?>" <?php echo $selected; ?> ><?php echo strtoupper( $value['nama'] ); ?></option>
                                    <?php } ?>
                                </select>
                            </td>
                            <td>
                                <select class="form-control pemegang_baru" data-required="1">
                                    <option value="">-- Pilih Karyawan --</option>
                                    <?php foreach ($karyawan as $key => $value) { ?>
                                        <?php
                                            $selected = null;
                                            if ( $value['nik'] == $v_data['kode_karyawan_baru'] ) {
                                                $selected = 'selected';
                                            }    
                                        ?>
                                        <option value="<?php echo $value['nik']; ?>" <?php echo $selected; ?> ><?php echo strtoupper( $value['nama'] ); ?></option>
                                    <?php } ?>
                                </select>
                            </td>
                            <td>
                                <select class="form-control unit_baru" data-required="1">
                                    <option value="">-- Pilih Unit --</option>
                                    <?php foreach ($unit as $key => $value) { ?>
                                        <?php
                                            $selected = null;
                                            if ( $value['kode'] == $v_data['kode_unit_baru'] ) {
                                                $selected = 'selected';
                                            }    
                                        ?>
                                        <option value="<?php echo $value['kode']; ?>" <?php echo $selected; ?> ><?php echo strtoupper( $value['nama'] ); ?></option>
                                    <?php } ?>
                                </select>
                            </td>
                            <td>
                                <textarea class="form-control keterangan uppercase" placeholder="Keterangan" data-required="1"><?php echo $v_data['keterangan']; ?></textarea>
                            </td>
                            <td></td>
                            <td>
                                <div class="col-xs-12 no-padding">
                                    <div class="col-xs-6 no-padding" style="padding-right: 3px;">
                                        <button type="button" class="col-xs-12 btn btn-danger"><i class="fa fa-times"></i></button>
                                    </div>
                                    <div class="col-xs-6 no-padding" style="padding-left: 3px;">
                                        <button type="button" class="col-xs-12 btn btn-primary"><i class="fa fa-plus"></i></button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </small>
    </div>
    <div class="col-xs-12 no-padding"><hr></div>
    <div class="col-xs-12 no-padding">
        <button type="button" class="btn btn-primary pull-right" onclick="kend.edit(this)" data-id="<?php echo $data[0]['id']; ?>"><i class="fa fa-save"></i> Simpan</button>
        <button type="button" class="btn btn-danger pull-right" onclick="kend.changeTabActive(this)" data-id="<?php echo $data[0]['id']; ?>" data-edit="" data-href="action" style="margin-right: 5px;"><i class="fa fa-times"></i> Batal</button>
    </div>
</div>