var gk = {
    startUp: function() {
        gk.settingUp();
    }, // end - startUp

    settingUp: function() {
        var div_riwayat = $('div#riwayat');
        var div_action = $('div#action');

        $(div_riwayat).find('select.perusahaan').select2();
        $(div_riwayat).find('select.bulan').select2();
        $(div_riwayat).find('#tahun').datetimepicker({
            locale: 'id',
            format: 'Y'
        });

        $(div_action).find('select.bulan').select2();
        $(div_action).find('#tahun').datetimepicker({
            locale: 'id',
            format: 'Y'
        });

        var tahun = $(div_action).find('#tahun input').attr('data-tgl');

        if ( !empty(tahun) ) {
            $(div_action).find('#tahun').data('DateTimePicker').date(new Date(tahun));
        }
        
        $.map( $(div_action).find('[name=tglTransfer]'), function(div) {
            $(div).find('input').datetimepicker({
                locale: 'id',
                format: 'DD MMM Y',
                useCurrent: true, //Important! See issue #1075
            });

            var tgl = $(div).find('input').attr('data-tgl');

            if ( !empty(tgl) ) {
                $(div).find('input').data('DateTimePicker').date(new Date(tgl));
            }
        });

        $(div_action).find('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal], [data-tipe=decimal3],[data-tipe=decimal4], [data-tipe=number]').each(function(){
		    $(this).priceFormat(Config[$(this).data('tipe')]);
		});
    }, // end - settingUp

    changeTabActive: function(elm) {
        var href = $(elm).data('href');
        var edit = $(elm).data('edit');
        var periode = $(elm).data('periode');
        var perusahaan = $(elm).data('perusahaan');

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

        gk.loadForm(periode, perusahaan, edit, href);
    }, // end - changeTabActive

    loadForm: function(periode, perusahaan, edit = null, href = null) {
        var dcontent = $('div#'+href);

        var params = {
        	'periode': periode,
        	'perusahaan': perusahaan
        };

        $.ajax({
            url : 'transaksi/GajiKaryawan/loadForm',
            data : {
                'params' :  params,
                'edit' :  edit
            },
            type : 'GET',
            dataType : 'HTML',
            beforeSend : function(){ App.showLoaderInContent(dcontent); },
            success : function(html){
                App.hideLoaderInContent(dcontent, html);

                gk.settingUp();
            },
        });
    }, // end - loadForm

    getLists: function() {
        var dcontent = $('div#riwayat');

        var err = 0;
        $.map( $(dcontent).find('[data-required=1]'), function( ipt ) {
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
            var data = {
                'bulan': $(dcontent).find('select.bulan').select2().val(),
                'tahun': $(dcontent).find('div#tahun input').val(),
                'perusahaan': $(dcontent).find('select.perusahaan').select2().val(),
            };

            var dtbody = $(dcontent).find('table tbody');

            $.ajax({
                url :'transaksi/GajiKaryawan/getLists',
                dataType: 'html',
				type: 'get',
                data : {
                    'params': data
                },
                beforeSend : function(){ App.showLoaderInContent(dtbody); },
                success : function(html){
                    App.hideLoaderInContent(dtbody, html);
                },
            });
        }
    }, // end - getLists

    htGrandTotal: function(elm) {
        var tr = $(elm).closest('tr');
        var tbody = $(tr).closest('tbody');

        var jml_transfer = 0;
        var _tot_gaji = numeral.unformat( $(tr).find('input.tot_gaji').val() );
        var _bpjs = numeral.unformat( $(tr).find('input.bpjs').val() );
        var _potongan = numeral.unformat( $(tr).find('input.potongan').val() );
        var _pph21 = numeral.unformat( $(tr).find('input.pph21').val() );

        jml_transfer = _tot_gaji - (_bpjs+_potongan+_pph21);
        $(tr).find('input.jml_transfer').val( numeral.formatDec( jml_transfer ) );

        var gt_gaji = 0;
        var gt_bpjs_karyawan = 0;
        var gt_potongan_hutang = 0;
        var gt_pph21_karyawan = 0;
        var gt_jumlah_transfer = 0;
        var gt_bpjs_perusahaan = 0;
        $.map( $(tbody).find('tr'), function(tr) {
            var tot_gaji = numeral.unformat( $(tr).find('input.tot_gaji').val() );
            var bpjs = numeral.unformat( $(tr).find('input.bpjs').val() );
            var potongan = numeral.unformat( $(tr).find('input.potongan').val() );
            var pph21 = numeral.unformat( $(tr).find('input.pph21').val() );
            var jml_transfer = numeral.unformat( $(tr).find('input.jml_transfer').val() );
            var bpjs_perusahaan = numeral.unformat( $(tr).find('input.bpjs_perusahaan').val() );

            gt_gaji += tot_gaji;
            gt_bpjs_karyawan += bpjs;
            gt_potongan_hutang += potongan;
            gt_pph21_karyawan += pph21;
            gt_jumlah_transfer += jml_transfer;
            gt_bpjs_perusahaan += bpjs_perusahaan;
        });

        $('div#action table thead tr td.gt_gaji b').text( numeral.formatDec(gt_gaji) );
        $('div#action table thead tr td.gt_bpjs_karyawan b').text( numeral.formatDec(gt_bpjs_karyawan) );
        $('div#action table thead tr td.gt_potongan_hutang b').text( numeral.formatDec(gt_potongan_hutang) );
        $('div#action table thead tr td.gt_pph21_karyawan b').text( numeral.formatDec(gt_pph21_karyawan) );
        $('div#action table thead tr td.gt_jumlah_transfer b').text( numeral.formatDec(gt_jumlah_transfer) );
        $('div#action table thead tr td.gt_bpjs_perusahaan b').text( numeral.formatDec(gt_bpjs_perusahaan) );
    }, // end - htGrandTotal

    save: function() {
        var dcontent = $('div#action');

        var err = 0;
        $.map( $(dcontent).find('[data-required=1]'), function( ipt ) {
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
            bootbox.confirm('Apakah anda yakin ingin menyimpan data ?', function(result) {
                if ( result ) {
                    var data = $.map( $('div#action table tbody').find('tr'), function(tr) {
                        var _data = {
                            'bulan': $('div#action select.bulan').select2().val(),
                            'tahun': $('div#action div#tahun').find('input').val(),
                            'perusahaan': $(tr).find('input.perusahaan').attr('data-kode'),
	                        'unit': $(tr).find('input.unit').attr('data-kode'),
                            'tot_gaji': numeral.unformat( $(tr).find('input.tot_gaji').val() ),
                            'bpjs_karyawan': numeral.unformat( $(tr).find('input.bpjs').val() ),
                            'pot_hutang': numeral.unformat( $(tr).find('input.potongan').val() ),
                            'pph21': numeral.unformat( $(tr).find('input.pph21').val() ),
                            'jml_transfer': numeral.unformat( $(tr).find('input.jml_transfer').val() ),
                            'bpjs_perusahaan': numeral.unformat( $(tr).find('input.bpjs_perusahaan').val() ),
                            'tgl_transfer': dateSQL( $(tr).find('[name=tglTransfer] input').data('DateTimePicker').date() )
                        };

                        return _data;
                    });

                    $.ajax({
                        url :'transaksi/GajiKaryawan/save',
                        data : {
                            'params': data
                        },
                        type : 'POST',
                        dataType : 'json',
                        beforeSend : function(){
                            showLoading();
                        },
                        success : function(data){
                            hideLoading();
                            if ( data.status == 1 ) {
                                bootbox.alert(data.message, function(){
                                    gk.loadForm(data.content.periode, data.content.perusahaan, null, 'action');
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
        var dcontent = $('div#action');

        var err = 0;
        $.map( $(dcontent).find('[data-required=1]'), function( ipt ) {
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
            bootbox.confirm('Apakah anda yakin ingin meng-ubah data ?', function(result) {
                if ( result ) {
                    var data = $.map( $('div#action table tbody').find('tr'), function(tr) {
                        var _data = {
                            'bulan': $('div#action select.bulan').select2().val(),
                            'tahun': $('div#action div#tahun').find('input').val(),
                            'perusahaan': $(tr).find('input.perusahaan').attr('data-kode'),
	                        'unit': $(tr).find('input.unit').attr('data-kode'),
                            'tot_gaji': numeral.unformat( $(tr).find('input.tot_gaji').val() ),
                            'bpjs_karyawan': numeral.unformat( $(tr).find('input.bpjs').val() ),
                            'pot_hutang': numeral.unformat( $(tr).find('input.potongan').val() ),
                            'pph21': numeral.unformat( $(tr).find('input.pph21').val() ),
                            'jml_transfer': numeral.unformat( $(tr).find('input.jml_transfer').val() ),
                            'bpjs_perusahaan': numeral.unformat( $(tr).find('input.bpjs_perusahaan').val() ),
                            'tgl_transfer': dateSQL( $(tr).find('[name=tglTransfer] input').data('DateTimePicker').date() )
                        };

                        return _data;
                    });

                    $.ajax({
                        url :'transaksi/GajiKaryawan/edit',
                        data : {
                            'params': data,
                            'periode_before': $(elm).attr('data-periode'),
                            'perusahaan_before': $(elm).attr('data-perusahaan'),
                        },
                        type : 'POST',
                        dataType : 'json',
                        beforeSend : function(){
                            showLoading();
                        },
                        success : function(data){
                            hideLoading();
                            if ( data.status == 1 ) {
                                bootbox.alert(data.message, function(){
                                    gk.loadForm(data.content.periode, data.content.perusahaan, null, 'action');
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
        bootbox.confirm('Apakah anda yakin ingin meng-hapus data ?', function(result) {
            if ( result ) {
                var data = {
                    'periode': $(elm).attr('data-periode'),
                    'perusahaan': $(elm).attr('data-perusahaan')
                };

                $.ajax({
                    url :'transaksi/GajiKaryawan/delete',
                    type : 'POST',
                    dataType : 'JSON',
                    data : {
                        'params': data
                    },
                    beforeSend : function(){ showLoading(); },
                    success : function(data){
                        hideLoading();
                        if ( data.status == 1 ) {
                            bootbox.alert(data.message, function(){
                                gk.loadForm(null, null, null, 'action');
                            });
                        } else {
                            bootbox.alert(data.message);
                        }
                    },
                });
            }
        });
    }, // end - delete
};

gk.startUp();