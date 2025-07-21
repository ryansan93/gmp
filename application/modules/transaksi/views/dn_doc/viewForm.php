<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
	<div class="col-xs-12 no-padding" style="padding-right: 5px;">
		<div class="col-xs-2 no-padding"><label class="label-control">Transaksi Jurnal</label></div>
		<div class="col-xs-10 no-padding">
			<label class="label-control">: <?php echo $data['nama_jurnal_trans']; ?></label>
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
	<div class="col-xs-12 no-padding" style="padding-right: 5px;">
		<div class="col-xs-2 no-padding"><label class="label-control">Transaksi</label></div>
		<div class="col-xs-10 no-padding">
			<label class="label-control">: <?php echo $data['kode_det_jurnal_trans'].' | '.$data['nama_det_jurnal_trans']; ?></label>
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
	<div class="col-xs-12 no-padding" style="padding-right: 5px;">
		<div class="col-xs-2 no-padding"><label class="label-control">Asal</label></div>
		<div class="col-xs-10 no-padding">
			<label class="label-control">: <?php echo $data['coa_asal'].' | '.$data['asal']; ?></label>
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding">
	<div class="col-xs-12 no-padding" style="padding-right: 5px;">
		<div class="col-xs-2 no-padding"><label class="label-control">Tujuan</label></div>
		<div class="col-xs-10 no-padding">
			<label class="label-control">: <?php echo $data['coa_tujuan'].' | '.$data['tujuan']; ?></label>
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
	<div class="col-xs-12 no-padding" style="padding-right: 5px;">
		<div class="col-xs-2 no-padding"><label class="label-control">No. DN</label></div>
		<div class="col-xs-10 no-padding">
			<label class="label-control">: <?php echo $data['nomor']; ?></label>
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
	<div class="col-xs-12 no-padding" style="padding-right: 5px;">
		<div class="col-xs-2 no-padding"><label class="label-control">Tanggal DN</label></div>
		<div class="col-xs-10 no-padding">
			<label class="label-control">: <?php echo tglIndonesia($data['tanggal'], '-', ' '); ?></label>
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
	<div class="col-xs-12 no-padding" style="padding-right: 5px;">
		<div class="col-xs-2 no-padding"><label class="label-control">Supplier</label></div>
		<div class="col-xs-10 no-padding">
			<label class="label-control">: <?php echo strtoupper($data['nama_supplier']); ?></label>
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
	<div class="col-xs-12 no-padding" style="padding-right: 5px;">
		<div class="col-xs-2 no-padding"><label class="label-control">Keterangan DN</label></div>
		<div class="col-xs-10 no-padding">
			<label class="label-control">: <?php echo strtoupper($data['ket_dn']); ?></label>
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
	<div class="col-xs-12 no-padding" style="padding-right: 5px;">
		<div class="col-xs-2 no-padding"><label class="label-control">Total DN</label></div>
		<div class="col-xs-10 no-padding">
			<label class="label-control">: <?php echo angkaDecimal($data['tot_dn']); ?></label>
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding" style="overflow-x: auto;">
	<small>
		<table class="table table-bordered" style="margin-bottom: 0px; max-width: 100%; width: 100%;">
			<thead>
				<tr>
					<th class="col-xs-2">Tgl SJ</th>
					<th class="col-xs-2">No. SJ</th>
					<th class="col-xs-6">Keterangan</th>
					<th class="col-xs-2">Nominal</th>
				</tr>
			</thead>
			<tbody>
                <?php if ( isset($data['detail']) && !empty($data['detail']) ) { ?>
                    <?php foreach ($data['detail'] as $k_det => $v_det) { ?>
                        <tr>
                            <td>
                                <?php echo tglIndonesia($v_det['tgl_sj'], '-', ' '); ?>
                            </td>
                            <td>
                                <?php echo strtoupper($v_det['no_sj']); ?>
                            </td>
                            <td>
                                <?php echo strtoupper($v_det['ket']); ?>
                            </td>
                            <td class="text-right">
                                <?php echo angkaDecimal($v_det['nominal']); ?>
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
        <button type="button" class="col-xs-12 btn btn-danger" onclick="dn.delete(this)" data-id="<?php echo $data['id']; ?>"><i class="fa fa-trash"></i> Hapus</button>
    </div>
    <div class="col-xs-6 no-padding" style="padding-left: 5px;">
        <button type="button" class="col-xs-12 btn btn-primary" onclick="dn.changeTabActive(this)" data-id="<?php echo $data['id']; ?>" data-edit="edit" data-href="action"><i class="fa fa-edit"></i> Edit</button>
    </div>
</div>