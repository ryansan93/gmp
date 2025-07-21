var ju = {
	start_up: function () {
		ju.setting_up();
	}, // end - start_up

	setting_up: function() {
		$('.unit').select2();
		// $('.perusahaan').select2();
		$('.noreg').select2();

		$('div#action .unit').select2().on("select2:select", function (e) {
			ju.getNoreg();
		});

		$('div#action .perusahaan').select2().on("select2:select", function (e) {
			ju.filter();
		});
		$('div#riwayat .jurnal_trans_detail').select2().on("select2:select", function (e) {
			ju.filter();
		});

		$('.jurnal_trans').select2().on("select2:select", function (e) {
            ju.getJurnalTrans();
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
	        $(row).find('select.jurnal_trans_detail').select2('destroy')
	                                   .removeAttr('data-live-search')
	                                   .removeAttr('data-select2-id')
	                                   .removeAttr('aria-hidden')
	                                   .removeAttr('tabindex');
	        $(row).find('select.jurnal_trans_detail option').removeAttr('data-select2-id');

	        let newRow = row.clone();

	        newRow.find('input, select, textarea').val('');
	        newRow.find('.sumber_coa').removeAttr('data-coa');
	        newRow.find('.sumber_coa label').text('-');
	        newRow.find('.tujuan_coa').removeAttr('data-coa');
	        newRow.find('.tujuan_coa label').text('-');

	        $(newRow).find('#tgl_trans').datetimepicker({
	            locale: 'id',
	            format: 'DD MMM Y'
	        });

	        row.after(newRow);

	        $.map( $(tbody).find('tr'), function(tr) {
	            $(tr).find('select.jurnal_trans_detail').select2().on("select2:select", function (e) {
		            ju.getSumberTujuanCoa( this, e.params.data.id );
		        });
	        	// $(tr).find('select.perusahaan').select2();
	        	// $(tr).find('select.unit').select2();
	        });
        }

        $('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal], [data-tipe=decimal3],[data-tipe=decimal4], [data-tipe=number]').each(function(){
			$(this).priceFormat(Config[$(this).data('tipe')]);
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

        ju.loadForm($(elm), edit, href);
    }, // end - changeTabActive

    loadForm: function(elm, edit = null, href = null) {
        var dcontent = $('div#'+href);

        var params = {
            'id': $(elm).data('id')
        };

        $.ajax({
            url : 'accounting/JurnalUnit/loadForm',
            data : {
                'params' :  params,
                'edit' :  edit
            },
            type : 'GET',
            dataType : 'HTML',
            beforeSend : function(){ App.showLoaderInContent(dcontent); },
            success : function(html){
                App.hideLoaderInContent(dcontent, html);

                ju.setting_up();

                if ( !empty(edit) ) {
                	ju.getJurnalTrans();
                	ju.getNoreg();
                }
            },
        });
    }, // end - loadForm

    getJurnalTrans: function() {
    	var sel_jurnal_trans = $('select.jurnal_trans');
    	var val = $(sel_jurnal_trans).select2('val');

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
    		$.map( $('div#action').find('table tbody tr'), function(tr) {
	    		$(tr).find('.jurnal_trans_detail').removeAttr('disabled');
	    		$(tr).find('.jurnal_trans_detail').find('option').removeAttr('disabled');
	    		$(tr).find('.jurnal_trans_detail').find('option:not([data-idheader='+val+'])').attr('disabled', 'disabled');

    			$(tr).find('.jurnal_trans_detail').select2().on("select2:select", function (e) {
		            ju.getSumberTujuanCoa( this, e.params.data.id );
		        });
    		});
    	}
    }, // end - getJurnalTrans

    getSumberTujuanCoa: function( elm, id ) {
    	var tr = $(elm).closest('tr');

    	$.ajax({
            url : 'accounting/JurnalUnit/getSumberTujuanCoa',
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

    getNoreg: function() {
    	var unit = $('div#action .unit').select2('val');

    	var params = {
    		'unit': unit
    	};

		$.ajax({
            url : 'accounting/JurnalUnit/getNoreg',
            data : { 'params': params },
            type : 'post',
            dataType : 'json',
            beforeSend : function(){ showLoading() },
            success : function(data){
                hideLoading();

               	var noreg = $('div#action .noreg').attr('data-noreg');

                var opt = '<option value="">-- Pilih Plasma --</option>';
                if ( data.status == 1 ) {
                	if ( data.content.length > 0 ) {
	                	for (var i = 0; i < data.content.length; i++) {
	                		var selected = null;

	                		if ( !empty(noreg) && noreg.trim() == data.content[i].noreg.trim() ) {
	                			selected = 'selected';
	                		}

	                		opt += '<option value="'+data.content[i].noreg+'" '+selected+' >'+data.content[i].tgl_terima+' | '+'KDG : '+data.content[i].kandang+' | '+data.content[i].nama_mitra+'</option>'
	                	}

                		$('div#action .noreg').removeAttr('disabled', 'disabled')
	                	$('div#action .noreg').html( opt );
                		$('div#action .noreg').select2();
                	} else {
                		$('div#action .noreg').attr('disabled', 'disabled')
                		$('div#action .noreg').html( opt );
                		$('div#action .noreg').select2();
                	}
                } else {
                	bootbox.alert( data.message );
                }
            },
        });
    }, // end - getNoreg

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
                'end_date': dateSQL($(div).find('#EndDate').data('DateTimePicker').date()),
                'unit': $(div).find('.unit').select2('val')
            };

            $.ajax({
                url : 'accounting/JurnalUnit/getLists',
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
							'det_jurnal_trans_id': $(tr).find('select.jurnal_trans_detail').select2('val'),
							'sumber': $(tr).find('.sumber_coa label').text(),
							'sumber_coa': $(tr).find('.sumber_coa').attr('data-coa'),
							'tujuan': $(tr).find('.tujuan_coa label').text(),
							'tujuan_coa': $(tr).find('.tujuan_coa').attr('data-coa'),
							'pic': $(tr).find('.pic').val(),
							'nominal': numeral.unformat($(tr).find('input.nominal').val()),
							'keterangan': $(tr).find('textarea.keterangan').val(),
						};

						return _detail;
					});

					var data = {
						'tanggal': dateSQL($(dcontent).find('#tanggal').data('DateTimePicker').date()),
						'jurnal_trans_id': $(dcontent).find('.jurnal_trans').val(),
						'unit': $(dcontent).find('select.unit').select2('val'),
						'perusahaan': $(dcontent).find('select.perusahaan').select2('val'),
						'noreg': $(dcontent).find('select.noreg').select2('val'),
						'detail': detail
					};

					$.ajax({
			            url : 'accounting/JurnalUnit/save',
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
										ju.getLists();
									}

			                        var btn = '<button data-id="'+data.content.id+'"></button>';
			                        ju.loadForm( $(btn), null, 'action' );
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
							'kode_jurnal': $(tr).find('.kode_jurnal').val(),
							'tanggal': dateSQL($(tr).find('#tgl_trans').data('DateTimePicker').date()),
							'det_jurnal_trans_id': $(tr).find('select.jurnal_trans_detail').select2('val'),
							'sumber': $(tr).find('.sumber_coa label').text(),
							'sumber_coa': $(tr).find('.sumber_coa').attr('data-coa'),
							'tujuan': $(tr).find('.tujuan_coa label').text(),
							'tujuan_coa': $(tr).find('.tujuan_coa').attr('data-coa'),
							'pic': $(tr).find('.pic').val(),
							'nominal': numeral.unformat($(tr).find('input.nominal').val()),
							'keterangan': $(tr).find('textarea.keterangan').val().replace(/^\s*|\s*$/g,"")
						};

						return _detail;
					});

					var data = {
						'id': $(elm).data('id'),
						'tanggal': dateSQL($(dcontent).find('#tanggal').data('DateTimePicker').date()),
						'jurnal_trans_id': $(dcontent).find('.jurnal_trans').val(),
						'unit': $(dcontent).find('select.unit').select2('val'),
						'perusahaan': $(dcontent).find('select.perusahaan').select2('val'),
						'noreg': $(dcontent).find('select.noreg').select2('val'),
						'detail': detail
					};

					$.ajax({
			            url : 'accounting/JurnalUnit/edit',
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
										ju.getLists();
									}

			                        var btn = '<button data-id="'+data.content.id+'"></button>';
			                        ju.loadForm( $(btn), null, 'action' );
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
		            url : 'accounting/JurnalUnit/delete',
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
									ju.getLists();
								}

		                        var btn = '<button data-id=""></button>';
		                        ju.loadForm( $(btn), null, 'action' );
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

ju.start_up();