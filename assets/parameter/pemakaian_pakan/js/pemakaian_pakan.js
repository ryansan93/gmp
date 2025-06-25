var pp = {
	start_up: function(){
        $('#datetimepicker1').datetimepicker({
        	locale: 'id',
        	format: 'DD MMM Y'
        });

		pp.hide_btn_remove();
	}, // end - start_up

	hide_btn_remove: function() {
		var table = $('table.tbl_input_pp');
		var tbody = $(table).find('tbody');
		if ( $(tbody).find('tr').length <= 1 ) {
	        $(tbody).find('tr #btn-remove').addClass('hide');
	    };
	}, // end - hide_btn_remove

	changeTabActive: function(elm) {
		var vhref = $(elm).data('href');
		// change tab-menu
		$('.nav-tabs').find('a').removeClass('active');
        $('.nav-tabs').find('a').removeClass('show');
        $('.nav-tabs').find('li a[data-tab='+vhref+']').addClass('show');
        $('.nav-tabs').find('li a[data-tab='+vhref+']').addClass('active');

        // change tab-content
        $('.tab-pane').removeClass('show');
        $('.tab-pane').removeClass('active');
        $('div#'+vhref).addClass('show');
        $('div#'+vhref).addClass('active');

		if ( vhref == 'action' ) {
		    var v_id = $(elm).attr('data-id');
		    var tgl_mulai = $(elm).attr('data-mulai');
		    var resubmit = $(elm).attr('data-resubmit');

			pp.load_form(v_id, tgl_mulai, resubmit);
		};
	}, // end - changeTabActive

	load_form: function(v_id = null, tgl_mulai = null, resubmit = null) {
	    var div_action = $('div#action');

		$.ajax({
			url : 'parameter/PemakaianPakan/load_form',
			data : {
				'id' :  v_id,
				'resubmit' : resubmit
			},
			type : 'GET',
			dataType : 'HTML',
			beforeSend : function(){},
			success : function(html){
				$(div_action).html(html);
				$('#datetimepicker1').datetimepicker({
		        	locale: 'id',
		        	format: 'DD MMM Y',
		        	defaultDate: tgl_mulai
		        });
			},
		});
	}, // end - load_form

	getLists : function(keyword = null){
		$.ajax({
			url : 'parameter/PemakaianPakan/list_sp',
			data : {'keyword' : keyword},
			dataType : 'HTML',
			type : 'GET',
			beforeSend : function(){},
			success : function(data){
				$('table.tbl_pemakaian_pakan tbody').html(data);
			}
		});
	}, // end - getLists

	save: function (elm) {
		var err = 0;

		$.map( $('[data-required=1]'), function(ipt) {
			if ( empty($(ipt).val()) ) {
				err++;
				$(ipt).parent().addClass('has-error');
			} else {
				$(ipt).parent().removeClass('has-error');
			};
		});

		if ( err > 0 ) {
			bootbox.alert('Data yang anda masukkan belum lengkap, harap lengkapi data.');
		} else {
			var tbl = $('#tb_input_standar_performa');
			var rows = tbl.find('tbody tr');
			var reject_id = $(elm).attr('data-rejectid');

			// NOTE: collect data from input of rows column
			var data_rows = $.map(rows, function(row){
				return pp.getDataRow(row);
			});

			var tanggal = dateSQL($('[name=tanggal-berlaku]').data('DateTimePicker').date());
			
			var data_params = {
				'reject_id' : reject_id,
				'tanggal' : tanggal,
				'detail_performa' : data_rows
			};

			bootbox.confirm('Apakah Anda yakin akan menyimpan standar performa?', function(isConfirm){
				if (isConfirm) {
					pp.exec_save(data_params);
					// console.log(data_params);
				}
			});
		};
	}, // end - save

	exec_save: function(data_params) {
		$.ajax({
			url : 'parameter/PemakaianPakan/save_data',
			data : {'params' :  data_params},
			type : 'POST',
			dataType : 'JSON',
			beforeSend : function(){
				showLoading();
			},
			success : function(data){
				hideLoading();
				if (data.status) {
					bootbox.alert(data.message, function(){
						// console.log(data.content);
						pp.getLists();
						pp.load_form(data.content.id, data.content.tgl_mulai);
						// $('div#action').html(data.content);
					});
				}else{
					alertDialog(data.message);
				}
			},
		});
	}, // end - exec_save

	edit: function (elm) {
		var err = 0;

		$.map( $('[data-required=1]'), function(ipt) {
			if ( empty($(ipt).val()) ) {
				err++;
				$(ipt).parent().addClass('has-error');
			} else {
				$(ipt).parent().removeClass('has-error');
			};
		});

		if ( err > 0 ) {
			bootbox.alert('Data yang anda masukkan belum lengkap, harap lengkapi data.');
		} else {
			var tbl = $('#tb_input_standar_performa');
			var rows = tbl.find('tbody tr');
			var reject_id = $(elm).attr('data-rejectid');
			var edit_id = $('span.dok_no').data('id');

			// NOTE: collect data from input of rows column
			var data_rows = $.map(rows, function(row){
				return pp.getDataRow(row);
			});

			var tanggal = dateSQL($('[name=tanggal-berlaku]').data('DateTimePicker').date());
			
			var data_params = {
				'edit_id' : edit_id,
				'reject_id' : reject_id,
				'tanggal' : tanggal,
				'detail_performa' : data_rows
			};

			bootbox.confirm('Apakah Anda yakin akan mengubah standar performa?', function(isConfirm){
				if (isConfirm) {
					pp.exec_edit(data_params);
					// console.log(data_params);
				}
			});
		};
	}, // end - edit

	exec_edit: function(data_params) {
		$.ajax({
			url : 'parameter/PemakaianPakan/edit_data',
			data : {'params' :  data_params},
			type : 'POST',
			dataType : 'JSON',
			beforeSend : function(){
				showLoading();
			},
			success : function(data){
				hideLoading();
				if (data.status) {
					bootbox.alert(data.message, function(){
						pp.load_form(data.content.id, data.content.tgl_mulai);
						pp.getLists();
					});
				}else{
					alertDialog(data.message);
				}
			},
		});
	}, // end - exec_edit

	ack: function(elm) {
		var id = $(elm).data('id');

		bootbox.confirm('Apakan anda yakin ingin ACK data ?', function (result) {
			if (result) {
				$.ajax({
					url : 'parameter/PemakaianPakan/ack_data',
					data : {'id' :  id},
					type : 'POST',
					dataType : 'JSON',
					beforeSend : function(){
						showLoading();
					},
					success : function(data){
						hideLoading();
						if (data.status) {
							bootbox.alert(data.message, function(){
								pp.load_form(data.content.id, data.content.tgl_mulai);
								pp.getLists();
							});
						}else{
							alertDialog(data.message);
						}
					},
				});
			};
		});
	}, // end - ack

	approve: function(elm) {
		var id = $(elm).data('id');

		bootbox.confirm('Apakan anda yakin ingin APPROVE data ?', function (result) {
			if (result) {
				$.ajax({
					url : 'parameter/PemakaianPakan/approve_data',
					data : {'id' :  id},
					type : 'POST',
					dataType : 'JSON',
					beforeSend : function(){
						showLoading();
					},
					success : function(data){
						hideLoading();
						if (data.status) {
							bootbox.alert(data.message, function(){
								pp.load_form(data.content.id, data.content.tgl_mulai);
								pp.getLists();
							});
						}else{
							alertDialog(data.message);
						}
					},
				});
			};
		});
	}, // end - approve

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

	// NOTE: fungsi utk menghitung nilai mortalitas, konsumsi pakan, bb, fcr tiap baris
	calcRowValue : function(elm){
		var row = $(elm).closest('tr');
		var row_prev = row.prev('tr');
		var row_next = row.next('tr');

		if ( row_prev.length > 0 ) {
			var vrow_prev = pp.getDataRow(row_prev);
			var vrow = pp.getDataRow(row);

			var kons_pakan = vrow_prev.kons_pakan + vrow.kons_pakan_harian;
			var bb = vrow_prev.bb + vrow.adg;
			var vfcr = roundUp( (((kons_pakan / bb ) * 1000 ) / 1000) , 3) ;

			row.find('input[name=daya_hidup]').val( numeral.formatDec ( vrow_prev.daya_hidup - vrow.mortalitas) );
			row.find('input[name=kons_pakan]').val( numeral.formatInt ( kons_pakan ) );
			row.find('input[name=bb]').val( numeral.formatInt (bb) );
			row.find('input[name=fcr]').val( numeral.formatDec (vfcr, 3) );
		}

		if (row_next.length > 0) {
			pp.calcRowValue( row_next.find('input[name=bb]') );
		}

	}, // end - calcRowValue

	getDataRow : function (row) {
		var data_row = {};
		$.map( $(row).find('td input'), function(ipt){
			var iptCell = $(ipt);
			data_row[ iptCell.attr('name') ] = numeral.unformat(iptCell.val());
		});
		return data_row;
	}, // end - getDataRow

	addRowTable: function(elm) {
		add_row(elm);

		var row = $(elm).closest("tr");
		var tbody = $(elm).closest("tbody");

		var row_clone = $(row).next();

		row_clone.find('input').prop('disabled', true);
		row_clone.find('input[isedit=1]').prop('disabled', false);
		row_clone.find('input').val('');
		row_clone.find('input[name=umur]').val( tbody.find('tr').length - 1 );
		// tbody.append(row_clone);

		row.find('td.action button').hide();
		App.formatNumber();
		tbody.find('tr:last td input[name=mortalitas]').focus();
		tbody.find('tr:last td input[name=adg]').enterKey(function(e){
			pp.addRowTable(this);
		});

	}, // end - addRowTable

	removeRowTable: function(elm) {
		var row = $(elm).closest("tr");
		if (row.prev('tr').length > 0 || row.next('tr').length > 0) {
			row.prev('tr').find('td.action button').show();
			row.find('input').val('');
			row.remove();
		}
	}, // end - removeRowTable

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

pp.start_up();