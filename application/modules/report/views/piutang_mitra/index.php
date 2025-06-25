<div class="row content-panel detailed">
	<div class="col-xs-12 no-padding detailed">
		<form role="form" class="form-horizontal">
            <div class="col-xs-12">
                <div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
                    <div class="col-xs-6 no-padding" style="padding-right: 5px;">
                        <div class="col-xs-12 no-padding"><label class="label-control">Plasma</label></div>
                        <div class="col-xs-12 no-padding">
                            <select class="form-control mitra" data-required="1" multiple="multiple">
                                <option value="all">ALL</option>
                                <?php foreach( $mitra as $key => $value ) : ?>
                                    <option value="<?php echo $value['nomor']; ?>"><?php echo strtoupper($value['kode'].' | '.$value['nama'].' ('.$value['kode_perusahaan'].')'); ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-xs-6 no-padding" style="padding-left: 5px;">
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
                </div>
                <div class="col-xs-12 no-padding">
                    <button type="button" class="btn btn-primary col-xs-12" title="ADD" onclick="pm.getLists()"> 
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
                                    <td class="text-right tot_piutang"><b>0</b></td>
                                    <td class="text-right tot_bayar"><b>0</b></td>
                                    <td class="text-right sisa_piutang"><b>0</b></td>
                                </tr>
                                <tr>
                                    <th class="col-xs-1">Kode</th>
                                    <th class="col-xs-1">Tgl Piutang</th>
                                    <th class="col-xs-3">Perusahaan</th>
                                    <th class="col-xs-4">Nama</th>
                                    <th class="col-xs-1">Nominal (Rp.)</th>
                                    <th class="col-xs-1">Bayar (Rp.)</th>
                                    <th class="col-xs-1">Sisa Piutang (Rp.)</th>
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