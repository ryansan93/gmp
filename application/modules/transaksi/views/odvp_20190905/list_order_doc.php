<?php // cetak_r($data); ?>
<?php if ( count($data) > 0 ): ?>
	<?php foreach ($data as $k_data => $v_data): ?>
		<?php $odc = false; ?>
		<?php $tdc = false; ?>
		<?php $filter = null; ?>
		<?php if ( !empty($v_data['order_doc']) ) {
			$odc = true;
			$filter = 'ordered';
		} ?>

		<?php if ( !empty($v_data['order_doc']['terima_doc']) ) {
			$tdc = true;
			$filter = 'terima';
		} ?>
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
			<td><?php echo !empty($v_data['order_doc']) ? tglIndonesia( $v_data['order_doc']['tgl_submit'], '-', ' ' ) : '-'; ?></td>
			<td class="text-center no_order"><?php echo !empty($v_data['order_doc']) ? $v_data['order_doc']['no_order'] : '-'; ?></td>
			<td class="text-right"><?php echo !empty($v_data['order_doc']) ? angkaRibuan( $v_data['order_doc']['jml_ekor'], '-', ' ' ) : '-'; ?></td>
			<!-- <td><?php echo !empty($v_data['order_doc']) ? tglIndonesia( next_date($v_data['order_doc']['tgl_submit']), '-', ' ' ) : '-'; ?></td> -->
			<td><?php echo !empty($v_data['order_doc']['terima_doc']['kirim']) ? tglIndonesia( $v_data['order_doc']['terima_doc']['kirim'], '-', ' ' ) : '-'; ?></td>
			<td><?php echo !empty($v_data['order_doc']) ? angkaRibuan( $v_data['order_doc']['jml_ekor'], '-', ' ' ) : '-'; ?></td>
			<td><?php echo !empty($v_data['order_doc']['terima_doc']) ? dateTimeFormat( $v_data['order_doc']['terima_doc']['datang'], '-', ' ' ) : '-'; ?></td>
			<td><?php echo !empty($v_data['order_doc']['terima_doc']) ? angkaRibuan( $v_data['order_doc']['terima_doc']['jml_ekor'], '-', ' ' ) : '-'; ?></td>
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
						<a class="cursor-p" title="DETAIL TERIMA" onclick="odvp.terima_doc_view_form(this)" data-noreg="<?php echo $v_data['noreg']; ?>" data-terima="<?php echo $v_data['order_doc']['terima_doc']['no_terima']; ?>"><i class="fa fa-file"></i></a>
						&nbsp
						<a class="cursor-p" title="EDIT TERIMA" onclick="odvp.terima_doc_edit_form(this)" data-noreg="<?php echo $v_data['noreg']; ?>" data-terima="<?php echo $v_data['order_doc']['terima_doc']['no_terima']; ?>"><i class="fa fa-edit"></i></a>
					<?php endif ?>
				<?php endif ?>
			</td>
		</tr>
	<?php endforeach ?>
<?php else: ?>
	<tr>
		<td class="text-center" colspan="13">Data tidak ditemukan.</td>
	</tr>
<?php endif ?>