var saj = {
    startUp: function() {
        saj.settingUp();
    }, // end - startUp

    settingUp: function() {
        $('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal], [data-tipe=decimal3],[data-tipe=decimal4], [data-tipe=number]').each(function(){
		    $(this).priceFormat(Config[$(this).data('tipe')]);
		});

        $('#tglBerlaku').datetimepicker({
            locale: 'id',
            format: 'DD MMM Y'
        });

        var tgl = $('#tglBerlaku input').data('tgl');
        if ( !empty(tgl) ) {
            $('#tglBerlaku').data('DateTimePicker').date( moment(new Date(tgl)) );
        }

        $('.fitur').select2();

        saj.settingUpDetail();
    }, // end - settingUp

    settingUpDetail: function() {
        $.map( $('#action table tbody tr'), function(_tr) {
            $(_tr).find('.det_jurnal_trans').select2().on("select2:select", function (e) {
                var tr = $(this).closest('tr');
    
                if ( !empty(e.params.data.id) ) {
                    var asal = e.params.data.element.dataset.asal;
                    var tujuan = e.params.data.element.dataset.tujuan;
        
                    $(tr).find('.coa_asal, .query_coa_asal, .coa_tujuan, .query_coa_tujuan').prop("disabled", true);
    
                    $(tr).find('.coa_asal').val(asal).trigger('change');
                    $(tr).find('.coa_tujuan').val(tujuan).trigger('change');
                } else {
                    $(tr).find('.coa_asal, .query_coa_asal, .coa_tujuan, .query_coa_tujuan').prop("disabled", false);

                    $(tr).find('.coa_asal').val(null).trigger('change');
                    $(tr).find('.coa_tujuan').val(null).trigger('change');
                }
            });

            $(_tr).find('.coa_asal, .query_coa_asal, .coa_tujuan, .query_coa_tujuan').prop("disabled", false);

            $(_tr).find('.coa_asal').select2();
            $(_tr).find('.coa_tujuan').select2();
        });
    }, // end - settingUpDetail

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

			saj.loadForm(v_id, resubmit);
		};
	}, // end - changeTabActive

	loadForm: function(v_id = null, resubmit = null) {
		var div_action = $('div#action');

		$.ajax({
			url : 'parameter/SettingAutomaticJurnal/loadForm',
			data : {
				'id' :  v_id,
				'resubmit' : resubmit
			},
			type : 'GET',
			dataType : 'HTML',
			beforeSend : function(){ showLoading(); },
			success : function(html){
				$(div_action).html(html);

                saj.settingUp();

				hideLoading();
			},
		});
	}, // end - loadForm

    addRow: function(elm) {
        let row = $(elm).closest('tr');
        let tbody = $(row).closest('tbody');

        $(row).find('.det_jurnal_trans, .coa_asal, .coa_tujuan').select2('destroy')
                                    .removeAttr('data-live-search')
                                    .removeAttr('data-select2-id')
                                    .removeAttr('aria-hidden')
                                    .removeAttr('tabindex');
        $(row).find('.det_jurnal_trans option, .coa_asal option, .coa_tujuan option').removeAttr('data-select2-id');

        let newRow = row.clone();
        newRow.find('input, select').val('');
        $(tbody).append(newRow);

        saj.settingUpDetail();

        saj.settingUrutan(elm);
    }, // end - addRow

    removeRow: function(elm) {
        let row = $(elm).closest('tr');
        let tbody = $(row).closest('tbody');

        if ( $(tbody).find('tr').length > 1 ) {
            $(row).remove();

            var tr_last = $(tbody).find('tr:last');
            saj.settingUrutan(tr_last);
        } else {
            $(row).find('input').val('');
            $(row).find('input.urut').val(1);

            $(row).find('.det_jurnal_trans').val(null).trigger('change');

            $(row).find('.coa_asal').val(null).trigger('change');
            $(row).find('.coa_tujuan').val(null).trigger('change');

            $(row).find('.coa_asal').prop("disabled", false);
            $(row).find('.coa_tujuan').prop("disabled", false);
        }
    }, // end - deleteRow

    settingUrutan: function(elm) {
        let row = $(elm).closest('tr');
        let tbody = $(row).closest('tbody');

        var urut = 1;
        $.map( $(tbody).find('tr'), function(tr) {
            $(tr).find('input.urut').val(urut);

            urut++;
        });
    }, // end - settingUrutan

    getLists: function() {
        var div = $('div#history');
        var dcontent = $(div).find('table tbody');

        var params = {
            'startDate': !empty($(div).find('#StartDate input').val()) ? dateSQL($(div).find('#StartDate').data('DateTimePicker').date()) : null,
            'endDate': !empty($(div).find('#EndDate input').val()) ? dateSQL($(div).find('#EndDate').data('DateTimePicker').date()) : null
        };

        $.ajax({
            url :'parameter/SettingAutomaticJurnal/getLists',
            data : {'params': params},
            type : 'GET',
            dataType : 'HTML',
            beforeSend : function(){ App.showLoaderInContent(dcontent); },
            success : function(html){
                App.hideLoaderInContent(dcontent, html);
            }
        });
    }, // end - getLists

    save: function() {
        var div = $('div#action');

		var err = 0;
		$.map( $(div).find('[data-required=1]'), function(ipt) {
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
                if (result) {
                    var detail = $.map( $(div).find('table tbody tr'), function(tr) {
                        var _detail = {
                            'urut': numeral.unformat($(tr).find('input.urut').val()),
                            'det_jurnal_trans_kode': $(tr).find('.det_jurnal_trans').val(),
                            'query_coa_asal': $(tr).find('.query_coa_asal').val(),
                            'coa_asal': $(tr).find('.coa_asal').select2().val(),
                            'query_coa_tujuan': $(tr).find('.query_coa_tujuan').val(),
                            'coa_tujuan': $(tr).find('.coa_tujuan').select2().val(),
                            'keterangan': $(tr).find('input.keterangan').val()
                        };
        
                        return _detail;
                    });
        
                    var data = {
                        'tgl_berlaku': dateSQL($(div).find('#tglBerlaku').data('DateTimePicker').date()),
                        'id_detfitur': $(div).find('.fitur').select2().val(),
                        'query': $(div).find('textarea.query').val(),
                        'tbl_name': $(div).find('input.tbl_name').val(),
                        'detail': detail
                    };
        
                    $.ajax({
                        url :'parameter/SettingAutomaticJurnal/save',
                        data : {'params': data},
                        type : 'POST',
                        dataType : 'JSON',
                        beforeSend : function(){ showLoading(); },
                        success : function(data){
                            hideLoading();
                            if ( data.status == 1 ) {
                                bootbox.alert(data.message, function(){
                                    saj.loadForm(data.content.id);
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

    edit: function(elm) {
        var div = $('div#action');

		var err = 0;
		$.map( $(div).find('[data-required=1]'), function(ipt) {
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
                if (result) {
                    var detail = $.map( $(div).find('table tbody tr'), function(tr) {
                        var _detail = {
                            'urut': numeral.unformat($(tr).find('input.urut').val()),
                            'det_jurnal_trans_kode': $(tr).find('.det_jurnal_trans').val(),
                            'query_coa_asal': $(tr).find('.query_coa_asal').val(),
                            'coa_asal': $(tr).find('.coa_asal').select2().val(),
                            'query_coa_tujuan': $(tr).find('.query_coa_tujuan').val(),
                            'coa_tujuan': $(tr).find('.coa_tujuan').select2().val(),
                            'keterangan': $(tr).find('input.keterangan').val()
                        };
        
                        return _detail;
                    });
        
                    var data = {
                        'id': $(elm).attr('data-id'),
                        'tgl_berlaku': dateSQL($(div).find('#tglBerlaku').data('DateTimePicker').date()),
                        'id_detfitur': $(div).find('.fitur').select2().val(),
                        'query': $(div).find('textarea.query').val(),
                        'tbl_name': $(div).find('input.tbl_name').val(),
                        'detail': detail
                    };
        
                    $.ajax({
                        url :'parameter/SettingAutomaticJurnal/edit',
                        data : {'params': data},
                        type : 'POST',
                        dataType : 'JSON',
                        beforeSend : function(){ showLoading(); },
                        success : function(data){
                            hideLoading();
                            if ( data.status == 1 ) {
                                bootbox.alert(data.message, function(){
                                    saj.loadForm(data.content.id);
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

    delete: function(elm) {
        var div = $('div#action');

        bootbox.confirm('Apakah anda yakin ingin meng-hapus data ?', function(result) {
            if (result) {    
                var data = {
                    'id': $(elm).attr('data-id')
                };
    
                $.ajax({
                    url :'parameter/SettingAutomaticJurnal/delete',
                    data : {'params': data},
                    type : 'POST',
                    dataType : 'JSON',
                    beforeSend : function(){ showLoading(); },
                    success : function(data){
                        hideLoading();
                        if ( data.status == 1 ) {
                            bootbox.alert(data.message, function(){
                                saj.loadForm();
                            });
                        } else {
                            bootbox.alert(data.message);
                        }
                    }
                });
            }
        });
    }, // end - delete
};

saj.startUp();