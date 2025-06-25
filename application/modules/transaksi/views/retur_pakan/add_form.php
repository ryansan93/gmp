<div class="col-sm-12">
    <form class="form-horizontal" role="form">
        <div class="form-group">
            <div class="col-sm-1 no-padding">
                <label class="control-label">Filter OP</label>
            </div>
            <div class="col-sm-2">
                <select class="form-control jenis_retur" data-required="1">
                    <option value="">-- Pilih Jenis Retur --</option>
                    <option value="opkp">Dari Peternak (OPKP)</option>
                    <option value="opkg">Dari Gudang (OPKG)</option>
                </select>
            </div>
            <div class="col-lg-2" style="padding-left: 0px;">
                <select class="form-control unit" data-required="1">
                    <option value="">-- Pilih Unit --</option>
                    <?php if ( count($unit) > 0 ): ?>
                        <?php foreach ($unit as $k => $val): ?>
                            <option value="<?php echo $val['kode'] ?>"><?php echo strtoupper($val['nama']); ?></option>
                        <?php endforeach ?>
                    <?php endif ?>
                </select>
            </div>
            <div class="col-lg-2" style="padding-left: 0px;">
                <div class="input-group date datetimepicker" name="tgl_kirim" id="tgl_kirim">
                    <input type="text" class="form-control text-center" placeholder="Tanggal Kirim" data-required="1" />
                    <span class="input-group-addon">
                        <span class="glyphicon glyphicon-calendar"></span>
                    </span>
                </div>
            </div>
            <div class="col-lg-2" style="padding-left: 0px;">
                <button type="button" class="btn btn-primary get_op" onclick="rp.cek_jenis(this)">Ambil OP</button>
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
               <select class="form-control no_order" onchange="rp.get_detail_order_pakan(this)" disabled data-required="1">
                   <option value="">Pilih No. Order</option>
               </select>
            </div>
            <div class="col-sm-1 text-right no-padding">
                <label class="control-label">Tgl Retur</label>
            </div>
            <div class="col-sm-2">
                <div class="input-group date" id="tgl_retur" name="tgl_retur">
                    <input type="text" class="form-control text-center" placeholder="Tanggal" data-required="1" disabled />
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
               <input type="text" class="form-control asal" placeholder="Asal" readonly>
            </div>
        </div>
        <div class="form-group">
            <div class="col-sm-1 no-padding">
                <label class="control-label">Tujuan</label>
            </div>
            <div class="col-sm-2">
               <select class="form-control tujuan" onchange="rp.cek_tujuan(this)" data-required="1">
                   <option value="gudang">Gudang</option>
                   <option value="supplier">Supplier</option>
               </select>
            </div>
            <div class="col-sm-3">
                <select class="form-control gudang" data-required="1">
                    <option value="">-- Pilih Gudang --</option>
                    <?php foreach ($gudang as $k_gudang => $v_gudang): ?>
                        <option value="<?php echo $v_gudang['id']; ?>"><?php echo strtoupper($v_gudang['nama']); ?></option>
                    <?php endforeach ?>
                </select>
                <select class="form-control supplier hide" data-required="1">
                    <option value="">-- Pilih Supplier --</option>
                    <?php foreach ($supplier as $k_supplier => $v_supplier): ?>
                        <option value="<?php echo $v_supplier['id']; ?>"><?php echo strtoupper($v_supplier['nama']); ?></option>
                    <?php endforeach ?>
                </select>
            </div>
        </div>
        <div class="form-group">
            <div class="col-sm-1 no-padding">
                <label class="control-label">OA</label>
            </div>
            <div class="col-sm-2">
               <input type="text" class="form-control text-right ongkos_angkut" data-tipe="decimal" maxlength="14" placeholder="Ongkos Angkut" data-required="1">
            </div>
        </div>
        <div class="form-group">
            <div class="col-sm-1 no-padding">
                <label class="control-label">Ekspedisi</label>
            </div>
            <div class="col-sm-4">
                <!-- <input type="text" class="form-control ekspedisi" placeholder="Ekspedisi" data-required="1"> -->
                <select class="form-control ekspedisi" data-required="1">
                    <option value="">-- Piliih Ekspedisi --</option>
                    <?php foreach ($ekspedisi as $k_eks => $v_eks): ?>
                        <option value="<?php echo $v_eks['nomor']; ?>" data-nama="<?php echo $v_eks['nama']; ?>" ><?php echo $v_eks['nomor'].' | '.$v_eks['nama']; ?></option>
                    <?php endforeach ?>
                </select>
            </div>
            <!-- <div class="col-sm-1 no-padding">&nbsp;</div> -->
            <div class="col-sm-1 no-padding text-right">
                <label class="control-label">No. Polisi</label>
            </div>
            <div class="col-sm-2">
               <input type="text" class="form-control nopol" placeholder="No. Polisi" data-required="1">
            </div>
            <!-- <div class="col-sm-1 no-padding">&nbsp;</div> -->
            <div class="col-sm-1 no-padding text-right">
                <label class="control-label">Sopir</label>
            </div>
            <div class="col-sm-2">
               <input type="text" class="form-control sopir" placeholder="Sopir" data-required="1">
            </div>
        </div>
        <div class="form-group">
            <div class="col-sm-1 no-padding">
                <label class="control-label">Keterangan</label>
            </div>
            <div class="col-sm-10">
                <textarea class="form-control keterangan" data-required="1"></textarea>
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
                        <tr>
                            <td colspan="6">Data tidak ditemukan.</td>
                        </tr>
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
    <button type="button" class="btn btn-primary pull-right" onclick="rp.save()"><i class="fa fa-save"></i> Simpan</button>
</div>