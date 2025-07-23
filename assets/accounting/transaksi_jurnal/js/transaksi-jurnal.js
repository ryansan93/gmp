var tj = {
	start_up: function () {
		tj.setting_up();
	}, // end - start_up

	setting_up: function() {
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
        $('.fitur').select2();
        $('.sumber').select2();
        $('.tujuan').select2();
        $('.peruntukan').select2();
	}, // end - setting_up

	addRow: function(elm) {
        let row = $(elm).closest('tr');
        var tbody = $(row).closest('tbody');

        $(row).find('select.sumber').select2('destroy')
                                   .removeAttr('data-live-search')
                                   .removeAttr('data-select2-id')
                                   .removeAttr('aria-hidden')
                                   .removeAttr('tabindex');
        $(row).find('select.sumber option').removeAttr('data-select2-id');

        $(row).find('select.tujuan').select2('destroy')
                                   .removeAttr('data-live-search')
                                   .removeAttr('data-select2-id')
                                   .removeAttr('aria-hidden')
                                   .removeAttr('tabindex');
        $(row).find('select.tujuan option').removeAttr('data-select2-id');

        let newRow = row.clone();

        newRow.find('input, select').val('');
        row.after(newRow);

		if ( empty($(newRow).find('input.kode').val()) ) {
			$(newRow).find('button.btn-danger').closest('div').removeClass('hide');
		} else {
			$(newRow).find('button.btn-danger').closest('div').addClass('hide');
		}

        $.map( $(tbody).find('tr'), function(tr) {
            $(tr).find('select.sumber').select2();
        	$(tr).find('select.tujuan').select2();
        });
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

        tj.load_form($(elm), edit, href);
    }, // end - changeTabActive

    load_form: function(elm, edit = null, href = null) {
        var dcontent = $('div#'+href);

        var params = {
            'id': $(elm).data('id')
        };

        $.ajax({
            url : 'accounting/TransaksiJurnal/load_form',
            data : {
                'params' :  params,
                'edit' :  edit
            },
            type : 'GET',
            dataType : 'HTML',
            beforeSend : function(){ App.showLoaderInContent(dcontent); },
            success : function(html){
                App.hideLoaderInContent(dcontent, html);

                tj.setting_up();
            },
        });
    }, // end - load_form

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
					var detail = $.map( $(dcontent).find('table.detail tbody tr'), function(tr) {
						var submit_periode = 0;

						var checkbox = $(tr).find('input[type=checkbox]');
						if ( $(checkbox).prop('checked') ) {
							submit_periode = 1;
						}

						var _detail = {
							'nama': $(tr).find('input').val().toUpperCase(),
							'sumber': $(tr).find('select.sumber').select2().find(':selected').data('nama'),
							'sumber_coa': $(tr).find('select.sumber').select2('val'),
							'tujuan': $(tr).find('select.tujuan').select2().find(':selected').data('nama'),
							'tujuan_coa': $(tr).find('select.tujuan').select2('val'),
							'submit_periode': submit_periode
						};

						return _detail;
					});

					var data = {
						'nama': $(dcontent).find('.nama').val().toUpperCase(),
						'peruntukan': $(dcontent).find('.peruntukan').select2().val(),
						'kode_voucher': $(dcontent).find('.kode_voucher').val(),
						'fitur': $(dcontent).find('.fitur').select2().val(),
						'jurnal_manual': ($(dcontent).find('.jurnal_manual').is(':checked')) ? 1 : 0,
						'detail': detail
					};

					$.ajax({
			            url : 'accounting/TransaksiJurnal/save',
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
					var detail = $.map( $(dcontent).find('table.detail tbody tr'), function(tr) {
						var submit_periode = 0;

						var checkbox = $(tr).find('input[type=checkbox]');
						if ( $(checkbox).prop('checked') ) {
							submit_periode = 1;
						}

						var _detail = {
							'nama': $(tr).find('input.nama_detail').val().toUpperCase(),
							'sumber': $(tr).find('select.sumber').select2().find(':selected').data('nama'),
							'sumber_coa': $(tr).find('select.sumber').select2('val'),
							'tujuan': $(tr).find('select.tujuan').select2().find(':selected').data('nama'),
							'tujuan_coa': $(tr).find('select.tujuan').select2('val'),
							'submit_periode': submit_periode,
							'kode': $(tr).find('input.kode').val().toUpperCase()
						};

						return _detail;
					});

					var data = {
						'id': $(elm).data('id'),
						'nama': $(dcontent).find('.nama').val().toUpperCase(),
						'peruntukan': $(dcontent).find('.peruntukan').select2().val(),
						'kode_voucher': $(dcontent).find('.kode_voucher').val(),
						'fitur': $(dcontent).find('.fitur').select2().val(),
						'jurnal_manual': ($(dcontent).find('.jurnal_manual').is(':checked')) ? 1 : 0,
						'detail': detail
					};

					$.ajax({
			            url : 'accounting/TransaksiJurnal/edit',
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

tj.start_up();