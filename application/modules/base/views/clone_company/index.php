<div class="row content-panel detailed">
	<div class="col-xs-12 no-padding detailed">
		<div class="col-xs-12">
			<div class="col-xs-12 no-padding">

				<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
					<div class="alert alert-warning">
						<strong>Perhatian.</strong> Fitur ini membuat database baru lalu menjalankan DDL
						(CREATE TABLE/INDEX/CONSTRAINT) &amp; INSERT langsung ke SQL Server. Database
						sumber/live TIDAK PERNAH ditulis. Jalankan dulu ke database uji coba sebelum
						dipakai ke database live.
					</div>
				</div>

				<div class="col-sm-6 no-padding" style="padding-right: 5px; margin-bottom: 10px;">
					<div class="col-sm-12 no-padding">
						<label>SUMBER DATABASE</label>
					</div>
					<div class="col-sm-12 no-padding">
						<select class="form-control sumber" data-required="1">
							<option value="default">Database Utama (gmp_erp_live)</option>
						</select>
					</div>
				</div>
				<div class="col-sm-6 no-padding" style="padding-left: 5px; margin-bottom: 10px;">
					<div class="col-sm-12 no-padding">
						<label>NAMA DATABASE BARU</label>
					</div>
					<div class="col-sm-12 no-padding">
						<input type="text" class="form-control db-baru uppercase" placeholder="mis. gmp_erp_perusahaanb" data-required="1" />
					</div>
				</div>

				<div class="col-sm-12 no-padding" style="margin-bottom: 10px;">
					<div class="col-sm-12 no-padding">
						<label>NAMA PERUSAHAAN BARU</label>
					</div>
					<div class="col-sm-12 no-padding">
						<input type="text" class="form-control nama-perusahaan" placeholder="Nama perusahaan yang tampil di aplikasi &amp; kop surat" data-required="1" />
					</div>
				</div>

				<div class="col-sm-12 no-padding" style="margin-bottom: 10px;">
					<div class="col-sm-12 no-padding">
						<label>NAMA FOLDER APLIKASI BARU (dibuat sejajar/sibling folder aplikasi ini)</label>
					</div>
					<div class="col-sm-12 no-padding">
						<input type="text" class="form-control target-folder-name" placeholder="mis. gmperp_perusahaanb (tanpa path, cuma nama folder)" data-required="1" />
					</div>
				</div>

				<div class="col-sm-12 no-padding" style="margin-bottom: 15px;">
					<div class="col-sm-12 no-padding">
						<label>KONFIRMASI - ketik ulang nama database baru</label>
					</div>
					<div class="col-sm-12 no-padding">
						<input type="text" class="form-control db-baru-konfirmasi uppercase" placeholder="Ketik ulang nama database baru persis sama" />
					</div>
				</div>

				<div class="col-xs-12 no-padding" style="margin-bottom: 15px;">
					<button type="button" class="col-xs-12 btn btn-danger pull-right" onclick="cloneCompany.mulai()">MULAI CLONE PERUSAHAAN</button>
				</div>

				<div class="col-xs-12 no-padding">
					<label>LOG PROSES</label>
					<div class="clone-log" style="background:#111; color:#0f0; font-family: monospace; padding: 10px; height: 260px; overflow-y: auto; white-space: pre-wrap;"></div>
				</div>

			</div>
		</div>
	</div>
</div>
