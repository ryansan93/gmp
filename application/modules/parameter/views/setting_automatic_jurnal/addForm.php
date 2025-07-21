<div class="col-xs-12 no-padding">
    <div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
        <div class="col-xs-12 no-padding">
            <label class="control-label">Tgl Berlaku</label>
        </div>
        <div class="col-xs-2 no-padding">
            <div class="input-group date" id="tglBerlaku" name="TglBerlaku">
                <input type="text" class="form-control text-center" data-required="1" placeholder="Tgl Berlaku" />
                <span class="input-group-addon">
                    <span class="glyphicon glyphicon-calendar"></span>
                </span>
            </div>
        </div>
    </div>
    <div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
        <div class="col-xs-12 no-padding">
            <label class="control-label">Fitur</label>
        </div>
        <div class="col-xs-4 no-padding">
            <select class="form-control fitur" data-required="1">
                <option value="">-- Pilih Fitur --</option>
                <?php foreach ($fitur as $key => $value) { ?>
                    <option value="<?php echo $value['id_detfitur'] ?>"><?php echo $value['nama_fitur'].' | '.$value['nama_detfitur'] ?></option>
                <?php } ?>
            </select>
        </div>
    </div>
    <div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
        <div class="col-xs-12 no-padding">
            <label class="control-label">Table Name</label>
        </div>
        <div class="col-xs-12 no-padding">
            <input type="text" class="form-control tbl_name" placeholder="Table Name (MAX:100)" data-required="1">
        </div>
    </div>
    <div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
        <div class="col-xs-12 no-padding">
            <label class="control-label">Query</label>
        </div>
        <div class="col-xs-12 no-padding">
            <textarea class="form-control query" placeholder="QUERY" style="height: 300p;"></textarea>
        </div>
    </div>
    <div class="col-xs-12 no-padding">
        <hr style="margin-top: 5px; margin-bottom: 5px;">
    </div>
    <div class="col-xs-12 no-padding">
        <small>
            <table class="table table-bordered" style="margin-bottom: 0px;">
                <thead>
                    <tr>
                        <td class="col-xs-1"><b>NO. URUT</b></td>
                        <td class="col-xs-2"><b>TRANSAKSI JURNAL KODE</b></td>
                        <td class="col-xs-2"><b>QUERY COA ASAL</b></td>
                        <td class="col-xs-1"><b>COA ASAL</b></td>
                        <td class="col-xs-2"><b>QUERY COA TUJUAN</b></td>
                        <td class="col-xs-1"><b>COA TUJUAN</b></td>
                        <td class="col-xs-2"><b>KETERANGAN JURNAL</b></td>
                        <td class="col-xs-1"><b>ACTION</b></td>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <input type="text" class="form-control urut" data-required="1" placeholder="URUT" data-tipe="integer" value="1">
                        </td>
                        <td>
                            <select class="form-control det_jurnal_trans">
                                <option value="">-- Pilih Tansaksi Jurnal Kode --</option>
                                <?php foreach ($det_jurnal_trans as $key => $value) { ?>
                                    <option value="<?php echo $value['kode'] ?>" data-asal="<?php echo $value['sumber_coa'] ?>" data-tujuan="<?php echo $value['tujuan_coa'] ?>"><?php echo $value['kode'].' | '.$value['nama'] ?></option>
                                <?php } ?>
                            </select>
                        </td>
                        <td>
                            <input type="text" class="form-control query_coa_asal" placeholder="select * from coa where '%%'">
                        </td>
                        <td>
                            <select class="form-control coa_asal">
                                <option value="">-- Pilih COA --</option>
                                <?php foreach ($coa as $key => $value) { ?>
                                    <option value="<?php echo $value['no_coa'] ?>"><?php echo $value['no_coa'].' | '.$value['nama_coa'] ?></option>
                                <?php } ?>
                            </select>
                        </td>
                        <td>
                            <input type="text" class="form-control query_coa_tujuan" placeholder="select * from coa where '%%'">
                        </td>
                        <td>
                            <select class="form-control coa_tujuan">
                                <option value="">-- Pilih COA --</option>
                                <?php foreach ($coa as $key => $value) { ?>
                                    <option value="<?php echo $value['no_coa'] ?>"><?php echo $value['no_coa'].' | '.$value['nama_coa'] ?></option>
                                <?php } ?>
                            </select>
                        </td>
                        <td>
                            <input type="text" class="form-control keterangan" data-required="1" placeholder="Keterangan">
                        </td>
                        <td>
                            <div class="col-xs-12 no-padding">
                                <div class="col-xs-6 no-padding" style="padding-right: 5px;">
                                    <button type="button" class="btn btn-primary col-xs-12" onclick="saj.addRow(this)"><i class="fa fa-plus"></i></button>
                                </div>
                                <div class="col-xs-6 no-padding" style="padding-left: 5px;">
                                    <button type="button" class="btn btn-danger col-xs-12" onclick="saj.removeRow(this)"><i class="fa fa-times"></i></button>
                                </div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </small>
    </div>
    <div class="col-xs-12 no-padding">
        <hr style="margin-top: 5px; margin-bottom: 5px;">
    </div>`
    <div class="col-xs-12 no-padding">
        <button type="button" class="btn btn-primary col-xs-12" onclick="saj.save()"><i class="fa fa-save"></i> SIMPAN</button>
    </div>
</div>