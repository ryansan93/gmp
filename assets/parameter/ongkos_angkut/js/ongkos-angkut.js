var data_kec = {};

var oa = {

    start_up: function() {
        $('.chosen-kab').chosen();
        $('.chosen-kec').chosen();

        oa.getLists();
        // oa.getDataOld();

        var select_kab = $('select.chosen-kab');
        oa.loadDataKec(select_kab);

        oa.setBindSHA1();

        $('#datetimepicker1').datetimepicker({
            locale: 'id',
            format: 'DD MMM Y'
        });
    }, // end - start_up

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

    getDataOld : function(id = null){
        var div_input = $('div#oa');
        var jns_oa = $(div_input).find('select.jns_oa').val();

        $.ajax({
            url: 'parameter/OngkosAngkut/getDataOld',
            data: {
                'jns_oa' : jns_oa,
                'id' : id
            },
            type: 'POST',
            dataType: 'JSON',
            beforeSend: function() {
                $(div_input).find('.data_oa').addClass('hide');
            },
            success: function(data) {
                if (data.status == 0) {
                    $(div_input).find('.loading').addClass('hide');
                    $(div_input).find('.data_oa').removeClass('hide');

                    var div_oa = $(div_input).find('.data_oa');
                    var table = $(div_oa).find('table');
                    var tr = $(table).find('tr.data');
                    var td = $(tr).find('td');

                    var select_kab = $(tr).find('select.chosen-kab');

                    $('.chosen-kab').chosen();
                    $('.chosen-kec').chosen();

                    oa.loadDataKec(select_kab);

                    $(tr).find('.lama1').prop('readonly', true);
                    $(tr).find('.lama2').prop('readonly', true);
                } else {
                    var table = $('table.oa');
                    var tbody = $(table).find('tbody');

                    var oa_item = data.content.oa_item;

                    var i = 0;
                    do{
                        var id_kab = oa_item[i].wilayah.id;
                        var id_kec = oa_item[i].kecamatan;
                        var nama_kab = oa_item[i].wilayah.nama;
                        var tarif_lama = oa_item[i].ongkos;
                        var tarif_lama2 = oa_item[i].ongkos2;

                        var row = $(tbody).find('tr.data:last');
                        var select_kab = $(row).find('select.chosen-kab');
                        var select_kec = $(row).find('select.chosen-kec');

                        row.find('input.lama1').val( numeral.formatInt(tarif_lama) );
                        row.find('input.lama2').val( numeral.formatInt(tarif_lama2) );
                        row.find('input.baru1').attr('data-required', 0);
                        row.find('input.baru2').attr('data-required', 0);

                        $('.chosen-kab').chosen();
                        $('.chosen-kec').chosen();

                        var select_kab_new = null;

                        var jml_per_kab = 0;

                        $.map( $(select_kab).find('option'), function(opt){
                            if ( $(opt).val() ==  id_kab) {
                                $(select_kab).find('option')[jml_per_kab].selected = true;
                                select_kec.find('option').remove();
                                select_kab.trigger("chosen:updated");

                                oa.loadDataKec(select_kab, id_kec, 'old');
                            };
                            jml_per_kab++;
                        });


                        i++;
                        if (i != oa_item.length) {
                            var row_clone = row.clone();

                            var select_kec_clone = $(row_clone).find('select.chosen-kec');
                            var select_kab_clone = $(row_clone).find('select.chosen-kab');

                            row_clone.find('input.lama1').val( numeral.formatInt() );
                            row_clone.find('input.lama2').val( numeral.formatInt() );

                            row_clone.find('input.baru1').attr('data-required', 0);
                            row_clone.find('input.baru2').attr('data-required', 0);

                            row_clone.find('.chosen-container').remove();

                            $(row_clone).find('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal]').each(function(){
                                $(this).priceFormat(Config[$(this).data('tipe')]);
                            });

                            tbody.append(row_clone);
                        };
                    } while ( i < oa_item.length);

                    $.map( $(table).find('tr.data:not(:last)'), function(opt){
                        $(opt).find('button').hide();
                    });

                    $(div_input).find('.loading').addClass('hide');
                    $(div_input).find('.data_oa').removeClass('hide');
                };
            },
        });
    }, // end - getDataOld

    getLists : function(keyword = null){
        $.ajax({
            url : 'parameter/OngkosAngkut/list_oa',
            data : {'keyword' : keyword},
            dataType : 'HTML',
            type : 'GET',
            beforeSend : function(){ showLoading(); },
            success : function(data){

                $('table#tb_lists_oa tbody').html(data);
                hideLoading();
            }
        });
    }, // end - getLists

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
            var tgl_mulai = $(elm).attr('data-mulai');
            var resubmit = $(elm).attr('data-resubmit');

            oa.load_form(v_id, tgl_mulai, resubmit);
        };
    }, // end - changeTabActive

    load_form: function(v_id = null, tgl_mulai = null, resubmit = null) {
        var div_action = $('div#action');

        $.ajax({
            url : 'parameter/OngkosAngkut/load_form',
            data : {
                'id' :  v_id,
                'resubmit' : resubmit
            },
            type : 'GET',
            dataType : 'HTML',
            beforeSend : function(){},
            success : function(html){
                $(div_action).html(html);

                $('#datetimepicker1').datetimepicker({
                    locale: 'id',
                    format: 'DD MMM Y',
                    defaultDate: tgl_mulai
                });

                $('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal], [data-tipe=decimal3],[data-tipe=decimal4], [data-tipe=number]').each(function(){
                    $(this).priceFormat(Config[$(this).data('tipe')]);
                });

                if ( !empty(resubmit) ) {
                    oa.getDataOld(v_id);
                };

                oa.setBindSHA1();
            },
        });
    }, // end - load_form

    reloadChangeTabActive : function(){
        $('#for_more_change_tab_activity').children(":first").click();
    }, // end - reloadChangeTabActive

    loadContentOA: function(elm) {
        var dcontent = $('#oa');
        var v_id = $(elm).attr('data-id');
        var resubmit = $(elm).attr('data-resubmit');

        $.ajax({
            url: 'parameter/OngkosAngkut/loadContentOA',
            data: {
            'id': v_id,
            'resubmit' : resubmit,
            },
            type: 'GET',
            dataType: 'HTML',
            beforeSend: function() {
                App.showLoaderInContent(dcontent);
            },
            success: function(html) {
                App.hideLoaderInContent(dcontent, html);
                App.formatNumber();
                App.formatDate();

                if (resubmit == 1) {
                    $('.loading').addClass('hide');
                    $('.data_oa').removeClass('hide');

                    var table = $('table.oa');
                    var tbody = $(table).find('tbody');

                    $.map( $(tbody).find('tr'), function(opt){
                        var select_kab = $(opt).find('.chosen-kab');
                        var select_kec = $(opt).find('.chosen-kec');
                        var id_kec = $(select_kec).val();

                        $(select_kab).chosen();
                        $(select_kec).chosen();

                        oa.loadDataKec(select_kab, id_kec);
                    });

                    var tr_length = $(table).find('tr').length;
                    $.map( $(table).find('tr.data:not(:last)'), function(opt){
                        var tr_last = $(table).find('tr.data:last');
                        if (tr_length != 1) {
                            $(opt).find('button').hide();
                            $(tr_last).find('button.remove').removeClass('hide');
                        } else {
                            $(tr_last).find('button.remove').addClass('hide');
                        };
                    });
                } else {
                    // oa.getDataOld();
                }
            },
        });
    }, // end - loadContentOA

    loadDataKec : function(elm, id_kec, data_old = null){
        var tr = $(elm).closest('tr');
        var select_kab_old = $(tr).find('select.kab');
        var select_kec = $(tr).find('select.chosen-kec');
        var select_kec_all = $(tr).find('select.kec');
        var id_induk = $(elm).val();
        var nama_induk = $(elm).find('option:selected').text();

        $.ajax({
            url: 'parameter/OngkosAngkut/getLokasiKc',
            data: {
            'id_induk': id_induk
            },
            type: 'POST',
            dataType: 'JSON',
            beforeSend: function() {
                select_kec.find('option').remove();
                select_kec_all.find('option').remove();
            },
            success: function(data) {
                var option = '';
                var selected_all = '';
                var selected_except = '';
                data_kec = data.content;

                if ( id_kec == -1 ) {
                    selected_except = 'selected';
                } else if ( id_kec == 0 ) {
                    selected_all = 'selected';
                };

                option += '<option '+ selected_except +' value=-1>&nbsp</option>';
                option += '<option '+ selected_all +' value=0>ALL</option>';
                for (var i = 0; i < data.content.length; i++) {
                    var selected = '';
                    if ( data.content[i].id == id_kec ) {
                        selected = 'selected';
                    }
                    option += '<option '+ selected +' class=data value='+ data.content[i].id +'>'+ data.content[i].nama +'</option>';
                };

                select_kec.attr('data-id', id_induk);
                select_kec.attr('data-namakab', nama_induk);          
                select_kec.append(option);

                select_kec_all.attr('data-id', id_induk);
                select_kec_all.attr('data-namakab', nama_induk);          
                select_kec_all.append(option);

                select_kec.trigger("chosen:updated");

                if ( empty(data_old) ) {
                    oa.loadDataKab(select_kec);
                };
            },
        });    
    }, // end - loadDataKec

    loadHeader : function (elm) {
        var table = $('table.oa');
        var val = $(elm).val();


        if ( val.trim() == 'doc' ) {
            $(table).find('th.head').html('Tarif / Ekor');
            $(table).find('th.head1').html('Buduran');
            $(table).find('th.head2').html('Gedangan');
            // doc : kediri - pasuruan
        } else {
            $(table).find('th.head').html('Tarif / Kg');
            $(table).find('th.head1').html('Kediri');
            $(table).find('th.head2').html('Pasuruan');
            // pakan : buduran - gedangan
        };
    }, // end - loadHeader

    loadContentKec : function (elm) {
        var tr = $(elm).closest('tr');
        var sel_kab = $(tr).find('select.chosen-kab');
        var id_kec = $(tr).find('select.chosen-kec').val();

        oa.loadDataKec(sel_kab, id_kec);
    }, // end - loadHeader

    loadDataKab : function(elm){
        var tr = $(elm).closest('tr');
        var tbody = $(tr).closest('tbody');
        var select_kab = $(tr).find('select.chosen-kab');
        var select_kab_old = $(tr).find('select.kab');
        var select_kec = $(tr).find('select.chosen-kec');
        var id_kab = $(select_kab).val();
        var id_kec = $(elm).val();

        var all_data_selected = {};

        var data_selected = $.map( $(tbody).find('select.chosen-kec'), function(opt) {
            var tr_opt = $(opt).closest('tr');
            var select_kab = $(tr_opt).find('select.chosen-kab');
            var id_kab_opt = $(select_kab).val();
            var id_selected = $(opt).val();

            var data = {
                'val' : $(opt).val(),
                'tr' : $(tr_opt),
                'id_selected' : id_selected,
                'id_kab_opt' : id_kab_opt
            };

            return data;
        });

        for (var i = 0; i < data_selected.length; i++) {
            var id_sel = data_selected[i]['id_selected'];
            var tr_opt = data_selected[i]['tr'];
            var id_kab_new = data_selected[i]['id_kab_opt'];
            var chosen_kec = $(tr_opt).find('select.chosen-kec');

            $(chosen_kec).find('option').remove();

            var data_selected_old = $.map( $(tr_opt).find('select.kec option.data'), function(opt) {
                var data = {
                    'val' : $(opt).val(),
                    'html' : $(opt).html()
                };

                return data;
            });

            var option = '';
            var selected = '';
            var ada = false;

            var jml_per_kab = 0;
            $.map( $(tbody).find('select.chosen-kec[data-id='+id_kab_new+']'), function(opt) {
                jml_per_kab++;
            });


            if ( jml_per_kab <= 1) {
                option += '<option value=-1>&nbsp</option>';
                option += '<option value=0>ALL</option>';
            } else if ( jml_per_kab > 1 && data_selected_old.length != jml_per_kab && data_selected_old.length > jml_per_kab) {
                option += '<option value=-1>&nbsp</option>';
            } else if ( data_selected_old.length == jml_per_kab || data_selected_old.length < jml_per_kab) {
                option += '<option></option>';
            };

            var selected_all = '';
            if ( id_sel != '' ) {
                if ( id_sel == 0 ) {
                    selected_all = 'selected';
                    option += '<option '+selected_all+' value=0>ALL</option>';
                }
            };

            for (var j = 0; j < data_selected_old.length; j++) {
                selected = '';
                for (var k = 0; k < data_selected.length; k++) {
                    if ( data_selected_old[j]['val'] != data_selected[k]['val'] ) {
                        ada = false;
                    } else {
                        if ( data_selected_old[j]['val'] == id_sel ) {
                            ada = false;
                            selected = 'selected';
                        } else {
                            ada = true;
                            break;
                        };
                    }
                };

                if (ada == false) {
                    option += '<option '+ selected +' class=data value='+ data_selected_old[j]['val'] +'>'+ data_selected_old[j]['html'] +'</option>';
                };
            };

            $(chosen_kec).append(option);
            $(chosen_kec).trigger("chosen:updated");
        };

        oa.loadDataKab2();
    }, // end - loadDataKab

    loadDataKab2 : function() {
        var table = $('table.oa');
        var tbody = $(table).find('tbody');

        var data_selected_all = $.map( $(tbody).find('select.chosen-kec'), function(opt) {
            if ( $(opt).val() == 0 ) {
                var tr_opt = $(opt).closest('tr');
                var select_kab = $(tr_opt).find('select.chosen-kab');
                var id_kab_opt = $(select_kab).val();
                var id_selected = $(opt).val();

                var data = {
                    'val' : $(opt).val(),
                    'tr' : $(tr_opt),
                    'id_selected' : id_selected,
                    'id_kab_opt' : id_kab_opt
                };

                return data;
            };
        });

        for (var i = 0; i < data_selected_all.length; i++) {
            var id_sel = data_selected_all[i]['id_selected'];
            var tr_opt = data_selected_all[i]['tr'];
            var id_kab_new = data_selected_all[i]['id_kab_opt'];
            var chosen_kec = $(tr_opt).find('select.chosen-kec');

            $.map( $('select.chosen-kec') , function(opt){
                var option = '<option></option>';

                var tr = $(opt).closest('tr');
                var kab = $(tr).find('select.chosen-kab').val();
                var kec = $(tr).find('select.chosen-kec').val();
                var sel_kec = $(tr).find('select.chosen-kec');

                if ( kab == id_kab_new ) {
                    if ( kec != id_sel ) {
                        $(sel_kec).find('option').remove();

                        $(sel_kec).append(option);
                        $(sel_kec).trigger("chosen:updated");
                    }
                };
            });
        }
    }, // end - loadDataKab2

    save : function(elm){
        var err = 0;
        var div = $('div.attachement');

        $.map( $('[data-required=1]'), function(obj) {
            if ( empty($(obj).val()) ) {
                err++;
                $(obj).parent().addClass('has-error');

                var td_chosen = $(obj).closest('td');
                var div_chosen = $(td_chosen).find('div.chosen-container');

                $(div_chosen).find('a.chosen-single').addClass('has-error');

                if ( empty($('a[name=dokumen]').text()) ) {
                    $('label[name=dokumen]').css('color','#a94442');
                    $('i.glyphicon-paperclip ').css('color','#a94442');
                } else {
                    $('label[name=dokumen]').css('color','#000');
                    $('i.glyphicon-paperclip ').css('color','#000');
                };

            } else {
                $(obj).parent().removeClass('has-error');

                var td_chosen = $(obj).closest('td');
                var div_chosen = $(td_chosen).find('div.chosen-container');

                $(div_chosen).find('a.chosen-single').removeClass('has-error');
            };
        });

        if ( err > 0 ) {
            bootbox.alert('Ada data yang belum diisi dengan lengkap. Mohon dilengkapi sebelum disimpan.');
        } else {
            var formData = new FormData();

            var reject_id = $(elm).attr('data-rejectid');

            var data_params = {};

            var tanggal = dateSQL($('[name=tanggal-berlaku]').data('DateTimePicker').date());
            var nama_attc = $('a[name=dokumen]').html();
            var jns_oa = $('select.jns_oa').val();

            var ipt = $('input:file');
            var __file = $(ipt).get(0).files[0];
            formData.append('files[]', __file);

            var lampiran = {
                'id' : $(ipt).closest('label').attr('data-idnama'),
                'name' : __file.name,
                'sha1' : $(ipt).attr('data-sha1'),
            };

            var data_detail = $.map( $('tr.data'), function(opt){
                var data = {
                    'id_kab' : $(opt).find('select.chosen-kab').val(),
                    'id_kec' : $(opt).find('select.chosen-kec').val(),
                    'tarif_lama' : numeral.unformat( $(opt).find('input.lama1').val() ),
                    'tarif_lama2' : numeral.unformat( $(opt).find('input.lama2').val() ),
                    'tarif_baru' : numeral.unformat( $(opt).find('input.baru1').val() ),
                    'tarif_baru2' : numeral.unformat( $(opt).find('input.baru2').val() )
                };

                return data;
            });

            var _filetmp = $('.file_lampiran').prop('files')[0];
            data_params['tanggal'] = tanggal;
            data_params['nama_attc'] = nama_attc;
            data_params['jns_oa'] = jns_oa;
            data_params['detail'] = data_detail;
            data_params['action'] = 'submit';
            data_params['reject_id'] = reject_id;
            data_params['lampiran'] = lampiran;

            formData.append("data", JSON.stringify(data_params));

            App.confirmDialog('Apakah anda yakin akan menyimpan tarif OA Pakan & DOC ?', function(isConfirm){
                if (isConfirm) {
                    oa.execute_save(formData);
                }
            });
        };
    }, // end - save

    execute_save : function(formData){
        $.ajax({
            url: 'parameter/OngkosAngkut/save',
            dataType: 'json',
            type: 'post',
            async:false,
            processData: false,
            contentType: false,
            data: formData,
            beforeSend: function() { showLoading(); },
            success: function(data) {
                hideLoading();
                if (data.status) {
                    bootbox.alert(data.message, function(){
                        // $('#oa').html(data.content);
                        oa.getLists();
                        oa.load_form(data.content.id);
                    });
                }else{
                    alertDialog(data.message);
                }
            }
        });
    }, // end - execute_save

    edit : function(elm){
        var err = 0;
        var div = $('div.attachement');

        $.map( $('[data-required=1]'), function(obj) {
            if ( empty($(obj).val()) ) {
                err++;
                $(obj).parent().addClass('has-error');

                var td_chosen = $(obj).closest('td');
                var div_chosen = $(td_chosen).find('div.chosen-container');

                $(div_chosen).find('a.chosen-single').addClass('has-error');

                if ( empty($('a[name=dokumen]').text()) ) {
                    $('label[name=dokumen]').css('color','#a94442');
                    $('i.glyphicon-paperclip ').css('color','#a94442');
                } else {
                    $('label[name=dokumen]').css('color','#000');
                    $('i.glyphicon-paperclip ').css('color','#000');
                };

            } else {
                $(obj).parent().removeClass('has-error');

                var td_chosen = $(obj).closest('td');
                var div_chosen = $(td_chosen).find('div.chosen-container');

                $(div_chosen).find('a.chosen-single').removeClass('has-error');
            };
        });

        if ( err > 0 ) {
            bootbox.alert('Ada data yang belum diisi dengan lengkap. Mohon dilengkapi sebelum disimpan.');
        } else {
            var formData = new FormData();

            var id = $(elm).data('id');
            var nomor = $(elm).data('nomor');
            var version = $(elm).data('version');

            var data_params = {};

            var tanggal = dateSQL($('[name=tanggal-berlaku]').data('DateTimePicker').date());
            var nama_attc = $('a[name=dokumen]').html();
            var jns_oa = $('select.jns_oa').val();

            var ipt = $('input:file');
            var filename = null;
            if ( !empty( $(ipt).val() ) || !empty( $(ipt).data('old') ) ) {
                var filename = $(ipt).data('old');

                if ( !empty( $(ipt).val() ) ) {
                    var __file = $(ipt).get(0).files[0];
                    formData.append('files[]', __file);

                    filename = __file.name;
                }
            }

            var lampiran = {
                'id' : $(ipt).closest('label').attr('data-idnama'),
                'name' : filename,
                'sha1' : $(ipt).attr('data-sha1'),
                'old' : $(ipt).data('old')
            };

            var data_detail = $.map( $('tr.data'), function(opt){
                var data = {
                    'id_kab' : $(opt).find('select.chosen-kab').val(),
                    'id_kec' : $(opt).find('select.chosen-kec').val(),
                    'tarif_lama' : numeral.unformat( $(opt).find('input.lama1').val() ),
                    'tarif_lama2' : numeral.unformat( $(opt).find('input.lama2').val() ),
                    'tarif_baru' : numeral.unformat( $(opt).find('input.baru1').val() ),
                    'tarif_baru2' : numeral.unformat( $(opt).find('input.baru2').val() )
                };

                return data;
            });

            data_params['tanggal'] = tanggal;
            data_params['nama_attc'] = nama_attc;
            data_params['jns_oa'] = jns_oa;
            data_params['detail'] = data_detail;
            data_params['action'] = 'submit';
            data_params['id'] = id;
            data_params['nomor'] = nomor;
            data_params['version'] = version;
            data_params['lampiran'] = lampiran;

            formData.append("data", JSON.stringify(data_params));

            App.confirmDialog('Apakah anda yakin akan mengubah data tarif OA Pakan & DOC ?', function(isConfirm){
                if (isConfirm) {
                    oa.execute_edit(formData);
                }
            });
        };
    }, // end - edit

    execute_edit : function(formData){
        $.ajax({
            url: 'parameter/OngkosAngkut/edit',
            dataType: 'json',
            type: 'post',
            async:false,
            processData: false,
            contentType: false,
            data: formData,
            beforeSend: function() { showLoading(); },
            success: function(data) {
                hideLoading();
                if (data.status) {
                    bootbox.alert(data.message, function(){
                        // $('#oa').html(data.content);
                        oa.getLists();
                        oa.load_form(data.content.id);
                    });
                }else{
                    alertDialog(data.message);
                }
            }
        });
    }, // end - execute_edit

    ack : function () {
        var ids = $('input[type=hidden]').data('id');
        bootbox.confirm('Data OA akan di-ack', function(result){
            if (result) {
                $.ajax({
                    url : 'parameter/OngkosAngkut/ack',
                    data : {'params' : ids},
                    dataType : 'JSON',
                    type : 'POST',
                    beforeSend : function () {
                        showLoading();
                    },
                    success : function(data){
                        hideLoading();
                        if(data.status){
                            bootbox.alert(data.message,function(){
                                oa.getLists();
                                oa.load_form(data.content.id);
                            });
                        }else{
                            bootbox.alert(data.message);
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
            App.confirmDialog("Approve Perhitungan Budidaya", function(confirm){
                if (confirm) {
                    oa.executeApproveReject(params);
                }
            });
        } else if (action == 'reject') {
            App.confirmRejectDialog("reject", function(text){
                if (text.length > 0) {
                    params['alasan_tolak'] = text;
                    oa.executeApproveReject(params);
                }
            });
        }
    }, // end - ackReject

    executeApproveReject : function(params) {
        $.ajax({
            url : 'parameter/OngkosAngkut/approveReject',
            data : {'params' :  params},
            type : 'POST',
            dataType : 'JSON',
            beforeSend : function(){
                showLoading();
            },
            success : function(data){
                if (data.status) {
                    bootbox.alert(data.message, function(){
                        $('#oa').html(data.content);
                        oa.getLists();
                    });
                }else{
                    alertDialog(data.message);
                }
            },
        });
    }, // end - executeAckReject

    addRowTable: function(elm) {
        var row = $(elm).closest("tr");
        var tbody = $(elm).closest("tbody");
        var row_clone = row.clone();

        var select_kec = $(row_clone).find('select.chosen-kec');
        var select_kab = $(row_clone).find('select.chosen-kab');

        select_kec.find('option.data').remove();

        row_clone.find('input').val('');

        row_clone.find('input.baru1').attr('data-required', 1);
        row_clone.find('input.baru2').attr('data-required', 1);
        row_clone.find('input.lama1').prop('readonly', true);
        row_clone.find('input.lama2').prop('readonly', true);

        row_clone.find('.chosen-container').remove();

        row_clone.find('.chosen-kab').chosen();
        row_clone.find('.chosen-kec').chosen();

        if (tbody.find('tr').length > 0) {
            row_clone.find('td.action button.remove').show();
            row_clone.find('td.action button.remove').removeClass('hide');
        };

        tbody.append(row_clone);

        oa.loadDataKec(select_kab);

        row.find('td.action button').hide();
        App.formatNumber();
    }, // end - addRowTelepon

    removeRowTable: function(elm) {
        var row = $(elm).closest("tr");

        var select_kab = null;
        var id_kec = 0;

        var table = $('table.oa');
        var tbody = $(table).find('tbody');
        var tr_length = $(tbody).find('tr:not(:last)').length;

        if (tr_length > 1) {
            row.prev('tr').find('td.action button').show();
            select_kab = $(row).prev('tr').find("select.chosen-kab");
            id_kec = $(row).prev('tr').find("select.chosen-kec").val();
            row.find('input').val('');
            row.remove();
        } else if (tr_length <= 1) {
            // console.log( row.prev('tr').find('td.action button.remove') );
            row.prev('tr').find('td.action button').show();
            row.prev('tr').find('td.action button.remove').hide();
            select_kab = $(row).prev('tr').find("select.chosen-kab");
            id_kec = $(row).prev('tr').find("select.chosen-kec").val();
            row.find('input').val('');
            row.remove();
        }

        oa.loadDataKec(select_kab, id_kec);
    }, // end - removeRowTelepon

};

oa.start_up();
