<?php if ( count($data) > 0) : ?>
	<?php // foreach ($data['detail'] as $k_det => $v_det): ?>
	<?php foreach ($data as $k_det => $v_det): ?>
		<tr class="v-center">
		    <!-- <td class="barang" data-kode="<?php echo $v_det['item']; ?>"><?php echo strtoupper($v_det['d_barang']['nama']); ?></td> -->
		    <td class="barang" data-kode="<?php echo $v_det['item']; ?>"><?php echo strtoupper($v_det['nama_barang']); ?></td>
		    <td class="text-right jml_ov"><?php echo angkaDecimal($v_det['jumlah']); ?></td>
		    <td class="text-right">
		    	<input type="text" class="form-control text-right jml_retur" data-tipe="decimal" data-trigger="manual" data-toggle="tooltip" title="" data-required="1" onblur="rv.cek_jml_retur(this)" placeholder="Jumlah">
		    </td>
			<td class="text-right">
		    	<input type="text" class="form-control text-right nilai_retur" data-tipe="decimal" data-trigger="manual" data-toggle="tooltip" title="" data-required="1" placeholder="Nilai">
		    </td>
		    <td class="text-left">
		    	<input type="text" class="form-control text-left kondisi" data-required="1" placeholder="Kondisi">
		    </td>
		</tr>
	<?php endforeach ?>
<?php else: ?>
	<tr>
		<td colspan="6">Data tidak ditemukan.</td>
	</tr>
<?php endif ?>