<div class="modal-header no-padding">
	<span class="modal-title"><b>List Nekropsi</b></span>
	<button type="button" class="close" data-dismiss="modal">&times;</button>
</div>
<div class="modal-body">
    <div class="row detailed">
		<div class="col-lg-12 no-padding detailed">
        	<small>
	        	<table class="table table-bordered" style="margin-bottom: 0px;">
	        		<thead>
	        			<tr>
	        				<th class="col-xs-5">Parameter</th>
	        				<th class="col-xs-6">Keterangan</th>
	        				<th class="col-xs-1">Lampiran</th>
	        			</tr>
	        		</thead>
	        		<tbody>
	        			<?php if ( !empty($data) ): ?>
		        			<?php foreach ($data as $k_data => $v_data): ?>
			        			<tr>
			        				<td><?php echo $v_data['d_nekropsi']['keterangan']; ?></td>
			        				<td><?php echo $v_data['keterangan']; ?></td>
			        				<td>
			        					<div class="col-xs-12 no-padding">
			        						<?php
			        							$_url = array();
			        							foreach ($v_data['foto_nekropsi'] as $k_fn => $v_fn) {
			        								array_push($_url, $v_fn['path']);
			        							}

			        							$json_url = json_encode($_url, JSON_FORCE_OBJECT);
			        						?>
											<div class="col-xs-12 no-padding preview_file_attachment" data-report="report" data-title="Preview Nekropsi" onclick="lhk.preview_file_attachment(this)" data-url='<?php echo $json_url; ?>' style="margin-top: 0px;">
												<label class="col-xs-12 no-padding" style="margin-bottom: 0px;">
								                	<i class="fa fa-camera cursor-p col-xs-12 text-center" style="padding-top: 5px;"></i> 
								              	</label>
											</div>
										</div>
			        				</td>
			        			</tr>
		        			<?php endforeach ?>
	        			<?php else: ?>
	        				<tr>
	        					<td colspan="3">Data tidak ditemukan.</td>
	        				</tr>
	        			<?php endif ?>
	        		</tbody>
	        	</table>
	        </small>
    	</div>
    </div>
</div>