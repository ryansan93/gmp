<?php foreach ($data_nekropsi as $k_ln => $v_ln): ?>
	<tr data-id="<?php echo $v_ln['id']; ?>">
		<td><?php echo $v_ln['d_nekropsi']['keterangan']; ?></td>
		<td><?php echo $v_ln['keterangan']; ?></td>
		<td>
			<?php if ( !empty($v_ln['foto_nekropsi']) ): ?>
				<div class="col-xs-12 no-padding">
					<?php
						$_url = array();
						foreach ($v_ln['foto_nekropsi'] as $k_fn => $v_fn) {
							array_push($_url, $v_fn['path']);
						}

						$json_url = json_encode($_url, JSON_FORCE_OBJECT);
					?>
					<div class="col-xs-12 no-padding preview_file_attachment" data-title="Preview Nekropsi" onclick="lhk.preview_file_attachment(this)" data-url='<?php echo $json_url; ?>' style="margin-top: 0px;">
						<label class="col-xs-12 no-padding">
		                	<i class="fa fa-file cursor-p col-xs-12 text-center"></i> 
		              	</label>
					</div>
				</div>
			<?php else: ?>
				<div class="col-xs-12 no-padding">
					<div class="col-xs-12 no-padding attachment" style="margin-top: 0px;">
						<label class="col-xs-12 no-padding">
							<input style="display: none;" class="file_lampiran no-check" multiple="multiple" type="file" name="foto_nekropsi" data-name="name" data-required="1" onchange="lhk.cek_file_exist(this)" />
		                	<i class="fa fa-camera cursor-p col-xs-12 text-center" title="Foto Nekropsi"></i> 
		              	</label>
						<a name="dokumen" class="text-right hide" target="_blank" style="padding-right: 10px;"></a>
					</div>
					<div class="col-xs-12 no-padding preview_file_attachment" data-title="Preview Nekropsi" onclick="lhk.preview_file_attachment(this)" style="margin-top: 0px;">
						<label class="col-xs-12 no-padding">
		                	<i class="fa fa-file cursor-p col-xs-12 text-center"></i> 
		              	</label>
					</div>
					<div class="col-xs-12 no-padding upload hide" data-title="Upload" onclick="lhk.upload_nekropsi(this)" style="margin-top: 0px;">
						<label class="col-xs-12 no-padding">
		                	<i class="fa fa-upload cursor-p col-xs-12 text-center"></i> 
		              	</label>
					</div>
				</div>
			<?php endif ?>
		</td>
	</tr>
<?php endforeach ?>