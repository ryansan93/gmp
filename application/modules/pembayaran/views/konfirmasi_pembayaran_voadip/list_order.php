<?php if ( !empty($data) && count($data) > 0 ): ?>
	<?php foreach ($data as $k_data => $v_data): ?>
		<tr class="cursor-p header" title="Klik untuk melihat detail" data-supplier="<?php echo $v_data['kode_supplier']; ?>">
			<td class="text-center tgl_sj" data-val="<?php echo $v_data['tgl_sj']; ?>"><?php echo tglIndonesia($v_data['tgl_sj'], '-', ' '); ?></td>
			<td class="kota_kab" data-val="<?php echo $v_data['id_kota_kab']; ?>"><?php echo strtoupper($v_data['kota_kab']); ?></td>
			<td class="perusahaan" data-val="<?php echo $v_data['id_perusahaan']; ?>"><?php echo strtoupper($v_data['perusahaan']); ?></td>
			<td class="supplier" data-val="<?php echo $v_data['kode_supplier']; ?>"><?php echo strtoupper($v_data['supplier']); ?></td>
			<td class="no_order" data-val="<?php echo $v_data['no_order']; ?>"><?php echo strtoupper($v_data['no_order']); ?></td>
			<td class="no_sj" data-val="<?php echo $v_data['no_sj']; ?>"><?php echo strtoupper($v_data['no_sj']); ?></td>
			<td class="text-right jumlah" data-val="<?php echo $v_data['jumlah']; ?>"><?php echo angkaDecimal($v_data['jumlah']); ?></td>
			<td class="text-right total" data-val="<?php echo $v_data['total']; ?>"><?php echo angkaDecimal($v_data['total']); ?></td>
			<td class="text-center">
				<?php $checked = !empty($v_data['checked']) ? 'checked="checked"' : '';?>
				<input type="checkbox" class="cursor-p check" target="list_data" <?php echo $checked; ?> >
			</td>
		</tr>
		<tr class="detail" style="display: none;">
			<td colspan="9" style="background-color: #ccc;">
				<table class="table table-bordered" style="margin-bottom: 0px;">
					<thead>
						<tr>
							<th class="col-xs-3" style="background-color: #adb3ff;">Tujuan</th>
							<th class="col-xs-3" style="background-color: #adb3ff;">Jenis OVK</th>
							<th class="col-xs-2" style="background-color: #adb3ff;">Jumlah</th>
							<th class="col-xs-2" style="background-color: #adb3ff;">Harga</th>
							<th class="col-xs-2" style="background-color: #adb3ff;">Sub Total</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($v_data['detail'] as $k_det => $v_det): ?>
							<tr>
								<td class="gudang" data-val="<?php echo $v_det['id_tujuan']; ?>"><?php echo strtoupper($v_det['tujuan']); ?></td>
								<td class="barang" data-val="<?php echo $v_det['kode_brg']; ?>"><?php echo strtoupper($v_det['nama_brg']); ?></td>
								<td class="text-right jumlah" data-val="<?php echo $v_det['jumlah']; ?>"><?php echo angkaDecimal($v_det['jumlah']); ?></td>
								<td class="text-right harga" data-val="<?php echo $v_det['harga']; ?>"><?php echo angkaDecimal($v_det['harga']); ?></td>
								<td class="text-right total" data-val="<?php echo $v_det['total']; ?>"><?php echo angkaDecimal($v_det['total']); ?></td>
							</tr>
						<?php endforeach ?>
					</tbody>
				</table>
			</td>
		</tr>
	<?php endforeach ?>
<?php else: ?>
	<tr>
		<td colspan="10">Data tidak ditemukan.</td>
	</tr>
<?php endif ?>