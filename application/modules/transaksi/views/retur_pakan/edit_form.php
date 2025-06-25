<div class="col-sm-12">
    <form class="form-horizontal" role="form">
        <div class="form-group">
            <div class="col-sm-1 no-padding">
                <label class="control-label">Jenis Retur</label>
            </div>
            <div class="col-sm-2">
                <select class="form-control jenis_retur" data-required="1" onchange="rp.cek_jenis(this)">
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
                <button type="button" class="btn btn-primary get_op" onclick="rp.cek_jenis(this)" data-tipe="edit">Ambil OP</button>
            </div>
        </div>
        <div class="form-group">
            <div class="col-sm-1 no-padding">
                <label class="control-label">No. Order</label>
            </div>
            <div class="col-sm-2">
               <select class="form-control no_order" onchange="rp.get_detail_order_pakan(this)" disabled data-required="1" data-noorder="<?php echo $data['no_order']; ?>" data-asal="<?php echo $data['asal']; ?>" data-idasal="<?php echo $data['id_asal']; ?>" data-tglkirim="<?php echo $data['tgl_kirim']; ?>">
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
               <select class="form-control tujuan" onchange="rp.cek_tujuan(this)" data-required="1">
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
                <label class="control-label">Ekspedisi</label>
            </div>
            <div class="col-sm-4">
               <!-- <input type="text" class="form-control ekspedisi" placeholder="Ekspedisi" data-required="1" value="<?php echo $data['ekspedisi']; ?>"> -->
               <select class="form-control ekspedisi" data-required="1">
                    <option value="">-- Piliih Ekspedisi --</option>
                    <?php foreach ($ekspedisi as $k_eks => $v_eks): ?>
                        <?php
                            $selected = null;
                            if ( $v_eks['nomor'] == $data['ekspedisi_id'] ) {
                                $selected = 'selected';
                            }
                        ?>
                        <option value="<?php echo $v_eks['nomor']; ?>" data-nama="<?php echo $v_eks['nama']; ?>" <?php echo $selected; ?> ><?php echo $v_eks['nomor'].' | '.$v_eks['nama']; ?></option>
                    <?php endforeach ?>
                </select>
            </div>
            <!-- <div class="col-sm-1 no-padding">&nbsp;</div> -->
            <div class="col-sm-1 no-padding text-right">
                <label class="control-label">No. Polisi</label>
            </div>
            <div class="col-sm-2">
               <input type="text" class="form-control nopol" placeholder="No. Polisi" data-required="1" value="<?php echo $data['no_polisi']; ?>">
            </div>
            <!-- <div class="col-sm-1 no-padding">&nbsp;</div> -->
            <div class="col-sm-1 no-padding text-right">
                <label class="control-label">Sopir</label>
            </div>
            <div class="col-sm-2">
               <input type="text" class="form-control sopir" placeholder="Sopir" data-required="1" value="<?php echo $data['sopir']; ?>">
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
                <table class="table table-bordered tbl_data_op header">
                    <thead>
                        <tr>
                            <th class="col-md-2 text-center">Nama Item</th>
                            <th class="col-md-1 text-center">Jumlah (Kg)</th>
                            <th class="col-md-1 text-center">Jumlah Retur (Kg)</th>
                            <th class="col-md-2 text-center">Kondisi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ( count($data['detail']) > 0) : ?>
                            <?php foreach ($data['detail'] as $k_det => $v_det): ?>
                                <tr class="v-center">
                                    <td class="barang" data-kode="<?php echo $v_det['item']; ?>"><?php echo strtoupper($v_det['nama']); ?></td>
                                    <td class="text-right jml_op"><?php echo angkaRibuan($v_det['jumlah_op']); ?></td>
                                    <td class="text-right">
                                        <input type="text" class="form-control text-right jml_retur" data-tipe="integer" data-trigger="manual" data-toggle="tooltip" title="" data-required="1" onkeyup="rp.cek_jml_retur(this)" placeholder="Jumlah" value="<?php echo angkaRibuan($v_det['jumlah_rp']); ?>">
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
    <button type="button" class="btn btn-primary pull-right" onclick="rp.edit(this);" data-id="<?php echo $data['id']; ?>"><i class="fa fa-edit"></i> Edit</button>
</div>