<?php if ( count($data) > 0) : ?>
	<?php foreach ($data['detail'] as $k_det => $v_det): ?>
		<tr class="v-center">
		    <td class="barang" data-kode="<?php echo $v_det['item']; ?>"><?php echo strtoupper($v_det['d_barang']['nama']); ?></td>
		    <td class="text-right jml_op"><?php echo ($v_det['jumlah'] > 0) ? angkaRibuan($v_det['jumlah']) : 0; ?></td>
		    <td class="text-right">
		    	<input type="text" class="form-control text-right jml_retur" data-tipe="integer" data-trigger="manual" data-toggle="tooltip" title="" data-required="1" onkeyup="rp.cek_jml_retur(this)" placeholder="Jumlah">
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