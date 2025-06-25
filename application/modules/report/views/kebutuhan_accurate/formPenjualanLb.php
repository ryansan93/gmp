<div class="col-xs-12 no-padding">
    <div class="col-xs-12 no-padding">
        <div class="col-xs-6 no-padding" style="padding-right: 5px; margin-bottom: 10px;">
            <div class="col-xs-12 no-padding">
                <label>TGL PANEN AWAL</label>
            </div>
            <div class="col-xs-12 no-padding">
                <div class="input-group date datetimepicker" name="startDate" id="StartDate">
                        <input type="text" class="form-control text-center" placeholder="Start Date" data-required="1" />
                        <span class="input-group-addon">
                            <span class="glyphicon glyphicon-calendar"></span>
                        </span>
                    </div>
            </div>
        </div>
        <div class="col-xs-6 no-padding" style="padding-left: 5px; margin-bottom: 10px;">
            <div class="col-xs-12 no-padding">
                <label>TGL PANEN AKHIR</label>
            </div>
            <div class="col-xs-12 no-padding">
                <div class="input-group date datetimepicker" name="endDate" id="EndDate">
                    <input type="text" class="form-control text-center" placeholder="End Date" data-required="1" />
                    <span class="input-group-addon">
                        <span class="glyphicon glyphicon-calendar"></span>
                    </span>
                </div>
            </div>
        </div>
        <div class="col-xs-6 no-padding" style="padding-right: 5px; margin-bottom: 10px;">
            <div class="col-xs-12 no-padding">
                <label>PERUSAHAAN</label>
            </div>
            <div class="col-xs-12 no-padding">
                <select class="form-control perusahaan" multiple="multiple" data-required="1">
                    <option value="all">ALL</option>
                    <?php foreach ($perusahaan as $k_perusahaan => $v_perusahaan): ?>
                        <option value="<?php echo $v_perusahaan['kode']; ?>"><?php echo strtoupper($v_perusahaan['perusahaan']); ?></option>
                    <?php endforeach ?>
                </select>
            </div>
        </div>
        <div class="col-xs-4 no-padding" style="padding-left: 5px; padding-right: 5px; margin-bottom: 10px;">
            <div class="col-xs-12 no-padding">
                <label>UNIT</label>
            </div>
            <div class="col-xs-12 no-padding">
                <select class="form-control unit" multiple="multiple" data-required="1">
                    <option value="all">ALL</option>
                    <?php foreach ($unit as $k_unit => $v_unit): ?>
                        <option value="<?php echo $v_unit['kode']; ?>"><?php echo strtoupper($v_unit['nama']); ?></option>
                    <?php endforeach ?>
                </select>
            </div>
        </div>
        <div class="col-xs-2 no-padding" style="padding-left: 5px; margin-bottom: 10px;">
            <div class="col-xs-12 no-padding">
                <label>TUTUP SIKLUS</label>
            </div>
            <div class="col-xs-12 no-padding">
                <select class="form-control tutup_siklus" data-required="1">
                    <option value="all">ALL</option>
                    <option value="1">SUDAH TUTUP SIKLUS</option>
                    <option value="0">BELUM TUTUP SIKLUS</option>
                </select>
            </div>
        </div>
        
        <div class="col-xs-12 no-padding">
            <button type="button" class="col-xs-12 btn btn-primary pull-right" onclick="ka.getLists(this)" data-jenis="1"><i class="fa fa-search"></i> Tampilkan</button>
        </div>
    </div>
</div>
<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
<div class="col-xs-12 no-padding">
    <div class="col-xs-12 no-padding" style="overflow-x: auto;">
        <small>
            <table class="table table-bordered tbl_laporan" style="margin-bottom: 0px; max-width: 200%; width: 200%;">
                <thead>
                    <tr>
                        <td colspan="10"><b>TOTAL</b></td>
                        <td class="total text-right" data-target="kuantitas" data-jenis="decimal"><b>0</b></td>
                        <td></td>
                        <td class="total text-right" data-target="jml_ekor" data-jenis="integer"><b>0</b></td>
                        <td class="total text-right" data-target="total" data-jenis="decimal"><b>0</b></td>
                        <td colspan="7"></td>
                    </tr>
                    <tr>
                        <th>Kode Bakul</th>
                        <th>NIK Bakul</th>
                        <th>Nama Bakul</th>
                        <th>Alamat Bakul</th>
                        <th>No. Faktur</th>
                        <th>Tanggal Panen</th>
                        <th>Tanggal RHPP</th>
                        <th>No. Nota (No. SJ)</th>
                        <th>Kode Barang (Ayam)</th>
                        <th>Deskripsi Barang (Ayam)</th>
                        <th>Kuantitas</th>
                        <th>Harga Per Satuan Kuantitas</th>
                        <th>Jumlah Ekor</th>
                        <th>Total</th>
                        <th>Periode (Tgl Chick In)</th>
                        <th>Departemen (Kota Unit)</th>
                        <th>NIM</th>
                        <th>NIK</th>
                        <th>Nama Plasma</th>
                        <th>Kandang Plasma</th>
                        <th>NPWP Plasma</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="21">Data tidak ditemukan.</td>
                    </tr>
                </tbody>
            </table>
        </small>
    </div>
</div>
<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
<div class="col-xs-12 no-padding">
    <button type="button" class="btn btn-default pull-right" onclick="ka.excryptParams(this)" data-tipe="excel" data-jenis="1"><i class="fa fa-file-excel-o"></i> Export Excel</button>
    <button type="button" class="btn btn-default pull-right" onclick="ka.excryptParams(this)" data-tipe="xml" data-jenis="1" style="margin-right: 10px;"><i class="fa fa-file-o"></i> Export XML</button>
</div>