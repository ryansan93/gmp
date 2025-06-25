<div class="row content-panel">
	<div class="col-xs-12 detailed">
		<form role="form" class="form-horizontal">
            <div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
                <div class="col-xs-12 no-padding"><label class="control-label">Perusahaan</label></div>
                <div class="col-xs-12 no-padding">
                    <select class="form-control perusahaan" data-required="1" multiple="multiple">
                        <option value="all">ALL</option>
                        <?php foreach ($perusahaan as $key => $value) { ?>
                            <option value="<?php echo $value['kode']; ?>"><?php echo strtoupper($value['nama']); ?></option>
                        <?php } ?>
                    </select>
                </div>
            </div>
            <div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
                <div class="col-xs-4 no-padding" style="padding-right: 5px;">
                    <div class="col-xs-12 no-padding"><label class="control-label">Jenis</label></div>
                    <div class="col-xs-12 no-padding">
                        <select class="form-control jenis" data-required="1">
                            <option value="">Pilih Jenis</option>
                            <option value="pakan">PAKAN</option>
                            <option value="voadip">OVK</option>
                        </select>
                    </div>
                </div>
                <div class="col-xs-8 no-padding" style="padding-left: 5px;">
                    <div class="col-xs-12 no-padding"><label class="control-label">Unit</label></div>
                    <div class="col-xs-12 no-padding">
                        <select class="form-control unit" data-required="1" multiple="multiple">
                            <option value="all">ALL</option>
                            <?php foreach ($unit as $key => $value) { ?>
                                <option value="<?php echo $value['kode']; ?>"><?php echo strtoupper($value['nama']); ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
                <div class="col-xs-12 no-padding"><label class="control-label">Barang</label></div>
                <div class="col-xs-12 no-padding">
                    <select class="form-control barang" data-required="1" multiple="multiple">
                        <option value="all" class="all">ALL</option>
                        <?php foreach ($barang as $key => $value) { ?>
                            <option value="<?php echo $value['kode']; ?>" class="<?php echo $value['tipe']; ?>"><?php echo strtoupper($value['nama']); ?></option>
                        <?php } ?>
                    </select>
                </div>
            </div>
            <div class="col-xs-12 no-padding">
                <button type="button" class="col-xs-12 btn btn-primary" onclick="rbtt.getLists()"><i class="fa fa-search"></i> Tampilkan</button>
            </div>
            <div class="col-xs-12 no-padding"><hr style="margin-top: 5px; margin-bottom: 5px;"></div>
			<div class="col-xs-12 no-padding">
				<small>
					<table class="table table-bordered">
						<thead>
							<tr>
								<th class="col-xs-1">Tgl Terima</th>
								<th class="col-xs-2">No. Order</th>
								<th class="col-xs-2">No. SJ</th>
								<th class="col-xs-1">Kode Barang</th>
								<th class="col-xs-2">Nama Barang</th>
								<th class="col-xs-1">Jml Beli</th>
								<th class="col-xs-1">Sisa Stok</th>
								<th class="col-xs-1">Harga</th>
								<th class="col-xs-1">Total</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td colspan="9">Data tidak ditemukan.</td>
							</tr>
						</tbody>
					</table>
				</small>
			</div>
		</form>
	</div>
</div>