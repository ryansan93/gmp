var bo = {
	start_up : function () {
		bo.get_list();
	}, // end - start_up

	get_list : function () {
		var dContent = $('tbody');

		$.ajax({
            url : 'parameter/BiayaOperasional/get_list',
            data : {},
            type : 'GET',
            dataType : 'HTML',
            beforeSend : function(){ App.showLoaderInContent(dContent); },
            success : function(html){
                App.hideLoaderInContent(dContent, html);
            }
        });
	}, // end - get_list

	add_form : function () {
		$.get('parameter/BiayaOperasional/add_form',{
        },function(data){
            var _options = {
                className : 'large',
                message : data,
                addClass : 'form',
            };
            bootbox.dialog(_options).bind('shown.bs.modal', function(){
                $('input').keyup(function(){
                    $(this).val($(this).val().toUpperCase());
                });

                $('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal]').each(function(){
                    $(this).priceFormat(Config[$(this).data('tipe')]);
                });

                $("[name=tgl_berlaku]").datetimepicker({
					locale: 'id',
		            format: 'DD MMM Y',
		            minDate: moment().subtract(0, 'days').millisecond(0).second(0).minute(0).hour(0),
					useCurrent: false //Important! See issue #1075
				});
            });
        },'html');
	}, // end - add_form

	edit_form : function (elm) {
		var id = $(elm).data('id');
		$.get('parameter/BiayaOperasional/edit_form',{
			'id' : id
        },function(data){
            var _options = {
                className : 'large',
                message : data,
                addClass : 'form',
            };
            bootbox.dialog(_options).bind('shown.bs.modal', function(){
                $('input').keyup(function(){
                    $(this).val($(this).val().toUpperCase());
                });

                $('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal]').each(function(){
                    $(this).priceFormat(Config[$(this).data('tipe')]);
                });

                $("[name=tgl_berlaku]").datetimepicker({
					locale: 'id',
		            format: 'DD MMM Y',
		            minDate: moment().subtract(0, 'days').millisecond(0).second(0).minute(0).hour(0),
					useCurrent: false //Important! See issue #1075
				});
            });
        },'html');
	}, // end - edit_form

	save : function () {
		var err = 0;

		$.map( $('[data-required=1]'), function (ipt) {
			if ( empty($(ipt).val()) ) {
				$(ipt).parent().addClass('has-error');
				err++;
			} else {
				$(ipt).parent().removeClass('has-error');
			};
		});

		if ( err > 0 ) {
			bootbox.alert('Harap lengkapi data yang anda input.');
		} else {
			bootbox.confirm('Apakah anda yakin ingin menyimpan data Biaya Operasional ?', function (result) {
				if ( result ) {
					var tgl_berlaku = dateSQL($('#tgl_berlaku').data('DateTimePicker').date());
					var biaya_opr = numeral.unformat( $('input.biaya_opr').val() );

					var params = {
						'tgl_berlaku' : tgl_berlaku,
						'biaya_opr' : biaya_opr
					};

					bo.execute_save(params);
				};
			});
		};
	}, // end - save

	execute_save : function (params = null) {
		$.ajax({
            url : 'parameter/BiayaOperasional/save',
            data : {'params' : params},
            type : 'POST',
            dataType : 'JSON',
            beforeSend : function(){ showLoading(); },
            success : function(data){
                hideLoading();
                if (data.status) {
                    bootbox.alert(data.message, function(){
                        bo.get_list();
                        bootbox.hideAll();
                    });
                } else {
                    alertDialog(data.message);
                }
            }
        });
	}, // end - execute_save

	edit : function (elm) {
		var err = 0;

		$.map( $('[data-required=1]'), function (ipt) {
			if ( empty($(ipt).val()) ) {
				$(ipt).parent().addClass('has-error');
				err++;
			} else {
				$(ipt).parent().removeClass('has-error');
			};
		});

		if ( err > 0 ) {
			bootbox.alert('Harap lengkapi data yang anda input.');
		} else {
			bootbox.confirm('Apakah anda yakin ingin meng-update data Biaya Operasional ?', function (result) {
				if ( result ) {
					var id = $(elm).data('id');
					var tgl_berlaku = dateSQL($('#tgl_berlaku').data('DateTimePicker').date());
					var biaya_opr = numeral.unformat( $('input.biaya_opr').val() );

					var params = {
						'id' : id,
						'tgl_berlaku' : tgl_berlaku,
						'biaya_opr' : biaya_opr
					};

					bo.execute_edit(params);
				};
			});
		};
	}, // end - edit

	execute_edit : function (params = null) {
		$.ajax({
            url : 'parameter/BiayaOperasional/edit',
            data : {'params' : params},
            type : 'POST',
            dataType : 'JSON',
            beforeSend : function(){ showLoading(); },
            success : function(data){
                hideLoading();
                if (data.status) {
                    bootbox.alert(data.message, function(){
                        bo.get_list();
                        bootbox.hideAll();
                    });
                } else {
                    alertDialog(data.message);
                }
            }
        });
	}, // end - execute_edit
};

bo.start_up();