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

                kbd.setBindSHA1();

                if ( !empty(resubmit) ) {
                    kbd.save_harga_kesepakatan();
                }
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
        var _allowtypes = $(elm).data('allowtypes').split('|');
        var _type = $(elm).get(0).files[0]['name'].split('.').pop();
        var _namafile = $(elm).val();
        var _temp_url = URL.createObjectURL($(elm).get(0).files[0]);
        _namafile = _namafile.substring(_namafile.lastIndexOf("\\") + 1, _namafile.length);

        if (in_array(_type, _allowtypes)) {
            if (isLable == 1) {
                if (_a.length) {
                    _a.attr('title', _namafile);
                    _a.attr('href', _temp_url);
                }
            }else if (isLable == 0) {
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
            if ( $('[name=mark]:checked').length > 0 ) {
                if ( empty(hrg_kesepakatan) ) {
                    $('button[name=hrg_kesepakatan]').parent().addClass('has-error');
                    bootbox.alert('Harga kesepakatan belum di isi.');
                } else {
                    $('button[name=hrg_kesepakatan]').parent().removeClass('has-error');
                    bootbox.confirm('Apakah anda yakin menyimpan harga sapronak dan kesepakatan ?', function(result){
                        if ( result ) {
                            _data['pola'] = $('select[name=pola_budidaya]').val();
                            _data['item_pola'] = $('select[name=item_pola]').val();

                            var harga_sapronak = {
                                'biaya_opr' : numeral.unformat( $('input.harga_sapronak[name=biaya_opr]').val() ),
                                'jaminan' : numeral.unformat( $('input.harga_sapronak[name=jaminan_keuntungan]').val() ),
                                // 'oa_doc' : numeral.unformat( $('input.harga_sapronak[name=oa_doc]').val() ),
                                // 'oa_pakan' : numeral.unformat( $('input.harga_sapronak[name=oa_pakan]').val() ),

                                'voadip' : numeral.unformat( $('input.harga_sapronak[name=voadip_supl]').val() ),
                                'doc' : numeral.unformat( $('input.harga_sapronak[name=doc_supl]').val() ),
                                'pakan1' : numeral.unformat( $('input.harga_sapronak[name=pakan1_supl]').val() ),
                                'pakan2' : numeral.unformat( $('input.harga_sapronak[name=pakan2_supl]').val() ),
                                'pakan3' : numeral.unformat( $('input.harga_sapronak[name=pakan3_supl]').val() ),

                                'voadip_mitra' : numeral.unformat( $('input.harga_sapronak[name=voadip_mitra]').val() ),
                                'doc_mitra' : numeral.unformat( $('input.harga_sapronak[name=doc_mitra]').val() ),
                                'pakan1_mitra' : numeral.unformat( $('input.harga_sapronak[name=pakan1_mitra]').val() ),
                                'pakan2_mitra' : numeral.unformat( $('input.harga_sapronak[name=pakan2_mitra]').val() ),
                                'pakan3_mitra' : numeral.unformat( $('input.harga_sapronak[name=pakan3_mitra]').val() ),

                                // 'oa_pakan_dok' : $('a.oa_pakan').attr('title'),
                                // 'oa_doc_dok' : $('a.oa_doc').attr('title'),
                                'voadip_dok' : $('a.voadip').attr('title'),
                                'doc_dok' : $('a.doc').attr('title'),
                                'pakan1_dok' : $('a.pakan1').attr('title')
                            };

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
                                'pakan3' : numeral.unformat( $('input.performa[name=pakan3]').val() )
                            };

                            var standar_pakan = $.map( $('div.aktif table.range').find('tr.data'), function(tr){
                                var data = {
                                    'bb_awal' : numeral.unformat( $(tr).find('td.bb_awal').html() ),
                                    'bb_akhir' : numeral.unformat( $(tr).find('td.bb_akhir').html() ),
                                    'standar_min' : numeral.unformat( $(tr).find('input.standar_min').val() )
                                };

                                return data;
                            } );

                            var selisih_pakan = $.map( $('div.aktif table.selisih').find('tr.data'), function(tr){
                                var data = {
                                    'range_awal' : numeral.unformat( $(tr).find('td.range_awal').html() ),
                                    'range_akhir' : numeral.unformat( $(tr).find('td.range_akhir').html() ),
                                    'selisih' : numeral.unformat( $(tr).find('input.selisih').val() ),
                                    'tarif' : numeral.unformat( $(tr).find('input.tarif').val() )
                                };

                                return data;
                            } );

                            var perwakilan = $.map( $('[name=mark]:checked'), function(check){
                                var data = {
                                    'id' : $(check).data('id'),
                                    'nama' : $(check).data('name')
                                };

                                return data;
                            });

                            var hitung_budidaya = {
                                'pola_kemitraan' : $('select[name=pola_kemitraan]').val(),
                                'text_pola_kemitraan' : $('select[name=pola_kemitraan] :selected').text().trim(),
                                'bonus_fcr' : $('input[name=bonus_fcr]').val(),
                                'bonus_dh' : $('input[name=bonus_dh]').val(),
                                'bonus_ch' : $('input[name=bonus_ch]').val(),
                                'bonus_bb' : $('input[name=bonus_bb]').val(),
                                'bonus_ip' : $('input[name=bonus_ip]').val()
                            };

                            var _filetmp = [];
                            var lampiran = $.map( $('input:file'), function(ipt){
                                if (!empty( $(ipt).val() )) {
                                    var __file = $(ipt).get(0).files[0];
                                    _filetmp.push( $(ipt).get(0).files[0] );
                                    return {
                                        'id' : $(ipt).closest('label').attr('data-idnama'),
                                        'name' : __file.name,
                                        'sha1' : $(ipt).attr('data-sha1'),
                                    };
                                }
                            });

                            _data['dokumen'] = $('a[name=dokumen]').text();
                            _data['tgl_berlaku'] = dateSQL($('[name=tanggal-berlaku]').data('DateTimePicker').date());
                            _data['action'] = 'submit';
                            _data['table'] = 'sapronak_kesepakatan';
                            _data['harga_kesepakatan'] = hrg_kesepakatan;
                            _data['harga_sapronak'] = harga_sapronak;
                            _data['performa'] = performa;
                            _data['standar_pakan'] = standar_pakan;
                            _data['selisih_pakan'] = selisih_pakan;
                            _data['note'] = $('textarea').val();
                            _data['perwakilan'] = perwakilan;
                            _data['hitung_budidaya'] = hitung_budidaya
                            _data['lampirans'] = lampiran;

                            kbd.execute_save(_data, _filetmp);
                        }
                    });
                };
            } else {
                $('button[name=hrg_kesepakatan]').parent().removeClass('has-error');
                bootbox.confirm('Apakah anda yakin menyimpan harga sapronak dan kesepakatan ?', function(result){
                    if ( result ) {
                        _data['pola'] = $('select[name=pola_budidaya]').val();
                        _data['item_pola'] = $('select[name=item_pola]').val();

                        var harga_sapronak = {
                            'biaya_opr' : numeral.unformat( $('input.harga_sapronak[name=biaya_opr]').val() ),
                            'jaminan' : numeral.unformat( $('input.harga_sapronak[name=jaminan_keuntungan]').val() ),
                            // 'oa_doc' : numeral.unformat( $('input.harga_sapronak[name=oa_doc]').val() ),
                            // 'oa_pakan' : numeral.unformat( $('input.harga_sapronak[name=oa_pakan]').val() ),

                            'voadip' : numeral.unformat( $('input.harga_sapronak[name=voadip_supl]').val() ),
                            'doc' : numeral.unformat( $('input.harga_sapronak[name=doc_supl]').val() ),
                            'pakan1' : numeral.unformat( $('input.harga_sapronak[name=pakan1_supl]').val() ),
                            'pakan2' : numeral.unformat( $('input.harga_sapronak[name=pakan2_supl]').val() ),
                            'pakan3' : numeral.unformat( $('input.harga_sapronak[name=pakan3_supl]').val() ),

                            'voadip_mitra' : numeral.unformat( $('input.harga_sapronak[name=voadip_mitra]').val() ),
                            'doc_mitra' : numeral.unformat( $('input.harga_sapronak[name=doc_mitra]').val() ),
                            'pakan1_mitra' : numeral.unformat( $('input.harga_sapronak[name=pakan1_mitra]').val() ),
                            'pakan2_mitra' : numeral.unformat( $('input.harga_sapronak[name=pakan2_mitra]').val() ),
                            'pakan3_mitra' : numeral.unformat( $('input.harga_sapronak[name=pakan3_mitra]').val() ),

                            // 'oa_pakan_dok' : $('a.oa_pakan').attr('title'),
                            // 'oa_doc_dok' : $('a.oa_doc').attr('title'),
                            'voadip_dok' : $('a.voadip').attr('title'),
                            'doc_dok' : $('a.doc').attr('title'),
                            'pakan1_dok' : $('a.pakan1').attr('title')
                        };

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
                            'pakan3' : numeral.unformat( $('input.performa[name=pakan3]').val() )
                        };

                        var _filetmp = [];
                        var lampiran = $.map( $('input:file'), function(ipt){
                            if (!empty( $(ipt).val() )) {
                                var __file = $(ipt).get(0).files[0];
                                _filetmp.push( $(ipt).get(0).files[0] );
                                return {
                                    'id' : $(ipt).closest('tr').attr('data-idnama'),
                                    'name' : __file.name,
                                    'sha1' : $(ipt).attr('data-sha1'),
                                };
                            }
                        });

                        // _data['dokumen'] = $('a[name=dokumen]').text();
                        _data['tgl_berlaku'] = dateSQL($('#tgl_berlaku').datepicker('getDate'));
                        _data['action'] = 'submit';
                        _data['table'] = 'sapronak_kesepakatan';
                        _data['harga_kesepakatan'] = hrg_kesepakatan;
                        _data['harga_sapronak'] = harga_sapronak;
                        _data['performa'] = performa;
                        _data['lampirans'] = lampiran;

                        // console.log(_filetmp);
                        // console.log(_data);
                        kbd.execute_save(_data, _filetmp);
                    }
                });
                bootbox.alert('Data perwakilan belum ada yang di pilih.');
            };
        };
    }, // end - save_harga_sk

    execute_save : function (data, file_tmp) {
        var div_tab_pane = $('div.tab-pane');

        var formData = new FormData();

        formData.append("data", JSON.stringify(data));
        for (var i = 0; i < file_tmp.length; i++) {
            formData.append('files[]', file_tmp[i]);
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
                err++;
            } else {
                $(ipt).parent().removeClass('has-error');
                $(td).find('label[name=dokumen]').css('color','#000');
                $(td).find('i.glyphicon-paperclip ').css('color','#000');
            };
        });

        $.map( $('input:file'), function(ipt){
            var td = $(ipt).closest('td');
            var a = $(td).find('a').attr('title');

            if ( empty( a ) ) {
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

                            var harga_sapronak = {
                                'biaya_opr' : numeral.unformat( $('input.harga_sapronak[name=biaya_opr]').val() ),
                                'jaminan' : numeral.unformat( $('input.harga_sapronak[name=jaminan_keuntungan]').val() ),
                                // 'oa_doc' : numeral.unformat( $('input.harga_sapronak[name=oa_doc]').val() ),
                                // 'oa_pakan' : numeral.unformat( $('input.harga_sapronak[name=oa_pakan]').val() ),

                                'voadip' : numeral.unformat( $('input.harga_sapronak[name=voadip_supl]').val() ),
                                'doc' : numeral.unformat( $('input.harga_sapronak[name=doc_supl]').val() ),
                                'pakan1' : numeral.unformat( $('input.harga_sapronak[name=pakan1_supl]').val() ),
                                'pakan2' : numeral.unformat( $('input.harga_sapronak[name=pakan2_supl]').val() ),
                                'pakan3' : numeral.unformat( $('input.harga_sapronak[name=pakan3_supl]').val() ),

                                'voadip_mitra' : numeral.unformat( $('input.harga_sapronak[name=voadip_mitra]').val() ),
                                'doc_mitra' : numeral.unformat( $('input.harga_sapronak[name=doc_mitra]').val() ),
                                'pakan1_mitra' : numeral.unformat( $('input.harga_sapronak[name=pakan1_mitra]').val() ),
                                'pakan2_mitra' : numeral.unformat( $('input.harga_sapronak[name=pakan2_mitra]').val() ),
                                'pakan3_mitra' : numeral.unformat( $('input.harga_sapronak[name=pakan3_mitra]').val() ),

                                // 'oa_pakan_dok' : $('a.oa_pakan').attr('title'),
                                // 'oa_doc_dok' : $('a.oa_doc').attr('title'),
                                'voadip_dok' : $('a.voadip').attr('title'),
                                'doc_dok' : $('a.doc').attr('title'),
                                'pakan1_dok' : $('a.pakan1').attr('title')
                            };

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
                                'pakan3' : numeral.unformat( $('input.performa[name=pakan3]').val() )
                            };

                            var standar_pakan = $.map( $('div.aktif table.range').find('tr.data'), function(tr){
                                var data = {
                                    'bb_awal' : numeral.unformat( $(tr).find('td.bb_awal').html() ),
                                    'bb_akhir' : numeral.unformat( $(tr).find('td.bb_akhir').html() ),
                                    'standar_min' : numeral.unformat( $(tr).find('input.standar_min').val() )
                                };

                                return data;
                            } );

                            var selisih_pakan = $.map( $('div.aktif table.selisih').find('tr.data'), function(tr){
                                var data = {
                                    'range_awal' : numeral.unformat( $(tr).find('td.range_awal').html() ),
                                    'range_akhir' : numeral.unformat( $(tr).find('td.range_akhir').html() ),
                                    'selisih' : numeral.unformat( $(tr).find('input.selisih').val() ),
                                    'tarif' : numeral.unformat( $(tr).find('input.tarif').val() )
                                };

                                return data;
                            } );

                            var perwakilan = $.map( $('[name=mark]:checked'), function(check){
                                var data = {
                                    'id' : $(check).data('id'),
                                    'nama' : $(check).data('name')
                                };

                                return data;
                            });

                            var hitung_budidaya = {
                                'pola_kemitraan' : $('select[name=pola_kemitraan]').val(),
                                'text_pola_kemitraan' : $('select[name=pola_kemitraan] :selected').text().trim(),
                                'bonus_fcr' : $('input[name=bonus_fcr]').val(),
                                'bonus_dh' : $('input[name=bonus_dh]').val(),
                                'bonus_ch' : $('input[name=bonus_ch]').val(),
                                'bonus_bb' : $('input[name=bonus_bb]').val(),
                                'bonus_ip' : $('input[name=bonus_ip]').val()
                            };

                            var _filetmp = [];
                            var lampiran = $.map( $('input:file'), function(ipt){
                                if ( !empty( $(ipt).val() ) || !empty( $(ipt).data('old') ) ) {
                                    var filename = $(ipt).data('old');

                                    if ( !empty( $(ipt).val() ) ) {
                                        var __file = $(ipt).get(0).files[0];
                                        _filetmp.push( $(ipt).get(0).files[0] );

                                        filename = __file.name;
                                    }

                                    return {
                                        'id' : $(ipt).closest('label').attr('data-idnama'),
                                        'name' : filename,
                                        'sha1' : $(ipt).attr('data-sha1'),
                                        'old' : $(ipt).data('old')
                                    };
                                }
                            });

                            _data['dokumen'] = $('a[name=dokumen]').text();
                            _data['tgl_berlaku'] = dateSQL($('[name=tanggal-berlaku]').data('DateTimePicker').date());
                            _data['id'] = $('input[type=hidden]').data('id');
                            _data['action'] = 'submit';
                            _data['table'] = 'sapronak_kesepakatan';
                            _data['harga_kesepakatan'] = hrg_kesepakatan;
                            _data['harga_sapronak'] = harga_sapronak;
                            _data['performa'] = performa;
                            _data['standar_pakan'] = standar_pakan;
                            _data['selisih_pakan'] = selisih_pakan;
                            _data['note'] = $('textarea').val();
                            _data['perwakilan'] = perwakilan;
                            _data['hitung_budidaya'] = hitung_budidaya
                            _data['lampirans'] = lampiran;

                            // console.log(_data);
                            // console.log('atas');
                            kbd.execute_edit(_data, _filetmp);
                        }
                    });
                };
            } else {
                $('button[name=hrg_kesepakatan]').parent().removeClass('has-error');
                bootbox.confirm('Apakah anda yakin mengubah harga sapronak dan kesepakatan ?', function(result){
                    if ( result ) {
                        _data['pola'] = $('select[name=pola_budidaya]').val();
                        _data['item_pola'] = $('select[name=item_pola]').val();

                        var harga_sapronak = {
                            'biaya_opr' : numeral.unformat( $('input.harga_sapronak[name=biaya_opr]').val() ),
                            'jaminan' : numeral.unformat( $('input.harga_sapronak[name=jaminan_keuntungan]').val() ),
                            // 'oa_doc' : numeral.unformat( $('input.harga_sapronak[name=oa_doc]').val() ),
                            // 'oa_pakan' : numeral.unformat( $('input.harga_sapronak[name=oa_pakan]').val() ),

                            'voadip' : numeral.unformat( $('input.harga_sapronak[name=voadip_supl]').val() ),
                            'doc' : numeral.unformat( $('input.harga_sapronak[name=doc_supl]').val() ),
                            'pakan1' : numeral.unformat( $('input.harga_sapronak[name=pakan1_supl]').val() ),
                            'pakan2' : numeral.unformat( $('input.harga_sapronak[name=pakan2_supl]').val() ),
                            'pakan3' : numeral.unformat( $('input.harga_sapronak[name=pakan3_supl]').val() ),

                            'voadip_mitra' : numeral.unformat( $('input.harga_sapronak[name=voadip_mitra]').val() ),
                            'doc_mitra' : numeral.unformat( $('input.harga_sapronak[name=doc_mitra]').val() ),
                            'pakan1_mitra' : numeral.unformat( $('input.harga_sapronak[name=pakan1_mitra]').val() ),
                            'pakan2_mitra' : numeral.unformat( $('input.harga_sapronak[name=pakan2_mitra]').val() ),
                            'pakan3_mitra' : numeral.unformat( $('input.harga_sapronak[name=pakan3_mitra]').val() ),

                            // 'oa_pakan_dok' : $('a.oa_pakan').attr('title'),
                            // 'oa_doc_dok' : $('a.oa_doc').attr('title'),
                            'voadip_dok' : $('a.voadip').attr('title'),
                            'doc_dok' : $('a.doc').attr('title'),
                            'pakan1_dok' : $('a.pakan1').attr('title')
                        };

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
                            'pakan3' : numeral.unformat( $('input.performa[name=pakan3]').val() )
                        };

                        var _filetmp = [];
                        var lampiran = $.map( $('input:file'), function(ipt){
                            if (!empty( $(ipt).val() )) {
                                var __file = $(ipt).get(0).files[0];
                                _filetmp.push( $(ipt).get(0).files[0] );
                                return {
                                    'id' : $(ipt).closest('tr').attr('data-idnama'),
                                    'name' : __file.name,
                                    'sha1' : $(ipt).attr('data-sha1'),
                                    'old' : $(ipt).data('old')
                                };
                            }
                        });

                        _data['tgl_berlaku'] = $('input[type=hidden]').data('id');
                        _data['tgl_berlaku'] = dateSQL($('#tgl_berlaku').datepicker('getDate'));
                        _data['id'] = $('input[type=hidden]').data('id');
                        _data['action'] = 'submit';
                        _data['table'] = 'sapronak_kesepakatan';
                        _data['harga_kesepakatan'] = hrg_kesepakatan;
                        _data['harga_sapronak'] = harga_sapronak;
                        _data['performa'] = performa;
                        _data['lampirans'] = lampiran;

                        // console.log(_filetmp);
                        // console.log(_data);
                        // console.log('bawah');
                        kbd.execute_edit(_data, _filetmp);
                    }
                });
                bootbox.alert('Data perwakilan belum ada yang di pilih.');
            };
        };
    }, // end - edit

    execute_edit : function (data, file_tmp) {
        var div_tab_pane = $('div.tab-pane');

        var formData = new FormData();

        formData.append("data", JSON.stringify(data));
        for (var i = 0; i < file_tmp.length; i++) {
            formData.append('files[]', file_tmp[i]);
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

        $.map( $('input:file'), function(ipt){
            var td = $(ipt).closest('td');
            var a = $(td).find('a').attr('title');

            if ( empty( a ) ) {
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
            if ( $('[name=mark]:checked').length > 0 ) {
                if ( empty(hrg_kesepakatan) ) {
                    $('button[name=hrg_kesepakatan]').parent().addClass('has-error');
                    bootbox.alert('Harga kesepakatan belum di isi.');
                } else {
                    $('button[name=hrg_kesepakatan]').parent().removeClass('has-error');
                    bootbox.confirm('Apakah anda yakin menyimpan harga sapronak dan kesepakatan ?', function(result){
                        if ( result ) {
                            _data['pola'] = $('select[name=pola_budidaya]').val();
                            _data['item_pola'] = $('select[name=item_pola]').val();

                            var harga_sapronak = {
                                'biaya_opr' : numeral.unformat( $('input.harga_sapronak[name=biaya_opr]').val() ),
                                'jaminan' : numeral.unformat( $('input.harga_sapronak[name=jaminan_keuntungan]').val() ),
                                // 'oa_doc' : numeral.unformat( $('input.harga_sapronak[name=oa_doc]').val() ),
                                // 'oa_pakan' : numeral.unformat( $('input.harga_sapronak[name=oa_pakan]').val() ),

                                'voadip' : numeral.unformat( $('input.harga_sapronak[name=voadip_supl]').val() ),
                                'doc' : numeral.unformat( $('input.harga_sapronak[name=doc_supl]').val() ),
                                'pakan1' : numeral.unformat( $('input.harga_sapronak[name=pakan1_supl]').val() ),
                                'pakan2' : numeral.unformat( $('input.harga_sapronak[name=pakan2_supl]').val() ),
                                'pakan3' : numeral.unformat( $('input.harga_sapronak[name=pakan3_supl]').val() ),

                                'voadip_mitra' : numeral.unformat( $('input.harga_sapronak[name=voadip_mitra]').val() ),
                                'doc_mitra' : numeral.unformat( $('input.harga_sapronak[name=doc_mitra]').val() ),
                                'pakan1_mitra' : numeral.unformat( $('input.harga_sapronak[name=pakan1_mitra]').val() ),
                                'pakan2_mitra' : numeral.unformat( $('input.harga_sapronak[name=pakan2_mitra]').val() ),
                                'pakan3_mitra' : numeral.unformat( $('input.harga_sapronak[name=pakan3_mitra]').val() ),

                                // 'oa_pakan_dok' : $('a.oa_pakan').attr('title'),
                                // 'oa_doc_dok' : $('a.oa_doc').attr('title'),
                                'voadip_dok' : $('a.voadip').attr('title'),
                                'doc_dok' : $('a.doc').attr('title'),
                                'pakan1_dok' : $('a.pakan1').attr('title')
                            };

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
                                'pakan3' : numeral.unformat( $('input.performa[name=pakan3]').val() )
                            };

                            var standar_pakan = $.map( $('div.aktif table.range').find('tr.data'), function(tr){
                                var data = {
                                    'bb_awal' : numeral.unformat( $(tr).find('td.bb_awal').html() ),
                                    'bb_akhir' : numeral.unformat( $(tr).find('td.bb_akhir').html() ),
                                    'standar_min' : numeral.unformat( $(tr).find('input.standar_min').val() )
                                };

                                return data;
                            } );

                            var selisih_pakan = $.map( $('div.aktif table.selisih').find('tr.data'), function(tr){
                                var data = {
                                    'range_awal' : numeral.unformat( $(tr).find('td.range_awal').html() ),
                                    'range_akhir' : numeral.unformat( $(tr).find('td.range_akhir').html() ),
                                    'selisih' : numeral.unformat( $(tr).find('input.selisih').val() ),
                                    'tarif' : numeral.unformat( $(tr).find('input.tarif').val() )
                                };

                                return data;
                            } );

                            var perwakilan = $.map( $('[name=mark]:checked'), function(check){
                                var data = {
                                    'id' : $(check).data('id'),
                                    'nama' : $(check).data('name')
                                };

                                return data;
                            });

                            var hitung_budidaya = {
                                'pola_kemitraan' : $('select[name=pola_kemitraan]').val(),
                                'text_pola_kemitraan' : $('select[name=pola_kemitraan] :selected').text().trim(),
                                'bonus_fcr' : $('input[name=bonus_fcr]').val(),
                                'bonus_dh' : $('input[name=bonus_dh]').val(),
                                'bonus_ch' : $('input[name=bonus_ch]').val(),
                                'bonus_bb' : $('input[name=bonus_bb]').val(),
                                'bonus_ip' : $('input[name=bonus_ip]').val()
                            };

                            var _filetmp = [];
                            var lampiran = $.map( $('input:file'), function(ipt){
                                if ( !empty( $(ipt).val() ) || !empty( $(ipt).data('old') ) ) {
                                    var filename = $(ipt).data('old');

                                    if ( !empty( $(ipt).val() ) ) {
                                        var __file = $(ipt).get(0).files[0];
                                        _filetmp.push( $(ipt).get(0).files[0] );

                                        filename = __file.name;
                                    }

                                    return {
                                        'id' : $(ipt).closest('label').attr('data-idnama'),
                                        'name' : filename,
                                        'sha1' : $(ipt).attr('data-sha1'),
                                        'old' : $(ipt).data('old')
                                    };
                                }
                            });

                            _data['dokumen'] = $('a[name=dokumen]').text();
                            _data['tgl_berlaku'] = dateSQL($('[name=tanggal-berlaku]').data('DateTimePicker').date());
                            _data['id'] = $('input[type=hidden]').data('id');
                            _data['action'] = 'submit';
                            _data['table'] = 'sapronak_kesepakatan';
                            _data['harga_kesepakatan'] = hrg_kesepakatan;
                            _data['harga_sapronak'] = harga_sapronak;
                            _data['performa'] = performa;
                            _data['standar_pakan'] = standar_pakan;
                            _data['selisih_pakan'] = selisih_pakan;
                            _data['note'] = $('textarea').val();
                            _data['perwakilan'] = perwakilan;
                            _data['hitung_budidaya'] = hitung_budidaya
                            _data['lampirans'] = lampiran;

                            // console.log(_data);
                            // console.log('atas');
                            kbd.execute_save_copy(_data, _filetmp);
                        }
                    });
                };
            } else {
                $('button[name=hrg_kesepakatan]').parent().removeClass('has-error');
                bootbox.confirm('Apakah anda yakin menyimpan harga sapronak dan kesepakatan ?', function(result){
                    if ( result ) {
                        _data['pola'] = $('select[name=pola_budidaya]').val();
                        _data['item_pola'] = $('select[name=item_pola]').val();

                        var harga_sapronak = {
                            'biaya_opr' : numeral.unformat( $('input.harga_sapronak[name=biaya_opr]').val() ),
                            'jaminan' : numeral.unformat( $('input.harga_sapronak[name=jaminan_keuntungan]').val() ),
                            // 'oa_doc' : numeral.unformat( $('input.harga_sapronak[name=oa_doc]').val() ),
                            // 'oa_pakan' : numeral.unformat( $('input.harga_sapronak[name=oa_pakan]').val() ),

                            'voadip' : numeral.unformat( $('input.harga_sapronak[name=voadip_supl]').val() ),
                            'doc' : numeral.unformat( $('input.harga_sapronak[name=doc_supl]').val() ),
                            'pakan1' : numeral.unformat( $('input.harga_sapronak[name=pakan1_supl]').val() ),
                            'pakan2' : numeral.unformat( $('input.harga_sapronak[name=pakan2_supl]').val() ),
                            'pakan3' : numeral.unformat( $('input.harga_sapronak[name=pakan3_supl]').val() ),

                            'voadip_mitra' : numeral.unformat( $('input.harga_sapronak[name=voadip_mitra]').val() ),
                            'doc_mitra' : numeral.unformat( $('input.harga_sapronak[name=doc_mitra]').val() ),
                            'pakan1_mitra' : numeral.unformat( $('input.harga_sapronak[name=pakan1_mitra]').val() ),
                            'pakan2_mitra' : numeral.unformat( $('input.harga_sapronak[name=pakan2_mitra]').val() ),
                            'pakan3_mitra' : numeral.unformat( $('input.harga_sapronak[name=pakan3_mitra]').val() ),

                            // 'oa_pakan_dok' : $('a.oa_pakan').attr('title'),
                            // 'oa_doc_dok' : $('a.oa_doc').attr('title'),
                            'voadip_dok' : $('a.voadip').attr('title'),
                            'doc_dok' : $('a.doc').attr('title'),
                            'pakan1_dok' : $('a.pakan1').attr('title')
                        };

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
                            'pakan3' : numeral.unformat( $('input.performa[name=pakan3]').val() )
                        };

                        var _filetmp = [];
                        var lampiran = $.map( $('input:file'), function(ipt){
                            if (!empty( $(ipt).val() )) {
                                var __file = $(ipt).get(0).files[0];
                                _filetmp.push( $(ipt).get(0).files[0] );
                                return {
                                    'id' : $(ipt).closest('tr').attr('data-idnama'),
                                    'name' : __file.name,
                                    'sha1' : $(ipt).attr('data-sha1'),
                                    'old' : $(ipt).data('old')
                                };
                            }
                        });

                        _data['tgl_berlaku'] = $('input[type=hidden]').data('id');
                        _data['tgl_berlaku'] = dateSQL($('#tgl_berlaku').datepicker('getDate'));
                        _data['id'] = $('input[type=hidden]').data('id');
                        _data['action'] = 'submit';
                        _data['table'] = 'sapronak_kesepakatan';
                        _data['harga_kesepakatan'] = hrg_kesepakatan;
                        _data['harga_sapronak'] = harga_sapronak;
                        _data['performa'] = performa;
                        _data['lampirans'] = lampiran;

                        // console.log(_filetmp);
                        // console.log(_data);
                        // console.log('bawah');
                        kbd.execute_save_copy(_data, _filetmp);
                    }
                });
                bootbox.alert('Data perwakilan belum ada yang di pilih.');
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