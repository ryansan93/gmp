<?php if ( count($data) > 0 ): ?>
	<?php $idx = 0; ?>
	<?php foreach ($data as $k_data => $v_data): ?>
		<tr class="data">
			<td class="text-center no_sj"><?php echo strtoupper($v_data['no_sj']); ?></td>
			<td class="text-center nopol"><?php echo strtoupper($v_data['nopol']); ?></td>
			<td>
				<div class="col-xs-12 no-padding text-center">
					<div class="col-xs-5 text-center no-padding">
						<!-- <button type="button" class="btn btn-default pull-left" data-toggle="modal" data-target="#mySolusi"><i class="fa fa-list-alt" aria-hidden="true"></i> Solusi</button> -->
						<i class="fa fa-folder cursor-p" data-toggle="modal" data-target="#myPakan<?php echo $idx; ?>"></i>
						<!-- Modal Pakan -->
						<div id="myPakan<?php echo $idx; ?>" class="modal fade my-style myPakan" role="dialog">
							<div class="modal-dialog">
							    <!-- Modal content-->
							    <div class="modal-content">
									<div class="modal-header">
										<h4 class="modal-title">List Pakan</h4>
										<button type="button" class="close" data-dismiss="modal">&times;</button>
									</div>
									<div class="modal-body">
								        <div class="panel-body no-padding">
								        	<div class="col-xs-12 no-padding">
									        	<table class="table table-bordered tbl_list_pakan" style="margin-bottom: 0px;">
									        		<thead>
									        			<tr>
									        				<th class="col-xs-8">Nama</th>
									        				<th class="col-xs-4">Jumlah Zak</th>
									        			</tr>
									        		</thead>
									        		<tbody>
									        			<?php if ( count($v_data['detail']) > 0 ): ?>
										        			<?php foreach ($v_data['detail'] as $k_det => $v_det): ?>
										        				<tr>
											        				<td class="text-left"><?php echo strtoupper($v_det['d_barang']['nama']); ?></td>
											        				<td>
											        					<input type="text" class="form-control text-right jml_zak" data-id="<?php echo $v_det['d_barang']['kode']; ?>" placeholder="Jumlah" data-tipe="integer" onblur="lhk.hit_total_pakan(this)">
											        				</td>
											        			</tr>
										        			<?php endforeach ?>
										        		<?php else: ?>
										        			<tr>
										        				<td colspan="2">Data tidak ditemukan.</td>
										        			</tr>
									        			<?php endif ?>
									        		</tbody>
									        	</table>
								        	</div>
								        </div>
								    </div>
								</div>
							</div>
						</div>
					</div>
					<div class="col-xs-2 text-center no-padding">|</div>
					<div class="col-xs-5 text-center no-padding"><span class="total_zak">0</span></div>
				</div>
			</td>
			<td class="text-center">
				<div class="col-xs-12 no-padding attachment">
					<label class="">
						<input style="display: none;" class="file_lampiran no-check" type="file" accept="image/*" name="foto_penerimaan_pakan" capture="camera" onchange="lhk.showNameFile(this)" data-name="no-name" data-required="1" />
	                	<!-- <input style="display: none;" placeholder="Dokumen" class="file_lampiran no-check" type="file" onchange="lhk.showNameFile(this)" data-name="no-name" data-required="1" data-allowtypes="doc|pdf|docx"> -->
	                	<i class="fa fa-camera cursor-p" title="Attachment BPB PAKAN"></i> 
	              	</label>
					<a name="dokumen" class="text-right hide" target="_blank" style="padding-left: 10px;"><i class="fa fa-file"></i></a>
				</div>
			</td>
		</tr>
		<?php $idx++; ?>
	<?php endforeach ?>
<?php else: ?>
	<tr>
		<td colspan="4">Data tidak ditemukan.</td>
	</tr>
<?php endif ?>