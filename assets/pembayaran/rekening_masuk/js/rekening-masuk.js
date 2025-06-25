var rm = {
    startUp: function() {
        $('.datetimepicker').datetimepicker({
            locale: 'id',
            format: 'DD MMM Y'
        });
    }, // end - startUp

    setBindSHA1 : function(){
        $('input:file').off('change.sha1');
        $('input:file').on('change.sha1',function(){
            var elm = $(this);
            var file = elm.get(0).files[0];
            elm.attr('data-sha1', '');
            sha1_file(file).then(function (sha1) {
                elm.attr('data-sha1', sha1);
            });
        });
    }, // end - setBindSHA1

    showNameFile : function(elm, isLable = 1) {
        var _label = $(elm).closest('label');
        var _a = _label.prev('a[name=dokumen]');
        _a.removeClass('hide');
        // var _allowtypes = $(elm).data('allowtypes').split('|');
        var _dataName = $(elm).data('name');
        var _allowtypes = ['xlsx'];
        var _type = $(elm).get(0).files[0]['name'].split('.').pop();
        var _namafile = $(elm).val();
        var _temp_url = URL.createObjectURL($(elm).get(0).files[0]);
        _namafile = _namafile.substring(_namafile.lastIndexOf("\\") + 1, _namafile.length);

        if (in_array(_type, _allowtypes)) {
            if (isLable == 1) {
                if (_a.length) {
                    _a.attr('title', _namafile);
                    _a.attr('href', _temp_url);
                    if ( _dataName == 'name' ) {
                        $(_a).text( _namafile );  
                    }
                }
            } else if (isLable == 0) {
                $(elm).closest('label').attr('title', _namafile);
            }
            $(elm).attr('data-filename', _namafile);
        } else {
            $(elm).val('');
            $(elm).closest('label').attr('title', '');
            $(elm).attr('data-filename', '');
            _a.addClass('hide');
            bootbox.alert('Format file tidak sesuai. Mohon attach ulang.');
        }
    }, // end - showNameFile

    getLists: function() {
        // var div = $('div#riwayat');
        let dcontent = $('table.tbl_riwayat tbody');

        var err = 0;
        var err = 0;
        $.map( $('[data-required=1]'), function(ipt) {
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
                'start_date': dateSQL($('#StartDate').data('DateTimePicker').date()),
                'end_date': dateSQL($('#EndDate').data('DateTimePicker').date())
            };

            $.ajax({
                url : 'pembayaran/RekeningMasuk/getLists',
                data : { 'params': params },
                type : 'get',
                dataType : 'html',
                beforeSend : function(){ showLoading() },
                success : function(html){
                    $(dcontent).html( html );

                    // $.map( $(dcontent).find('tr.data td'), function(td) {
                    //     $(td).click(function() {
                    //         console.log( $(this) );
                    //         // window.open('pembayaran/Bakul/index', '_blank');
                    //     });
                    // });

                    hideLoading();
                },
            });
        }
    }, // end - getLists

    addForm: function() {
        $.get('pembayaran/RekeningMasuk/addForm',{
        },function(data){
            var _options = {
                className : 'veryWidth',
                message : data,
                size : 'large',
            };
            bootbox.dialog(_options).bind('shown.bs.modal', function(){
                var modal_dialog = $(this).find('.modal-dialog');
                var modal_body = $(this).find('.modal-body');

                $(modal_dialog).css({'max-width' : '40%'});
                $(modal_dialog).css({'width' : '40%'});

                var modal_header = $(this).find('.modal-header');
                $(modal_header).css({'padding-top' : '0px'});

                $(modal_body).find('#tanggal').datetimepicker({
                    locale: 'id',
                    format: 'DD MMM Y'
                });

                $(modal_body).find('select.perusahaan').select2();
                $(modal_body).find('select.pelanggan').select2();

                $(modal_body).find('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal], [data-tipe=decimal3],[data-tipe=decimal4], [data-tipe=number]').each(function(){
                    $(this).priceFormat(Config[$(this).data('tipe')]);
                });
                
                $('.modal').removeAttr('tabindex');
            });
        },'html');
    }, // end - addForm

    viewForm: function(elm) {
        $('.modal').modal('hide');
        var kode = $(elm).attr('data-kode');

        var params = {
            'kode': kode
        };

        $.get('pembayaran/RekeningMasuk/viewForm',{
            'params': params
        },function(data){
            var _options = {
                className : 'veryWidth',
                message : data,
                size : 'large',
            };
            bootbox.dialog(_options).bind('shown.bs.modal', function(){
                var modal_dialog = $(this).find('.modal-dialog');
                var modal_body = $(this).find('.modal-body');

                $(modal_dialog).css({'max-width' : '40%'});
                $(modal_dialog).css({'width' : '40%'});

                var modal_header = $(this).find('.modal-header');
                $(modal_header).css({'padding-top' : '0px'});

                $(modal_body).find('#tanggal').datetimepicker({
                    locale: 'id',
                    format: 'DD MMM Y'
                });

                $(modal_body).find('select.perusahaan').select2();
                $(modal_body).find('select.pelanggan').select2();

                $(modal_body).find('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal], [data-tipe=decimal3],[data-tipe=decimal4], [data-tipe=number]').each(function(){
                    $(this).priceFormat(Config[$(this).data('tipe')]);
                });

                $('.modal').removeAttr('tabindex');
            });
        },'html');
    }, // end - viewForm

    editForm: function(elm) {
        $('.modal').modal('hide');
        var kode = $(elm).attr('data-kode');

        var params = {
            'kode': kode
        };

        $.get('pembayaran/RekeningMasuk/editForm',{
            'params': params
        },function(data){
            var _options = {
                className : 'veryWidth',
                message : data,
                size : 'large',
            };
            bootbox.dialog(_options).bind('shown.bs.modal', function(){
                var modal_dialog = $(this).find('.modal-dialog');
                var modal_body = $(this).find('.modal-body');

                $(modal_dialog).css({'max-width' : '40%'});
                $(modal_dialog).css({'width' : '40%'});

                var modal_header = $(this).find('.modal-header');
                $(modal_header).css({'padding-top' : '0px'});

                $(modal_body).find('#tanggal').datetimepicker({
                    locale: 'id',
                    format: 'DD MMM Y'
                });

                var tanggal = $(modal_body).find('#tanggal').attr('data-tgl');
                $(modal_body).find('#tanggal').data("DateTimePicker").date(new Date(tanggal));

                $(modal_body).find('select.perusahaan').select2();
                $(modal_body).find('select.pelanggan').select2();

                $(modal_body).find('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal], [data-tipe=decimal3],[data-tipe=decimal4], [data-tipe=number]').each(function(){
                    $(this).priceFormat(Config[$(this).data('tipe')]);
                });

                $('.modal').removeAttr('tabindex');
            });
        },'html');
    }, // end - editForm

    save: function() {
        var modal_body = $('.modal-body');
        var div = $('div#transaksi');

        var err = 0;
        $.map( $(modal_body).find('[data-required=1]'), function(ipt) {
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
            bootbox.confirm('Apakah anda yakin ingin menyimpan data uang masuk bakul ?', function(result) {
                if ( result ) {
                    var params = {
                        'tanggal': dateSQL($(modal_body).find('#tanggal').data('DateTimePicker').date()),
                        'pelanggan': $(modal_body).find('select.pelanggan').select2().val(),
                        'perusahaan': $(modal_body).find('select.perusahaan').select2().val(),
                        'jml_transfer': numeral.unformat($(modal_body).find('input.jml_transfer').val()),
                        'ket': $(modal_body).find('textarea.ket').val()
                    };

                    $.ajax({
                        url : 'pembayaran/RekeningMasuk/save',
                        data : { 'params': params },
                        type : 'POST',
                        dataType : 'JSON',
                        beforeSend : function(){ showLoading() },
                        success : function(data){
                            hideLoading();
                            if ( data.status == 1 ) {
                                bootbox.alert(data.message, function() {
                                    if ( !empty($('#StartDate').find('input').val()) && !empty($('#EndDate').find('input').val()) ) {
                                        rm.getLists();
                                    }

                                    bootbox.hideAll();
                                });
                            } else {
                                bootbox.alert(data.message);
                            }
                        },
                    });
                }
            });
        }
    }, // end - save

    edit: function(elm) {
        var modal_body = $('.modal-body');
        var div = $('div#transaksi');

        var err = 0;
        $.map( $(modal_body).find('[data-required=1]'), function(ipt) {
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
            bootbox.confirm('Apakah anda yakin ingin meng-ubah data uang masuk bakul ?', function(result) {
                if ( result ) {
                    var params = {
                        'kode': $(elm).attr('data-kode'),
                        'tanggal': dateSQL($(modal_body).find('#tanggal').data('DateTimePicker').date()),
                        'pelanggan': $(modal_body).find('select.pelanggan').select2().val(),
                        'perusahaan': $(modal_body).find('select.perusahaan').select2().val(),
                        'jml_transfer': numeral.unformat($(modal_body).find('input.jml_transfer').val()),
                        'ket': $(modal_body).find('textarea.ket').val()
                    };

                    $.ajax({
                        url : 'pembayaran/RekeningMasuk/edit',
                        data : { 'params': params },
                        type : 'POST',
                        dataType : 'JSON',
                        beforeSend : function(){ showLoading() },
                        success : function(data){
                            hideLoading();
                            if ( data.status == 1 ) {
                                bootbox.alert(data.message, function() {
                                    if ( !empty($('#StartDate').find('input').val()) && !empty($('#EndDate').find('input').val()) ) {
                                        rm.getLists();
                                    }

                                    bootbox.hideAll();
                                });
                            } else {
                                bootbox.alert(data.message);
                            }
                        },
                    });
                }
            });
        }
    }, // end - edit

    delete: function(elm) {
        var modal_body = $('.modal-body');
        var div = $('div#transaksi');

        bootbox.confirm('Apakah anda yakin ingin meng-hapus data uang masuk bakul ?', function(result) {
            if ( result ) {
                var params = {
                    'kode': $(elm).attr('data-kode')
                };

                $.ajax({
                    url : 'pembayaran/RekeningMasuk/delete',
                    data : { 'params': params },
                    type : 'POST',
                    dataType : 'JSON',
                    beforeSend : function(){ showLoading() },
                    success : function(data){
                        hideLoading();
                        if ( data.status == 1 ) {
                            bootbox.alert(data.message, function() {
                                if ( !empty($('#StartDate').find('input').val()) && !empty($('#EndDate').find('input').val()) ) {
                                    rm.getLists();
                                }

                                bootbox.hideAll();
                            });
                        } else {
                            bootbox.alert(data.message);
                        }
                    },
                });
            }
        });
    }, // end - delete

    importForm: function() {
        $.get('pembayaran/RekeningMasuk/importForm',{
        },function(data){
            var _options = {
                className : 'veryWidth',
                message : data,
                size : 'large',
            };
            bootbox.dialog(_options).bind('shown.bs.modal', function(){
                var modal_dialog = $(this).find('.modal-dialog');
                var modal_body = $(this).find('.modal-body');

                $(modal_dialog).css({'max-width' : '40%'});
                $(modal_dialog).css({'width' : '40%'});

                var modal_header = $(this).find('.modal-header');
                $(modal_header).css({'padding-top' : '0px'});

                $(modal_body).find('select.perusahaan').select2();

                rm.setBindSHA1();
                
                $('.modal').removeAttr('tabindex');
            });
        },'html');
    }, // end - importForm

    import: function() {
		var file_tmp = $('.file_lampiran').get(0).files[0];

		if ( !empty($('.file_lampiran').val()) ) {
            $('.modal').modal('hide');

            var params = {
                'perusahaan': $('select.perusahaan').select2().val()
            };
            
			var formData = new FormData();
	        formData.append('params', JSON.stringify(params));
	        formData.append('file', file_tmp);
            
            showLoading('Proses import data rekening masuk . . .');
			$.ajax({
                url: 'pembayaran/RekeningMasuk/import',
				dataType: 'json',
	            type: 'post',
	            async:false,
	            processData: false,
	            contentType: false,
	            data: formData,
				beforeSend: function() {
				},
				success: function(data) {
					hideLoading();
					if ( data.status == 1 ) {
						bootbox.alert(data.message, function() {
							// location.reload();
                            $('.modal').modal('hide');
						});
					} else {
						bootbox.alert(data.message);
					};
				},
		    });
		} else {
			bootbox.alert('Harap isi lampiran terlebih dahulu.');
		}
	}, // end - import
};

rm.startUp();