<div class="modal-header no-padding">
	<span class="modal-title"><b>List Sekat</b></span>
	<button type="button" class="close" data-dismiss="modal">&times;</button>
</div>
<div class="modal-body">
    <div class="row detailed">
		<div class="col-lg-12 no-padding detailed">
        	<small>
				<table class="table table-bordered" style="margin-bottom: 0px;">
					<thead>
						<tr>
							<th class="col-xs-3">Sekat Ke</th>
							<th class="col-xs-5">BB Rata2 (Kg)</th>
						</tr>
					</thead>
					<tbody>
						<?php if ( !empty($data) ): ?>
							<?php $idx = 0; ?>
							<?php foreach ($data as $k_data => $v_data): ?>
								<?php $idx++; ?>
								<tr>
									<td class="text-center no_urut"><?php echo $idx; ?></td>
									<td class="text-right"><?php echo angkaDecimalFormat($v_data['bb'], 3); ?></td>
								</tr>
							<?php endforeach ?>
						<?php else: ?>
							<tr>
								<td colspan="2">Data tidak ditemuka.</td>
							</tr>
						<?php endif ?>
					</tbody>
				</table>
			</small>
    	</div>
    </div>
</div>