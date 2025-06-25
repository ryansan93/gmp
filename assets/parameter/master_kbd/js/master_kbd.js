var hrg_kesepakatan = {};
var kbd = {
    start_up : function () {
        // $('input').keyup(function(){
        //     $(this).val($(this).val().toUpperCase());
        // });

        $('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal], [data-tipe=decimal3],[data-tipe=decimal4], [data-tipe=number]').each(function(){
            $(this).priceFormat(Config[$(this).data('tipe')]);
        });

        $('#datetimepicker1').datetimepicker({
            locale: 'id',
            format: 'DD MMM Y'
        });

        kbd.setBindSHA1();
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

    addRowDoc : function(elm) {
        var div = $(elm).closest('div.row_doc');
        var div_hrg_sapronak_doc = $(div).closest('div.hrg_sapronak_doc');

        var clone_div = $(div).clone();
        $(clone_div).find('input, select').val('');

        $(div).find('div.btn_action').addClass('hide');

        $(div_hrg_sapronak_doc).append( clone_div );

        $('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal], [data-tipe=decimal3],[data-tipe=decimal4], [data-tipe=number]').each(function(){
            $(this).priceFormat(Config[$(this).data('tipe')]);
        });
    }, // end - addRowDoc

    removeRowDoc : function(elm) {
        var div = $(elm).closest('div.row_doc');
        var div_hrg_sapronak_doc = $(div).closest('div.hrg_sapronak_doc');

        if ( $(div_hrg_sapronak_doc).find('div.row_doc').length > 1 ) {
            $(div).remove();
            var div_last = $(div_hrg_sapronak_doc).find('div.row_doc').last();
            $(div_last).find('div.btn_action').removeClass('hide');
        }
    }, // end - removeRowDoc

    addSapronak : function(elm) {
        var div = $(elm).closest('div.hrg_sapronak');
        var div_sapronak = $(div).closest('div.sapronak');

        if ( $(div_sapronak).find('div.hrg_sapronak').length < 3 ) {
            var clone_div = $(div).clone();
            $(clone_div).find('input, select').val('');
            $(clone_div).find('a').text('');
            $(clone_div).find('a').attr('title', '');
            $(clone_div).find('a').addClass('hide');
            $(clone_div).find('a').attr('data-old', '');

            var div_row_doc = $(clone_div).find('div.row_doc').first();
            var div_hrg_sapronak_doc = $(div_row_doc).closest('div.hrg_sapronak_doc');
            $(clone_div).find('div.row_doc').remove();
            $(div_hrg_sapronak_doc).append(div_row_doc);

            $(div_sapronak).append( clone_div );

            $('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal], [data-tipe=decimal3],[data-tipe=decimal4], [data-tipe=number]').each(function(){
                $(this).priceFormat(Config[$(this).data('tipe')]);
            });
        } else {
            bootbox.alert('Tidak boleh melebihi 3 supplier.');
        }
    }, // end - addRowDoc

    removeSapronak : function(elm) {
        var div = $(elm).closest('div.hrg_sapronak');
        var div_sapronak = $(div).closest('div.sapronak');

        if ( $(div_sapronak).find('div.hrg_sapronak').length > 1 ) {
            $(div).remove();
        }
    }, // end - removeRowDoc

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

            kbd.load_form(v_id, tgl_mulai, resubmit);
        };
    }, // end - changeTabActive

    load_form: function(v_id = null, tgl_mulai = null, resubmit = null) {
        var div_action = $('div#action');

        $.ajax({
            url : 'parameter/MasterKBD/load_form',
            data : {
                'id' :  v_id,
                'resubmit' : resubmit
            },
            type : 'GET',
            dataType : 'HTML',
            beforeSend : function(){ showLoading(); },
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

                kbd.setBindSHA1();

                if ( !empty(resubmit) ) {
                    kbd.save_harga_kesepakatan();
                }

                hideLoading();
            },
        });
    }, // end - load_form

    getLists : function(keyword = null){
        $.ajax({
            url : 'parameter/MasterKBD/list_sk',
            data : {'keyword' : keyword},
            dataType : 'HTML',
            type : 'GET',
            beforeSend : function(){},
            success : function(data){
                $('table.tbl_sapronak_kesepakatan tbody').html(data);
            }
        });
    }, // end - getLists

    showNameFile : function(elm, isLable = 1) {
        var _label = $(elm).closest('label');
        var _a = _label.prev('a[name=dokumen]');
        _a.removeClass('hide');
        // var _allowtypes = $(elm).data('allowtypes').split('|');
        var _dataName = $(elm).data('name');
        var _allowtypes = ['doc', 'DOC', 'docx', 'DOCX', 'jpg', 'JPG', 'jpeg', 'JPEG', 'pdf', 'PDF', 'png', 'PNG'];
        var _type = $(elm).get(0).files[0]['name'].split('.').pop();
        var _namafile = $(elm).val();
        var _temp_url = URL.createObjectURL($(elm).get(0).files[0]);
        _namafile = _namafile.substring(_namafile.lastIndexOf("\\") + 1, _namafile.length);

        if (in_array(_type, _allowtypes)) {
            if (isLable == 1) {
                if (_a.length) {
                    _a.attr('title', _namafile);
                    _a.attr('href', _temp_url);
                    if ( _dataName == 'name' ) {
                        $(_a).text( _namafile );  
                    }
                }
            } else if (isLable == 0) {
                $(elm).closest('label').attr('title', _namafile);
            }
            $(elm).attr('data-filename', _namafile);
        } else {
            $(elm).val('');
            $(elm).closest('label').attr('title', '');
            $(elm).attr('data-filename', '');
            _a.addClass('hide');
            bootbox.alert('Format file tidak sesuai. Mohon attach ulang.');
        }
    }, // end - showNameFile

    load_form_spp : function() {
        var pola = $('select[name=pola_kemitraan]').val();
        var pola_name = $('select[name=pola_kemitraan]').find('option:selected').text();

        if ( pola == 1 ) {
            $('div.reguler').removeClass('hide');
            $('div.reguler').addClass('aktif');
            $('div.reguler input').addClass('aktif');
            $('div.bebas').addClass('hide');
            $('div.bebas').removeClass('aktif');
            $('div.bebas input').removeClass('aktif');
        } else {
            $('div.bebas').removeClass('hide');
            $('div.bebas').addClass('aktif');
            $('div.bebas input').addClass('aktif');
            $('div.reguler').addClass('hide');
            $('div.reguler').removeClass('aktif');
            $('div.reguler input').removeClass('aktif');
        };
    }, // end - load_form_spp

    hpp : function() {
        $('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal], [data-tipe=decimal3],[data-tipe=decimal4], [data-tipe=number]').each(function(){
            $(this).priceFormat(Config[$(this).data('tipe')]);
        });

        var biaya_opr = numeral.unformat( $('input.harga_sapronak[name=biaya_opr]').val() );
        var bb = numeral.unformat( $('input.performa[name=bb]').val() );
        var dh = numeral.unformat( $('input.performa[name=dh]').val() );

        var hrg_doc = numeral.unformat( $('input.harga_sapronak[name=doc_mitra]').val() );
        var voadip = numeral.unformat( $('input.harga_sapronak[name=voadip_mitra]').val() );
        var hrg_pakan1 = numeral.unformat( $('input.harga_sapronak[name=pakan1_mitra]').val() );
        var jml_pakan1 = numeral.unformat( $('input.performa[name=pakan1]').val() );
        var hrg_pakan2 = numeral.unformat( $('input.harga_sapronak[name=pakan2_mitra]').val() );
        var jml_pakan2 = numeral.unformat( $('input.performa[name=pakan2]').val() );
        var hrg_pakan3 = numeral.unformat( $('input.harga_sapronak[name=pakan3_mitra]').val() );
        var jml_pakan3 = numeral.unformat( $('input.performa[name=pakan3]').val() );

        var hpp = (hrg_doc + (jml_pakan1 * hrg_pakan1) + (jml_pakan2 * hrg_pakan2) + (jml_pakan3 * hrg_pakan3) + biaya_opr + voadip) / ((dh/100) * bb);

        $('input[name=hpp]').val( numeral.formatInt(hpp) );
    }, // end - hpp

    hitJmlPakan2 : function() {
        var tot_pakan = numeral.unformat( $('input.performa[name=kebutuhan_pakan]').val() );
        var pakan1 = numeral.unformat( $('input.performa[name=pakan1]').val() );
        var pakan2 = numeral.unformat( $('input.performa[name=pakan2]').val() );
        var pakan3 = numeral.unformat( $('input.performa[name=pakan3]').val() );

        var sisa = 0;
        sisa = tot_pakan - pakan1 - pakan3;
        $('input.performa[name=pakan2]').val(numeral.formatDec3(sisa));
    }, //end - hitJmlPakan2

    hitJmlPakan3 : function() {
        var tot_pakan = numeral.unformat( $('input.performa[name=kebutuhan_pakan]').val() );
        var pakan1 = numeral.unformat( $('input.performa[name=pakan1]').val() );
        var pakan2 = numeral.unformat( $('input.performa[name=pakan2]').val() );
        var pakan3 = numeral.unformat( $('input.performa[name=pakan3]').val() );

        var sisa = 0;
        sisa = tot_pakan - pakan1 - pakan2;
        $('input.performa[name=pakan3]').val(numeral.formatDec3(sisa));
        
    }, //end - hitJmlPakan3

    change_pakan : function(elm) {
        let kode = $(elm).val();
        let nama = $(elm).find('option:selected').text().trim();
        let href = $(elm).data('href');

        $('td.'+href).attr('data-kode', kode);
        $('td.'+href).html(nama);
    }, // end - change_pakan

    save_harga_kesepakatan : function() {
        var err_input = 0;
        var _mark = 0;
        hrg_kesepakatan = {};
        $.map( $('input[name=harga]'), function(input){
            if ( empty($(input).val()) ) {
                err_input++;
                $(input).parent().addClass('has-error');
            } else {
                $(input).parent().removeClass('has-error');
            };
        });

        $.map( $('[name=mark]:checked'), function(mark){
            _mark = 1;
        });        

        if ( err_input > 0 ) {
            $('div#modalHrgKesepakatan').css('visibility', 'hidden');
            bootbox.alert('Ada data yang belum diisi dengan lengkap. Mohon dilengkapi sebelum disimpan.', function(){
                $('div#modalHrgKesepakatan').css('visibility', '');
            });
        } else {
            if ( _mark == 0 ) {
                $('div#modalHrgKesepakatan').css('visibility', 'hidden');
                bootbox.alert('Belum ada data yang tercentang.', function(){
                    $('div#modalHrgKesepakatan').css('visibility', '');
                });
            } else {
                var data_kesepakatan = $.map( $('input[name=harga]'), function(input){
                    var tr = $(input).closest('tr.data');

                    var _hpp = 0;

                    if ( $(tr).find('[name=mark]').is(':checked') ) {
                        _hpp = 1;
                    };

                    var data = {
                        'range_min' : numeral.unformat( $(tr).find('input.range_min').val() ),
                        'range_max' :  numeral.unformat( $(tr).find('input.range_max').val() ),
                        'harga' :  numeral.unformat( $(tr).find('input[name=harga]').val() ),
                        'hpp' : _hpp
                    };

                    return data;
                });

                hrg_kesepakatan = data_kesepakatan;

                $('#modalHrgKesepakatan').modal('hide');
            };
        };
    }, // end - save_harga_kesepakatan

    save : function() {
        var _data = {}
        var err = 0;

        $.map($('[data-required=1]'), function(ipt){
            var td = $(ipt).closest('td');
            if ( empty($(ipt).val()) ) {
                $(ipt).parent().addClass('has-error');

                $(td).find('label[name=dokumen]').css('color','#a94442');
                $(td).find('i.glyphicon-paperclip ').css('color','#a94442');
                // err++;
            } else {
                $(ipt).parent().removeClass('has-error');
                $(td).find('label[name=dokumen]').css('color','#000');
                $(td).find('i.glyphicon-paperclip ').css('color','#000');
            };
        });

        $.map($('div.aktif').find('input:not(.file_lampiran), select, textarea'), function(ipt){
            if ( empty($(ipt).val()) ) {
                $(ipt).parent().addClass('has-error');
                err++;
            } else {
                $(ipt).parent().removeClass('has-error');
            };
        });

        if ( err > 0 ) {
            bootbox.alert('Ada data yang belum diisi dengan lengkap. Mohon dilengkapi sebelum disimpan.');
        } else {
            if ( $('div.perwakilan').find('input[name=mark]:checked').length > 0 ) {
                if ( empty(hrg_kesepakatan) ) {
                    $('button[name=hrg_kesepakatan]').parent().addClass('has-error');
                    bootbox.alert('Harga kesepakatan belum di isi.');
                } else {
                    $('button[name=hrg_kesepakatan]').parent().removeClass('has-error');
                    bootbox.confirm('Apakah anda yakin menyimpan harga sapronak dan kesepakatan ?', function(result){
                        if ( result ) {
                            _data['pola'] = $('select[name=pola_budidaya]').val();
                            _data['item_pola'] = $('select[name=item_pola]').val();
                            _data['perusahaan'] = $('select[name=perusahaan]').val();

                            var _filetmp = [];
                            var harga_sapronak = $.map( $('div.hrg_sapronak'), function(div) {
                                var doc = $.map( $(div).find('.row_doc'), function(row_doc) {
                                    var _doc = {
                                        'doc': $(row_doc).find('select').val(),
                                        'hrg_doc_supplier': numeral.unformat( $(row_doc).find('[name=harga_supplier]').val() ),
                                        'hrg_doc_peternak': numeral.unformat( $(row_doc).find('[name=harga_peternak]').val() ),
                                    };

                                    return _doc;
                                });

                                var _harga_sapronak = {
                                    'supplier' : $(div).find('.supplier').val(),
                                    'doc' : doc,
                                    'pakan1' : $(div).find('div.hrg_sapronak_pakan1 select').val(),
                                    'hrg_pakan1_supplier' : numeral.unformat( $(div).find('div.hrg_sapronak_pakan1 input[name=harga_supplier]').val() ),
                                    'hrg_pakan1_peternak' : numeral.unformat( $(div).find('div.hrg_sapronak_pakan1 input[name=harga_peternak]').val() ),
                                    'pakan2' : $(div).find('div.hrg_sapronak_pakan2 select').val(),
                                    'hrg_pakan2_supplier' : numeral.unformat( $(div).find('div.hrg_sapronak_pakan2 input[name=harga_supplier]').val() ),
                                    'hrg_pakan2_peternak' : numeral.unformat( $(div).find('div.hrg_sapronak_pakan2 input[name=harga_peternak]').val() ),
                                    'pakan3' : $(div).find('div.hrg_sapronak_pakan3 select').val(),
                                    'hrg_pakan3_supplier' : numeral.unformat( $(div).find('div.hrg_sapronak_pakan3 input[name=harga_supplier]').val() ),
                                    'hrg_pakan3_peternak' : numeral.unformat( $(div).find('div.hrg_sapronak_pakan3 input[name=harga_peternak]').val() ),
                                    'doc_dok' : $(div).find('a.doc').attr('title'),
                                    'pakan_dok' : $(div).find('a.pakan').attr('title'),
                                };

                                if ( !empty($(div).find('.file_lampiran_doc').val()) ) {
                                    var key = $(div).find('.supplier').val()+'_DOC';
                                    var _file = {
                                        'key': key,
                                        'file' : $(div).find('.file_lampiran_doc').get(0).files[0]
                                    };
                                    _filetmp.push( _file );
                                }

                                if ( !empty($(div).find('.file_lampiran_pakan').val()) ) {
                                    var key = $(div).find('.supplier').val()+'_PAKAN';
                                    var _file = {
                                        'key': key,
                                        'file' : $(div).find('.file_lampiran_pakan').get(0).files[0]
                                    };
                                    _filetmp.push( _file );
                                }

                                return _harga_sapronak;
                            });

                            var performa = {
                                'dh' : numeral.unformat( $('input.performa[name=dh]').val() ),
                                'bb' : numeral.unformat( $('input.performa[name=bb]').val() ),
                                'fcr' : numeral.unformat( $('input.performa[name=fcr]').val() ),
                                'umur' : numeral.unformat( $('input.performa[name=umur]').val() ),
                                'ip' : numeral.unformat( $('input.performa[name=ip]').val() ),
                                'ie' : numeral.unformat( $('input.performa[name=ie]').val() ),
                                'kebutuhan_pakan' : numeral.unformat( $('input.performa[name=kebutuhan_pakan]').val() ),
                                'pakan1' : numeral.unformat( $('input.performa[name=pakan1]').val() ),
                                'pakan2' : numeral.unformat( $('input.performa[name=pakan2]').val() ),
                                'pakan3' : numeral.unformat( $('input.performa[name=pakan3]').val() ),
                                'kode_pakan1' : $('select.pakan1').val(),
                                'kode_pakan2' : $('select.pakan2').val(),
                                'kode_pakan3' : $('select.pakan3').val()
                            };

                            var bonus = $.map( $('div.aktif table.bonus').find('tr.data'), function(tr){
                                var data = {
                                    'pola_kemitraan' : $('select[name=pola_kemitraan]').val(),
                                    'ip_awal' : ($(tr).find('input.ip_awal').length > 0) ? numeral.unformat( $(tr).find('input.ip_awal').val() ) : 0,
                                    'ip_akhir' : ($(tr).find('input.ip_akhir').length > 0) ? numeral.unformat( $(tr).find('input.ip_akhir').val() ) : 0,
                                    'bonus_kematian' : numeral.unformat( $(tr).find('input.bonus_kematian').val() ),
                                    'bonus_harga' : numeral.unformat( $(tr).find('input.bonus_harga').val() )
                                };

                                return data;
                            } );

                            var bonus_fcr = $.map( $('div.aktif table.bonus_fcr').find('tr.data'), function(tr){
                                var data = {
                                    'range_awal' : numeral.unformat( $(tr).find('td.range_awal').html() ),
                                    'range_akhir' : numeral.unformat( $(tr).find('td.range_akhir').html() ),
                                    'tarif' : numeral.unformat( $(tr).find('input.tarif').val() )
                                };

                                return data;
                            } );

                            var perwakilan = $.map( $('div.perwakilan').find('input[name=mark]:checked'), function(check){
                                var data = {
                                    'id' : $(check).data('id'),
                                    'nama' : $(check).data('name')
                                };

                                return data;
                            });

                            var bonus_insentif_listrik = $.map( $('div.aktif table.bonus_insentif_listrik').find('tr.data'), function(tr){
                                var data = {
                                    'range_awal' : ($(tr).find('input.range_awal').length > 0) ? numeral.unformat( $(tr).find('input.range_awal').val() ) : 0,
                                    'range_akhir' : ($(tr).find('input.range_akhir').length > 0) ? numeral.unformat( $(tr).find('input.range_akhir').val() ) : 0,
                                    'tarif' : numeral.unformat( $(tr).find('input.tarif').val() )
                                };

                                return data;
                            } );

                            var bonus_insentif_khusus = $.map( $('div.aktif table.bonus_insentif_khusus').find('tr.data'), function(tr){
                                var data = {
                                    'box_awal' : ($(tr).find('input.box_awal').length > 0) ? numeral.unformat( $(tr).find('input.box_awal').val() ) : 0,
                                    'box_akhir' : ($(tr).find('input.box_akhir').length > 0) ? numeral.unformat( $(tr).find('input.box_akhir').val() ) : 0,
                                    'bonus' : numeral.unformat( $(tr).find('input.bonus').val() )
                                };

                                return data;
                            } );

                            _data['dokumen'] = $('a[name=dokumen]').text();
                            _data['tgl_berlaku'] = dateSQL($('[name=tanggal-berlaku]').data('DateTimePicker').date());
                            _data['action'] = 'submit';
                            _data['table'] = 'sapronak_kesepakatan';
                            _data['harga_kesepakatan'] = hrg_kesepakatan;
                            _data['harga_sapronak'] = harga_sapronak;
                            _data['performa'] = performa;
                            _data['bonus'] = bonus;
                            _data['bonus_fcr'] = bonus_fcr;
                            _data['bonus_insentif_listrik'] = bonus_insentif_listrik;
                            _data['bonus_insentif_khusus'] = bonus_insentif_khusus;
                            _data['note'] = $('textarea').val();
                            _data['perwakilan'] = perwakilan;

                            kbd.execute_save(_data, _filetmp);
                        }
                    });
                };
            } else {
                bootbox.alert('Data koordinator wilayah belum ada yang di pilih.');
            };
        };
    }, // end - save_harga_sk

    execute_save : function (data, file_tmp) {
        var div_tab_pane = $('div.tab-pane');

        var formData = new FormData();

        formData.append("data", JSON.stringify(data));
        for (var i = 0; i < file_tmp.length; i++) {
            formData.append('files['+file_tmp[i].key+']', file_tmp[i].file);
        };

        $.ajax({
            url: 'parameter/MasterKBD/save_data',
            dataType: 'json',
            type: 'post',
            async:false,
            processData: false,
            contentType: false,
            data: formData,
            beforeSend: function() {
                showLoading();
            },
            success: function(data) {
                hideLoading();
                if ( data.status == 1 ) {
                    bootbox.alert(data.message, function(){
                        kbd.getLists();
                        kbd.load_form(data.content.id, data.content.tgl_mulai);
                    });
                } else {
                    bootbox.alert(data.message);
                }
            }
        });
    }, // end - execute_save

    edit : function() {
        var _data = {}
        var err = 0;

        $.map($('[data-required=1]'), function(ipt){
            var td = $(ipt).closest('td');
            if ( empty($(ipt).val()) ) {
                $(ipt).parent().addClass('has-error');

                $(td).find('label[name=dokumen]').css('color','#a94442');
                $(td).find('i.glyphicon-paperclip ').css('color','#a94442');
                // err++;
            } else {
                $(ipt).parent().removeClass('has-error');
                $(td).find('label[name=dokumen]').css('color','#000');
                $(td).find('i.glyphicon-paperclip ').css('color','#000');
            };
        });

        $.map($('div.aktif').find('input, select, textarea'), function(ipt){
            if ( empty($(ipt).val()) ) {
                $(ipt).parent().addClass('has-error');
                err++;
            } else {
                $(ipt).parent().removeClass('has-error');
            };
        });

        if ( err > 0 ) {
            bootbox.alert('Ada data yang belum diisi dengan lengkap. Mohon dilengkapi sebelum disimpan.');
        } else {
            if ( $('[name=mark]:checked').length > 0 ) {
                if ( empty(hrg_kesepakatan) ) {
                    $('button[name=hrg_kesepakatan]').parent().addClass('has-error');
                    bootbox.alert('Harga kesepakatan belum di isi.');
                } else {
                    $('button[name=hrg_kesepakatan]').parent().removeClass('has-error');
                    bootbox.confirm('Apakah anda yakin mengubah harga sapronak dan kesepakatan ?', function(result){
                        if ( result ) {
                            _data['pola'] = $('select[name=pola_budidaya]').val();
                            _data['item_pola'] = $('select[name=item_pola]').val();
                            _data['perusahaan'] = $('select[name=perusahaan]').val();

                            var _filetmp = [];
                            var harga_sapronak = $.map( $('div.hrg_sapronak'), function(div) {
                                var doc = $.map( $(div).find('.row_doc'), function(row_doc) {
                                    var _doc = {
                                        'doc': $(row_doc).find('select').val(),
                                        'hrg_doc_supplier': numeral.unformat( $(row_doc).find('[name=harga_supplier]').val() ),
                                        'hrg_doc_peternak': numeral.unformat( $(row_doc).find('[name=harga_peternak]').val() ),
                                    };

                                    return _doc;
                                });

                                var _harga_sapronak = {
                                    'supplier' : $(div).find('.supplier').val(),
                                    'doc' : doc,
                                    'pakan1' : $(div).find('div.hrg_sapronak_pakan1 select').val(),
                                    'hrg_pakan1_supplier' : numeral.unformat( $(div).find('div.hrg_sapronak_pakan1 input[name=harga_supplier]').val() ),
                                    'hrg_pakan1_peternak' : numeral.unformat( $(div).find('div.hrg_sapronak_pakan1 input[name=harga_peternak]').val() ),
                                    'pakan2' : $(div).find('div.hrg_sapronak_pakan2 select').val(),
                                    'hrg_pakan2_supplier' : numeral.unformat( $(div).find('div.hrg_sapronak_pakan2 input[name=harga_supplier]').val() ),
                                    'hrg_pakan2_peternak' : numeral.unformat( $(div).find('div.hrg_sapronak_pakan2 input[name=harga_peternak]').val() ),
                                    'pakan3' : $(div).find('div.hrg_sapronak_pakan3 select').val(),
                                    'hrg_pakan3_supplier' : numeral.unformat( $(div).find('div.hrg_sapronak_pakan3 input[name=harga_supplier]').val() ),
                                    'hrg_pakan3_peternak' : numeral.unformat( $(div).find('div.hrg_sapronak_pakan3 input[name=harga_peternak]').val() ),
                                    'doc_dok' : $(div).find('a.doc').attr('title'),
                                    'pakan_dok' : $(div).find('a.pakan').attr('title'),
                                    'doc_dok_old' : $(div).find('a.doc').data('old'),
                                    'pakan_dok_old' : $(div).find('a.pakan').data('old')
                                };

                                if ( !empty($(div).find('.file_lampiran_doc').val()) ) {
                                    var key = $(div).find('.supplier').val()+'_DOC';
                                    var _file = {
                                        'key': key,
                                        'file' : $(div).find('.file_lampiran_doc').get(0).files[0]
                                    };
                                    _filetmp.push( _file );
                                }

                                if ( !empty($(div).find('.file_lampiran_pakan').val()) ) {
                                    var key = $(div).find('.supplier').val()+'_PAKAN';
                                    var _file = {
                                        'key': key,
                                        'file' : $(div).find('.file_lampiran_pakan').get(0).files[0]
                                    };
                                    _filetmp.push( _file );
                                }

                                return _harga_sapronak;
                            });

                            var performa = {
                                'dh' : numeral.unformat( $('input.performa[name=dh]').val() ),
                                'bb' : numeral.unformat( $('input.performa[name=bb]').val() ),
                                'fcr' : numeral.unformat( $('input.performa[name=fcr]').val() ),
                                'umur' : numeral.unformat( $('input.performa[name=umur]').val() ),
                                'ip' : numeral.unformat( $('input.performa[name=ip]').val() ),
                                'ie' : numeral.unformat( $('input.performa[name=ie]').val() ),
                                'kebutuhan_pakan' : numeral.unformat( $('input.performa[name=kebutuhan_pakan]').val() ),
                                'pakan1' : numeral.unformat( $('input.performa[name=pakan1]').val() ),
                                'pakan2' : numeral.unformat( $('input.performa[name=pakan2]').val() ),
                                'pakan3' : numeral.unformat( $('input.performa[name=pakan3]').val() ),
                                'kode_pakan1' : $('select.pakan1').val(),
                                'kode_pakan2' : $('select.pakan2').val(),
                                'kode_pakan3' : $('select.pakan3').val()
                            };

                            var bonus = $.map( $('div.aktif table.bonus').find('tr.data'), function(tr){
                                var data = {
                                    'pola_kemitraan' : $('select[name=pola_kemitraan]').val(),
                                    'ip_awal' : ($(tr).find('input.ip_awal').length > 0) ? numeral.unformat( $(tr).find('input.ip_awal').val() ) : 0,
                                    'ip_akhir' : ($(tr).find('input.ip_akhir').length > 0) ? numeral.unformat( $(tr).find('input.ip_akhir').val() ) : 0,
                                    'bonus_kematian' : numeral.unformat( $(tr).find('input.bonus_kematian').val() ),
                                    'bonus_harga' : numeral.unformat( $(tr).find('input.bonus_harga').val() )
                                };

                                return data;
                            } );

                            var bonus_fcr = $.map( $('div.aktif table.bonus_fcr').find('tr.data'), function(tr){
                                var data = {
                                    'range_awal' : numeral.unformat( $(tr).find('td.range_awal').html() ),
                                    'range_akhir' : numeral.unformat( $(tr).find('td.range_akhir').html() ),
                                    'tarif' : numeral.unformat( $(tr).find('input.tarif').val() )
                                };

                                return data;
                            } );

                            var perwakilan = $.map( $('div.perwakilan').find('input[name=mark]:checked'), function(check){
                                var data = {
                                    'id' : $(check).data('id'),
                                    'nama' : $(check).data('name')
                                };

                                return data;
                            });

                            var bonus_insentif_listrik = $.map( $('div.aktif table.bonus_insentif_listrik').find('tr.data'), function(tr){
                                var data = {
                                    'range_awal' : ($(tr).find('input.range_awal').length > 0) ? numeral.unformat( $(tr).find('input.range_awal').val() ) : 0,
                                    'range_akhir' : ($(tr).find('input.range_akhir').length > 0) ? numeral.unformat( $(tr).find('input.range_akhir').val() ) : 0,
                                    'tarif' : numeral.unformat( $(tr).find('input.tarif').val() )
                                };

                                return data;
                            } );

                            var bonus_insentif_khusus = $.map( $('div.aktif table.bonus_insentif_khusus').find('tr.data'), function(tr){
                                var data = {
                                    'box_awal' : ($(tr).find('input.box_awal').length > 0) ? numeral.unformat( $(tr).find('input.box_awal').val() ) : 0,
                                    'box_akhir' : ($(tr).find('input.box_akhir').length > 0) ? numeral.unformat( $(tr).find('input.box_akhir').val() ) : 0,
                                    'bonus' : numeral.unformat( $(tr).find('input.bonus').val() )
                                };

                                return data;
                            } );

                            _data['dokumen'] = $('a[name=dokumen]').text();
                            _data['tgl_berlaku'] = dateSQL($('[name=tanggal-berlaku]').data('DateTimePicker').date());
                            _data['id'] = $('input[type=hidden]').data('id');
                            _data['action'] = 'submit';
                            _data['table'] = 'sapronak_kesepakatan';
                            _data['harga_kesepakatan'] = hrg_kesepakatan;
                            _data['harga_sapronak'] = harga_sapronak;
                            _data['performa'] = performa;
                            _data['bonus'] = bonus;
                            _data['bonus_fcr'] = bonus_fcr;
                            _data['bonus_insentif_listrik'] = bonus_insentif_listrik;
                            _data['bonus_insentif_khusus'] = bonus_insentif_khusus;
                            _data['note'] = $('textarea').val();
                            _data['perwakilan'] = perwakilan;

                            kbd.execute_edit(_data, _filetmp);
                        }
                    });
                };
            };
        };
    }, // end - edit

    execute_edit : function (data, file_tmp) {
        var div_tab_pane = $('div.tab-pane');

        var formData = new FormData();

        formData.append("data", JSON.stringify(data));
        for (var i = 0; i < file_tmp.length; i++) {
            formData.append('files['+file_tmp[i].key+']', file_tmp[i].file);
        };

        $.ajax({
            url: 'parameter/MasterKBD/edit_data',
            dataType: 'json',
            type: 'post',
            async:false,
            processData: false,
            contentType: false,
            data: formData,
            beforeSend: function() {
                showLoading();
            },
            success: function(data) {
                hideLoading();
                if ( data.status == 1 ) {
                    bootbox.alert(data.message, function(){
                        kbd.getLists();
                        kbd.load_form(data.content.id, data.content.tgl_mulai);
                    });
                } else {
                    bootbox.alert(data.message);
                }
            }
        });
    }, // end - execute_edit

    save_copy : function() {
        var _data = {}
        var err = 0;

        $.map($('[data-required=1]'), function(ipt){
            var td = $(ipt).closest('td');
            if ( empty($(ipt).val()) ) {
                $(ipt).parent().addClass('has-error');

                $(td).find('label[name=dokumen]').css('color','#a94442');
                $(td).find('i.glyphicon-paperclip ').css('color','#a94442');
                err++;
            } else {
                $(ipt).parent().removeClass('has-error');
                $(td).find('label[name=dokumen]').css('color','#000');
                $(td).find('i.glyphicon-paperclip ').css('color','#000');
            };
        });

        $.map($('div.aktif').find('input, select, textarea'), function(ipt){
            if ( empty($(ipt).val()) ) {
                $(ipt).parent().addClass('has-error');
                err++;
            } else {
                $(ipt).parent().removeClass('has-error');
            };
        });

        if ( err > 0 ) {
            bootbox.alert('Ada data yang belum diisi dengan lengkap. Mohon dilengkapi sebelum disimpan.');
        } else {
            if ( $('div.perwakilan').find('input[name=mark]:checked').length > 0 ) {
                if ( empty(hrg_kesepakatan) ) {
                    $('button[name=hrg_kesepakatan]').parent().addClass('has-error');
                    bootbox.alert('Harga kesepakatan belum di isi.');
                } else {
                    $('button[name=hrg_kesepakatan]').parent().removeClass('has-error');
                    bootbox.confirm('Apakah anda yakin menyimpan harga sapronak dan kesepakatan ?', function(result){
                        if ( result ) {
                            _data['pola'] = $('select[name=pola_budidaya]').val();
                            _data['item_pola'] = $('select[name=item_pola]').val();
                            _data['perusahaan'] = $('select[name=perusahaan]').val();

                            var _filetmp = [];
                            var harga_sapronak = $.map( $('div.hrg_sapronak'), function(div) {
                                var doc = $.map( $(div).find('.row_doc'), function(row_doc) {
                                    var _doc = {
                                        'doc': $(row_doc).find('select').val(),
                                        'hrg_doc_supplier': numeral.unformat( $(row_doc).find('[name=harga_supplier]').val() ),
                                        'hrg_doc_peternak': numeral.unformat( $(row_doc).find('[name=harga_peternak]').val() ),
                                    };

                                    return _doc;
                                });

                                var _harga_sapronak = {
                                    'supplier' : $(div).find('.supplier').val(),
                                    'doc' : doc,
                                    'pakan1' : $(div).find('div.hrg_sapronak_pakan1 select').val(),
                                    'hrg_pakan1_supplier' : numeral.unformat( $(div).find('div.hrg_sapronak_pakan1 input[name=harga_supplier]').val() ),
                                    'hrg_pakan1_peternak' : numeral.unformat( $(div).find('div.hrg_sapronak_pakan1 input[name=harga_peternak]').val() ),
                                    'pakan2' : $(div).find('div.hrg_sapronak_pakan2 select').val(),
                                    'hrg_pakan2_supplier' : numeral.unformat( $(div).find('div.hrg_sapronak_pakan2 input[name=harga_supplier]').val() ),
                                    'hrg_pakan2_peternak' : numeral.unformat( $(div).find('div.hrg_sapronak_pakan2 input[name=harga_peternak]').val() ),
                                    'pakan3' : $(div).find('div.hrg_sapronak_pakan3 select').val(),
                                    'hrg_pakan3_supplier' : numeral.unformat( $(div).find('div.hrg_sapronak_pakan3 input[name=harga_supplier]').val() ),
                                    'hrg_pakan3_peternak' : numeral.unformat( $(div).find('div.hrg_sapronak_pakan3 input[name=harga_peternak]').val() ),
                                    'doc_dok' : $(div).find('a.doc').attr('title'),
                                    'pakan_dok' : $(div).find('a.pakan').attr('title'),
                                    'doc_dok_old' : $(div).find('a.doc').data('old'),
                                    'pakan_dok_old' : $(div).find('a.pakan').data('old')
                                };

                                if ( !empty($(div).find('.file_lampiran_doc').val()) ) {
                                    var key = $(div).find('.supplier').val()+'_DOC';
                                    var _file = {
                                        'key': key,
                                        'file' : $(div).find('.file_lampiran_doc').get(0).files[0]
                                    };
                                    _filetmp.push( _file );
                                }

                                if ( !empty($(div).find('.file_lampiran_pakan').val()) ) {
                                    var key = $(div).find('.supplier').val()+'_PAKAN';
                                    var _file = {
                                        'key': key,
                                        'file' : $(div).find('.file_lampiran_pakan').get(0).files[0]
                                    };
                                    _filetmp.push( _file );
                                }

                                return _harga_sapronak;
                            });

                            var performa = {
                                'dh' : numeral.unformat( $('input.performa[name=dh]').val() ),
                                'bb' : numeral.unformat( $('input.performa[name=bb]').val() ),
                                'fcr' : numeral.unformat( $('input.performa[name=fcr]').val() ),
                                'umur' : numeral.unformat( $('input.performa[name=umur]').val() ),
                                'ip' : numeral.unformat( $('input.performa[name=ip]').val() ),
                                'ie' : numeral.unformat( $('input.performa[name=ie]').val() ),
                                'kebutuhan_pakan' : numeral.unformat( $('input.performa[name=kebutuhan_pakan]').val() ),
                                'pakan1' : numeral.unformat( $('input.performa[name=pakan1]').val() ),
                                'pakan2' : numeral.unformat( $('input.performa[name=pakan2]').val() ),
                                'pakan3' : numeral.unformat( $('input.performa[name=pakan3]').val() ),
                                'kode_pakan1' : $('select.pakan1').val(),
                                'kode_pakan2' : $('select.pakan2').val(),
                                'kode_pakan3' : $('select.pakan3').val()
                            };

                            var bonus = $.map( $('div.aktif table.bonus').find('tr.data'), function(tr){
                                var data = {
                                    'pola_kemitraan' : $('select[name=pola_kemitraan]').val(),
                                    'ip_awal' : numeral.unformat( $(tr).find('td.ip_awal').html() ),
                                    'ip_akhir' : numeral.unformat( $(tr).find('td.ip_akhir').html() ),
                                    'bonus_kematian' : numeral.unformat( $(tr).find('input.bonus_kematian').val() ),
                                    'bonus_harga' : numeral.unformat( $(tr).find('input.bonus_harga').val() )
                                };

                                return data;
                            } );

                            var bonus_fcr = $.map( $('div.aktif table.bonus_fcr').find('tr.data'), function(tr){
                                var data = {
                                    'range_awal' : numeral.unformat( $(tr).find('td.range_awal').html() ),
                                    'range_akhir' : numeral.unformat( $(tr).find('td.range_akhir').html() ),
                                    'tarif' : numeral.unformat( $(tr).find('input.tarif').val() )
                                };

                                return data;
                            } );

                            var perwakilan = $.map( $('div.perwakilan').find('input[name=mark]:checked'), function(check){
                                var data = {
                                    'id' : $(check).data('id'),
                                    'nama' : $(check).data('name')
                                };

                                return data;
                            });

                            var bonus_insentif_listrik = $.map( $('div.aktif table.bonus_insentif_listrik').find('tr.data'), function(tr){
                                var data = {
                                    'range_awal' : numeral.unformat( $(tr).find('td.range_awal input').val() ),
                                    'range_akhir' : numeral.unformat( $(tr).find('td.range_akhir').html() ),
                                    'tarif' : numeral.unformat( $(tr).find('input.tarif').val() )
                                };

                                return data;
                            } );

                            _data['dokumen'] = $('a[name=dokumen]').text();
                            _data['tgl_berlaku'] = dateSQL($('[name=tanggal-berlaku]').data('DateTimePicker').date());
                            _data['id'] = $('input[type=hidden]').data('id');
                            _data['action'] = 'submit';
                            _data['table'] = 'sapronak_kesepakatan';
                            _data['harga_kesepakatan'] = hrg_kesepakatan;
                            _data['harga_sapronak'] = harga_sapronak;
                            _data['performa'] = performa;
                            _data['bonus'] = bonus;
                            _data['bonus_fcr'] = bonus_fcr;
                            _data['bonus_insentif_listrik'] = bonus_insentif_listrik;
                            _data['note'] = $('textarea').val();
                            _data['perwakilan'] = perwakilan;

                            kbd.execute_save_copy(_data, _filetmp);
                        }
                    });
                };
            } else {
                bootbox.alert('Data koordinator wilayah belum ada yang di pilih.');
            };
        };
    }, // end - save_copy

    execute_save_copy : function (data, file_tmp) {
        var div_tab_pane = $('div.tab-pane');

        var formData = new FormData();

        formData.append("data", JSON.stringify(data));
        for (var i = 0; i < file_tmp.length; i++) {
            formData.append('files[]', file_tmp[i]);
        };

        $.ajax({
            url: 'parameter/MasterKBD/save_copy',
            dataType: 'json',
            type: 'post',
            async:false,
            processData: false,
            contentType: false,
            data: formData,
            beforeSend: function() {
                showLoading();
            },
            success: function(data) {
                hideLoading();
                if ( data.status == 1 ) {
                    bootbox.alert(data.message, function(){
                        kbd.getLists();
                        kbd.load_form(data.content.id, data.content.tgl_mulai);
                    });
                } else {
                    bootbox.alert(data.message);
                }
            }
        });
    }, // end - execute_save_copy

    ack : function () {
        var id_sk = $('input#id').data('idsk');

        bootbox.confirm('Apakah anda yakin ingin ACK data ?', function(result){
            if (result) {
                $.ajax({
                    url: 'parameter/MasterKBD/ack_data',
                    data : {'params' :  id_sk},
                    type : 'POST',
                    dataType : 'JSON',
                    beforeSend: function() {
                        showLoading();
                    },
                    success: function(data) {
                        hideLoading();
                        if ( data.status == 1 ) {
                            bootbox.alert(data.message, function(){
                                kbd.getLists();
                                kbd.load_form(data.content.id, data.content.tgl_mulai);
                            });
                        } else {
                            bootbox.alert(data.message);
                        }
                    }
                });
            };
        });

    }, // end - ack

    approve : function () {
        var id_sk = $('input#id').data('idsk');

        bootbox.confirm('Apakah anda yakin ingin APPROVE data ?', function(result){
            if (result) {
                $.ajax({
                    url: 'parameter/MasterKBD/approve_data',
                    data : {'params' :  id_sk},
                    type : 'POST',
                    dataType : 'JSON',
                    beforeSend: function() {
                        showLoading();
                    },
                    success: function(data) {
                        hideLoading();
                        if ( data.status == 1 ) {
                            bootbox.alert(data.message, function(){
                                kbd.getLists();
                                kbd.load_form(data.content.id, data.content.tgl_mulai);
                            });
                        } else {
                            bootbox.alert(data.message);
                        }
                    }
                });
            };
        });
    }, // end - approve
}

kbd.start_up();