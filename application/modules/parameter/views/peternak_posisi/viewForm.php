<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
	<div class="col-xs-12 no-padding">
		<label class="control-label">Nama Plasma</label>
	</div>
	<div class="col-xs-12 no-padding not-plg-baru">
		<span><?php echo $data['nama_plasma']; ?></span>
	</div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
	<div class="col-xs-12 no-padding">
		<label class="control-label">Kandang</label>
	</div>
	<div class="col-xs-12 no-padding not-plg-baru">
		<span><?php echo (isset($data['kandang']) && !empty($data['kandang'])) ? $data['kandang'] : '-'; ?></span>
	</div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
	<div class="col-xs-12 no-padding">
		<label class="control-label">Alamat Plasma</label>
	</div>
	<div class="col-xs-12 no-padding">
		<span><?php echo $data['alamat_plasma']; ?></span>
	</div>
</div>
<div class="col-xs-12 no-padding">
	<hr style="margin-top: 10px; margin-bottom: 10px;">
</div>
<div class="col-xs-12 no-padding">
	<table class="table table-bordered" style="margin-bottom: 0px;">
		<tbody>
			<tr>
				<td class="col-xs-4 data lat_long">
					Lokasi
				</td>
				<td class="col-xs-8">
					<?php if ( $isMobile ): ?>
						<a class="cursor-p" href="geo:0, 0?z=15&q=<?php echo $data['lat_long']; ?>" target="_blank"><?php echo $data['lat_long']; ?></a>
					<?php else: ?>
						<a class="cursor-p" href="https://www.google.com/maps/?q=<?php echo $data['lat_long']; ?>" target="_blank"><?php echo $data['lat_long']; ?></a>
					<?php endif ?>
				</td>
			</tr>
			<tr>
				<td class="col-xs-4 data foto_kunjungan">
					Foto
				</td>
				<td class="col-xs-8">
					<a class="cursor-p" href="uploads/<?php echo $data['foto_kunjungan']; ?>" target="_blank"><?php echo $data['foto_kunjungan']; ?></a>
				</td>
			</tr>
		</tbody>
	</table>
</div>