<div class="modal-header no-padding">
	<span class="modal-title"><b>List Solusi</b></span>
	<button type="button" class="close" data-dismiss="modal">&times;</button>
</div>
<div class="modal-body">
    <div class="row detailed">
		<div class="col-lg-12 no-padding detailed">
        	<small>
				<table class="table table-bordered" style="margin-bottom: 0px;">
					<thead>
						<tr>
							<th class="col-xs-10">Parameter</th>
						</tr>
					</thead>
					<tbody>
						<?php if ( !empty($data) ): ?>
							<?php foreach ($data as $k_data => $v_data): ?>
				    			<tr>
				    				<td><?php echo $v_data['d_solusi']['keterangan']; ?></td>
				    			</tr>
							<?php endforeach ?>
						<?php else: ?>
							<tr>
			    				<td>Data tidak ditemukan.</td>
			    			</tr>
						<?php endif ?>
					</tbody>
				</table>
			</small>
    	</div>
    </div>
</div>