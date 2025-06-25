<div class="row content-panel detailed">
	<div class="col-xs-12 no-padding detailed">
		<form role="form" class="form-horizontal">
			<div class="col-xs-12">
                <div class="col-xs-12 no-padding">
                    <small>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th class="col-xs-1">Umur</th>
                                    <th class="col-xs-10">Plasma</th>
                                    <th class="col-xs-1 text-center">
                                        <input type="checkbox" class="cursor-p check_all" data-target="lhk" >
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ( !empty($data) ) { ?>
                                    <?php foreach ($data as $key => $value) { ?>
                                        <tr class="header cursor-p">
                                            <td class="text-center"><?php echo strtoupper($value['umur']); ?></td>
                                            <td><?php echo strtoupper($value['nama_mitra'].' (KDG:'.$value['no_kdg'].')'); ?></td>
                                            <!-- <td class="text-center"><?php echo strtoupper($value['no_kdg']); ?></td> -->
                                            <td class="text-center non_click">
                                                <input type="checkbox" class="cursor-p check" target="lhk" data-id="<?php echo $value['id']; ?>">
                                            </td>
                                        </tr>
                                        <tr class="detail" style="display: none;">
                                            <td colspan="3" style="background-color: #ededed;">
                                                <div class="col-xs-12 no-padding" style="border: 1px solid #ddd; border-radius: 5px; background-color: #fff;">
                                                    <div class="col-xs-12" style="padding-top: 10px; padding-bottom: 10px;">
                                                        <div class="col-xs-12 no-padding">
                                                            <div class="col-xs-2 no-padding">PPL</div>
                                                            <div class="col-xs-1 no-padding text-center">:</div>
                                                            <div class="col-xs-7 no-padding"><?php echo strtoupper($value['nama_karyawan']); ?></div>
                                                            <div class="col-xs-2 no-padding">
                                                                <?php
                                                                    // $url = null;
                                                                    // if ( $isMobile ) {
                                                                    //     $url = 'geo:0, 0?z=15&q='.preg_replace('/\s+/', '', $value['lat_long']).'/'.preg_replace('/\s+/', '', $value['lat_long_mitra']);
                                                                    // } else {
                                                                    // }

                                                                    $url = 'https://www.google.com/maps/dir/'.preg_replace('/\s+/', '', $value['lat_long']).'/'.preg_replace('/\s+/', '', $value['lat_long_mitra']);
                                                                ?>
                                                                <button type="button" class="col-xs-12 btn btn-default" style="padding: 0px; font-size: 8pt;" title="DATA POSISI" onclick="window.open('<?php echo $url; ?>', 'blank')"><i class="fa fa-map-marker"></i></button>
                                                            </div>
                                                        </div>
                                                        <div class="col-xs-12 no-padding">
                                                            <div class="col-xs-2 no-padding">Tanggal</div>
                                                            <div class="col-xs-1 no-padding text-center">:</div>
                                                            <div class="col-xs-9 no-padding"><?php echo strtoupper(tglIndonesia($value['tanggal'], '-', ' ')); ?></div>
                                                        </div>
                                                        <div class="col-xs-12" style="padding-right: 0px;">
                                                            <div class="col-xs-12 no-padding"><u><b>PEFORMA</b></u></div>
                                                            <div class="col-xs-12 no-padding" style="padding-left: 10px; padding-bottom: 5px;">
                                                                <div class="col-xs-5 no-padding">Konsumsi Pakan (Kg)</div>
                                                                <div class="col-xs-1 no-padding text-center">:</div>
                                                                <div class="col-xs-4 no-padding"><?php echo angkaRibuan($value['konsumsi_pakan']); ?></div>
                                                            </div>
                                                            <div class="col-xs-12 no-padding" style="padding-left: 10px; padding-bottom: 5px;">
                                                                <div class="col-xs-5 no-padding">ADG</div>
                                                                <div class="col-xs-1 no-padding text-center">:</div>
                                                                <div class="col-xs-4 no-padding"><?php echo angkaDecimalFormat($value['adg'], 3); ?></div>
                                                            </div>
                                                            <div class="col-xs-12 no-padding" style="padding-left: 10px; padding-bottom: 5px;">
                                                                <div class="col-xs-5 no-padding">Deplesi</div>
                                                                <div class="col-xs-1 no-padding text-center">:</div>
                                                                <div class="col-xs-4 no-padding">-</div>
                                                            </div>
                                                            <div class="col-xs-12 no-padding" style="padding-left: 10px; padding-bottom: 5px;">
                                                                <div class="col-xs-5 no-padding">BB</div>
                                                                <div class="col-xs-1 no-padding text-center">:</div>
                                                                <div class="col-xs-4 no-padding"><?php echo angkaDecimalFormat($value['bb'], 3); ?></div>
                                                                <div class="col-xs-2 no-padding">
                                                                    <button type="button" class="col-xs-12 btn btn-default" style="padding: 0px; font-size: 8pt;" title="DATA SEKAT" onclick="lhk.sekat(this)" data-id="<?php echo $value['id']; ?>"><i class="fa fa-list"></i></button>
                                                                </div>
                                                            </div>
                                                            <div class="col-xs-12 no-padding" style="padding-left: 10px; padding-bottom: 5px;">
                                                                <div class="col-xs-5 no-padding">FCR</div>
                                                                <div class="col-xs-1 no-padding text-center">:</div>
                                                                <div class="col-xs-4 no-padding"><?php echo angkaDecimalFormat($value['fcr'], 3); ?></div>
                                                            </div>
                                                            <div class="col-xs-12 no-padding" style="padding-left: 10px; padding-bottom: 5px;">
                                                                <div class="col-xs-5 no-padding">Mati (Ekor)</div>
                                                                <div class="col-xs-1 no-padding text-center">:</div>
                                                                <div class="col-xs-4 no-padding"><?php echo angkaRibuan($value['ekor_mati']); ?></div>
                                                                <div class="col-xs-2 no-padding">
                                                                    <button type="button" class="col-xs-12 btn btn-default" style="padding: 0px; font-size: 8pt;" title="FOTO KEMATIAN" onclick="lhk.preview_file_attachment(this)" data-url='<?php echo $value['json_kematian']; ?>' data-jenis="view" data-title="FOTO KEMATIAN"><i class="fa fa-camera"></i></button>
                                                                </div>
                                                            </div>
                                                            <div class="col-xs-12 no-padding" style="padding-left: 10px; padding-bottom: 5px;">
                                                                <div class="col-xs-5 no-padding">IP</div>
                                                                <div class="col-xs-1 no-padding text-center">:</div>
                                                                <div class="col-xs-4 no-padding"><?php echo angkaDecimalFormat($value['ip'], 3); ?></div>
                                                            </div>
                                                        </div>
                                                        <div class="col-xs-12" style="padding-right: 0px;">
                                                            <div class="col-xs-12 no-padding"><u><b>PAKAN</b></u></div>
                                                            <div class="col-xs-12 no-padding" style="padding-left: 10px; padding-bottom: 5px;">
                                                                <div class="col-xs-5 no-padding">Kirim (Zak)</div>
                                                                <div class="col-xs-1 no-padding text-center">:</div>
                                                                <div class="col-xs-4 no-padding"><?php echo angkaRibuan($value['terima_pakan'] / 50); ?></div>
                                                            </div>
                                                            <div class="col-xs-12 no-padding" style="padding-left: 10px; padding-bottom: 5px;">
                                                                <div class="col-xs-5 no-padding">Sisa (Zak)</div>
                                                                <div class="col-xs-1 no-padding text-center">:</div>
                                                                <div class="col-xs-4 no-padding"><?php echo angkaRibuan($value['sisa_pakan'] / 50); ?></div>
                                                                <div class="col-xs-2 no-padding">
                                                                    <button type="button" class="col-xs-12 btn btn-default" style="padding: 0px; font-size: 8pt;" title="FOTO SISA PAKAN" onclick="lhk.preview_file_attachment(this)" data-url='<?php echo $value['json_sisa_pakan']; ?>' data-jenis="view" data-title="FOTO SISA PAKAN"><i class="fa fa-camera"></i></button>
                                                                </div>
                                                            </div>
                                                            <div class="col-xs-12 no-padding" style="padding-left: 10px; padding-bottom: 5px;">
                                                                <div class="col-xs-5 no-padding">Pakai (Zak)</div>
                                                                <div class="col-xs-1 no-padding text-center">:</div>
                                                                <div class="col-xs-4 no-padding"><?php echo angkaRibuan($value['konsumsi_pakan_zak']); ?></div>
                                                            </div>
                                                        </div>
                                                        <div class="col-xs-12" style="padding-left: 10px; padding-bottom: 5px; padding-right: 0px;">
                                                            <div class="col-xs-10 no-padding"><u><b>NEKROPSI</b></u></div>
                                                            <div class="col-xs-2 no-padding">
                                                                <button type="button" class="col-xs-12 btn btn-default" style="padding: 0px; font-size: 8pt;" title="DATA NEKROPSI" onclick="lhk.nekropsi(this)" data-id="<?php echo $value['id']; ?>"><i class="fa fa-list"></i></button>
                                                            </div>
                                                        </div>
                                                        <div class="col-xs-12" style="padding-left: 10px; padding-bottom: 5px; padding-right: 0px;">
                                                            <div class="col-xs-10 no-padding"><u><b>SOLUSI</b></u></div>
                                                            <div class="col-xs-2 no-padding">
                                                                <button type="button" class="col-xs-12 btn btn-default" style="padding: 0px; font-size: 8pt;" title="DATA SOLUSI" onclick="lhk.solusi(this)" data-id="<?php echo $value['id']; ?>"><i class="fa fa-list"></i></button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                <?php } else { ?>
                                    <tr>
                                        <td colspan="3">Data tidak ditemukan.</td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </small>
                </div>
                <div class="col-xs-12 no-padding">
                    <button type="button" class="col-xs-12 btn btn-primary" onclick="lhk.ack()"><i class="fa fa-check"></i> ACK</button>
                </div>
            </div>
		</form>
	</div>
</div>