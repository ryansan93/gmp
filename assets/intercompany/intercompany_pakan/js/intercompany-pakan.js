var intercompanyPakan = {

	startUp: function () {
		intercompanyPakan.load();
	}, // end - startUp

	load: function () {
		var status = $('.filter-status').val();

		$.ajax({
			url: 'intercompany/IntercompanyPakan/list_log',
			data: { 'params': { 'status': status } },
			type: 'post',
			beforeSend: function () { showLoading('Memuat data . . .'); },
			success: function (html) {
				hideLoading();
				$('.list-container').html(html);
			}
		});
	}, // end - load

	kirimUlang: function (id_log) {
		bootbox.confirm('Kirim ulang baris ini ke partner?', function (result) {
			if (result) {
				$.ajax({
					url: 'intercompany/IntercompanyPakan/kirimUlang',
					data: { 'id_log': id_log },
					dataType: 'json',
					type: 'post',
					beforeSend: function () { showLoading('Mengirim ulang . . .'); },
					success: function (data) {
						hideLoading();
						bootbox.alert(data.message, function () {
							if (data.status == 1) { intercompanyPakan.load(); }
						});
					},
					error: function () {
						hideLoading();
						bootbox.alert('Terjadi kesalahan saat mengirim ulang.');
					}
				});
			}
		});
	}, // end - kirimUlang
};

intercompanyPakan.startUp();
