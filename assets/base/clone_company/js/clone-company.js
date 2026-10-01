var cloneCompany = {

	log: function (msg) {
		var now = new Date();
		var jam = ('0' + now.getHours()).slice(-2) + ':' + ('0' + now.getMinutes()).slice(-2) + ':' + ('0' + now.getSeconds()).slice(-2);
		$('.clone-log').append('[' + jam + '] ' + msg + '\n');
		$('.clone-log').scrollTop($('.clone-log')[0].scrollHeight);
	}, // end - log

	ambilParams: function () {
		return {
			'sumber': $('.sumber').val(),
			'db_baru': $.trim($('.db-baru').val()),
			'nama_perusahaan': $.trim($('.nama-perusahaan').val()),
			'target_folder_name': $.trim($('.target-folder-name').val())
		};
	}, // end - ambilParams

	mulai: function () {
		var params = cloneCompany.ambilParams();
		var konfirmasi = $.trim($('.db-baru-konfirmasi').val());

		if (!params.db_baru || !params.nama_perusahaan || !params.target_folder_name) {
			bootbox.alert('Nama database baru, nama perusahaan, dan nama folder aplikasi baru wajib diisi.');
			return;
		}

		if (params.db_baru.toUpperCase() !== konfirmasi.toUpperCase()) {
			bootbox.alert('Konfirmasi nama database tidak cocok. Ketik ulang persis sama dengan "Nama Database Baru".');
			return;
		}

		bootbox.confirm(
			'Proses ini akan membuat database baru <b>' + params.db_baru + '</b>, replikasi schema dari ' +
			'database utama (tanpa data), lalu insert data master/config yang diperlukan saja. Database ' +
			'sumber/live TIDAK akan diubah. Lanjutkan?',
			function (result) {
				if (result) {
					$('.clone-log').empty();
					cloneCompany.stepValidasi(params);
				}
			}
		);
	}, // end - mulai

	stepValidasi: function (params) {
		cloneCompany.log('Validasi nama database baru . . .');
		showLoading('Validasi . . .');

		$.ajax({
			url: 'base/CloneCompany/validasi',
			data: { 'params': params },
			dataType: 'json',
			type: 'post',
			success: function (data) {
				if (data.status != 1) {
					hideLoading();
					cloneCompany.log('GAGAL: ' + data.message);
					bootbox.alert(data.message);
					return;
				}
				cloneCompany.log(data.message);
				cloneCompany.stepCloneSchema(params);
			},
			error: function () {
				hideLoading();
				cloneCompany.log('GAGAL: request validasi error.');
			}
		});
	}, // end - stepValidasi

	stepCloneSchema: function (params) {
		cloneCompany.log('Membuat database & replikasi schema database utama (tanpa data) sebagai [' + params.db_baru + '] . . .');
		showLoading('Membuat schema database utama . . .');

		$.ajax({
			url: 'base/CloneCompany/cloneSchema',
			data: { 'params': $.extend({}, params, { 'sumber': 'default' }) },
			dataType: 'json',
			type: 'post',
			success: function (data) {
				if (data.status != 1) {
					hideLoading();
					cloneCompany.log('GAGAL: ' + data.message);
					bootbox.alert(data.message);
					return;
				}
				cloneCompany.log(data.message);
				cloneCompany.stepCloneLogSchema(params);
			},
			error: function () {
				hideLoading();
				cloneCompany.log('GAGAL: request clone schema database utama error/timeout.');
			}
		});
	}, // end - stepCloneSchema

	stepCloneLogSchema: function (params) {
		cloneCompany.log('Membuat schema kosong database log history . . . (audit trail baru dimulai fresh)');
		showLoading('Membuat schema log history . . .');

		$.ajax({
			url: 'base/CloneCompany/cloneSchema',
			data: { 'params': $.extend({}, params, { 'sumber': 'log' }) },
			dataType: 'json',
			type: 'post',
			success: function (data) {
				if (data.status != 1) {
					hideLoading();
					cloneCompany.log('GAGAL: ' + data.message);
					bootbox.alert(data.message);
					return;
				}
				cloneCompany.log(data.message);
				cloneCompany.stepInsertMasterData(params);
			},
			error: function () {
				hideLoading();
				cloneCompany.log('GAGAL: request clone schema log history error/timeout.');
			}
		});
	}, // end - stepCloneLogSchema

	stepInsertMasterData: function (params) {
		cloneCompany.log('Insert data master/config yang diperlukan (ID/PK/FK sama persis dgn sumber) . . .');
		showLoading('Insert data master . . .');

		$.ajax({
			url: 'base/CloneCompany/insertMasterData',
			data: { 'params': params },
			dataType: 'json',
			type: 'post',
			success: function (data) {
				if (data.status != 1) {
					hideLoading();
					cloneCompany.log('GAGAL: ' + data.message);
					bootbox.alert(data.message);
					return;
				}
				cloneCompany.log(data.message);
				cloneCompany.stepCreateForeignKeys(params);
			},
			error: function () {
				hideLoading();
				cloneCompany.log('GAGAL: request insert data master error/timeout.');
			}
		});
	}, // end - stepInsertMasterData

	stepCreateForeignKeys: function (params) {
		cloneCompany.log('Membuat foreign key constraint . . .');
		showLoading('Membuat foreign key . . .');

		$.ajax({
			url: 'base/CloneCompany/createForeignKeys',
			data: { 'params': params },
			dataType: 'json',
			type: 'post',
			success: function (data) {
				if (data.status != 1) {
					hideLoading();
					cloneCompany.log('GAGAL: ' + data.message);
					bootbox.alert(data.message);
					return;
				}
				cloneCompany.log(data.message);
				cloneCompany.stepUpdatePerusahaan(params);
			},
			error: function () {
				hideLoading();
				cloneCompany.log('GAGAL: request buat foreign key error.');
			}
		});
	}, // end - stepCreateForeignKeys

	stepUpdatePerusahaan: function (params) {
		cloneCompany.log('Update nama perusahaan utama menjadi "' + params.nama_perusahaan + '" . . .');
		showLoading('Update identitas perusahaan . . .');

		$.ajax({
			url: 'base/CloneCompany/updatePerusahaan',
			data: { 'params': params },
			dataType: 'json',
			type: 'post',
			success: function (data) {
				if (data.status != 1) {
					hideLoading();
					cloneCompany.log('GAGAL: ' + data.message);
					bootbox.alert(data.message);
					return;
				}
				cloneCompany.log(data.message);
				cloneCompany.stepCloneFolder(params);
			},
			error: function () {
				hideLoading();
				cloneCompany.log('GAGAL: request update perusahaan error.');
			}
		});
	}, // end - stepUpdatePerusahaan

	stepCloneFolder: function (params) {
		cloneCompany.log('Clone folder aplikasi ke [' + params.target_folder_name + '] . . . (termasuk vendor/, bisa beberapa menit)');
		showLoading('Clone folder aplikasi . . .');

		$.ajax({
			url: 'base/CloneCompany/cloneFolder',
			data: { 'params': params },
			dataType: 'json',
			type: 'post',
			success: function (data) {
				hideLoading();
				if (data.status != 1) {
					cloneCompany.log('GAGAL: ' + data.message);
					bootbox.alert(data.message);
					return;
				}
				cloneCompany.log(data.message);
				cloneCompany.log('SELESAI. Database [' + params.db_baru + '] + folder aplikasi siap dipakai.');
				cloneCompany.log('Sisa langkah manual (di luar aplikasi ini):');
				cloneCompany.log('1) Ganti file logo di assets/images/ folder baru kalau ada logo baru.');
				cloneCompany.log('2) Setup document root/virtual host di web server supaya folder baru bisa diakses.');
				cloneCompany.log('3) pelanggan/mitra/ekspedisi/karyawan/kendaraan/kandang dkk masih kosong - pakai tombol clone terpisah (belum tersedia).');
				bootbox.alert('Clone perusahaan (database + folder aplikasi) selesai. Lihat panel Log Proses untuk sisa langkah manual.');
			},
			error: function () {
				hideLoading();
				cloneCompany.log('GAGAL: request clone folder error/timeout.');
			}
		});
	}, // end - stepCloneFolder
};
