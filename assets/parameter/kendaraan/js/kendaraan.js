var kend = {
    startUp: function() {
        kend.settingUp();

        kend.getLists();
    }, // end - startUp

    settingUp: function() {
        var div_riwayat = $('div#history');
        var div_action = $('div#action');

        // $(div_riwayat).find('select.perusahaan').select2();
        $(div_action).find('select.perusahaan').select2();
        $(div_action).find('select.jenis').select2();
        $(div_action).find('select.tf_bank').select2();

        $(div_action).find('#TglPembelian, #MasaBerlakuStnk, #PajakTahunKe2, #PajakTahunKe3, #PajakTahunKe4, #PajakTahunKe5').datetimepicker({
            locale: 'id',
            format: 'DD MMM Y',
            useCurrent: true, //Important! See issue #1075
        });

        var tglPembelian = $(div_action).find('#TglPembelian input').attr('data-tgl');
        if ( !empty(tglPembelian) ) {
            $(div_action).find('#TglPembelian').data('DateTimePicker').date(new Date(tglPembelian));
        }

        var masaBerlaku = $(div_action).find('#MasaBerlakuStnk input').attr('data-tgl');
        if ( !empty(masaBerlaku) ) {
            $(div_action).find('#MasaBerlakuStnk').data('DateTimePicker').date(new Date(masaBerlaku));
        }

        var pajakTahunKe2 = $(div_action).find('#PajakTahunKe2 input').attr('data-tgl');
        if ( !empty(pajakTahunKe2) ) {
            $(div_action).find('#PajakTahunKe2').data('DateTimePicker').date(new Date(pajakTahunKe2));
        }

        var pajakTahunKe3 = $(div_action).find('#PajakTahunKe3 input').attr('data-tgl');
        if ( !empty(pajakTahunKe3) ) {
            $(div_action).find('#PajakTahunKe3').data('DateTimePicker').date(new Date(pajakTahunKe3));
        }

        var pajakTahunKe4 = $(div_action).find('#PajakTahunKe4 input').attr('data-tgl');
        if ( !empty(pajakTahunKe4) ) {
            $(div_action).find('#PajakTahunKe4').data('DateTimePicker').date(new Date(pajakTahunKe4));
        }

        var pajakTahunKe5 = $(div_action).find('#PajakTahunKe5 input').attr('data-tgl');
        if ( !empty(pajakTahunKe5) ) {
            $(div_action).find('#PajakTahunKe5').data('DateTimePicker').date(new Date(pajakTahunKe5));
        }

        $(div_action).find('[data-tipe=integer], [data-tipe=decimal], [data-tipe=decimal3], [data-tipe=decimal4]').each(function(){
		    $(this).priceFormat(Config[$(this).data('tipe')]);
		});

        $.map($(div_action).find('table tbody tr'), function(tr) {
            $(tr).find('#TglSerahTerima').datetimepicker({
                locale: 'id',
                format: 'DD MMM Y',
                useCurrent: true, //Important! See issue #1075
            });

            var tglSerahTerima = $(tr).find('#TglSerahTerima').attr('data-tgl');
            if ( !empty(tglSerahTerima) ) {
                $(tr).find('#TglSerahTerima').data('DateTimePicker').date(new Date(tglSerahTerima));
            }

            $(tr).find('select.pemegang_lama').select2();
            $(tr).find('select.unit_lama').select2();
            $(tr).find('select.pemegang_baru').select2();
            $(tr).find('select.unit_baru').select2();
        });
    }, // end - settingUp

    getLists: function() {
        var div = $('div#history');

        var dcontent = $(div).find('.table tbody');

        $.ajax({
            url: 'parameter/Kendaraan/getLists',
            data: {},
            type: 'GET',
            dataType: 'HTML',
            beforeSend: function(){ App.showLoaderInContent( $(dcontent) ); },
            success: function(html){
                App.hideLoaderInContent( $(dcontent), html );
            }
        });
    }, // end - getLists

	changeTabActive: function(elm) {
		var id = $(elm).data('id');
		var edit = $(elm).data('edit');
		var href = $(elm).data('href');

		$('a.nav-link').removeClass('active');
		$('div.tab-pane').removeClass('active');
		$('div.tab-pane').removeClass('show');

		$('a[data-tab='+href+']').addClass('active');
		$('div.tab-content').find('div#'+href).addClass('show');
		$('div.tab-content').find('div#'+href).addClass('active');

		kend.loadForm(id, edit, href);
	}, // end - changeTabActive

	loadForm: function(id, edit, href) {
		var params = {
			'id': id,
			'edit': edit
		};

		$.ajax({
            url: 'parameter/Kendaraan/loadForm',
            data: { 'params': params },
            type: 'GET',
            dataType: 'HTML',
            beforeSend: function(){},
            success: function(html){
                $('div#'+href).html( html );

                kend.settingUp();
            }
        });
	}, // end - loadForm

    save: function() {
        var div_action = $('div#action');

        var err = 0;

        $.map( $(div_action).find('[data-required=1]'), function(ipt) {
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
            bootbox.confirm('Apakah anda yakin ingin menyimpan data kendaraan ?', function(result) {
                if ( result ) {
                    var detail = $.map( $(div_action).find('table tbody tr'), function(tr) {
                        var _detail = {
                            'tgl_serah_terima': dateSQL( $(tr).find('#TglSerahTerima').data('DateTimePicker').date() ),
                            'pemegang_lama': $(tr).find('.pemegang_lama').select2().val(),
                            'unit_lama': $(tr).find('.unit_lama').select2().val(),
                            'pemegang_baru': $(tr).find('.pemegang_baru').select2().val(),
                            'unit_baru': $(tr).find('.unit_baru').select2().val(),
                            'keterangan': $(tr).find('.keterangan').val(),
                        };

                        return _detail;
                    });

                    var data = {
                        'perusahaan': $(div_action).find('.perusahaan').select2().val(),
                        'jenis': $(div_action).find('.jenis').select2().val(),
                        'nopol': $(div_action).find('.nopol').val(),
                        'tgl_pembelian': dateSQL( $(div_action).find('#TglPembelian').data('DateTimePicker').date() ),
                        'merk': $(div_action).find('.merk').val(),
                        'tipe': $(div_action).find('.tipe').val(),
                        'warna': $(div_action).find('.warna').val(),
                        'tahun': $(div_action).find('.tahun').val(),
                        'no_bpkb': $(div_action).find('.no_bpkb').val(),
                        'no_stnk': $(div_action).find('.no_stnk').val(),
                        'masa_berlaku_stnk': dateSQL( $(div_action).find('#MasaBerlakuStnk').data('DateTimePicker').date() ),
                        'pajak_tahun_ke2': !empty($(div_action).find('#PajakTahunKe2 input').val()) ? dateSQL( $(div_action).find('#PajakTahunKe2').data('DateTimePicker').date() ) : null,
                        'pajak_tahun_ke3': !empty($(div_action).find('#PajakTahunKe3 input').val()) ? dateSQL( $(div_action).find('#PajakTahunKe3').data('DateTimePicker').date() ) : null,
                        'pajak_tahun_ke4': !empty($(div_action).find('#PajakTahunKe4 input').val()) ? dateSQL( $(div_action).find('#PajakTahunKe4').data('DateTimePicker').date() ) : null,
                        'pajak_tahun_ke5': !empty($(div_action).find('#PajakTahunKe5 input').val()) ? dateSQL( $(div_action).find('#PajakTahunKe5').data('DateTimePicker').date() ) : null,
                        'detail': detail
                    };

                    $.ajax({
                        url :'parameter/Kendaraan/save',
                        type : 'POST',
                        dataType : 'JSON',
                        data : {
                            'params': data
                        },
                        beforeSend : function(){
                            showLoading();
                        },
                        success : function(data){
                            hideLoading();
                            if(data.status){
                                bootbox.alert(data.message, function(){
                                    kend.loadForm(data.content.id, null, 'action');
                                    kend.getLists();
                                });
                            }else{
                                bootbox.alert(data.message);
                            }
                        },
                    });
                }
            });
        }
    }, // end - save

    edit: function(elm) {
        var div_action = $('div#action');

        var err = 0;

        $.map( $(div_action).find('[data-required=1]'), function(ipt) {
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
            bootbox.confirm('Apakah anda yakin ingin menyimpan data kendaraan ?', function(result) {
                if ( result ) {
                    var detail = $.map( $(div_action).find('table tbody tr'), function(tr) {
                        var _detail = {
                            'tgl_serah_terima': dateSQL( $(tr).find('#TglSerahTerima').data('DateTimePicker').date() ),
                            'pemegang_lama': $(tr).find('.pemegang_lama').select2().val(),
                            'unit_lama': $(tr).find('.unit_lama').select2().val(),
                            'pemegang_baru': $(tr).find('.pemegang_baru').select2().val(),
                            'unit_baru': $(tr).find('.unit_baru').select2().val(),
                            'keterangan': $(tr).find('.keterangan').val(),
                        };

                        return _detail;
                    });

                    var data = {
                        'id': $(elm).attr('data-id'),
                        'perusahaan': $(div_action).find('.perusahaan').select2().val(),
                        'jenis': $(div_action).find('.jenis').select2().val(),
                        'nopol': $(div_action).find('.nopol').val(),
                        'tgl_pembelian': dateSQL( $(div_action).find('#TglPembelian').data('DateTimePicker').date() ),
                        'merk': $(div_action).find('.merk').val(),
                        'tipe': $(div_action).find('.tipe').val(),
                        'warna': $(div_action).find('.warna').val(),
                        'tahun': $(div_action).find('.tahun').val(),
                        'no_bpkb': $(div_action).find('.no_bpkb').val(),
                        'no_stnk': $(div_action).find('.no_stnk').val(),
                        'masa_berlaku_stnk': dateSQL( $(div_action).find('#MasaBerlakuStnk').data('DateTimePicker').date() ),
                        'pajak_tahun_ke2': !empty($(div_action).find('#PajakTahunKe2 input').val()) ? dateSQL( $(div_action).find('#PajakTahunKe2').data('DateTimePicker').date() ) : null,
                        'pajak_tahun_ke3': !empty($(div_action).find('#PajakTahunKe3 input').val()) ? dateSQL( $(div_action).find('#PajakTahunKe3').data('DateTimePicker').date() ) : null,
                        'pajak_tahun_ke4': !empty($(div_action).find('#PajakTahunKe4 input').val()) ? dateSQL( $(div_action).find('#PajakTahunKe4').data('DateTimePicker').date() ) : null,
                        'pajak_tahun_ke5': !empty($(div_action).find('#PajakTahunKe5 input').val()) ? dateSQL( $(div_action).find('#PajakTahunKe5').data('DateTimePicker').date() ) : null,
                        'detail': detail
                    };

                    $.ajax({
                        url :'parameter/Kendaraan/edit',
                        type : 'POST',
                        dataType : 'JSON',
                        data : {
                            'params': data
                        },
                        beforeSend : function(){
                            showLoading();
                        },
                        success : function(data){
                            hideLoading();
                            if(data.status){
                                bootbox.alert(data.message, function(){
                                    kend.loadForm(data.content.id, null, 'action');
                                    kend.getLists();
                                });
                            }else{
                                bootbox.alert(data.message);
                            }
                        },
                    });
                }
            });
        }
    }, // end - edit

    delete: function(elm) {
        var div_action = $('div#action');

        bootbox.confirm('Apakah anda yakin ingin meng-hapus data kendaraan ?', function(result) {
            if ( result ) {
                var data = {
                    'id': $(elm).attr('data-id'),
                };

                $.ajax({
                    url :'parameter/Kendaraan/delete',
                    type : 'POST',
                    dataType : 'JSON',
                    data : {
                        'params': data
                    },
                    beforeSend : function(){
                        showLoading();
                    },
                    success : function(data){
                        hideLoading();
                        if(data.status){
                            bootbox.alert(data.message, function(){
                                kend.loadForm(null, null, 'action');
                                kend.getLists();
                            });
                        }else{
                            bootbox.alert(data.message);
                        }
                    },
                });
            }
        });
    }, // end - delete
};

kend.startUp();