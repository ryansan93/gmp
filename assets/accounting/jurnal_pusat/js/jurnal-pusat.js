var jp = {
	start_up: function () {
		jp.setting_up();
	}, // end - start_up

	setting_up: function() {
		$('.unit').select2();
		$('div#action select.perusahaan').select2();
		$('div#action select.supplier').select2();

		$('div#riwayat select.perusahaan').select2();
		$('div#riwayat select.jurnal_trans_detail').select2();
		// $('div#riwayat .perusahaan').select2().on("select2:select", function (e) {
		// 	jp.filter();
		// });
		// $('div#riwayat .jurnal_trans_detail').select2().on("select2:select", function (e) {
		// 	jp.filter();
		// });

		$('.jurnal_trans').select2().on("select2:select", function (e) {
            jp.getJurnalTrans();
        });

        $('#tanggal, #tgl_trans, #StartDate, #EndDate').datetimepicker({
            locale: 'id',
            format: 'DD MMM Y'
        });

        var tgl = $('#tanggal').find('input').data('tgl');
        if ( !empty(tgl) ) {
        	$('#tanggal').data('DateTimePicker').date( moment(new Date(tgl)) );
        }
        $.map( $('div#action').find('table tbody tr'), function(tr) {
        	$(tr).find('.date').datetimepicker({
	            locale: 'id',
	            format: 'DD MMM Y'
	        });

        	var tgl = $(tr).find('.date input').data('tgl');

	        if ( !empty(tgl) ) {
	        	$(tr).find('.date').data('DateTimePicker').date( moment(new Date(tgl)) );
	        }
        });

        $('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal], [data-tipe=decimal3],[data-tipe=decimal4], [data-tipe=number]').each(function(){
		    $(this).priceFormat(Config[$(this).data('tipe')]);
		  });
	}, // end - setting_up

	addRow: function(elm) {
        let row = $(elm).closest('tr');
        let tbody = $(row).closest('tbody');

        var err = 0;

        $(row).find('[data-required="1"]').parent().removeClass('has-error');
        $.map( $(row).find('[data-required="1"]'), function(ipt) {
        	if ( empty($(ipt).val()) ) {
        		$(ipt).parent().addClass('has-error');
        		err++;
        	}
        });

        if ( err == 0 ) {
	        $(row).find('select.jurnal_trans_detail, select.perusahaan, select.unit').select2('destroy')
	                                   .removeAttr('data-live-search')
	                                   .removeAttr('data-select2-id')
	                                   .removeAttr('aria-hidden')
	                                   .removeAttr('tabindex');
	        $(row).find('select.jurnal_trans_detail option, select.perusahaan option, select.unit option').removeAttr('data-select2-id');

	        let newRow = row.clone();

	        newRow.find('input, select, textarea').val('');
	        newRow.find('.sumber_coa').removeAttr('data-coa');
	        newRow.find('.sumber_coa label').text('-');
	        newRow.find('.tujuan_coa').removeAttr('data-coa');
	        newRow.find('.tujuan_coa label').text('-');

			newRow.find('div.submit_periode').addClass('hide');
			newRow.find('div.submit_periode input').removeAttr('data-required');
			newRow.find('div.submit_periode input').val('');

	        $(newRow).find('#tgl_trans, #tgl_cn').datetimepicker({
	            locale: 'id',
	            format: 'DD MMM Y'
	        });

	        row.after(newRow);

	        $.map( $(tbody).find('tr'), function(tr) {
	            $(tr).find('select.jurnal_trans_detail').select2().on("select2:select", function (e) {
		            jp.getSumberTujuanCoa( this, e.params.data.id );

					var div_sp = $(tr).find('div.submit_periode');

					var submit_periode = e.params.data.element.dataset.sp;
					if ( submit_periode == 1 ) {
						$(div_sp).removeClass('hide');
						$(div_sp).find('input').attr('data-required', 1);
					} else {
						$(div_sp).addClass('hide');
						$(div_sp).find('input').removeAttr('data-required');
						$(div_sp).find('input').val('');
					}
		        });
	        	$(tr).find('select.perusahaan').select2();
	        	$(tr).find('select.unit').select2();
	        });

	        $('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal], [data-tipe=decimal3],[data-tipe=decimal4], [data-tipe=number]').each(function(){
				$(this).priceFormat(Config[$(this).data('tipe')]);
			});
        }
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

        jp.loadForm($(elm), edit, href);
    }, // end - changeTabActive

    loadForm: function(elm, edit = null, href = null) {
        var dcontent = $('div#'+href);

        var params = {
            'id': $(elm).data('id')
        };

        $.ajax({
            url : 'accounting/JurnalPusat/loadForm',
            data : {
                'params' :  params,
                'edit' :  edit
            },
            type : 'GET',
            dataType : 'HTML',
            beforeSend : function(){ App.showLoaderInContent(dcontent); },
            success : function(html){
                App.hideLoaderInContent(dcontent, html);

                jp.setting_up();
				jp.hitTotal();

                if ( !empty(edit) ) {
                	jp.getJurnalTrans();
                }
            },
        });
    }, // end - loadForm

	hitTotal: function() {
		var div = $('div#action');

		var total = 0;
		$.map( $(div).find('label.nominal'), function(label) {
			var nominal = parseFloat(numeral.unformat($(label).text()));

			total += nominal;
		});

		$(div).find('label.total').text(numeral.formatDec( total ));
	}, // end - hitTotal

    getJurnalTrans: function() {
    	var div = $('div#action');

    	var sel_jurnal_trans = $('select.jurnal_trans');
    	var val = $(sel_jurnal_trans).select2('val');

    	if ( empty(val) ) {
    		$('div#action .jurnal_trans_detail').val('');
    		$('div#action .jurnal_trans_detail').attr('disabled', 'disabled');
    		$('.sumber_tujuan').val('');
    		$('.sumber_tujuan').attr('disabled', 'disabled');
    		$('.sumber_tujuan').removeClass('hide');
    		$('.supplier').val('');
    		$('.supplier').attr('disabled', 'disabled');
    		$('.supplier').addClass('hide');

			$('div.submit_periode').addClass('hide');
			$('div.submit_periode').find('input').removeAttr('data-required');
			$('div.submit_periode').find('input').val('');
    	} else {
    		$.map( $('div#action').find('table tbody tr'), function(tr) {
	    		$(tr).find('.jurnal_trans_detail').removeAttr('disabled');
	    		$(tr).find('.jurnal_trans_detail').find('option').removeAttr('disabled');
	    		$(tr).find('.jurnal_trans_detail').find('option:not([data-idheader='+val+'])').attr('disabled', 'disabled');

    			$(tr).find('.jurnal_trans_detail').select2().on("select2:select", function (e) {
		            jp.getSumberTujuanCoa( this, e.params.data.id );

					var div_sp = $(tr).find('div.submit_periode');

					var submit_periode = e.params.data.element.dataset.sp;
					if ( submit_periode == 1 ) {
						$(div_sp).removeClass('hide');
						$(div_sp).find('input').attr('data-required', 1);
					} else {
						$(div_sp).addClass('hide');
						$(div_sp).find('input').removeAttr('data-required');
						$(div_sp).find('input').val('');
					}
		        });

				$(tr).find('#tgl_cn').datetimepicker({
					locale: 'id',
					format: 'DD MMM Y'
				});
				var tgl = $(tr).find('#tgl_cn input').data('tgl');
	
				if ( !empty(tgl) ) {
					$(tr).find('#tgl_cn').data('DateTimePicker').date( moment(new Date(tgl)) );
				}
    		});
    	}
    }, // end - getJurnalTrans

    getSumberTujuanCoa: function( elm, id ) {
    	var tr = $(elm).closest('tr');

    	$.ajax({
            url : 'accounting/JurnalPusat/getSumberTujuanCoa',
            data : { 'params': id },
            type : 'post',
            dataType : 'json',
            beforeSend : function(){ showLoading() },
            success : function(data){
                hideLoading();

                if ( data.status == 1 ) {
                	$(tr).find('.sumber_coa label').text( data.content.sumber+' ('+data.content.sumber_coa+')' );
                	$(tr).find('.sumber_coa').attr( 'data-coa', data.content.sumber_coa );
                	$(tr).find('.tujuan_coa label').text( data.content.tujuan+' ('+data.content.tujuan_coa+')' );
                	$(tr).find('.tujuan_coa').attr( 'data-coa', data.content.tujuan_coa );
                } else {
                	bootbox.alert( data.message );
                }
            },
        });
    }, // end - getSumberTujuanCoa

    filter: function () {
    	var filter = [];

    	var idx = 0;
    	$.map( $('select.filter'), function(select) {
    		if ( !empty( $(select).select2('val') ) ) {
    			filter[ idx ] = {
    				'target': $(select).attr('data-target'),
    				'text': $(select).find('option:selected').text().trim()
    			};

    			idx++;
    		}
    	});

    	if ( filter.length > 0 ) {
    		$.map( $('div#riwayat tbody').find('tr'), function(tr) {
    			var hide = 0;
    			for (var i = 0; i < filter.length; i++) {
    				var td_val = $(tr).find('td.'+filter[i].target).html();

    				if ( td_val.trim().toUpperCase().indexOf(filter[i].text) == -1 ) {
    					hide = 1;
    				}
    			}

    			if ( hide == 1 ) {
    				$(tr).hide();
    			} else {
    				$(tr).show();
    			}
			});
    	} else {
    		$('div#riwayat tbody').find('tr').show();
    	}
    }, // end - filter

    getLists: function() {
        var div = $('div#riwayat');
        let dcontent = $(div).find('table.tbl_riwayat tbody');

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
                'end_date': dateSQL($(div).find('#EndDate').data('DateTimePicker').date()),
                'jurnal_trans_detail': $(div).find('select.jurnal_trans_detail').select2('val'),
                'perusahaan': $(div).find('select.perusahaan').select2('val')
            };

            $.ajax({
                url : 'accounting/JurnalPusat/getLists',
                data : { 'params': params },
                type : 'get',
                dataType : 'html',
                beforeSend : function(){ showLoading() },
                success : function(html){
                    $(dcontent).html( html );

                    $(div).find('select.jurnal_trans_detail').select2();
					$(div).find('select.perusahaan').select2();

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
							'det_jurnal_trans_id': $(tr).find('select.jurnal_trans_detail').select2('val'),
							'supplier': $(tr).find('select.supplier').select2('val'),
							'invoice': $(tr).find('input.invoice').val(),
							'submit_periode': (!empty($(tr).find('div.submit_periode input').val())) ? dateSQL($(tr).find('#tgl_cn').data('DateTimePicker').date()) : null,
							'sumber': $(tr).find('.sumber_coa label').text(),
							'sumber_coa': $(tr).find('.sumber_coa').attr('data-coa'),
							'tujuan': $(tr).find('.tujuan_coa label').text(),
							'tujuan_coa': $(tr).find('.tujuan_coa').attr('data-coa'),
							'perusahaan': $(tr).find('select.perusahaan').select2('val'),
							'unit': $(tr).find('select.unit').select2('val'),
							'nominal': numeral.unformat($(tr).find('input.nominal').val()),
							'keterangan': $(tr).find('textarea.keterangan').val().replace(/^\s*|\s*$/g,""),
							'no_bukti': $(tr).find('input.no_bukti').val()
						};

						return _detail;
					});

					var data = {
						'tanggal': dateSQL($(dcontent).find('#tanggal').data('DateTimePicker').date()),
						'jurnal_trans_id': $(dcontent).find('.jurnal_trans').val(),
						'detail': detail
					};

					$.ajax({
			            url : 'accounting/JurnalPusat/save',
			            data : {'params' : data},
			            type : 'POST',
			            dataType : 'JSON',
			            beforeSend : function(){ showLoading(); },
			            success : function(data){
			                hideLoading();
			                if (data.status) {
			                    bootbox.alert(data.message, function(){
			                    	var start_date = $('#StartDate input').val();
									var end_date = $('#EndDate input').val();

									if ( !empty(start_date) && !empty(end_date) ) {
										jp.getLists();
									}

			                        var btn = '<button data-id="'+data.content.id+'"></button>';
			                        jp.loadForm( $(btn), null, 'action' );
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
							'det_jurnal_trans_id': $(tr).find('select.jurnal_trans_detail').select2('val'),
							'supplier': $(tr).find('select.supplier').select2('val'),
							'invoice': $(tr).find('input.invoice').val(),
							'submit_periode': (!empty($(tr).find('div.submit_periode input').val())) ? dateSQL($(tr).find('#tgl_cn').data('DateTimePicker').date()) : null,
							'sumber': $(tr).find('.sumber_coa label').text(),
							'sumber_coa': $(tr).find('.sumber_coa').attr('data-coa'),
							'tujuan': $(tr).find('.tujuan_coa label').text(),
							'tujuan_coa': $(tr).find('.tujuan_coa').attr('data-coa'),
							'perusahaan': $(tr).find('select.perusahaan').select2('val'),
							'unit': $(tr).find('select.unit').select2('val'),
							'nominal': numeral.unformat($(tr).find('input.nominal').val()),
							'keterangan': $(tr).find('textarea.keterangan').val().replace(/^\s*|\s*$/g,""),
							'no_bukti': $(tr).find('input.no_bukti').val(),
							'kode_jurnal': $(tr).find('input.kode_jurnal').val()
						};

						return _detail;
					});

					var data = {
						'id': $(elm).data('id'),
						'tanggal': dateSQL($(dcontent).find('#tanggal').data('DateTimePicker').date()),
						'jurnal_trans_id': $(dcontent).find('.jurnal_trans').val(),
						'detail': detail
					};

					$.ajax({
			            url : 'accounting/JurnalPusat/edit',
			            data : {'params' : data},
			            type : 'POST',
			            dataType : 'JSON',
			            beforeSend : function(){ showLoading(); },
			            success : function(data){
			                hideLoading();
			                if (data.status) {
			                    bootbox.alert(data.message, function(){
			                        var start_date = $('#StartDate input').val();
									var end_date = $('#EndDate input').val();

									if ( !empty(start_date) && !empty(end_date) ) {
										jp.getLists();
									}

			                        var btn = '<button data-id="'+data.content.id+'"></button>';
			                        jp.loadForm( $(btn), null, 'action' );
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
		            url : 'accounting/JurnalPusat/delete',
		            data : {'params' : params},
		            type : 'POST',
		            dataType : 'JSON',
		            beforeSend : function(){ showLoading(); },
		            success : function(data){
		                hideLoading();
		                if (data.status) {
		                    bootbox.alert(data.message, function(){
		                        var start_date = $('#StartDate input').val();
								var end_date = $('#EndDate input').val();

								if ( !empty(start_date) && !empty(end_date) ) {
									jp.getLists();
								}

		                        var btn = '<button data-id=""></button>';
		                        jp.loadForm( $(btn), null, 'action' );
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

jp.start_up();