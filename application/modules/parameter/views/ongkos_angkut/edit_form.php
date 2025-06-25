<?php // cetak_r( $data ); ?>

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
            <!-- <select class="form-control jns_oa" data-required="1" onchange="oa.loadHeader(this)">
                <option value="pakan">OA Pakan</option>
                <option value="doc">OA DOC</option>
            </select> -->

            <select class="form-control jns_oa" data-required="1" onchange="oa.loadHeader(this)">
				<?php $selected_pakan = $selected_doc = ''; 
					if ( $data['jns_oa'] == 'pakan' ) {
						$selected_pakan = 'selected';
					} else {
						$selected_doc = 'selected';
					}
				?>
				<option <?php echo $selected_pakan; ?> value="pakan">OA Pakan</option>
				<option <?php echo $selected_doc; ?> value="doc">OA DOC</option>
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
          	<span class="file"><?php echo $data['lampiran']['filename']; ?></span>
			<label class="col-sm-1 text-left" data-idnama="">
            	<input style="display: none;" placeholder="Dokumen" class="file_lampiran" type="file" onchange="showNameFile(this)" data-name="no-name" data-allowtypes="doc|pdf|docx|jpg|jpeg|png" data-old="<?php echo $data['lampiran']['path']; ?>">
            	<i class="glyphicon glyphicon-paperclip cursor-p" title="Lampiran OA"></i> 
          	</label>
			<!-- <label class="col-sm-1 text-left" data-idnama="">
				<input required="required" type="file" onchange="showNameFile(this)" class="file_lampiran oa" data-required="1" name="lampiran_dds" data-allowtypes="doc|pdf|docx" style="display: none;" placeholder="Lampiran OA">
				<i class="glyphicon glyphicon-paperclip cursor-p" title="Lampiran OA"></i>
			</label> -->
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
				<?php foreach ($data['detail'] as $key => $value): ?>
					<tr class="data">
						<td class="col-sm-2 kab">
							<select class="form-control chosen-kab" onchange="oa.loadDataKec(this)">
								<?php foreach ($lokasi_kb_kt as $key => $val): ?>
									<?php $selected = ''; ?>
									<?php if ( $val['id'] == $value['wilayah'] ):
											$selected = 'selected';
									endif ?>
									<option <?php echo $selected; ?> value="<?php echo $val['id']; ?>"><?php echo strtoupper($val['nama']); ?></option>
								<?php endforeach ?>
							</select>
						</td>
						<td class="col-sm-2 kec">
							<select class="form-control chosen-kec" data-required="1" onchange="oa.loadContentKec(this)">
								<?php $selected_except = $selected_all = $selected = ''; ?>
								<?php $option = ''; ?>
								<?php 
									if ( $value['kecamatan'] == '-1' ) {
										$selected_except = 'selected';
									} else if ( $value['kecamatan'] == '0' ) {
										$selected_all = 'selected';
									} else {
										$selected = 'selected';
										$option = '<option '.$selected.' value='.$value['kecamatan'].'>-</option>';
									}
								?>
								<option <?php echo $selected_except; ?> value="-1">&nbsp</option>
								<option <?php echo $selected_all; ?> value="0">ALL</option>
								<?php echo $option; ?>
							</select>
							<select class="hide kec">
							</select>
						</td>
						<td class="col-sm-1">
							<input type="text" class="form-control text-right lama1" data-tipe="decimal" maxlength="6" value="<?php echo angkaDecimal($value['ongkos_lama']); ?>" />
						</td>
						<td class="col-sm-1">
							<input type="text" class="form-control text-right lama2" data-tipe="decimal" maxlength="6" value="<?php echo angkaDecimal($value['ongkos_lama2']); ?>" />
						</td>
						<td class="col-sm-1">
							<input type="text" class="form-control text-right baru1" data-tipe="decimal" maxlength="6" value="<?php echo angkaDecimal($value['ongkos']); ?>" data-required="1" />
						</td>
						<td class="col-sm-1">
							<input type="text" class="form-control text-right baru2" data-tipe="decimal" maxlength="6" value="<?php echo angkaDecimal($value['ongkos2']); ?>" data-required="1" />
						</td>
						<td class="action text-center col-sm-1">
							<button type="button" class="btn btn-sm btn-danger remove" onclick="oa.removeRowTable(this)"><i class="fa fa-minus"></i></button>
							<button type="button" class="btn btn-sm btn-default add" onclick="oa.addRowTable(this)"><i class="fa fa-plus"></i></button>
						</td>
					</tr>
				<?php endforeach ?>
			</tbody>
		</table>
    </div>
    <div class="col-sm-12">
        <button type="button" class="btn btn-primary edit" href='#oa' onclick="oa.edit(this)" data-id="<?php echo $data['id']; ?>" data-nomor="<?php echo $data['nomor']; ?>" data-version="<?php echo $data['version']; ?>"><i class="fa fa-edit"></i> Edit</button>
    </div>
</div>