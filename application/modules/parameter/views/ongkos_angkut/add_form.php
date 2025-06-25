<div class="row new-line">
    <div class="col-sm-6 pull-left">
        <div class="col-md-2 text-left no-padding">
            <h5>Tanggal Berlaku</h5>
        </div>
        <div class="col-md-4">
            <div class="input-group date" id="datetimepicker1" name="tanggal-berlaku">
		        <input type="text" class="form-control text-center" data-required="1" />
		        <span class="input-group-addon">
		            <span class="glyphicon glyphicon-calendar"></span>
		        </span>
		    </div>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="col-md-3 pull-right no-padding">
            <select class="form-control jns_oa" data-required="1" onchange="oa.loadHeader(this)">
                <option value="pakan">OA Pakan</option>
                <option value="doc">OA DOC</option>
            </select>
        </div>
    </div>
</div>
<div class="row new-line">
</div>
<div class="row new-line attachement">
    <div class="col-sm-12">
        <div class="col-md-6 no-padding" style="margin-top: 10px; margin-bottom: 10px;">
            <div class="col-sm-2 no-padding">Lampiran OA</div>
			<label class="col-sm-1 text-left" data-idnama="">
				<input required="required" type="file" onchange="showNameFile(this)" class="file_lampiran oa" data-required="1" name="lampiran_dds" data-allowtypes="doc|pdf|docx" style="display: none;" placeholder="Lampiran OA">
				<i class="glyphicon glyphicon-paperclip cursor-p" title="Lampiran OA"></i>
			</label>
        </div>
    </div>
</div>
<div class="row new-line loading">
    <div class="cssload-container">
        <div class="cssload-speeding-wheel"></div>
    </div>
</div>
<div class="row new-line data_oa">
    <div class="col-sm-12">
        <table class="table table-bordered custom_table oa">
            <thead>
                <tr>
                    <th class="text-center" rowspan="3">Kabupaten / Kota</th>
                    <th class="text-center" rowspan="3">Kecamatan</th>
                    <th class="head text-center" colspan="4">Tarif / Kg</th>
                    <th class="text-center" rowspan="3"></th>
                </tr>
                <tr>
                    <th class="text-center" colspan="2">Lama</th>
                    <th class="text-center" colspan="2">Baru</th>
                </tr>
                <tr>
                    <th class="head1 text-center">Kediri</th>
                    <th class="head2 text-center">Pasuruan</th>
                    <th class="head1 text-center">Kediri</th>
                    <th class="head2 text-center">Pasuruan</th>
                </tr>
            </thead>
            <tbody>
                <tr class="data">
                    <td class="col-sm-2 kab">
                        <select class="form-control chosen-kab" onchange="oa.loadDataKec(this)">
                            <?php foreach ($lokasi_kb_kt as $key => $val): ?>
                                <option value="<?php echo $val['id']; ?>"><?php echo strtoupper($val['nama']); ?></option>
                            <?php endforeach ?>
                        </select>
                    </td>
                    <td class="col-sm-2 kec">
                        <select class="form-control chosen-kec" data-required="1" onchange="oa.loadContentKec(this)">
                            <option value="-1">&nbsp</option>
                            <option value="0">ALL</option>
                        </select>
                        <select class="hide kec">
                        </select>
                    </td>
                    <td class="col-sm-1">
                        <input type="text" class="form-control text-right lama1" data-tipe="decimal" maxlength="6" />
                    </td>
                    <td class="col-sm-1">
                        <input type="text" class="form-control text-right lama2" data-tipe="decimal" maxlength="6" />
                    </td>
                    <td class="col-sm-1">
                        <input type="text" class="form-control text-right baru1" data-tipe="decimal" maxlength="6" data-required="1" />
                    </td>
                    <td class="col-sm-1">
                        <input type="text" class="form-control text-right baru2" data-tipe="decimal" maxlength="6" data-required="1" />
                    </td>
                    <td class="action text-center col-sm-1">
                        <button type="button" class="btn btn-sm btn-danger remove hide" onclick="oa.removeRowTable(this)"><i class="fa fa-minus"></i></button>
                        <button type="button" class="btn btn-sm btn-default add" onclick="oa.addRowTable(this)"><i class="fa fa-plus"></i></button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="col-sm-12">
        <button type="button" class="btn btn-primary save" href='#oa' onclick="oa.save(this)"><i class="fa fa-save"></i> Simpan</button>
    </div>
</div>