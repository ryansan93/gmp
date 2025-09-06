<div class="row content-panel">
	<div class="col-xs-12 detailed">
		<form role="form" class="form-horizontal">
			<div class="col-xs-12 no-padding">
				<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
					<div class="col-xs-12 no-padding">
						<label> Tanggal </label>
					</div>
					<div class="col-xs-2 no-padding">
						<div class="input-group date datetimepicker" name="tanggal" id="Tanggal">
					        <input type="text" class="form-control text-center" placeholder="Start Date" data-required="1" />
					        <span class="input-group-addon">
					            <span class="glyphicon glyphicon-calendar"></span>
					        </span>
					    </div>
					</div>
				</div>
				<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
					<div class="col-xs-12 no-padding">
						<label>Jenis</label>
					</div>
					<div class="col-xs-12 no-padding">
						<select class="form-control jenis">
							<option value="">Pilih Jenis</option>
							<option value="OBAT">OVK</option>
							<option value="PAKAN">PAKAN</option>
						</select>
					</div>
				</div>
				<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
					<div class="col-xs-12 no-padding">
						<label>Gudang</label>
					</div>
					<div class="col-xs-12 no-padding">
						<select class="form-control gudang" data-required="1">
							<option value="all" data-jenis="all">ALL</option>
							<?php if ( !empty($gudang) && count($gudang) > 0 ) { ?>
								<?php foreach ($gudang as $k_gudang => $v_gudang) { ?>
									<option value="<?php echo $v_gudang['id']; ?>" data-jenis="<?php echo strtoupper($v_gudang['jenis']); ?>"><?php echo strtoupper($v_gudang['jenis'].' | '.$v_gudang['nama']); ?></option>
								<?php } ?>
							<?php } ?>
						</select>
					</div>
				</div>
				<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
					<div class="col-xs-12 no-padding">
						<label>Barang</label>
					</div>
					<div class="col-xs-12 no-padding">
						<select class="form-control barang" data-required="1">
							<option value="all">ALL</option>
							<?php foreach ($barang as $k_barang => $v_barang) { ?>
								<option value="<?php echo $v_barang['kode']; ?>" data-jenis="<?php echo strtoupper($v_barang['tipe']); ?>"><?php echo strtoupper($v_barang['nama']); ?></option>
							<?php } ?>
						</select>
					</div>
				</div>
				<div class="col-xs-12 no-padding">
					<button id="btn-tampil" type="button" data-href="action" class="col-xs-12 btn btn-primary cursor-p pull-left" title="TAMPIL" onclick="ps.getData()">Tampilkan</button>
				</div>
				<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
				<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
					<small>
						<table class="table table-bordered tbl_list" style="margin-bottom: 0px;">
							<!-- <thead>
								<tr>
									<th class="text-center col-xs-1">Kode Brg</th>
									<th class="text-center col-xs-2">Nama Brg</th>
									<th class="text-center col-xs-1">Tanggal</th>
									<th class="text-center col-xs-3">Kode Transaksi</th>
									<th class="text-center col-xs-1">Jumlah</th>
									<th class="text-center col-xs-1">Hrg Beli</th>
									<th class="text-center col-xs-1">Total Beli</th>
									<th class="text-center col-xs-1">Hrg Jual</th>
									<th class="text-center col-xs-1">Total Jual</th>
									<th class="text-center col-xs-1">Saldo</th>
									<th class="text-center col-xs-1">Nilai Saldo</th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td colspan="11" style="background-color: #dedede;"><b>Gudang : -</b></td>
								</tr>
								<tr>
									<td colspan="11">Data tidak ditemukan.</td>
								</tr>
							</tbody> -->
							<tbody></tbody>
						</table>
					</small>
				</div>
			</div>
		</form>
	</div>
</div>
