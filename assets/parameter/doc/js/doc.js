var doc = {
    start_up : function () {
        doc.setBindSHA1();
        doc.getLists();

        $('#datetimepicker1').datetimepicker({
            locale: 'id',
            format: 'DD MMM Y'
        });
    }, // end - start_up

    getLists : function(keyword = null){
        $.ajax({
            url : 'parameter/Doc/list_doc',
            data : {'keyword' : keyword},
            dataType : 'HTML',
            type : 'GET',
            beforeSend : function(){},
            success : function(data){
                $('table.tbl_doc tbody').html(data);
            }
        });
    }, // end - getLists

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

    changeTabActive: function(elm) {
        var vhref = $(elm).data('href');
        // change tab-menu
        $('.nav-tabs').find('li').removeClass('active');
        $('.nav-tabs').find('li a[data-tab='+vhref+']').parent().addClass('active');

        // change tab-content
        $('.tab-pane').removeClass('active');
        $('div#'+vhref).addClass('active');

        if ( vhref == 'action' ) {
            var v_id = $(elm).attr('data-id');
            var tgl_mulai = $(elm).attr('data-mulai');
            var resubmit = $(elm).attr('data-resubmit');

            doc.load_form(v_id, tgl_mulai, resubmit);
        };
    }, // end - changeTabActive

    load_form: function(v_id = null, tgl_mulai = null, resubmit = null) {
        var div_action = $('div#action');

        $.ajax({
            url : 'parameter/Doc/load_form',
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

                doc.setBindSHA1();
            },
        });
    }, // end - load_form

    save : function() {
        var _data = {}
        var err = 0;

        $.map($('[data-required=1]'), function(ipt){
            if ( empty($(ipt).val()) ) {
                $(ipt).parent().addClass('has-error');

                if ( empty($('a[name=dokumen]').text()) ) {
                    $('label[name=dokumen]').css('color','#a94442');
                    $('i.glyphicon-paperclip ').css('color','#a94442');
                } else {
                    $('label[name=dokumen]').css('color','#000');
                    $('i.glyphicon-paperclip ').css('color','#000');
                };

                err++;
            } else {
                $(ipt).parent().removeClass('has-error');
            };
        });

        $.map( $('input:file'), function(file){
            if (empty( $(file).val() )) {
                $('label[name=dokumen]').css('color','#a94442');
                $('i.glyphicon-paperclip ').css('color','#a94442');

                err++;
            } else {
                $('label[name=dokumen]').css('color','#000');
                $('i.glyphicon-paperclip ').css('color','#000');
            }
        });

        if ( err > 0 ) {
            bootbox.alert('Data yang anda masukkan belum lengkap, harap lengkapi data.');
        } else {
            bootbox.confirm('Apakah anda yakin menyimpan harga doc ?', function(result){
                if ( result ) {
                    var formData = new FormData();
                    var filename = null;

                    var lampiran = $.map( $('input:file'), function(ipt){
                        if (!empty( $(ipt).val() )) {
                            var __file = $(ipt).get(0).files[0];
                            formData.append('files[]', __file);

                            filename = __file.name;

                            return {
                                'id' : $(ipt).closest('tr').attr('data-idnama'),
                                'name' : __file.name,
                                'sha1' : $(ipt).attr('data-sha1'),
                                'old' : $(ipt).attr('data-old'),
                            };
                        }
                    });

                    _data['dokumen'] = filename;
                    _data['tgl_berlaku'] = dateSQL($('[name=tanggal-berlaku]').data('DateTimePicker').date());
                    _data['harga_kontrak'] = numeral.unformat($('input[name=harga_kontrak]').val());
                    _data['lampirans'] = lampiran;

                    formData.append('data', JSON.stringify(_data));

                    // console.log(_data);

                    doc.execute_save(formData);
                }
            });
        };
    }, // end - save

    execute_save : function (formData) {
        $.ajax({
            url :'parameter/Doc/save',
            type : 'post',
            data : formData,
            beforeSend : function(){
                showLoading();
            },
            success : function(data){
                hideLoading();
                if(data.status){
                    bootbox.alert(data.message,function(){
                        doc.getLists();
                        doc.load_form(data.content.id, data.content.tgl_mulai);
                    });
                }else{
                    bootbox.alert(data.message);
                }
            },
            contentType : false,
            processData : false,
        });
    }, // end - execute_save

    edit : function () {
        var _data = {}
        var err = 0;

        $.map($('[data-required=1]'), function(ipt){
            if ( empty($(ipt).val()) ) {
                $(ipt).parent().addClass('has-error');

                if ( empty($('a[name=dokumen]').text()) ) {
                    $('label[name=dokumen]').css('color','#a94442');
                    $('i.glyphicon-paperclip ').css('color','#a94442');
                } else {
                    $('label[name=dokumen]').css('color','#000');
                    $('i.glyphicon-paperclip ').css('color','#000');
                };

                err++;
            } else {
                $(ipt).parent().removeClass('has-error');
            };
        });

        $.map( $('span.file'), function(file){
            if (empty( $(file).text() )) {
                $('label[name=dokumen]').css('color','#a94442');
                $('i.glyphicon-paperclip ').css('color','#a94442');

                err++;
            } else {
                $('label[name=dokumen]').css('color','#000');
                $('i.glyphicon-paperclip ').css('color','#000');
            }
        });

        if ( err > 0 ) {
            bootbox.alert('Data yang anda masukkan belum lengkap, harap lengkapi data.');
        } else {
            bootbox.confirm('Apakah anda yakin mengubah harga doc ?', function(result){
                if ( result ) {
                    var formData = new FormData();
                    var filename = null;

                    var lampiran = $.map( $('input:file'), function(ipt){
                        if (!empty( $(ipt).val() )) {
                            var __file = $(ipt).get(0).files[0];
                            formData.append('files[]', __file);

                            filename = __file.name;

                            return {
                                'id' : $(ipt).closest('tr').attr('data-idnama'),
                                'name' : __file.name,
                                'sha1' : $(ipt).attr('data-sha1'),
                                'old' : $(ipt).attr('data-old'),
                            };
                        }
                    });

                    if ( empty(filename) ) {
                        filename = $('span.file').text();
                    };

                    _data['id'] = $('input[type=hidden]').data('id');
                    _data['dokumen'] = filename;
                    _data['tgl_berlaku'] = dateSQL($('[name=tanggal-berlaku]').data('DateTimePicker').date());
                    _data['harga_kontrak'] = numeral.unformat($('input[name=harga_kontrak]').val());
                    _data['lampirans'] = lampiran;

                    formData.append('data', JSON.stringify(_data));

                    doc.execute_edit(formData);
                }
            });
        };
    }, // end - edit

    execute_edit : function (formData) {
        $.ajax({
            url :'parameter/Doc/edit',
            type : 'post',
            data : formData,
            beforeSend : function(){
                showLoading();
            },
            success : function(data){
                hideLoading();
                if(data.status){
                    bootbox.alert(data.message, function(){
                        doc.getLists();
                        doc.load_form(data.content.id, data.content.tgl_mulai);
                    });
                }else{
                    bootbox.alert(data.message);
                }
            },
            contentType : false,
            processData : false,
        });
    }, // end - execute_edit

    ack : function(elm) {
        var tr = $(elm).closest('tr');
        var no_dokumen = $(tr).find('td#no_dokumen').html();
        var id = $(tr).find('td#no_dokumen').data('id');

        bootbox.confirm('Apakah anda yakin ingin melakukan ack?', function(result){
            if ( result ) {
                var params = {
                    'id' : id,
                    'no_dokumen' : no_dokumen,
                    'action' : 'ack',
                    'table' : 'doc'
                };

                doc.execute_ack(params);
            };
        });
    }, // end - ack

    execute_ack : function(params) {
        $.ajax({
            url : 'parameter/Doc/ack',
            data : {'params' : params},
            type : 'POST',
            dataType : 'JSON',
            beforeSend : function(){ showLoading(); },
            success : function(data){
                hideLoading();
                if (data.status) {
                    bootbox.alert(data.message, function(){
                        doc.getLists();
                    });
                } else {
                    alertDialog(data.message);
                }
            }
        });
    }, // end - execute_ack

    approve : function(elm) {
        var tr = $(elm).closest('tr');
        var no_dokumen = $(tr).find('td#no_dokumen').html();
        var id = $(tr).find('td#no_dokumen').data('id');

        bootbox.confirm('Apakah anda yakin ingin melakukan approve?', function(result){
            if ( result ) {
                var params = {
                    'id' : id,
                    'no_dokumen' : no_dokumen,
                    'action' : 'approve',
                    'table' : 'doc'
                };

                doc.execute_approve(params);
            };
        });
    }, // end - approve

    execute_approve : function(params) {
        $.ajax({
            url : 'parameter/Doc/approve',
            data : {'params' : params},
            type : 'POST',
            dataType : 'JSON',
            beforeSend : function(){ showLoading(); },
            success : function(data){
                hideLoading();
                if (data.status) {
                    bootbox.alert(data.message, function(){
                        doc.getLists();
                    });
                } else {
                    alertDialog(data.message);
                }
            }
        });
    }, // end - execute_approve
}

doc.start_up();