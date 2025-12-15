var dn = {
	startUp: function() {
        dn.settingUp();
	}, // end - startUp

    settingUp: function() {
        $('.date').datetimepicker({
            locale: 'id',
            format: 'DD MMM Y',
            useCurrent: true, //Important! See issue #1075
        });

        $.map( $('.date'), function(div) {
            var tgl = $(div).find('input').attr('data-tgl');

            if ( !empty(tgl) ) {
                $(div).data('DateTimePicker').date(new Date(tgl));
            }
        });

		$('.jenis_dn').select2().select2().on("select2:select", function (e) {
            dn.getTujuan();
        });
		$('#riwayat').find('.supplier').select2();

        $('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal], [data-tipe=decimal3],[data-tipe=decimal4], [data-tipe=number]').each(function(){
            $(this).priceFormat(Config[$(this).data('tipe')]);
        });

        dn.getTujuan();

        App.setTutupBulan();
    }, // end - settingUp

    getTujuan: function() {
        var action = $('#action');
        var jenis_tujuan = $('.jenis_dn').find('option:selected').attr('data-jenis');

        $(action).find('.supplier option').attr('disabled', 'disabled');
        if ( !empty(jenis_tujuan) ) {
            $(action).find('.supplier option[data-jenis="'+jenis_tujuan+'"]').removeAttr('disabled');
        }

        $(action).find('.supplier').select2();
    }, // end - getTujuan

    getLists: function() {
        var div = $('#riwayat');
		var dcontent = $(div).find('.tbl_riwayat tbody');

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
                'start_date': dateSQL( $(div).find('#StartDate').data('DateTimePicker').date() ),
                'end_date': dateSQL( $(div).find('#EndDate').data('DateTimePicker').date() ),
                'supplier': $(div).find('.supplier').select2().val(),
                'jenis': $(div).find('.supplier option:selected').attr('data-jenis')
            };

            $.ajax({
                url: 'transaksi/DebitNote/getLists',
                data: { 'params': params },
                type: 'GET',
                dataType: 'HTML',
                beforeSend: function(){ App.showLoaderInContent( $(dcontent) ) },
                success: function(html){
					App.hideLoaderInContent( $(dcontent), html );
                }
            });
        }
    }, // end - getLists

	changeTabActive: function(elm) {
		var id = $(elm).data('kode');
		var edit = $(elm).data('edit');
		var href = $(elm).data('href');

		$('a.nav-link').removeClass('active');
		$('div.tab-pane').removeClass('active');
		$('div.tab-pane').removeClass('show');

		$('a[data-tab='+href+']').addClass('active');
		$('div.tab-content').find('div#'+href).addClass('show');
		$('div.tab-content').find('div#'+href).addClass('active');

        console.log(id);
        console.log(edit);
        console.log(href);

		dn.loadForm(id, edit, href);
	}, // end - changeTabActive

	loadForm: function(id, edit, href) {
		var params = {
			'id': id,
			'edit': edit
		};

		$.ajax({
            url: 'transaksi/DebitNote/loadForm',
            data: { 'params': params },
            type: 'GET',
            dataType: 'HTML',
            beforeSend: function(){ showLoading() },
            success: function(html){
                $('div#'+href).html( html );

                dn.settingUp();

                hideLoading();
            }
        });
	}, // end - loadForm

	save: function() {
		var div = $('#action');

		var err = 0;
		$.map( $(div).find('[data-required="1"]'), function(ipt) {
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
			bootbox.confirm('Apakah anda yakin ingin menyimpan data ?', function (result) {
				if ( result ) {
					var params = {
						'jenis_dn': $(div).find('.jenis_dn').select2('val'),
						'tgl_dn': dateSQL( $(div).find('#Tanggal').data('DateTimePicker').date() ),
						'nilai_dn': numeral.unformat($(div).find('.nilai_dn').val()),
						'no_dok': $(div).find('.no_dok').val(),
						'supplier': $(div).find('.supplier').select2('val'),
						'ket_dn': $(div).find('.ket_dn').val()
					};
                    
                    var formData = new FormData();
        
                    var _file = $('.file_lampiran').get(0).files[0];
                    formData.append('files', _file);
                    formData.append('data', JSON.stringify(params));
        
                    $.ajax({
                        url : 'transaksi/DebitNote/save',
                        type : 'post',
                        data : formData,
                        beforeSend : function(){ showLoading() },
                        success : function(data){
                            hideLoading();
                            if ( data.status == 1 ) {
                                bootbox.alert(data.message, function() {
                                    dn.loadForm(data.content.id, null, 'action');
									dn.getLists();
                                });
                            } else {
                                bootbox.alert(data.message);
                            }
                        },
                        contentType : false,
                        processData : false,
                    });
				}
			});
		}
	}, // end - save

    edit: function(elm) {
		var div = $('#action');

		var err = 0;
		$.map( $(div).find('[data-required="1"]'), function(ipt) {
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
			bootbox.confirm('Apakah anda yakin ingin meng-edit data ?', function (result) {
				if ( result ) {
					var params = {
						'id': $(elm).attr('data-kode'),
						'jenis_dn': $(div).find('.jenis_dn').select2('val'),
						'tgl_dn': dateSQL( $(div).find('#Tanggal').data('DateTimePicker').date() ),
						'nilai_dn': numeral.unformat($(div).find('.nilai_dn').val()),
						'no_dok': $(div).find('.no_dok').val(),
						'supplier': $(div).find('.supplier').select2('val'),
						'ket_dn': $(div).find('.ket_dn').val()
					};
                    
                    var formData = new FormData();
        
                    var _file = $('.file_lampiran').get(0).files[0];
                    formData.append('files', _file);
                    formData.append('data', JSON.stringify(params));
        
                    $.ajax({
                        url : 'transaksi/DebitNote/edit',
                        type : 'post',
                        data : formData,
                        beforeSend : function(){ showLoading() },
                        success : function(data){
                            hideLoading();
                            if ( data.status == 1 ) {
                                bootbox.alert(data.message, function() {
                                    dn.loadForm(data.content.id, null, 'action');
									dn.getLists();
                                });
                            } else {
                                bootbox.alert(data.message);
                            }
                        },
                        contentType : false,
                        processData : false,
                    });
				}
			});
		}
	}, // end - edit

    delete: function(elm) {
		var div = $('#action');

        bootbox.confirm('Apakah anda yakin ingin meng-hapus data ?', function (result) {
            if ( result ) {
                var params = {
                    'id': $(elm).attr('data-kode')
                };
                
                $.ajax({
                    url : 'transaksi/DebitNote/delete',
                    data: { 'params': params },
					type: 'POST',
					dataType: 'JSON',
                    beforeSend : function(){ showLoading() },
                    success : function(data){
                        hideLoading();
                        if ( data.status == 1 ) {
                            bootbox.alert(data.message, function() {
                                dn.loadForm(data.content.id, null, 'action');
                                dn.getLists();
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

dn.startUp();