var rdim = {
    start_up : function(){
        rdim.set_table_page('#tb_rencana_doc_in_mingguan');
        rdim.getLists();
        rdim.settingDatepickerPeriode();
    }, // end - start_up

    set_table_page : function(tbl_id){
        let _t_rdim = TUPageTable;
        _t_rdim.destroy();
        _t_rdim.setTableTarget(tbl_id);
        _t_rdim.setPages(['page1', 'page2']);
        _t_rdim.setHideButton(true);
        _t_rdim.onClickNext(function(){
            // console.log('Log onClickNext');
        });
        _t_rdim.start();
    }, // end - set_table_page

    settingDatepickerPeriode: function(resubmit = null){
        $('[name$=Periode]').datetimepicker({
            format: 'DD MMM Y',
            locale: 'id'
        }).on('dp.change', function(e) {
            var _n = $(this).attr('name');
            var start_date = $('#datetimepicker1').data('DateTimePicker').date();

            if (_n == 'startPeriode') {
                $('#datetimepicker2').data('DateTimePicker').minDate(moment(e.date).add(1, 'days'));
                // $('#datetimepicker2').data('DateTimePicker').maxDate(moment(e.date).add(8, 'days'));
                // $('#datetimepicker2').data('DateTimePicker').disabledDates([moment(e.date).add(8, 'days')]);

                // if ( !empty( resubmit ) ) {
                //     var start_periode = $('#datetimepicker1').attr('data-tgl');
                //     $('#datetimepicker1').data('DateTimePicker').date( moment(start_periode) );

                //     var end_periode = $('#datetimepicker2').attr('data-tgl');
                //     $('#datetimepicker2').data('DateTimePicker').date( moment(end_periode) );
                // };
            } else if (_n == 'endPeriode') {
                $('#datetimepicker1').data('DateTimePicker').maxDate(moment(start_date).add(1, 'days'));
            }

            rdim.settingDatepickerInRow(resubmit);
        });

        if ( !empty( resubmit ) ) {
            var start_periode = $('#datetimepicker1').attr('data-tgl');
            $('#datetimepicker1').data('DateTimePicker').date( moment(start_periode) );

            var end_periode = $('#datetimepicker2').attr('data-tgl');
            $('#datetimepicker2').data('DateTimePicker').date( moment(end_periode) );
        }
    }, // end - settingDatepickerPeriode

    settingDatepickerInRow: function(resubmit = null){
        var startPeriode = $('#datetimepicker1').data('DateTimePicker').date();
        var endPeriode = $('#datetimepicker2').data('DateTimePicker').date();
        
        $.map( $('#tb_rencana_doc_in_mingguan tbody tr.child:not(.inactive)'), function (tr) {
            var dp = $(tr).find('div#datetimepicker3');

            $(dp).datetimepicker({
                format: 'DD MMM Y',
                locale:'id',
            });

            if ( !empty( resubmit ) ) {
                var date = $(dp).data('tgl');
                $(dp).data('DateTimePicker').date( moment(date) );
            }

            // $(dp).data('DateTimePicker').minDate( moment(startPeriode) );
            // $(dp).data('DateTimePicker').maxDate( moment(endPeriode) );

            if ( !empty( resubmit ) ) {
                var mitra = $(tr).find('select[name=mitra]');
                rdim.changeMitraRow(mitra, resubmit);
            } 
        } );
    }, // end - settingDatepickerInRow

    changeTabActive: function(elm) {
        var vhref = $(elm).data('href');
        // change tab-menu
        $('.nav-tabs').find('a').removeClass('active');
        $('.nav-tabs').find('a').removeClass('show');
        $('.nav-tabs').find('li a[data-tab='+vhref+']').addClass('show');
        $('.nav-tabs').find('li a[data-tab='+vhref+']').addClass('active');

        // change tab-content
        $('.tab-pane').removeClass('show');
        $('.tab-pane').removeClass('active');
        $('div#'+vhref).addClass('show');
        $('div#'+vhref).addClass('active');

        if ( vhref == 'action' ) {
            var v_id = $(elm).attr('data-id');
            var resubmit = $(elm).attr('data-resubmit');

            rdim.load_form(v_id, resubmit);
        };
    }, // end - changeTabActive

    load_form: function(v_id = null, resubmit = null) {
        var div_action = $('div#action');

        $.ajax({
            url : 'transaksi/Rdim/load_form',
            data : {
                'id' :  v_id,
                'resubmit' : resubmit
            },
            type : 'GET',
            dataType : 'HTML',
            beforeSend : function(){showLoading();},
            success : function(html){
                $(div_action).html(html);

                $('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal], [data-tipe=decimal3],[data-tipe=decimal4], [data-tipe=number]').each(function(){
                    $(this).priceFormat(Config[$(this).data('tipe')]);
                });

                rdim.set_table_page('#tb_rencana_doc_in_mingguan');
                rdim.settingDatepickerPeriode(resubmit);

                hideLoading();
            },
        });
    }, // end - load_form

    addFirstChild: function(elm) {
        let row = $(elm).closest('tr');
        $(row).next('tr.child').find('input, select').val('');
        $(row).next('tr.child').removeClass('inactive');
        $(row).next('tr.child').find('td.kecamatan, td.kabupaten').html('');
        $(elm).closest('div').hide();

        rdim.settingDatepickerInRow();
        App.formatNumber();
    }, // end - addFirstChild

    addRowChild: function(elm) {
        let row = $(elm).closest('tr');
        let newRow = row.clone();
        // newRow.find('[name=tanggal]').val('');

        newRow.find('input, select').val('');
        newRow.find('td.kecamatan, td.kabupaten').html('');
        row.find('.btn-ctrl').hide();
        row.after(newRow);

        rdim.settingDatepickerInRow();

        newRow.find('#datetimepicker3').data('DateTimePicker').date(null);

        App.formatNumber();
    }, // end - addRowChild

    removeRowChild: function(elm) {
        let row = $(elm).closest('tr');
        if ($(row).prev('tr.child').length > 0) {
            $(row).prev('tr').find('.btn-ctrl').show();
            $(row).remove();
        }else{
            $(row).prev('tr').find('.btn-ctrl').show();
            $(row).addClass('inactive');
        }
    }, // end - removeRowChild

    changeMitraRow: function (elm, resubmit = null) {
        let row = $(elm).closest('tr');

        let dataOld = $(row).attr('data-old');
        let dataKandangs = $(elm).find('option:selected').attr('data-kandangs');
        let eSelectKandang = row.find('select[name=kandang]');
        let eTglDocin = row.find('div#datetimepicker3');
        let jenis = $(elm).find('option:selected').attr('data-jenis');

        /** reset value ketika mitra diubah */
        // $(row).find('input:not([name=tanggal]), select:not([name=mitra])').val('');
        // $(row).find('td.kecamatan, td.kabupaten').html('');
        $(row).find('input[name=jenis]').val(jenis);

        var no_kdg = null;
        if ( !empty(resubmit) ) {
            no_kdg = $(eSelectKandang).data('kdg');
            // $(row).find('input:not([name=tanggal], [name=populasi]), select:not([name=mitra])').val('');
        } else {
            $(row).find('input:not([name=tanggal]), select:not([name=mitra])').val('');
        };

        eTglDocin.unbind('dp.change');

        eSelectKandang.html('<option value="">-</option>');
        eSelectKandang.unbind('change.pilihKandang');

        if (! empty(dataKandangs)) {
            let jsonObjectKandangs = JSON.parse(dataKandangs);

            for (var i = 0; i < jsonObjectKandangs.length; i++) {

                var selected = null;
                let kandang = jsonObjectKandangs[i];
                if ( no_kdg == parseInt(kandang.nomor) ) {
                    selected = 'selected';
                };

                row.find('select[name=kandang]').append('<option value="'+ kandang.id +'" '+selected+' >'+ kandang.nomor +'</option>');
            }

            $(eTglDocin).bind('dp.change', function(){
                if ( !empty(resubmit) ) {
                    // $(row).find('input:not([name=tanggal], [name=jenis], [name=populasi]), select:not([name=mitra], [name=kandang])').val('');
                } else {
                    $(row).find('input:not([name=tanggal], [name=jenis]), select:not([name=mitra], [name=kandang])').val('');
                }

                // $(row).find('td.kecamatan, td.kabupaten').html('');
                let i = $(row).find('select[name=kandang] option:selected').index();
                let idKandang = $(row).find('select[name=kandang]').val();
                if (i > 0) {
                    let kandang = jsonObjectKandangs[--i];

                    // NOTE: cek apakah kandang sudah dipilih sebelumnya
                    let row_prev = row.prevUntil('tr.parent');
                    let row_next = row.nextUntil('tr.parent');
                    let rows = $.merge(row_prev, row_next);
                    for (var x = 0; x < rows.length; x++) {
                        let tempId = $(rows[x]).find('select[name=kandang]').val();
                        if (idKandang == tempId) {
                            $(this).val('');
                            toastr.error('Kandang sudah dipilih sebelumnya', 'Kandang tidak boleh sama!');

                            return false;
                        }
                    }

                    var tgl_docin = dateSQL( $(row).find('div#datetimepicker3').data('DateTimePicker').date() );

                    if ( !empty($(row).find('div#datetimepicker3 input').val()) ) {
                        rdim.getDataKandangMitraRDIM(row, tgl_docin, kandang, jenis, dataOld);
                    }
                }
            });

            eSelectKandang.bind('change.pilihKandang', function(evt){
                if ( !empty(resubmit) ) {
                    // $(row).find('input:not([name=tanggal], [name=jenis], [name=populasi]), select:not([name=mitra], [name=kandang])').val('');
                } else {
                    $(row).find('input:not([name=tanggal], [name=jenis]), select:not([name=mitra], [name=kandang])').val('');
                }

                // $(row).find('td.kecamatan, td.kabupaten').html('');
                let i = $(this).find('option:selected').index();
                let idKandang = $(this).val();

                if (i > 0) {
                    let kandang = jsonObjectKandangs[--i];

                    // NOTE: cek apakah kandang sudah dipilih sebelumnya
                    let row_prev = row.prevUntil('tr.parent');
                    let row_next = row.nextUntil('tr.parent');
                    let rows = $.merge(row_prev, row_next);
                    for (var x = 0; x < rows.length; x++) {
                        let tempId = $(rows[x]).find('select[name=kandang]').val();
                        if (idKandang == tempId) {
                            $(this).val('');
                            toastr.error('Kandang sudah dipilih sebelumnya', 'Kandang tidak boleh sama!');

                            return false;
                        }
                    }

                    var tgl_docin = dateSQL( $(row).find('div#datetimepicker3').data('DateTimePicker').date() );

                    if ( !empty($(row).find('div#datetimepicker3 input').val()) ) {
                        rdim.getDataKandangMitraRDIM(row, tgl_docin, kandang, jenis, dataOld);
                    }
                }
            });
        }else{

        }

        // if ( !empty(resubmit) ) {
        //     $(eSelectKandang).change();
        // };
    }, // end - changeMitraRow

    getDataKandangMitraRDIM: function(row, tgl_docin, kandang, jenis, dataOld = null) {
        console.log( kandang );

        $.ajax({
            url: 'transaksi/Rdim/getDataKandangMitraRDIM',
            data: { 
                'tgl_docin': tgl_docin, 
                'nim': kandang.nim, 
                'kandang': kandang.nomor,
                'data_old': dataOld
            },
            type: 'GET',
            dataType: 'JSON',
            beforeSend: function(){ showLoading() },
            success: function(data){
                hideLoading();
                if (data.status) {
                    row.find('td input[name=ip_terakhir_1]').val(data.content.ip1);
                    row.find('td input[name=ip_terakhir_2]').val(data.content.ip2);
                    row.find('td input[name=ip_terakhir_3]').val(data.content.ip3);
                    row.find('td input[name=jenis]').val(jenis);
                    row.find('td.kecamatan').html(kandang.kecamatan);
                    row.find('td.kabupaten').html(kandang.kabupaten);
                    row.find('td input[name=noreg]').val(data.content.next_noreg);
                    row.find('td input[name=noreg]').attr('data-nim', kandang.nim);

                    // console.log('coba');
                    // console.log(kandang.group);
                    // console.log(row.find('td input[name=group]').length);

                    row.find('td input[name=group]').val(kandang._group);
                    row.find('input[name=kapasitas_kandang]').val( numeral.formatInt(kandang.kapasitas) );
                    row.find('input[name=tipe_densitas]').val( kandang.tipe + '-' + numeral.formatDec(kandang.densitas) );
                }else{
                    alertDialog(data.message);
                }
            }
        });
    }, // end - getDataKandangMitraRDIM

    /**
    // NOTE: not use
    changeFormatPb: function(elm) {
        let row = $(elm).closest('tr');
        let pola = $(elm).find('option:selected').data('pola');
        row.find('input[name=pola]').val(pola);
    }, // end - changeFormatPb
    */

    checkBatasPopulasi: function(elm) {
        let row = $(elm).closest('tr');
        let batas_populasi = numeral.unformat(row.find('input[name=kapasitas_kandang]').val());
        let populasi = numeral.unformat($(elm).val());
        if (batas_populasi <= 0) {
            toastr.warning('Tentukan kandang mitra terlebih dulu.', 'Warning!');
        $(elm).val('');
        }else if (populasi > batas_populasi) {
            toastr.error('Populasi tidak boleh lebih dari kapasitas kandang : ' + batas_populasi, 'Populasi '+populasi+' melebihi kapasitas!');
            $(elm).val( numeral.formatInt(batas_populasi) );
        }
    }, // end - checkBatasPopulasi

    save: function() {
        // NOTE: collect data
        let $tbl = $('#tb_rencana_doc_in_mingguan');
        let $rows = $tbl.find('tbody tr.child:not(tr.inactive)');
        let input_error = 0;
        $('input, select').parent().removeClass('has-error');
        let params = {};
        let datas = $.map( $rows, function(row) {

            let data = {
                'nim': $(row).find('input[name=noreg]').attr('data-nim'),
                'pola': $(row).find('select[name=formatPb] option:selected').data('pola'),
                'perusahaan': $(row).find('select[name=formatPb] option:selected').data('perusahaan'),
            };

            $.map( $(row).find('input, select'), function(elm) {
                if ( !empty($(elm).val()) ) {
                    data[$(elm).attr('name')] = getValueOf(elm);

                    if ( $(elm).closest('div#datetimepicker3').hasClass('date') ) {
                        data[$(elm).attr('name')] = dateSQL( $(elm).closest('div#datetimepicker3').data('DateTimePicker').date() );
                    };
                } else if ( $(elm).hasClass('no-check') ) {
                    data[$(elm).attr('name')] = getValueOf(elm);
                } else {
                    input_error++;
                    $(elm).parent().addClass('has-error');
                }
            });

            return data;
        });

        if ( empty($('#datetimepicker1 input').val()) ) {
            $('#datetimepicker1').parent().addClass('has-error');
            input_error++;
        }

        if ( empty($('#datetimepicker2 input').val()) ) {
            $('#datetimepicker2').parent().addClass('has-error');
            input_error++;
        }

        params['periode'] = {
            'start' : dateSQL($('#datetimepicker1').data('DateTimePicker').date()),
            'end' : dateSQL($('#datetimepicker2').data('DateTimePicker').date())
        };
        params['details'] = datas;


        if (input_error > 0) {
            toastr.error('Mohon periksa kembali kelengkapan data yang akan disimpan.', 'Data bermasalah!')
        }else{
            App.confirmDialog('Simpan Rencana Chick In Mingguan', function(result) {
                if (result) {
                    console.log(params);
                    rdim.execute_save(params);
                }
            });
        }
        // console.log('input_error : ' + input_error ,datas, $rows);
    }, // end - save

    execute_save: function(params) {
        // if (! isSubmitting) {
        //     isSubmitting = true;
            $.ajax({
                url: 'transaksi/Rdim/saveRdim',
                data: {'params': params },
                type: 'POST',
                dataType: 'JSON',
                beforeSend: function(){showLoading()},
                success: function(data){
                    hideLoading();
                    if (data.status == 1) {
                        rdim.getLists();
                        rdim.load_form(data.content.id);
                        // $('#rencana_doc_in_mingguan').html(data.content);
                        // rdim.getLists();
                        bootbox.alert(data.message);
                    }else{
                        alertDialog(data.message);
                    }
                }
            });
        // }
    }, // end - execute_save

    edit: function() {
        // NOTE: collect data
        let $tbl = $('#tb_rencana_doc_in_mingguan');
        let $rows = $tbl.find('tbody tr.child:not(tr.inactive)');
        let input_error = 0;
        $('input, select').parent().removeClass('has-error');
        let params = {};
        let datas = $.map( $rows, function(row) {

            let data = {
                'nim': $(row).find('input[name=noreg]').attr('data-nim'),
                'pola': $(row).find('select[name=formatPb] option:selected').data('pola'),
                'perusahaan': $(row).find('select[name=formatPb] option:selected').data('perusahaan'),
            };

            $.map( $(row).find('input, select'), function(elm) {
                if ( !empty($(elm).val()) ) {
                    data[$(elm).attr('name')] = getValueOf(elm);

                    if ( $(elm).closest('div#datetimepicker3').hasClass('date') ) {
                        data[$(elm).attr('name')] = dateSQL( $(elm).closest('div#datetimepicker3').data('DateTimePicker').date() );
                    };
                } else if ( $(elm).hasClass('no-check') ) {
                    data[$(elm).attr('name')] = getValueOf(elm);
                } else {
                    input_error++;
                    $(elm).parent().addClass('has-error');
                }
            });

            return data;
        });

        if ( empty($('#datetimepicker1 input').val()) ) {
            $('#datetimepicker1').parent().addClass('has-error');
            input_error++;
        }

        if ( empty($('#datetimepicker2 input').val()) ) {
            $('#datetimepicker2').parent().addClass('has-error');
            input_error++;
        }

        params['id'] = $('input[type=hidden]').data('id');
        params['periode'] = {
            'start' : dateSQL($('#datetimepicker1').data('DateTimePicker').date()),
            'end' : dateSQL($('#datetimepicker2').data('DateTimePicker').date())
        };
        params['details'] = datas;

        if (input_error > 0) {
            // toastr.error('Mohon periksa kembali kelengkapan data yang akan diubah.', 'Data bermasalah!');
            bootbox.alert('Mohon periksa kembali kelengkapan data yang akan diubah.');
        }else{
            App.confirmDialog('Ubah Rencana Chick In Mingguan', function(result) {
                if (result) {
                    // console.log(params);
                    rdim.execute_edit(params);
                }
            });
        }
        // console.log('input_error : ' + input_error ,datas, $rows);
    }, // end - edit

    execute_edit: function(params) {
        // if (! isSubmitting) {
        //     isSubmitting = true;
            $.ajax({
                url: 'transaksi/Rdim/editRdim',
                data: {'params': params },
                type: 'POST',
                dataType: 'JSON',
                beforeSend: function(){showLoading()},
                success: function(data){
                    hideLoading();
                    if (data.status == 1) {
                        rdim.getLists();
                        rdim.load_form(data.content.id);
                        // $('#rencana_doc_in_mingguan').html(data.content);
                        // rdim.getLists();
                        bootbox.alert(data.message);
                    }else{
                        alertDialog(data.message);
                    }
                }
            });
        // }
    }, // end - execute_edit

    getLists : function(keyword = null){
        $.ajax({
            url : 'transaksi/Rdim/list_rdim',
            data : {'keyword' : keyword},
            dataType : 'HTML',
            type : 'GET',
            beforeSend : function(){},
            success : function(data){
                $('table.tbl_rdim tbody').html(data);
            }
        });
    }, // end - getLists

    viewDetail: function(elm) {
        const dcontent = $('#rencana_doc_in_mingguan');
        const action = $(elm).attr('data-action');
        let id = $(elm).data('id');
        changeTabActivity(elm, function() {
            $.ajax({
                url: 'transaksi/Rdim/loadContentRdim',
                data: {'id': id, 'action': action},
                dataType: 'HTML',
                type: 'GET',
                beforeSend: function() { App.showLoaderInContent(dcontent); },
                success: function(data){
                    App.hideLoaderInContent(dcontent, data);
                    rdim.set_table_page('[name=tb_rencana_doc_in_mingguan]');
                },
            });
        });
    }, // viewDetail

    addNewRdim: function(elm) {
        const dcontent = $('#rencana_doc_in_mingguan');
        changeTabActivity(elm, function() {
            $.ajax({
                url: 'transaksi/Rdim/addNewRdim',
                dataType: 'HTML',
                type: 'GET',
                beforeSend: function() { App.showLoaderInContent(dcontent); },
                success: function(data){
                    App.hideLoaderInContent(dcontent, data);
                    rdim.set_table_page('[name=tb_rencana_doc_in_mingguan]');
                    rdim.settingDatepickerPeriode();
                },
            });
        });
    }, // end - viewDetail

    ack : function (elm) {
        var action = $(elm).attr('data-action');
        var _id = $(elm).attr('data-id');

        var params = {
            'action' : action,
            'id' : _id,
        };

        App.confirmDialog("Apakah anda yakin ingin ACK data ?", function(confirm){
            if (confirm) {
                $.ajax({
                    url : 'transaksi/Rdim/ack',
                    data : {'params' :  params},
                    type : 'POST',
                    dataType : 'JSON',
                    beforeSend : function(){
                        showLoading();
                    },
                    success : function(data){
                        if (data.status) {
                            bootbox.alert(data.message, function(){
                                rdim.getLists();
                                rdim.load_form(data.content.id);
                            });
                        }else{
                            alertDialog(data.message);
                        }
                    },
                });
            }
        });

    }, // end - ack

    approveReject : function(elm){

        var action = $(elm).attr('data-action');
        var _id = $(elm).attr('data-id');

        var params = {
            'action' : action,
            'id' : _id,
        };

        if (action == 'approve') {
            App.confirmDialog("Apakah anda yakin ingin Approve data ?", function(confirm){
                if (confirm) {
                    rdim.executeApproveReject(params);
                }
            });
        }
        else if (action == 'reject') {
            App.confirmRejectDialog("reject", function(text){
                if (text.length > 0) {
                    params['alasan_tolak'] = text;
                    rdim.executeApproveReject(params);
                }
            });
        }
    }, // end - approveReject

    executeApproveReject : function (params) {
        $.ajax({
            url : 'transaksi/Rdim/approveReject',
            data : {'params' :  params},
            type : 'POST',
            dataType : 'JSON',
            beforeSend : function(){
                showLoading();
            },
            success : function(data){
                if (data.status) {
                    bootbox.alert(data.message, function(){
                        rdim.getLists();
                        rdim.load_form(data.content.id);
                    });
                }else{
                    alertDialog(data.message);
                }
            },
        });
    }, // end - executeApproveReject

    getDataPembatalanRdim: function (elm) {
        let idrdim = $(elm).val();
        $.ajax({
            url: 'transaksi/Rdim/getDataPembatalanRdim',
            data: {'id_rdim': idrdim},
            dataType: 'HTML',
            type: 'GET',
            beforeSend: function(){ showLoading() },
            success: function( data ){
                $('#tb_pembatalan_rdim tbody').html(data);
                App.setBindSHA1();

                hideLoading();
            }
        });
    }, // end - getDataPembatalanRdim

    editRowPembatalanRdim: function(elm){
        const row = $(elm).closest('tr');
        let isEdit = row.attr('isEdit');
        if (isEdit == 1) {
            row.attr('isEdit', 0);
            let fd = row.find('input:file').parent('label').prev('span').remove();
            row.find('textarea, input').val('').prop('disabled', true);
            $(elm).find('i').removeClass('fa-close').addClass('fa-edit');
        }else{
            row.attr('isEdit', 1);
            row.find('textarea, input').val('').prop('disabled', false);
            $(elm).find('i').removeClass('fa-edit').addClass('fa-close');
        }
    }, // end -  editRowPembatalanRdim

    savePembatalanRdim: function(){
        // NOTE: row yang diedit
        const rows = $('#tb_pembatalan_rdim tbody tr[isEdit=1]');
        let formData = new FormData();
        let error_msg = '';
        let files = [];

        rows.find('input, textarea').parent().removeClass('has-error');
        let datas = $.map(rows, function(row){

            let eFile = $(row).find('input:file');
            let ket_alasan = $(row).find('[name=ket_alasan]').val();
            if (empty(eFile.val()) || empty(ket_alasan)) {
                if ( empty(eFile.val()) ) {
                    error_msg += 'Dokumen pembatalan belum terpenuhi, ';
                }

                if ( empty(ket_alasan) ) {
                    $(row).find('[name=ket_alasan]').parent().addClass('has-error');
                    error_msg += ' Keterangan alasan tidak boleh kosong.';
                }
            }else{
                var __file = eFile.get(0).files[0];
                formData.append('files[]', __file);
                data = {
                    'id_rs' : $(row).attr('data-id'),
                    'filename' : __file.name,
                    'sha1' : eFile.attr('data-sha1'),
                    'ket_alasan' : ket_alasan,
                };
                return data;
            }
        });

        if ( !empty(error_msg) || empty(datas) ) {
            error_msg += ' Mohon dicek kembali.';
            toastr.error(error_msg, 'Data belum lengkap!');
        }else {

            formData.append('data_rs', JSON.stringify(datas));
            $.ajax({
                url :'transaksi/Rdim/savePembatalanRdim',
                type : 'post',
                data : formData,
                beforeSend : function(){
                    showLoading();
                },
                success : function(data){
                    hideLoading();
                    if(data.status){
                        bootbox.alert(data.message,function(){
                            $('#pembatalan_doc_in').find('select[name=periode] option:selected').remove();
                            $('#pembatalan_doc_in').find('select[name=periode]').change();
                        });
                    }else{
                        bootbox.alert(data.message);
                    }
                },
                contentType : false,
                processData : false,
            });
        }

    }, // end - savePembatalanRdim

    formPenanggungJawabKandang: function (elm) {
        showLoading('Buka form penanggung jawab . . .');

        var id = $(elm).attr('data-id');

        var params = {'id': id};

        $.get('transaksi/Rdim/formPenanggungJawabKandang',{
                'params': params
            },function(data){
            hideLoading();

            var _options = {
                className : 'veryWidth',
                message : data,
                size : 'large',
            };
            bootbox.dialog(_options).bind('shown.bs.modal', function(){
                $(this).removeAttr('tabindex');

                var modal_dialog = $(this).find('.modal-dialog');
                $(modal_dialog).css({'max-width' : '70%'});
                $(modal_dialog).css({'width' : '70%'});

                var modal_header = $(this).find('.modal-header');
                $(modal_header).css({'padding-top' : '0px'});

                var modal_body = $(this).find('.modal-body');

                $(modal_body).find('select.ppl').select2();
                $(modal_body).find('select.kanit').select2();
                $(modal_body).find('select.marketing').select2();
                $(modal_body).find('select.korwil').select2();
            });
        },'html');
    }, // end - formPenanggungJawabKandang

    cekForm: function (elm) {
        var modal = $('.modal');

        var tujuan = $(elm).attr('data-tujuan');

        if ( tujuan == 'edit' ) {
            $(modal).find('div.edit').addClass('hide');
            $(modal).find('div.not-edit').removeClass('hide');

            $(modal).find('select.ppl').removeAttr('disabled');
            $(modal).find('select.kanit').removeAttr('disabled');
            $(modal).find('select.marketing').removeAttr('disabled');
            $(modal).find('select.korwil').removeAttr('disabled');

            $(modal).find('select.ppl').select2();
            $(modal).find('select.kanit').select2();
            $(modal).find('select.marketing').select2();
            $(modal).find('select.korwil').select2();
        } else {
            $(modal).find('div.edit').removeClass('hide');
            $(modal).find('div.not-edit').addClass('hide');

            $(modal).find('select.ppl').attr('disabled', 'disabled');
            $(modal).find('select.kanit').attr('disabled', 'disabled');
            $(modal).find('select.marketing').attr('disabled', 'disabled');
            $(modal).find('select.korwil').attr('disabled', 'disabled');

            $(modal).find('select.ppl').select2();
            $(modal).find('select.kanit').select2();
            $(modal).find('select.marketing').select2();
            $(modal).find('select.korwil').select2();

            var val_ppl = $(modal).find('select.ppl').attr('data-val');
            var val_kanit = $(modal).find('select.kanit').attr('data-val');
            var val_marketing = $(modal).find('select.marketing').attr('data-val');
            var val_korwil = $(modal).find('select.korwil').attr('data-val');

            $(modal).find('select.ppl').val(val_ppl);
            $(modal).find('select.kanit').val(val_kanit);
            $(modal).find('select.marketing').val(val_marketing);
            $(modal).find('select.korwil').val(val_korwil);

            $(modal).find('select.ppl').trigger('change');
            $(modal).find('select.kanit').trigger('change');
            $(modal).find('select.marketing').trigger('change');
            $(modal).find('select.korwil').trigger('change');
        }
    }, // end - cekForm

    editPenanggungJawab: function (elm) {
        var modal = $('.modal');

        var err = 0
        $.map( $(modal).find('[data-required=1]'), function (ipt) {
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
            bootbox.confirm('Apakah anda yakin ingin meng-ubah penanggung jawab kandang ?', function (result) {
                if ( result ) {
                    var params = {
                        'id': $(elm).attr('data-id'),
                        'ppl': $(modal).find('select.ppl').select2('val'),
                        'kanit': $(modal).find('select.kanit').select2('val'),
                        'marketing': $(modal).find('select.marketing').select2('val'),
                        'korwil': $(modal).find('select.korwil').select2('val')
                    };

                    $.ajax({
                        url :'transaksi/Rdim/editPenanggungJawab',
                        data : {
                            'params': params
                        },
                        type: 'POST',
                        dataType: 'JSON',
                        beforeSend : function(){
                            showLoading('Proses merubah penanggung jawab . . .');
                        },
                        success : function(data){
                            hideLoading();
                            if(data.status){
                                bootbox.alert(data.message, function (){
                                    $(modal).modal('hide');
                                    rdim.load_form(data.content.id_rdim, null);
                                });
                            }else{
                                bootbox.alert(data.message);
                            }
                        }
                    });
                }
            });
        }
    }, // end - editPenanggungJawab
};

rdim.start_up();
