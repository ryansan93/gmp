<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
	<div class="col-xs-12 no-padding">
		<label class="control-label">Kab / Kota Pelanggan</label>
	</div>
	<div class="col-xs-12 no-padding">
		<span><?php echo $data['kab_kota']; ?></span>
	</div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
	<div class="col-xs-12 no-padding">
		<label class="control-label">Nama Pelanggan</label>
	</div>
	<div class="col-xs-12 no-padding not-plg-baru">
		<span><?php echo $data['nama_pelanggan']; ?></span>
	</div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
	<div class="col-xs-12 no-padding">
		<label class="control-label">Alamat Pelanggan</label>
	</div>
	<div class="col-xs-12 no-padding">
		<span><?php echo $data['alamat_pelanggan']; ?></span>
	</div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
	<div class="col-xs-12 no-padding">
		<label class="control-label">Alamat Usaha</label>
	</div>
	<div class="col-xs-12 no-padding">
		<span><?php echo $data['alamat_usaha']; ?></span>
	</div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
	<div class="col-xs-12 no-padding">
		<label class="control-label">Catatan Kunjungan</label>
	</div>
	<div class="col-xs-12 no-padding">
		<span><?php echo $data['catatan']; ?></span>
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
					<!-- <a class="cursor-p" href="https://www.google.com/maps/?q=<?php echo $data['lat_long']; ?>" target="_blank"><?php echo $data['lat_long']; ?></a> -->
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