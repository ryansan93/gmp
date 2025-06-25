var rt = {
	startUp: function () {
		rt.settingUp();
	}, // end - startUp

	settingUp: function () {
		$('#start_date_rm').datetimepicker({
            locale: 'id',
            format: 'DD MMM Y'
        });
		$('#end_date_rm').datetimepicker({
            locale: 'id',
            format: 'DD MMM Y'
        });
		$('#tgl_rm').datetimepicker({
            locale: 'id',
            format: 'DD MMM Y'
        });

        $("#start_date_rm").on("dp.change", function (e) {
            $("#end_date_rm").data("DateTimePicker").minDate(e.date);
        });
        $("#end_date_rm").on("dp.change", function (e) {
            $('#start_date_rm').data("DateTimePicker").maxDate(e.date);
        });

        var tgl_rm_val = $('#tgl_rm').find('input').data('val');
        if ( !empty(tgl_rm_val) ) {
        	$("#tgl_rm").data("DateTimePicker").date(new Date(tgl_rm_val));
        }

        $('#start_date_rk').datetimepicker({
            locale: 'id',
            format: 'DD MMM Y'
        });
		$('#end_date_rk').datetimepicker({
            locale: 'id',
            format: 'DD MMM Y'
        });
		$('#tgl_rk').datetimepicker({
            locale: 'id',
            format: 'DD MMM Y'
        });

        $("#start_date_rk").on("dp.change", function (e) {
            $("#end_date_rk").data("DateTimePicker").minDate(e.date);
        });
        $("#end_date_rk").on("dp.change", function (e) {
            $('#start_date_rk').data("DateTimePicker").maxDate(e.date);
        });

        var tgl_rk_val = $('#tgl_rk').find('input').data('val');
        if ( !empty(tgl_rk_val) ) {
        	$("#tgl_rk").data("DateTimePicker").date(new Date(tgl_rk_val));
        }

        $('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal], [data-tipe=decimal3],[data-tipe=decimal4], [data-tipe=number]').each(function(){
			$(this).priceFormat(Config[$(this).data('tipe')]);
		});

	    $('.perusahaan').select2({
	        dropdownParent: $('.modal')
	    });
		$('.pelanggan').select2({
	        dropdownParent: $('.modal')
	    });
	}, // end - settingUp

	getListsRm: function() {
		var div = $('div#rekening_masuk');

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
        	bootbox.alert('Harap lengkapi periode Rekening Masuk terlebih dahulu.');
        } else {
        	var params = {
        		'startDate': dateSQL( $(div).find('#start_date_rm').data('DateTimePicker').date() ),
        		'endDate': dateSQL( $(div).find('#end_date_rm').data('DateTimePicker').date() )
        	};

        	$.ajax({
                url : 'pembayaran/RekeningTampungan/getListsRm',
                type : 'get',
                data : {
                	'params': params
                },
                dataType : 'html',
                beforeSend : function(){ showLoading() },
                success : function(html){
                    hideLoading();

                    $(div).find('table tbody').html(html);
                },
            });
        }
	}, // end - getListsRm

	addFormRm: function() {
        $.get('pembayaran/RekeningTampungan/addFormRm',{
        },function(data){
            var _options = {
                className : 'veryWidth',
                message : data,
                size : 'large',
            };
            bootbox.dialog(_options).bind('shown.bs.modal', function(){
                var modal_dialog = $(this).find('.modal-dialog');
                var modal_body = $(this).find('.modal-body');

                // $(modal_dialog).css({'max-width' : '100%'});
                $(modal_dialog).css({'width' : '80%'});

                var modal_header = $(this).find('.modal-header');
                $(modal_header).css({'padding-top' : '0px'});

                rt.settingUp();
            });
        },'html');
    }, // end - addFormRm

    viewFormRm: function(elm) {
    	$('.modal').modal('hide');
    	var kode = $(elm).data('kode');

        $.get('pembayaran/RekeningTampungan/viewFormRm',{
        	'kode': kode
        },function(data){
            var _options = {
                className : 'veryWidth',
                message : data,
                size : 'large',
            };
            bootbox.dialog(_options).bind('shown.bs.modal', function(){
                var modal_dialog = $(this).find('.modal-dialog');
                var modal_body = $(this).find('.modal-body');

                // $(modal_dialog).css({'max-width' : '100%'});
                $(modal_dialog).css({'width' : '80%'});

                var modal_header = $(this).find('.modal-header');
                $(modal_header).css({'padding-top' : '0px'});
            });
        },'html');
    }, // end - viewFormRm

    editFormRm: function(elm) {
    	$('.modal').modal('hide');
    	var kode = $(elm).data('kode');

        $.get('pembayaran/RekeningTampungan/editFormRm',{
        	'kode': kode
        },function(data){
            var _options = {
                className : 'veryWidth',
                message : data,
                size : 'large',
            };
            bootbox.dialog(_options).bind('shown.bs.modal', function(){
                var modal_dialog = $(this).find('.modal-dialog');
                var modal_body = $(this).find('.modal-body');

                // $(modal_dialog).css({'max-width' : '100%'});
                $(modal_dialog).css({'width' : '80%'});

                var modal_header = $(this).find('.modal-header');
                $(modal_header).css({'padding-top' : '0px'});

                rt.settingUp();
            });
        },'html');
    }, // end - editFormRm

    saveRm: function() {
    	var modal_body = $('.modal-body');

        var err = 0;
        $.map( $(modal_body).find('[data-required=1]'), function(ipt) {
            if ( empty($(ipt).val()) ) {
                if ( $(ipt).hasClass('file_lampiran') ) {
                    var label = $(ipt).closest('label');
                    $(label).find('i').css({'color': '#a94442'});
                } else {
                    $(ipt).parent().addClass('has-error');
                }
                err++;
            } else {
                if ( $(ipt).hasClass('file_lampiran') ) {
                    var label = $(ipt).closest('label');
                    $(label).find('i').css({'color': '#000000'});
                } else {
                    $(ipt).parent().removeClass('has-error');
                }
            }
        });

        if ( err > 0 ) {
            bootbox.alert('Harap lengkapi data terlebih dahulu.');
        } else {
            bootbox.confirm('Apakah anda yakin ingin menyimpan data rekening masuk ?', function(result) {
                if ( result ) { 
                    var data = {
                        'tanggal': dateSQL($(modal_body).find('#tgl_rm').data('DateTimePicker').date()),
                        'perusahaan': $(modal_body).find('.perusahaan').select2().val(),
                        'nominal': numeral.unformat($(modal_body).find('.nominal').val()),
                        'keterangan': $(modal_body).find('.keterangan').val()
                    };

                    var formData = new FormData();

                    var _file = $('.file_lampiran').get(0).files[0];
                    formData.append('files', _file);
                    formData.append('data', JSON.stringify(data));

                    $.ajax({
                        url : 'pembayaran/RekeningTampungan/saveRm',
                        type : 'post',
                        data : formData,
                        beforeSend : function(){ showLoading() },
                        success : function(data){
                            hideLoading();
                            if ( data.status == 1 ) {
                                bootbox.alert(data.message, function() {
                                	var startDate = $('#start_date_rm input').val();
									var endDate = $('#end_date_rm input').val();
									if ( !empty(startDate) && !empty(endDate) ) {
										rt.getListsRm();
									}

                                    bootbox.hideAll();
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
    }, // end - saveRm

    editRm: function(elm) {
    	var modal_body = $('.modal-body');

        var err = 0;
        $.map( $(modal_body).find('[data-required=1]'), function(ipt) {
            if ( empty($(ipt).val()) ) {
                if ( $(ipt).hasClass('file_lampiran') ) {
                    var label = $(ipt).closest('label');
                    $(label).find('i').css({'color': '#a94442'});
                } else {
                    $(ipt).parent().addClass('has-error');
                }
                err++;
            } else {
                if ( $(ipt).hasClass('file_lampiran') ) {
                    var label = $(ipt).closest('label');
                    $(label).find('i').css({'color': '#000000'});
                } else {
                    $(ipt).parent().removeClass('has-error');
                }
            }
        });

        if ( err > 0 ) {
            bootbox.alert('Harap lengkapi data terlebih dahulu.');
        } else {
            bootbox.confirm('Apakah anda yakin ingin meng-ubah data rekening masuk ?', function(result) {
                if ( result ) { 
                    var data = {
                        'kode': $(elm).data('kode'),
                        'tanggal': dateSQL($(modal_body).find('#tgl_rm').data('DateTimePicker').date()),
                        'perusahaan': $(modal_body).find('.perusahaan').select2().val(),
                        'nominal': numeral.unformat($(modal_body).find('.nominal').val()),
                        'keterangan': $(modal_body).find('.keterangan').val()
                    };

                    var formData = new FormData();

                    var _file = $('.file_lampiran').get(0).files[0];
                    formData.append('files', _file);
                    formData.append('data', JSON.stringify(data));

                    $.ajax({
                        url : 'pembayaran/RekeningTampungan/editRm',
                        type : 'post',
                        data : formData,
                        beforeSend : function(){ showLoading() },
                        success : function(data){
                            hideLoading();
                            if ( data.status == 1 ) {
                                bootbox.alert(data.message, function() {
                                	var startDate = $('#start_date_rm input').val();
									var endDate = $('#end_date_rm input').val();
									if ( !empty(startDate) && !empty(endDate) ) {
										rt.getListsRm();
									}

                                    bootbox.hideAll();
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
    }, // end - editRm

    deleteRm: function(elm) {
    	var modal_body = $('.modal-body');

        bootbox.confirm('Apakah anda yakin ingin meng-hapus data rekening masuk ?', function(result) {
            if ( result ) { 
                var kode = $(elm).data('kode');

                $.ajax({
                    url : 'pembayaran/RekeningTampungan/deleteRm',
                    type : 'post',
                    data : {
                    	'kode': kode
                    },
                    dataType: 'json',
                    beforeSend : function(){ showLoading() },
                    success : function(data){
                        hideLoading();
                        if ( data.status == 1 ) {
                            bootbox.alert(data.message, function() {
                            	var startDate = $('#start_date_rm input').val();
								var endDate = $('#end_date_rm input').val();
								if ( !empty(startDate) && !empty(endDate) ) {
									rt.getListsRm();
								}

                                bootbox.hideAll();
                            });
                        } else {
                            bootbox.alert(data.message);
                        }
                    }
                });
            }
        });
    }, // end - deleteRm

    getListsRk: function() {
		var div = $('div#rekening_keluar');

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
        	bootbox.alert('Harap lengkapi periode Rekening Keluar terlebih dahulu.');
        } else {
        	var params = {
        		'startDate': dateSQL( $(div).find('#start_date_rk').data('DateTimePicker').date() ),
        		'endDate': dateSQL( $(div).find('#end_date_rk').data('DateTimePicker').date() )
        	};

        	$.ajax({
                url : 'pembayaran/RekeningTampungan/getListsRk',
                type : 'get',
                data : {
                	'params': params
                },
                dataType : 'html',
                beforeSend : function(){ showLoading() },
                success : function(html){
                    hideLoading();

                    $(div).find('table tbody').html(html);
                },
            });
        }
	}, // end - getListsRm

	addFormRk: function() {
        $.get('pembayaran/RekeningTampungan/addFormRk',{
        },function(data){
            var _options = {
                className : 'veryWidth',
                message : data,
                size : 'large',
            };
            bootbox.dialog(_options).bind('shown.bs.modal', function(){
                var modal_dialog = $(this).find('.modal-dialog');
                var modal_body = $(this).find('.modal-body');

                // $(modal_dialog).css({'max-width' : '100%'});
                $(modal_dialog).css({'width' : '80%'});

                var modal_header = $(this).find('.modal-header');
                $(modal_header).css({'padding-top' : '0px'});

                rt.settingUp();
            });
        },'html');
    }, // end - addFormRm

    viewFormRk: function(elm) {
    	$('.modal').modal('hide');
    	var kode = $(elm).data('kode');

        $.get('pembayaran/RekeningTampungan/viewFormRk',{
        	'kode': kode
        },function(data){
            var _options = {
                className : 'veryWidth',
                message : data,
                size : 'large',
            };
            bootbox.dialog(_options).bind('shown.bs.modal', function(){
                var modal_dialog = $(this).find('.modal-dialog');
                var modal_body = $(this).find('.modal-body');

                // $(modal_dialog).css({'max-width' : '100%'});
                $(modal_dialog).css({'width' : '80%'});

                var modal_header = $(this).find('.modal-header');
                $(modal_header).css({'padding-top' : '0px'});
            });
        },'html');
    }, // end - viewFormRm

    editFormRk: function(elm) {
    	$('.modal').modal('hide');
    	var kode = $(elm).data('kode');

        $.get('pembayaran/RekeningTampungan/editFormRk',{
        	'kode': kode
        },function(data){
            var _options = {
                className : 'veryWidth',
                message : data,
                size : 'large',
            };
            bootbox.dialog(_options).bind('shown.bs.modal', function(){
                var modal_dialog = $(this).find('.modal-dialog');
                var modal_body = $(this).find('.modal-body');

                // $(modal_dialog).css({'max-width' : '100%'});
                $(modal_dialog).css({'width' : '80%'});

                var modal_header = $(this).find('.modal-header');
                $(modal_header).css({'padding-top' : '0px'});

                rt.settingUp();
            });
        },'html');
    }, // end - editFormRm

    saveRk: function() {
    	var modal_body = $('.modal-body');

        var err = 0;
        $.map( $(modal_body).find('[data-required=1]'), function(ipt) {
            if ( empty($(ipt).val()) ) {
                if ( $(ipt).hasClass('file_lampiran') ) {
                    var label = $(ipt).closest('label');
                    $(label).find('i').css({'color': '#a94442'});
                } else {
                    $(ipt).parent().addClass('has-error');
                }
                err++;
            } else {
                if ( $(ipt).hasClass('file_lampiran') ) {
                    var label = $(ipt).closest('label');
                    $(label).find('i').css({'color': '#000000'});
                } else {
                    $(ipt).parent().removeClass('has-error');
                }
            }
        });

        if ( err > 0 ) {
            bootbox.alert('Harap lengkapi data terlebih dahulu.');
        } else {
            bootbox.confirm('Apakah anda yakin ingin menyimpan data rekening keluar ?', function(result) {
                if ( result ) { 
                    var data = {
                        'tanggal': dateSQL($(modal_body).find('#tgl_rk').data('DateTimePicker').date()),
                        'perusahaan': $(modal_body).find('.perusahaan').select2().val(),
                        'pelanggan': $(modal_body).find('.pelanggan').select2().val(),
                        'nominal': numeral.unformat($(modal_body).find('.nominal').val()),
                        'keterangan': $(modal_body).find('.keterangan').val(),
                        'jenis': $(modal_body).find('[name=optradio]:checked').val()
                    };

                    var formData = new FormData();

                    var _file = $('.file_lampiran').get(0).files[0];
                    formData.append('files', _file);
                    formData.append('data', JSON.stringify(data));

                    $.ajax({
                        url : 'pembayaran/RekeningTampungan/saveRk',
                        type : 'post',
                        data : formData,
                        beforeSend : function(){ showLoading() },
                        success : function(data){
                            hideLoading();
                            if ( data.status == 1 ) {
                                bootbox.alert(data.message, function() {
                                	var startDate = $('#start_date_rk input').val();
									var endDate = $('#end_date_rk input').val();
									if ( !empty(startDate) && !empty(endDate) ) {
										rt.getListsRk();
									}

                                    bootbox.hideAll();
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
    }, // end - saveRm

    editRk: function(elm) {
    	var modal_body = $('.modal-body');

        var err = 0;
        $.map( $(modal_body).find('[data-required=1]'), function(ipt) {
            if ( empty($(ipt).val()) ) {
                if ( $(ipt).hasClass('file_lampiran') ) {
                    var label = $(ipt).closest('label');
                    $(label).find('i').css({'color': '#a94442'});
                } else {
                    $(ipt).parent().addClass('has-error');
                }
                err++;
            } else {
                if ( $(ipt).hasClass('file_lampiran') ) {
                    var label = $(ipt).closest('label');
                    $(label).find('i').css({'color': '#000000'});
                } else {
                    $(ipt).parent().removeClass('has-error');
                }
            }
        });

        if ( err > 0 ) {
            bootbox.alert('Harap lengkapi data terlebih dahulu.');
        } else {
            bootbox.confirm('Apakah anda yakin ingin meng-ubah data rekening keluar ?', function(result) {
                if ( result ) { 
                    var data = {
                        'kode': $(elm).data('kode'),
                        'tanggal': dateSQL($(modal_body).find('#tgl_rk').data('DateTimePicker').date()),
                        'perusahaan': $(modal_body).find('.perusahaan').select2().val(),
                        'pelanggan': $(modal_body).find('.pelanggan').select2().val(),
                        'nominal': numeral.unformat($(modal_body).find('.nominal').val()),
                        'keterangan': $(modal_body).find('.keterangan').val(),
                        'jenis': $(modal_body).find('[name=optradio]:checked').val()
                    };

                    var formData = new FormData();

                    var _file = $('.file_lampiran').get(0).files[0];
                    formData.append('files', _file);
                    formData.append('data', JSON.stringify(data));

                    $.ajax({
                        url : 'pembayaran/RekeningTampungan/editRk',
                        type : 'post',
                        data : formData,
                        beforeSend : function(){ showLoading() },
                        success : function(data){
                            hideLoading();
                            if ( data.status == 1 ) {
                                bootbox.alert(data.message, function() {
                                	var startDate = $('#start_date_rk input').val();
									var endDate = $('#end_date_rk input').val();
									if ( !empty(startDate) && !empty(endDate) ) {
										rt.getListsRk();
									}

                                    bootbox.hideAll();
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
    }, // end - editRm

    deleteRk: function(elm) {
    	var modal_body = $('.modal-body');

        bootbox.confirm('Apakah anda yakin ingin meng-hapus data rekening keluar ?', function(result) {
            if ( result ) { 
                var kode = $(elm).data('kode');

                $.ajax({
                    url : 'pembayaran/RekeningTampungan/deleteRk',
                    type : 'post',
                    data : {
                    	'kode': kode
                    },
                    dataType: 'json',
                    beforeSend : function(){ showLoading() },
                    success : function(data){
                        hideLoading();
                        if ( data.status == 1 ) {
                            bootbox.alert(data.message, function() {
                            	var startDate = $('#start_date_rk input').val();
								var endDate = $('#end_date_rk input').val();
								if ( !empty(startDate) && !empty(endDate) ) {
									rt.getListsRk();
								}

                                bootbox.hideAll();
                            });
                        } else {
                            bootbox.alert(data.message);
                        }
                    }
                });
            }
        });
    }, // end - deleteRm
};

rt.startUp();