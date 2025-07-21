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

	cekNoCoa: function (elm) {
		var no_coa = $(elm).val().replaceAll('.', '');

		var gol1 = no_coa.substring(0, 1);
		var gol2 = no_coa.substring(1, 2);
		var gol3 = no_coa.substring(2, 4);
		var gol4 = no_coa.substring(4, 6);
		var gol5 = no_coa.substring(6, 8);

		var params = {
			'gol1': gol1,
			'gol2': gol2,
			'gol3': gol3,
			'gol4': gol4,
			'gol5': gol5
		};

		$.ajax({
			url: 'accounting/ChartOfAccount/cekNoCoa',
			data: {
				'params': params
			},
			type: 'POST',
			dataType: 'JSON',
			beforeSend: function() { showLoading(); },
			success: function(data) {
				hideLoading();
				if ( data.status == 1 ) {
					$('input.gol1').attr('disabled', 'disabled');
					$('input.gol2').attr('disabled', 'disabled');
					$('input.gol3').attr('disabled', 'disabled');
					$('input.gol4').attr('disabled', 'disabled');
					$('input.gol5').attr('disabled', 'disabled');

					if ( !empty(data.content.gol1) ) {
						$('input.gol1').val( data.content.gol1 );
					} else {
						if ( gol1 != '00' ) {
							$('input.gol1').removeAttr('disabled');
						} else {
							$('input.gol1').val('');
						}
					}

					if ( !empty(data.content.gol2) ) {
						$('input.gol2').val( data.content.gol2 );
					} else {
						if ( gol2 != '00' ) {
							$('input.gol2').removeAttr('disabled');
						} else {
							$('input.gol2').val('');
						}
					}

					if ( !empty(data.content.gol3) ) {
						$('input.gol3').val( data.content.gol3 );
					} else {
						if ( gol3 != '00' ) {
							$('input.gol3').removeAttr('disabled');
						} else {
							$('input.gol3').val('');
						}
					}

					if ( !empty(data.content.gol4) ) {
						$('input.gol4').val( data.content.gol4 );
					} else {
						if ( gol4 != '00' ) {
							$('input.gol4').removeAttr('disabled');
						} else {
							$('input.gol4').val('');
						}
					}

					if ( !empty(data.content.gol5) ) {
						$('input.gol5').val( data.content.gol5 );
					} else {
						if ( gol5 != '00' ) {
							$('input.gol5').removeAttr('disabled');
						} else {
							$('input.gol5').val('');
						}
					}

					coa.cekNamaCoa();
				} else {
					bootbox.alert(data.message);
				}
			}
		});
	}, // end - cekNoCoa

	cekNamaCoa: function () {
		var val = $('input.gol:not(:disabled):last').val();

		$('input.nama').val( val );
	}, // end - cekNamaCoa

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

				$(this).find('input.coa').mask("9999.99.99");
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
						'coa': $(modal).find('.coa').val(),
						'nama': $(modal).find('.nama').val(),
						'gol1': $(modal).find('.gol1').val(),
						'gol2': $(modal).find('.gol2').val(),
						'gol3': $(modal).find('.gol3').val(),
						'gol4': $(modal).find('.gol4').val(),
						'gol5': $(modal).find('.gol5').val(),
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
						'coa': $(modal).find('.coa').val(),
						'nama': $(modal).find('.nama').val(),
						'gol1': $(modal).find('.gol1').val(),
						'gol2': $(modal).find('.gol2').val(),
						'gol3': $(modal).find('.gol3').val(),
						'gol4': $(modal).find('.gol4').val(),
						'gol5': $(modal).find('.gol5').val(),
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