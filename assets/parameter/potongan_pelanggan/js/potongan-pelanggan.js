var pp = {
	start_up: function () {
		pp.setting_up();
		pp.get_lists();
	}, // end - start_up

	setting_up: function () {
		$('.date').datetimepicker({
            locale: 'id',
            format: 'DD MMM Y'
        });

        $.map( $('.date'), function(ipt) {
            var tgl = $(ipt).find('input').data('tgl');
            if ( !empty(tgl) ) {
                $(ipt).data("DateTimePicker").date(new Date(tgl));
            }
        });

        $('.pelanggan').selectpicker();
	}, // end - setting_up

	get_lists: function() {
		var tbody = $('tbody');

		$.ajax({
			url : 'parameter/PotonganPelanggan/get_lists',
			data : {},
			type : 'GET',
			dataType : 'HTML',
			beforeSend : function(){
				showLoading();
			},
			success : function(html){
				hideLoading();

				$(tbody).html(html);
			},
		});
	}, // end - get_lists

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
			var resubmit = $(elm).attr('data-resubmit');

			pp.load_form(v_id, resubmit);
		};
	}, // end - changeTabActive

	load_form: function(v_id = null, resubmit = null) {
		var div_action = $('div#action');

		$.ajax({
			url : 'parameter/PotonganPelanggan/load_form',
			data : {
				'id' :  v_id,
				'resubmit' : resubmit
			},
			type : 'GET',
			dataType : 'HTML',
			beforeSend : function(){
				showLoading();
			},
			success : function(html){
				$(div_action).html(html);
				pp.setting_up();

				$('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal], [data-tipe=decimal3],[data-tipe=decimal4], [data-tipe=number]').each(function(){
					$(this).priceFormat(Config[$(this).data('tipe')]);
				});

				hideLoading();
			},
		});
	}, // end - load_form

	save: function () {
		var div = $('#action');

		var err = 0;

		$.map( $(div).find('[data-required]'), function(ipt) {
			if ( empty($(ipt).val()) ) {
				$(ipt).parent().addClass('has-error');
				err++;
			} else {
				$(ipt).parent().removeClass('has-error');
			}
		});

		if ( err > 0 ) {
			bootbox.alert('Harap lengkapi data terlebih dahulu.');
		} else {
			bootbox.confirm('Apakah anda yakin ingin menyimpan data ?', function(result) {
				if ( result ) {
					var params = {
						'pelanggan': $('select.pelanggan').val(),
						'potongan': numeral.unformat($('.potongan_persen').val()),
						'start_date': dateSQL($('#StartDate_PP').data('DateTimePicker').date()),
						'end_date': dateSQL($('#EndDate_PP').data('DateTimePicker').date()),
						'aktif': $('.aktif').val(),
					};

					$.ajax({
			            url : 'parameter/PotonganPelanggan/save',
			            data : {
			            	'params' : params
			            },
			            dataType : 'JSON',
			            type : 'POST',
			            beforeSend : function(){
			            	showLoading();
			            },
			            success : function(data){
			                hideLoading();
			                if ( data.status == 1 ) {
			                	bootbox.alert(data.message, function() {
			                		pp.load_form(data.content.id);
			                		pp.get_lists();
			                	});
			                } else {
			                	bootbox.alert(data.message);
			                }
			            }
			        });
				}
			});
		}
	}, // end - save

	edit: function (elm) {
		var div = $('#action');

		var err = 0;

		$.map( $(div).find('[data-required]'), function(ipt) {
			if ( empty($(ipt).val()) ) {
				$(ipt).parent().addClass('has-error');
				err++;
			} else {
				$(ipt).parent().removeClass('has-error');
			}
		});

		if ( err > 0 ) {
			bootbox.alert('Harap lengkapi data terlebih dahulu.');
		} else {
			bootbox.confirm('Apakah anda yakin ingin meng-ubah data ?', function(result) {
				if ( result ) {
					var params = {
						'id': $(elm).data('id'),
						'pelanggan': $('select.pelanggan').val(),
						'potongan': numeral.unformat($('.potongan_persen').val()),
						'start_date': dateSQL($('#StartDate_PP').data('DateTimePicker').date()),
						'end_date': dateSQL($('#EndDate_PP').data('DateTimePicker').date()),
						'aktif': $('.aktif').val(),
					};

					$.ajax({
			            url : 'parameter/PotonganPelanggan/edit',
			            data : {
			            	'params' : params
			            },
			            dataType : 'JSON',
			            type : 'POST',
			            beforeSend : function(){
			            	showLoading();
			            },
			            success : function(data){
			                hideLoading();
			                if ( data.status == 1 ) {
			                	bootbox.alert(data.message, function() {
			                		pp.load_form(data.content.id);
			                		pp.get_lists();
			                	});
			                } else {
			                	bootbox.alert(data.message);
			                }
			            }
			        });
				}
			});
		}
	}, // end - edit

	delete : function (elm) {
		bootbox.confirm('Apakah anda yakin ingin meng-hapus data Potongan Pelanggan ?', function (result) {
			if ( result ) {
				var id = $(elm).data('id');

				$.ajax({
		            url : 'parameter/PotonganPelanggan/delete',
		            data : {'id' : id},
		            type : 'POST',
		            dataType : 'JSON',
		            beforeSend : function(){ showLoading(); },
		            success : function(data){
		                hideLoading();
		                if (data.status) {
		                    bootbox.alert(data.message, function(){
		                    	pp.load_form();
		                        pp.get_list();
		                    });
		                } else {
		                    alertDialog(data.message);
		                }
		            }
		        });
			}
		});
	}, // end - delete
};

pp.start_up();