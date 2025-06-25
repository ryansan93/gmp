var mj = {
	start_up: function () {
		mj.setting_up();
	}, // end - start_up

	setting_up: function() {
		$('.det_jurnal_trans').select2();
		$('.jurnal_report').select2();

        $('#StartDate, #EndDate').datetimepicker({
            locale: 'id',
            format: 'DD MMM Y'
        });
	}, // end - setting_up

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

        mj.load_form($(elm), edit, href);
    }, // end - changeTabActive

    load_form: function(elm, edit = null, href = null) {
        var dcontent = $('div#'+href);

        var params = {
            'id': $(elm).data('id')
        };

        $.ajax({
            url : 'accounting/MappingJurnal/load_form',
            data : {
                'params' :  params,
                'edit' :  edit
            },
            type : 'GET',
            dataType : 'HTML',
            beforeSend : function(){ App.showLoaderInContent(dcontent); },
            success : function(html){
                App.hideLoaderInContent(dcontent, html);

                mj.setting_up();
            },
        });
    }, // end - load_form

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
                'jurnal_report_id': $(div).find('.jurnal_report').val()
            };

            $.ajax({
                url : 'accounting/MappingJurnal/getLists',
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
					var data = {
						'det_jurnal_trans_id': $(dcontent).find('.det_jurnal_trans').val(),
						'jurnal_report_id': $(dcontent).find('.jurnal_report').val(),
						'posisi': $(dcontent).find('.posisi').val()
					};

					$.ajax({
			            url : 'accounting/MappingJurnal/save',
			            data : {'params' : data},
			            type : 'POST',
			            dataType : 'JSON',
			            beforeSend : function(){ showLoading(); },
			            success : function(data){
			                hideLoading();
			                if (data.status) {
			                    bootbox.alert(data.message, function(){
			                        var btn = '<button type="button" class="btn" data-id="'+data.content.id+'"></button>';
			                       	mj.load_form($(btn), null, 'action');

			                       	var div = $('div#riwayat');
			                       	var jurnal_report_id = $(div).find('.jurnal_report').val();
			                       	if ( !empty(jurnal_report_id) ) {
			                       		$('#btn-tampil').click();
			                       	}
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
			bootbox.confirm('Apakah anda yakin ingin meng-ubah data ?', function(result) {
				if ( result ) {
					var data = {
						'id': $(elm).attr('data-id'),
						'det_jurnal_trans_id': $(dcontent).find('.det_jurnal_trans').val(),
						'jurnal_report_id': $(dcontent).find('.jurnal_report').val(),
						'posisi': $(dcontent).find('.posisi').val()
					};

					$.ajax({
			            url : 'accounting/MappingJurnal/edit',
			            data : {'params' : data},
			            type : 'POST',
			            dataType : 'JSON',
			            beforeSend : function(){ showLoading(); },
			            success : function(data){
			                hideLoading();
			                if (data.status) {
			                    bootbox.alert(data.message, function(){
			                        var btn = '<button type="button" class="btn" data-id="'+data.content.id+'"></button>';
			                       	mj.load_form($(btn), null, 'action');

			                       	$('#btn-tampil').click();
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
		            url : 'accounting/MappingJurnal/delete',
		            data : {'params' : params},
		            type : 'POST',
		            dataType : 'JSON',
		            beforeSend : function(){ showLoading(); },
		            success : function(data){
		                hideLoading();
		                if (data.status) {
		                    bootbox.alert(data.message, function(){
		                    	var btn = '<button type="button" class="btn" data-id=""></button>';
			                    mj.load_form($(btn), null, 'action');

		                        $('#btn-tampil').click();
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

mj.start_up();