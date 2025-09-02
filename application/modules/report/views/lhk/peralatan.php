<div class="modal-header no-padding">
	<span class="modal-title"><b>Peralatan</b></span>
	<button type="button" class="close" data-dismiss="modal">&times;</button>
</div>
<div class="modal-body">
    <div class="row detailed">
		<div class="col-lg-12 no-padding detailed">
        	<small>
				<table class="table table-bordered" style="margin-bottom: 0px;">
					<thead>
                        <tr>
                            <th class="col-xs-6">Keterangan</th>
                            <th class="col-xs-3">Controller</th>
                            <th class="col-xs-3">Kessler</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $d_lp = (!empty($data)) ? $data : null; ?>
                        <tr>
                            <td>Umur</td>
                            <td class="umur text-center" colspan="2"><?php echo !empty($d_lp) ? $d_lp['umur'] : '-'; ?></td>
                        </tr>
                        <tr>
                            <td>Waktu Cek</td>
                            <td class="waktu text-center" colspan="2">
                                <?php
                                    $ket = '-';
                                    if ( !empty($d_lp) ) {
                                        $date = substr((string) $d_lp['waktu'], 0, 10);
                                        $day = explode('-', $date)[2];
                                        $month = explode('-', $date)[1];
                                        $year = explode('-', $date)[0];
                                        $time = substr((string) $d_lp['waktu'], 11, 5);

                                        $hari = tglKeHari( $date );

                                        $ket = $hari.', '.$day.'/'.$month.'/'.$year.' '.$time;
                                    }

                                    echo strtoupper($ket);
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <td>Flok / Lantai</td>
                            <td class="text-center" colspan="2"><?php echo !empty($d_lp) ? $d_lp['flok_lantai'] : '-'; ?></td>
                        </tr>
                        <tr>
                            <td>Tipe Controller</td>
                            <td class="text-center" colspan="2"><?php echo !empty($d_lp) ? $d_lp['tipe_controller'] : '-'; ?></td>
                        </tr>
                        <tr>
                            <td>Kelembapan (%)</td>
                            <td class="text-right"><?php echo !empty($d_lp) ? angkaDecimal($d_lp['kelembapan1']) : 0; ?></td>
                            <td class="text-right"><?php echo !empty($d_lp) ? angkaDecimal($d_lp['kelembapan2']) : 0; ?></td>
                        </tr>
                        <tr>
                            <td>Suhu Current &#8451;</td>
                            <td class="text-right"><?php echo !empty($d_lp) ? angkaDecimal($d_lp['suhu_current1']) : 0; ?></td>
                            <td class="text-right"><?php echo !empty($d_lp) ? angkaDecimal($d_lp['suhu_current2']) : 0; ?></td>
                        </tr>
                        <tr>
                            <td>Suhu Experience &#8451;</td>
                            <td class="text-right <?php echo !empty($d_lp) ? (($d_lp['stts_suhu_experience1'] == 0) ? 'red' : null) : 0; ?>"><?php echo !empty($d_lp) ? angkaDecimal($d_lp['suhu_experience1']) : 0; ?></td>
                            <td class="text-right <?php echo !empty($d_lp) ? (($d_lp['stts_suhu_experience2'] == 0) ? 'red' : null) : 0; ?>"><?php echo !empty($d_lp) ? angkaDecimal($d_lp['suhu_experience2']) : 0; ?></td>
                        </tr>
                        <tr>
                            <td>Air Speed Depan Inlet</td>
                            <td class="text-right <?php echo !empty($d_lp) ? (($d_lp['air_speed_depan_inlet1'] == 0) ? 'red' : null) : 0; ?>"><?php echo !empty($d_lp) ? angkaDecimal($d_lp['air_speed_depan_inlet1']) : 0; ?></td>
                            <td class="text-right <?php echo !empty($d_lp) ? (($d_lp['air_speed_depan_inlet2'] == 0) ? 'red' : null) : 0; ?>"><?php echo !empty($d_lp) ? angkaDecimal($d_lp['air_speed_depan_inlet2']) : 0; ?></td>
                        </tr>
                        <tr>
                            <td>Kerataan Air Speed</td>
                            <td class="text-right <?php echo !empty($d_lp) ? (($d_lp['stts_kerataan_air_speed1'] == 0) ? 'red' : null) : 0; ?>"><?php echo !empty($d_lp) ? angkaDecimal($d_lp['kerataan_air_speed1']) : 0; ?></td>
                            <td class="text-right <?php echo !empty($d_lp) ? (($d_lp['stts_kerataan_air_speed2'] == 0) ? 'red' : null) : 0; ?>"><?php echo !empty($d_lp) ? angkaDecimal($d_lp['kerataan_air_speed2']) : 0; ?></td>
                        </tr>
                        <tr>
                            <td>Ukuran Kipas</td>
                            <td class="text-right"><?php echo !empty($d_lp) ? angkaDecimal($d_lp['ukuran_kipas1']) : 0; ?></td>
                            <td class="text-right"><?php echo !empty($d_lp) ? angkaDecimal($d_lp['ukuran_kipas2']) : 0; ?></td>
                        </tr>
                        <tr>
                            <td>Jumlah Kipas Total</td>
                            <td class="text-right"><?php echo !empty($d_lp) ? angkaRibuan($d_lp['jumlah_kipas1']) : 0; ?></td>
                            <td class="text-right"><?php echo !empty($d_lp) ? angkaRibuan($d_lp['jumlah_kipas2']) : 0; ?></td>
                        </tr>
                        <tr>
                            <td>Jumlah Kipas On</td>
                            <td class="text-right"><?php echo !empty($d_lp) ? angkaRibuan($d_lp['jumlah_kipas_on1']) : 0; ?></td>
                            <td class="text-right"><?php echo !empty($d_lp) ? angkaRibuan($d_lp['jumlah_kipas_on2']) : 0; ?></td>
                        </tr>
                        <tr>
                            <td>Jumlah Kipas Off</td>
                            <td class="text-right"><?php echo !empty($d_lp) ? angkaRibuan($d_lp['jumlah_kipas_off1']) : 0; ?></td>
                            <td class="text-right"><?php echo !empty($d_lp) ? angkaRibuan($d_lp['jumlah_kipas_off2']) : 0; ?></td>
                        </tr>
                        <tr>
                            <td>Waktu Kipas On (menit)</td>
                            <td class="text-right"><?php echo !empty($d_lp) ? angkaRibuan($d_lp['waktu_kipas_on1']) : 0; ?></td>
                            <td class="text-right"><?php echo !empty($d_lp) ? angkaRibuan($d_lp['waktu_kipas_on2']) : 0; ?></td>
                        </tr>
                        <tr>
                            <td>Waktu Kipas Off (menit)</td>
                            <td class="text-right"><?php echo !empty($d_lp) ? angkaRibuan($d_lp['waktu_kipas_off1']) : 0; ?></td>
                            <td class="text-right"><?php echo !empty($d_lp) ? angkaRibuan($d_lp['waktu_kipas_off2']) : 0; ?></td>
                        </tr>
                        <tr class="cooling_pad_status">
                            <td>Cooling Pad Status</td>
                            <td class="text-center"><?php echo !empty($d_lp) ? ( ($d_lp['cooling_pad_status1'] == 1) ? 'ON' : 'OFF' ) : '-'; ?></td>
                            <td class="text-center"><?php echo !empty($d_lp) ? ( ($d_lp['cooling_pad_status2'] == 1) ? 'ON' : 'OFF' ) : '-'; ?></td>
                        </tr>
                    </tbody>
				</table>
			</small>
    	</div>
    </div>
</div>