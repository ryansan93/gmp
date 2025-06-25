<div class="row content-panel detailed">
	<!-- <h4 class="mb">Rencana Chick In Mingguan</h4> -->
	<div class="col-lg-12 detailed">
		<form role="form" class="form-horizontal">
			<div class="panel-body">
				<div id="voadip">
					<div class="col-lg-8 search no-padding d-flex align-items-center">
						<div class="col-sm-2 no-padding">
							<span> Periode DOC In</span>
						</div>
						<div class="col-sm-3">
							<div class="input-group date datetimepicker" name="startDate" id="StartDate_VOADIP">
						        <input type="text" class="form-control text-center" placeholder="Start Date" data-required="1" />
						        <span class="input-group-addon">
						            <span class="glyphicon glyphicon-calendar"></span>
						        </span>
						    </div>
						</div>
						<div class="col-sm-1 text-center no-padding" style="max-width: 4%;">s/d</div>
						<div class="col-sm-3">
							<div class="input-group date datetimepicker" name="endDate" id="EndDate_VOADIP">
						        <input type="text" class="form-control text-center" placeholder="End Date" data-required="1" />
						        <span class="input-group-addon">
						            <span class="glyphicon glyphicon-calendar"></span>
						        </span>
						    </div>
						</div>
						<div class="col-sm-2">
							<button id="btn-tampil" type="button" data-href="action" class="btn btn-primary cursor-p pull-left" title="TAMPIL" onclick="dv.get_lists()">Tampilkan</button>
						</div>
						<!-- <div class="col-sm-2">
							<button id="btn-add" type="button" data-href="action" class="btn btn-primary cursor-p pull-left" title="ADD" onclick="odvp.order_voadip_form()"><i class="fa fa-plus" aria-hidden="true"></i> ADD</button>
						</div> -->
					</div>
					<div class="col-lg-4 action no-padding">
						<div class="col-lg-4 search left-inner-addon no-padding pull-right" style="margin-left: 10px;">
							<i class="glyphicon glyphicon-search"></i><input class="form-control" type="search" data-table="tbl_odvp" placeholder="Search" onkeyup="filter_all(this)">
						</div>
					</div>
					<div style="padding-right: 30px;">
						<table class="table table-bordered table-hover tbl_voadip" id="dataTable" width="100%" cellspacing="0">
							<tbody class="list">
								<tr class="v-center">
									<th class="text-center">Unit</th>
									<td class="text-left" colspan="3">-</td>
								</tr>
								<tr class="v-center">
									<th class="col-sm-2 text-center" rowspan="2">Peternak</th>
									<th class="text-center" rowspan="2">Kandang</th>
									<th class="text-center" rowspan="2">Populasi</th>
									<th class="text-center" rowspan="2">Noreg</th>
									<th class="text-center" rowspan="2">Umur</th>
									<th class="text-center" colspan="5">Obat</th>
									<th class="text-center" colspan="4">Rencana Kirim</th>
								</tr>
								<tr class="v-center">
									<th class="col-sm-1 text-center">Kategori</th>
									<th class="col-sm-1 text-center">Nama</th>
									<th class="text-center">Isi<br>Kemasan</th>
									<th class="text-center">Bentuk</th>
									<th class="col-sm-1 text-center">Supplier</th>
									<th class="col-sm-1 text-center">Tanggal</th>
									<th class="text-center">Jumlah<br>(Kemasan)</th>
									<th class="text-center">Jumlah<br>(Isi)</th>
									<th class="text-center">DO</th>
								</tr>
								<tr>
									<td colspan="15">Data tidak ditemukan.</td>
								</tr>
							</tbody>
						</table>
					</div>
					<div class="col-lg-12">
						<button type="button" class="btn btn-primary pull-right" onclick="dv.save()"><i class="fa fa-save"></i> Simpan</button>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>