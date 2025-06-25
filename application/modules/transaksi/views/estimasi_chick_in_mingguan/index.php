<div class="row content-panel detailed">
	<div class="col-xs-12 no-padding detailed">
		<form role="form" class="form-horizontal">
			<div class="col-xs-12">
                <div class="col-xs-12 no-padding">
                    <div class="col-xs-12 no-padding">
                        <?php if ( $akses['a_submit'] == 1 ) { ?>
                            <button id="btn-add" type="button" data-href="action" class="btn btn-primary cursor-p pull-right" title="ADD" onclick="est.addForm()"> 
                                <i class="fa fa-plus" aria-hidden="true"></i> ADD
                            </button>
                        <?php } ?>
                    </div>
                </div>
                <div class="col-xs-12 no-padding">
                    <hr style="margin-top: 5px; margin-bottom: 5px;">
                </div>
                <div class="col-xs-12 no-padding">
                    <small>
                        <table class="table table-bordered tblRiwayat">
                            <thead>
                                <tr>
                                    <td>
                                        <div class="col-xs-12 search left-inner-addon no-padding">
                                            <i class="fa fa-search"></i><input class="form-control" type="search" placeholder="Search">
                                        </div>
                                    </td>
                                    <td>
                                        <div class="col-xs-12 search left-inner-addon no-padding">
                                            <i class="fa fa-search"></i><input class="form-control" type="search" placeholder="Search">
                                        </div>
                                    </td>
                                    <td>
                                        <div class="col-xs-12 search left-inner-addon no-padding">
                                            <i class="fa fa-search"></i><input class="form-control" type="search" placeholder="Search">
                                        </div>
                                    </td>
                                    <td>
                                        <div class="col-xs-12 search left-inner-addon no-padding">
                                            <i class="fa fa-search"></i><input class="form-control" type="search" placeholder="Search">
                                        </div>
                                    </td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td class="text-right" colspan="3"><b>TOTAL</b></td>
                                    <td class="text-right hit_total total_jumlah"><b>0</b></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <th class="col-xs-2">Perusahaan</th>
                                    <th class="col-xs-4">Minggu</th>
                                    <th class="col-xs-2">Unit</th>
                                    <th class="col-xs-2">Jumlah (Ekor)</th>
                                    <th class="col-xs-2">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="5">Data tidak ditemukan.</td>
                                </tr>
                            </tbody>
                        </table>
                    </small>
                </div>
            </div>
		</form>
	</div>
</div>