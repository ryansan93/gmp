var pkw = {
	start_up_perusahaan : function () {
        pkw.getLists_Perusahaan();
    }, // end - start_up_perusahaan

    start_up_wilayah : function () {
        pkw.getLists_Wilayah();
    }, // end - start_up_wilayah

    start_up_korwil : function () {
        pkw.getLists_Korwil();
    }, // end - start_up_korwil

    set_autocomplete_lokasi : function (elements, tipe_lokasi = '', induk = '') {
        // $( "[name=kabupaten]" ).autocomplete({
        $( elements ).autocomplete({
            source : function(request, response){

                var elm = $(this)[0].element[0];
                var elm_name = $(elm).attr('name');

                $.ajax({
                    url: 'parameter/PKW/autocomplete_lokasi?tipe_lokasi=' + tipe_lokasi + '&induk=' + induk,
                    beforeSend: function(){},
                    async:    true,
                    data : request,
                    dataType: "json",
                    success: response
                });
            },
            minLength: 2,
            select: function( event, ui ) {
                $(this).attr('data-id', ui.item.id );
            }
        });
    }, // end - set_autocomplete_lokasi

    set_autocomplete_wilayah : function (elements, tipe_wilayah = '', induk = '') {
        // $( "[name=kabupaten]" ).autocomplete({
        $( elements ).autocomplete({
            source : function(request, response){

                var elm = $(this)[0].element[0];
                var elm_name = $(elm).attr('name');

                $.ajax({
                    url: 'parameter/PKW/autocomplete_wilayah?tipe_wilayah=' + tipe_wilayah + '&induk=' + induk,
                    beforeSend: function(){},
                    async:    true,
                    data : request,
                    dataType: "json",
                    success: response
                });
            },
            minLength: 2,
            select: function( event, ui ) {
                $(this).attr('data-id', ui.item.id );
            }
        });
    }, // end - set_autocomplete_wilayah

    set_autocomplete_kab_kota : function (elements) {
        // $( "[name=kabupaten]" ).autocomplete({
        $( elements ).autocomplete({
            source : function(request, response){

                var elm = $(this)[0].element[0];
                var elm_name = $(elm).attr('name');

                $.ajax({
                    url: 'parameter/PKW/autocomplete_kota_kab',
                    beforeSend: function(){},
                    async:    true,
                    data : request,
                    dataType: "json",
                    success: response
                });
            },
            minLength: 2,
            select: function( event, ui ) {
                $(this).attr('data-id', ui.item.id );
            }
        });
    }, // end - set_autocomplete_kab_kota

    add_form: function(elm) {
    	var href = $(elm).data('href');
        var tab_pane = $(elm).closest('div.tab-pane');

    	$.ajax({
            url : 'parameter/PKW/add_form',
            data : {
                'jenis' :  href,
            },
            type : 'GET',
            dataType : 'HTML',
            beforeSend : function(){},
            success : function(html){
	    		$(tab_pane).html(html);

                $('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal], [data-tipe=decimal3],[data-tipe=decimal4], [data-tipe=number]').each(function(){
                    $(this).priceFormat(Config[$(this).data('tipe')]);
                });

                $('input#npwp').mask("99.999.999.9-999.999");

                if ( href == 'perusahaan' ) {
                    var kota = $('input#kota');
                    pkw.set_autocomplete_kab_kota(kota);
                    // pkw.set_autocomplete_lokasi(kota, 'KT');
                } else if ( href == 'korwil' ) {
                    var perwakilan = $('input#perwakilan');
                    var kota = $('input#unit');

                    pkw.set_autocomplete_wilayah(perwakilan, 'PW');
                    pkw.set_autocomplete_kab_kota(kota);
                } else {
                    var provinsi = $('input#provinsi');

                    pkw.set_autocomplete_lokasi(provinsi, 'PV');
                    pkw.set_autocomplete_kab_kota();
                };
            },
        });
    }, // end - add_form

    autocomplete_kota_kab: function () {
        var jenis = $('select.jenis').val();
        var nama = $('input#nama');
        var induk = $('input#provinsi').data('id');
        pkw.set_autocomplete_lokasi(nama, jenis, induk);
    }, // end - autocomplete_kota_kab

    edit_form: function(elm) {
        var href = $(elm).data('href');
        var id = $(elm).data('id');
        var tab_pane = $(elm).closest('div.tab-pane');

        $.ajax({
            url : 'parameter/PKW/edit_form',
            data : {
                'jenis' : href,
                'id' : id,
            },
            type : 'GET',
            dataType : 'HTML',
            beforeSend : function(){},
            success : function(html){
                $(tab_pane).html(html);

                $('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal], [data-tipe=decimal3],[data-tipe=decimal4], [data-tipe=number]').each(function(){
                    $(this).priceFormat(Config[$(this).data('tipe')]);
                });

                $('input#npwp').mask("99.999.999.9-999.999");
                var kota = $('input#kota');
                
                pkw.set_autocomplete_kab_kota(kota);
            },
        });
    }, // end - edit_form

    cancel: function(elm) {
        var data_href = $(elm).data('href');

        if ( data_href == 'perusahaan' ) {
            pkw.getLists_Perusahaan();
        } else if ( data_href == 'wilayah' ) {
            pkw.getLists_Wilayah();
        } else if ( data_href == 'korwil' ) {
            pkw.getLists_Korwil();
        };
    }, // end - cancel

    add_row_kecamatan: function(elm) {
        var div_kecamatan = $(elm).closest('div.kecamatan');
        var div_kecamatan_clone = $(div_kecamatan).clone();

        // $(div_kecamatan_clone).find('label').text('');
        $(div_kecamatan_clone).find('input').val('');
        $(div_kecamatan_clone).find('div.form-group:not(.kelurahan) button.remove').removeClass('hide');

        $(div_kecamatan_clone).find('div.kelurahan:not(:first)').remove();
        $(div_kecamatan_clone).find('div.kelurahan:first button:not(.remove)').removeClass('hide');

        div_kecamatan.after(div_kecamatan_clone);

        // hide button
        $(div_kecamatan).find('div.form-group:not(.kelurahan) button').addClass('hide');
    }, // end - add_row_kecamatan

    remove_row_kecamatan: function(elm) {
        var div_kecamatan = $(elm).closest('div.kecamatan');
        var div_kecamatan_prev = $(div_kecamatan).prev();

        $(div_kecamatan).remove();

        if ( $('div.kecamatan').length > 1 ) {
            $(div_kecamatan_prev).find('div.form-group:not(.kelurahan) button.add').removeClass('hide');
            $(div_kecamatan_prev).find('div.form-group:not(.kelurahan) button.remove').removeClass('hide');
            $(div_kecamatan_prev).find('div.form-group:not(.kelurahan) button.save').removeClass('hide');
        } else {
            $(div_kecamatan_prev).find('div.form-group:not(.kelurahan) button.add').removeClass('hide');
            $(div_kecamatan_prev).find('div.form-group:not(.kelurahan) button.save').removeClass('hide');
        };
    }, // end - remove_row_kecamatan

    add_row_kelurahan: function(elm) {
        var div_kelurahan = $(elm).closest('div.kelurahan');
        var div_kelurahan_clone = $(div_kelurahan).clone();

        $(div_kelurahan_clone).find('label').text('');
        $(div_kelurahan_clone).find('input').val('');
        $(div_kelurahan_clone).find('button.remove').removeClass('hide');

        div_kelurahan.after(div_kelurahan_clone);

        // hide button
        $(div_kelurahan).find('button').addClass('hide');
    }, // end - add_row_kelurahan

    remove_row_kelurahan: function(elm) {
        var div_kecamatan = $(elm).closest('div.kecamatan');
        var div_kelurahan = $(elm).closest('div.kelurahan');
        var div_kelurahan_prev = $(div_kelurahan).prev();

        $(div_kelurahan).remove();

        if ( $(div_kecamatan).find('div.kelurahan').length > 1 ) {
            $(div_kelurahan_prev).find('button.add').removeClass('hide');
            $(div_kelurahan_prev).find('button.remove').removeClass('hide');
            $(div_kelurahan_prev).find('button.save').removeClass('hide');
        } else {
            $(div_kelurahan_prev).find('button.add').removeClass('hide');
            $(div_kelurahan_prev).find('button.save').removeClass('hide');
        };
    }, // end - remove_row_kelurahan

    add_row_unit: function(elm) {
        var div_kota = $(elm).closest('div.kota');
        var div_kota_clone = $(div_kota).clone();

        $(div_kota_clone).find('label').text('');
        $(div_kota_clone).find('input').val('');
        $(div_kota_clone).find('button.remove').removeClass('hide');

        var kota = $(div_kota_clone).find('input#unit');
        pkw.set_autocomplete_kab_kota(kota);

        div_kota.after(div_kota_clone);

        // hide button
        $(div_kota).find('button').addClass('hide');
    }, // end - add_row_kelurahan

    remove_row_unit: function(elm) {
        var div_kota = $(elm).closest('div.kota');
        var div_kota_prev = $(div_kota).prev();

        $(div_kota).remove();

        if ( $('div.kota').length > 1 ) {
            $(div_kota_prev).find('button.add').removeClass('hide');
            $(div_kota_prev).find('button.remove').removeClass('hide');
            $(div_kota_prev).find('button.save').removeClass('hide');
        } else {
            $(div_kota_prev).find('button.add').removeClass('hide');
            $(div_kota_prev).find('button.save').removeClass('hide');
        };
    }, // end - remove_row_kelurahan

    getLists_Perusahaan : function(keyword = null){
        var div_action = $('div#perusahaan');

        $.ajax({
            url : 'parameter/PKW/list_perusahaan',
            data : {'keyword' : keyword},
            dataType : 'HTML',
            type : 'GET',
            beforeSend : function(){},
            success : function(data){
                $(div_action).html(data);
            }
        });
    }, // end - getLists_Perusahaan

    getLists_Wilayah : function(keyword = null){
        var div_action = $('div#wilayah');

        $.ajax({
            url : 'parameter/PKW/list_wilayah',
            data : {'keyword' : keyword},
            dataType : 'HTML',
            type : 'GET',
            beforeSend : function(){},
            success : function(data){
                $(div_action).html(data);
            }
        });
    }, // end - getLists_Wilayah

    getLists_Korwil : function(keyword = null){
        var div_action = $('div#korwil');

        $.ajax({
            url : 'parameter/PKW/list_korwil',
            data : {'keyword' : keyword},
            dataType : 'HTML',
            type : 'GET',
            beforeSend : function(){},
            success : function(data){
                $(div_action).html(data);
            }
        });
    }, // end - getLists_Korwil

    save_perusahaan : function () {
        var div_action = $('div#perusahaan');

        var err = 0;

        $.map( $(div_action).find('[required]'), function(ipt) {
            if ( empty( $(ipt).val() ) ) {
                $(ipt).parent().addClass('has-error');
                err++;
            } else {
                $(ipt).parent().removeClass('has-error');
            };
        });

        if ( err > 0 ) {
            bootbox.alert('Data belum lengkap, mohon cek kembali data yang anda masukkan.');
        } else {
            bootbox.confirm('Apakah anda yakin ingin menyimpan data perusahaan ?', function(result) {
                if ( result ) {
                    var data = {
                        'nama_perusahaan' : $('input#nama_perusahaan').val(),
                        'alamat' : $('textarea#alamat').val(),
                        'kota' : $('input#kota').data('id'),
                        'npwp' : $('input#npwp').mask(),
                    };

                    pkw.exec_save_perusahaan(data);
                };
            });
        };
    }, // end - save_perusahaan

    exec_save_perusahaan : function(params){
        $.ajax({
            url : 'parameter/PKW/save_perusahaan',
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
                        pkw.getLists_Perusahaan();
                    });
                } else {
                    bootbox.alert(data.message);
                };
            }
        });
    }, // end - exec_save_perusahaan

    edit_perusahaan : function (elm) {
        var div_action = $('div#perusahaan');

        var err = 0;

        $.map( $(div_action).find('[required]'), function(ipt) {
            if ( empty( $(ipt).val() ) ) {
                $(ipt).parent().addClass('has-error');
                err++;
            } else {
                $(ipt).parent().removeClass('has-error');
            };
        });

        if ( err > 0 ) {
            bootbox.alert('Data belum lengkap, mohon cek kembali data yang anda masukkan.');
        } else {
            bootbox.confirm('Apakah anda yakin ingin mengubah data perusahaan ?', function(result) {
                if ( result ) {
                    var data = {
                        'id' : $(elm).data('id'),
                        'nomor' : $('input#kode').val(),
                        'nama_perusahaan' : $('input#nama_perusahaan').val(),
                        'alamat' : $('textarea#alamat').val(),
                        'kota' : $('input#kota').data('id'),
                        'npwp' : $('input#npwp').mask(),
                        'version' : $(elm).data('version'),
                    };

                    pkw.exec_edit_perusahaan(data);
                };
            });
        };
    }, // end - edit_perusahaan

    exec_edit_perusahaan : function(params, elm){
        $.ajax({
            url : 'parameter/PKW/edit_perusahaan',
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
                        pkw.getLists_Perusahaan();
                    });
                } else {
                    bootbox.alert(data.message);
                };
            }
        });
    }, // end - exec_edit_perusahaan

    save_lokasi : function () {
        var div_wilayah = $('div[name=data-wilayah]');

        var err = 0;

        $.map( $(div_wilayah).find('input[required]'), function(ipt) {
            if ( empty($(ipt).val()) ) {
                err++;
                $(ipt).parent().addClass('has-error');
            } else {
                $(ipt).parent().removeClass('has-error');
            };
        });

        if ( err > 0 ) {
            bootbox.alert('Data belum lengkap, mohon cek kembali data yang anda masukkan.');
        } else {
            bootbox.confirm('Apakah anda yakin ingin menyimpan data wilayah ?', function(result) {
                if ( result ) {
                    var list_kecamatan = $.map( $(div_wilayah).find('div.kecamatan'), function(div_kec) {
                        var list_kelurahan = $.map( $(div_kec).find('div.kelurahan'), function(div_kel) {

                            // NOTE : DATA KELURAHAN
                            if ( !empty( $(div_kel).find('input').val() ) ) {
                                var data_kelurahan = {
                                    'nama' : $(div_kel).find('input').val()
                                };    

                            };

                            return data_kelurahan;
                        });

                        // NOTE : DATA KECAMATAN
                        if ( !empty( $(div_kec).find('input').val() ) ) {
                            var data_kecamatan = {
                                'nama' : $(div_kec).find('input').val()
                            };

                            if ( !empty(list_kelurahan) ) {
                                data_kecamatan['kelurahan'] = list_kelurahan;
                            };
                        }

                        return data_kecamatan;
                    });

                    // NOTE : DATA HEADER
                    var head_lok = 'Kota'
                    if ( $('select.jenis').val() == 'KB' ) {
                        head_lok = 'Kab';
                    };

                    var tipe = $('select.jenis').val();

                    var row_data = {
                        'nama_prov' : $('input#provinsi').val(),
                        'tipe_lok' : tipe,
                        'nama_lok' : $('input#nama').val()
                    }

                    if ( !empty(list_kecamatan) ) {
                        row_data['kecamatan'] = list_kecamatan;
                    };

                    console.log(row_data);
                    pkw.exec_save_lokasi(row_data, tipe);
                };
            });
        };
    }, // end - save_lokasi

    exec_save_lokasi : function(params, tipe){
        $.ajax({
            url : 'parameter/PKW/save_lokasi',
            data : {
                'params' : params,
                'tipe' : tipe
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
                        pkw.getLists_Wilayah();
                    });
                } else {
                    bootbox.alert(data.message);
                };
            },
        });
    }, // end - exec_save_lokasi

    edit_wilayah : function (elm) {
        var table = $('table.tbl_wilayah');
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

                    pkw.exec_edit_wilayah(row_data, elm);
                };
            });
        };
    }, // end - edit_wilayah

    exec_edit_wilayah : function(params, elm){
        $.ajax({
            url : 'parameter/PKW/edit_wilayah',
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
                        pkw.getLists_Wilayah();
                        pkw.showHeader(elm);
                    });
                } else {
                    bootbox.alert(data.message);
                };
            }
        });
    }, // end - exec_edit_wilayah

    save_korwil : function () {
        var div_korwil = $('div[name=data-korwil]');

        var err = 0;

        $.map( $(div_korwil).find('input[required]'), function(ipt) {
            if ( empty($(ipt).val()) ) {
                err++;
                $(ipt).parent().addClass('has-error');
            } else {
                $(ipt).parent().removeClass('has-error');
            };
        });

        if ( err > 0 ) {
            bootbox.alert('Data belum lengkap, mohon cek kembali data yang anda masukkan.');
        } else {
            bootbox.confirm('Apakah anda yakin ingin menyimpan data koordinator wilayah ?', function(result) {
                if ( result ) {
                    var list_kota = $.map( $(div_korwil).find('div.kota'), function(div_kota) {

                        // NOTE : DATA KOTA
                        if ( !empty( $(div_kota).find('input#unit').val() ) ) {
                            var data_kota = {
                                'nama' : $(div_kota).find('input#unit').val(),
                                'kode' : $(div_kota).find('input#kode_unit').val()
                            };    

                        };

                        return data_kota;
                    });

                    var row_data = {
                        'nama_pwk' : $('input#perwakilan').val()
                    }

                    if ( !empty(list_kota) ) {
                        row_data['kota'] = list_kota;
                    };

                    // console.log(row_data);
                    pkw.exec_save_korwil(row_data);
                };
            });
        };
    }, // end - save_korwil

    exec_save_korwil : function(params){
        $.ajax({
            url : 'parameter/PKW/save_korwil',
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
                        pkw.getLists_Korwil();
                    });
                } else {
                    bootbox.alert(data.message);
                };
            },
        });
    }, // end - exec_save_korwil

    set_required: function(elm) {
        var prev = $(elm).data('prev');
        $('input#'+prev).attr('required', '1');
    }, // end - set_required
};

pkw.start_up_perusahaan();
pkw.start_up_wilayah();
pkw.start_up_korwil();