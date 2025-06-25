var group = {
	start_up: function(){
		group.getLists();
	}, // end - start_up

	add_form: function(elm) {
		$.get('master/Group/add_form',{
			},function(data){
			var _options = {
				className : 'veryWidth',
				message : data,
				size : 'large',
			};
			bootbox.dialog(_options).bind('shown.bs.modal', function(){
				// $(this).find('.modal-dialog').css({
				// 	'width':'70%',
				// 	'max-width':'100%'
				// });

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
		var id_group = $(tr).find('td.id_group').html();

		$.get('master/Group/edit_form',{
			id_group: id_group,
			},function(data){
			var _options = {
				className : 'veryWidth',
				message : data,
				size : 'large',
			};
			bootbox.dialog(_options).bind('shown.bs.modal', function(){
				// $(this).find('.modal-dialog').css({
				// 	'width':'70%',
				// 	'max-width':'100%'
				// });

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
			url : 'master/Group/list_group',
			data : {'keyword' : keyword},
			dataType : 'HTML',
			type : 'GET',
			beforeSend : function(){ showLoading(); },
			success : function(data){
				$('table.tbl_group tbody').html(data);
				group.showHideDetail();
				hideLoading();
			}
		});
	}, // end - getLists

	save: function () {
		var err = 0;
		var jml_data = 0;

		$.map( $('input[data-required=1]'), function(input){
			if ( empty($(input).val()) ) {
				$(input).parent().addClass('has-error');
				err++;
			} else {
				$(input).parent().removeClass('has-error');
			};
		});
		
		// CHECK FOR EMPTY CHECK BO OR NO
		$.map( $('.check-fitur'), function(check){
			if ( $(check).is(':checked') ) {
				jml_data++;
			};
		});

		if ( err > 0 ) {
			bootbox.alert('Harap lengkapi data terlebih dahulu.', function() {
				var body = $('button[data-bb-handler=ok]').closest('body');
				$('button[data-bb-handler=ok]').closest('div.bootbox-alert').next('div.modal-backdrop').remove();
				$('button[data-bb-handler=ok]').closest('div.bootbox-alert').remove();

				$(body).addClass('modal-open');
			});
		} else {
			if ( jml_data > 0 ) {
				bootbox.confirm('Apakah anda yakin ingin menyimpan data ?', function(result){
					if (result) {
						var nama_group = $('input#nama_group').val();
						var detail_group = $.map($('table.detail tbody tr'), function(tr){
							var check = $(tr).find('.check-fitur');
							if ( $(check).is(':checked') ) {
								var a_view = $(tr).find('.check-view').is(':checked') ? 1 : 0;
								var a_submit = $(tr).find('.check-submit').is(':checked') ? 1 : 0;
								var a_edit = $(tr).find('.check-update').is(':checked') ? 1 : 0;
								var a_delete = $(tr).find('.check-delete').is(':checked') ? 1 : 0;
								var a_ack = $(tr).find('.check-ack').is(':checked') ? 1 : 0;
								var a_approve = $(tr).find('.check-approve').is(':checked') ? 1 : 0;

								var data_detail = {
									'id_detfitur' : $(check).data('idftr'),
									'a_view' : a_view,
									'a_submit' : a_submit,
									'a_edit' : a_edit,
									'a_delete' : a_delete,
									'a_ack' : a_ack,
									'a_approve' : a_approve,
								};
							};

							return data_detail;
						});

						var data = {
							'nama_group' : nama_group,
							'detail_group' : detail_group
						};

						group.exec_save(data);
					};
				});
			} else {
				bootbox.alert('Belum ada fitur yang anda pilih.');
			};
		};
	}, // end - save

	exec_save: function(data) {
		$.ajax({
			url : 'master/Group/save_data',
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
						group.getLists();
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
		var jml_data = 0;

		$.map( $('input[data-required=1]'), function(input){
			if ( empty($(input).val()) ) {
				$(input).parent().addClass('has-error');
				err++;
			} else {
				$(input).parent().removeClass('has-error');
			};
		});
		
		// CHECK FOR EMPTY CHECK BO OR NO
		$.map( $('.check-fitur'), function(check){
			if ( $(check).is(':checked') ) {
				jml_data++;
			};
		});

		if ( err > 0 ) {
			bootbox.alert('Harap lengkapi data terlebih dahulu.');
		} else {
			if ( jml_data > 0 ) {
				bootbox.confirm('Apakah anda yakin ingin menyimpan data ?', function(result){
					if ( result ) {
						var id_group = $('input#nama_group').data('id');
						var nama_group = $('input#nama_group').val();
						var detail_group = $.map($('table.detail tbody tr'), function(tr){
							var check = $(tr).find('.check-fitur');
							if ( $(check).is(':checked') ) {
								var _tr = $(check).closest('tr');
								var a_view = $(_tr).find('.check-view').is(':checked') ? 1 : 0;
								var a_submit = $(_tr).find('.check-submit').is(':checked') ? 1 : 0;
								var a_edit = $(_tr).find('.check-update').is(':checked') ? 1 : 0;
								var a_delete = $(_tr).find('.check-delete').is(':checked') ? 1 : 0;
								var a_ack = $(_tr).find('.check-ack').is(':checked') ? 1 : 0;
								var a_approve = $(_tr).find('.check-approve').is(':checked') ? 1 : 0;

								// console.log('view : ' + a_view + ',submit : ' + a_submit + ',edit : ' + a_edit + ',delete : ' + a_delete + ',ack : ' + a_ack + ',approve : ' + a_approve);

								var data_detail = {
									'id_detfitur' : $(check).data('idftr'),
									'a_view' : a_view,
									'a_submit' : a_submit,
									'a_edit' : a_edit,
									'a_delete' : a_delete,
									'a_ack' : a_ack,
									'a_approve' : a_approve,
								};
							};

							return data_detail;
						});

						var data = {
							'id_group' : id_group,
							'nama_group' : nama_group,
							'detail_group' : detail_group
						};

						group.exec_edit(data);
					};
				});
			} else {
				bootbox.alert('Belum ada fitur yang anda pilih.');
			};
		};
	}, // end - edit

	exec_edit: function(data) {
		$.ajax({
			url : 'master/Group/edit_data',
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
						group.getLists();
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

		var id_group = $(tr).find('td.id_group').html();

		bootbox.confirm('Apakah anda yakin ingin menghapus data ?', function(result){
			if ( result ) {
				$.ajax({
					url : 'master/Group/delete_data',
					dataType: 'json',
					type: 'post',
					data: {
						'params' : id_group
					},
					beforeSend : function(){
						showLoading();
					},
					success : function(data){
						hideLoading();
						if ( data.status == 1 ) {
							bootbox.alert(data.message, function(){
								group.getLists();
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

	mark_view: function(elm) {
		var tr = $(elm).closest('tr');
		var check_fitur = $(tr).find('.check-fitur');
		if ( $(elm).is(':checked') ) {
			$(tr).find('.check-view').prop('checked', true);
			$(tr).find('.check-fitur').prop('checked', true);
		} else {
			$(tr).find('.check-view').prop('checked', false);
			$(tr).find('.check-submit').prop('checked', false);
			$(tr).find('.check-update').prop('checked', false);
			$(tr).find('.check-delete').prop('checked', false);
			$(tr).find('.check-ack').prop('checked', false);
			$(tr).find('.check-approve').prop('checked', false);
			$(tr).find('.check-reject').prop('checked', false);
			$(tr).find('.check-fitur').prop('checked', false);
		};

		set_mark(check_fitur);
	}, // end - mark_view

	mark_view_all: function(elm) {
		if ( $(elm).is(':checked') ) {
			$('.check-view').prop('checked', true);
			$('.check-fitur').prop('checked', true);
		} else {
			$('.check-view').prop('checked', false);
			$('.check-submit').prop('checked', false);
			$('.check-update').prop('checked', false);
			$('.check-delete').prop('checked', false);
			$('.check-ack').prop('checked', false);
			$('.check-approve').prop('checked', false);
			$('.check-reject').prop('checked', false);
			$('.check-fitur').prop('checked', false);
		};

		set_mark_all(elm);
	}, // end - mark_view
};

group.start_up();