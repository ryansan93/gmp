var pp = {
	start_up: function () {
		pp.get_list();
	}, // end - start_up

	get_list : function () {
		var dContent = $('tbody');

		$.ajax({
            url : 'parameter/PotonganPajak/get_list',
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
		$.ajax({
            url : 'parameter/PotonganPajak/add_form',
            data : {},
            type : 'GET',
            dataType : 'HTML',
            beforeSend : function(){},
            success : function(html){
            	var tr_data = $('table.tbl_potongan tbody').find('tr.data');
            	if ( $(tr_data).length > 0 ) {
            		$('table.tbl_potongan tbody').prepend( html );
            	} else {
            		$('table.tbl_potongan tbody').html( html );
            	}

            	$('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal], [data-tipe=decimal3],[data-tipe=decimal4], [data-tipe=number]').each(function(){
				    $(this).priceFormat(Config[$(this).data('tipe')]);
				});
            }
        });
	}, // end - add_form

	save : function (elm) {
		var tr = $(elm).closest('tr');
		var err = 0;

		$.map( $(tr).find('input'), function (ipt) {
			if ( empty($(ipt).val()) ) {
				$(ipt).parent().addClass('has-error');
				err++;
			} else {
				$(ipt).parent().removeClass('has-error');
			}
		});

		if ( err > 0 ) {
			bootbox.alert('Harap lengkapi data yang anda input.');
		} else {
			bootbox.confirm('Apakah anda yakin ingin menyimpan data Potongan Pajak ?', function (result) {
				if ( result ) {
					var potongan = numeral.unformat( $(tr).find('input').val() );

					pp.execute_save(potongan);
				};
			});
		};
	}, // end - save

	batal_save : function (elm) {
		$(elm).closest('tr').remove();
	}, // end - batal_save

	execute_save : function (params = null) {
		$.ajax({
            url : 'parameter/PotonganPajak/save',
            data : {'params' : params},
            type : 'POST',
            dataType : 'JSON',
            beforeSend : function(){ showLoading(); },
            success : function(data){
                hideLoading();
                if (data.status) {
                    bootbox.alert(data.message, function(){
                        pp.get_list();
                    });
                } else {
                    alertDialog(data.message);
                }
            }
        });
	}, // end - execute_save

	edit_form : function (elm) {
		var tr = $(elm).closest('tr');
		$(tr).find('td.prs_potongan span').addClass('hide');
		$(tr).find('td.prs_potongan input').removeClass('hide');

		$(tr).find('div.edit').addClass('hide');
		$(tr).find('div.save_edit').removeClass('hide');

		$('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal], [data-tipe=decimal3],[data-tipe=decimal4], [data-tipe=number]').each(function(){
		    $(this).priceFormat(Config[$(this).data('tipe')]);
		});
	}, // end - edit_form

	batal_edit : function (elm) {
		var tr = $(elm).closest('tr');
		$(tr).find('td.prs_potongan span').removeClass('hide');
		$(tr).find('td.prs_potongan input').addClass('hide');

		$(tr).find('div.edit').removeClass('hide');
		$(tr).find('div.save_edit').addClass('hide');
	}, // end - batal_edit

	edit : function (elm) {
		var tr = $(elm).closest('tr');
		var err = 0;

		$.map( $(tr).find('input'), function (ipt) {
			if ( empty($(ipt).val()) ) {
				$(ipt).parent().addClass('has-error');
				err++;
			} else {
				$(ipt).parent().removeClass('has-error');
			}
		});

		if ( err > 0 ) {
			bootbox.alert('Harap lengkapi data yang anda input.');
		} else {
			bootbox.confirm('Apakah anda yakin ingin mengupdate data Potongan Pajak ?', function (result) {
				if ( result ) {
					var potongan = numeral.unformat( $(tr).find('input').val() );

					var params = {
						'id' : $(tr).data('id'),
						'prs_potongan' : potongan
					};

					pp.execute_edit(params);
				};
			});
		};
	}, // end - edit

	execute_edit : function (params) {
		$.ajax({
            url : 'parameter/PotonganPajak/edit',
            data : {'params' : params},
            type : 'POST',
            dataType : 'JSON',
            beforeSend : function(){ showLoading(); },
            success : function(data){
                hideLoading();
                if (data.status) {
                    bootbox.alert(data.message, function(){
                        pp.get_list();
                    });
                } else {
                    alertDialog(data.message);
                }
            }
        });
	}, // end - execute_edit

	delete : function (elm) {
		var tr = $(elm).closest('tr');

		bootbox.confirm('Apakah anda yakin ingin meng-hapus data Potongan Pajak ?', function (result) {
			if ( result ) {
				var id = $(tr).data('id');
				$.ajax({
		            url : 'parameter/PotonganPajak/delete',
		            data : {'id' : id},
		            type : 'POST',
		            dataType : 'JSON',
		            beforeSend : function(){ showLoading(); },
		            success : function(data){
		                hideLoading();
		                if (data.status) {
		                    bootbox.alert(data.message, function(){
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
