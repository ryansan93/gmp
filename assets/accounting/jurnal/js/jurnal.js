var jurnal = {
	start_up: function () {
		jurnal.setting_up();
	}, // end - start_up

	setting_up: function() {
		$('.unit').select2();
        // $('.unit').select2({placeholder: 'Pilih Item'}).on("select2:select", function (e) {
        //     var unit = $('.unit').select2().val();

        //     for (var i = 0; i < unit.length; i++) {
        //         if ( unit[i] == 'all' ) {
        //             $('.unit').select2().val('all').trigger('change');

        //             i = unit.length;
        //         }
        //     }

        //     $('.unit').next('span.select2').css('width', '100%');
        // });
        // $('.unit').next('span.select2').css('width', '100%');

        $('#tanggal, #StartDate, #EndDate').datetimepicker({
            locale: 'id',
            format: 'DD MMM Y'
        });

        var tgl = $('#tanggal').find('input').data('val');
        if ( !empty(tgl) ) {
        	$('#tanggal').data('DateTimePicker').date( moment(new Date(tgl)) );
        }
        $.map( $('div#action').find('table tbody tr'), function(tr) {
        	$(tr).find('input.date').datetimepicker({
	            locale: 'id',
	            format: 'DD MMM Y'
	        });

        	var tgl = $(tr).find('input.date').data('val');

	        if ( !empty(tgl) ) {
	        	$(tr).find('input.date').data('DateTimePicker').date( moment(new Date(tgl)) );
	        }
        });
	}, // end - setting_up

	addRow: function(elm) {
        let row = $(elm).closest('tr');
        let newRow = row.clone();

        newRow.find('input, select').val('');
        row.after(newRow);
    }, // end - addRow

    removeRow: function(elm) {
        let tbody = $(elm).closest('tbody');
        let row = $(elm).closest('tr');
        if ($(tbody).find('tr').length > 1) {
            $(row).remove();
        }
    }, // end - removeRow

	changeTabActive: function(elm) {
        var href = $(elm).data('href');
        var edit = $(elm).data('edit');
        // change tab-menu
        $('.nav-tabs').find('a').removeClass('active');
        $('.nav-tabs').find('a').removeClass('show');
        $('.nav-tabs').find('li a[data-tab='+href+']').addClass('show');
        $('.nav-tabs').find('li a[data-tab='+href+']').addClass('active');

        // change tab-content
        $('.tab-pane').removeClass('show');
        $('.tab-pane').removeClass('active');
        $('div#'+href).addClass('show');
        $('div#'+href).addClass('active');

        jurnal.load_form($(elm), edit, href);
    }, // end - changeTabActive

    load_form: function(elm, edit = null, href = null) {
        var dcontent = $('div#'+href);

        var params = {
            'id': $(elm).data('id')
        };

        $.ajax({
            url : 'accounting/Jurnal/load_form',
            data : {
                'params' :  params,
                'edit' :  edit
            },
            type : 'GET',
            dataType : 'HTML',
            beforeSend : function(){ App.showLoaderInContent(dcontent); },
            success : function(html){
                App.hideLoaderInContent(dcontent, html);

                jurnal.setting_up();
            },
        });
    }, // end - load_form

    getJurnalTrans: function() {
    	var sel_jurnal_trans = $('select.jurnal_trans');
    	var val = $(sel_jurnal_trans).val();

    	if ( empty(val) ) {
    		$('.jurnal_trans_detail').val('');
    		$('.jurnal_trans_detail').attr('disabled', 'disabled');
    		$('.sumber_tujuan').val('');
    		$('.sumber_tujuan').attr('disabled', 'disabled');
    		$('.sumber_tujuan').removeClass('hide');
    		$('.supplier').val('');
    		$('.supplier').attr('disabled', 'disabled');
    		$('.supplier').addClass('hide');
    	} else {
    		var text = $(sel_jurnal_trans).find('option:selected').text().trim();

    		$('.jurnal_trans_detail').removeAttr('disabled');
    		$('.jurnal_trans_detail').find('option').removeClass('hide');
    		$('.jurnal_trans_detail').find('option:not([data-idheader='+val+'])').addClass('hide');

    		if ( text == 'CREDIT NOTE' ) {
    			$('.sumber_tujuan').addClass('hide');
    			$('.sumber_tujuan').attr('disabled', 'disabled');
    			$('.sumber_tujuan').removeAttr('data-required');
    			$('.sumber_tujuan').val('');
				$('.supplier').removeClass('hide');
				$('.supplier').removeAttr('disabled');
				$('.supplier').attr('data-required', 1);
    		} else {
    			$('.sumber_tujuan').find('option').removeClass('hide');

    			$('.sumber_tujuan').removeClass('hide');
    			$('.sumber_tujuan').removeAttr('disabled');
    			$('.sumber_tujuan').attr('data-required', 1);
				$('.supplier').addClass('hide');
				$('.supplier').attr('disabled', 'disabled');
				$('.supplier').removeAttr('data-required', 1);
				$('.supplier').val('');

				$('.sumber_tujuan').find('option:not([data-idheader='+val+'])').addClass('hide');
    		}
    	}
    }, // end - getJurnalTrans

    getLists: function() {
        var div = $('div#riwayat');
        let dcontent = $(div).find('table.tbl_riwayat tbody');

        var err = 0;
        var err = 0;
        $.map( $(div).find('[data-required=1]'), function(ipt) {
            if ( empty( $(ipt).val() ) ) {
                $(ipt).parent().addClass('has-error');
                err++;
            } else {
                $(ipt).parent().removeClass('has-error');
            }
        });

        if ( err > 0 ) {
            bootbox.alert('Harap lengkapi data terlebih dahulu.');
        } else {
            var params = {
                'start_date': dateSQL($(div).find('#StartDate').data('DateTimePicker').date()),
                'end_date': dateSQL($(div).find('#EndDate').data('DateTimePicker').date())
            };

            $.ajax({
                url : 'accounting/Jurnal/getLists',
                data : { 'params': params },
                type : 'get',
                dataType : 'html',
                beforeSend : function(){ showLoading() },
                success : function(html){
                    $(dcontent).html( html );
                    hideLoading();
                },
            });
        }
    }, // end - getLists

	save: function (elm) {
		var dcontent = $('div#action');

		var err = 0;
		$.map( $(dcontent).find('[data-required=1]'), function(ipt) {
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
					var detail = $.map( $(dcontent).find('table tbody tr'), function(tr) {
						var jurnal_trans_sumber_tujuan_id = $(tr).find('select.sumber_tujuan').val();
						var supplier = $(tr).find('select.supplier').val();

						var _detail = {
							'tanggal': dateSQL($(tr).find('#tgl_trans').data('DateTimePicker').date()),
							'det_jurnal_trans_id': $(tr).find('select.jurnal_trans_detail').val(),
							'jurnal_trans_sumber_tujuan_id': jurnal_trans_sumber_tujuan_id,
							'supplier': supplier,
							'perusahaan': $(tr).find('select.perusahaan').val(),
							'keterangan': $(tr).find('textarea.keterangan').val(),
							'nominal': numeral.unformat($(tr).find('input.nominal').val())
						};

						return _detail;
					});

					var data = {
						'tanggal': dateSQL($(dcontent).find('#tanggal').data('DateTimePicker').date()),
						'jurnal_trans_id': $(dcontent).find('.jurnal_trans').val(),
						'unit': $(dcontent).find('.unit').val(),
						'detail': detail
					};

					$.ajax({
			            url : 'accounting/Jurnal/save',
			            data : {'params' : data},
			            type : 'POST',
			            dataType : 'JSON',
			            beforeSend : function(){ showLoading(); },
			            success : function(data){
			                hideLoading();
			                if (data.status) {
			                    bootbox.alert(data.message, function(){
			                        location.reload();
			                    });
			                } else {
			                    alertDialog(data.message);
			                }
			            }
			        });
				}
			});
		}
	}, // end - save

	edit: function (elm) {
		var dcontent = $('div#action');

		var err = 0;
		$.map( $(dcontent).find('[data-required=1]'), function(ipt) {
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
					var detail = $.map( $(dcontent).find('table tbody tr'), function(tr) {
						var jurnal_trans_sumber_tujuan_id = $(tr).find('select.sumber_tujuan').val();
						var supplier = $(tr).find('select.supplier').val();

						var _detail = {
							'tanggal': dateSQL($(tr).find('#tgl_trans').data('DateTimePicker').date()),
							'det_jurnal_trans_id': $(tr).find('select.jurnal_trans_detail').val(),
							'jurnal_trans_sumber_tujuan_id': jurnal_trans_sumber_tujuan_id,
							'supplier': supplier,
							'perusahaan': $(tr).find('select.perusahaan').val(),
							'keterangan': $(tr).find('textarea.keterangan').val(),
							'nominal': numeral.unformat($(tr).find('input.nominal').val())
						};

						return _detail;
					});

					var data = {
						'id': $(elm).data('id'),
						'tanggal': dateSQL($(dcontent).find('#tanggal').data('DateTimePicker').date()),
						'jurnal_trans_id': $(dcontent).find('.jurnal_trans').val(),
						'unit': $(dcontent).find('.unit').val(),
						'detail': detail
					};

					console.log( data );

					return;

					$.ajax({
			            url : 'accounting/Jurnal/save',
			            data : {'params' : data},
			            type : 'POST',
			            dataType : 'JSON',
			            beforeSend : function(){ showLoading(); },
			            success : function(data){
			                hideLoading();
			                if (data.status) {
			                    bootbox.alert(data.message, function(){
			                        location.reload();
			                    });
			                } else {
			                    alertDialog(data.message);
			                }
			            }
			        });
				}
			});
		}
	}, // end - edit

	delete: function (elm) {
		bootbox.confirm('Apakah anda yakin ingin meng-hapus data ?', function(result) {
			if ( result ) {
				var params = {
					'id': $(elm).data('id')
				};

				$.ajax({
		            url : 'accounting/TransaksiJurnal/delete',
		            data : {'params' : params},
		            type : 'POST',
		            dataType : 'JSON',
		            beforeSend : function(){ showLoading(); },
		            success : function(data){
		                hideLoading();
		                if (data.status) {
		                    bootbox.alert(data.message, function(){
		                        location.reload();
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

jurnal.start_up();