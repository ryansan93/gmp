<div class="form-group d-flex align-items-center">
    <div class="col-lg-6 d-flex align-items-center no-padding filter">
        <div class="col-lg-3 text-left">Filter SJ</div>
        <div class="col-lg-3">
            <select class="form-control unit" data-required="1">
                <option value="">-- Pilih Unit --</option>
                <?php if ( count($unit) > 0 ): ?>
                    <?php foreach ($unit as $k => $val): ?>
                        <option value="<?php echo $val['kode'] ?>"><?php echo strtoupper($val['nama']); ?></option>
                    <?php endforeach ?>
                <?php endif ?>
            </select>
        </div>
        <div class="col-lg-3" style="padding-left: 0px;">
            <div class="input-group date datetimepicker" name="tgl_kirim" id="tgl_kirim">
                <input type="text" class="form-control text-center" placeholder="Tanggal Kirim" data-required="1" />
                <span class="input-group-addon">
                    <span class="glyphicon glyphicon-calendar"></span>
                </span>
            </div>
        </div>
        <div class="col-lg-2" style="padding-left: 0px;">
            <button type="button" class="btn btn-primary get_sj_not_terima" onclick="pv.get_sj_not_terima(this)">Ambil SJ</button>
        </div>
    </div>
</div>
<hr style="margin-top: 10px; margin-bottom: 10px;">
<div class="form-group d-flex align-items-center">
    <div class="col-lg-6 d-flex align-items-center no-padding">
        <div class="col-lg-3 text-left">No. BBM</div>
        <div class="col-lg-4">
            <input type="text" class="form-control no_bbm" placeholder="No. BBM" readonly>
        </div>
    </div>
</div>
<div class="form-group d-flex align-items-center">
    <div class="col-lg-6 d-flex align-items-center no-padding">
        <div class="col-lg-3 text-left">No. SJ</div>
        <div class="col-lg-6">
            <select class="form-control no_sj" data-required="1" onchange="pv.get_data_by_sj(this)" disabled>
                <option value="">-- Pilih No. SJ --</option>
                <!-- <?php if ( count($get_sj_not_terima) > 0 ): ?>
                    <?php foreach ($get_sj_not_terima as $k => $val): ?>
                        <option value="<?php echo $val['id'] ?>"><?php echo $val['no_sj']; ?></option>
                    <?php endforeach ?>
                <?php endif ?> -->
            </select>
        </div>
        <!-- <div class="col-lg-2" style="padding-top: 2px;">
            <a name="dokumen" class="text-right hide sj" target="_blank" style="padding-right: 10px;"><i class="fa fa-file"></i></a>
            <label class="" style="margin-bottom: 0px;">
                <input style="display: none;" placeholder="Dokumen" class="file_lampiran_sj no-check" type="file" onchange="pv.showNameFile(this)" data-name="no-name" data-allowtypes="doc|pdf|docx|jpg|jpeg|png" data-required="1">
                <i class="glyphicon glyphicon-paperclip cursor-p" title="Attachment SJ"></i> 
            </label>
        </div> -->
    </div>
    <div class="col-lg-6 d-flex align-items-center no-padding">
        <div class="col-lg-2 text-left">No. Polisi</div>
        <div class="col-lg-4">
            <input type="text" class="form-control no_pol" placeholder="No. Polisi" data-required="1" readonly>
        </div>
    </div>
</div>
<div class="form-group d-flex align-items-center">
    <div class="col-lg-6 d-flex align-items-center no-padding">
        <div class="col-lg-3 text-left">Ekspedisi</div>
        <div class="col-lg-8">
            <input type="text" class="form-control ekspedisi" placeholder="Ekspedisi" data-required="1" readonly>
        </div>
    </div>
    <div class="col-lg-6 d-flex align-items-center no-padding">
        <div class="col-lg-2 text-left">Sopir</div>
        <div class="col-lg-4">
            <input type="text" class="form-control sopir" placeholder="Sopir" data-required="1" readonly>
        </div>
    </div>
</div>
<div class="form-group d-flex align-items-center">
    <div class="col-lg-6 d-flex align-items-center no-padding">
        <div class="col-lg-3 text-left">Jenis Pengiriman</div>
        <div class="col-lg-4">
            <input type="text" class="form-control jenis_kirim" placeholder="Jenis" data-required="1" readonly>
        </div>
    </div>
    <div class="col-lg-6 d-flex align-items-center no-padding">
        <div class="col-lg-2 text-left">No. Order</div>
        <div class="col-lg-4">
            <input type="text" class="form-control no_order" placeholder="No. Order" data-required="1" readonly>
        </div>
    </div>
</div>
<div class="form-group d-flex align-items-center">
    <div class="col-lg-6 d-flex align-items-center no-padding">
        <div class="col-lg-3 text-left">Tgl Kirim</div>
        <div class="col-lg-4">
            <input type="text" class="form-control tgl_kirim" placeholder="Tanggal" data-required="1" readonly>
        </div>
    </div>
    <div class="col-lg-6 d-flex align-items-center no-padding">
        <div class="col-lg-2 text-left">Tgl Tiba</div>
        <div class="col-lg-4">
            <div class="input-group date datetimepicker" name="tgl_terima" id="tgl_terima">
                <input type="text" class="form-control text-center" placeholder="Tanggal Terima" data-required="1" />
                <span class="input-group-addon">
                    <span class="glyphicon glyphicon-calendar"></span>
                </span>
            </div>
        </div>
    </div>
</div>
<div class="form-group d-flex align-items-center">
    <div class="col-lg-6 d-flex align-items-center no-padding">
        <div class="col-lg-3 text-left">Asal</div>
        <div class="col-lg-6">
            <input type="text" class="form-control asal" placeholder="Asal" data-required="1" readonly>
        </div>
    </div>
    <div class="col-lg-6 d-flex align-items-center no-padding">
        <div class="col-lg-2 text-left">Tujuan</div>
        <div class="col-lg-6">
            <input type="text" class="form-control tujuan" placeholder="Tujuan" data-required="1" readonly>
        </div>
    </div>
</div>
<div class="form-group d-flex align-items-center">
    <div class="col-lg-12 d-flex align-items-center">
        <table class="table table-bordered table-hover" style="margin-bottom: 0px;">
            <thead>
                <tr>
                    <th class="col-lg-2 text-center" rowspan="2">Nama Barang</th>
                    <th class="col-lg-2 text-center" colspan="2">Kirim</th>
                    <th class="col-lg-1 text-center" rowspan="2">Harga Beli</th>
                    <th class="col-lg-1 text-center" rowspan="2">Harga Jual</th>
                    <th class="col-lg-2 text-center" colspan="2">Terima</th>
                </tr>
                <tr>
                    <th class="text-center">Jumlah</th>
                    <th class="text-center">Kondisi</th>
                    <th class="text-center">Jumlah</th>
                    <th class="text-center">Kondisi</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="7">Data tidak ditemukan.</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
<div class="form-group">
    <div class="col-lg-12 no-padding">
        <hr>
        <button id="btn-add" type="button" data-href="action" class="btn btn-primary cursor-p pull-left" title="ADD" onclick="pv.save_terima_voadip()" style="margin-left: 10px;"> 
            <i class="fa fa-save" aria-hidden="true"></i> Simpan
        </button>
    </div>
</div>