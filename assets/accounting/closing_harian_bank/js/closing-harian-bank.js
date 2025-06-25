var chb = {
    startUp: function () {
        chb.settingUp();
    }, // end - startUp

    settingUp: function () {
        $('div#riwayat').find('select.bank').select2();
        $('div#action').find('select.bank').select2();

        $("#StartDate").datetimepicker({
            locale: 'id',
            format: 'DD MMM Y'
        });
        $("#EndDate").datetimepicker({
            locale: 'id',
            format: 'DD MMM Y'
        });
        var today = moment(new Date()).format('YYYY-MM-DD');
        $("#StartDate").on("dp.change", function (e) {
            var minDate = dateSQL($("#StartDate").data("DateTimePicker").date())+' 00:00:00';
            $("#EndDate").data("DateTimePicker").minDate(moment(new Date(minDate)));
        });
        $("#EndDate").on("dp.change", function (e) {
            var maxDate = dateSQL($("#EndDate").data("DateTimePicker").date())+' 23:59:59';
            if ( maxDate >= (today+' 00:00:00') ) {
                $("#StartDate").data("DateTimePicker").maxDate(moment(new Date(maxDate)));
            }
        });

        $("#Tanggal").datetimepicker({
            locale: 'id',
            format: 'DD MMM Y',
            maxDate: moment(new Date(today))
        });
    }, // end - settingUp

    changeTabActive: function(elm) {
        var href = $(elm).data('href');
        var edit = $(elm).data('edit');
        var id = $(elm).data('id');
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

        chb.loadForm(id, edit, href);
    }, // end - changeTabActive

    loadForm: function(id = null, edit = null, href = null) {
        var dcontent = $('div#'+href);

        var params = {
            'id': id
        };

        $.ajax({
            url : 'accounting/ClosingHarianBank/loadForm',
            data : {
                'params' :  params,
                'edit' :  edit
            },
            type : 'GET',
            dataType : 'HTML',
            beforeSend : function(){ App.showLoaderInContent(dcontent); },
            success : function(html){
                App.hideLoaderInContent(dcontent, html);

                chb.settingUp();
				chb.hitTotal( dcontent );
            },
        });
    }, // end - loadForm

    getLists: function () {
        var div = $('div#riwayat');
        var dcontent = $(div).find('table tbody');

		var err = 0;

		$.map( $(div).find('[data-required=1]'), function (ipt) {
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
				'bank': $(div).find('select.bank').select2().val(),
				'start_date': dateSQL( $('#StartDate').data('DateTimePicker').date() ),
				'end_date': dateSQL( $('#EndDate').data('DateTimePicker').date() )
			};

			$.ajax({
                url : 'accounting/ClosingHarianBank/getLists',
                data : {
                    'params' : params
                },
                type : 'GET',
                dataType : 'HTML',
                beforeSend : function(){ App.showLoaderInContent( $(dcontent) ); },
                success : function(html){
                	App.hideLoaderInContent( $(dcontent), html );
                }
            });
		}
	}, // end - getLists

    getDataHarian: function () {
        var div = $('div#action');
        var dcontent = $(div).find('table tbody');

		var err = 0;

		$.map( $(div).find('[data-required=1]'), function (ipt) {
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
				'bank': $(div).find('select.bank').select2().val(),
				'tanggal': dateSQL( $('#Tanggal').data('DateTimePicker').date() )
			};

			$.ajax({
                url : 'accounting/ClosingHarianBank/getDataHarian',
                data : {
                    'params' : params
                },
                type : 'POST',
                dataType : 'JSON',
                beforeSend : function(){ App.showLoaderInContent( $(dcontent) ); },
                success : function(data){
                    if ( data.status == 1 ) {
                        if ( data.content.save == 1 ) {
                            $(div).find('.btn-tutup-saldo').removeClass('hide');
                        } else {
                            $(div).find('.btn-tutup-saldo').addClass('hide');
                        }

                        App.hideLoaderInContent( $(dcontent), data.content.html );
    
                        chb.hitTotal(div);
                    } else {
                        bootbox.alert(data.message);
                    }
                }
            });
		}
	}, // end - getDataHarian

    hitTotal: function( div ) {
        var tbody = $(div).find('table tbody');
        var thead = $(div).find('table thead');

        var tot_debit = 0;
        var tot_kredit = 0;

        $.map( $(tbody).find('tr'), function (tr) {
            var debit = parseFloat(numeral.unformat($(tr).find('td.debit').text()));
            var kredit = parseFloat(numeral.unformat($(tr).find('td.kredit').text()));
            
            tot_debit += debit;
            tot_kredit += kredit;
        });

        $(thead).find('td.tot_debit b').text( numeral.formatDec( tot_debit ) );
        $(thead).find('td.tot_kredit b').text( numeral.formatDec( tot_kredit ) );
    }, // end - hitTotal

    save: function () {
        var div = $('div#action');
        var dcontent = $(div).find('table tbody');

		var err = 0;

		$.map( $(div).find('[data-required=1]'), function (ipt) {
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
            var nama_bank = $(div).find('select.bank option:selected').text();
            var nama_tanggal = $('#Tanggal').find('input').val();

            var thead = $(div).find('table thead');

            var debit = parseFloat(numeral.unformat( $(thead).find('td.tot_debit b').text() ));
            var kredit = parseFloat(numeral.unformat( $(thead).find('td.tot_kredit b').text() ));

            var saldo_akhir = debit - kredit;
            if ( saldo_akhir < 0 ) {
                saldo_akhir = 0;
            }
            var text_saldo_akhir = numeral.formatDec(saldo_akhir);
            // if ( saldo_akhir < 0 ) {
            //     text_saldo_akhir = '('+numeral.formatDec(Math.abs(saldo_akhir))+')';
            // }

            bootbox.confirm('Apakah anda yakin ingin menutup saldo bank <b>'+nama_bank+'</b> tanggal <b>'+nama_tanggal.toUpperCase()+'</b> senilai <b> Rp. '+text_saldo_akhir+'</b> ?', function(result) {
                if ( result ) {
                    var params = {
                        'bank': $(div).find('select.bank').select2().val(),
                        'tanggal': dateSQL( $('#Tanggal').data('DateTimePicker').date() ),
                        'saldo': saldo_akhir
                    };

                    $.ajax({
                        url : 'accounting/ClosingHarianBank/save',
                        data : {
                            'params' : params
                        },
                        type : 'POST',
                        dataType : 'JSON',
                        beforeSend : function(){ showLoading(); },
                        success : function(data){
                            hideLoading();
                            if ( data.status == 1 ) {
                                bootbox.alert(data.message, function() {
                                    chb.loadForm( data.content.id, null, 'action' );
                                });
                            } else {
                                bootbox.alert(data.message);
                            }
                        }
                    });
                }
            });
		}
    }, // end - save
};

chb.startUp();