<div class="panel-body no-padding">
	<?php if ($akses['submit']) :?>
	<div class="padding-left-15">
		<button class="btn btn-primary" href="#master_pelanggan" onclick="PLG.active_tab(this, 'add')" role="tab" data-toggle="tab">Tambah</button>
		<button data-toggle="modal" data-target="#modal_saldo_awal" class="btn btn-success"><i class="fa fa-edit"></i> Saldo Awal</button>
	</div>
	<?php endif; ?>
	<div class="row new-line padding-left-right-15">
		<div class="col-sm-offset-7">
			<div class="col-sm-4">
				<select id="filter_search" class="form-control">
					<option value="0">NIP</option>
					<option value="1">Nama Pelanggan</option>
					<option value="2">NIK</option>
					<option value="6">Keterangan</option>
				</select>
			</div>
			<div class="col-sm-8 padding-left-0">
				<i id="icon_search_pelanggan" class="glyphicon glyphicon-search"></i>
				<input id="input_cari_pelanggan" onkeyup="PLG.cari_pelanggan()" class="form-control" type="text" name="cari_pelanggan">
			</div>
		</div>
	</div>
	<div class="text-center">
		<h3><?php echo $title_panel;?></h3>
	</div>
	<div class="panel-body">
		<div class="row">
			<div class="col-sm-12">
				<table id="table_pelanggan" class="table table-bordered">
					<thead>
						<tr>
							<th class="col-sm-1">NIP</th>
							<th class="col-sm-2">Nama Pelanggan</th>
							<th class="col-sm-1">NIK</th>
							<th class="col-sm-2">Alamat</th>
							<th class="col-sm-1">Status</th>
							<th class="col-sm-1">Saldo Awal (Rp)</th>
							<th class="col-sm-2">Keterangan</th>
							<th class="col-sm-1">Action</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach($pelanggans as $pelanggan) : ?>
						<tr>
							<td name="id_pelanggan" data-id="<?php echo $pelanggan['nip']; ?>"><a class="link_detail" onclick="PLG.active_tab(this, 'show')"><?php echo $pelanggan['nip']; ?></a></td>
							<td><?php echo $pelanggan['nama']; ?></td>
							<td><?php echo $pelanggan['nik']; ?></td>
							<td><?php echo $pelanggan['alamat']; ?></td>
							<td><?php echo ($pelanggan['status']==1) ? 'AKTIF' : "NON AKTIF" ?></td>
							<td><?php echo $pelanggan['saldo_awal']; ?></td>
							<td><?php echo $pelanggan['keterangan']; ?></td>
							<td>
								<?php if ($pelanggan['status']==1) :?>
									<button onclick="PLG.load_form_status(this)" class="btn btn-danger">Non Aktif</button>
								<?php elseif ($pelanggan['status']==0) : ?>
									<button onclick="" class="btn btn-primary">Aktif</button>
								<?php endif; ?>
							</td>
						</tr>
						<?php endforeach ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>