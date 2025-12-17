var bk = {
	start_up: function () {
		bk.setting_up();

        if ( !empty($("#StartDate").find('input').data('tgl')) && empty($("#StartDate").find('input').val()) ) {
            var tgl = $("#StartDate").find('input').data('tgl');
            $("#StartDate").data('DateTimePicker').date( moment(new Date((tgl+' 00:00:00'))) );
        }
        if ( !empty($("#EndDate").find('input').data('tgl')) && empty($("#EndDate").find('input').val()) ) {
            var tgl = $("#EndDate").find('input').data('tgl');
            $("#EndDate").data('DateTimePicker').date( moment(new Date((tgl+' 00:00:00'))) );
        }
        bk.getLists();
	}, // end - start_up

	setting_up: function() {
        var today = moment(new Date()).format('YYYY-MM-DD');
        $("#StartDate, #EndDate").datetimepicker({
            locale: 'id',
            format: 'DD MMM Y'
        });

        $("#StartDate").on("dp.change", function (e) {
            var minDate = dateSQL($("#StartDate").data("DateTimePicker").date())+' 00:00:00';
            $("#EndDate").data("DateTimePicker").minDate(moment(new Date(minDate)));
        });
        $("#EndDate").on("dp.change", function (e) {
            var maxDate = dateSQL($("#EndDate").data("DateTimePicker").date())+' 23:59:59';
            if ( maxDate >= (today+' 00:00:00') ) {
                $("#StartDate").data("DateTimePicker").maxDate(moment(new Date(maxDate)));
            }
        });

        $("#TglKk, #TglTempo, #TglCair").datetimepicker({
            locale: 'id',
            format: 'DD MMM Y',
        });
        $.map( $("#TglKk, #TglTempo, #TglCair"), function(div) {
            if ( !empty($(div).find('input').data('tgl')) ) {
                var tgl = $(div).find('input').data('tgl');
                $(div).data('DateTimePicker').date( moment(new Date((tgl))) );
            }
        });
        App.setTutupBulan();

        $('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal],[data-tipe=decimal3],[data-tipe=decimal4],[data-tipe=number]').each(function(){
            $(this).priceFormat(Config[$(this).data('tipe')]);
        });

        $('select.jurnal_trans').select2().on("select2:select", function (e) {
            bk.getDetJurnalTrans();
        });

        $('select.unit').select2();
        $('select.bank').select2();
        $('select.bank_riwayat').select2();

        $('select.no_supplier').select2().on("select2:select", function (e) {
            bk.getNamaSupplier();
        });

        bk.getDetJurnalTrans();
        bk.getNamaSupplier();

        // $('select.no_coa_header').select2({matcher: matchStart}).on('select2:select', function (e) {
        //     var data = e.params.data.element.dataset;
        //     var nama = data.nama;

        //     $('select.nama_coa_header').val(nama).trigger('change');
        // });
        // $('select.nama_coa_header').select2().on('select2:select', function (e) {
        //     var data = e.params.data.element.dataset;
        //     var no = data.no;

        //     $('select.no_coa_header').val(no).trigger('change');
        // });

        // $('select.no_coa').select2({matcher: matchStart}).on('select2:select', function (e) {
        //     var _tr = $(this).closest('tr');

        //     var data = e.params.data.element.dataset;
        //     var nama = data.nama;

        //     $(_tr).find('select.nama_coa').val(nama).trigger('change');
        // });
        // $('select.nama_coa').select2().on('select2:select', function (e) {
        //     var _tr = $(this).closest('tr');

        //     var data = e.params.data.element.dataset;
        //     var no = data.no;

        //     $(_tr).find('select.no_coa').val(no).trigger('change');
        // });
        // $('select.supplier').select2().on('select2:select', function (e) {
        //     var kode_supl = e.params.data.id;

        //     bk.getNoLpb( kode_supl );
        // });
        // $('select.lpb').select2().on('select2:select', function (e) {
        //     var _tr = $(this).closest('tr');

        //     var no_lpb = e.params.data.id;

        //     var data = e.params.data.element.dataset;
        //     var nilai = data.nilai;

        //     $(_tr).find('input.nilai_lpb').val( numeral.format(nilai) );
        //     $(_tr).find('input.nilai').val( numeral.format(nilai) );

        //     var ket = '';
        //     if ( !empty(no_lpb) ) {
        //         $(_tr).find('select.no_coa').val('2102.00.00').trigger('change');
        //         var nama =  $(_tr).find('select.no_coa option:selected').attr('data-nama');

        //         $(_tr).find('select.nama_coa').val(nama).trigger('change');

        //         var nama_supl = $('select.supplier').find('option:selected').attr('data-nama');

        //         ket = 'Pelunasan Hutang a.n '+nama_supl+' / '+no_lpb;
        //         $(_tr).find('input.keterangan').val(ket);
        //     } else {
        //         $(_tr).find('select.no_coa').val('').trigger('change');
        //         $(_tr).find('select.nama_coa').val('').trigger('change');

        //         $(_tr).find('input.keterangan').val(ket);
        //     }

        //     bk.hitGrandTotal( $(this) );
        // });
    }, // end - setting_up

    getDetJurnalTrans: function() {
        var jt_id = $('select.jurnal_trans').find('option:selected').attr('data-id');

        $.map( $('select.det_jurnal_trans'), function(select) {
            $(select).find('option').removeAttr('disabled');
            $(select).find('option:not([data-idjt="'+jt_id+'"])').attr('disabled', 'disabled');
            $(select).find('option[value="all"]').removeAttr('disabled');
            $(select).find('option[value=""]').removeAttr('disabled');
    
            $(select).select2().on("select2:select", function (e) {
                bk.getTujuanCoa( $(select) );
            });

            bk.getTujuanCoa( $(select) );
        });
    }, // end - getDetJurnalTrans

    getTujuanCoa: function(elm) {
        var tr = $(elm).closest('tr');

        $(tr).find('select.tujuan').select2();

        var val_det_jurnal_trans = $(tr).find('select.det_jurnal_trans').select2().val();

        if ( !empty(val_det_jurnal_trans) ) {
            $(tr).find('select.tujuan').attr('disabled', 'disabled');

            var tujuan = $(tr).find('select.det_jurnal_trans option:selected').attr('data-coatujuan');

            $(tr).find('select.tujuan').select2().val( tujuan );
            $(tr).find('select.tujuan').trigger('change');
        } else {
            $(tr).find('select.tujuan').removeAttr('disabled', 'disabled');
        }
    }, // end - getTujuanCoa

    getNamaSupplier: function() {
        var no_supplier = $('select.no_supplier').select2().val();

        $('input.supplier').removeAttr('disabled', 'disabled');
        if ( !empty(no_supplier) ) {
            var nama_pelanggan = $('select.no_supplier').find('option:selected').attr('data-nama');

            $('input.supplier').val( nama_pelanggan.toUpperCase() );
            $('input.supplier').attr('disabled', 'disabled');
        } else {
            // $('input.supplier').val(null);
        }
    }, // end - getData

    addRow: function (elm) {
        var tr = $(elm).closest('tr');
        var tbody = $(tr).closest('tbody');

        $(tr).find('select.det_jurnal_trans, select.tujuan').select2('destroy')
                                   .removeAttr('data-live-search')
                                   .removeAttr('data-select2-id')
                                   .removeAttr('aria-hidden')
                                   .removeAttr('tabindex');
        $(tr).find('select.det_jurnal_trans option, select.tujuan option').removeAttr('data-select2-id');

        var tr_clone = $(tr).clone();

        $(tr_clone).find('input, select').val('');

        $(tr_clone).find('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal], [data-tipe=decimal3],[data-tipe=decimal4], [data-tipe=number]').each(function(){
            $(this).priceFormat(Config[$(this).data('tipe')]);
        });

        $(tbody).append( $(tr_clone) );

        bk.getDetJurnalTrans();
        bk.getTujuanCoa();

        // $.each($(tbody).find('select'), function(a) {
        //     if ( $(this).hasClass('no_coa') ) {
        //         $(this).select2({matcher: matchStart}).on('select2:select', function (e) {
        //             var _tr = $(this).closest('tr');
        
        //             var data = e.params.data.element.dataset;
        //             var nama = data.nama;
        
        //             $(_tr).find('select.nama_coa').val(nama).trigger('change');
        //         });
        //     }

        //     if ( $(this).hasClass('nama_coa') ) {
        //         $(this).select2({matcher: matchStart}).on('select2:select', function (e) {
        //             var _tr = $(this).closest('tr');

        //             var data = e.params.data.element.dataset;
        //             var no = data.no;

        //             $(_tr).find('select.no_coa').val(no).trigger('change');
        //         });
        //     }

        //     if ( $(this).hasClass('lpb') ) {
        //         $(this).select2().on('select2:select', function (e) {
        //             var _tr = $(this).closest('tr');

        //             var no_lpb = e.params.data.id;

        //             var data = e.params.data.element.dataset;
        //             var nilai = data.nilai;

        //             $(_tr).find('input.nilai_lpb').val( numeral.format(nilai) );
        //             $(_tr).find('input.nilai').val( numeral.format(nilai) );

        //             var ket = '';
        //             if ( !empty(no_lpb) ) {
        //                 $(_tr).find('select.no_coa').val('2102.00.00').trigger('change');
        //                 var nama =  $(_tr).find('select.no_coa option:selected').attr('data-nama');

        //                 $(_tr).find('select.nama_coa').val(nama).trigger('change');

        //                 var nama_supl = $('select.supplier').find('option:selected').attr('data-nama');

        //                 ket = 'Pelunasan Hutang a.n '+nama_supl+' / '+no_lpb;
        //                 $(_tr).find('input.keterangan').val(ket);
        //             } else {
        //                 $(_tr).find('select.no_coa').val('').trigger('change');
        //                 $(_tr).find('select.nama_coa').val('').trigger('change');

        //                 $(_tr).find('input.keterangan').val(ket);
        //             }

        //             bk.hitGrandTotal( $(this) );
        //         });
        //     }
        // });
    }, // end - addRow

    removeRow: function (elm) {
        var tr = $(elm).closest('tr');
        var tbody = $(tr).closest('tbody');

        if ( $(tbody).find('tr').length > 1 ) {
            $(tr).remove();

            bk.hitGrandTotal( $(tbody).find('tr:first()') );
        }
    }, // end - addRow

    hitGrandTotal: function (elm) {
        var tr = $(elm).closest('tr');
        var tbody = $(tr).closest('tbody');

        var grand_total = 0;
        
        $.map( $(tbody).find('tr'), function (tr) {
            var ipt = $(tr).find('input.nilai');
            var nilai = parseFloat(numeral.unformat( $(ipt).val() ));

            grand_total += nilai;
        });

        $('div.nilai input').val( numeral.format(grand_total) );
    }, // end - hitGrandTotal    

	changeTabActive: function(elm) {
        var vhref = $(elm).data('href');
        var edit = $(elm).data('edit');
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
            var v_id = $(elm).attr('data-kode');

            bk.loadForm(v_id, edit);
        };
    }, // end - changeTabActive

    loadForm: function(v_id = null, resubmit = null) {
        var dcontent = $('div#action');

        $.ajax({
            url : 'accounting/BankKeluar/loadForm',
            data : {
                'id' :  v_id,
                'resubmit' : resubmit
            },
            type : 'GET',
            dataType : 'HTML',
            beforeSend : function(){ showLoading(); },
            success : function(html){
                hideLoading();
                $(dcontent).html(html);
                bk.setting_up();

                // if ( !empty(resubmit) ) {
                //     var kode_supl = $(dcontent).find('select.supplier').select2().val();
                //     if ( !empty(kode_supl) ) {
                //         bk.getNoLpb( kode_supl );
                //     }
                // }
            },
        });
    }, // end - loadForm

	getLists: function() {
        var dcontent = $('div#riwayat');

        var err = 0;
        $.map( $(dcontent).find('[data-required=1]'), function(ipt) {
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
            var tbody = $(dcontent).find('table.tbl_riwayat tbody');

            var params = {
                'start_date': dateSQL( $(dcontent).find('#StartDate').data('DateTimePicker').date() ),
                'end_date': dateSQL( $(dcontent).find('#EndDate').data('DateTimePicker').date() ),
                'bank': $(dcontent).find('select.bank_riwayat').select2().val().toUpperCase()
            };

            // if ($.fn.dataTable.isDataTable('.tbl_riwayat')) {
            //     $('.tbl_riwayat').DataTable().destroy();
            // }

            $.ajax({
                url : 'accounting/BankKeluar/getLists',
                data : {
                    'params' : params
                },
                type : 'GET',
                dataType : 'HTML',
                beforeSend : function(){ App.showLoaderInContent( $(tbody) ); },
                success : function(html){
                    App.hideLoaderInContent( $(tbody), html );

                    // if ( $('.tbl_riwayat').find('tbody tr.data').length > 0 ) {
                    //     $('.tbl_riwayat').DataTable();
                    // }
                },
            });
        }
    }, // end - getLists

    // getNoLpb: function(kode_supl, no_kk = null) {
    //     var dcontent = $('div#action');

    //     no_kk = $(dcontent).find('select.supplier').attr('data-nokk');

    //     var params = {
    //         'kode_supl': kode_supl,
    //         'no_kk': no_kk
    //     };

    //     $.ajax({
    //         url : 'accounting/BankKeluar/getNoLpb',
    //         data : {
    //             'params' : params
    //         },
    //         type : 'GET',
    //         dataType : 'HTML',
    //         beforeSend : function(){ showLoading('Sedang mengambil No. LPB . . .'); },
    //         success : function(html){
    //             hideLoading();

    //             $('select.lpb').html( html );
    //             $.map( $(dcontent).find('table tbody tr'), function(tr) {
    //                 $(tr).find('select.lpb').select2().val('');

    //                 var val = $(tr).find('select.lpb').attr('data-val');
    //                 if ( !empty(val) && !empty(kode_supl) ) {
    //                     $(tr).find('select.lpb').select2().val( val ).trigger('change');
    //                 } else {
    //                     $(tr).find('input.nilai_lpb').val('');
    //                     $(tr).find('select.lpb').select2().val('').trigger('change');
    //                 }
    //             });
    //         },
    //     });
    // }, // end - getNoLpb

    cekData: function() {
        var dcontent = $('#action');
        var err = 0;
		$.map( $(dcontent).find('[data-required=1]'), function(ipt) {
			if ( empty($(ipt).val()) ) {
				$(ipt).parent().addClass('has-error');
				err++;
			} else {
				$(ipt).parent().removeClass('has-error');
			}
		});

        var return_status = 0;
        var return_keterangan_error = null;

		if ( err > 0 ) {
			return_keterangan_error = 'Harap lengkapi data terlebih dahulu.';
		} else {
            var return_status = 1;

            var tgl_lpb = null;
            var keterangan = null;
            var nilai_lpb = null;
            var nilai = null;

            var keterangan_error = null;
            var tanggal = dateSQL( $(dcontent).find('#TglKk').data('DateTimePicker').date() );
            $.map( $(dcontent).find('.tbl_detail tbody tr'), function(tr) {
                var no_lpb = $(tr).find('select.lpb').select2().val();
                if ( !empty(no_lpb) ) {
                    keterangan = $(tr).find('select.lpb option:selected').text();

                    tgl_lpb = $(tr).find('select.lpb option:selected').attr('data-tgllpb');
                    nilai_lpb = numeral.unformat($(tr).find('input.nilai_lpb').val());
                    nilai = numeral.unformat($(tr).find('input.nilai').val());

                    /* CEK TANGGAL */
                    if ( tanggal < tgl_lpb ) {
                        if ( empty( keterangan_error ) ) {
                            keterangan_error = 'Tanggal bank keluar lebih kecil dari tanggal pembelian, cek kembali data yang anda masukkan.';
                        } else {
                            keterangan_error += '<br><br>Tanggal bank keluar lebih kecil dari tanggal pembelian, cek kembali data yang anda masukkan.';
                        }
                        keterangan_error += '<br><b>'+keterangan+'</b>';
                    }

                    /* CEK NOMINAL */
                    if ( nilai_lpb < nilai ) {
                        if ( empty( keterangan_error ) ) {
                            keterangan_error = 'Nominal bayar lebih besar dari nilai pembelian, cek kembali data yang anda masukkan.';
                        } else {
                            keterangan_error += '<br><br>Nominal bayar lebih besar dari nilai pembelian, cek kembali data yang anda masukkan.';
                        }
                        keterangan_error += '<br><b>'+keterangan+'</b>';
                    }
                }
            });

            if ( !empty(keterangan_error) ) {
                return_keterangan_error = keterangan_error;
                return_keterangan_error += '<br><br>Apakah anda yakin ingin tetap menyimpan data bank keluar ?';
            } else {
                return_keterangan_error = 'Apakah anda yakin ingin menyimpan data bank keluar ?';
            }
        }

        return {'status': return_status, 'keterangan': return_keterangan_error}
    }, // end - cekData

	save: function() {
		var dcontent = $('#action');

		var cek_data = bk.cekData();
        var status = cek_data.status;
        var keterangan = cek_data.keterangan;

        if ( status == 0 ) {
			bootbox.alert( keterangan );
		} else {
			bootbox.confirm( keterangan , function(result) {
                if ( result ) {
                    showLoading('Proses simpan data bank keluar . . .');

                    var no_urut = 1;
					var detail = $.map( $(dcontent).find('.tbl_detail tbody tr'), function(tr) {
						var _detail = {
                            'det_jurnal_trans': $(tr).find('select.det_jurnal_trans').select2().val(),
                            // 'coa_asal': $(tr).find('select.det_jurnal_trans option:selected').attr('data-coaasal'),
                            'coa_asal': $(dcontent).find('select.bank').select2().val(),
                            'coa_asal_nama': $(dcontent).find('select.bank option:selected').attr('data-nama'),
                            'coa_tujuan': $(tr).find('select.tujuan').select2().val(),
                            'coa_tujuan_nama': $(tr).find('select.tujuan option:selected').attr('data-nama'),
                            'keterangan': $(tr).find('input.keterangan').val().toUpperCase(),
                            'no_invoice': $(tr).find('input.no_invoice').val(),
							'nilai': numeral.unformat($(tr).find('input.nilai').val())
						};

                        no_urut++;

						return _detail;
					});

					var data = {
						'tgl_kk': dateSQL( $(dcontent).find('#TglKk').data('DateTimePicker').date() ),
						// 'no_coa': $(dcontent).find('select.no_coa_header').select2().val(),
                        'jurnal_trans': $(dcontent).find('select.jurnal_trans').select2().val(),
                        'no_supplier': $(dcontent).find('select.no_supplier').select2().val(),
                        'supplier': $(dcontent).find('input.supplier').val().toUpperCase(),
                        'keterangan': $(dcontent).find('textarea.keterangan').val().trim().toUpperCase(),
                        'coa_bank': $(dcontent).find('select.bank').select2().val().toUpperCase(),
                        'nama_bank': $(dcontent).find('select.bank').find('option:selected').attr('data-nama'),
                        'no_giro': $(dcontent).find('input.no_giro').val().toUpperCase(),
						'tgl_tempo': !empty($(dcontent).find('#TglTempo input').val()) ? dateSQL( $(dcontent).find('#TglTempo').data('DateTimePicker').date() ) : null,
						'tgl_cair': !empty($(dcontent).find('#TglCair input').val()) ? dateSQL( $(dcontent).find('#TglCair').data('DateTimePicker').date() ) : null,
                        'nilai': numeral.unformat($(dcontent).find('div.nilai input').val()),
                        // 'unit': $(dcontent).find('select.unit').select2().val(),
                        'unit': $(dcontent).find('select.bank').find('option:selected').attr('data-unit'),
                        'kode': $(dcontent).find('select.bank').find('option:selected').attr('data-kode'),
						'detail': detail
					};

					$.ajax({
		                url: 'accounting/BankKeluar/save',
		                dataType: 'json',
		                type: 'post',
		                data: {
		                	'params': data
		                },
		                beforeSend: function() {},
		                success: function(data) {
		                    hideLoading();
		                    if ( data.status == 1 ) {
                                bootbox.alert( data.message, function () {
                                    bk.loadForm( data.content.id );
                                });
		                    } else {
		                        bootbox.alert(data.message);
		                    };
		                },
		            });
				}
			});
		}
	}, // end - save

    edit: function(elm) {
        var dcontent = $('#action');
        
        var cek_data = bk.cekData();
        var status = cek_data.status;
        var keterangan = cek_data.keterangan;

        if ( status == 0 ) {
			bootbox.alert( keterangan );
		} else {
            var user_submit = $(elm).attr('data-usersubmit');
            var user_edit = $(elm).attr('data-useredit');

            var exec = 1;
            if ( user_submit != user_edit ) {
                var nominal_old = $(elm).attr('data-nominalold');
                var nilai = numeral.unformat($(dcontent).find('div.nilai input').val());

                if ( nominal_old != nilai ) {
                    exec = 0;
                    bootbox.alert('Nominal yang anda masukkan tidak sama.<br>Nominal Sebelumnya : <b>'+numeral.formatDec(nominal_old)+'</b><br>Nominal Sekarang : <b>'+numeral.formatDec(nilai)+'</b><br><br>Harap cek kembali data yang anda masukkan.');
                }
            }

            if ( exec == 1 ) {
                bootbox.confirm( keterangan , function(result) {
                    if ( result ) {
                        showLoading('Proses simpan data bank keluar . . .');
    
                        var no_urut = 1;
                        var detail = $.map( $(dcontent).find('.tbl_detail tbody tr'), function(tr) {
                            var _detail = {
                                'det_jurnal_trans': $(tr).find('select.det_jurnal_trans').select2().val(),
                                // 'coa_asal': $(tr).find('select.det_jurnal_trans option:selected').attr('data-coaasal'),
                                'coa_asal': $(dcontent).find('select.bank').select2().val(),
                                'coa_asal_nama': $(dcontent).find('select.bank option:selected').attr('data-nama'),
                                'coa_tujuan': $(tr).find('select.tujuan').select2().val(),
                                'coa_tujuan_nama': $(tr).find('select.tujuan option:selected').attr('data-nama'),
                                'keterangan': $(tr).find('input.keterangan').val().toUpperCase(),
                                'no_invoice': $(tr).find('input.no_invoice').val(),
                                'nilai': numeral.unformat($(tr).find('input.nilai').val())
                            };
    
                            no_urut++;
    
                            return _detail;
                        });
    
                        var data = {
                            'no_kk': $(elm).attr('data-kode'),
                            'tgl_kk': dateSQL( $(dcontent).find('#TglKk').data('DateTimePicker').date() ),
                            // 'no_coa': $(dcontent).find('select.no_coa_header').select2().val(),
                            'jurnal_trans': $(dcontent).find('select.jurnal_trans').select2().val(),
                            'no_supplier': $(dcontent).find('select.no_supplier').select2().val(),
                            'supplier': $(dcontent).find('input.supplier').val().toUpperCase(),
                            'keterangan': $(dcontent).find('textarea.keterangan').val().trim().toUpperCase(),
                            'coa_bank': $(dcontent).find('select.bank').select2().val().toUpperCase(),
                            'nama_bank': $(dcontent).find('select.bank').find('option:selected').attr('data-nama'),
                            'no_giro': $(dcontent).find('input.no_giro').val().toUpperCase(),
                            'tgl_tempo': !empty($(dcontent).find('#TglTempo input').val()) ? dateSQL( $(dcontent).find('#TglTempo').data('DateTimePicker').date() ) : null,
                            'tgl_cair': !empty($(dcontent).find('#TglCair input').val()) ? dateSQL( $(dcontent).find('#TglCair').data('DateTimePicker').date() ) : null,
                            'nilai': numeral.unformat($(dcontent).find('div.nilai input').val()),
                            // 'unit': $(dcontent).find('select.unit').select2().val(),
                            'unit': $(dcontent).find('select.bank').find('option:selected').attr('data-unit'),
                            'kode': $(dcontent).find('select.bank').find('option:selected').attr('data-kode'),
                            'detail': detail
                        };
    
                        $.ajax({
                            url: 'accounting/BankKeluar/edit',
                            dataType: 'json',
                            type: 'post',
                            data: {
                                'params': data
                            },
                            beforeSend: function() {},
                            success: function(data) {
                                hideLoading();
                                if ( data.status == 1 ) {
                                    bootbox.alert( data.message, function () {
                                        bk.loadForm( data.content.id );
                                    });
                                } else {
                                    bootbox.alert(data.message);
                                };
                            },
                        });
                    }
                });
            }
        }
    }, // end - edit

    delete: function(elm) {
        var dcontent = $('#action');

        bootbox.confirm('Apakah anda yakin ingin meng-hapus data ?', function(result) {
            if ( result ) {
                showLoading();

                var params = {
                    'no_kk': $(elm).attr('data-kode')
                };

                $.ajax({
                    url: 'accounting/BankKeluar/delete',
                    dataType: 'json',
                    type: 'post',
                    data: {
                        'params': params
                    },
                    beforeSend: function() {},
                    success: function(data) {
                        hideLoading();
                        if ( data.status == 1 ) {
                            bootbox.alert( data.message, function () {
                                bk.getLists();
                                bk.loadForm();
                            });
                        } else {
                            bootbox.alert(data.message);
                        };
                    },
                });
            }
        });
    }, // end - delete

    printPreview: function (elm) {
        var no_so = $(elm).attr('data-kode');

        window.open('accounting/BankKeluar/printPreview/'+no_so, 'blank');
    }, // end - printPreview

    exportPdf : function (elm) {
        var kode = $(elm).attr('data-kode');

        var params = {
            'kode': kode
        };

        $.ajax({
            url: 'accounting/BankKeluar/exportPdf',
            dataType: 'json',
            type: 'post',
            data: {
                'params': params
            },
            beforeSend: function() {
                showLoading('Proses Print . . .');
            },
            success: function(data) {
                hideLoading();
                if ( data.status == 1 ) {
                    // if ( $('iframe').length > 0 ) {
                    //     $('iframe').remove();
                    // }

                    // var ifr = document.createElement("iframe");
                    // ifr.src = data.content.url;
                    // ifr.id = "PDF";
                    // ifr.style.width = "0px";
                    // ifr.style.height = "0px";
                    // ifr.style.border = "0px";
                    // document.body.appendChild(ifr);

                    // var PDFG = document.getElementById("PDF");
                    // PDFG.contentWindow.print();
                } else {
                    bootbox.alert(data.message);
                };
            },
        });
    }, // end - exportPdf
};

bk.start_up();