<div class="row content-panel detailed">
    <!-- <h4 class="mb">Rencana Chick In Mingguan</h4> -->
    <div class="col-lg-12 detailed">
        <input type="hidden" data-noreg="">

        <form role="form" class="form-horizontal">
            <div class="panel-heading">
                <ul class="nav nav-tabs nav-justified">
                    <li class="nav-item">
                        <a class="nav-link active" data-toggle="tab" href="#riwayat" data-tab="riwayat">Riwayat Penerimaan</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#penerimaan" data-tab="penerimaan">Penerimaan Voadip</a>
                    </li>
                </ul>
            </div>
            <div class="panel-body">
                <div class="tab-content">
                    <div id="riwayat" class="tab-pane fade show active">
                        <div class="col-lg-10 search no-padding d-flex align-items-center">
                            <div class="col-sm-1 no-padding">
                                <span> Periode </label>
                            </div>
                            <div class="col-sm-2">
                                <div class="input-group date datetimepicker" name="startDate" id="StartDate">
                                    <input type="text" class="form-control text-center" placeholder="Start Date" data-required="1" />
                                    <span class="input-group-addon">
                                        <span class="glyphicon glyphicon-calendar"></span>
                                    </span>
                                </div>
                            </div>
                            <div class="col-sm-1 text-center no-padding" style="max-width: 4%;">s/d</div>
                            <div class="col-sm-2">
                                <div class="input-group date datetimepicker" name="endDate" id="EndDate">
                                    <input type="text" class="form-control text-center" placeholder="End Date" data-required="1" />
                                    <span class="input-group-addon">
                                        <span class="glyphicon glyphicon-calendar"></span>
                                    </span>
                                </div>
                            </div>
                            <div class="col-sm-1 text-center no-padding" style="max-width: 4%;">Unit</div>
                            <div class="col-sm-2">
                                <select class="form-control unit">
                                    <option value="all">All</option>
                                    <?php if ( !empty($unit) ): ?>
                                        <?php foreach ($unit as $k_unit => $v_unit): ?>
                                            <option value="<?php echo $v_unit['kode']; ?>"><?php echo strtoupper($v_unit['nama']); ?></option>
                                        <?php endforeach ?>
                                    <?php endif ?>
                                </select>
                            </div>
                            <div class="col-sm-3">
                                <button id="btn-tampil" type="button" data-href="action" class="btn btn-primary cursor-p pull-left" title="TAMPIL" onclick="pv.get_lists()" style="margin-right: 10px;">Tampilkan</button>
                                <button id="btn-add" type="button" data-href="penerimaan" class="btn btn-primary cursor-p pull-left" title="ADD" onclick="pv.changeTabActive(this)"><i class="fa fa-plus" aria-hidden="true"></i> ADD</button>
                            </div>
                        </div>
                        <div class="col-lg-2 action no-padding">
                            <div class="col-lg-12 search left-inner-addon no-padding pull-right" style="margin-left: 10px;">
                                <i class="glyphicon glyphicon-search"></i><input class="form-control" type="search" data-table="tbl_penerimaan" placeholder="Search" onkeyup="filter_all(this)">
                            </div>
                        </div>
                        <small>
                            <table class="table table-bordered tbl_penerimaan">
                                <thead>
                                    <tr>
                                        <th class="col-sm-1 text-center">No. SJ</th>
                                        <th class="col-sm-1 text-center">Tgl Terima</th>
                                        <th class="col-sm-4 text-center">Asal</th>
                                        <th class="col-sm-4 text-center">Tujuan</th>
                                        <th class="col-sm-1 text-center">No. Polisi</th>
                                        <th class="col-sm-1 text-center"></th>
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
                    <div id="penerimaan" class="tab-pane fade">
                        <?php echo $add_form; ?>
                    </div>
                </div>
            </div>
            <!-- <div class="form-group d-flex align-items-center">
                <div class="col-lg-6 d-flex align-items-center no-padding">
                    <div class="col-lg-3 text-left">No. SJ</div>
                    <div class="col-lg-6">
                        <select class="form-control no_sj" data-required="1" onchange="pv.get_data_by_sj(this)">
                            <option value="">-- Pilih No. SJ --</option>
                            <?php if ( count($get_sj_not_terima) > 0 ): ?>
                                <?php foreach ($get_sj_not_terima as $k => $val): ?>
                                    <option value="<?php echo $val['id'] ?>"><?php echo $val['no_sj']; ?></option>
                                <?php endforeach ?>
                            <?php endif ?>
                        </select>
                    </div>
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
                                <th class="col-lg-2 text-center" rowspan="2">Jenis Pakan</th>
                                <th class="col-lg-2 text-center" colspan="2">Kirim</th>
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
                                <td colspan="5">Data tidak ditemukan.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="form-group">
                <div class="col-lg-12 no-padding">
                    <hr>
                    <button id="btn-add" type="button" data-href="action" class="btn btn-primary cursor-p pull-left" title="ADD" onclick="pv.save_terima_pakan()" style="margin-left: 10px;"> 
                        <i class="fa fa-save" aria-hidden="true"></i> Simpan
                    </button>
                </div>
            </div> -->
        </form>
    </div>
</div>