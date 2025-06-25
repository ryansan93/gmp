<?php // cetak_r($data); ?>
<?php if ( count($data) > 0 ): ?>
	<?php foreach ($data as $k_data => $v_data): ?>
		<?php $odc = false; ?>
		<?php $tdc = false; ?>
		<?php $filter = null; ?>
		<?php if ( !empty($v_data['order_doc']) ): ?>
			<?php if ( !empty($v_data['order_doc']) ) {
				$odc = true;
				$filter = 'ordered';
			} ?>
			<?php foreach ($v_data['order_doc'] as $k_od => $v_od): ?>
				<?php if ( !empty($v_od['terima_doc']) ) {
					$tdc = true;
					$filter = 'terima';
				} ?>
				<tr class="search" data-filter="<?php echo $filter; ?>">
					<td class="tgl_docin" data-tgl="<?php echo $v_data['tgl_docin']; ?>"><?php echo tglIndonesia($v_data['tgl_docin'], '-', ' '); ?></td>
					<td><?php echo $v_data['d_kandang']['d_unit']['nama']; ?></td>
					<td class="nama_mitra"><?php echo $v_data['mitra']['d_mitra']['nama'].'<br>'.$v_data['noreg']; ?></td>
					<td class="text-center kandang">
						<?php 
							$kdg = $v_data['d_kandang']['kandang'];
							if ( strlen($kdg) == 1 ) {
								$kdg = '0'.$kdg;
							}

							echo $kdg; 
						?>
					</td>
					<td class="text-right populasi"><?php echo angkaRibuan($v_data['populasi']); ?></td>
					<td><?php echo tglIndonesia( $v_od['tgl_submit'], '-', ' ' ); ?></td>
					<td class="text-center no_order"><?php echo $v_od['no_order']; ?></td>
					<td class="text-right"><?php echo !empty($v_od) ? angkaRibuan( $v_od['jml_ekor'], '-', ' ' ) : '-'; ?></td>
					<!-- <td><?php echo !empty($v_od) ? tglIndonesia( next_date($v_od['tgl_submit']), '-', ' ' ) : '-'; ?></td> -->
					<td><?php echo !empty($v_od['terima_doc']['kirim']) ? tglIndonesia( $v_od['terima_doc']['kirim'], '-', ' ' ) : '-'; ?></td>
					<td><?php echo angkaRibuan( $v_od['jml_ekor'], '-', ' ' ); ?></td>
					<td><?php echo !empty($v_od['terima_doc']) ? dateTimeFormat( $v_od['terima_doc']['datang'], '-', ' ' ) : '-'; ?></td>
					<td><?php echo !empty($v_od['terima_doc']) ? angkaRibuan( $v_od['terima_doc']['jml_ekor'], '-', ' ' ) : '-'; ?></td>
					<td class="text-center">
						<?php if ( !$odc ): ?>
							<a class="cursor-p" title="ADD ORDER" onclick="odvp.order_doc_form(this)" data-noreg="<?php echo $v_data['noreg']; ?>"><i class="fa fa-plus"></i></a>
							&nbsp
						<?php else: ?>
							<a class="cursor-p" title="DETAIL ORDER" onclick="odvp.order_doc_view_form(this)" data-noreg="<?php echo $v_data['noreg']; ?>"><i class="fa fa-file"></i></a>
							&nbsp
							<a class="cursor-p" title="EDIT ORDER" onclick="odvp.order_doc_edit_form(this)" data-noreg="<?php echo $v_data['noreg']; ?>"><i class="fa fa-edit"></i></a>
						<?php endif ?>
					</td>
					<td class="text-center">
						<?php if ( $odc ): ?>
							<?php if ( !$tdc ): ?>
								<a class="cursor-p" title="ADD TERIMA" onclick="odvp.terima_doc_form(this)" data-noreg="<?php echo $v_data['noreg']; ?>"><i class="fa fa-plus"></i></a>
								&nbsp
							<?php else: ?>
								<!-- <a href="#" title="EDIT TERIMA"><i class="fa fa-edit"></i></a> -->
								<a class="cursor-p" title="DETAIL TERIMA" onclick="odvp.terima_doc_view_form(this)" data-noreg="<?php echo $v_data['noreg']; ?>" data-id="<?php echo $v_od['terima_doc']['id']; ?>"><i class="fa fa-file"></i></a>
								&nbsp
								<a class="cursor-p" title="EDIT TERIMA" onclick="odvp.terima_doc_edit_form(this)" data-noreg="<?php echo $v_data['noreg']; ?>" data-id="<?php echo $v_od['terima_doc']['id']; ?>"><i class="fa fa-edit"></i></a>
							<?php endif ?>
						<?php endif ?>
					</td>
				</tr>
			<?php endforeach ?>
		<?php else: ?>
			<tr class="search" data-filter="<?php echo $filter; ?>">
				<td class="tgl_docin" data-tgl="<?php echo $v_data['tgl_docin']; ?>"><?php echo tglIndonesia($v_data['tgl_docin'], '-', ' '); ?></td>
				<td><?php echo $v_data['d_kandang']['d_unit']['nama']; ?></td>
				<td class="nama_mitra"><?php echo $v_data['mitra']['d_mitra']['nama']; ?></td>
				<td class="text-center kandang">
					<?php 
						$kdg = $v_data['d_kandang']['kandang'];
						if ( strlen($kdg) == 1 ) {
							$kdg = '0'.$kdg;
						}

						echo $kdg; 
					?>
				</td>
				<td class="text-right populasi"><?php echo angkaRibuan($v_data['populasi']); ?></td>
				<td><?php echo '-'; ?></td>
				<td class="text-center no_order"><?php echo '-'; ?></td>
				<td class="text-right"><?php echo '-'; ?></td>
				<!-- <td><?php echo !empty($v_od) ? tglIndonesia( next_date($v_od['tgl_submit']), '-', ' ' ) : '-'; ?></td> -->
				<td><?php echo '-'; ?></td>
				<td><?php echo '-'; ?></td>
				<td><?php echo '-'; ?></td>
				<td><?php echo '-'; ?></td>
				<td class="text-center">
					<?php if ( !$odc ): ?>
						<a class="cursor-p" title="ADD ORDER" onclick="odvp.order_doc_form(this)" data-noreg="<?php echo $v_data['noreg']; ?>"><i class="fa fa-plus"></i></a>
						&nbsp
					<?php else: ?>
						<a class="cursor-p" title="DETAIL ORDER" onclick="odvp.order_doc_view_form(this)" data-noreg="<?php echo $v_data['noreg']; ?>"><i class="fa fa-file"></i></a>
						&nbsp
						<a class="cursor-p" title="EDIT ORDER" onclick="odvp.order_doc_edit_form(this)" data-noreg="<?php echo $v_data['noreg']; ?>"><i class="fa fa-edit"></i></a>
					<?php endif ?>
				</td>
				<td class="text-center">
					<?php if ( $odc ): ?>
						<?php if ( !$tdc ): ?>
							<a class="cursor-p" title="ADD TERIMA" onclick="odvp.terima_doc_form(this)" data-noreg="<?php echo $v_data['noreg']; ?>"><i class="fa fa-plus"></i></a>
							&nbsp
						<?php else: ?>
							<!-- <a href="#" title="EDIT TERIMA"><i class="fa fa-edit"></i></a> -->
							<a class="cursor-p" title="DETAIL TERIMA" onclick="odvp.terima_doc_view_form(this)" data-noreg="<?php echo $v_data['noreg']; ?>" data-id="<?php echo $v_od['terima_doc']['id']; ?>"><i class="fa fa-file"></i></a>
							&nbsp
							<a class="cursor-p" title="EDIT TERIMA" onclick="odvp.terima_doc_edit_form(this)" data-noreg="<?php echo $v_data['noreg']; ?>" data-id="<?php echo $v_od['terima_doc']['id']; ?>"><i class="fa fa-edit"></i></a>
						<?php endif ?>
					<?php endif ?>
				</td>
			</tr>
		<?php endif ?>
	<?php endforeach ?>
<?php else: ?>
	<tr>
		<td class="text-center" colspan="13">Data tidak ditemukan.</td>
	</tr>
<?php endif ?>