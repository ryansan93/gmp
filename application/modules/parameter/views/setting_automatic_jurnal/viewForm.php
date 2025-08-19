<div class="col-xs-12 no-padding">
    <div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
        <div class="col-xs-1 no-padding">
            <label class="control-label">Tgl Berlaku</label>
        </div>
        <div class="col-xs-10 no-padding">
            <label class="control-label">:</label> <?php echo strtoupper(tglIndonesia($data['tgl_berlaku'], '-', ' ', true)); ?>
        </div>
    </div>
    <div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
        <div class="col-xs-1 no-padding">
            <label class="control-label">Fitur</label>
        </div>
        <div class="col-xs-10 no-padding">
            <label class="control-label">:</label> <?php echo $data['nama_fitur'].' -> '.$data['nama']; ?>
        </div>
    </div>
    <div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
        <div class="col-xs-1 no-padding">
            <label class="control-label">Table Name</label>
        </div>
        <div class="col-xs-10 no-padding">
            <label class="control-label">:</label> <?php echo $data['tbl_name']; ?>
        </div>
    </div>
    <div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
        <div class="col-xs-12 no-padding">
            <label class="control-label">Query</label>
        </div>
        <div class="col-xs-12 no-padding">
            <textarea class="form-control query" placeholder="QUERY" style="height: 300p;" readonly><?php echo $data['_query']; ?></textarea>
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
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data['detail'] as $k_det => $v_det) { ?>
                        <tr>
                            <td>
                                <?php echo $v_det['urut']; ?>
                            </td>
                            <td>
                                <?php echo !empty($v_det['det_jurnal_trans_kode']) ? $v_det['det_jurnal_trans_kode'] : '-'; ?>
                            </td>
                            <td>
                                <?php echo !empty($v_det['_query_coa_asal']) ? $v_det['_query_coa_asal'] : '-'; ?>
                            </td>
                            <td>
                                <?php echo !empty($v_det['coa_asal']) ? $v_det['coa_asal'].' | '.$v_det['nama_coa_asal'] : '-'; ?>
                            </td>
                            <td>
                                <?php echo !empty($v_det['_query_coa_tujuan']) ? $v_det['_query_coa_tujuan'] : '-'; ?>
                            </td>
                            <td>
                                <?php echo !empty($v_det['coa_tujuan']) ? $v_det['coa_tujuan'].' | '.$v_det['nama_coa_tujuan'] : '-'; ?>
                            </td>
                            <td>
                                <?php echo !empty($v_det['_ket']) ? $v_det['_ket'] : '-'; ?>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </small>
    </div>
    <div class="col-xs-12 no-padding">
        <hr style="margin-top: 5px; margin-bottom: 5px;">
    </div>
    <div class="col-xs-12 no-padding">
        <div class="col-xs-6 no-padding" style="padding-right: 5px;">
            <button type="button" class="btn btn-danger col-xs-12" onclick="saj.delete(this)" data-id="<?php echo $data['id']; ?>"><i class="fa fa-trash"></i> HAPUS</button>
        </div>
        <div class="col-xs-6 no-padding" style="padding-left: 5px;">
            <button type="button" class="btn btn-primary col-xs-12" onclick="saj.changeTabActive(this)" data-id="<?php echo $data['id']; ?>" data-resubmit="edit" data-href="action"><i class="fa fa-edit"></i> EDIT</button>
        </div>
    </div>
</div>