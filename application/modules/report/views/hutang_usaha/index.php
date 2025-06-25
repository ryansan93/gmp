<div class="row content-panel detailed">
	<div class="col-xs-12 no-padding detailed">
		<form role="form" class="form-horizontal">
            <div class="col-xs-12">
                <div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
                    <div class="col-xs-12 no-padding"><label class="label-control">Perusahaan</label></div>
                    <div class="col-xs-12 no-padding">
                        <select class="form-control perusahaan" data-required="1" multiple="multiple">
                            <option value="all">ALL</option>
                            <?php foreach( $perusahaan as $key => $value ) : ?>
                                <option value="<?php echo $value['nomor']; ?>"><?php echo strtoupper($value['nama']); ?></option>
                            <?php endforeach ?>
                        </select>
                    </div>
                </div>
                <div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
                    <div class="col-xs-6 no-padding" style="padding-right: 5px;">
                        <div class="col-xs-12 no-padding"><label class="label-control">Jenis</label></div>
                        <div class="col-xs-12 no-padding">
                            <select class="form-control jenis" data-required="1" multiple="multiple">
                                <option value="all">ALL</option>
                                <?php foreach( $jenis as $key => $value ) : ?>
                                    <option value="<?php echo $key; ?>"><?php echo strtoupper($value); ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-xs-6 no-padding" style="padding-left: 5px;">
                        <div class="col-xs-12 no-padding"><label class="label-control">Supplier</label></div>
                        <div class="col-xs-12 no-padding">
                            <select class="form-control supplier" data-required="1" multiple="multiple">
                                <option value="all">ALL</option>
                                <?php foreach( $supplier as $key => $value ) : ?>
                                    <option value="<?php echo $value['nomor']; ?>"><?php echo strtoupper($value['nama']); ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-xs-12 no-padding">
                    <button type="button" class="btn btn-primary col-xs-12" title="ADD" onclick="hu.getLists()"> 
                        <i class="fa fa-search" aria-hidden="true"></i> Tampilkan
                    </button>
                </div>
                <div class="col-xs-12 no-padding">
                    <hr style="margin-top: 10px; margin-bottom: 10px;">
                </div>
                <div class="col-xs-12 no-padding">
                    <span>* Klik pada baris untuk melihat detail pembayaran.</span>
                    <small>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <td class="text-right" colspan="4"><b>Total</b></td>
                                    <td class="text-right tot_hutang"><b>0</b></td>
                                    <td class="text-right tot_bayar"><b>0</b></td>
                                    <td class="text-right sisa_hutang"><b>0</b></td>
                                </tr>
                                <tr>
                                    <th class="col-xs-1">Tanggal</th>
                                    <th class="col-xs-2">No. Order</th>
                                    <th class="col-xs-2">Supplier</th>
                                    <th class="col-xs-1">Unit</th>
                                    <th class="col-xs-2">Nominal (Rp.)</th>
                                    <th class="col-xs-2">Bayar (Rp.)</th>
                                    <th class="col-xs-2">Sisa Hutang (Rp.)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="7">Data tidak ditemukan.</td>
                                </tr>
                            </tbody>
                        </table>
                    </small>
                </div>
            </div>
		</form>
	</div>
</div>