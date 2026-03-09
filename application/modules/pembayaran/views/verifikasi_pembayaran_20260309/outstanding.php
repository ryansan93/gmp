<div class="col-xs-12 no-padding">
    <div class="col-xs-12 no-padding">
        <button type="button" class="col-xs-12 btn btn-default" onclick="vp.getDataOutstanding()"><i class="fa fa-refresh"></i> REFRESH</button>
    </div>
    <div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
    <div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
        <div class="col-xs-12 no-padding"><label class="label-control">BANK</label></div>
        <div class="col-xs-12 no-padding">
            <select class="form-control bank">
                <option value="all">ALL</option>
                <?php foreach ($bank as $key => $value) { ?>
                    <option value="<?php echo $value['no_coa']; ?>"><?php echo $value['no_coa'].' | '.$value['nama_coa']; ?></option>
                <?php } ?>
            </select>
        </div>
    </div>
    <div class="col-xs-12 no-padding">
        <small>
            <table class="table table-bordered" style="margin-bottom: 0px;">
                <thead>
                    <tr>
                        <th class="col-xs-1">TRANSAKSI</th>
                        <th class="col-xs-2">SUPPLIER</th>
                        <th class="col-xs-1">TGL PENGAJUAN</th>
                        <th class="col-xs-1">TRANSFER</th>
                        <th class="col-xs-2">LAMPIRAN</th>
                        <th class="col-xs-2">DI AJUKAN OLEH</th>
                        <th class="col-xs-1">BANK</th>
                        <th class="col-xs-1">DETAIL</th>
                        <th class="col-xs-1">ACTION</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="8">Tidak ada pengajuan.</td>
                    </tr>
                </tbody>
            </table>
        </small>
    </div>
</div>