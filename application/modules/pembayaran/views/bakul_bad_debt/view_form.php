<div class="col-lg-12 no-padding">
	<div class="col-lg-2 no-padding"><label class="control-label text-left">Tgl Pengakuan Bad Debt</label></div>
	<div class="col-lg-1 no-padding" style="max-width: 2%;"><label class="control-label">:</label></div>
	<div class="col-lg-2 no-padding"><label class="control-label"><?php echo tglIndonesia($data['tgl_bayar'], '-', ' ', true); ?></label></div>
</div>
<div class="col-lg-12"></div>
<div class="col-lg-12 no-padding" style="height: 34px;">
	<div class="col-lg-2 no-padding"><label class="control-label text-left">Pelanggan</label></div>
	<div class="col-lg-1 no-padding" style="max-width: 2%;"><label class="control-label">:</label></div>
	<div class="col-lg-6 text-left" style="padding: 0px 30px 0px 0px;">
		<label class="control-label"><?php echo strtoupper($data['pelanggan']['nama']).' ('.strtoupper(str_replace('Kab ', '', $data['pelanggan']['kecamatan']['d_kota']['nama'])).')'; ?></label>
	</div>
</div>
<div class="col-lg-12"></div>
<div class="col-lg-12 no-padding" style="height: 34px;">
	<div class="col-lg-2 no-padding"><label class="control-label text-left">Perusahaan</label></div>
	<div class="col-lg-1 no-padding" style="max-width: 2%;"><label class="control-label">:</label></div>
	<div class="col-lg-6 text-left" style="padding: 0px 30px 0px 0px;">
		<label class="control-label"><?php echo strtoupper($data['perusahaan']); ?></label>
	</div>
</div>
<div class="col-lg-12 hide"></div>
<div class="col-lg-12 no-padding hide">
	<div class="col-lg-2 no-padding"><label class="control-label text-left">Jumlah Transfer</label></div>
	<div class="col-lg-1 no-padding" style="max-width: 2%;"><label class="control-label">:</label></div>
	<div class="col-lg-2 no-padding"><label class="control-label"><?php echo angkaDecimal($data['jml_transfer']); ?></label></div>
	<div class="col-lg-1" style="padding: 0px 30px 0px 0px;">&nbsp;</div>
	<div class="col-lg-2 no-padding"><label class="control-label text-left">Bukti Transfer</label></div>
	<div class="col-lg-1 no-padding" style="max-width: 2%;"><label class="control-label">:</label></div>
	<div class="col-lg-3 no-padding"><a href="uploads/<?php echo $data['lampiran_transfer']; ?>" target="_blank" class="cursor-p"><label class="control-label"><?php echo $data['lampiran_transfer']; ?></label></a></div>
</div>
<div class="col-lg-12 no-padding hide">&nbsp;</div>
<div class="col-lg-12 no-padding hide">
	<div class="col-lg-2 no-padding"><label class="control-label text-left">Saldo</label></div>
	<div class="col-lg-1 no-padding" style="max-width: 2%;"><label class="control-label">:</label></div>
	<div class="col-lg-2 no-padding"><label class="control-label"><?php echo angkaDecimal($data['saldo']); ?></label></div>
	<div class="col-lg-1" style="padding: 0px 30px 0px 0px;">&nbsp;</div>
	<div class="col-lg-2 no-padding"><label class="control-label text-left">Total Uang</label></div>
	<div class="col-lg-1 no-padding" style="max-width: 2%;"><label class="control-label">:</label></div>
	<div class="col-lg-2 no-padding"><label class="control-label"><?php echo angkaDecimal($data['total_uang']); ?></label></div>
</div>
<div class="col-lg-12 hide"></div>
<div class="col-lg-12 no-padding hide">
	<div class="col-lg-2 no-padding"><label class="control-label text-left">Total Penyesuaian</label></div>
	<div class="col-lg-1 no-padding" style="max-width: 2%;"><label class="control-label">:</label></div>
	<div class="col-lg-2 no-padding"><label class="control-label"><?php echo angkaDecimal($data['total_penyesuaian']); ?></label></div>
</div>
<div class="col-lg-12 hide"></div>
<div class="col-lg-12 no-padding hide">
	<div class="col-lg-2 no-padding"><label class="control-label text-left">Nilai Pajak</label></div>
	<div class="col-lg-1 no-padding" style="max-width: 2%;"><label class="control-label">:</label></div>
	<div class="col-lg-2 no-padding"><label class="control-label"><?php echo angkaDecimal($data['nil_pajak']); ?></label></div>
</div>
<div class="col-lg-12"></div>
<div class="col-lg-12 no-padding">
	<div class="col-lg-2 no-padding"><label class="control-label text-left">Jumlah Tagihan</label></div>
	<div class="col-lg-1 no-padding" style="max-width: 2%;"><label class="control-label">:</label></div>
	<div class="col-lg-2 no-padding"><label class="control-label"><?php echo angkaDecimal($data['total_bayar']); ?></label></div>
	<div class="col-lg-1 hide" style="padding: 0px 30px 0px 0px;">&nbsp;</div>
	<div class="col-lg-2 hide no-padding"><label class="control-label text-left">Lebih / Kurang</label></div>
	<div class="col-lg-1 hide no-padding" style="max-width: 2%;"><label class="control-label">:</label></div>
	<div class="col-lg-2 hide no-padding"><label class="control-label"><?php echo ($data['lebih_kurang'] < 0) ? '('.angkaDecimal(abs($data['lebih_kurang'])).')' : angkaDecimal($data['lebih_kurang']); ?></label></div>
</div>
<div class="col-lg-12 no-padding"><hr></div>
<div class="col-lg-12 no-padding">
	<small>
		<table class="table table-bordered tbl_list_do" style="margin-bottom: 0px;">
			<thead>
				<tr>
					<th class="col-lg-1 text-center">Tanggal Panen</th>
					<th class="col-lg-1 text-center">Plasma</th>
					<th class="col-lg-1 text-center">No. DO</th>
					<th class="col-lg-1 text-center">No. SJ</th>
					<th class="text-center" style="width: 5%;">Ekor</th>
					<th class="text-center" style="width: 5%;">Kg</th>
					<th class="text-center" style="width: 7%;">Harga</th>
					<th class="col-lg-1 text-center">Total</th>
					<th class="col-lg-1 text-center">Sudah Bayar</th>
					<th class="col-lg-1 text-center">Jumlah Bayar</th>
					<th class="col-lg-1 text-center">Penyesuaian</th>
					<th class="text-center" style="width: 5%;">Status</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($data['detail'] as $k_det => $v_det): ?>
					<tr class="data" data-id="<?php echo $v_det['id_do']; ?>">
						<td class="text-center"><?php echo tglIndonesia($v_det['data_do']['header']['tgl_panen'], '-', ' '); ?></td>
						<td class="text-left"><?php echo strtoupper($v_det['nama']).'<br>'.'KDG : '.$v_det['kandang']; ?></td>
						<td class="text-center"><?php echo $v_det['data_do']['no_do']; ?></td>
						<td class="text-center"><?php echo $v_det['data_do']['no_sj']; ?></td>
						<td class="text-right"><?php echo angkaRibuan($v_det['data_do']['ekor']); ?></td>
						<td class="text-right"><?php echo angkaDecimal($v_det['data_do']['tonase']); ?></td>
						<td class="text-right"><?php echo angkaDecimal($v_det['data_do']['harga']); ?></td>
						<td class="text-right total">
							<?php
								$total = $v_det['data_do']['tonase'] * $v_det['data_do']['harga'];
								echo angkaDecimal($total);
							?>
						</td>
						<td class="text-right"><?php echo angkaDecimal($v_det['sudah_bayar']); ?></td>
						<td class="text-right jml_bayar" data-sudah="<?php echo $v_det['sudah_bayar']; ?>" data-bayar="<?php echo $v_det['jumlah_bayar']; ?>"><?php echo angkaDecimal($v_det['jumlah_bayar']); ?></td>
						<td class="penyesuaian">
							<div class="col-lg-12 text-right no-padding">
								<?php echo angkaRibuan($v_det['penyesuaian']); ?>
							</div>
							<div class="col-lg-12 no-padding">
								<hr style="margin-top: 5px; margin-bottom: 5px;">
							</div>
							<div class="col-lg-12 pull-left no-padding">
								<?php echo (!empty($v_det['ket_penyesuaian']) && trim($v_det['ket_penyesuaian']) != "") ? $v_det['ket_penyesuaian'] : '-'; ?>
							</div>
						</td>
						<td class="text-center status">
							<?php
								$ket = '';
								$total_bayar = ($v_det['jumlah_bayar']+$v_det['sudah_bayar']) + $v_det['penyesuaian'];
								if ( $total <= $total_bayar ) {
									$ket = '<span style="color: blue;"><b>LUNAS</b></span>';
								} else if ( $total > $v_det['sudah_bayar'] ) {
									$ket = '<span style="color: red;"><b>BELUM</b></span>';
								}

								echo $ket;
							?>
						</td>
					</tr>
				<?php endforeach ?>
			</tbody>
		</table>
	</small>
</div>
<div class="col-lg-12 no-padding"><hr></div>
<div class="col-lg-6 no-padding">
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
</div>
<div class="col-lg-6 no-padding">
	<?php if ( $data['edit'] == 1 ): ?>
		<!-- <?php if ( $akses['a_edit'] == 1 ): ?>
			<button type="button" class="btn btn-primary pull-right" onclick="bakul.changeTabActive(this)" data-href="action" data-id="<?php echo $data['id']; ?>" data-resubmit="resubmit"><i class="fa fa-edit"></i> Update</button>
		<?php endif ?> -->
		<?php if ( $akses['a_delete'] == 1 ): ?>
			<button type="button" class="btn btn-danger pull-right" onclick="bakul.delete(this)" data-id="<?php echo $data['id']; ?>" style="margin-right: 10px;"><i class="fa fa-trash"></i> Hapus</button>
		<?php endif ?>
	<?php endif ?>
</div>