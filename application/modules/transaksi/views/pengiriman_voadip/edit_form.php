<div class="form-group d-flex align-items-center">
    <div class="col-lg-12 d-flex align-items-center no-padding filter">
        <div class="col-lg-2 text-left">Filter OP</div>
        <div class="col-lg-2">
            <select class="form-control unit">
                <option value="">-- Pilih Unit --</option>
                <?php if ( count($unit) > 0 ): ?>
                    <?php foreach ($unit as $k => $val): ?>
                        <?php 
                            $true = false;
                            if ( stristr($data_ov['no_order'], $val['kode']) !== FALSE ) { 
                                $true = true;
                            }
                        ?>
                        <option value="<?php echo $val['kode'] ?>" <?php echo ($true) ? 'selected' : null; ?> ><?php echo strtoupper($val['nama']); ?></option>
                    <?php endforeach ?>
                <?php endif ?>
            </select>
        </div>
        <div class="col-lg-2" style="padding-left: 0px;">
            <div class="input-group date datetimepicker" name="tgl_kirim_ov" id="tgl_kirim_ov">
                <input type="text" class="form-control text-center" placeholder="Tanggal Kirim" data-tgl="<?php echo $data_ov['tanggal']; ?>" />
                <span class="input-group-addon">
                    <span class="glyphicon glyphicon-calendar"></span>
                </span>
            </div>
        </div>
        <div class="col-lg-2" style="padding-left: 0px;">
            <button type="button" class="btn btn-primary get_sj_not_terima" onclick="pv.get_op_not_kirim(this)">Ambil SJ</button>
        </div>
    </div>
</div>
<hr style="margin-top: 10px; margin-bottom: 10px;">
<div class="form-group d-flex align-items-center">
	<div class="col-lg-12 d-flex align-items-center no-padding">
		<div class="col-lg-2 text-left">Jenis Pengiriman</div>
		<div class="col-lg-2">
			<select class="form-control jenis_kirim" data-required="1" onchange="pv.cek_jenis(this)">
				<option value="">-- Pilih Jenis --</option>
				<option value="opks" <?php echo ($data['jenis_kirim'] == 'opks') ? 'selected' : null; ?> >Order Pabrik (OPKS)</option>
				<option value="opkp" <?php echo ($data['jenis_kirim'] == 'opkp') ? 'selected' : null; ?> >Dari Peternak (OPKP)</option>
				<option value="opkg" <?php echo ($data['jenis_kirim'] == 'opkg') ? 'selected' : null; ?> >Dari Gudang (OPKG)</option>
			</select>
		</div>
		<div class="col-lg-2"></div>
		<div class="col-lg-2">Ongkos Angkut</div>
		<div class="col-lg-2">
			<input type="text" class="form-control text-right ongkos_angkut" placeholder="Ongkos Angkut" data-required="1" data-tipe="decimal" maxlength="14" value="<?php echo angkaDecimal($data['ongkos_angkut']); ?>">
		</div>
	</div>
</div>
<div class="form-group d-flex align-items-center">
	<div class="col-lg-12 d-flex align-items-center no-padding">
		<div class="col-lg-2 text-left">No. Order</div>
		<div class="col-lg-2">
			<?php
				$supplier = null;
				$id_supplier = null;

				$hide = 'hide';
				$data_required = null;
				if ( $data['jenis_kirim'] == 'opks' ) {
					$hide = null;
					$data_required = 'data-required=1';
				}
			?>
			<select class="form-control no_order <?php echo $hide; ?>" data-jenis="opks" <?php echo $data_required; ?> onchange="pv.get_asal(this)">
				<option value="">-- Pilih No. Order --</option>
				<option value="<?php echo $data['no_order']; ?>" data-supplier="<?php echo $data_ov['supl_nama']; ?>" data-idsupplier="<?php echo $data_ov['supl_nomor']; ?>" selected ><?php echo $data['no_order']; ?></option>
				<?php
					$supplier = $data_ov['supl_nama'];
					$id_supplier = $data_ov['supl_nomor'];
				?>
				<?php foreach ($order_voadip as $k_op => $v_op): ?>
					<?php 
						// $selected = null;
						// if ( $v_op['no_order'] == $data['no_order'] ) {
						// 	$selected = 'selected';

						// 	$supplier = $v_op['d_supplier']['nama'];
						// 	$id_supplier = $v_op['supplier'];
						// }
					?>
					<option value="<?php echo $v_op['no_order']; ?>" data-supplier="<?php echo $v_op['d_supplier']['nama']; ?>" data-idsupplier="<?php echo $v_op['supplier']; ?>" <?php // echo $selected; ?> ><?php echo $v_op['no_order']; ?></option>
				<?php endforeach ?>
			</select>
			<?php
				$hide = 'hide';
				$data_required = null;
				$readonly = 'readonly';
				$no_order = null;
				if ( $data['jenis_kirim'] != 'opks' ) {
					$hide = null;
					$data_required = 'data-required=1';
					$readonly = null;
					$no_order = $data['no_order'];
				}
			?>
			<input type="text" class="form-control no_order <?php echo $hide; ?>" data-jenis="non_opks" placeholder="No. Order" <?php echo $data_required; ?> <?php echo $readonly; ?> value="<?php echo $no_order; ?>" readonly >
		</div>
	</div>
</div>
<?php
	$hide = 'hide';
	if ( $data['jenis_kirim'] == 'opks' ) {
		$hide = null;
	}
?>
<div class="form-group align-items-center opks <?php echo $hide; ?>">
	<div class="col-lg-12 d-flex align-items-center no-padding">
		<div class="col-lg-2 text-left">Perusahaan</div>
		<div class="col-lg-2">
			<input type="text" class="form-control perusahaan" placeholder="Perusahaan" value="<?php echo $data_ov['nama_prs']; ?>" readonly>
		</div>
	</div>
</div>
<div class="form-group d-flex align-items-center">
	<div class="col-lg-12 d-flex align-items-center no-padding">
		<div class="col-lg-2">Asal</div>
		<?php
			$hide = 'hide';
			$data_required = null;
			if ( $data['jenis_kirim'] == 'opks' ) {
				$hide = null;
				$data_required = 'data-required=1';
			}
		?>
		<div class="col-lg-4 opks <?php echo $hide; ?>">
			<input type="text" class="form-control asal" placeholder="Asal" <?php echo $data_required; ?> value="<?php echo $supplier; ?>" data-id="<?php echo $id_supplier; ?>" readonly>
		</div>
		<?php
			$hide = 'hide';
			$data_required = null;
			if ( $data['jenis_kirim'] == 'opkp' ) {
				$hide = null;
				$data_required = 'data-required=1';
			}
		?>
		<div class="col-lg-4 opkp <?php echo $hide; ?>">
			<div class="col-lg-12 div_peternak no-padding">
				<div class="col-lg-3 no-padding">
					<input type="text" class="form-control text-center datetimepicker" placeholder="Bulan" name="bulan_docin" id="bulan_docin" onblur="pv.get_peternak(this)" data-tgl="<?php echo $tgl_docin_asal; ?>" />
		        </div>
		        <div class="col-lg-9">
					<select class="form-control peternak_asal" <?php echo $data_required; ?> data-noreg="<?php echo ($data['jenis_kirim'] == 'opkp') ? trim($data['asal']) : null; ?>" >
						<option value="">-- Pilih Peternak --</option>
						<!-- <?php foreach ($peternak as $k_peternak => $v_peternak): ?>
							<?php
								$selected = null;
								if ( $data['jenis_kirim'] == 'opkp' ) {
									if ( $data['asal'] == $v_peternak['noreg'] ) {
										$selected = 'selected';
									}
								}
							?>
							<option value="<?php echo $v_peternak['noreg']; ?>" <?php echo $selected; ?> ><?php echo strtoupper($v_peternak['kode_unit']).' | '.strtoupper($v_peternak['nama']).' ('.$v_peternak['noreg'].')'; ?></option>
						<?php endforeach ?> -->
					</select>
		        </div>
		    </div>
		</div>
		<?php
			$hide = 'hide';
			$data_required = null;
			if ( $data['jenis_kirim'] == 'opkg' ) {
				$hide = null;
				$data_required = 'data-required=1';
			}
		?>
		<div class="col-lg-4 opkg <?php echo $hide; ?>">
			<select class="form-control gudang_asal" <?php echo $data_required; ?> >
				<option value="">-- Pilih Gudang --</option>
				<?php foreach ($gudang_asal as $k_gudang => $v_gudang): ?>
					<?php
						$selected = null;
						if ( $data['jenis_kirim'] == 'opkg' ) {
							if ( $data['asal'] == $v_gudang['id'] ) {
								$selected = 'selected';
							}
						}
					?>
					<option value="<?php echo $v_gudang['id']; ?>" <?php echo $selected; ?> ><?php echo strtoupper($v_gudang['nama']); ?></option>
				<?php endforeach ?>
			</select>
		</div>
	</div>
</div>
<div class="form-group d-flex align-items-center">
	<div class="col-lg-12 d-flex align-items-center no-padding">
		<div class="col-lg-2">Tujuan</div>
		<div class="col-lg-2">
			<!-- <input type="text" class="form-control tujuan" placeholder="Tujuan" data-required="1" readonly> -->
			<select class="form-control tujuan" onchange="pv.cek_tujuan(this)">
				<option value="peternak" <?php echo ($data['jenis_tujuan'] == 'peternak') ? 'selected' : null; ?>>Peternak</option>
				<option value="gudang" <?php echo ($data['jenis_tujuan'] == 'gudang') ? 'selected' : null; ?>>Gudang</option>
			</select>
		</div>
		<div class="col-lg-6" style="padding-left: 0px;">
			<div class="col-lg-12 div_peternak no-padding <?php echo ($data['jenis_tujuan'] == 'peternak') ? null : 'hide'; ?>">
				<div class="col-lg-3 no-padding">
					<input type="text" class="form-control text-center datetimepicker" placeholder="Bulan" name="bulan_docin" id="bulan_docin" onblur="pv.get_peternak(this)" data-tgl="<?php echo $tgl_docin_tujuan; ?>" />
		        </div>
		        <div class="col-lg-9">
					<select class="form-control peternak" data-noreg="<?php echo ($data['jenis_tujuan'] == 'peternak') ? trim($data['tujuan']) : ''; ?>" >
						<option value="">-- Pilih Peternak --</option>
						<!-- <?php foreach ($peternak as $k_peternak => $v_peternak): ?>
							<?php
								$selected = null;
								if ( $data['jenis_tujuan'] == 'peternak' ) {
									if ( $data['tujuan'] == $v_peternak['noreg'] ) {
										$selected = 'selected';
									}
								}
							?>
							<option value="<?php echo $v_peternak['noreg']; ?>" <?php echo $selected; ?> ><?php echo strtoupper($v_peternak['kode_unit']).' | '.strtoupper($v_peternak['nama']).' ('.$v_peternak['noreg'].')'; ?></option>
						<?php endforeach ?> -->
					</select>
		        </div>
			</div>
			<div class="col-lg-12 gudang no-padding <?php echo ($data['jenis_tujuan'] == 'gudang') ? null : 'hide'; ?>">
				<select class="form-control gudang">
					<option value="">-- Pilih Gudang --</option>
					<?php foreach ($gudang_tujuan as $k_gudang => $v_gudang): ?>
						<?php
							$selected = null;
							if ( $data['jenis_tujuan'] == 'gudang' ) {
								if ( $data['tujuan'] == $v_gudang['id'] ) {
									$selected = 'selected';
								}
							}
						?>
						<option value="<?php echo $v_gudang['id']; ?>" <?php echo $selected; ?> ><?php echo strtoupper($v_gudang['nama']); ?></option>
					<?php endforeach ?>
				</select>
			</div>
		</div>
	</div>
</div>
<div class="form-group d-flex align-items-center">
	<div class="col-lg-12 d-flex align-items-center no-padding">
		<div class="col-lg-2">Rencana Kirim</div>
		<div class="col-lg-2">
			<div class="input-group date datetimepicker" name="rcn_kirim" id="rcn_kirim">
		        <input type="text" class="form-control text-center" placeholder="Rencana Kirim" data-required="1" data-tgl="<?php echo $data['tgl_kirim']; ?>" />
		        <span class="input-group-addon">
		            <span class="glyphicon glyphicon-calendar"></span>
		        </span>
		    </div>
		</div>
		<div class="col-lg-2"></div>
		<div class="col-lg-2">Ekspedisi</div>
		<div class="col-lg-3">
			<input type="text" class="form-control ekspedisi" placeholder="Ekspedisi" data-required="1" value="<?php echo $data['ekspedisi'] ?>">
		</div>
	</div>
</div>
<div class="form-group d-flex align-items-center">
	<div class="col-lg-12 d-flex align-items-center no-padding">
		<div class="col-lg-2">Tgl Kirim</div>
		<div class="col-lg-2">
			<div class="input-group date datetimepicker" name="tgl_kirim" id="tgl_kirim">
		        <input type="text" class="form-control text-center" placeholder="Tanggal Kirim" data-required="1" data-tgl="<?php echo $data['tgl_kirim']; ?>" />
		        <span class="input-group-addon">
		            <span class="glyphicon glyphicon-calendar"></span>
		        </span>
		    </div>
		</div>
		<div class="col-lg-2"></div>
		<div class="col-lg-2">No. Polisi</div>
		<div class="col-lg-2">
			<input type="text" class="form-control no_pol" placeholder="No. Polisi" data-required="1" value="<?php echo $data['no_polisi'] ?>">
		</div>
	</div>
</div>
<div class="form-group d-flex align-items-center">
	<div class="col-lg-12 d-flex align-items-center no-padding">
		<div class="col-lg-2">No. SJ</div>
		<div class="col-lg-3">
			<input type="text" class="form-control no_sj" placeholder="No. SJ" data-required="1" value="<?php echo $data['no_sj'] ?>" <?php echo ($data['jenis_kirim'] == 'opks') ? '' : 'readonly'; ?> >
		</div>
		<div class="col-lg-1"></div>
		<div class="col-lg-2">Sopir</div>
		<div class="col-lg-2">
			<input type="text" class="form-control sopir" placeholder="Sopir" data-required="1" value="<?php echo $data['sopir'] ?>">
		</div>
	</div>
</div>
<div class="form-group d-flex align-items-center" style="padding-right: 30px;">
	<div class="col-lg-12 d-flex align-items-center">
		<table class="table table-bordered table-hover tbl_detail_brg" style="margin-bottom: 0px;">
			<thead>
				<tr>
					<th class="col-lg-2">Jenis OVK</th>
					<th class="col-lg-2">Jumlah</th>
					<th class="col-lg-2">Kondisi</th>
				</tr>
			</thead>
			<tbody>
				<?php $jml_detail = 0; ?>
				<?php foreach ($data['detail'] as $k_det => $v_det): ?>
					<tr>
						<td>
							<select class="form-control barang">
								<?php foreach ($voadip as $k_voadip => $v_voadip): ?>
									<?php
										$selected = null;
										if ( $v_voadip['kode'] == $v_det['item'] ) {
											$selected = 'selected';
										}
									?>
									<option value="<?php echo $v_voadip['kode']; ?>" <?php echo $selected; ?> ><?php echo $v_voadip['nama']; ?></option>
								<?php endforeach ?>
							</select>
						</td>
						<td>
							<input type="text" class="form-control text-right jumlah" placeholder="Jumlah" data-tipe="decimal" data-required="1" value="<?php echo angkaDecimal($v_det['jumlah']) ?>">
						</td>
						<td>
							<input type="text" class="form-control kondisi" placeholder="Kondisi" data-required="1" value="<?php echo angkaRibuan($v_det['kondisi']) ?>" onblur="pv.cek_stok_gudang(this)">
							<?php
								$jml_detail++;
								$css = 'display: none;';
								if ( $jml_detail == count($data['detail']) ) {
									$css = 'display: block;';
								}
							?>
							<div class="btn-ctrl" style="<?php echo $css; ?>">
								<span onclick="pv.removeRowChild(this)" class="btn_del_row_2x <?php echo (count($data['detail']) == 1) ? 'hide' : null; ?>"></span>
								<span onclick="pv.addRowChild(this)" class="btn_add_row_2x"></span>
							</div>
						</td>
					</tr>
				<?php endforeach ?>
			</tbody>
		</table>
	</div>
</div>
<div class="form-group d-flex align-items-center">
	<div class="col-lg-12"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
</div>
<div class="form-group d-flex align-items-center">
	<div class="col-lg-12">
		<button type="button" class="btn btn-primary cursor-p pull-right" title="ADD" style="margin-left: 5px;" data-id="<?php echo $data['id']; ?>" onclick="pv.edit_kirim_voadip(this)" data-href="pengiriman"> 
			<i class="fa fa-edit" aria-hidden="true"></i> Update
		</button>
		<button type="button" class="btn btn-danger cursor-p pull-right" title="ADD" style="margin-right: 5px;" data-id="<?php echo $data['id']; ?>" onclick="pv.changeTabActive(this)" data-href="pengiriman"> 
			<i class="fa fa-times" aria-hidden="true"></i> Batal
		</button>
	</div>
</div>
<!-- <div class="form-group">
	<div class="col-lg-12 no-padding">
		<hr>
		<button id="btn-add" type="button" data-href="action" class="btn btn-primary cursor-p pull-left" title="ADD" onclick="pv.edit_kirim_voadip(this)" style="margin-left: 10px;" data-id="<?php echo $data['id']; ?>"> 
			<i class="fa fa-edit" aria-hidden="true"></i> Update
		</button>
	</div>
</div> -->