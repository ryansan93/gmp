var pf = {
	start_up : function () {
		pf.get_list();
	}, // end - start_up

	get_list : function () {
		var dContent = $('tbody');

		$.ajax({
            url : 'parameter/PeriodeFiskal/get_list',
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
		$.get('parameter/PeriodeFiskal/add_form',{
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

				$("[name=periode]").datetimepicker({
					locale: 'id',
		            format: 'MMM Y'
				});

				$("[name=startDate]").datetimepicker({
					locale: 'id',
		            format: 'DD MMM Y'
				});

				$("[name=endDate]").datetimepicker({
					locale: 'id',
		            format: 'DD MMM Y'
				});
            });
        },'html');
	}, // end - add_form

	edit_form : function (elm) {
		var id = $(elm).data('id');
		$.get('parameter/PeriodeFiskal/edit_form',{
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

                $("[name=periode]").datetimepicker({
					locale: 'id',
		            format: 'MMM Y'
				});
				var periode = $("[name=periode]").find('input').attr('data-tgl');
				if ( !empty(periode) ) {
					$("[name=periode]").data('DateTimePicker').date(new Date(periode));
				}

				$("[name=startDate]").datetimepicker({
					locale: 'id',
		            format: 'DD MMM Y'
				});
				var startDate = $("[name=startDate]").find('input').attr('data-tgl');
				if ( !empty(startDate) ) {
					$("[name=startDate]").data('DateTimePicker').date(new Date(startDate));
				}

				$("[name=endDate]").datetimepicker({
					locale: 'id',
		            format: 'DD MMM Y'
				});
				var endDate = $("[name=endDate]").find('input').attr('data-tgl');
				if ( !empty(endDate) ) {
					$("[name=endDate]").data('DateTimePicker').date(new Date(endDate));
				}
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
			bootbox.confirm('Apakah anda yakin ingin menyimpan data periode fiskal ?', function (result) {
				if ( result ) {
					var periode = dateSQL($('#periode').data('DateTimePicker').date());
					var startDate = dateSQL($('#startDate').data('DateTimePicker').date());
					var endDate = dateSQL($('#endDate').data('DateTimePicker').date());
					var status = ( $('.status').is(':checked') ) ? 1 : 0;

					var params = {
						'periode': periode,
						'start_date': startDate,
						'end_date': endDate,
						'status': status
					};

					pf.execute_save(params);
				};
			});
		};
	}, // end - save

	execute_save : function (params = null) {
		$.ajax({
            url : 'parameter/PeriodeFiskal/save',
            data : {'params' : params},
            type : 'POST',
            dataType : 'JSON',
            beforeSend : function(){ showLoading(); },
            success : function(data){
                hideLoading();
                if (data.status) {
                    bootbox.alert(data.message, function(){
                        pf.get_list();
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
			bootbox.confirm('Apakah anda yakin ingin meng-update data periode fiskal ?', function (result) {
				if ( result ) {
					var id = $(elm).data('id');
					var periode = dateSQL($('#periode').data('DateTimePicker').date());
					var startDate = dateSQL($('#startDate').data('DateTimePicker').date());
					var endDate = dateSQL($('#endDate').data('DateTimePicker').date());
					var status = ( $('.status').is(':checked') ) ? 1 : 0;

					var params = {
						'id' : id,
						'periode': periode,
						'start_date': startDate,
						'end_date': endDate,
						'status': status
					};

					pf.execute_edit(params);
				};
			});
		};
	}, // end - edit

	execute_edit : function (params = null) {
		$.ajax({
            url : 'parameter/PeriodeFiskal/edit',
            data : {'params' : params},
            type : 'POST',
            dataType : 'JSON',
            beforeSend : function(){ showLoading(); },
            success : function(data){
                hideLoading();
                if (data.status) {
                    bootbox.alert(data.message, function(){
                        pf.get_list();
                        bootbox.hideAll();
                    });
                } else {
                    alertDialog(data.message);
                }
            }
        });
	}, // end - execute_edit
};

pf.start_up();