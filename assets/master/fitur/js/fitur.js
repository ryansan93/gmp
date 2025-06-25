var fitur = {
	start_up: function(){
		fitur.getLists();
	}, // end - start_up

	add_row: function (elm) {
	    var btn_add = $(elm);
	    var tr = $(btn_add).closest('tr');
	    var btn_remove = $(tr).find('#btn-remove');
	    var tbody = $(btn_add).closest('tbody');

	    var tr_clone  = tr.clone();
	    tr_clone.find('input').val(null);

	    $(tr_clone).attr('data-iddet', '');

	    tbody.append(tr_clone);

	    $(tbody).find('tr #btn-remove').removeClass('hide');
	}, // end - add_row

	add_form: function() {
		$.get('master/Fitur/add_form',{
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
		var id_fitur = $(tr).find('td.id_fitur').html();

		$.get('master/Fitur/edit_form',{
			id_fitur: id_fitur,
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
			url : 'master/Fitur/list_fitur',
			data : {'keyword' : keyword},
			dataType : 'HTML',
			type : 'GET',
			beforeSend : function(){ showLoading(); },
			success : function(data){
				$('table.tbl_fitur tbody').html(data);
				fitur.showHideDetail();
				hideLoading();
			}
		});
	}, // end - getLists

	save: function () {
		var err = 0;
		$.map( $('input[data-required=1]'), function(input){
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
					var nama_parent = $('input#nama_parent').val();
					var detail_fitur = $.map($('table.detail tbody tr'), function(tr){
						var data_detail = {
							'nama_fitur' : $(tr).find('input#nama_fitur').val(),
							'path_fitur' : $(tr).find('input#path_fitur').val()
						};

						return data_detail;
					});

					var data = {
						'nama_parent' : nama_parent,
						'detail_fitur' : detail_fitur
					};

					fitur.exec_save(data);
				};
			});
		};
	}, // end - save

	exec_save: function(data) {
		$.ajax({
			url : 'master/Fitur/save_data',
			dataType: 'json',
			type: 'post',
			data: {
				'params' : data
			},
			beforeSend : function(){
				showLoading();
			},
			success : function(data){
				hideLoading();
				if ( data.status == 1 ) {
					bootbox.alert(data.message, function(){
						fitur.getLists();
						bootbox.hideAll();
					});
				} else {
					bootbox.alert(data.message);
				}
			}
		});
	}, // end - exec_save

	edit: function () {
		var err = 0;
		$.map( $('input[data-required=1]'), function(input){
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
					var id_parent = $('input#nama_parent').data('id');
					var nama_parent = $('input#nama_parent').val();
					var detail_fitur = $.map($('table.detail tbody tr'), function(tr){
						var data_detail = {
							'id_detfitur' : $(tr).data('iddet'),
							'nama_fitur' : $(tr).find('input#nama_fitur').val(),
							'path_fitur' : $(tr).find('input#path_fitur').val()
						};

						return data_detail;
					});

					var data = {
						'id_parent' : id_parent,
						'nama_parent' : nama_parent,
						'detail_fitur' : detail_fitur
					};

					fitur.exec_edit(data);
				};
			});
		};
	}, // end - edit

	exec_edit: function(data) {
		$.ajax({
			url : 'master/Fitur/edit_data',
			dataType: 'json',
			type: 'post',
			data: {
				'params' : data
			},
			beforeSend : function(){
				showLoading();
			},
			success : function(data){
				hideLoading();
				if ( data.status == 1 ) {
					bootbox.alert(data.message, function(){
						fitur.getLists();
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

		var id_fitur = $(tr).find('td.id_fitur').html();

		bootbox.confirm('Apakah anda yakin ingin menghapus data ?', function(result){
			if ( result ) {
				$.ajax({
					url : 'master/Fitur/delete_data',
					dataType: 'json',
					type: 'post',
					data: {
						'params' : id_fitur
					},
					beforeSend : function(){
						showLoading();
					},
					success : function(data){
						hideLoading();
						if ( data.status == 1 ) {
							bootbox.alert(data.message, function(){
								fitur.getLists();
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

fitur.start_up();