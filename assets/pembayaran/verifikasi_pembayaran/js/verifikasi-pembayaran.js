var vp = {
    startUp: function () {
        vp.settingUp();
        vp.getDataOutstanding();
    }, // end - startUp

    settingUp: function() {
        $('.date').datetimepicker({
            locale: 'id',
            format: 'DD MMM Y',
            useCurrent: false, //Important! See issue #1075
            widgetPositioning: {
                horizontal: "auto",
                vertical: "auto"
            }
        });

        $("#startDate").on("dp.change", function (e) {
            $("#endDate").data("DateTimePicker").minDate(e.date);
        });

        $("#endDate").on("dp.change", function (e) {
            $("#startDate").data("DateTimePicker").maxDate(e.date);
        });

        $('select.jenis_transaksi').select2({placeholder: 'Pilih Jenis Transaksi'}).on("select2:select", function (e) {
            var jt = $('select.jenis_transaksi').select2().val();
	
            for (var i = 0; i < jt.length; i++) {
                if ( jt[i] == 'all' ) {
                    $('select.jenis_transaksi').select2().val('all').trigger('change');

                    i = jt.length;
                }
            }

            $('select.jenis_transaksi').next('span.select2').css('width', '100%');
        });
        $('select.jenis_transaksi').next('span.select2').css('width', '100%');

        $('div#outstanding').find('select.bank').select2().on("select2:select", function (e) {
            vp.filterOutstanding();
        });
        $('div#history').find('select.bank').select2();
    }, // end - settingUp

    getDataOutstanding: function() {
        var dcontent = $('#outstanding').find('table tbody');

        $.ajax({
            url : 'pembayaran/VerifikasiPembayaran/getDataOutstanding',
            data : {},
            type : 'GET',
            dataType : 'HTML',
            beforeSend : function(){ App.showLoaderInContent(dcontent); },
            success : function(html){
                App.hideLoaderInContent(dcontent, html);

                vp.filterOutstanding();
            },
        });
    }, // end - getDataOutstanding

    filterOutstanding: function() {
        var div = $('div#outstanding');

        var bank = $(div).find('select.bank').select2().val();

        $(div).find('tr.data').addClass('hide');
        if ( bank != 'all' ) {
            $(div).find('tr.data[data-coabank="'+bank+'"]').removeClass('hide');
        } else {
            $(div).find('tr.data').removeClass('hide');
        }
    }, // end - filterOutstanding

    getLists: function() {
        let dcontent = $('table.tbl_riwayat tbody');

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
                'start_date': dateSQL($('#startDate').data('DateTimePicker').date()),
                'end_date': dateSQL($('#endDate').data('DateTimePicker').date()),
                'jenis': $('.jenis_transaksi').select2().val(),
                'bank': $('div#history').find('.bank').select2().val()
            };

            $.ajax({
                url : 'pembayaran/VerifikasiPembayaran/getLists',
                data : { 'params': params },
                type : 'get',
                dataType : 'html',
                beforeSend : function(){ App.showLoaderInContent(dcontent); },
                success : function(html){
                    App.hideLoaderInContent(dcontent, html);
                },
            });
        }
    }, // end - get_lists

    formDetail: function(elm) {
        var tr = $(elm).closest('tr.data');

        var params = {
            'id': $(elm).attr('data-id'),
            'tbl_name': $(elm).attr('data-table'),
            'no_rek': $(tr).attr('data-norek'),
            'atas_nama': $(tr).attr('data-atasnama'),
            'bank': $(tr).attr('data-bank')
        };

        $.get('pembayaran/VerifikasiPembayaran/formDetail',{
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

                $(modal_dialog).css({'max-width' : '50%'});
                $(modal_dialog).css({'width' : '50%'});

                var modal_header = $(this).find('.modal-header');
                $(modal_header).css({'padding-top' : '0px'});

                $(modal_body).find('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal], [data-tipe=decimal3],[data-tipe=decimal4], [data-tipe=number]').each(function(){
                    $(this).priceFormat(Config[$(this).data('tipe')]);
                });

                var tglBayar = $(modal_body).find('#tglBayar').data('val');
                $(modal_body).find('#tglBayar').datetimepicker({
                    locale: 'id',
                    format: 'DD MMM Y'
                });

                if ( !empty(tglBayar) ) {
                    $(modal_body).find('#tglBayar').data("DateTimePicker").date(new Date(tgl_bayar));
                }

                App.setTutupBulan();
            });
        },'html');
    }, // end - formDetail

    encryptParams: function(elm) {
        var modal = $(elm).closest('.modal-body');

        var params = {
            'id': $(elm).attr('data-id'),
            'no_rek': $(modal).find('span.norek').text(),
            'atas_nama': $(modal).find('span.atasnama').text(),
            'bank': $(modal).find('span.bank').text()
        };

        $.ajax({
            url: 'pembayaran/VerifikasiPembayaran/encryptParams',
            data: {
                'params': params
            },
            type: 'POST',
            dataType: 'JSON',
            beforeSend: function() { showLoading(); },
            success: function(data) {
                hideLoading();

                if ( data.status == 1 ) {
                    vp.exportExcel(data.content);
                } else {
                    bootbox.alert( data.message );
                }
            }
        });
	}, // end - encryptParams

	exportExcel : function (params) {
		goToURL('pembayaran/VerifikasiPembayaran/exportExcel/'+params);
	}, // end - exportExcel

    formRealisasiBayar: function(elm) {
        var params = {
            'id': $(elm).attr('data-id'),
            'tbl_name': $(elm).attr('data-table')
        };

        $.get('pembayaran/VerifikasiPembayaran/formRealisasiBayar',{
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

                $(modal_dialog).css({'max-width' : '50%'});
                $(modal_dialog).css({'width' : '50%'});

                var modal_header = $(this).find('.modal-header');
                $(modal_header).css({'padding-top' : '0px'});

                $(modal_body).find('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal], [data-tipe=decimal3],[data-tipe=decimal4], [data-tipe=number]').each(function(){
                    $(this).priceFormat(Config[$(this).data('tipe')]);
                });

                var tglBayar = $(modal_body).find('#tglBayar').data('val');
                $(modal_body).find('#tglBayar').datetimepicker({
                    locale: 'id',
                    format: 'DD MMM Y'
                });

                if ( !empty(tglBayar) ) {
                    $(modal_body).find('#tglBayar').data("DateTimePicker").date(new Date(tgl_bayar));
                }

                App.setTutupBulan();
            });
        },'html');
    }, // end - formRealisasiBayar

    formRealisasiBayarDetail: function(elm) {
        var params = {
            'id': $(elm).attr('data-id'),
            'tbl_name': $(elm).attr('data-table')
        };

        $.get('pembayaran/VerifikasiPembayaran/formRealisasiBayarDetail',{
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

                $(modal_dialog).css({'max-width' : '50%'});
                $(modal_dialog).css({'width' : '50%'});

                var modal_header = $(this).find('.modal-header');
                $(modal_header).css({'padding-top' : '0px'});

                $(modal_body).find('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal], [data-tipe=decimal3],[data-tipe=decimal4], [data-tipe=number]').each(function(){
                    $(this).priceFormat(Config[$(this).data('tipe')]);
                });

                var tglBayar = $(modal_body).find('#tglBayar').data('val');
                $(modal_body).find('#tglBayar').datetimepicker({
                    locale: 'id',
                    format: 'DD MMM Y'
                });

                if ( !empty(tglBayar) ) {
                    $(modal_body).find('#tglBayar').data("DateTimePicker").date(new Date(tgl_bayar));
                }

                App.setTutupBulan();
            });
        },'html');
    }, // end - formRealisasiBayarDetail

    formRealisasiBayarEdit: function(elm) {
        $('.modal').modal('hide');

        var params = {
            'id': $(elm).attr('data-id'),
            'tbl_name': $(elm).attr('data-table')
        };

        $.get('pembayaran/VerifikasiPembayaran/formRealisasiBayarEdit',{
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

                $(modal_dialog).css({'max-width' : '50%'});
                $(modal_dialog).css({'width' : '50%'});

                var modal_header = $(this).find('.modal-header');
                $(modal_header).css({'padding-top' : '0px'});

                $(modal_body).find('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal], [data-tipe=decimal3],[data-tipe=decimal4], [data-tipe=number]').each(function(){
                    $(this).priceFormat(Config[$(this).data('tipe')]);
                });

                var tglBayar = $(modal_body).find('#tglBayar').attr('data-val');
                $(modal_body).find('#tglBayar').datetimepicker({
                    locale: 'id',
                    format: 'DD MMM Y'
                });

                if ( !empty(tglBayar) ) {
                    $(modal_body).find('#tglBayar').data("DateTimePicker").date(new Date(tglBayar));
                }

                App.setTutupBulan();
            });
        },'html');
    }, // end - formRealisasiBayarEdit

    formEditJurnal: function(elm) {
        var params = {
            'id': $(elm).attr('data-id'),
            'tbl_name': $(elm).attr('data-table')
        };

        $.get('pembayaran/VerifikasiPembayaran/formEditJurnal',{
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

                $(modal_dialog).css({'max-width' : '80%'});
                $(modal_dialog).css({'width' : '80%'});

                var modal_header = $(this).find('.modal-header');
                $(modal_header).css({'padding-top' : '0px'});

                $(modal_body).find('select.asal, select.tujuan').select2({ dropdownParent: $(this) }).on('select2:select', function(e) {
                    vp.hitTotalJurnal();
                });

                $(modal_body).find('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal], [data-tipe=decimal3],[data-tipe=decimal4], [data-tipe=number]').each(function(){
                    $(this).priceFormat(Config[$(this).data('tipe')]);
                });

                vp.hitTotalJurnal();
            });
        },'html');
    }, // end - formEditJurnal

    addRowJurnal: function(elm) {
        let row = $(elm).closest('tr');
        let tbody = $(row).closest('tbody');
        let modal = $(elm).closest('.modal');

        $(row).find('select.asal, select.tujuan').select2('destroy')
                                   .removeAttr('data-live-search')
                                   .removeAttr('data-select2-id')
                                   .removeAttr('aria-hidden')
                                   .removeAttr('tabindex');
        $(row).find('select.asal option, select.tujuan option').removeAttr('data-select2-id');

        let newRow = row.clone();

        newRow.removeAttr('data-id');
        newRow.removeAttr('data-delete');
        newRow.find('input, select, textarea').val('');

        row.after(newRow);

        $.map( [row, newRow], function(tr) {
            $(tr).find('select.asal, select.tujuan').select2({ dropdownParent: modal }).on('select2:select', function(e) {
                vp.hitTotalJurnal();
            });
        });

        newRow.find('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal], [data-tipe=decimal3],[data-tipe=decimal4], [data-tipe=number]').each(function(){
            $(this).priceFormat(Config[$(this).data('tipe')]);
        });

        vp.hitTotalJurnal();
    }, // end - addRowJurnal

    removeRowJurnal: function(elm) {
        let tbody = $(elm).closest('tbody');
        let row = $(elm).closest('tr');

        if ( $(tbody).find('tr').length > 1 ) {
            var id = $(row).attr('data-id');
            if ( !empty(id) ) {
                bootbox.confirm('Baris ini sudah tersimpan sebagai jurnal otomatis. Hapus baris ini juga saat Simpan ?', function(result) {
                    if ( result ) {
                        $(row).attr('data-delete', '1');
                        $(row).hide();

                        vp.hitTotalJurnal();
                    }
                });
            } else {
                $(row).remove();

                vp.hitTotalJurnal();
            }
        } else {
            bootbox.alert('Minimal harus ada 1 baris jurnal.');
        }
    }, // end - removeRowJurnal

    hitTotalJurnal: function() {
        var total_debet = 0;
        var total_kredit = 0;
        var total = 0;

        $.map( $('#tbl_edit_jurnal tbody tr').not('[data-delete]'), function(tr) {
            var nominal = ( numeral.unformat( $(tr).find('input.nominal').val() ) || 0 );
            var coa_asal = $(tr).find('select.asal').val();
            var coa_tujuan = $(tr).find('select.tujuan').val();

            if ( !empty(coa_asal) ) {
                total_kredit += nominal;
            }
            if ( !empty(coa_tujuan) ) {
                total_debet += nominal;
            }

            total += nominal;
        });

        var fmt = { minimumFractionDigits: 2, maximumFractionDigits: 2 };
        $('#total_kredit_jurnal').text( total_kredit.toLocaleString('id-ID', fmt) );
        $('#total_debet_jurnal').text( total_debet.toLocaleString('id-ID', fmt) );
        $('#total_nominal_jurnal').text( total.toLocaleString('id-ID', fmt) );
    }, // end - hitTotalJurnal

    saveEditJurnal: function(elm) {
        var rows = $('#tbl_edit_jurnal tbody tr');
        var fmt = { minimumFractionDigits: 2, maximumFractionDigits: 2 };

        var totalSebelumnya = parseFloat( $('#total_nominal_jurnal').attr('data-original') );
        var total_debet = 0;
        var total_kredit = 0;
        var totalNow = 0;
        $.map( $(rows).not('[data-delete]'), function(tr) {
            var nominal = ( numeral.unformat( $(tr).find('input.nominal').val() ) || 0 );
            var coa_asal = $(tr).find('select.asal').val();
            var coa_tujuan = $(tr).find('select.tujuan').val();

            if ( !empty(coa_asal) ) {
                total_kredit += nominal;
            }
            if ( !empty(coa_tujuan) ) {
                total_debet += nominal;
            }

            totalNow += nominal;
        });

        if ( Math.abs(total_debet - total_kredit) > 1 ) {
            bootbox.alert('Data tidak balance. Total Debet ('+total_debet.toLocaleString('id-ID', fmt)+') harus sama dengan Total Kredit ('+total_kredit.toLocaleString('id-ID', fmt)+').');
        } else if ( Math.abs(totalNow - totalSebelumnya) > 1 ) {
            bootbox.alert('Total nominal jurnal ('+totalNow.toLocaleString('id-ID', fmt)+') tidak boleh berbeda dengan total sebelumnya ('+totalSebelumnya.toLocaleString('id-ID', fmt)+').');
        } else {
            bootbox.confirm('Apakah anda yakin ingin menyimpan perubahan jurnal ?', function(result) {
                if ( result ) {
                    var tanggal_jurnal = $('#tanggal_jurnal').attr('data-tgl');

                    var detail = $.map( rows, function(tr) {
                        return {
                            'id': $(tr).attr('data-id') || null,
                            'delete': $(tr).attr('data-delete') == '1' ? 1 : 0,
                            'tanggal': tanggal_jurnal,
                            'coa_asal': $(tr).find('select.asal').val(),
                            'coa_asal_nama': $(tr).find('select.asal option:selected').attr('data-nama'),
                            'unit_asal': $(tr).find('select.unit_asal').val(),
                            'coa_tujuan': $(tr).find('select.tujuan').val(),
                            'coa_tujuan_nama': $(tr).find('select.tujuan option:selected').attr('data-nama'),
                            'unit_tujuan': $(tr).find('select.unit_tujuan').val(),
                            'nominal': numeral.unformat($(tr).find('input.nominal').val()),
                            'keterangan': $(tr).find('input.keterangan').val()
                        };
                    });

                    var params = {
                        'id': $(elm).attr('data-id'),
                        'tbl_name': $(elm).attr('data-table'),
                        'detail': detail
                    };

                    $.ajax({
                        url : 'pembayaran/VerifikasiPembayaran/saveEditJurnal',
                        data : { 'params': params },
                        type : 'POST',
                        dataType : 'JSON',
                        beforeSend : function(){ showLoading() },
                        success : function(data){
                            hideLoading();
                            if ( data.status == 1 ) {
                                bootbox.alert(data.message, function() {
                                    $('.modal').modal('hide');

                                    vp.getLists();
                                });
                            } else {
                                bootbox.alert(data.message);
                            }
                        },
                    });
                }
            });
        }
    }, // end - saveEditJurnal

    save: function(elm) {
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
            $(elm).attr('disabled', 'disabled')
            bootbox.confirm('Apakah anda yakin ingin menyimpan data pembayaran ?', function(result) {
                if ( result ) {

                    var data = {
                        'id': $(elm).attr('data-id'),
                        'tbl_name': $(elm).attr('data-table'),
                        'tgl_bayar': dateSQL($(modal_body).find('#tglBayar').data('DateTimePicker').date()),
                        'no_bukti': $(modal_body).find('.no_bukti').val(),
                        'ket_bayar': $(modal_body).find('.ket_bayar').val()
                    };
        
                    var formData = new FormData();
        
                    // var _file = $('.file_lampiran').get(0).files[0];
                    // formData.append('files', _file);
                    
                    $('.file_lampiran').each(function () {
                        
                        if (this.files.length > 0) {
                            formData.append('files[]', this.files[0]);
                        }
                        
                    });
                    formData.append('data', JSON.stringify(data));

                    
        
                    $.ajax({
                        url : 'pembayaran/VerifikasiPembayaran/save',
                        type : 'post',
                        data : formData,
                        beforeSend : function(){ showLoading() },
                        success : function(data){
                            hideLoading();
                            if ( data.status == 1 ) {
                                bootbox.alert(data.message, function() {
                                    $('.modal').modal('hide');
        
                                    vp.getDataOutstanding();
                                });
                            } else {
                                bootbox.alert(data.message);
                            }
                        },
                        contentType : false,
                        processData : false,
                    });
                } else {
                    $(elm).removeAttr('disabled', 'disabled')
                }
            });
        }
    }, // end - save

    edit: function(elm) {
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
            $(elm).attr('disabled', 'disabled')
            bootbox.confirm('Apakah anda yakin ingin meng-ubah data pembayaran ?', function(result) {
                if ( result ) {

                    let temp_attach = [];

                    $(".file-form").each(function () {
                        let id_file = $(this).attr("id_file");
                        if (id_file) {
                            temp_attach.push({
                                id_file: id_file
                            });
                        }
                    });

                    var data = {
                        'id': $(elm).attr('data-id'),
                        'tbl_name': $(elm).attr('data-table'),
                        'tgl_bayar': dateSQL($(modal_body).find('#tglBayar').data('DateTimePicker').date()),
                        'no_bukti': $(modal_body).find('.no_bukti').val(),
                        'ket_bayar': $(modal_body).find('.ket_bayar').val(),
                        'old_file' : temp_attach,
                    };
        
                    var formData = new FormData();
        
                    // var _file = $('.file_lampiran').get(0).files[0];
                    // formData.append('files', _file);
                    // formData.append('data', JSON.stringify(data));
                    $('.file_lampiran').each(function () {
                        if (this.files.length > 0) {
                            formData.append('files[]', this.files[0]);
                        }
                        
                    });
                    formData.append('data', JSON.stringify(data));
        
                    $.ajax({
                        url : 'pembayaran/VerifikasiPembayaran/edit',
                        type : 'post',
                        data : formData,
                        beforeSend : function(){ showLoading() },
                        success : function(data){
                            hideLoading();
                            if ( data.status == 1 ) {
                                bootbox.alert(data.message, function() {
                                    $('.modal').modal('hide');

                                    vp.getLists();
                                });
                            } else {
                                bootbox.alert(data.message);
                            }

                            if (data.status == 0) {
                                $(".file-form").each(function () {
                                    if (!$(this).attr("id_file")) {
                                        $(this).remove(".file-form");
                                    }
                                });
                            }
                        },
                        contentType : false,
                        processData : false,
                    });
                } else {
                    $(elm).removeAttr('disabled', 'disabled')
                }
            });
        }
    }, // end - edit

    delete: function(elm) {
        bootbox.confirm('Apakah anda yakin ingin meng-hapus data pembayaran ?', function(result) {
            if ( result ) {
                var params = {
                    'id': $(elm).attr('data-id'),
                    'tbl_name': $(elm).attr('data-table'),
                };
    
                $.ajax({
                    url : 'pembayaran/VerifikasiPembayaran/delete',
                    data : { 'params': params },
                    type : 'POST',
                    dataType : 'JSON',
                    beforeSend : function(){ showLoading() },
                    success : function(data){
                        hideLoading();
                        if ( data.status == 1 ) {
                            bootbox.alert(data.message, function() {
                                $('.modal').modal('hide');

                                vp.getLists();
                            });
                        } else {
                            bootbox.alert(data.message);
                        }
                    }
                });
            }
        });
    }, // end - delete

    printPreview: function (elm) {
        var id = $(elm).attr('data-id');
        var table = $(elm).attr('data-table');

        window.open('pembayaran/VerifikasiPembayaran/printPreview/'+id, 'blank');
    }, // end - printPreview


    // Tambahan Hafidz

    addRowLampiran: (elm, e) => {
        e.preventDefault();
        let html = `
            <div class="file-form" style="display:flex; flex-direction:row; gap:5px">
                
                <button type="button" class="name-file-button flex items-center justify-center border border-gray-300 rounded p-1 hover:bg-gray-100" style="width:auto;">
                    Nama File
                </button>
             
                <input type="file" class="file_lampiran" onchange="vp.get_lampiran(this, event)" style="display:none;">
                <button type="button" class="btn btn-sm btn-warning" onclick="vp.edit_lampiran(this, event)">
                    <i class="glyphicon glyphicon-paperclip cursor-p"></i>
                </button>

                <button type="button" onclick="vp.removeRowLampiran(this, event)" class="btn btn-remove btn-sm btn-danger">
                    <i class="fa fa-trash"></i>
                </button>
            </div>
        `;

        $(".attachment-area").append(html);
    },

    removeRowLampiran: (elm, e) => {
        e.preventDefault();

        let rows = $(".file-form");

        // if (rows.length <= 1) {
        //     alert("minimal harus ada 1 baris");
        //     return;
        // }

        $(elm).closest(".file-form").remove();

        // let new_length = $(".file-form").length;

        // vp.first_row(new_length);
    },

    // first_row: (length) => {

    //     if (length == 1) {
    //         $(".file-form:first .btn-remove").html("<i class='fa fa-plus'></i>");
    //         $(".file-form:first .btn-remove").attr("onclick", "vp.addRowLampiran_edit(this, event)");
    //         $(".file-form:first .btn-remove").removeClass("btn-danger").addClass("btn-success");
    //     }

    //     console.log(length);
    // },

    edit_lampiran: (elm, e) => {
        let input = $(elm).closest(".file-form").find(".file_lampiran")[0];
        input.click(); 
        $(elm).closest(".file-form").removeAttr("id_file");
    },

    get_lampiran: (elm, e) =>{
        let file = $(elm)[0].files[0];

        let html = file.name;
        $(elm).closest(".file-form").find(".name-file-button").html(html);
    },

    addRowLampiran_edit:() =>{
        let html =  `<div class="file-form" style="display:flex; flex-direction:row; gap:5px">
                        <a style="text-decoration:none;" href="<?php echo base_url() . 'uploads/'. $file['file_name']; ?>" target="_blank">
                            <button type="button" class="name-file-button flex items-center justify-center border border-gray-300 rounded p-1 hover:bg-gray-100" style="width:auto;">
                                Nama File
                            </button>
                        </a>

                        <input type="file" class="file_lampiran"  onchange="vp.get_lampiran(this, event)" style="display:none;">
                        <button type="button" class="btn btn-sm btn-warning" onclick="vp.edit_lampiran(this, event)">
                            <i class="glyphicon glyphicon-paperclip cursor-p"></i>
                        </button>

                        <button type="button" onclick="vp.removeRowLampiran(this, event)" class="btn btn-remove btn-sm btn-danger">
                            <i class="fa fa-trash"></i>
                        </button>
                    </div>`;

        $(".attachment-area").append(html);
    }

    // end Tambahan Hafidz
};

vp.startUp();