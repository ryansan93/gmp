var est = {
    startUp: function() {
        est.getLists();
    }, // end - startUp

    settingUp: function() {
        $("#StartDate").datetimepicker({
            locale: 'id',
            format: 'DD MMM Y',
            useCurrent:false,
            daysOfWeekDisabled: [0, 2, 3, 4, 5, 6],
        });
        $("#EndDate").datetimepicker({
            locale: 'id',
            format: 'DD MMM Y',
            useCurrent:false,
            daysOfWeekDisabled: [1, 2, 3, 4, 5, 6],
        });

        var startDate = $("#StartDate").find('input').attr('data-tgl');
        var endDate = $("#EndDate").find('input').attr('data-tgl');

        if ( !empty(startDate) ) {
            $("#StartDate").data("DateTimePicker").date(moment(new Date(startDate)));
            var minDate = dateSQL($("#StartDate").data("DateTimePicker").date())+' 00:00:00';
            $("#EndDate").data("DateTimePicker").minDate(moment(new Date(minDate)));
        }

        if ( !empty(endDate) ) {
            $("#EndDate").data("DateTimePicker").date(moment(new Date(endDate)));
            var maxDate = dateSQL($("#EndDate").data("DateTimePicker").date())+' 23:59:59';
            $("#StartDate").data("DateTimePicker").maxDate(moment(new Date(maxDate)));
        }

        // var today = moment(new Date()).format('YYYY-MM-DD');
        $("#StartDate").on("dp.change", function (e) {
            var minDate = dateSQL($("#StartDate").data("DateTimePicker").date())+' 00:00:00';
            $("#EndDate").data("DateTimePicker").minDate(moment(new Date(minDate)));
        });
        $("#EndDate").on("dp.change", function (e) {
            var maxDate = dateSQL($("#EndDate").data("DateTimePicker").date())+' 23:59:59';
            $("#StartDate").data("DateTimePicker").maxDate(moment(new Date(maxDate)));
            // if ( maxDate >= (today+' 00:00:00') ) {
            // }
        });

        $('.perusahaan').select2();
        $('.unit').select2();

        $('[data-tipe=integer], [data-tipe=angka], [data-tipe=decimal], [data-tipe=decimal3],[data-tipe=decimal4], [data-tipe=number]').each(function(){
            $(this).priceFormat(Config[$(this).data('tipe')]);
        });
    }, // end - settingUp

    getLists: function() {
        var dcontent = $('table.tblRiwayat tbody');

        $.ajax({
            url: 'transaksi/EstimasiChickInMingguan/getLists',
            data: {},
            type: 'GET',
            dataType: 'HTML',
            beforeSend: function(){ App.showLoaderInContent(dcontent); },
            success: function(html){
                App.hideLoaderInContent(dcontent, html);
            }
        });
    }, // end - getLists

    addForm: function() {
        $.get('transaksi/EstimasiChickInMingguan/addForm',{
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

                est.settingUp();

                $('.modal').removeAttr('tabindex');
            });
        },'html');
    }, // end - addForm

    editForm: function(elm) {
        var params = {
            'id': $(elm).attr('data-id')
        };

        $.get('transaksi/EstimasiChickInMingguan/editForm',{
            'params': params
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

                est.settingUp();

                $('.modal').removeAttr('tabindex');
            });
        },'html');
    }, // end - editForm

    save: function() {
        var div = $('.modal-body');

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
						'start_date': dateSQL( $(div).find('#StartDate').data('DateTimePicker').date() ),
						'end_date': dateSQL( $(div).find('#EndDate').data('DateTimePicker').date() ),
						'perusahaan': $(div).find('.perusahaan').select2('val'),
						'unit': $(div).find('.unit').select2('val'),
						'jumlah': numeral.unformat( $(div).find('.jumlah').val() )
					};

					$.ajax({
			            url: 'transaksi/EstimasiChickInMingguan/save',
			            data: { 'params': params },
			            type: 'POST',
			            dataType: 'JSON',
			            beforeSend: function(){ showLoading('Proses simpan data . . .') },
			            success: function(data){
                            hideLoading();
			            	if ( data.status == 1 ) {
			            		bootbox.alert(data.message, function() {
                                    $('.modal').modal('hide');
			            			est.getLists();
			            		});
			            	} else{
			            		bootbox.alert(data.message);
			            	}
			            }
			        });
				}
			});
		}
    }, // end - save

    edit: function(elm) {
        var div = $('.modal-body');

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
						'id': $(elm).attr('data-id'),
						'start_date': dateSQL( $(div).find('#StartDate').data('DateTimePicker').date() ),
						'end_date': dateSQL( $(div).find('#EndDate').data('DateTimePicker').date() ),
						'perusahaan': $(div).find('.perusahaan').select2('val'),
						'unit': $(div).find('.unit').select2('val'),
						'jumlah': numeral.unformat( $(div).find('.jumlah').val() )
					};

					$.ajax({
			            url: 'transaksi/EstimasiChickInMingguan/edit',
			            data: { 'params': params },
			            type: 'POST',
			            dataType: 'JSON',
			            beforeSend: function(){ showLoading('Proses simpan data . . .') },
			            success: function(data){
                            hideLoading();
			            	if ( data.status == 1 ) {
			            		bootbox.alert(data.message, function() {
                                    $('.modal').modal('hide');
			            			est.getLists();
			            		});
			            	} else{
			            		bootbox.alert(data.message);
			            	}
			            }
			        });
				}
			});
		}
    }, // end - edit

    delete: function(elm) {
        var div = $('.modal-body');

        bootbox.confirm('Apakah anda yakin ingin meng-hapus data ?', function (result) {
            if ( result ) {
                var params = {
                    'id': $(elm).attr('data-id')
                };

                $.ajax({
                    url: 'transaksi/EstimasiChickInMingguan/delete',
                    data: { 'params': params },
                    type: 'POST',
                    dataType: 'JSON',
                    beforeSend: function(){ showLoading('Proses hapus data . . .') },
                    success: function(data){
                        hideLoading();
                        if ( data.status == 1 ) {
                            bootbox.alert(data.message, function() {
                                $('.modal').modal('hide');
                                est.getLists();
                            });
                        } else{
                            bootbox.alert(data.message);
                        }
                    }
                });
            }
        });
    }, // end - delete
};

est.startUp();