<?php if ( count($data) > 0 ): ?>
	<?php $no = 1; ?>
	<?php foreach ($data as $k_data => $v_data): ?>
		<tr class="v-center header" data-iddetrpah="<?php echo $v_data['id']; ?>" data-noplg="<?php echo $v_data['no_pelanggan']; ?>" data-pelanggan="<?php echo $v_data['pelanggan']; ?>" data-do="<?php echo strtoupper($v_data['no_do']); ?>" data-sj="<?php echo strtoupper($v_data['no_sj']); ?>">
			<td class="text-center no_urut" style="vertical-align: top;"><?php echo $no; ?></td>
			<td class="no_do">
				<?php echo $v_data['pelanggan'].'<br>'.strtoupper($v_data['no_do']); ?>
			</td>
			<!-- <td style="vertical-align: top;">
				<div class="col-xs-12 no-padding" style="padding-left: 8px; padding-right: 8px;">
		            <a name="dokumen" class="text-right hide sj" target="_blank" style="padding-right: 10px;"><i class="fa fa-file"></i></a>
		            <label class="" style="margin-bottom: 0px;">
		                <input style="display: none;" placeholder="Dokumen" class="file_lampiran no-check" type="file" onchange="rsm.showNameFile(this)" data-name="no-name" data-allowtypes="jpg|jpeg|png|JPG|JPEG|PNG" data-required="1">
		                <i class="glyphicon glyphicon-paperclip cursor-p" title="Attachment SJ"></i> 
		            </label>
		        </div>
			</td> -->
		</tr>
		<tr class="v-center detail">
			<td colspan="3" style="background-color: #ccc;">
				<table class="table table-bordered tbl_detail" style="margin-bottom: 0px;">
					<thead>
						<tr>
							<th class="col-xs-2" style="background-color: #adb3ff;">Ekor</th>
							<th class="col-xs-3" style="background-color: #adb3ff;">Tonase</th>
							<th class="col-xs-1" style="background-color: #adb3ff;">BB</th>
							<th class="col-xs-2 hide" style="background-color: #adb3ff;">Harga</th>
							<th class="col-xs-1" style="background-color: #adb3ff;">Action</th>
						</tr>
					</thead>
					<tbody>
						<tr class="rpah_top">
							<td>
								<input type="text" class="form-control text-right ekor" data-tipe="integer" onblur="rsm.hit_total();" data-required="1" style="height: fit-content; padding: 0px 3px 0px 3px;" maxlength="7" placeholder="Ekor"  value="0" />
							</td>
							<td>
								<input type="text" class="form-control text-right tonase" data-tipe="decimal" onblur="rsm.hit_total();" data-required="1" style="height: fit-content; padding: 0px 3px 0px 3px;" maxlength="10" placeholder="Tonase"  value="0" />
							</td>
							<td>
								<input type="text" class="form-control text-right bb" data-tipe="decimal" data-required="1" style="height: fit-content; padding: 0px 3px 0px 3px;" maxlength="5" placeholder="BB" value="0"  disabled />
							</td>
							<td class="hide">
								<input type="text" class="form-control text-right harga" data-tipe="integer" style="height: fit-content; padding: 0px 3px 0px 3px;" maxlength="7" placeholder="Harga" value="0"  />
							</td>
							<td rowspan="3">
								<div class="col-xs-12 no-padding">
									<div class="col-xs-6 no-padding text-center">
										<button type="button" class="btn btn-add-row btn-primary" onclick="rsm.add_row(this)"><i class="fa fa-plus"></i></button>
									</div>
									<div class="col-xs-6 no-padding text-center">
										<button type="button" class="btn btn-remove-row btn-danger" onclick="rsm.remove_row(this)"><i class="fa fa-times"></i></button>
									</div>
								</div>
							</td>
						</tr>
						<tr class="rpah_bottom">
							<th style="background-color: #adb3ff;">Jenis Ayam</th>
							<td colspan="2">
								<select class="jenis_ayam form-control" style="height: fit-content; padding: 0px 3px 0px 3px;" data-required="1">
									<?php foreach ($jenis_ayam as $k_ja => $v_ja): ?>
										<option value="<?php echo $k_ja ?>"><?php echo strtoupper($v_ja); ?></option>
									<?php endforeach ?>
								</select>
							</td>
						</tr>
						<tr class="rpah_bottom">
							<th style="background-color: #adb3ff;">No. Nota</th>
							<td colspan="2">
								<input type="text" class="form-control no_nota" placeholder="No. Nota" data-required="1" style="height: fit-content; padding: 0px 3px 0px 3px;" maxlength="15">
							</td>
						</tr>
					</tbody>
				</table>
			</td>
		</tr>
		<?php $no++; ?>
	<?php endforeach ?>
<?php else: ?>
	<tr>
		<td colspan="3">Data rencana penjualan tidak ditemukan.</td>
	</tr>
<?php endif ?>