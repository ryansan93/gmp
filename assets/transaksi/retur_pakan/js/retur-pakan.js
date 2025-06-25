var rp = {
    start_up: function () {
        // rp.get_lists();
        rp.setting();
    }, // end - start_up

    setting: function() {
        $('.ekspedisi').select2();

        $("[name=tgl_op], [name=tgl_retur], [name=tgl_kirim], #StartDate, #EndDate").datetimepicker({
            locale: 'id',
            format: 'DD MMM Y'
        });
        let data_tgl_op = $("[name=tgl_op]").find('input').data('tgl');
        if ( !empty(data_tgl_op) ) {
            $("[name=tgl_op]").data("DateTimePicker").date(new Date(data_tgl_op));
        }
        let data_tgl_retur = $("[name=tgl_retur]").find('input').data('tgl');
        if ( !empty(data_tgl_retur) ) {
            $("[name=tgl_retur]").data("DateTimePicker").date(new Date(data_tgl_retur));
        }

        $("[name=tgl_op]").on("dp.change", function (e) {
            $("[name=tgl_retur]").data("DateTimePicker").minDate(e.date);
            $("[name=tgl_retur]").data("DateTimePicker").date(e.date);
        });

        $.map( $("[name=tgl_kirim]"), function(ipt) {
            var tgl = $(ipt).find('input').data('tgl');
            if ( !empty(tgl) ) {
                $(ipt).data("DateTimePicker").date(new Date(tgl));
            }
        });
    }, // end - setting

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

        if ( vhref == 'rp' ) {
            let v_id = $(elm).attr('data-id');
            rp.load_form(v_id, resubmit);
        } else {
            rp.load_form(null, resubmit);
        };
    }, // end - changeTabActive

    load_form: function(v_id = null, resubmit = null) {
        var dcontent = $('div#rp');

        $.ajax({
            url : 'transaksi/ReturPakan/load_form',
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
                rp.setting();

                if ( !empty(v_id) ) {
                    $(dcontent).find('button.get_op').click();
                }
            },
        });
    }, // end - load_form

    get_lists: function(elm) {
        let div_riwayat = $('div#riwayat_rp');
        let dcontent = $('table.tbl_rp tbody');

        var start_date = $(div_riwayat).find('[name=startDate]').data('DateTimePicker').date();
        var end_date = $(div_riwayat).find('[name=endDate]').data('DateTimePicker').date();
        var kode_unit = $(div_riwayat).find('select.unit').val();

        if ( empty(start_date) || empty(end_date) ) {
            bootbox.alert('Harap lengkapi periode terlebih dahulu.');
        } else {
            var start_date = dateSQL( $(div_riwayat).find('[name=startDate]').data('DateTimePicker').date() );
            var end_date = dateSQL( $(div_riwayat).find('[name=endDate]').data('DateTimePicker').date() );

            var params = {
                'start_date': start_date,
                'end_date': end_date,
                'kode_unit': kode_unit
            };

            $.ajax({
                url : 'transaksi/ReturPakan/get_lists',
                data : {'params': params},
                type : 'GET',
                dataType : 'HTML',
                beforeSend : function(){ App.showLoaderInContent(dcontent) },
                success : function(html){
                    App.hideLoaderInContent(dcontent, html);
                },
            });
        }
    }, // end - get_lists

    cek_tujuan: function(elm) {
        var div = $(elm).closest('div.detailed');
        var tujuan = $(div).find('select.tujuan').val();

        if ( tujuan == 'supplier' ) {
            $(div).find('select.supplier').removeClass('hide');
            $(div).find('select.supplier').attr('data-required', 1);
            $(div).find('select.gudang').addClass('hide');
            $(div).find('select.gudang').removeAttr('data-required');

        } else {
            $(div).find('select.supplier').addClass('hide');
            $(div).find('select.supplier').removeAttr('data-required');
            $(div).find('select.gudang').removeClass('hide');
            $(div).find('select.gudang').attr('data-required', 1);
        }
    }, // end - cek_tujuan

    cek_jenis: function(elm) {
        var div = $(elm).closest('div#rp');
        var jenis = $(div).find('select.jenis_retur').val();
        var unit = $(div).find('select.unit').val();
        var tgl_kirim = $(div).find('div#tgl_kirim input').val();

        if ( empty(jenis) || empty(unit) || empty(tgl_kirim) ) {
            bootbox.alert('Harap isi data filter terlebih dahulu.');

            $(div).find('select.no_order').attr('disabled', 'disabled');
            rp.get_op(null);
        } else {
            var params = {
                'jenis': jenis,
                'unit': unit,
                'tgl_kirim': dateSQL( $(div).find('[name=tgl_kirim]').data('DateTimePicker').date() )
            };
            if ( jenis == 'opkg' ) {
                $(div).find('select.no_order').removeAttr('disabled');
                rp.get_op(params);
            } else if ( jenis == 'opkp' ) {
                $(div).find('select.no_order').removeAttr('disabled');
                rp.get_op(params);
            } 
        }

        // else {
        //     $(div).find('select.no_order').attr('disabled', 'disabled');
        //     rp.get_op(null);
        // }

        rp.cek_tujuan(elm);
    }, // end - cek_jenis

    get_op: function(params) {
        if ( !empty(params) ) {
            $.ajax({
                url : 'transaksi/ReturPakan/get_op',
                data : {
                    'params': params
                },
                type : 'POST',
                dataType : 'JSON',
                beforeSend : function(){ showLoading(); },
                success : function(data){
                    hideLoading();
                    if ( data.status == 1 ) {
                        let opt_ov = '<option value="">Pilih No. Order</option>';
                        $('select.no_order').removeAttr('disabled');

                        var no_order = $('select.no_order').data('noorder');
                        var asal = $('select.no_order').data('asal');
                        var id_asal = $('select.no_order').data('idasal');
                        var tgl_kirim = $('select.no_order').data('tglkirim');

                        if ( !empty(no_order) && no_order != 'undefined' ) {
                            opt_ov += '<option value="'+no_order+'" data-asal="'+asal+'" data-idasal="'+id_asal+'" data-tglkirim="'+tgl_kirim+'" selected >'+no_order.toUpperCase()+'</option>';

                            $('#tgl_retur').data('DateTimePicker').minDate( moment(tgl_kirim) );
                            $('#tgl_retur').data('DateTimePicker').maxDate( moment(new Date()) );
                        }

                        if ( !empty(data.content) && data.content.length > 0 ) {
                            for (var i = 0; i < data.content.length; i++) {
                                opt_ov += '<option value="'+data.content[i].no_order+'" data-asal="'+data.content[i].asal+'" data-idasal="'+data.content[i].id_asal+'" data-tglkirim="'+data.content[i].tgl_kirim+'">'+data.content[i].no_order.toUpperCase()+'</option>';
                            }
                        } else {
                            if ( empty(no_order) || no_order == 'undefined' ) {
                                $('select.no_order').attr('disabled', true);

                                $('div#tgl_retur input').val('');
                                $('div#tgl_retur input').attr('disabled', 'disabled');

                                rp.reset_table();
                            }
                        }

                        $('select.no_order').html( opt_ov );
                    }
                },
            });
        } else {
            let opt_ov = '<option value="">Pilih No. Order</option>';
            $('select.no_order').attr('disabled', true);
            $('select.no_order').html( opt_ov );

            $('div#tgl_retur input').val('');
            $('div#tgl_retur input').attr('disabled', 'disabled');

            $('input.asal').val('');
            
            rp.reset_table();
        }

    }, // end - get_op

    get_detail_order_pakan: function(elm) {
        let div = $(elm).closest('div.detailed');
        let no_order = $(elm).val();
        let asal = $(elm).find('option:selected').data('asal');

        if ( !empty(no_order) ) {
            $(div).find('input.asal').val(asal);
            $(div).find('div#tgl_retur input').removeAttr('disabled');

            var tgl_kirim = $(div).find('select.no_order option:selected').data('tglkirim');

            $.ajax({
                url : 'transaksi/ReturPakan/get_detail_order_pakan',
                data : {
                    'params': no_order
                },
                type : 'GET',
                dataType : 'HTML',
                beforeSend : function(){ showLoading(); },
                success : function(html){
                    $('table.tbl_data_op').find('tbody').html( html );
                    App.formatNumber();

                    $(div).find('#tgl_retur').data('DateTimePicker').minDate( moment(tgl_kirim) );
                    // $(div).find('#tgl_retur').data('DateTimePicker').maxDate( moment(new Date()) );
                    
                    hideLoading();
                },
            });
        } else {
            $(div).find('input.asal').val('');
            $(div).find('div#tgl_retur input').val('');
            $(div).find('div#tgl_retur').attr('disabled', 'disabled');
            rp.reset_table();
        }
    }, // end - get_op

    reset_table: function() {
        let params = {
            'tgl_op' : null,
            'no_order' : null,
            'peternak' : null
        };

        $.ajax({
            url : 'transaksi/ReturPakan/get_data',
            data : {
                'params' : params
            },
            type : 'GET',
            dataType : 'HTML',
            beforeSend : function(){},
            success : function(html){
                $('table.tbl_data_op').find('tbody').html( html );

                App.formatNumber();
            },
        });
    }, // end - reset_table

    cek_jml_retur: function(elm) {
        let tr = $(elm).closest('tr');

        let jml_op = numeral.unformat( $(tr).find('td.jml_op').text() );
        let jml_retur = numeral.unformat( $(elm).val() );

        if ( jml_op < jml_retur ) {
            $(elm).val(0);
            $(tr).find('[data-toggle="tooltip"]').attr('title', 'Jumlah retur melebihi jumlah order').tooltip('show');
            setTimeout(function() {
                $(tr).find('[data-toggle="tooltip"]').tooltip('hide');
            }, 2500);
        } else {
            $(tr).find('[data-toggle="tooltip"]').tooltip('hide');
        };
    }, // end - hit_bb

    save: function() {
        var div_rp = $('div#rp');

        let err = 0;
        let jml_data_ipt = 0;

        $.map( $(div_rp).find('[data-required=1]'), function(ipt) {
            if ( empty( $(ipt).val() ) ) {
                $(ipt).parent().addClass('has-error');
                err++;
            } else {
                $(ipt).parent().removeClass('has-error');
            }
        });

        let table = $(div_rp).find('table.tbl_data_op');

        let jml_data = $(table).find('tbody tr').length;
        $.map( $(table).find('tbody tr'), function(tr) {
            let ipt = $(tr).find('input');
            if ( empty( $(ipt).val() ) ) {
                jml_data_ipt++;
            }
        });

        if ( err > 0 ) {
            bootbox.alert( 'Harap lengkapi data terlebih dahulu.' );
        } else {
            if ( jml_data_ipt == jml_data ) {
                bootbox.alert( 'Tidak ada data yang akan disimpan.' );
            } else {
                bootbox.confirm( 'Apakah anda yakin ingin menyimpan data Retur Pakan ?', function(result) {
                    if ( result ) {
                        let data = null;

                        let jenis_retur = $(div_rp).find('select.jenis_retur').val();
                        let tgl_retur = dateSQL( $(div_rp).find("[name=tgl_retur]").data('DateTimePicker').date() );
                        let no_order = $(div_rp).find("select.no_order").val();
                        let asal = null;
                        if ( jenis_retur == 'opkp' ) {
                            asal = 'peternak';
                        } else {
                            asal = 'gudang';
                        }
                        let id_asal = $(div_rp).find('select.no_order option:selected').data('idasal');
                        let tujuan = $(div_rp).find('select.tujuan').val();
                        let id_tujuan = null;
                        if ( tujuan == 'gudang' ) {
                            id_tujuan = $(div_rp).find('select.gudang').val();
                        } else {
                            id_tujuan = $(div_rp).find('select.supplier').val();
                        }
                        let ongkos_angkut = numeral.unformat($(div_rp).find('input.ongkos_angkut').val());
                        let ekspedisi = $(div_rp).find('.ekspedisi option:selected').attr('data-nama');
                        let ekspedisi_id = $(div_rp).find('.ekspedisi').select2('val');
                        let nopol = $(div_rp).find('input.nopol').val();
                        let sopir = $(div_rp).find('input.sopir').val();
                        let keterangan = $(div_rp).find('textarea.keterangan').val().replace(/^\s*|\s*$/g,"");

                        let data_detail = $.map( $(table).find('tbody tr'), function(tr) {
                            let jml_retur = numeral.unformat( $(tr).find('input.jml_retur').val() );
                            // if ( jml_retur > 0 ) {
                            let _data = {
                                'kode_brg': $(tr).find('td.barang').data('kode'),
                                'jml_retur': jml_retur,
                                'kondisi': $(tr).find('input.kondisi').val()
                            };

                            return _data;
                            // }
                        });

                        data = {
                            'jenis_retur': jenis_retur,
                            'tgl_retur': tgl_retur,
                            'no_order': no_order,
                            'asal': asal,
                            'id_asal': id_asal,
                            'tujuan': tujuan,
                            'id_tujuan': id_tujuan,
                            'ongkos_angkut': ongkos_angkut,
                            'ekspedisi': ekspedisi,
                            'ekspedisi_id': ekspedisi_id,
                            'nopol': nopol,
                            'sopir': sopir,
                            'keterangan': keterangan,
                            'data_detail': data_detail
                        };

                        rp.execute_save( data );
                    };
                });
            };
        }
    }, // end - save

    execute_save: function(params) {
        $.ajax({
            url : 'transaksi/ReturPakan/save',
            data : {
                'params' :  params
            },
            type : 'POST',
            dataType : 'JSON',
            beforeSend : function(){ showLoading(); },
            success : function(data){
                if ( data.status == 1 ) {
                    // rp.hitungStokAwal( data.content.id );
                    rp.hitungStokByTransaksi(data.content.id, data.content.tanggal, data.content.delete, data.content.message);
                } else {
                    hideLoading();
                    bootbox.alert( data.message );
                };
            },
        });
    }, // end - execute_save

    hitungStokAwal: function(id_retur) {
        var params = {
            'id_retur': id_retur
        };

        $.ajax({
            url: 'transaksi/ReturPakan/hitungStokAwal',
            dataType: 'json',
            type: 'post',
            data: {
                'params': params
            },
            beforeSend: function() {},
            success: function(data) {
                hideLoading();
                if ( data.status == 1 ) {
                    bootbox.alert(data.message, function() {
                        rp.load_form();
                    });
                } else {
                    bootbox.alert(data.message);
                };
            },
        });
    }, // end - hitungStokAwal

    edit: function(elm) {
        var div_rp = $('div#rp');

        let err = 0;
        let jml_data_ipt = 0;

        $.map( $(div_rp).find('[data-required=1]'), function(ipt) {
            if ( empty( $(ipt).val() ) ) {
                $(ipt).parent().addClass('has-error');
                err++;
            } else {
                $(ipt).parent().removeClass('has-error');
            }
        });

        let table = $(div_rp).find('table.tbl_data_op');

        let jml_data = $(table).find('tbody tr').length;
        $.map( $(table).find('tbody tr'), function(tr) {
            let ipt = $(tr).find('input');
            if ( empty( $(ipt).val() ) ) {
                jml_data_ipt++;
            }
        });

        if ( err > 0 ) {
            bootbox.alert( 'Harap lengkapi data terlebih dahulu.' );
        } else {
            if ( jml_data_ipt == jml_data ) {
                bootbox.alert( 'Tidak ada data yang akan disimpan.' );
            } else {
                bootbox.confirm( 'Apakah anda yakin ingin menyimpan data Retur Pakan ?', function(result) {
                    if ( result ) {
                        let data = null;

                        let id = $(elm).data('id');
                        let jenis_retur = $(div_rp).find('select.jenis_retur').val();
                        let tgl_retur = dateSQL( $(div_rp).find("[name=tgl_retur]").data('DateTimePicker').date() );
                        let no_order = $(div_rp).find("select.no_order").val();
                        let asal = null;
                        if ( jenis_retur == 'opkp' ) {
                            asal = 'peternak';
                        } else {
                            asal = 'gudang';
                        }
                        let id_asal = $(div_rp).find('select.no_order option:selected').data('idasal');
                        let tujuan = $(div_rp).find('select.tujuan').val();
                        let id_tujuan = null;
                        if ( tujuan == 'gudang' ) {
                            id_tujuan = $(div_rp).find('select.gudang').val();
                        } else {
                            id_tujuan = $(div_rp).find('select.supplier').val();
                        }
                        let ongkos_angkut = numeral.unformat($(div_rp).find('input.ongkos_angkut').val());
                        let ekspedisi = $(div_rp).find('.ekspedisi option:selected').attr('data-nama');
                        let ekspedisi_id = $(div_rp).find('.ekspedisi').select2('val');
                        let nopol = $(div_rp).find('input.nopol').val();
                        let sopir = $(div_rp).find('input.sopir').val();
                        let keterangan = $(div_rp).find('textarea.keterangan').val().replace(/^\s*|\s*$/g,"");

                        let data_detail = $.map( $(table).find('tbody tr'), function(tr) {
                            let jml_retur = numeral.unformat( $(tr).find('input.jml_retur').val() );
                            // if ( jml_retur > 0 ) {
                            let _data = {
                                'kode_brg': $(tr).find('td.barang').data('kode'),
                                'jml_retur': jml_retur,
                                'kondisi': $(tr).find('input.kondisi').val()
                            };

                            return _data;
                            // }
                        });

                        data = {
                            'id': id,
                            'jenis_retur': jenis_retur,
                            'tgl_retur': tgl_retur,
                            'no_order': no_order,
                            'asal': asal,
                            'id_asal': id_asal,
                            'tujuan': tujuan,
                            'id_tujuan': id_tujuan,
                            'ongkos_angkut': ongkos_angkut,
                            'ekspedisi': ekspedisi,
                            'ekspedisi_id': ekspedisi_id,
                            'nopol': nopol,
                            'sopir': sopir,
                            'keterangan': keterangan,
                            'data_detail': data_detail
                        };

                        rp.execute_edit( data );
                    };
                });
            };
        }
    }, // end - edit

    execute_edit: function(params) {
        $.ajax({
            url : 'transaksi/ReturPakan/edit',
            data : {
                'params' : params
            },
            type : 'POST',
            dataType : 'JSON',
            beforeSend : function(){ showLoading(); },
            success : function(data){
                if ( data.status == 1 ) {
                    rp.hitungStokByTransaksi(data.content.id, data.content.tanggal, data.content.delete, data.content.message);
                    // bootbox.alert( data.message, function() {
                    //     // rp.get_lists();
                    //     rp.load_form();
                    // });
                } else {
                    hideLoading();
                    bootbox.alert( data.message );
                };
            },
        });
    }, // end - execute_edit

    delete: function(elm) {
        let id = $(elm).data('id');

        bootbox.confirm( 'Apakah anda yakin ingin meng-hapus data Retur Pakan ?', function(result) {
            if ( result ) {
                $.ajax({
                    url : 'transaksi/ReturPakan/delete',
                    data : {
                        'id' : id
                    },
                    type : 'POST',
                    dataType : 'JSON',
                    beforeSend : function(){ showLoading(); },
                    success : function(data){
                        if ( data.status == 1 ) {
                            rp.hitungStokByTransaksi(data.content.id, data.content.tanggal, data.content.delete, data.content.message);
                            // bootbox.alert( data.message, function() {
                            //     rp.get_lists();
                            //     rp.load_form();
                            // });
                        } else {
                            hideLoading();
                            bootbox.alert( data.message );
                        };
                    },
                });
            }
        });
    }, // end - delete

    hitungStokByTransaksi: function(id, tanggal, _delete, message) {
        var params = {
            'id': id,
            'tanggal': tanggal,
            'delete': _delete,
            'message': message
        }

        $.ajax({
            url: 'transaksi/ReturPakan/hitungStokByTransaksi',
            data: {
                'params': params
            },
            type: 'POST',
            dataType: 'JSON',
            beforeSend: function() {
                showLoading('Hitung Stok Ulang . . .');
            },
            success: function(data) {
                hideLoading();
                if ( data.status == 1 ) {
                    bootbox.alert(message, function() {
                        rp.get_lists();
                        rp.load_form();
                    });
                } else {
                    bootbox.alert(data.message);
                };
            },
        });
    }, // end - hitungStokByTransaksi

    listActivity: function(elm) {
        let tr = $(elm).closest('tr');

        let params = {
            'id' : $(elm).data('id'),
            'tgl_retur' : $(tr).find('td.tgl_retur').text(),
            'no_order' : $(tr).find('td.no_order').text(),
            'asal' : $(tr).find('td.asal').text(),
            'tujuan' : $(tr).find('td.tujuan').text(),
        }

        $.get('transaksi/ReturPakan/listActivity',{
                'params': params
            },function(data){
            var _options = {
                className : 'veryWidth',
                message : data,
                size : 'large',
            };
            bootbox.dialog(_options).bind('shown.bs.modal', function(){
                var modal_dialog = $(this).find('.modal-dialog');
                $(modal_dialog).css({'max-width' : '80%'});
                $(modal_dialog).css({'width' : '80%'});

                var modal_header = $(this).find('.modal-header');
                $(modal_header).css({'padding-top' : '0px'});
            });
        },'html');
    }, // end - listActivity
};

rp.start_up();