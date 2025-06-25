<div class="row content-panel detailed">
	<div class="col-lg-12 detailed">
		<form role="form" class="form-horizontal">
			<div class="col-lg-12" id="penerimaan-pakan">
				<div class="form-group d-flex align-items-center">
					<div class="col-lg-2">Periode Stok</div>
					<div class="col-sm-2">
						<div class="input-group date" id="tgl_proses_awal" name="startDate">
					        <input type="text" class="form-control text-center" placeholder="Start" name="tgl_proses_awal" data-required="1" />
					        <span class="input-group-addon">
					            <span class="glyphicon glyphicon-calendar"></span>
					        </span>
					    </div>
					</div>
					<div class="col-sm-2">
						<div class="input-group date" id="tgl_proses_akhir" name="endDate">
					        <input type="text" class="form-control text-center" placeholder="End" name="tgl_proses_akhir" data-required="1" />
					        <span class="input-group-addon">
					            <span class="glyphicon glyphicon-calendar"></span>
					        </span>
					    </div>
					</div>
					<div class="col-sm-2">
						<button type="button" class="btn btn-primary" onclick="hs.hitung_stok()">Proses</button>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>