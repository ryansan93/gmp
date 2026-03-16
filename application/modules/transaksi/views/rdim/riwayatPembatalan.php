<div class="col-xs-12 no-padding">
    <form class="form-horizontal">
        <div class="col-xs-1 no-padding">
            <label class="control-label"> Plasma </label>
        </div>
        <div class="col-xs-3">
            <select class="form-control mitra">
                <option value="all">ALL</option>
                <?php foreach ($mitra as $key => $value) { ?>
                    <option value="<?php echo $value['nomor']; ?>"><?php echo strtoupper($value['nama'].' ('.$value['kab_kota'].')'); ?></option>
                <?php } ?>
            </select>
        </div>
        <div class="col-xs-1 no-padding">
            <button type="button" class="col-xs-12 btn btn-primary" onclick="rdim.getListsPembatalan()"><i class="fa fa-search"></i> Tampilkan</button>
        </div>
        <div class="col-xs-7 no-padding">
            <button type="button" class="btn btn-success pull-right" onclick="rdim.addFormPembatalan()"><i class="fa fa-plus"></i> Tambah</button>
        </div>
    </form>
</div>
<div class="col-xs-12 no-padding">
    <hr style="margin-top: 10px; margin-bottom: 10px;">
</div>
<div class="col-xs-12 no-padding" style="margin-top: 10px;">
    <span>* Klik pada baris untuk melihat detail</span>
    <table id="tbl_pembatalan" class="table table-hover table-bordered custom_table table-form">
        <thead>
            <tr>
                <th class="col-xs-2">Periode</th>
                <th class="col-xs-3">Mitra</th>
                <th class="col-xs-1">Noreg</th>
                <th class="col-xs-2">Document</th>
                <th class="col-xs-4">Alasan batal</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="5">pilih periode untuk menampilkan data</td>
            </tr>
        </tbody>
    </table>
</div>