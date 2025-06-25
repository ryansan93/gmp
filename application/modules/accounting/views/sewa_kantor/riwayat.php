<div class="col-xs-12 no-padding">
    <div class="col-xs-12 no-padding">
        <button type="button" class="col-xs-12 btn btn-success" onclick="sk.changeTabActive(this)" data-href="action" data-edit="" data-id=""><i class="fa fa-plus"></i> Tambah</button>
    </div>
    <div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
    <div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
        <div class="col-xs-12 no-padding">
            <label class="control-label">Unit</label>
        </div>
        <div class="col-xs-12 no-padding">
            <select class="form-control unit" data-required="1" multiple="multiple">
                <?php foreach( $unit as $key => $value ) : ?>
                    <option value="<?php echo $value['kode']; ?>"><?php echo strtoupper($value['kode'].' | '.$value['nama']); ?></option>
                <?php endforeach ?>
            </select>
        </div>
    </div>
    <div class="col-xs-12 no-padding">
        <button type="button" class="col-xs-12 btn btn-primary" onclick="sk.getLists()"><i class="fa fa-search"></i> Tampilkan</button>
    </div>
    <div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
    <div class="col-xs-12 no-padding">
        <small>
            <table class="table table-bordered" style="margin-bottom: 0px;">
                <thead>
                    <tr>
                        <th class="col-xs-2">Perusahaan</th>
                        <th class="col-xs-1">Unit</th>
                        <th class="col-xs-2">Nominal</th>
                        <th class="col-xs-1">Lama (Bln)</th>
                        <th class="col-xs-1">Mulai</th>
                        <th class="col-xs-1">Akhir</th>
                        <th class="col-xs-4">Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="7">Data tidak ditemukan.</td>
                    </tr>
                </tbody>
            </table>
        </small>
    </div>
</div>