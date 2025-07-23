<div class="col-xs-12 no-padding">
	<div class="col-xs-6 no-padding" style="padding-right: 5px;">
		<div class="col-xs-2 no-padding"><label class="label-control">Tipe CN</label></div>
		<div class="col-xs-10 no-padding">
			<label class="label-control">: <?php echo $data['nama_jurnal_trans']; ?></label>
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
	<div class="col-xs-6 no-padding" style="padding-right: 5px;">
		<div class="col-xs-2 no-padding"><label class="label-control">Jenis CN</label></div>
		<div class="col-xs-10 no-padding">
			<label class="label-control">: <?php echo $jenis_cn[$data['jenis_cn']]; ?></label>
		</div>
	</div>
	<div class="col-xs-6 no-padding text-right" style="padding-left: 5px;">
		<div class="col-xs-2 no-padding"><label class="label-control">Total CN</label></div>
		<div class="col-xs-10 no-padding">
			<label class="label-control">: <?php echo angkaDecimal($data['tot_cn']); ?></label>
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
	<div class="col-xs-6 no-padding">
		<div class="col-xs-2 no-padding"><label class="label-control">No. CN</label></div>
		<div class="col-xs-10 no-padding">
			<label class="label-control">: <?php echo $data['nomor']; ?></label>
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
	<div class="col-xs-6 no-padding">
		<div class="col-xs-2 no-padding"><label class="label-control">Tanggal CN</label></div>
		<div class="col-xs-10 no-padding">
			<label class="label-control">: <?php echo tglIndonesia($data['tanggal'], '-', ' '); ?></label>
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
	<div class="col-xs-6 no-padding">
		<div class="col-xs-2 no-padding"><label class="label-control">Pelanggan</label></div>
		<div class="col-xs-10 no-padding">
			<label class="label-control">: <?php echo !empty($data['pelanggan']) ? strtoupper($data['nama_pelanggan']) : '-'; ?></label>
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
	<div class="col-xs-6 no-padding">
		<div class="col-xs-2 no-padding"><label class="label-control">Plasma</label></div>
		<div class="col-xs-10 no-padding">
			<label class="label-control">: <?php echo !empty($data['mitra']) ? strtoupper($data['nama_mitra']) : '-'; ?></label>
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
	<div class="col-xs-6 no-padding">
		<div class="col-xs-2 no-padding"><label class="label-control">Keterangan CN</label></div>
		<div class="col-xs-10 no-padding">
			<label class="label-control">: <?php echo strtoupper($data['ket_cn']); ?></label>
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding" style="overflow-x: auto;">
	<small>
		<table class="table table-bordered" style="margin-bottom: 0px; max-width: 100%; width: 100%;">
			<thead>
				<tr>
					<th class="col-xs-2">No. SJ</th>
					<th class="col-xs-2">Barang</th>
					<th class="col-xs-1">Jumlah</th>
					<th class="col-xs-5">Keterangan</th>
					<th class="col-xs-2">Nominal</th>
				</tr>
			</thead>
			<tbody>
                <?php if ( isset($data['detail']) && !empty($data['detail']) ) { ?>
                    <?php foreach ($data['detail'] as $k_det => $v_det) { ?>
                        <tr class="head">
							<td>
								<?php echo !empty($v_det['no_sj']) ? strtoupper(tglIndonesia($v_det['tgl_sj'], '-', ' ').' | '.$v_det['no_sj']) : '-'; ?>
							</td>
                            <td>
                                <?php echo !empty($v_det['nama_brg']) ? strtoupper($v_det['nama_brg']) : '-'; ?>
                            </td>
							<td class="text-right">
                                <?php echo angkaDecimal($v_det['jumlah']); ?>
                            </td>
                            <td>
                                <?php echo strtoupper($v_det['ket']); ?>
                            </td>
                            <td class="text-right">
                                <?php echo angkaDecimal($v_det['nominal']); ?>
                            </td>
                        </tr>
						<tr class="detail">
							<td colspan="5" style="background-color: #dedede;">
								<table class="table table-bordered" style="margin-bottom: 0px;">
									<thead>
										<tr>
											<td class="col-xs-6"><b>Akun</b></td>
											<td class="col-xs-3"><b>Kredit</b></td>
											<td class="col-xs-3"><b>Debet</b></td>
										</tr>
									</thead>
									<tbody>
										<?php foreach ($v_det['det_jurnal_trans'] as $k_djt => $v_djt) { ?>
											<tr>
												<td><?php echo strtoupper( $v_djt['nama_det_jurnal_trans'] ); ?></td>
												<td><?php echo strtoupper( $v_djt['coa_asal'].' | '.$v_djt['asal'] ); ?></td>
												<td><?php echo strtoupper( $v_djt['coa_tujuan'].' | '.$v_djt['tujuan'] ); ?></td>
											</tr>
										<?php } ?>
									</tbody>
								</table>
							</td>
						</tr>
                    <?php } ?>
                <?php } else { ?>
                    <tr>
                        <td colspan="4">Data tidak ditemukan.</td>
                    </tr>
                <?php } ?>
			</tbody>
		</table>
	</small>
</div>
<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
<div class="col-xs-12 no-padding">
    <div class="col-xs-6 no-padding" style="padding-right: 5px;">
        <button type="button" class="col-xs-12 btn btn-danger" onclick="cn.delete(this)" data-id="<?php echo $data['id']; ?>"><i class="fa fa-trash"></i> Hapus</button>
    </div>
    <div class="col-xs-6 no-padding" style="padding-left: 5px;">
        <button type="button" class="col-xs-12 btn btn-primary" onclick="cn.changeTabActive(this)" data-id="<?php echo $data['id']; ?>" data-edit="edit" data-href="action"><i class="fa fa-edit"></i> Edit</button>
    </div>
</div>