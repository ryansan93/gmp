<div class="col-sm-12">
    <form class="form-horizontal" role="form">
        <div class="form-group">
            <div class="col-sm-1 no-padding">
                <label class="control-label">Jenis Retur</label>
            </div>
            <div class="col-sm-2">
                <select class="form-control jenis_retur" data-required="1" onchange="rv.cek_jenis(this)">
                    <option value="">-- Pilih Jenis --</option>
                    <option value="opkp" <?php echo ($data['jenis_retur'] == 'opkp') ? 'selected' : null; ?> >Dari Peternak (OPKP)</option>
                    <option value="opkg" <?php echo ($data['jenis_retur'] == 'opkg') ? 'selected' : null; ?> >Dari Gudang (OPKG)</option>
                </select>
            </div>
            <div class="col-lg-2" style="padding-left: 0px;">
                <select class="form-control unit" data-required="1">
                    <option value="">-- Pilih Unit --</option>
                    <?php if ( count($unit) > 0 ): ?>
                        <?php foreach ($unit as $k => $val): ?>
                            <?php 
                                $true = false;
                                if ( stristr($data['no_order'], $val['kode']) !== FALSE ) { 
                                    $true = true;
                                }
                            ?>
                            <option value="<?php echo $val['kode'] ?>" <?php echo ($true) ? 'selected' : null; ?> ><?php echo strtoupper($val['nama']); ?></option>
                        <?php endforeach ?>
                    <?php endif ?>
                </select>
            </div>
            <div class="col-lg-2" style="padding-left: 0px;">
                <div class="input-group date datetimepicker" name="tgl_kirim" id="tgl_kirim">
                    <input type="text" class="form-control text-center" placeholder="Tanggal Kirim" data-required="1" data-tgl="<?php echo $data['tgl_kirim']; ?>" />
                    <span class="input-group-addon">
                        <span class="glyphicon glyphicon-calendar"></span>
                    </span>
                </div>
            </div>
            <div class="col-lg-2" style="padding-left: 0px; display: hidden;">
                <button type="button" class="btn btn-primary get_op" onclick="rv.cek_jenis(this)" data-tipe="edit">Ambil OP</button>
            </div>
        </div>
        <div class="form-group">
            <hr style="margin-top: 10px; margin-bottom: 10px;">
        </div>
        <div class="form-group">
            <div class="col-sm-1 no-padding">
                <label class="control-label">No. Order</label>
            </div>
            <div class="col-sm-2">
               <select class="form-control no_order" onchange="rv.get_detail_order_voadip(this)" disabled data-required="1" data-noorder="<?php echo $data['no_order']; ?>" data-asal="<?php echo $data['asal']; ?>" data-idasal="<?php echo $data['id_asal']; ?>" data-tglkirim="<?php echo $data['tgl_kirim']; ?>">
                   <option value="">Pilih No. Order</option>
               </select>
            </div>
            <div class="col-sm-1 text-right no-padding">
                <label class="control-label">Tgl Retur</label>
            </div>
            <div class="col-sm-2">
                <div class="input-group date" id="tgl_retur" name="tgl_retur">
                    <input type="text" class="form-control text-center" placeholder="Tanggal" data-required="1" data-tgl="<?php echo $data['tgl_retur']; ?>" />
                    <span class="input-group-addon">
                        <span class="glyphicon glyphicon-calendar"></span>
                    </span>
                </div>
            </div>
        </div>
        <div class="form-group">
            <div class="col-sm-1 no-padding">
                <label class="control-label">Asal</label>
            </div>
            <div class="col-sm-5">
               <input type="text" class="form-control asal" placeholder="Asal" value="<?php echo $data['asal']; ?>" readonly>
            </div>
        </div>
        <div class="form-group">
            <div class="col-sm-1 no-padding">
                <label class="control-label">Tujuan</label>
            </div>
            <div class="col-sm-2">
               <select class="form-control tujuan" onchange="rv.cek_tujuan(this)" data-required="1">
                   <option value="gudang" <?php echo ($data['tujuan'] == 'gudang') ? 'selected' : null; ?> >Gudang</option>
                   <option value="supplier" <?php echo ($data['tujuan'] == 'supplier') ? 'selected' : null; ?> >Supplier</option>
               </select>
            </div>
            <div class="col-sm-3">
                <select class="form-control gudang <?php echo ($data['tujuan'] == 'gudang') ? 'hide' : null; ?>" data-required="1">
                    <option value="">-- Pilih Gudang --</option>
                    <?php foreach ($gudang as $k_gudang => $v_gudang): ?>
                        <option value="<?php echo $v_gudang['id']; ?>" <?php echo ($data['tujuan'] == 'gudang' && $data['id_tujuan'] == $v_gudang['id']) ? 'selected' : null; ?> ><?php echo strtoupper($v_gudang['nama']); ?></option>
                    <?php endforeach ?>
                </select>
                <select class="form-control supplier <?php echo ($data['tujuan'] == 'supplier') ? 'hide' : null; ?>" data-required="1">
                    <option value="">-- Pilih Supplier --</option>
                    <?php foreach ($supplier as $k_supplier => $v_supplier): ?>
                        <option value="<?php echo $v_supplier['nomor']; ?>" <?php echo ($data['tujuan'] == 'supplier' && $data['id_tujuan'] == $v_supplier['nomor']) ? 'selected' : null; ?> ><?php echo strtoupper($v_supplier['nama']); ?></option>
                    <?php endforeach ?>
                </select>
            </div>
        </div>
        <div class="form-group">
            <div class="col-sm-1 no-padding">
                <label class="control-label">OA</label>
            </div>
            <div class="col-sm-2">
               <input type="text" class="form-control text-right ongkos_angkut" data-tipe="decimal" maxlength="14" placeholder="Ongkos Angkut" data-required="1" value="<?php echo angkaDecimal($data['ongkos_angkut']); ?>">
            </div>
        </div>
        <div class="form-group">
            <div class="col-sm-1 no-padding">
                <label class="control-label">Keterangan</label>
            </div>
            <div class="col-sm-10">
                <textarea class="form-control keterangan" data-required="1"><?php echo $data['keterangan']; ?></textarea>
            </div>
        </div>
        <div class="form-group">
        	<small>
            	<table class="table table-bordered tbl_data_ov header">
            		<thead>
                        <tr>
                            <th class="col-md-4 text-center">Nama Item</th>
                            <th class="col-md-1 text-center">Jumlah</th>
                            <th class="col-md-1 text-center">Jumlah Retur</th>
                            <th class="col-md-3 text-center">Nilai Retur</th>
                            <th class="col-md-3 text-center">Kondisi</th>
                        </tr>
            		</thead>
            		<tbody>
            			<?php if ( count($data['detail']) > 0) : ?>
                            <?php foreach ($data['detail'] as $k_det => $v_det): ?>
                                <tr class="v-center">
                                    <td class="barang" data-kode="<?php echo $v_det['item']; ?>"><?php echo strtoupper($v_det['nama']); ?></td>
                                    <td class="text-right jml_ov"><?php echo angkaDecimal($v_det['jumlah_ov']); ?></td>
                                    <td class="text-right">
                                        <input type="text" class="form-control text-right jml_retur" data-tipe="decimal" data-trigger="manual" data-toggle="tooltip" title="" data-required="1" onblur="rv.cek_jml_retur(this)" placeholder="Jumlah" value="<?php echo angkaDecimal($v_det['jumlah_rv']); ?>">
                                    </td>
                                    <td class="text-right">
                                        <input type="text" class="form-control text-right nilai_retur" data-tipe="decimal" data-trigger="manual" data-toggle="tooltip" title="" data-required="1" placeholder="Nilai" value="<?php echo angkaDecimal($v_det['nilai_retur']); ?>">
                                    </td>
                                    <td class="text-left">
                                        <input type="text" class="form-control text-left kondisi" data-required="1" placeholder="Kondisi" value="<?php echo $v_det['kondisi']; ?>">
                                    </td>
                                </tr>
                            <?php endforeach ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6">Data tidak ditemukan.</td>
                            </tr>
                        <?php endif ?>
            		</tbody>
            	</table>
            </small>
        </div>
    </form>
</div>
<div class="col-md-12 no-padding">
    <hr style="margin-top: 10px; margin-bottom: 10px;">
</div>
<div class="col-md-12 no-padding">
	<button type="button" class="btn btn-primary pull-right" onclick="rv.edit(this);" data-id="<?php echo $data['id']; ?>"><i class="fa fa-edit"></i> Edit</button>
</div>