var fdvp = {
	start_up_feed : function () {
        fdvp.getLists_Feed();
    }, // end - start_up_feed

    start_up_doc : function () {
        fdvp.getLists_Doc();
    }, // end - start_up_doc

    start_up_voadip : function () {
        fdvp.getLists_Voadip();
    }, // end - start_up_voadip

    start_up_peralatan : function () {
        fdvp.getLists_Peralatan();
    }, // end - start_up_peralatan

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

    row_add: function(elm) {
    	var href = $(elm).data('href');
        var tab_pane = $(elm).closest('div.tab-pane');
    	var div_search = $(tab_pane).find('div.search');
    	var table = $(tab_pane).find('table.table');
    	var tbody = $(table).find('tbody');

        var tr_empty = $(tbody).find('tr.empty');
    	var tr_first = $(tbody).find('tr:first');

    	$.ajax({
            url : 'parameter/FDVP/add_form',
            data : {
                'jenis' :  href,
            },
            type : 'GET',
            dataType : 'HTML',
            beforeSend : function(){},
            success : function(html){
                if ( $(tr_empty).length > 0 ) {
		    		$(tbody).html(html);
		    	} else {
                    $(html).insertBefore(tr_first);
                };

                fdvp.hideHeader( $(elm) );

                $('.supplier').select2();
                $('.supplier').next('span.select2').css('width', '100%');

                $('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal], [data-tipe=decimal3],[data-tipe=decimal4], [data-tipe=number]').each(function(){
                    $(this).priceFormat(Config[$(this).data('tipe')]);
                });

                // NOTE: FOR MULTISELECT
                // $('.supplier').multiselect({
                //     includeSelectAllOption: true,
                //     enableCaseInsensitiveFiltering: true,
                //     maxHeight: 400,
                //     nonSelectedText: '- Pilih Supplier - ',
                //     buttonWidth: '200px',
                //     numberDisplayed: 2,
                // });

                fdvp.setBindSHA1();
            },
        });
    }, // end - changeTabActive

    addRowChild: function(elm) {
        let row = $(elm).closest('tr');
        let tbody = $(row).closest('tbody');
        let table = $(tbody).closest('table');

        // NOTE: FOR SELECT2
        row.find('select.supplier').select2('destroy')
                                   .removeAttr('data-live-search')
                                   .removeAttr('data-select2-id')
                                   .removeAttr('aria-hidden')
                                   .removeAttr('tabindex');

        // NOTE: FOR SELECT2
        row.find('select.supplier option').removeAttr('data-select2-id');

        let newRow = row.clone();

        // NOTE: FOR MULTISELECT
        // var rowSelSupl = row.find('select.supplier').clone();

        newRow.find('input').val('');
        newRow.find('select:not(select.supplier) option.empty').attr('selected', 'selected');

        // NOTE: FOR MULTISELECT
        // var newRowTdSelSupl = newRow.find('select.supplier').closest('td');
        // newRowTdSelSupl.empty();

        // newRowTdSelSupl.html( rowSelSupl );

        // newRow.find('.supplier').select2();
        // newRow.find('.supplier').next('span.select2').css('width', '100%');


        // var newRowTdSelSupl = newRow.find('select.supplier').closest('td');
        // newRowTdSelSupl.empty();

        // newRowTdSelSupl.html(rowSelSupl);

        // newRowTdSelSupl.find('.supplier').multiselect({
        //     includeSelectAllOption: true,
        //     enableCaseInsensitiveFiltering: true,
        //     maxHeight: 400,
        //     nonSelectedText: '- Pilih Supplier - ',
        //     buttonWidth: '200px',
        //     numberDisplayed: 2,
        // });

        row.find('.btn-ctrl').hide();
        row.before(newRow);

        $('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal], [data-tipe=decimal3],[data-tipe=decimal4], [data-tipe=number]').each(function(){
            $(this).priceFormat(Config[$(this).data('tipe')]);
        });

        // NOTE: FOR SELECT2
        $('select.supplier').select2();
        $('.supplier').next('span.select2').css('width', '100%');
    }, // end - addRowChild

    removeRowChild: function(elm) {
        var tab_pane = $(elm).closest('div.tab-pane');
        var table = $(tab_pane).find('table.table');

        let row = $(elm).closest('tr');
        var href = $(tab_pane).attr('id');

        if ($(table).find('tr.row_data').length > 1) {
            $(row).next('tr').find('.btn-ctrl').show();
            $(row).remove();
        }else{
            fdvp.showHeader( $(elm) );

            $(row).remove();

            if ( href == 'feed' ) { fdvp.getLists_Feed(); };
            if ( href == 'doc' ) { fdvp.getLists_Doc(); };
            if ( href == 'voadip' ) { fdvp.getLists_Voadip(); };
            if ( href == 'peralatan' ) { fdvp.getLists_Peralatan(); };
        }
    }, // end - removeRowChild

    hideHeader: function (elm) {
        var tab_pane = $(elm).closest('div.tab-pane');
        var div_search = $(tab_pane).find('div.search');
        var table = $(tab_pane).find('table.table');

        $(table).css('width', '97.5%');
        $(div_search).find('input, i').addClass('hide');
        $(elm).addClass('hide');
        $(elm).next('button').removeClass('hide');
    }, // end - hideHeader

    showHeader: function (elm) {
        var tab_pane = $(elm).closest('div.tab-pane');
        var table = $(tab_pane).find('table.table');

        var div_search = $(tab_pane).find('div.search');
        var div_action = $(tab_pane).find('div.action');
        var button_add = $(div_action).find('button#btn-add');

        $(button_add).next('button').addClass('hide');
        $(div_action).find('button#btn-edit').addClass('hide');

        $(table).css('width', '100%');
        $(div_search).find('input, i').removeClass('hide');
        $(button_add).removeClass('hide');
    }, // end - showHeader

    edit_form : function(elm) {
        var tab_pane = $(elm).closest('div.tab-pane');
        var div_search = $(tab_pane).find('div.search');

        var tr = $(elm).closest('tr');
        var tr_edit = $(tr).next('tr.edit');

        $(tr).addClass('hide');

        $(tr_edit).attr('data-aktif', 'aktif');
        $(tr_edit).removeClass('hide');
        $(tr_edit).find('input').removeClass('hide');
        $(tr_edit).find('.supplier').select2();
        $(tr_edit).find('.supplier').next('span.select2').css('width', '100%');

        $(tr_edit).find('.supplier').val('19A011').trigger('change');

        $(div_search).find('input, i').addClass('hide');
        $(tab_pane).find('button#btn-add').addClass('hide');
        $(tab_pane).find('button#btn-edit').removeClass('hide');

        $('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal], [data-tipe=decimal3],[data-tipe=decimal4], [data-tipe=number]').each(function(){
            $(this).priceFormat(Config[$(this).data('tipe')]);
        });
    }, // end - edit_form

    cancel_edit : function(elm) {
        var tab_pane = $(elm).closest('div.tab-pane');
        var dic_action = $(tab_pane).find('div.action');
        var div_search = $(tab_pane).find('div.search');
        var table = $(tab_pane).find('table.table');

        var tr = $(elm).closest('tr');
        var tr_head = $(tr).prev('tr.head');

        $(tr).addClass('hide');
        $(tr).attr('data-aktif', 'n_aktif');

        $(tr_head).removeClass('hide');

        var edit_len = $(table).find('tr[data-aktif=aktif]').length;
        if ( edit_len == 0 ) {
            $(div_search).find('input, i').removeClass('hide');
            $(tab_pane).find('button#btn-add').removeClass('hide');
            $(dic_action).find('button#btn-edit').addClass('hide');
        };


    }, // end - cancel_edit

    load_form: function(v_id = null, vhref = null, resubmit = null) {
        var div_action = $('div#' + vhref);

        $.ajax({
            url : 'parameter/FDVP/load_form',
            data : {
                'id' :  v_id,
                'resubmit' : resubmit,
                'href' : vhref
            },
            type : 'GET',
            dataType : 'HTML',
            beforeSend : function(){},
            success : function(html){
                $(div_action).html(html);

                $('#datetimepicker1').datetimepicker({
                    locale: 'id',
                    format: 'DD MMM Y'
                });

                $('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal], [data-tipe=decimal3],[data-tipe=decimal4], [data-tipe=number]').each(function(){
                    $(this).priceFormat(Config[$(this).data('tipe')]);
                });

                fdvp.setBindSHA1();
            },
        });
    }, // end - load_form

    getLists_Feed : function(keyword = null){
        $.ajax({
            url : 'parameter/FDVP/list_feed',
            data : {'keyword' : keyword},
            dataType : 'HTML',
            type : 'GET',
            beforeSend : function(){},
            success : function(data){
                $('table.tbl_feed tbody').html(data);
            }
        });
    }, // end - getLists_Feed

    getLists_Doc : function(keyword = null){
        $.ajax({
            url : 'parameter/FDVP/list_doc',
            data : {'keyword' : keyword},
            dataType : 'HTML',
            type : 'GET',
            beforeSend : function(){},
            success : function(data){
                $('table.tbl_doc tbody').html(data);
            }
        });
    }, // end - getLists_Doc

    getLists_Voadip : function(keyword = null){
        $.ajax({
            url : 'parameter/FDVP/list_voadip',
            data : {'keyword' : keyword},
            dataType : 'HTML',
            type : 'GET',
            beforeSend : function(){},
            success : function(data){
                $('table.tbl_voadip tbody').html(data);
            }
        });
    }, // end - getLists_Voadip

    getLists_Peralatan : function(keyword = null){
        $.ajax({
            url : 'parameter/FDVP/list_peralatan',
            data : {'keyword' : keyword},
            dataType : 'HTML',
            type : 'GET',
            beforeSend : function(){},
            success : function(data){
                $('table.tbl_peralatan tbody').html(data);
            }
        });
    }, // end - getLists_Peralatan

    save_feed : function (elm) {
        var table = $('table.tbl_feed');
        var tbody = $(table).find('tbody');

        var err = 0;

        $.map( $(tbody).find('tr.row_data'), function(tr) {
            $.map( $(tr).find('input[data-required=1], select[data-required=1]'), function(ipt) {
                if ( empty( $(ipt).val() ) ) {
                    $(ipt).closest('td').addClass('has-error');
                    err++;
                } else {
                    $(ipt).closest('td').removeClass('has-error');
                };
            });
        });

        if ( err > 0 ) {
            bootbox.alert('Data belum lengkap, mohon cek kembali data yang anda masukkan.');
        } else {
            bootbox.confirm('Apakah anda yakin ingin menyimpan data pakan ?', function(result) {
                if ( result ) {
                    var row_data = $.map( $('tr.row_data'), function(tr) {
                        var data = {
                            'kategori' : $(tr).find('select#kategori').val(),
                            'nama_pakan' : $(tr).find('input#nama_pakan').val(),
                            'kode_item' : $(tr).find('input#kode_item_sup').val(),
                            'supl' : $(tr).find('.supplier').val(),
                            'umur' : numeral.unformat( $(tr).find('input#umur').val() ),
                            'berat_pakan' : numeral.unformat( $(tr).find('input#berat_pakan').val() ),
                            'bentuk_pakan' : $(tr).find('select#bentuk_pakan').val(),
                            'masa_simpan' : numeral.unformat( $(tr).find('input#masa_simpan').val() ),
                        };

                        return data;
                    })

                    fdvp.exec_save_feed(row_data, elm);
                };
            });
        };
    }, // end - save_feed

    exec_save_feed : function(params, elm){
        $.ajax({
            url : 'parameter/FDVP/save_feed',
            data : {
                'params' : params
            },
            dataType : 'JSON',
            type : 'POST',
            beforeSend : function(){
                showLoading();
            },
            success : function(data){
                hideLoading();

                if ( data.status == 1 ) {
                    bootbox.alert(data.message, function() {
                        fdvp.getLists_Feed();
                        fdvp.showHeader(elm);
                    });
                } else {
                    bootbox.alert(data.message);
                };
            }
        });
    }, // end - exec_save_feed

    edit_feed : function (elm) {
        var table = $('table.tbl_feed');
        var tbody = $(table).find('tbody');

        var err = 0;

        $.map( $(tbody).find('tr.row_data'), function(tr) {
            $.map( $(tr).find('input[data-required=1], select[data-required=1]'), function(ipt) {
                if ( empty( $(ipt).val() ) ) {
                    $(ipt).closest('td').addClass('has-error');
                    err++;
                } else {
                    $(ipt).closest('td').removeClass('has-error');
                };
            });
        });

        if ( err > 0 ) {
            bootbox.alert('Data belum lengkap, mohon cek kembali data yang anda masukkan.');
        } else {
            bootbox.confirm('Apakah anda yakin ingin mengubah data pakan ?', function(result) {
                if ( result ) {
                    var row_data = $.map( $('tr.edit[data-aktif=aktif]'), function(tr) {
                        var data = {
                            'id' : $(tr).data('id'),
                            'kode' : $(tr).find('input#kode').val(),
                            'kategori' : $(tr).find('select#kategori').val(),
                            'nama_pakan' : $(tr).find('input#nama_pakan').val(),
                            'kode_item' : $(tr).find('input#kode_item_sup').val(),
                            'supl' : $(tr).find('.supplier').val(),
                            'umur' : numeral.unformat( $(tr).find('input#umur').val() ),
                            'berat_pakan' : numeral.unformat( $(tr).find('input#berat_pakan').val() ),
                            'bentuk_pakan' : $(tr).find('select#bentuk_pakan').val(),
                            'masa_simpan' : numeral.unformat( $(tr).find('input#masa_simpan').val() ),
                            'status' : $(tr).data('status'),
                            'version' : $(tr).data('version'),
                        };

                        return data;
                    })

                    fdvp.exec_edit_feed(row_data, elm);
                };
            });
        };
    }, // end - edit_feed

    exec_edit_feed : function(params, elm){
        $.ajax({
            url : 'parameter/FDVP/edit_feed',
            data : {
                'params' : params
            },
            dataType : 'JSON',
            type : 'POST',
            beforeSend : function(){
                showLoading();
            },
            success : function(data){
                hideLoading();

                if ( data.status == 1 ) {
                    bootbox.alert(data.message, function() {
                        fdvp.getLists_Feed();
                        fdvp.showHeader(elm);
                    });
                } else {
                    bootbox.alert(data.message);
                };
            }
        });
    }, // end - exec_edit_feed

    ack_feed : function (elm) {
        var tr = $(elm).closest('tr');
        var id = $(tr).data('id');
        var kode = $(tr).find('td.kode').html();

        bootbox.confirm('Apakah anda yakin ingin ACK data ' + kode + ' ?', function(result) {
            if ( result ) {
                var params = {
                    'id' : id,
                    'tipe' : 'pakan',
                    'kode' : kode
                };

                $.ajax({
                    url : 'parameter/FDVP/ack',
                    data : {
                        'params' : params
                    },
                    dataType : 'JSON',
                    type : 'POST',
                    beforeSend : function(){
                        showLoading();
                    },
                    success : function(data){
                        hideLoading();

                        if ( data.status == 1 ) {
                            bootbox.alert(data.message, function() {
                                fdvp.getLists_Feed();
                                fdvp.showHeader(elm);
                            });
                        } else {
                            bootbox.alert(data.message);
                        };
                    }
                });
            };
        });
    }, // end - ack_feed

    save_doc : function (elm) {
        var table = $('table.tbl_doc');
        var tbody = $(table).find('tbody');

        var err = 0;

        $.map( $(tbody).find('tr.row_data'), function(tr) {
            $.map( $(tr).find('input[data-required=1], select[data-required=1]'), function(ipt) {
                if ( empty( $(ipt).val() ) ) {
                    $(ipt).closest('td').addClass('has-error');
                    err++;
                } else {
                    $(ipt).closest('td').removeClass('has-error');
                };
            });
        });

        if ( err > 0 ) {
            bootbox.alert('Data belum lengkap, mohon cek kembali data yang anda masukkan.');
        } else {
            bootbox.confirm('Apakah anda yakin ingin menyimpan data doc ?', function(result) {
                if ( result ) {
                    var row_data = $.map( $('tr.row_data'), function(tr) {
                        var data = {
                            'kategori' : $(tr).find('input#kategori').val(),
                            'nama_doc' : $(tr).find('input#nama_doc').val(),
                            'kode_item' : $(tr).find('input#kode_item_sup').val(),
                            'supl' : $(tr).find('select#supplier').val(),
                            'berat' : numeral.unformat( $(tr).find('input#berat').val() ),
                            'isi' : numeral.unformat( $(tr).find('input#isi').val() )
                        };

                        return data;
                    })

                    fdvp.exec_save_doc(row_data, elm);
                };
            });
        };
    }, // end - save_doc

    exec_save_doc : function(params, elm){
        $.ajax({
            url : 'parameter/FDVP/save_doc',
            data : {
                'params' : params
            },
            dataType : 'JSON',
            type : 'POST',
            beforeSend : function(){
                showLoading();
            },
            success : function(data){
                hideLoading();

                if ( data.status == 1 ) {
                    bootbox.alert(data.message, function() {
                        fdvp.getLists_Doc();
                        fdvp.showHeader(elm);
                    });
                } else {
                    bootbox.alert(data.message);
                };
            }
        });
    }, // end - exec_save_doc

    edit_doc : function (elm) {
        var table = $('table.tbl_feed');
        var tbody = $(table).find('tbody');

        var err = 0;

        $.map( $(tbody).find('tr.row_data'), function(tr) {
            $.map( $(tr).find('input[data-required=1], select[data-required=1]'), function(ipt) {
                if ( empty( $(ipt).val() ) ) {
                    $(ipt).closest('td').addClass('has-error');
                    err++;
                } else {
                    $(ipt).closest('td').removeClass('has-error');
                };
            });
        });

        if ( err > 0 ) {
            bootbox.alert('Data belum lengkap, mohon cek kembali data yang anda masukkan.');
        } else {
            bootbox.confirm('Apakah anda yakin ingin mengubah data pakan ?', function(result) {
                if ( result ) {
                    var row_data = $.map( $('tr.edit[data-aktif=aktif]'), function(tr) {
                        var data = {
                            'id' : $(tr).data('id'),
                            'kode' : $(tr).find('input#kode').val(),
                            'kategori' : $(tr).find('input#kategori').val(),
                            'nama_doc' : $(tr).find('input#nama_doc').val(),
                            'kode_item' : $(tr).find('input#kode_item_sup').val(),
                            'supl' : $(tr).find('select#supplier').val(),
                            'berat' : numeral.unformat( $(tr).find('input#berat').val() ),
                            'isi' : numeral.unformat( $(tr).find('input#isi').val() ),
                            'status' : $(tr).data('status'),
                            'version' : $(tr).data('version'),
                        };

                        return data;
                    })

                    fdvp.exec_edit_doc(row_data, elm);
                };
            });
        };
    }, // end - edit_doc

    exec_edit_doc : function(params, elm){
        $.ajax({
            url : 'parameter/FDVP/edit_doc',
            data : {
                'params' : params
            },
            dataType : 'JSON',
            type : 'POST',
            beforeSend : function(){
                showLoading();
            },
            success : function(data){
                hideLoading();

                if ( data.status == 1 ) {
                    bootbox.alert(data.message, function() {
                        fdvp.getLists_Doc();
                        fdvp.showHeader(elm);
                    });
                } else {
                    bootbox.alert(data.message);
                };
            }
        });
    }, // end - exec_edit_doc

    ack_doc : function (elm) {
        var tr = $(elm).closest('tr');
        var id = $(tr).data('id');
        var kode = $(tr).find('td.kode').html();

        bootbox.confirm('Apakah anda yakin ingin ACK data ' + kode + ' ?', function(result) {
            if ( result ) {
                var params = {
                    'id' : id,
                    'tipe' : 'doc',
                    'kode' : kode
                };

                $.ajax({
                    url : 'parameter/FDVP/ack',
                    data : {
                        'params' : params
                    },
                    dataType : 'JSON',
                    type : 'POST',
                    beforeSend : function(){
                        showLoading();
                    },
                    success : function(data){
                        hideLoading();

                        if ( data.status == 1 ) {
                            bootbox.alert(data.message, function() {
                                fdvp.getLists_Doc();
                                fdvp.showHeader(elm);
                            });
                        } else {
                            bootbox.alert(data.message);
                        };
                    }
                });
            };
        });
    }, // end - ack_doc

    save_voadip : function (elm) {
        var table = $('table.tbl_voadip');
        var tbody = $(table).find('tbody');

        var err = 0;

        $.map( $(tbody).find('tr.row_data'), function(tr) {
            $.map( $(tr).find('input[data-required=1], select[data-required=1]'), function(ipt) {
                if ( empty( $(ipt).val() ) ) {
                    $(ipt).closest('td').addClass('has-error');
                    err++;
                } else {
                    $(ipt).closest('td').removeClass('has-error');
                };
            });
        });

        if ( err > 0 ) {
            bootbox.alert('Data belum lengkap, mohon cek kembali data yang anda masukkan.');
        } else {
            bootbox.confirm('Apakah anda yakin ingin menyimpan data voadip ?', function(result) {
                if ( result ) {
                    var row_data = $.map( $('tr.row_data'), function(tr) {
                        var data = {
                            'kategori' : $(tr).find('select#kategori').val(),
                            'nama_voadip' : $(tr).find('input#nama_voadip').val(),
                            'kode_item' : $(tr).find('input#kode_item_sup').val(),
                            'supl' : $(tr).find('select#supplier').val(),
                            'berat' : numeral.unformat( $(tr).find('input#dosis').val() ),
                            'isi' : numeral.unformat( $(tr).find('input#isi').val() ),
                            'satuan' : $(tr).find('input#satuan').val(),
                            'bentuk' : $(tr).find('select#bentuk_voadip').val(),
                            'masa_simpan' : numeral.unformat( $(tr).find('input#masa_simpan').val() ),
                        };

                        return data;
                    });

                    fdvp.exec_save_voadip(row_data, elm);
                };
            });
        };
    }, // end - save_voadip

    exec_save_voadip : function(params, elm){
        $.ajax({
            url : 'parameter/FDVP/save_voadip',
            data : {
                'params' : params
            },
            dataType : 'JSON',
            type : 'POST',
            beforeSend : function(){
                showLoading();
            },
            success : function(data){
                hideLoading();

                if ( data.status == 1 ) {
                    bootbox.alert(data.message, function() {
                        fdvp.getLists_Voadip();
                        fdvp.showHeader(elm);
                    });
                } else {
                    bootbox.alert(data.message);
                };
            }
        });
    }, // end - exec_save_voadip

    edit_voadip : function (elm) {
        var table = $('table.tbl_voadip');
        var tbody = $(table).find('tbody');

        var err = 0;

        $.map( $(tbody).find('tr.row_data'), function(tr) {
            $.map( $(tr).find('input[data-required=1], select[data-required=1]'), function(ipt) {
                if ( empty( $(ipt).val() ) ) {
                    $(ipt).closest('td').addClass('has-error');
                    err++;
                } else {
                    $(ipt).closest('td').removeClass('has-error');
                };
            });
        });

        if ( err > 0 ) {
            bootbox.alert('Data belum lengkap, mohon cek kembali data yang anda masukkan.');
        } else {
            bootbox.confirm('Apakah anda yakin ingin mengubah data voadip ?', function(result) {
                if ( result ) {
                    var row_data = $.map( $('tr.edit[data-aktif=aktif]'), function(tr) {
                        var data = {
                            'id' : $(tr).data('id'),
                            'kode' : $(tr).find('input#kode').val(),
                            'kategori' : $(tr).find('select#kategori').val(),
                            'nama_voadip' : $(tr).find('input#nama_voadip').val(),
                            'kode_item' : $(tr).find('input#kode_item_sup').val(),
                            'supl' : $(tr).find('select#supplier').val(),
                            'berat' : numeral.unformat( $(tr).find('input#dosis').val() ),
                            'isi' : numeral.unformat( $(tr).find('input#isi').val() ),
                            'satuan' : $(tr).find('input#satuan').val(),
                            'bentuk' : $(tr).find('select#bentuk_voadip').val(),
                            'masa_simpan' : numeral.unformat( $(tr).find('input#masa_simpan').val() ),
                            'status' : $(tr).data('status'),
                            'version' : $(tr).data('version'),
                        };

                        return data;
                    });

                    fdvp.exec_edit_voadip(row_data, elm);
                };
            });
        };
    }, // end - edit_voadip

    exec_edit_voadip : function(params, elm){
        $.ajax({
            url : 'parameter/FDVP/edit_voadip',
            data : {
                'params' : params
            },
            dataType : 'JSON',
            type : 'POST',
            beforeSend : function(){
                showLoading();
            },
            success : function(data){
                hideLoading();

                if ( data.status == 1 ) {
                    bootbox.alert(data.message, function() {
                        fdvp.getLists_Voadip();
                        fdvp.showHeader(elm);
                    });
                } else {
                    bootbox.alert(data.message);
                };
            }
        });
    }, // end - exec_edit_voadip

    ack_voadip : function (elm) {
        var tr = $(elm).closest('tr');
        var id = $(tr).data('id');
        var kode = $(tr).find('td.kode').html();

        bootbox.confirm('Apakah anda yakin ingin ACK data ' + kode + ' ?', function(result) {
            if ( result ) {
                var params = {
                    'id' : id,
                    'tipe' : 'obat',
                    'kode' : kode
                };

                $.ajax({
                    url : 'parameter/FDVP/ack',
                    data : {
                        'params' : params
                    },
                    dataType : 'JSON',
                    type : 'POST',
                    beforeSend : function(){
                        showLoading();
                    },
                    success : function(data){
                        hideLoading();

                        if ( data.status == 1 ) {
                            bootbox.alert(data.message, function() {
                                fdvp.getLists_Voadip();
                                fdvp.showHeader(elm);
                            });
                        } else {
                            bootbox.alert(data.message);
                        };
                    }
                });
            };
        });
    }, // end - ack_voadip

    save_peralatan : function (elm) {
        var table = $('table.tbl_peralatan');
        var tbody = $(table).find('tbody');

        var err = 0;

        $.map( $(tbody).find('tr.row_data'), function(tr) {
            $.map( $(tr).find('input[data-required=1], select[data-required=1]'), function(ipt) {
                if ( empty( $(ipt).val() ) ) {
                    $(ipt).closest('td').addClass('has-error');
                    err++;
                } else {
                    $(ipt).closest('td').removeClass('has-error');
                };
            });
        });

        if ( err > 0 ) {
            bootbox.alert('Data belum lengkap, mohon cek kembali data yang anda masukkan.');
        } else {
            bootbox.confirm('Apakah anda yakin ingin menyimpan data voadip ?', function(result) {
                if ( result ) {
                    var row_data = $.map( $('tr.row_data'), function(tr) {
                        var data = {
                            'kategori' : $(tr).find('input#kategori').val(),
                            'nama_peralatan' : $(tr).find('input#nama_peralatan').val(),
                            'kode_item' : $(tr).find('input#kode_item_sup').val(),
                            'supl' : $(tr).find('select#supplier').val(),
                            'isi' : numeral.unformat( $(tr).find('input#isi').val() ),
                            'satuan' : $(tr).find('input#satuan').val(),
                            'masa_simpan' : numeral.unformat( $(tr).find('input#masa_simpan').val() ),
                        };

                        return data;
                    });

                    fdvp.exec_save_peralatan(row_data, elm);
                };
            });
        };
    }, // end - save_voadip

    exec_save_peralatan : function(params, elm){
        $.ajax({
            url : 'parameter/FDVP/save_peralatan',
            data : {
                'params' : params
            },
            dataType : 'JSON',
            type : 'POST',
            beforeSend : function(){
                showLoading();
            },
            success : function(data){
                hideLoading();

                if ( data.status == 1 ) {
                    bootbox.alert(data.message, function() {
                        fdvp.getLists_Peralatan();
                        fdvp.showHeader(elm);
                    });
                } else {
                    bootbox.alert(data.message);
                };
            }
        });
    }, // end - exec_save_peralatan

    edit_peralatan : function (elm) {
        var table = $('table.tbl_feed');
        var tbody = $(table).find('tbody');

        var err = 0;

        $.map( $(tbody).find('tr.row_data'), function(tr) {
            $.map( $(tr).find('input[data-required=1], select[data-required=1]'), function(ipt) {
                if ( empty( $(ipt).val() ) ) {
                    $(ipt).closest('td').addClass('has-error');
                    err++;
                } else {
                    $(ipt).closest('td').removeClass('has-error');
                };
            });
        });

        if ( err > 0 ) {
            bootbox.alert('Data belum lengkap, mohon cek kembali data yang anda masukkan.');
        } else {
            bootbox.confirm('Apakah anda yakin ingin mengubah data voadip ?', function(result) {
                if ( result ) {
                    var row_data = $.map( $('tr.edit[data-aktif=aktif]'), function(tr) {
                        var data = {
                            'id' : $(tr).data('id'),
                            'kode' : $(tr).find('input#kode').val(),
                            'kategori' : $(tr).find('input#kategori').val(),
                            'nama_peralatan' : $(tr).find('input#nama_peralatan').val(),
                            'kode_item' : $(tr).find('input#kode_item_sup').val(),
                            'supl' : $(tr).find('select#supplier').val(),
                            'isi' : numeral.unformat( $(tr).find('input#isi').val() ),
                            'satuan' : $(tr).find('input#satuan').val(),
                            'masa_simpan' : numeral.unformat( $(tr).find('input#masa_simpan').val() ),
                            'status' : $(tr).data('status'),
                            'version' : $(tr).data('version'),
                        };

                        return data;
                    })

                    fdvp.exec_edit_peralatan(row_data, elm);
                };
            });
        };
    }, // end - edit_voadip

    exec_edit_peralatan : function(params, elm){
        $.ajax({
            url : 'parameter/FDVP/edit_peralatan',
            data : {
                'params' : params
            },
            dataType : 'JSON',
            type : 'POST',
            beforeSend : function(){
                showLoading();
            },
            success : function(data){
                hideLoading();

                if ( data.status == 1 ) {
                    bootbox.alert(data.message, function() {
                        fdvp.getLists_Peralatan();
                        fdvp.showHeader(elm);
                    });
                } else {
                    bootbox.alert(data.message);
                };
            }
        });
    }, // end - exec_edit_peralatan

    ack_peralatan : function (elm) {
        var tr = $(elm).closest('tr');
        var id = $(tr).data('id');
        var kode = $(tr).find('td.kode').html();

        bootbox.confirm('Apakah anda yakin ingin ACK data ' + kode + ' ?', function(result) {
            if ( result ) {
                var params = {
                    'id' : id,
                    'tipe' : 'peralatan',
                    'kode' : kode
                };

                $.ajax({
                    url : 'parameter/FDVP/ack',
                    data : {
                        'params' : params
                    },
                    dataType : 'JSON',
                    type : 'POST',
                    beforeSend : function(){
                        showLoading();
                    },
                    success : function(data){
                        hideLoading();

                        if ( data.status == 1 ) {
                            bootbox.alert(data.message, function() {
                                fdvp.getLists_Peralatan();
                                fdvp.showHeader(elm);
                            });
                        } else {
                            bootbox.alert(data.message);
                        };
                    }
                });
            };
        });
    }, // end - ack_peralatan
};

fdvp.start_up_feed();
fdvp.start_up_doc();
fdvp.start_up_voadip();
fdvp.start_up_peralatan();