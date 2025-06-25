var gaji = {
	startUp: function () {
		gaji.settingUp();
	}, // end - startUp

	settingUp: function () {
		$('#riwayat .pegawai').select2();
		$('#action .pegawai').select2().on("select2:select", function (e) {
            gaji.setUnit();
        });

        $('#tglBerlaku').datetimepicker({
            locale: 'id',
            format: 'DD MMM Y'
        });

        var tgl = $('#tglBerlaku').find('input').data('val');
        if ( !empty(tgl) ) {
        	$('#tglBerlaku').data('DateTimePicker').minDate( moment(new Date(tgl)) );
        } else {
        	$('#tglBerlaku').data('DateTimePicker').minDate( moment(new Date()) );
        }

        $('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal], [data-tipe=decimal3],[data-tipe=decimal4], [data-tipe=number]').each(function(){
			$(this).priceFormat(Config[$(this).data('tipe')]);
		});
	}, // end - settingUp

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

        gaji.loadForm($(elm), edit, href);
    }, // end - changeTabActive

    loadForm: function(elm, edit = null, href = null) {
        var dcontent = $('div#'+href);

        var params = {
            'id': $(elm).data('id')
        };

        $.ajax({
            url : 'parameter/Gaji/loadForm',
            data : {
                'params' :  params,
                'edit' :  edit
            },
            type : 'GET',
            dataType : 'HTML',
            beforeSend : function(){ App.showLoaderInContent(dcontent); },
            success : function(html){
                App.hideLoaderInContent(dcontent, html);

                gaji.settingUp();
            },
        });
    }, // end - loadForm

    addRow: function(elm) {
    	var tr = $(elm).closest('tr');
    	var tbody = $(tr).closest('tbody');

    	var tr_clone = $(tr).clone();
    	$(tr_clone).find('input').val('');

    	$(tbody).append( $(tr_clone) );
    }, // end - addRow

    removeRow: function(elm) {
    	var tr = $(elm).closest('tr');
    	var tbody = $(tr).closest('tbody');

    	if ( $(tbody).find('tr').length > 1 ) {
    		$(tr).remove();
    	}
    }, // end - removeRow

	getLists: function() {
		var dcontent = $('div#riwayat');

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
			bootbox.alert('Harap pilih pegawai terlebih dahulu.');
		} else {
			var nik = $(dcontent).find('.pegawai').select().val();

			$.ajax({
	            url : 'parameter/Gaji/getLists',
	            data : {'nik' : nik},
	            type : 'GET',
	            dataType : 'HTML',
	            beforeSend : function(){ showLoading(); },
	            success : function(html){
	                hideLoading();

	                $(dcontent).find('table tbody').html(html);
	            }
	        });
		}
	}, // end - getLists

	setUnit: function() {
		var list_nama_unit = $('#action .pegawai').select2().find(':selected').data('namaunit');

		$('#action textarea').text( list_nama_unit );
	}, // end - setUnit

	save: function() {
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
					var insentif = $.map( $(dcontent).find('.tbl_insentif tbody tr'), function(tr) {
						var keterangan = $(tr).find('.nama').val();
						var nominal = numeral.unformat($(tr).find('.nominal').val());
						if ( !empty(keterangan) && nominal > 0 ) {
							var _insentif = {
								'keterangan': $(tr).find('.nama').val(),
								'nominal': numeral.unformat($(tr).find('.nominal').val())
							};

							return _insentif;
						}
					});

					var potongan = $.map( $(dcontent).find('.tbl_potongan tbody tr'), function(tr) {
						var keterangan = $(tr).find('.nama').val();
						var nominal = numeral.unformat($(tr).find('.nominal').val());
						if ( !empty(keterangan) && nominal > 0 ) {
							var _potongan = {
								'keterangan': $(tr).find('.nama').val(),
								'nominal': numeral.unformat($(tr).find('.nominal').val())
							};

							return _potongan;
						}
					});

					var data = {
						'nik': $(dcontent).find('.pegawai').select().val(),
						'jabatan': $(dcontent).find('.pegawai').find(':selected').data('jabatan'),
						'tgl_berlaku': dateSQL($(dcontent).find('#tglBerlaku').data('DateTimePicker').date()),
						'gaji': numeral.unformat($(dcontent).find('.gaji').val()),
						'kode_unit': $(dcontent).find('.pegawai').find(':selected').data('kodeunit'),
						'insentif': insentif,
						'potongan': potongan
					};

					$.ajax({
			            url : 'parameter/Gaji/save',
			            data : {'params' : data},
			            type : 'POST',
			            dataType : 'JSON',
			            beforeSend : function(){ showLoading(); },
			            success : function(data){
			                hideLoading();
			                if (data.status) {
			                    bootbox.alert(data.message, function(){
			                        $('#riwayat .btn-add').click();
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
};

gaji.startUp();