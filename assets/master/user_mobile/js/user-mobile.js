var um = {
	start_up: function(){
		um.getLists();
	}, // end - start_up

	add_form: function() {
		$.get('master/UserMobile/add_form',{
			},function(data){
			var _options = {
				className : 'veryWidth',
				message : data,
				size : 'large',
			};
			bootbox.dialog(_options).bind('shown.bs.modal', function(){
				// $(this).find('.modal-dialog').css(
				// 	'max-width','80%'
				// );

				var modal_body = $(this).find('.modal-body');
				var table = $(modal_body).find('table');
				var tbody = $(table).find('tbody');
				if ( $(tbody).find('.modal-body tr').length <= 1 ) {
			        $(this).find('tr #btn-remove').addClass('hide');
			    };
			});
		},'html');
	}, // end - add_form

	edit_form: function(elm) {
		var btn_edit = $(elm);
		var tr = $(btn_edit).closest('tr');
		var id_user = $(tr).data('id');

		$.get('master/UserMobile/edit_form',{
			id_user: id_user,
			},function(data){
			var _options = {
				className : 'veryWidth',
				message : data,
				size : 'large',
			};
			bootbox.dialog(_options).bind('shown.bs.modal', function(){
				// $(this).find('.modal-dialog').css(
				// 	'max-width','80%'
				// );

				var modal_body = $(this).find('.modal-body');
				var table = $(modal_body).find('table');
				var tbody = $(table).find('tbody');
				if ( $(tbody).find('tr').length <= 1 ) {
			        $(tbody).find('tr #btn-remove').addClass('hide');
			    };
			});
		},'html');
	}, // end - edit_form

	getLists : function(keyword = null){
		$.ajax({
			url : 'master/UserMobile/list_user',
			data : {'keyword' : keyword},
			dataType : 'HTML',
			type : 'GET',
			beforeSend : function(){ showLoading(); },
			success : function(data){
				$('table.tbl_user tbody').html(data);
				hideLoading();

				
			}
		});
	}, // end - getLists

	save: function (elm) {
		var modal_body = $(elm).closest('.modal-body');

		var err = 0;
		$.map( $(modal_body).find('[data-required=1]'), function(input){
			if ( empty($(input).val()) ) {
				$(input).parent().addClass('has-error');
				err++;
			} else {
				$(input).parent().removeClass('has-error');
			};
		});

		if ( err > 0 ) {
			bootbox.alert('Harap lengkapi data terlebih dahulu.');
		} else {
			var password = $(modal_body).find('#password').val();
			var verifikasi_password = $(modal_body).find('#verifikasi_password').val();
			if ( password != verifikasi_password ) {
				bootbox.alert('Cek kembali password yang anda masukkan.');
			} else {
				bootbox.confirm('Apakah anda yakin ingin menyimpan data ?', function(result){
					if (result) {
						var id_karyawan = $(modal_body).find('select.karyawan').val();
						var nik_karyawan = $(modal_body).find('select.karyawan option:selected').data('nik');
						var nama = $(modal_body).find('select.karyawan option:selected').data('nama');
						var jabatan = $(modal_body).find('select.karyawan option:selected').data('jabatan');
						var username = $(modal_body).find('input#username').val();
						var password = $(modal_body).find('input#password').val();

						var data = {
							'id_karyawan': id_karyawan,
							'nik_karyawan': nik_karyawan,
							'nama': nama,
							'jabatan': jabatan,
							'username': username,
							'password': password
						};

						um.exec_save(data);
					};
				});
			}
		};
	}, // end - save

	exec_save: function(data) {
		$.ajax({
            url: 'master/UserMobile/save_data',
            data: {'params': data },
            type: 'POST',
            dataType: 'JSON',
            beforeSend: function(){ showLoading(); },
            success: function(data){
                hideLoading();
				if ( data.status == 1 ) {
					bootbox.alert(data.message, function(){
						um.getLists();
						bootbox.hideAll();
					});
				} else {
					bootbox.alert(data.message);
				}
            }
        });
	}, // end - exec_save

	edit: function (elm) {
		var modal_body = $(elm).closest('.modal-body');

		var err = 0;
		$.map( $(modal_body).find('[data-required=1]'), function(input){
			if ( empty($(input).val()) ) {
				$(input).parent().addClass('has-error');
				err++;
			} else {
				$(input).parent().removeClass('has-error');
			};
		});

		if ( err > 0 ) {
			bootbox.alert('Harap lengkapi data terlebih dahulu.');
		} else {
			var new_password = $(modal_body).find('#password').val();
			var verifikasi_password = $(modal_body).find('#verifikasi_password').val();
			var password = null;
			var save = 0;
			if ( empty(new_password) && empty(verifikasi_password) ) {
				password = $(modal_body).find('#old_password').val();
				save = 1;
			} else {
				if ( new_password != verifikasi_password ) {
					bootbox.alert('Cek kembali password yang anda masukkan.');
				} else {
					save = 1;
					password = new_password;
				}
			}

			if ( save == 1 ) {
				bootbox.confirm('Apakah anda yakin ingin meng-edit data ?', function(result){
					if (result) {
						var id_user = $(elm).data('id');
						var id_karyawan = $(modal_body).find('select.karyawan').val();
						var nik_karyawan = $(modal_body).find('select.karyawan option:selected').data('nik');
						var nama = $(modal_body).find('select.karyawan option:selected').data('nama');
						var jabatan = $(modal_body).find('select.karyawan option:selected').data('jabatan');
						var username = $(modal_body).find('input#username').val();
						var status = $(modal_body).find('select.status').val();

						var data = {
							'id_user': id_user,
							'id_karyawan': id_karyawan,
							'nik_karyawan': nik_karyawan,
							'nama': nama,
							'jabatan': jabatan,
							'username': username,
							'password': password,
							'status': status
						};

						um.exec_edit(data);
					};
				});
			}
		};
	}, // end - edit

	exec_edit: function(data) {
		$.ajax({
            url: 'master/UserMobile/edit_data',
            data: {'params': data },
            type: 'POST',
            dataType: 'JSON',
            beforeSend: function(){ showLoading(); },
            success: function(data){
                hideLoading();
				if ( data.status == 1 ) {
					bootbox.alert(data.message, function(){
						um.getLists();
						bootbox.hideAll();
					});
				} else {
					bootbox.alert(data.message);
				}
            }
        });
	}, // end - exec_edit

	delete: function(elm) {
		var btn_delete = $(elm);
		var tr = $(btn_delete).closest('tr');

		var id_user = $(tr).data('id');

		bootbox.confirm('Apakah anda yakin ingin menghapus data ?', function(result){
			if ( result ) {
				$.ajax({
					url : 'master/UserMobile/delete_data',
					dataType: 'json',
					type: 'post',
					data: {
						'params' : id_user
					},
					beforeSend : function(){
						showLoading();
					},
					success : function(data){
						hideLoading();
						if ( data.status == 1 ) {
							bootbox.alert(data.message, function(){
								um.getLists();
								bootbox.hideAll();
							});
						} else {
							bootbox.alert(data.message);
						}
					}
				});
			};
		});
	}, // end - delete

	get_data_karyawan: function(elm) {
	}, // end - get_data_karyawan
};

um.start_up();