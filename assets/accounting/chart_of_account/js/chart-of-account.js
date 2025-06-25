var coa = {
	start_up: function () {
		coa.get_list();
	}, // end - start_up

	get_list : function () {
		var dContent = $('tbody');

		$.ajax({
            url : 'accounting/ChartOfAccount/get_lists',
            data : {},
            type : 'GET',
            dataType : 'HTML',
            beforeSend : function(){ App.showLoaderInContent(dContent); },
            success : function(html){
                App.hideLoaderInContent(dContent, html);
            }
        });
	}, // end - get_list

	add_form: function () {
		$.get('accounting/ChartOfAccount/add_form',{
        },function(data){
            var _options = {
                className : 'large',
                message : data,
                addClass : 'form',
            };
            bootbox.dialog(_options).bind('shown.bs.modal', function(){
            	$(this).find('.modal-header').css({'padding-top': '0px'});
            	$(this).find('.modal-dialog').css({'width': '60%', 'max-width': '100%'});

                $('input').keyup(function(){
                    $(this).val($(this).val().toUpperCase());
                });

                $('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal]').each(function(){
                    $(this).priceFormat(Config[$(this).data('tipe')]);
                });
            });
        },'html');
	}, // end - add_form

	view_form: function (elm) {
		$.get('accounting/ChartOfAccount/view_form',{
			'id': $(elm).data('id')
        },function(data){
            var _options = {
                className : 'large',
                message : data,
                addClass : 'form',
            };
            bootbox.dialog(_options).bind('shown.bs.modal', function(){
            	$(this).find('.modal-header').css({'padding-top': '0px'});
            	$(this).find('.modal-dialog').css({'width': '60%', 'max-width': '100%'});

                $('input').keyup(function(){
                    $(this).val($(this).val().toUpperCase());
                });

                $('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal]').each(function(){
                    $(this).priceFormat(Config[$(this).data('tipe')]);
                });
            });
        },'html');
	}, // end - view_form

	edit_form: function (elm) {
		bootbox.hideAll();

		$.get('accounting/ChartOfAccount/edit_form',{
			'id': $(elm).data('id')
        },function(data){
            var _options = {
                className : 'large',
                message : data,
                addClass : 'form',
            };
            bootbox.dialog(_options).bind('shown.bs.modal', function(){
            	$(this).find('.modal-header').css({'padding-top': '0px'});
            	$(this).find('.modal-dialog').css({'width': '60%', 'max-width': '100%'});

                $('input').keyup(function(){
                    $(this).val($(this).val().toUpperCase());
                });

                $('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal]').each(function(){
                    $(this).priceFormat(Config[$(this).data('tipe')]);
                });
            });
        },'html');
	}, // end - edit_form

	batal: function(elm) {
		bootbox.hideAll();
		coa.view_form(elm);
	}, // end - batal

	save: function (elm) {
		var modal = $(elm).closest('div.modal-body');

		var err = 0;
		$.map( $(modal).find('[data-required=1]'), function(ipt) {
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
			bootbox.confirm('Apakah anda yakin ingin menyimpan data COA ?', function(result) {
				if ( result ) {
					var params = {
						'perusahaan': $(modal).find('#perusahaan').val(),
						'unit': $(modal).find('.unit').val(),
						'nama': $(modal).find('.nama').val(),
						'coa': $(modal).find('.coa').val(),
						'laporan': $(modal).find('.laporan').val(),
						'posisi': $(modal).find('.posisi').val(),
					};

					$.ajax({
			            url : 'accounting/ChartOfAccount/save',
			            data : {'params' : params},
			            type : 'POST',
			            dataType : 'JSON',
			            beforeSend : function(){ showLoading(); },
			            success : function(data){
			                hideLoading();
			                if (data.status) {
			                    bootbox.alert(data.message, function(){
			                        coa.get_list();
			                        bootbox.hideAll();
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
		var modal = $(elm).closest('div.modal-body');

		var err = 0;
		$.map( $(modal).find('[data-required=1]'), function(ipt) {
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
			bootbox.confirm('Apakah anda yakin ingin meng-update data COA ?', function(result) {
				if ( result ) {
					var params = {
						'id': $(elm).data('id'),
						'perusahaan': $(modal).find('#perusahaan').val(),
						'unit': $(modal).find('.unit').val(),
						'nama': $(modal).find('.nama').val(),
						'coa': $(modal).find('.coa').val(),
						'laporan': $(modal).find('.laporan').val(),
						'posisi': $(modal).find('.posisi').val(),
					};

					$.ajax({
			            url : 'accounting/ChartOfAccount/edit',
			            data : {'params' : params},
			            type : 'POST',
			            dataType : 'JSON',
			            beforeSend : function(){ showLoading(); },
			            success : function(data){
			                hideLoading();
			                if (data.status) {
			                    bootbox.alert(data.message, function(){
			                        coa.get_list();
			                        bootbox.hideAll();
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
		bootbox.confirm('Apakah anda yakin ingin meng-hapus data COA ?', function(result) {
			if ( result ) {
				var params = {
					'id': $(elm).data('id')
				};

				$.ajax({
		            url : 'accounting/ChartOfAccount/delete',
		            data : {'params' : params},
		            type : 'POST',
		            dataType : 'JSON',
		            beforeSend : function(){ showLoading(); },
		            success : function(data){
		                hideLoading();
		                if (data.status) {
		                    bootbox.alert(data.message, function(){
		                        coa.get_list();
		                        bootbox.hideAll();
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

coa.start_up();