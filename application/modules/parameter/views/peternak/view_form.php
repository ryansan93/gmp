<div class="panel panel-default" id="view_data_mitra">
    <!-- <div class="panel-heading">
        <?php echo $title_panel ?>
        <div class="pull-right">
            <button type="button" class="btn btn-xs btn-default hide" onclick="goBack()"> <i class="fa fa-arrow-left"></i> Back</button>
        </div>
    </div> -->
    <div class="panel-body">
        <div class="row new-line">
            <div class="col-md-12" style="padding-bottom: 15px;">
                <label class="control-label pull-right">Nomor : <?php echo $mitra->nomor ?>, Status : <?php echo strtoupper($mitra->status) ?></label>
                <input type="hidden" data-idmitra="<?php echo $mitra->id; ?>" />
                <div class="pull-left">
                    <label for="">Action : </label>
                    <?php if ( $akses['a_approve'] == 1 && $mitra->status == 'ack' ): ?>
                        <button type="button" class="btn btn-large btn-primary" onclick="ptk.ack_reject(this)" data-action="approve" data-id="<?php echo $mitra['id'] ?>"><i class="fa fa-check"></i> Approve</button>
                    <?php elseif( $akses['a_ack'] == 1 && $mitra->status == 'submit' ): ?>
                        <button type="button" class="btn btn-large btn-primary" onclick="ptk.ack_reject(this)" data-action="ack" data-id="<?php echo $mitra['id'] ?>"><i class="fa fa-check"></i> ACK</button>
                    <?php else: ?>
                        <!-- <button type="button" class="btn btn-large btn-primary" onclick="ptk.formPindahPerusahaan(this)" data-id="<?php echo $mitra['id'] ?>"><i class="fa fa-exchange"></i> Pindah Perusahaan</button> -->
                    <?php endif; ?>
                </div>
            </div><hr>
            <div class="col-md-12">
                <form class="form-horizontal" role="form">
                    <div name="data-mitra">
                        <div class="form-group align-items-center d-flex">
                            <span class="col-sm-2 text-right">Jenis Mitra</span>
                            <div class="col-sm-2">
                                <select class="form-control" name="jenis_mitra" disabled>
                                    <?php foreach ($jenis_mitra as $key => $jmitra): ?>
                                        <?php if ($mitra['jenis'] == $key): ?>
                                            <option value="<?php echo $key ?>"><?php echo $jmitra ?></option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <!-- <div class="col-sm-8">
                                <?php 
                                    $disabled = 'disabled';
                                    if ( !empty($mitra['posisi']) ) {
                                        $disabled = null;
                                    } 
                                ?>
                                <a type="button" class="btn btn-default pull-right <?php echo $disabled; ?>" href="https://www.google.com/maps/?q=<?php echo $mitra['posisi']['lat_long']; ?>" target="_blank">
                                    <i class="fa fa-map-marker"></i> Lokasi
                                </a>
                            </div> -->
                        </div>
                        <div class="form-group align-items-center d-flex">
                            <span class="col-sm-2 text-right">Perusahaan</span>
                            <div class="col-sm-4">
                                <select class="form-control" name="perusahaan" disabled>
                                    <?php foreach ($perusahaan as $key => $value): ?>
                                        <?php if ($mitra['perusahaan'] == $value['kode']): ?>
                                            <option value="<?php echo $key; ?>"><?php echo strtoupper($value['nama']); ?></option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group align-items-center d-flex">
                            <span class="col-sm-2 text-right">Nama Plasma</span>
                            <div class="col-sm-5">
                                <input type="text" class="form-control" name="nama_mitra" placeholder="nama mitra" value="<?php echo $mitra['nama'] ?>" required="1" readonly="">
                            </div>
                        </div>
                        <div class="form-group align-items-center d-flex">
                            <span class="col-sm-2 text-right">Nama Group</span>
                            <div class="col-sm-5">
                                <input type="text" class="form-control" name="pemilik" placeholder="pemilik" value="<?php echo $mitra->d_pemilik->nomor.' - '.$mitra['d_pemilik']['nama']; ?>" required="1" readonly="">
                            </div>
                        </div>
                        <div class="form-group align-items-center d-flex">
                            <span class="col-sm-2 text-right">KTP</span>
                            <div class="col-sm-3">
                                <input type="text" class="form-control" name="ktp" placeholder="nomor ktp" value="<?php echo $mitra['ktp'] ?>" required="1" readonly="">
                            </div>
                        </div>
                        <div class="form-group align-items-center d-flex">
                            <span class="col-sm-2 text-right">NPWP</span>
                            <div class="col-sm-3">
                                <input type="email" class="form-control" name="npwp" placeholder="npwp" value="<?php echo $mitra['npwp'] ?>" readonly="">
                            </div>
                        </div>
                        <div class="form-group align-items-center d-flex">
                            <span class="col-sm-2 text-right">No. SKB</span>
                            <div class="col-sm-3">
                                <input type="text" class="form-control" name="skb" placeholder="No. SKB" value="<?php echo $mitra['skb'] ?>" readonly="" maxlength="50">
                            </div>
                            <span class="col-sm-2 text-right">Tgl Habis Berlaku</span>
                            <div class="col-sm-3">
                                <input type="text" class="form-control text-center" placeholder="Tanggal" value="<?php echo tglIndonesia($mitra['tgl_habis_skb'], '-', ' ') ?>" readonly />
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2"></label>
                            <div class="col-sm-3">
                                <table class="table telepon">
                                    <thead>
                                        <tr>
                                        <th class="text-left">Telepon</th>
                                        <th hidden=""></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($mitra['telepons'] as $telp): ?>
                                            <tr>
                                                <td>
                                                    <input class="form-control" type="text" name="telepon" value="<?php echo $telp['nomor'] ?>" placeholder="telepon" readonly="">
                                                    </td>
                                                <th hidden="">
                                                    <button type="button" class="btn btn-danger" onclick="ptk.removeRowTable(this)"><i class="fa fa-minus"></i></button>
                                                    <button type="button" class="btn btn-default" onclick="ptk.addRowTable(this)"><i class="fa fa-plus"></i></button>
                                                </th>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="form-group align-items-center d-flex">
                            <span class="col-sm-2 text-right">Plafon</span>
                            <div class="col-sm-2">
                                <input type="text" class="form-control text-right" name="plafon" placeholder="Plafon" data-tipe="integer" value="<?php echo angkaRibuan($mitra['plafon']); ?>" readonly="">
                            </div>
                        </div>
                        <div class="form-group align-items-center d-flex">
                            <span class="col-sm-2 text-right">Jatuh Tempo</span>
                            <div class="col-sm-1">
                                <input type="text" class="form-control text-right" name="jatuh_tempo" placeholder="Jatuh Tempo" data-tipe="integer" value="<?php echo angkaRibuan($mitra['jatuh_tempo']); ?>" readonly="">
                            </div>
                            <div class="col-sm-1">Hari</div>
                        </div>


                        <div class="row">
                            <div class="col-sm-12">
                                <span for="">Alamat</span>
                            </div>
                        </div>
                        <div class="row form-lokasi">
                            <div class="col-sm-4">
                                <div class="form-group align-items-center d-flex">
                                    <span class="col-sm-6 text-right">Provinsi</span>
                                    <div class="col-sm-6">
                                        <select class="form-control" name="provinsi" onchange="ptk.getListLokasi(this, 'kab')" disabled="">
                                            <option value="<?php echo $mitra['dKecamatan']['dKota']['dProvinsi']['id'] ?>"><?php echo $mitra['dKecamatan']['dKota']['dProvinsi']['nama'] ?></option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="col-sm-6 no-padding">
                                        <div class="col-sm-8 pull-right no-padding">
                                            <select class="form-control" name="tipe_lokasi" onchange="ptk.getListLokasi(this, 'kab')" disabled="">
                                                <?php foreach ($tipe_lokasi as $key => $lokasi): ?>
                                                    <?php if ($key == $mitra['dKecamatan']['dKota']['jenis']): ?>
                                                        <option value="<?php echo $key ?>"><?php echo $lokasi ?></option>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <select class="form-control" name="kabupaten" onchange="ptk.getListLokasi(this, 'kec')" disabled="">
                                            <option value="<?php echo $mitra['dKecamatan']['dKota']['id'] ?>"><?php echo $mitra['dKecamatan']['dKota']['nama'] ?></option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group align-items-center d-flex">
                                    <span class="col-sm-6 text-right">Kecamatan</span>
                                    <div class="col-sm-6">
                                        <select class="form-control" name="kecamatan" disabled="" disabled="">
                                            <option value="<?php echo $mitra['dKecamatan']['id'] ?>"><?php echo $mitra['dKecamatan']['nama'] ?></option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group align-items-center d-flex">
                                    <span class="col-sm-6 text-right">Kelurahan/Desa</span>
                                    <div class="col-sm-5">
                                        <input type="text" class="form-control" name="kelurahan" placeholder="kelurahan/desa" data-id="" value="<?php echo $mitra['alamat_kelurahan'] ?>" required="1" readonly="">
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <div class="col-sm-8">
                                        <textarea disabled="" class="form-control" name="alamat" style="height: 73px;"><?php echo $mitra['alamat_jalan'] ?></textarea>
                                    </div>
                                </div>
                                <div class="form-group align-items-center d-flex">
                                    <span class="col-sm-1 text-right">RT</span>
                                    <div class="col-sm-2">
                                        <input type="text" class="form-control" name="rt" placeholder="RT" value="<?php echo $mitra['alamat_rt'] ?>" required="1" readonly="">
                                    </div>
                                </div>
                                <div class="form-group align-items-center d-flex">
                                    <span class="col-sm-1 text-right">RW</span>
                                    <div class="col-sm-2">
                                        <input type="text" class="form-control" name="rw" placeholder="RW" value="<?php echo $mitra['alamat_rw'] ?>" required="1" readonly="">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm-12">
                                <span for="">Rekening</span>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="form-group align-items-center d-flex">
                                    <span class="col-sm-2 text-right">Bank</span>
                                    <div class="col-sm-3">
                                        <input type="text" class="form-control" name="bank" placeholder="bank" value="<?php echo $mitra['bank'] ?>" required="1" readonly="">
                                    </div>
                                </div>
                                <div class="form-group align-items-center d-flex">
                                    <span class="col-sm-2 text-right">Cabang Bank</span>
                                    <div class="col-sm-3">
                                        <input type="text" class="form-control" name="cabang-bank" placeholder="cabang bank" value="<?php echo $mitra['rekening_cabang_bank'] ?>" required="1" readonly="">
                                    </div>
                                </div>
                                <div class="form-group align-items-center d-flex">
                                    <span class="col-sm-2 text-right">No. Rekening</span>
                                    <div class="col-sm-3">
                                        <input type="text" class="form-control" name="no-rekening" placeholder="no. rekening" value="<?php echo $mitra['rekening_nomor'] ?>" required="1" readonly="">
                                    </div>
                                </div>
                                <div class="form-group align-items-center d-flex">
                                    <span class="col-sm-2 text-right">Pemilik Rekening</span>
                                    <div class="col-sm-4">
                                        <input type="text" class="form-control" name="pemilik-rekening" placeholder="pemilik rekening" value="<?php echo $mitra['rekening_pemilik'] ?>" required="1" readonly="">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm-12">
                                <span for="">Jaminan</span>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="form-group align-items-center d-flex">
                                    <span class="col-sm-2 text-right">Keterangan Jaminan</span>
                                    <div class="col-sm-5">
                                        <textarea name="jaminan" class="form-control" rows="2" disabled><?php echo $mitra['keterangan_jaminan'] ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-sm-12">
                            <!-- Nav tabs -->
                            <div class="panel-heading no-padding">
                                <ul class="nav nav-tabs nav-justified">
                                    <li class="nav-item">
                                        <a class="nav-link active" data-toggle="tab" href="#lampiran_mitra" data-tab="lampiran">Lampiran</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" data-toggle="tab" href="#lampiran_jaminan_mitra" data-tab="jaminan">Jaminan</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" data-toggle="tab" href="#kandang" data-tab="kandang">Kandang</a>
                                    </li>
                                </ul>
                            </div>

                            <div class="tab-content new-line">
                                <!-- tab kandang -->
                                <div class="tab-pane fade" id="kandang">
                                    <form class="form form-horizontal">
                                        <?php foreach ( $mitra['perwakilans'] as $perwakilan): ?>
                                            <div name="data-perwakilan">
                                                <div class="col-lg-12 div-bordered align-items-center d-flex">
                                                    <div class="col-sm-5 align-items-center d-flex">
                                                        <span class="col-sm-5 text-right">Perwakilan</span>
                                                        <div class="col-sm-7">
                                                            <select class="form-control" name="perwakilan" onchange="ptk.getListUnitPerwakilan(this)" disabled="">
                                                                    <option value="<?php echo $perwakilan->dPerwakilan['id'] ?>"><?php echo $perwakilan->dPerwakilan['nama'] ?></option>
                                                                </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-7 align-items-center d-flex">
                                                        <span class="col-sm-1 text-right">NIM</span>
                                                        <div class="col-sm-4">
                                                            <input type="text" class="form-control" name="nim" value="<?php echo $perwakilan->nim ?>" placeholder="nim" maxlength="8" readonly="">
                                                        </div>
                                                    </div>
                                                </div>

                                                <?php foreach ($perwakilan->kandangs as $kandang): ?>
                                                    <div name="data-kandang">
                                                        <input type="hidden" name="mitramap-id" data-mitramapid="<?php echo $kandang['mitra_mapping']; ?>">
                                                        <div class="col-sm-12 no-padding" style="margin-bottom: 10px;">
                                                            <fieldset>
                                                                <legend>Kandang</legend>
                                                                    <div class="row col-sm-12">
                                                                        <div class="col-sm-4 no-padding">
                                                                            <div class="col-sm-12">
                                                                                <div class="form-group align-items-center d-flex">
                                                                                    <span class="col-sm-4 text-right">Grup</span>
                                                                                    <div class="col-sm-3">
                                                                                        <input type="text" class="form-control text-center" name="grup" data-tipe="integer" value="<?php echo $kandang->grup ?>" maxlength="2" readonly>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="form-group align-items-center d-flex">
                                                                                    <span class="col-sm-4 text-right">No. Kandang</span>
                                                                                    <div class="col-sm-2">
                                                                                        <input type="text" class="form-control text-center" name="no-kandang" value="<?php echo $kandang->kandang ?>" maxlength="2" readonly="">
                                                                                    </div>
                                                                                </div>
                                                                                <div class="form-group align-items-center d-flex">
                                                                                    <span class="col-sm-4 text-right">Kapasitas</span>
                                                                                    <div class="col-sm-4">
                                                                                        <input type="text" class="form-control" name="kapasitas" value="<?php echo $kandang->ekor_kapasitas ?>" data-tipe="integer" readonly="">
                                                                                    </div>
                                                                                    <span class="col-sm-2 text-right">Ekor</span>
                                                                                </div>
                                                                                <div class="form-group align-items-center d-flex">
                                                                                    <span class="col-sm-4 text-right">Tipe Kandang</span>
                                                                                    <div class="col-sm-8">
                                                                                        <select class="form-control" name="tipe_kandang" disabled="">
                                                                                            <?php foreach ($tipe_kandang as $key_kandang => $vkandang): ?>
                                                                                                <?php if ($key_kandang == $kandang['tipe']): ?>
                                                                                                    <option value="<?php echo $key_kandang ?>"><?php echo $vkandang ?></option>
                                                                                                <?php endif; ?>
                                                                                            <?php endforeach; ?>
                                                                                        </select>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="form-group align-items-center d-flex">
                                                                                    <span class="col-sm-4 text-right">Status</span>
                                                                                    <div class="col-sm-6">
                                                                                        <select class="form-control" name="status">
                                                                                            <?php foreach ($status_kandang as $key => $s_kandang): ?>
                                                                                                <?php 
                                                                                                    $select = null;
                                                                                                    if ($key == $kandang['status']) {
                                                                                                        $select = 'selected';
                                                                                                    } 
                                                                                                ?>
                                                                                                <option value="<?php echo $key ?>" <?php echo $select; ?> ><?php echo $s_kandang ?></option>
                                                                                            <?php endforeach; ?>
                                                                                        </select>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="form-group align-items-center d-flex">
                                                                                    <span class="col-sm-4 text-right">Foto Kandang</span>
                                                                                    <div class="col-sm-8">
                                                                                        <?php 
                                                                                            $key = $mitra->nomor.'-'.$kandang->kandang;

                                                                                            $disabled = 'disabled';
                                                                                            $href = null;
                                                                                            if ( isset($mitra_posisi[ $key ]) && !empty($mitra_posisi[ $key ]) ) {
                                                                                                $disabled = null;
                                                                                                $href = "uploads/".$mitra_posisi[ $key ]['foto_kunjungan'];
                                                                                            } 
                                                                                        ?>
                                                                                        <a type="button" class="btn btn-default pull-left <?php echo $disabled; ?>" href="<?php echo $href ?>" target="_blank">
                                                                                            <i class="fa fa-camera"></i> Foto
                                                                                        </a>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>

                                                                        <div class="col-sm-4 no-padding form-lokasi">
                                                                            <div class="form-group">
                                                                                <div class="col-sm-12">
                                                                                    <div class="form-group align-items-center d-flex">
                                                                                        <span class="col-sm-4 text-right">Unit</span>
                                                                                        <div class="col-sm-8">
                                                                                            <select class="form-control" name="unit" disabled="">
                                                                                                <option value="<?php echo $kandang->d_unit->id ?>"><?php echo $kandang->d_unit->nama ?></option>
                                                                                            </select>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="form-group align-items-center d-flex">
                                                                                        <span class="col-sm-4 text-right">Provinsi</span>
                                                                                        <div class="col-sm-8">
                                                                                            <select class="form-control" name="provinsi" onchange="ptk.getListLokasi(this, 'kab')" disabled="">
                                                                                                <option value="<?php echo $kandang->dKecamatan['dKota']['dProvinsi']['id'] ?>"><?php echo $kandang->dKecamatan['dKota']['dProvinsi']['nama'] ?></option>
                                                                                            </select>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="form-group">
                                                                                        <div class="col-sm-4 no-padding">
                                                                                            <div class="col-sm-11 pull-right no-padding">
                                                                                                <select class="form-control" name="tipe_lokasi" onchange="ptk.getListLokasi(this, 'kab')" disabled="">
                                                                                                    <?php foreach ($tipe_lokasi as $key => $lokasi): ?>
                                                                                                        <option value="<?php echo $key ?>"><?php echo $lokasi ?></option>
                                                                                                    <?php endforeach; ?>
                                                                                                </select>
                                                                                            </div>
                                                                                        </div>
                                                                                        <div class="col-sm-8">
                                                                                            <select class="form-control" name="kabupaten" onchange="ptk.getListLokasi(this, 'kec')" disabled="">
                                                                                                <option value="<?php echo $kandang->dKecamatan['dKota']['id'] ?>"><?php echo $kandang->dKecamatan['dKota']['nama'] ?></option>
                                                                                            </select>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="form-group align-items-center d-flex">
                                                                                        <span class="col-sm-4 text-right">Kecamatan</span>
                                                                                        <div class="col-sm-8">
                                                                                            <select class="form-control" name="kecamatan" disabled="">
                                                                                                <option value="<?php echo $kandang->dKecamatan['id'] ?>"><?php echo $kandang->dKecamatan['nama'] ?></option>
                                                                                            </select>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="form-group align-items-center d-flex">
                                                                                        <span class="col-sm-4 text-right">Kelurahan/Desa</span>
                                                                                        <div class="col-sm-8">
                                                                                            <input type="text" class="form-control autocomplete_lokasi" name="kelurahan" placeholder="kelurahan/desa" value="<?php echo $kandang->alamat_kelurahan ?>" required="1" readonly="">
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="form-group align-items-center d-flex">
                                                                                        <span class="col-sm-4 text-right">Posisi Kandang</span>
                                                                                        <div class="col-sm-8">
                                                                                            <?php 
                                                                                                $key = $mitra->nomor.'-'.$kandang->kandang;

                                                                                                $disabled = 'disabled';
                                                                                                $href = null;
                                                                                                if ( isset($mitra_posisi[ $key ]) && !empty($mitra_posisi[ $key ]) ) {
                                                                                                    $disabled = null;
                                                                                                    $href = "https://www.google.com/maps/?q=".$mitra_posisi[ $key ]['lat_long'];
                                                                                                } 
                                                                                            ?>
                                                                                            <a type="button" class="btn btn-default pull-left <?php echo $disabled; ?>" href="<?php echo $href; ?>" target="_blank">
                                                                                                <i class="fa fa-map-marker"></i> Lokasi
                                                                                            </a>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>

                                                                        <div class="col-sm-4">
                                                                            <div class="form-group">
                                                                                <div class="col-sm-12">
                                                                                    <textarea disabled="" class="form-control" name="alamat" style="height: 73px;"><?php echo $kandang->alamat_jalan ?></textarea>
                                                                                </div>
                                                                            </div>
                                                                            <div class="form-group align-items-center d-flex">
                                                                                <span class="col-sm-1 text-right">RT</span>
                                                                                <div class="col-sm-3">
                                                                                    <input type="text" class="form-control" name="rt" placeholder="RT" value="<?php echo $kandang->alamat_rt ?>" required="1" readonly="">
                                                                                </div>
                                                                            </div>
                                                                            <div class="form-group align-items-center d-flex">
                                                                                <span class="col-sm-1 text-right">RW</span>
                                                                                <div class="col-sm-3">
                                                                                    <input type="text" class="form-control" name="rw" placeholder="RW" value="<?php echo $kandang->alamat_rw ?>" required="1" readonly="">
                                                                                </div>
                                                                            </div>
                                                                            <div class="form-group align-items-center d-flex">
                                                                                <span class="col-sm-1 text-right">OA</span>
                                                                                <div class="col-sm-8">
                                                                                    <input type="text" class="form-control" name="ongkos-angkut" value="<?php echo $kandang->ongkos_angkut ?>" placeholder="ongkos angkut" data-tipe="decimal" required="1" readonly>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>

                                                                <div class="row col-sm-12">
                                                                    <span for="">Bangunan Kandang</span>
                                                                    <table class="table table-bordered bangunan-kandang">
                                                                        <thead>
                                                                            <tr>
                                                                                <th class="text-center">#</th>
                                                                                <th class="text-right">Panjang (m)</th>
                                                                                <th class="text-right">Lebar (m)</th>
                                                                                <th class="text-right">Jumlah Unit</th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                            <?php foreach ($kandang->bangunans as $bangunan): ?>
                                                                                <tr>
                                                                                    <td class=""><input class="form-control text-center" type="text" name="no" value="<?php echo $bangunan['bangunan'] ?>" readonly="" ></td>
                                                                                    <td class=""><input class="form-control text-right" type="text" name="panjang" value="<?php echo angkaDecimal($bangunan['meter_panjang']) ?>" readonly=""  data-tipe="decimal"></td>
                                                                                    <td class=""><input class="form-control text-right" type="text" name="lebar" value="<?php echo angkaDecimal($bangunan['meter_lebar']) ?>" readonly=""  data-tipe="decimal"></td>
                                                                                    <td class=""><input class="form-control text-right" type="text" name="jml" value="<?php echo $bangunan['jumlah_unit'] ?>" readonly=""  data-tipe="integer"></td>
                                                                                </tr>
                                                                            <?php endforeach; ?>
                                                                        </tbody>
                                                                    </table>
                                                                </div>

                                                                <div class="row col-sm-12">
                                                                    <table class="table table-bordered tpanel lampiran-kandang">
                                                                        <thead>
                                                                            <tr>
                                                                                <th colspan="3">Lampiran <span class="cursor-p pull-right" onclick="ptk.collapseLampiran(this)"><i class="glyphicon glyphicon-chevron-up"></i></span>  </th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody class="tpanel-body">
                                                                            <?php foreach ($kandang->lampirans as $lkandang): ?>
                                                                                <tr data-idnama="<?php echo $lkandang['d_nama_lampiran']['id'] ?>">
                                                                                    <td><?php echo $lkandang['d_nama_lampiran']['sequence'] ?></td>
                                                                                    <td class=""><?php echo $lkandang['d_nama_lampiran']['nama'] ?></td>
                                                                                    <td class="col-sm-5">
                                                                                        <label class="">
                                                                                            <a href="uploads/<?php echo $lkandang['path'] ?>" target="_blank"><?php echo $lkandang['filename'] ?></a>
                                                                                        </label>
                                                                                    </td>
                                                                                </tr>
                                                                            <?php endforeach; ?>
                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                            </fieldset>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>

                                                <!-- kandang after approve -->
                                                <div class="row hide" name="data-kandang-after-approve" data-hide="hide">
                                                    <div class="col-sm-12">
                                                        <fieldset>
                                                            <legend> <button type="button" class="btn btn-xs btn-danger" onclick="ptk.hapusKandangExistPerwakilan(this)"><i class="fa fa-trash"></i></button> | Kandang</legend>
                                                            <div class="row col-sm-12">
                                                                <div class="col-sm-4 no-padding">
                                                                    <div class="col-sm-12">
                                                                        <div class="form-group align-items-center d-flex">
                                                                            <span class="col-sm-4 text-right">Grup</span>
                                                                            <div class="col-sm-3">
                                                                                <input type="text" class="form-control text-center" name="grup" data-tipe="integer" value="" maxlength="2" placeholder="grup" required>
                                                                            </div>
                                                                        </div>
                                                                        <div class="form-group align-items-center d-flex">
                                                                            <span class="col-sm-4 text-right">No. Kandang</span>
                                                                            <div class="col-sm-3">
                                                                                <input type="text" class="form-control text-center" name="no-kandang" value="" maxlength="2" placeholder="no. kandang" required>
                                                                            </div>
                                                                        </div>
                                                                        <div class="form-group align-items-center d-flex">
                                                                            <span class="col-sm-4 text-right">Kapasitas</span>
                                                                            <div class="col-sm-4">
                                                                                <input type="text" class="form-control" name="kapasitas" value="" data-tipe="integer" placeholder="kapasitas" required>
                                                                            </div>
                                                                            <span class="col-sm-2 text-right">Ekor</span>
                                                                        </div>
                                                                        <div class="form-group align-items-center d-flex">
                                                                            <span class="col-sm-4 text-right">Tipe Kandang</span>
                                                                            <div class="col-sm-8">
                                                                                <select class="form-control" name="tipe_kandang" placeholder="tipe kandang" required>
                                                                                    <option value="">pilih tipe kandang</option>
                                                                                    <?php foreach ($tipe_kandang as $key => $kandang): ?>
                                                                                        <option value="<?php echo $key ?>"><?php echo $kandang ?></option>
                                                                                    <?php endforeach; ?>
                                                                                </select>
                                                                            </div>
                                                                        </div>
                                                                        <div class="form-group align-items-center d-flex">
                                                                            <span class="col-sm-4 text-right">Status</span>
                                                                            <div class="col-sm-6">
                                                                                <select class="form-control" name="status" placeholder="status kandang" required>
                                                                                    <option value="">pilih status</option>
                                                                                    <?php foreach ($status_kandang as $key => $s_kandang): ?>
                                                                                        <option value="<?php echo $key ?>"><?php echo $s_kandang ?></option>
                                                                                    <?php endforeach; ?>
                                                                                </select>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <div class="col-sm-4 no-padding form-lokasi">
                                                                    <div class="form-group">
                                                                        <div class="col-sm-12">
                                                                            <div class="form-group align-items-center d-flex">
                                                                                <span class="col-sm-4 text-right">Unit</span>
                                                                                <div class="col-sm-8">
                                                                                    <select class="form-control" name="unit" placeholder="unit" required>
                                                                                    </select>
                                                                                </div>
                                                                            </div>
                                                                            <div class="form-group align-items-center d-flex">
                                                                                <span class="col-sm-4 text-right">Provinsi</span>
                                                                                <div class="col-sm-8">
                                                                                    <select class="form-control" name="provinsi" onchange="ptk.getListLokasi(this, 'kab')" placeholder="provinsi" required>
                                                                                        <option value="">pilih provinsi</option>
                                                                                        <?php foreach ($list_provinsi as $prov): ?>
                                                                                            <option value="<?php echo $prov['id'] ?>"><?php echo $prov['nama'] ?></option>
                                                                                        <?php endforeach; ?>
                                                                                    </select>
                                                                                </div>
                                                                            </div>
                                                                            <div class="form-group">
                                                                                <div class="col-sm-4 no-padding">
                                                                                    <div class="col-sm-11 pull-right">
                                                                                        <select class="form-control" name="tipe_lokasi" onchange="ptk.getListLokasi(this, 'kab')">
                                                                                            <?php foreach ($tipe_lokasi as $key => $lokasi): ?>
                                                                                                <option value="<?php echo $key ?>"><?php echo $lokasi ?></option>
                                                                                            <?php endforeach; ?>
                                                                                        </select>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="col-sm-8">
                                                                                    <select class="form-control" name="kabupaten" onchange="ptk.getListLokasi(this, 'kec')" placeholder="kabupaten/kota" required>
                                                                                        <option value="">pilih kota/kabupaten</option>
                                                                                    </select>
                                                                                </div>
                                                                            </div>
                                                                            <div class="form-group align-items-center d-flex">
                                                                                <span class="col-sm-4 text-right">Kecamatan</span>
                                                                                <div class="col-sm-8">
                                                                                    <select class="form-control" name="kecamatan" placeholder="kecamatan" required>
                                                                                        <option value="">pilih kecamatan</option>
                                                                                    </select>
                                                                                </div>
                                                                            </div>
                                                                            <div class="form-group align-items-center d-flex">
                                                                                <span class="col-sm-4 text-right">Kelurahan/Desa</span>
                                                                                <div class="col-sm-8">
                                                                                    <input type="text" class="form-control autocomplete_lokasi" name="kelurahan" placeholder="kelurahan/desa" required>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <div class="col-sm-4">
                                                                    <div class="form-group">
                                                                        <div class="col-sm-12">
                                                                            <textarea class="form-control" name="alamat" style="height: 73px;" placeholder=" . . . alamat / jalan kandang" required></textarea>
                                                                        </div>
                                                                    </div>
                                                                    <div class="form-group align-items-center d-flex">
                                                                        <span class="col-sm-1 text-right">RT</span>
                                                                        <div class="col-sm-3">
                                                                            <input type="text" class="form-control" name="rt" placeholder="RT">
                                                                        </div>
                                                                    </div>
                                                                    <div class="form-group align-items-center d-flex">
                                                                        <span class="col-sm-1 text-right">RW</span>
                                                                        <div class="col-sm-3">
                                                                            <input type="text" class="form-control" name="rw" placeholder="RW">
                                                                        </div>
                                                                    </div>
                                                                    <div class="form-group align-items-center d-flex">
                                                                        <span class="col-sm-1 text-right">OA</span>
                                                                        <div class="col-sm-8">
                                                                            <input type="text" class="form-control" name="ongkos-angkut" placeholder="ongkos angkut" data-tipe="integer" required>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="row col-sm-12">
                                                                <span for="">Bangunan Kandang</span>
                                                                <table class="table table-bordered bangunan-kandang">
                                                                    <thead>
                                                                        <tr>
                                                                            <th class="text-center">#</th>
                                                                            <th class="text-right">Panjang (m)</th>
                                                                            <th class="text-right">Lebar (m)</th>
                                                                            <th class="text-right">Jumlah Unit</th>
                                                                            <th></th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        <tr>
                                                                            <td class=""><input class="form-control text-center" type="text" name="no" placeholder="no bangunan kandang" value="" required></td>
                                                                            <td class=""><input class="form-control text-right" type="text" name="panjang" placeholder="panjang bangunan kandang" value="" required data-tipe="decimal"></td>
                                                                            <td class=""><input class="form-control text-right" type="text" name="lebar" placeholder="lebar bangunan kandang" value="" required data-tipe="decimal"></td>
                                                                            <td class=""><input class="form-control text-right" type="text" name="jml" placeholder="jumlah bangunan kandang" value="" data-tipe="integer" required></td>
                                                                            <td class="text-center">
                                                                                <button type="button" class="btn btn-danger" onclick="ptk.removeRowTable(this)"><i class="fa fa-minus"></i></button>
                                                                                <button type="button" class="btn btn-default" onclick="ptk.addRowTable(this)"><i class="fa fa-plus"></i></button>
                                                                            </td>
                                                                        </tr>
                                                                    </tbody>
                                                                </table>
                                                            </div>

                                                            <div class="row col-sm-12">
                                                                <table class="table table-bordered tpanel lampiran-kandang">
                                                                    <thead>
                                                                        <tr>
                                                                            <th colspan="3">Lampiran <span class="cursor-p pull-right" onclick="ptk.collapseLampiran(this)"><i class="glyphicon glyphicon-chevron-up"></i></span>  </th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody class="tpanel-body" hidden="">
                                                                        <?php foreach ($list_lampiran_kandang as $lkandang): ?>
                                                                            <tr data-idnama="<?php echo $lkandang['id'] ?>">
                                                                                <td><?php echo $lkandang['sequence'] ?></td>
                                                                                <td class=""><?php echo $lkandang['nama'] ?></td>
                                                                                <td class="col-sm-5 lampiran">
                                                                                    <label class="">
                                                                                        <input type="file" onchange="showNameFile(this)" class="file_lampiran" data-required="<?php echo $lkandang['required'] ?>" name="" placeholder="lampiran kandang - <?php echo $lkandang['nama'] ?>" data-allowtypes="doc|pdf|docx" style="display: none;">
                                                                                        <i class="glyphicon glyphicon-paperclip cursor-p"></i>
                                                                                    </label>
                                                                                </td>
                                                                            </tr>
                                                                        <?php endforeach; ?>
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </fieldset>
                                                    </div>
                                                </div>
                                                <!-- end - kandang after approve -->

                                                <?php 
                                                    $hide = 'hide';
                                                    if ( strtoupper($mitra->status) == 'APPROVE' && $akses['a_submit'] == 1 ) {
                                                        $hide = null;
                                                    }
                                                ?>
                                                <div class="text-right <?php echo $hide; ?>" style="margin-bottom:12px; margin-top:12px;">
                                                    <button type="button" class="btn btn-default" onclick="ptk.tambahKandangExistPerwakilan(this)">Tambah Kandang</button>
                                                </div>

                                            </div>
                                        <?php endforeach; ?>
                                        <!-- end - data perwakilan -->
                                        <?php 
                                            $hide = 'hide';
                                            if ( strtoupper($mitra->status) == 'APPROVE' && $akses['a_submit'] == 1 ) {
                                                $hide = null;
                                            }
                                        ?>
                                        <div class="row open-form <?php echo $hide; ?>">
                                            <div class="col-sm-12">
                                                <div class="pull-left hide simpan">
                                                    <button type="button" class="btn btn-primary" onclick="ptk.simpanPerwakilanAfterApprove(this)">Simpan</button>
                                                </div>
                                                <div class="pull-right">
                                                    <button type="button" class="btn btn-default" onclick="ptk.openFormAddPerwakilanNew(this)">Tambah Perwakilan</button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>

                                    <div name="data-perwakilan-form-new" class="hide">
                                        <form class="form form-horizontal">
                                            <div name="data-perwakilan-after-approve">
                                                <div class="col-lg-12 div-bordered align-items-center d-flex">
                                                    <div class="col-sm-5">
                                                        <span class="col-sm-5 text-right">Perwakilan</span>
                                                        <div class="col-sm-7">
                                                            <select class="form-control" name="perwakilan" onchange="ptk.getListUnitPerwakilanAfterApprove(this)" placeholder="perwakilan" required>
                                                                <option value="">pilih perwakilan</option>
                                                                <?php foreach ($list_perwakilan as $perwakilan): ?>
                                                                    <option value="<?php echo $perwakilan['id'] ?>"><?php echo $perwakilan['nama'] ?></option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-7">
                                                        <span class="col-sm-1 text-right">NIM</span>
                                                        <div class="col-sm-4">
                                                            <input type="text" class="form-control" name="nim" value="" placeholder="nim" maxlength="8" required>
                                                        </div>
                                                        <button type="button" class="btn btn-danger pull-right" onclick="ptk.hapusPerwakilan(this)"><i class="fa fa-trash"></i></button>
                                                    </div>
                                                </div>

                                                <div class="row" name="data-kandang">
                                                    <div class="col-sm-12">
                                                        <fieldset>
                                                            <legend> <button type="button" class="btn btn-xs btn-danger" onclick="ptk.hapusKandangAfterApprove(this)"><i class="fa fa-trash"></i></button> | Kandang</legend>
                                                            <div class="row col-sm-12">
                                                                <div class="col-sm-4 no-padding">
                                                                    <div class="col-sm-12">
                                                                        <div class="form-group align-items-center d-flex">
                                                                            <span class="col-sm-4 text-right">Grup</span>
                                                                            <div class="col-sm-3">
                                                                                <input type="text" class="form-control text-center" name="grup" data-tipe="integer" value="" maxlength="2" placeholder="grup" required>
                                                                            </div>
                                                                        </div>
                                                                        <div class="form-group align-items-center d-flex">
                                                                            <span class="col-sm-4 text-right">No. Kandang</span>
                                                                            <div class="col-sm-2">
                                                                                <input type="text" class="form-control text-center" name="no-kandang" value="" maxlength="2" placeholder="no. kandang" required>
                                                                            </div>
                                                                        </div>
                                                                        <div class="form-group align-items-center d-flex">
                                                                            <span class="col-sm-4 text-right">Kapasitas</span>
                                                                            <div class="col-sm-4">
                                                                                <input type="text" class="form-control" name="kapasitas" value="" data-tipe="integer" placeholder="kapasitas" required>
                                                                            </div>
                                                                            <span class="col-sm-2 text-right">Ekor</span>
                                                                        </div>
                                                                        <div class="form-group align-items-center d-flex">
                                                                            <span class="col-sm-4 text-right">Tipe Kandang</span>
                                                                            <div class="col-sm-8">
                                                                                <select class="form-control" name="tipe_kandang" placeholder="tipe kandang" required>
                                                                                    <option value="">pilih tipe kandang</option>
                                                                                    <?php foreach ($tipe_kandang as $key => $kandang): ?>
                                                                                        <option value="<?php echo $key ?>"><?php echo $kandang ?></option>
                                                                                    <?php endforeach; ?>
                                                                                </select>
                                                                            </div>
                                                                        </div>
                                                                        <div class="form-group align-items-center d-flex">
                                                                            <span class="col-sm-4 text-right">Status</span>
                                                                            <div class="col-sm-6">
                                                                                <select class="form-control" name="status" placeholder="status kandang" required>
                                                                                    <option value="">pilih status</option>
                                                                                    <?php foreach ($status_kandang as $key => $s_kandang): ?>
                                                                                        <option value="<?php echo $key ?>"><?php echo $s_kandang ?></option>
                                                                                    <?php endforeach; ?>
                                                                                </select>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <div class="col-sm-4 no-padding form-lokasi">
                                                                    <div class="form-group">
                                                                        <div class="col-sm-12">
                                                                            <div class="form-group align-items-center d-flex">
                                                                                <span class="col-sm-4 text-right">Unit</span>
                                                                                <div class="col-sm-8">
                                                                                    <select class="form-control" name="unit" placeholder="unit" required>
                                                                                    </select>
                                                                                </div>
                                                                            </div>
                                                                            <div class="form-group align-items-center d-flex">
                                                                                <span class="col-sm-4 text-right">Provinsi</span>
                                                                                <div class="col-sm-8">
                                                                                    <select class="form-control" name="provinsi" onchange="ptk.getListLokasi(this, 'kab')" placeholder="provinsi" required>
                                                                                        <option value="">pilih provinsi</option>
                                                                                        <?php foreach ($list_provinsi as $prov): ?>
                                                                                            <option value="<?php echo $prov['id'] ?>"><?php echo $prov['nama'] ?></option>
                                                                                        <?php endforeach; ?>
                                                                                    </select>
                                                                                </div>
                                                                            </div>
                                                                            <div class="form-group">
                                                                                <div class="col-sm-4 no-padding">
                                                                                    <div class="col-sm-11 pull-right">
                                                                                        <select class="form-control" name="tipe_lokasi" onchange="ptk.getListLokasi(this, 'kab')">
                                                                                            <?php foreach ($tipe_lokasi as $key => $lokasi): ?>
                                                                                                <option value="<?php echo $key ?>"><?php echo $lokasi ?></option>
                                                                                            <?php endforeach; ?>
                                                                                        </select>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="col-sm-8">
                                                                                    <select class="form-control" name="kabupaten" onchange="ptk.getListLokasi(this, 'kec')" placeholder="kabupaten/kota" required>
                                                                                        <option value="">pilih kota/kabupaten</option>
                                                                                    </select>
                                                                                </div>
                                                                            </div>
                                                                            <div class="form-group align-items-center d-flex">
                                                                                <span class="col-sm-4 text-right">Kecamatan</span>
                                                                                <div class="col-sm-8">
                                                                                    <select class="form-control" name="kecamatan" placeholder="kecamatan" required>
                                                                                        <option value="">pilih kecamatan</option>
                                                                                    </select>
                                                                                </div>
                                                                            </div>
                                                                            <div class="form-group align-items-center d-flex">
                                                                                <span class="col-sm-4 text-right">Kelurahan/Desa</span>
                                                                                <div class="col-sm-8">
                                                                                    <input type="text" class="form-control autocomplete_lokasi" name="kelurahan" placeholder="kelurahan/desa" required>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <div class="col-sm-4">
                                                                    <div class="form-group">
                                                                        <div class="col-sm-12">
                                                                            <textarea class="form-control" name="alamat" style="height: 73px;" placeholder=" . . . alamat / jalan kandang" required></textarea>
                                                                        </div>
                                                                    </div>
                                                                    <div class="form-group align-items-center d-flex">
                                                                        <span class="col-sm-1 text-right">RT</span>
                                                                        <div class="col-sm-3">
                                                                            <input type="text" class="form-control" name="rt" placeholder="RT">
                                                                        </div>
                                                                    </div>
                                                                    <div class="form-group align-items-center d-flex">
                                                                        <span class="col-sm-1 text-right">RW</span>
                                                                        <div class="col-sm-3">
                                                                            <input type="text" class="form-control" name="rw" placeholder="RW">
                                                                        </div>
                                                                    </div>
                                                                    <div class="form-group align-items-center d-flex">
                                                                        <span class="col-sm-1 text-right">OA</span>
                                                                        <div class="col-sm-8">
                                                                            <input type="text" class="form-control" name="ongkos-angkut" placeholder="ongkos angkut" data-tipe="integer" required>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="row col-sm-12">
                                                                <span for="">Bangunan Kandang</span>
                                                                <table class="table table-bordered bangunan-kandang">
                                                                    <thead>
                                                                        <tr>
                                                                            <th class="text-center">#</th>
                                                                            <th class="text-right">Panjang (m)</th>
                                                                            <th class="text-right">Lebar (m)</th>
                                                                            <th class="text-right">Jumlah Unit</th>
                                                                            <th></th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        <tr>
                                                                            <td class=""><input class="form-control text-center" type="text" name="no" placeholder="no bangunan kandang" value="" required></td>
                                                                            <td class=""><input class="form-control text-right" type="text" name="panjang" placeholder="panjang bangunan kandang" value="" required data-tipe="decimal"></td>
                                                                            <td class=""><input class="form-control text-right" type="text" name="lebar" placeholder="lebar bangunan kandang" value="" required data-tipe="decimal"></td>
                                                                            <td class=""><input class="form-control text-right" type="text" name="jml" placeholder="jumlah bangunan kandang" value="" data-tipe="integer" required></td>
                                                                            <td class="text-center">
                                                                                <button type="button" class="btn btn-danger" onclick="ptk.removeRowTable(this)"><i class="fa fa-minus"></i></button>
                                                                                <button type="button" class="btn btn-default" onclick="ptk.addRowTable(this)"><i class="fa fa-plus"></i></button>
                                                                            </td>
                                                                        </tr>
                                                                    </tbody>
                                                                </table>
                                                            </div>

                                                            <div class="row col-sm-12">
                                                                <table class="table table-bordered tpanel lampiran-kandang">
                                                                    <thead>
                                                                        <tr>
                                                                            <th colspan="3">Lampiran <span class="cursor-p pull-right" onclick="ptk.collapseLampiran(this)"><i class="glyphicon glyphicon-chevron-up"></i></span>  </th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody class="tpanel-body" hidden="">
                                                                        <?php foreach ($list_lampiran_kandang as $lkandang): ?>
                                                                            <tr data-idnama="<?php echo $lkandang['id'] ?>">
                                                                                <td><?php echo $lkandang['sequence'] ?></td>
                                                                                <td class=""><?php echo $lkandang['nama'] ?></td>
                                                                                <td class="col-sm-5 lampiran">
                                                                                    <label class="">
                                                                                        <input type="file" onchange="showNameFile(this)" class="file_lampiran" data-required="<?php echo $lkandang['required'] ?>" name="" placeholder="lampiran kandang - <?php echo $lkandang['nama'] ?>" data-allowtypes="doc|pdf|docx" style="display: none;">
                                                                                        <i class="glyphicon glyphicon-paperclip cursor-p"></i>
                                                                                    </label>
                                                                                </td>
                                                                            </tr>
                                                                        <?php endforeach; ?>
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </fieldset>
                                                    </div>
                                                </div>

                                            <div class="text-right" style="margin-bottom:12px;">
                                                <button type="button" class="btn btn-default" onclick="ptk.tambahKandangAfterApprove(this)">Tambah Kandang</button>
                                            </div>

                                        </div>
                                        <!-- end - data perwakilan -->

                                        </form>
                                        <div class="row">
                                            <div class="col-sm-12">
                                                <div class="pull-left">
                                                    <button type="button" class="btn btn-primary" onclick="ptk.simpanPerwakilanAfterApprove(this)">Simpan Perwakilan Baru</button>
                                                </div>
                                                <div class="pull-right">
                                                    <button type="button" class="btn btn-default" onclick="ptk.tambahPerwakilanAfterApprove(this)">Tambah Perwakilan</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- end - tab kandang -->


                                <!-- tab kandang after approve-->
                                <div id="kandang_after_approve">
                                </div>
                                <!-- end - tab kandang after approve-->

                                <!-- tab lampiran_mitra -->
                                <div class="tab-pane fade show active" id="lampiran_mitra" style="padding-top: 10px;">
                                    <form class="form form-horizontal">
                                        <div class="row">
                                            <div class="col-sm-12">
                                                <table class="table table-bordered lampiran-mitra">
                                                    <thead>
                                                        <tr>
                                                            <th colspan="3">Mitra</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($mitra['lampirans'] as $lmitra): ?>
                                                            <?php echo $lmitra['d_nama_mitra'] ?>
                                                            <tr data-idnama="<?php echo $lmitra['d_nama_lampiran']['id'] ?>">
                                                                <td><?php echo $lmitra['d_nama_lampiran']['sequence'] ?></td>
                                                                <td class=""><?php echo $lmitra['d_nama_lampiran']['nama'] ?></td>
                                                                <td class="col-sm-5">
                                                                    <label class="">
                                                                        <a href="uploads/<?php echo $lmitra['path'] ?>" target="_blank"><?php echo $lmitra['filename'] ?></a>
                                                                    </label>
                                                                </td>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <!-- end - tab lampiran_mitra -->

                                <!-- tab lampiran_jaminan_mitra -->
                                <div class="tab-pane fade" id="lampiran_jaminan_mitra" style="padding-top: 10px;">
                                    <form class="form form-horizontal">
                                        <div class="row">
                                            <div class="col-sm-12">
                                                <table class="table table-bordered lampiran-mitra">
                                                    <thead>
                                                        <tr>
                                                            <th colspan="3">Jaminan</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="not-reset">
                                                        <?php foreach ($mitra['lampirans_jaminan'] as $lmitra): ?>
                                                            <?php echo $lmitra['d_nama_mitra'] ?>
                                                            <tr data-idnama="<?php echo $lmitra['d_nama_lampiran']['id'] ?>">
                                                                <td><?php echo $lmitra['d_nama_lampiran']['sequence'] ?></td>
                                                                <td class=""><?php echo $lmitra['d_nama_lampiran']['nama'] ?></td>
                                                                <td class="col-sm-5">
                                                                    <label class="">
                                                                        <a href="uploads/<?php echo $lmitra['path'] ?>" target="_blank"><?php echo $lmitra['filename'] ?></a>
                                                                    </label>
                                                                </td>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                    <tbody class="reset hide" name="reset">
                                                        <?php foreach ($list_lampiran_jaminan as $ljaminan): ?>
                                                            <tr data-idnama="<?php echo $ljaminan['id'] ?>">
                                                                <td><?php echo $ljaminan['sequence'] ?></td>
                                                                <td class=""><?php echo $ljaminan['nama'] ?></td>
                                                                <td class="col-sm-5">
                                                                    <?php 
                                                                        $filename = null;
                                                                        foreach ( $mitra['lampirans_jaminan'] as $lmitr ) {
                                                                            if ( $lmitra['d_nama_lampiran']['id'] == $ljaminan['id'] ){ ?>
                                                                                <span>
                                                                                    <u>
                                                                                        <?php 
                                                                                            $filename = $lmitra['path'];
                                                                                            echo $lmitra['filename'];
                                                                                        ?>
                                                                                    </u>
                                                                                </span>
                                                                            <?php }
                                                                        }
                                                                    ?>
                                                                    <label class="">
                                                                        <input type="file" onchange="showNameFile(this)" class="file_lampiran" data-required="<?php echo $ljaminan['required'] ?>" name="" placeholder="dokumen jaminan <?php echo $ljaminan['nama'] ?>" data-allowtypes="doc|pdf|docx" style="display: none;" data-filename="<?php echo $filename; ?>" data-old="<?php echo $filename; ?>">
                                                                        <i class="glyphicon glyphicon-paperclip cursor-p"></i>
                                                                    </label>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <?php 
                                            $hide = 'hide';
                                            if ( strtoupper($mitra->status) == 'APPROVE' && $akses['a_submit'] == 1 ) {
                                                $hide = null;
                                            }
                                        ?>
                                        <div class="row <?php echo $hide; ?>">
                                            <div class="col-sm-12 reset">
                                                <button type="button" class="btn btn-default" onclick="ptk.resetJaminan(this)">Reset Jaminan</button>
                                            </div>
                                            <div class="col-sm-12 cancel hide">
                                                <button type="button" class="btn btn-danger" onclick="ptk.batalResetJaminan(this)">Batal Reset</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <!-- end - tab lampiran_mitra -->
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
