var user = {
	start_up: function(){
		user.getLists();
	}, // end - start_up

	add_form: function() {
		$.get('master/User/add_form',{
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

			    $(modal_body).find('#nama_user').selectpicker();
			});
		},'html');
	}, // end - add_form

	edit_form: function(elm) {
		var btn_edit = $(elm);
		var tr = $(btn_edit).closest('tr');
		var id_user = $(tr).find('td.id_user').html();

		$.get('master/User/edit_form',{
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

			    $(modal_body).find('#nama_user').selectpicker();
			});
		},'html');
	}, // end - edit_form

	getLists : function(keyword = null){
		$.ajax({
			url : 'master/User/list_user',
			data : {'keyword' : keyword},
			dataType : 'HTML',
			type : 'GET',
			beforeSend : function(){ showLoading(); },
			success : function(data){
				$('table.tbl_user tbody').html(data);
				user.showHideDetail();
				hideLoading();
			}
		});
	}, // end - getLists

	save: function () {
		var _filetmp = null;
		var err = 0;
		$.map( $('[data-required=1]'), function(input){
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
			bootbox.confirm('Apakah anda yakin ingin menyimpan data ?', function(result){
				if (result) {
					_filetmp = $('.file').prop('files')[0];

					var nama_user = $('#nama_user').val();
					var jenis_kelamin = $('#jenis_kelamin').val();
					var no_tlp = $('#no_tlp').val();
					var email = $('#email').val();
					var username = $('#username').val().toUpperCase();
					var id_group = $('#id_group').val();

					var data = {
						'nama_user' : nama_user,
						'jenis_kelamin' : jenis_kelamin,
						'no_tlp' : no_tlp,
						'email' : email,
						'username' : username,
						'id_group' : id_group
					};

					user.exec_save(data, _filetmp);
				};
			});
		};
	}, // end - save

	exec_save: function(data, file_tmp) {
		var formData = new FormData();
		formData.append("data", JSON.stringify(data));
        formData.append("file", file_tmp);

		$.ajax({
			url : 'master/User/save_data',
			dataType: 'json',
            type: 'post',
            async:false,
            processData: false,
            contentType: false,
            data: formData,
			beforeSend : function(){
				showLoading();
			},
			success : function(data){
				hideLoading();
				if ( data.status == 1 ) {
					bootbox.alert(data.message, function(){
						user.getLists();
						bootbox.hideAll();
					});
				} else {
					bootbox.alert(data.message);
				}
			}
		});
	}, // end - exec_save

	edit: function () {
		var _filetmp = null;
		var err = 0;
		$.map( $('[data-required=1]'), function(input){
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
			bootbox.confirm('Apakah anda yakin ingin menyimpan data ?', function(result){
				if ( result ) {
					_filetmp = $('.file').prop('files')[0];

					var id_user = $('#nama_user').data('id');
					var id_detuser = $('#nama_user').data('iddet');
					var status_user = $('#status').val();
					var nama_user = $('#nama_user').val();
					var jenis_kelamin = $('#jenis_kelamin').val();
					var no_tlp = $('#no_tlp').val();
					var email = $('#email').val();
					var username = $('#username').val().toUpperCase();
					var id_group = $('#id_group').val();

					var data = {
						'id_user' : id_user,
						'id_detuser' : id_detuser,
						'status_user' : status_user,
						'nama_user' : nama_user,
						'jenis_kelamin' : jenis_kelamin,
						'no_tlp' : no_tlp,
						'email' : email,
						'username' : username,
						'id_group' : id_group
					};

					user.exec_edit(data, _filetmp);
				};
			});
		};
	}, // end - edit

	exec_edit: function(data, file_tmp) {
		var formData = new FormData();
		formData.append("data", JSON.stringify(data));
        formData.append("file", file_tmp);

		$.ajax({
			url : 'master/User/edit_data',
			dataType: 'json',
            type: 'post',
            async:false,
            processData: false,
            contentType: false,
            data: formData,
			beforeSend : function(){
				showLoading();
			},
			success : function(data){
				hideLoading();
				if ( data.status == 1 ) {
					bootbox.alert(data.message, function(){
						user.getLists();
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

		var id_user = $(tr).find('td.id_user').html();

		bootbox.confirm('Apakah anda yakin ingin menghapus data ?', function(result){
			if ( result ) {
				$.ajax({
					url : 'master/User/delete_data',
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
								user.getLists();
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

	change_password: function() {
		var id_user = $('input#nama_user').data('id');
		var username = $('input#username').val().toUpperCase();
		var old_password = $('input#old_password').val();
		var new_password = $('input#new_password').val();
		if ( !empty(old_password) && !empty(new_password) && !empty(username) ) {
			bootbox.confirm('Apakah anda yakin ingin mengubah password ?', function(result){
				if ( result ) {
					$.ajax({
						url : 'master/User/change_password',
						dataType: 'json',
						type: 'post',
						data: {
							'id_user' : id_user,
							'username' : username,
							'old_password' : old_password,
							'new_password' : new_password
						},
						beforeSend : function(){
							showLoading();
						},
						success : function(data){
							hideLoading();
							if ( data.status == 1 ) {
								bootbox.alert(data.message, function(){
									location.reload();
									bootbox.hideAll();
								});
							} else {
								bootbox.alert(data.message);
							}
						}
					});
				};
			});
		} else {
			$.map($('input:not([disabled])'), function(input){
				if ( empty($(input).val()) ) {
					$(input).parent().addClass('has-error');
				} else {
					$(input).parent().removeClass('has-error');
				};
			});
			bootbox.alert('Password belum lengkap.');
		};
	}, // end - change_password

	reset_password: function(elm) {
		var tr_det = $(elm).closest('tr.det');
		var tr_head = $(tr_det).prev('tr.head');
		var id_user = $(tr_head).find('td.id_user').html();

		bootbox.confirm('Apakah anda yakin ingin me reset password ?', function(result){
			if (result) {
				$.ajax({
					url : 'master/User/reset_password',
					dataType: 'json',
					type: 'post',
					data: {
						'id_user' : id_user,
					},
					beforeSend : function(){
						showLoading();
					},
					success : function(data){
						hideLoading();
						if ( data.status == 1 ) {
							bootbox.alert(data.message, function(){
								location.reload();
								bootbox.hideAll();
							});
						} else {
							bootbox.alert(data.message);
						}
					}
				});
			};
		});
	}, // end - reset_password

	showHideDetail: function() {
		$('tr.head').click(function () {
			var val = $(this).data('val');
			if ( val == 0 ) {
	            $(this).next('tr.det').removeClass('hide');
	            $(this).data('val', 1);
			} else {
				$(this).next('tr.det').addClass('hide');
	            $(this).data('val', 0);
			};
        });
	}, // end - showHideDetail
};

user.start_up();