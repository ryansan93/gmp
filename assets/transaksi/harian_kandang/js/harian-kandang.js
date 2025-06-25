var Hk = {
    start_up : function () {
        $("#tgl_timbang").datetimepicker({
            locale: 'id',
            format: 'DD MMM Y',
            maxDate : new Date()
        }).on("dp.change", function (e) {
            Hk.getUmur($("#tgl_timbang"));
        });

        Hk.getLists();
    }, // end - start_up

    getLists : function(keyword = null){
        $.ajax({
            url : 'transaksi/HarianKandang/getLists',
            data : {'keyword' : keyword},
            dataType : 'HTML',
            type : 'GET',
            beforeSend : function(){ showLoading(); },
            success : function(data){
                $('div#riwayat').find('div.data').html(data);
                hideLoading();
            }
        });
    }, // end - getLists

    changeTabActive: function(elm) {
        var vhref = $(elm).data('href');
        var resubmit = $(elm).data('resubmit');
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

            Hk.load_form(v_id, resubmit, elm);
        };
    }, // end - changeTabActive

    load_form: function(v_id = null, resubmit = null) {
        var dcontent = $('div#action');

        $.ajax({
            url : 'transaksi/HarianKandang/load_form',
            data : {
                'id' :  v_id,
                'resubmit' :  resubmit
            },
            type : 'GET',
            dataType : 'HTML',
            beforeSend : function(){ App.showLoaderInContent(dcontent); },
            success : function(html){
                App.hideLoaderInContent(dcontent, html);
                App.formatNumber();

                if ( empty(resubmit) ) {
                    $("#tgl_timbang").datetimepicker({
                        locale: 'id',
                        format: 'DD MMM Y',
                        maxDate : new Date()
                    }).on("dp.change", function (e) {
                        Hk.getUmur($("#tgl_timbang"));
                    });
                };

                if ( resubmit == 'edit' ) {
                    var id_rdim_submit = $('select[name=noreg]').data('idrdimsubmit');
                    Hk.getNoregMitraByRdim( $('select[name=periode]'), id_rdim_submit );
                };
            },
        });
    }, // end - load_form

    getNoregMitraByRdim: function(select, id_rdim_submit=null){
        const id = $(select).val();
        let elNoreg = $('select[name=noreg]');
        if (!empty(id)) {
            $.ajax({
                url: 'transaksi/HarianKandang/getNoregMitraByRdim/' + id,
                type: 'GET',
                dataType: 'JSON',
                beforeSend: function(){ showLoading() },
                success: function(data){
                    let rJSON = data; // response JSON
                    if (!empty(rJSON)) {
                        elNoreg.html('<option value="">-- pilih noreg --</option>');
                        rJSON.forEach(function(json){
                            elNoreg.append('<option value="'+ json.id +'"> '+ json.noreg +' </option>');
                        });

                        elNoreg.unbind('change.selectNoreg');
                        elNoreg.bind('change.selectNoreg', function(evt){
                            let i = $(this).find('option:selected').index();
                            if (i > 0) {
                                let json = rJSON[--i];
                                $('input[name=nama-mitra]').val(json.mitra);
                                $('input[name=populasi]').val( numeral.formatInt(json.populasi));
                            }else{
                                $('input[name=nama-mitra]').val('');
                                $('input[name=populasi]').val('');
                            }
                        });

                        if ( !empty(id_rdim_submit) ) {
                            $(elNoreg).find('option[value='+id_rdim_submit+']').attr('selected', 'selected');
                            elNoreg.change();

                            var tgl_timbang = $("#tgl_timbang").data('tgl');

                            $("#tgl_timbang").datetimepicker({
                                locale: 'id',
                                format: 'DD MMM Y',
                                maxDate : new Date()
                            }).on("dp.change", function (e) {
                                Hk.getUmur($("#tgl_timbang"));
                            });

                            $('#tgl_timbang').data("DateTimePicker").date(new Date(tgl_timbang));
                        };
                    }

                    hideLoading();
                }
            });
        }else{
            elNoreg.html('');
        }
    }, // end -  getNoregMitraByRdim

    getUmur: function (elm) {
        var tgl_timbang = dateSQL( $(elm).data('DateTimePicker').date() );

        var noreg = $('select[name=noreg]').find('option:selected').text();

        if ( !empty(noreg) ) {
            $('select[name=noreg]').parent().removeClass('has-error');

            $.ajax({
                url: 'transaksi/HarianKandang/getUmur',
                data: {
                    'tgl_timbang': tgl_timbang,
                    'noreg': noreg
                },
                type: 'POST',
                dataType: 'JSON',
                beforeSend: function() {
                    showLoading();
                },
                success: function(data) {
                    // console.log(data);
                    $('input[name=umur]').val(data);
                    hideLoading();
                },
            });
        } else {
            $('select[name=noreg]').parent().addClass('has-error');
            $(elm).val('');
            bootbox.alert('Tanggal timbang masih kosong.');
        };
    }, // end - getUmur

    addRowTable: function(elm) {
        let row = $(elm).closest("tr");
        let tbody = $(elm).closest("tbody");
        let row_clone = row.clone();

        row_clone.find('input').val('');
        tbody.append(row_clone);

        App.formatNumber();
        tbody.find('tr:last td input[name=sekat]').focus();
        tbody.find('tr:last td input[name=bb]').enterKey(function(e){
            Hk.addRowTable(this);
        });
    }, // end - addRow

    removeRowTable: function(elm) {
        let row = $(elm).closest("tr");
        if (row.prev('tr').length > 0 || row.next('tr').length > 0) {
            row.find('input').val('');
            row.remove();
        }
    }, // end - removeRow

    save: function () {
        var div = $('div#action');

        let isError = 0;
        $(div).find('input, select, textarea').parent().removeClass('has-error');
        $.map( $(div).find('input, select, textarea'), function(elm){
            if ( empty($(elm).val()) ) {
                $(elm).parent().addClass('has-error');
                isError++;
            }
        });

        if (isError > 0) {
            toastr.error('Mohon periksa kembali kelengkapan data yang akan disimpan.', 'Data bermasalah!')
        } else {

            let data = {
                'id_rdim_submit': $('select[name=noreg]').val(),
                'tanggal': dateSQL( $('#tgl_timbang').data('DateTimePicker').date() ),
                'umur': numeral.unformat( $('input[name=umur]').val() ),
                'mati': numeral.unformat( $('input[name=jml-kematian]').val() ),
                'bb' : numeral.unformat( $('input[name=bb-average]').val() ),
                'terima_pakan': numeral.unformat( $('input[name=terima-pakan]').val() ),
                'sisa_pakan': numeral.unformat( $('input[name=sisa-pakan]').val() ),
                'ket': $('textarea[name=komentar]').val(),
                'details': null
            };

            data['details'] = $.map( $(div).find('#tb_sekat tbody tr'), function(row){
                return {
                    'sekat': numeral.unformat( $(row).find('input[name=sekat]').val() ),
                    'bb': numeral.unformat( $(row).find('input[name=bb]').val( ))
                };
            });

            App.confirmDialog('Apakah Anda yakin akan menyimpan harian kandang?', function(isConfirm){
                if (isConfirm) {
                    Hk.execute_save(data);
                }
            });
        }
    }, // end - save

    execute_save: function(params){
        $.ajax({
            url: 'transaksi/HarianKandang/save',
            data: {'params' : params},
            type: 'POST',
            dataType: 'JSON',
            beforeSend: function(){ showLoading() },
            success: function(data){
                if (data.status == 1) {
                    bootbox.alert(data.message, function(){
                        $('#action').html(data.content);
                        Hk.getLists();
                    });
                }else{
                    alertDialog(data);
                }
            }
        });
    }, // end - execute_save

    edit: function () {
        var div = $('div#action');
        var button = $(div).find('button.edit');

        let isError = 0;
        $(div).find('input, select, textarea').parent().removeClass('has-error');
        $.map( $(div).find('input, select, textarea'), function(elm){
            if ( empty($(elm).val()) ) {
                $(elm).parent().addClass('has-error');
                isError++;
            }
        });

        if (isError > 0) {
            toastr.error('Mohon periksa kembali kelengkapan data yang akan disimpan.', 'Data bermasalah!')
        } else {

            let data = {
                'id_old': $(button).data('id'),
                'id_rdim_submit': $('select[name=noreg]').val(),
                'tanggal': dateSQL( $('#tgl_timbang').data('DateTimePicker').date() ),
                'umur': numeral.unformat( $('input[name=umur]').val() ),
                'mati': numeral.unformat( $('input[name=jml-kematian]').val() ),
                'bb' : numeral.unformat( $('input[name=bb-average]').val() ),
                'terima_pakan': numeral.unformat( $('input[name=terima-pakan]').val() ),
                'sisa_pakan': numeral.unformat( $('input[name=sisa-pakan]').val() ),
                'ket': $('textarea[name=komentar]').val(),
                'details': null
            };

            data['details'] = $.map( $(div).find('#tb_sekat tbody tr'), function(row){
                return {
                    'sekat': numeral.unformat( $(row).find('input[name=sekat]').val() ),
                    'bb': numeral.unformat( $(row).find('input[name=bb]').val( ))
                };
            });

            App.confirmDialog('Apakah Anda yakin akan mengubah harian kandang?', function(isConfirm){
                if (isConfirm) {
                    // console.log(data);
                    Hk.execute_edit(data);
                }
            });
        }
    }, // end - edit

    execute_edit: function(params){
        $.ajax({
            url: 'transaksi/HarianKandang/edit',
            data: {'params' : params},
            type: 'POST',
            dataType: 'JSON',
            beforeSend: function(){ showLoading() },
            success: function(data){
                if (data.status == 1) {
                    bootbox.alert(data.message, function(){
                        $('#action').html(data.content);
                        Hk.getLists();
                    });
                }else{
                    alertDialog(data);
                }
            }
        });
    }, // end - execute_edit
};

Hk.start_up();
