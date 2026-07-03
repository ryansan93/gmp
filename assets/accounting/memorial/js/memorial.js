var mm = {
	start_up: function () {
		mm.setting_up();

        if ( !empty($("#StartDate").find('input').data('tgl')) && empty($("#StartDate").find('input').val()) ) {
            var tgl = $("#StartDate").find('input').data('tgl');
            $("#StartDate").data('DateTimePicker').date( moment(new Date((tgl+' 00:00:00'))) );
        }
        if ( !empty($("#EndDate").find('input').data('tgl')) && empty($("#EndDate").find('input').val()) ) {
            var tgl = $("#EndDate").find('input').data('tgl');
            $("#EndDate").data('DateTimePicker').date( moment(new Date((tgl+' 00:00:00'))) );
        }
        mm.getLists();
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

        $("#TglMm").datetimepicker({
            locale: 'id',
            format: 'DD MMM Y',
        });
        $.map( $("#TglMm"), function(div) {
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
            mm.getDetJurnalTrans();
        });

        $('select.unit').select2();

        $('select.no_pelanggan').select2().on("select2:select", function (e) {
            mm.getNamaPelanggan();
        });
        $('select.no_supplier').select2().on("select2:select", function (e) {
            mm.getNamaSupplier();
        });

        mm.getDetJurnalTrans();
        mm.getNamaPelanggan();
        mm.getNamaSupplier();

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

        //     mm.getNoLpb( kode_supl );
        // });
        // $('select.lpb').select2().on('select2:select', function (e) {});
        // $('select.customer').select2().on('select2:select', function (e) {
        //     var kode_cust = e.params.data.id;

        //     mm.getNoFaktur( kode_cust );
        // });
        // $('select.faktur').select2().on('select2:select', function (e) {});
    }, // end - setting_up

    getDetJurnalTrans: function() {
        var jt_id = $('select.jurnal_trans').find('option:selected').attr('data-id');

        $.map( $('select.det_jurnal_trans'), function(select) {
            $(select).find('option').removeAttr('disabled');
            $(select).find('option:not([data-idjt="'+jt_id+'"])').attr('disabled', 'disabled');
            $(select).find('option[value="all"]').removeAttr('disabled');
            $(select).find('option[value=""]').removeAttr('disabled');
    
            $(select).select2().on("select2:select", function (e) {
                mm.getAsalTujuanCoa( $(select) );
            });

            mm.getAsalTujuanCoa( $(select) );
        });
    }, // end - getDetJurnalTrans

    getAsalTujuanCoa: function(elm) {
        var tr = $(elm).closest('tr');

        $(tr).find('select.asal').select2();
        $(tr).find('select.tujuan').select2();
        $(tr).find('select.unit').select2();

        var val_det_jurnal_trans = $(tr).find('select.det_jurnal_trans').select2().val();

        if ( !empty(val_det_jurnal_trans) ) {
            $(tr).find('select.asal').attr('disabled', 'disabled');
            $(tr).find('select.tujuan').attr('disabled', 'disabled');

            var asal = $(tr).find('select.det_jurnal_trans option:selected').attr('data-coaasal');
            var tujuan = $(tr).find('select.det_jurnal_trans option:selected').attr('data-coatujuan');

            $(tr).find('select.asal').select2().val( asal );
            $(tr).find('select.asal').trigger('change');
            $(tr).find('select.tujuan').select2().val( tujuan );
            $(tr).find('select.tujuan').trigger('change');
        } else {
            $(tr).find('select.asal').removeAttr('disabled', 'disabled');
            $(tr).find('select.tujuan').removeAttr('disabled', 'disabled');
        }
    }, // end - getTujuanCoa

    getNamaPelanggan: function() {
        var no_pelanggan = $('select.no_pelanggan').select2().val();

        $('input.pelanggan').removeAttr('disabled', 'disabled');
        if ( !empty(no_pelanggan) ) {
            var nama_pelanggan = $('select.no_pelanggan').find('option:selected').attr('data-nama');

            $('input.pelanggan').val( nama_pelanggan.toUpperCase() );
            $('input.pelanggan').attr('disabled', 'disabled');
        } else {
            // $('input.pelanggan').val(null);
        }

        mm.cekPelangganSupplier();
    }, // end - getNamaPelanggan

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

        mm.cekPelangganSupplier();
    }, // end - getNamaSupplier

    cekPelangganSupplier: function() {
        var supplier = $('input.supplier').val();
        var pelanggan = $('input.pelanggan').val();

        $('select.no_pelanggan').removeAttr('disabled', 'disabled');
        // $('input.pelanggan').val(null);
        $('input.pelanggan').removeAttr('disabled', 'disabled');
        $('input.pelanggan').attr('data-required', 1);
        $('select.no_supplier').removeAttr('disabled', 'disabled');
        // $('input.supplier').val(null);
        $('input.supplier').removeAttr('disabled', 'disabled');
        $('input.supplier').attr('data-required', 1);

        if ( !empty(supplier) ) {
            $('select.no_pelanggan').attr('disabled', 'disabled');

            $('input.pelanggan').val(null);
            $('input.pelanggan').attr('disabled', 'disabled');
            $('input.pelanggan').attr('data-required', 0);
        } else if ( !empty(pelanggan) ) {
            $('select.no_supplier').attr('disabled', 'disabled');

            $('input.supplier').val(null);
            $('input.supplier').attr('disabled', 'disabled');
            $('input.supplier').attr('data-required', 0);
        }
    }, // end - cekPelangganSupplier

    addRow: function (elm) {
        var tr = $(elm).closest('tr');
        var tbody = $(tr).closest('tbody');

        $(tr).find('select.unit, select.det_jurnal_trans, select.asal, select.tujuan').select2('destroy')
                                   .removeAttr('data-live-search')
                                   .removeAttr('data-select2-id')
                                   .removeAttr('aria-hidden')
                                   .removeAttr('tabindex');
        $(tr).find('select.unit option, select.det_jurnal_trans option, select.asal option, select.tujuan option').removeAttr('data-select2-id');

        var tr_clone = $(tr).clone();

        $(tr_clone).find('input, select').val('');

        $(tr_clone).find('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal], [data-tipe=decimal3],[data-tipe=decimal4], [data-tipe=number]').each(function(){
            $(this).priceFormat(Config[$(this).data('tipe')]);
        });

        $(tbody).append( $(tr_clone) );

        mm.getDetJurnalTrans();
        mm.getTujuanCoa();

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

        //             var data = e.params.data.element.dataset;
        //             var nilai = data.nilai;
        //             var no_lpb = e.params.data.id;

        //             $(_tr).find('input.debet').val( numeral.format(nilai) );
        //             mm.hitGrandTotal( $(_tr).find('input.debet') );

        //             var ket = '';
        //             if ( !empty(no_lpb) ) {
        //                 $(_tr).find('input.kredit').val(0);
        //                 $(_tr).find('select.faktur').select2().val('');
        //                 $(_tr).find('select.faktur').attr('disabled', 'disabled');

        //                 $(_tr).find('select.no_coa').val('2102.00.00').trigger('change');
        //                 var nama =  $(_tr).find('select.no_coa option:selected').attr('data-nama');

        //                 $(_tr).find('select.nama_coa').val(nama).trigger('change');

        //                 var nama_supl = $('select.supplier').find('option:selected').attr('data-nama');

        //                 ket = 'Pelunasan Hutang a.n '+nama_supl+' / '+no_lpb;
        //                 $(_tr).find('input.keterangan').val(ket);
        //             } else {
        //                 $(_tr).find('select.faktur').removeAttr('disabled');

        //                 $(_tr).find('select.no_coa').val('').trigger('change');
        //                 $(_tr).find('select.nama_coa').val('').trigger('change');

        //                 $(_tr).find('input.keterangan').val(ket);
        //             }
        //         });
        //     }

        //     if ( $(this).hasClass('faktur') ) {
        //         $(this).select2().on('select2:select', function (e) {
        //             var _tr = $(this).closest('tr');

        //             var data = e.params.data.element.dataset;
        //             var nilai = data.nilai;
        //             var no_faktur = e.params.data.id;

        //             $(_tr).find('input.kredit').val( numeral.format(nilai) );
        //             mm.hitGrandTotal( $(_tr).find('input.kredit') );
                    
        //             var ket = '';
        //             if ( !empty(no_faktur) ) {
        //                 $(_tr).find('input.debet').val(0);
        //                 $(_tr).find('select.lpb').select2().val('');
        //                 $(_tr).find('select.lpb').attr('disabled', 'disabled');

        //                 $(_tr).find('select.no_coa').val('1104.02.00').trigger('change');
        //                 var nama =  $(_tr).find('select.no_coa option:selected').attr('data-nama');

        //                 $(_tr).find('select.nama_coa').val(nama).trigger('change');

        //                 var nama_cust = $('select.customer').find('option:selected').attr('data-nama');

        //                 ket = 'Pelunasan Piutang a.n '+nama_cust+' / '+no_faktur;
        //                 $(_tr).find('input.keterangan').val(ket);
        //             } else {
        //                 $(_tr).find('select.lpb').removeAttr('disabled');

        //                 $(_tr).find('select.no_coa').val('').trigger('change');
        //                 $(_tr).find('select.nama_coa').val('').trigger('change');

        //                 $(_tr).find('input.keterangan').val(ket);
        //             }
        //         });
        //     }
        // });
    }, // end - addRow

    removeRow: function (elm) {
        var tr = $(elm).closest('tr');
        var tbody = $(tr).closest('tbody');

        if ( $(tbody).find('tr').length > 1 ) {
            $(tr).remove();

            mm.hitGrandTotal( $(tbody).find('tr:first()') );
        }
    }, // end - addRow

    hitGrandTotal: function (elm) {
        var tr = $(elm).closest('tr');
        var tbody = $(tr).closest('tbody');

        var grand_total = 0;
        var grand_total_debet = 0;
        var grand_total_kredit = 0;
        
        $.map( $(tbody).find('tr'), function (tr) {
            var ipt = $(tr).find('input.nilai');
            var nilai = parseFloat(numeral.unformat( $(ipt).val() ));

            var coa_asal = $(tr).find('select.asal').select2().val();
            var coa_tujuan = $(tr).find('select.tujuan').select2().val();
            if ( !empty(coa_asal) ) {
                grand_total_kredit += nilai;
            }
            if ( !empty(coa_tujuan) ) {
                grand_total_debet += nilai;
            }

            grand_total += nilai;
        });

        $('div.nilai input.nilai').val( numeral.format(grand_total) );
        $('div.nilai input.tot_debet').val( numeral.format(grand_total_debet) );
        $('div.nilai input.tot_kredit').val( numeral.format(grand_total_kredit) );
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

            mm.loadForm(v_id, edit);
        };
    }, // end - changeTabActive

    loadForm: function(v_id = null, resubmit = null) {
        var dcontent = $('div#action');

        $.ajax({
            url : 'accounting/Memorial/loadForm',
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
                mm.setting_up();

                // if ( !empty(resubmit) ) {
                //     var kode_cust = $(dcontent).find('select.customer').select2().val();
                //     if ( !empty(kode_cust) ) {
                //         mm.getNoFaktur( kode_cust );
                //     }

                //     var kode_supl = $(dcontent).find('select.supplier').select2().val();
                //     if ( !empty(kode_supl) ) {
                //         mm.getNoLpb( kode_supl );
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
                'end_date': dateSQL( $(dcontent).find('#EndDate').data('DateTimePicker').date() )
            };

            // if ($.fn.dataTable.isDataTable('.tbl_riwayat')) {
            //     $('.tbl_riwayat').DataTable().destroy();
            // }

            $.ajax({
                url : 'accounting/Memorial/getLists',
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

    // getNoLpb: function(kode_supl, no_mm = null) {
    //     var dcontent = $('div#action');

    //     no_mm = $(dcontent).find('select.supplier').attr('data-nomm');

    //     var params = {
    //         'kode_supl': kode_supl,
    //         'no_mm': no_mm
    //     };

    //     $.ajax({
    //         url : 'accounting/Memorial/getNoLpb',
    //         data : {
    //             'params' : params
    //         },
    //         type : 'GET',
    //         dataType : 'HTML',
    //         beforeSend : function(){ showLoading('Sedang mengambil No. LPB . . .'); },
    //         success : function(html){
    //             hideLoading();

    //             $.map( $(dcontent).find('table tbody tr'), function(tr) {
    //                 $(tr).find('select.lpb').html( html );
    //                 $(tr).find('select.lpb').select2().val('');

    //                 var val = $(tr).find('select.lpb').attr('data-val');
    //                 if ( !empty(val) && !empty(kode_supl) ) {
    //                     $(tr).find('select.lpb').select2().val( val );
    //                     $(tr).find('select.lpb').attr('data-val', '');
    //                     $(tr).find('select.faktur').attr('disabled', 'disabled');
    //                 } else {
    //                     $(tr).find('input.debet').val(0);
    //                     $(tr).find('select.faktur').removeAttr('disabled');
    //                     mm.hitGrandTotal( $(tr).find('input.debet') );
    //                 }

    //                 $(tr).find('select.lpb').select2().on('select2:select', function (e) {
    //                     var _tr = $(this).closest('tr');

    //                     var data = e.params.data.element.dataset;
    //                     var nilai = data.nilai;
    //                     var no_lpb = e.params.data.id;

    //                     $(_tr).find('input.debet').val( numeral.format(nilai) );
    //                     mm.hitGrandTotal( $(_tr).find('input.debet') );

    //                     var ket = '';
    //                     if ( !empty(no_lpb) ) {
    //                         $(_tr).find('input.kredit').val(0);
    //                         $(_tr).find('select.faktur').select2().val('');
    //                         $(_tr).find('select.faktur').attr('disabled', 'disabled');

    //                         $(_tr).find('select.no_coa').val('2102.00.00').trigger('change');
    //                         var nama =  $(_tr).find('select.no_coa option:selected').attr('data-nama');

    //                         $(_tr).find('select.nama_coa').val(nama).trigger('change');

    //                         var nama_supl = $('select.supplier').find('option:selected').attr('data-nama');

    //                         ket = 'Pelunasan Hutang a.n '+nama_supl+' / '+no_lpb;
    //                         $(_tr).find('input.keterangan').val(ket);
    //                     } else {
    //                         $(_tr).find('select.faktur').removeAttr('disabled');

    //                         $(_tr).find('select.no_coa').val('').trigger('change');
    //                         $(_tr).find('select.nama_coa').val('').trigger('change');

    //                         $(_tr).find('input.keterangan').val(ket);
    //                     }
    //                 });
    //             });
    //         },
    //     });
    // }, // end - getNoLpb

    // getNoFaktur: function(kode_cust, no_mm = null) {
    //     var dcontent = $('div#action');

    //     no_mm = $(dcontent).find('select.customer').attr('data-nomm');

    //     var params = {
    //         'kode_cust': kode_cust,
    //         'no_mm': no_mm
    //     };

    //     $.ajax({
    //         url : 'accounting/Memorial/getNoFaktur',
    //         data : {
    //             'params' : params
    //         },
    //         type : 'GET',
    //         dataType : 'HTML',
    //         beforeSend : function(){ showLoading('Sedang mengambil No. Faktur . . .'); },
    //         success : function(html){
    //             hideLoading();

    //             $.map( $(dcontent).find('table tbody tr'), function(tr) {
    //                 $(tr).find('select.faktur').html( html );
    //                 $(tr).find('select.faktur').select2().val('');

    //                 var val = $(tr).find('select.faktur').attr('data-val');
    //                 if ( !empty(val) && !empty(kode_cust) ) {
    //                     $(tr).find('select.faktur').select2().val( val );
    //                     $(tr).find('select.faktur').attr('data-val', '');
    //                     $(tr).find('select.lpb').attr('disabled', 'disabled');
    //                 } else {
    //                     $(tr).find('input.kredit').val(0);
    //                     $(tr).find('select.lpb').removeAttr('disabled');
    //                     mm.hitGrandTotal( $(tr).find('input.kredit') );
    //                 }

    //                 $(tr).find('select.faktur').select2().on('select2:select', function (e) {
    //                     var _tr = $(this).closest('tr');

    //                     var data = e.params.data.element.dataset;
    //                     var nilai = data.nilai;
    //                     var no_faktur = e.params.data.id;

    //                     $(_tr).find('input.kredit').val( numeral.format(nilai) );
    //                     mm.hitGrandTotal( $(_tr).find('input.kredit') );
                        
    //                     var ket = '';
    //                     if ( !empty(no_faktur) ) {
    //                         $(_tr).find('input.debet').val(0);
    //                         $(_tr).find('select.lpb').select2().val('');
    //                         $(_tr).find('select.lpb').attr('disabled', 'disabled');

    //                         $(_tr).find('select.no_coa').val('1104.02.00').trigger('change');
    //                         var nama =  $(_tr).find('select.no_coa option:selected').attr('data-nama');

    //                         $(_tr).find('select.nama_coa').val(nama).trigger('change');

    //                         var nama_cust = $('select.customer').find('option:selected').attr('data-nama');

    //                         ket = 'Pelunasan Piutang a.n '+nama_cust+' / '+no_faktur;
    //                         $(_tr).find('input.keterangan').val(ket);
    //                     } else {
    //                         $(_tr).find('select.lpb').removeAttr('disabled');

    //                         $(_tr).find('select.no_coa').val('').trigger('change');
    //                         $(_tr).find('select.nama_coa').val('').trigger('change');

    //                         $(_tr).find('input.keterangan').val(ket);
    //                     }
    //                 });
    //             });
    //         },
    //     });
    // }, // end - getNoFaktur

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
            var tot_debet = numeral.unformat($(dcontent).find('input.tot_debet').val());
            var tot_kredit = numeral.unformat($(dcontent).find('input.tot_kredit').val());

            if ( tot_debet == tot_kredit ) {
                return_status = 1;
    
                var keterangan_faktur = null;
                var keterangan_lpb = null;
                var tgl_faktur = null;
                var nilai_faktur = null;
                var tgl_lpb = null;
                var nilai_lpb = null;
                var debet = null;
                var kredit = null;
    
                var err_coa = 0;
                var keterangan_error = null;
                var tanggal = dateSQL( $(dcontent).find('#TglMm').data('DateTimePicker').date() );
                $.map( $(dcontent).find('.tbl_detail tbody tr'), function(tr) {
                    var coa_asal = $(tr).find('select.asal').select2().val();
                    var coa_tujuan = $(tr).find('select.tujuan').select2().val();

                    $(tr).find('select.asal').removeClass('has-error');
                    $(tr).find('select.tujuan').removeClass('has-error');
                    if ( empty(coa_asal) && empty(coa_tujuan) ) {
                        $(tr).find('select.asal').addClass('has-error');
                        $(tr).find('select.tujuan').addClass('has-error');

                        err_coa++;
                    }

                    var no_faktur = $(tr).find('select.faktur').select2().val();
                    var no_lpb = $(tr).find('select.lpb').select2().val();
                    if ( !empty(no_faktur) || !empty(no_lpb) ) {
                        keterangan_faktur = $(tr).find('select.faktur option:selected').text();
                        keterangan_lpb = $(tr).find('select.lpb option:selected').text();
    
                        tgl_faktur = $(tr).find('select.faktur option:selected').attr('data-tglfaktur');
                        nilai_faktur = $(tr).find('select.faktur option:selected').attr('data-nilai');
    
                        tgl_lpb = $(tr).find('select.lpb option:selected').attr('data-tgllpb');
                        nilai_lpb = $(tr).find('select.lpb option:selected').attr('data-nilai');
    
                        debet = numeral.unformat($(tr).find('input.debet').val());
                        kredit = numeral.unformat($(tr).find('input.kredit').val());

                        var nilai = (debet > 0) ? debet : kredit;
    
                        /* CEK TANGGAL FAKTUR */
                        if ( tanggal < tgl_faktur ) {
                            if ( empty( keterangan_error ) ) {
                                keterangan_error = 'Tanggal jurnal memorial lebih kecil dari tanggal faktur, cek kembali data yang anda masukkan.';
                            } else {
                                keterangan_error += '<br><br>Tanggal jurnal memorial lebih kecil dari tanggal faktur, cek kembali data yang anda masukkan.';
                            }
                            keterangan_error += '<br><b>'+keterangan_faktur+'</b>';
                        }
    
                        /* CEK TANGGAL LPB */
                        if ( tanggal < tgl_lpb ) {
                            if ( empty( keterangan_error ) ) {
                                keterangan_error = 'Tanggal jurnal memorial lebih kecil dari tanggal pembelian, cek kembali data yang anda masukkan.';
                            } else {
                                keterangan_error += '<br><br>Tanggal jurnal memorial lebih kecil dari tanggal pembelian, cek kembali data yang anda masukkan.';
                            }
                            keterangan_error += '<br><b>'+keterangan_lpb+'</b>';
                        }
    
                        /* CEK NOMINAL FAKTUR */
                        if ( nilai_faktur < nilai ) {
                            if ( empty( keterangan_error ) ) {
                                keterangan_error = 'Nominal bayar lebih besar dari nilai faktur, cek kembali data yang anda masukkan.';
                            } else {
                                keterangan_error += '<br><br>Nominal bayar lebih besar dari nilai faktur, cek kembali data yang anda masukkan.';
                            }
                            keterangan_error += '<br><b>'+keterangan_faktur+'</b>';
                        }
    
                        /* CEK NOMINAL LPB */
                        if ( nilai_lpb < nilai ) {
                            if ( empty( keterangan_error ) ) {
                                keterangan_error = 'Nominal bayar lebih besar dari nilai pembelian, cek kembali data yang anda masukkan.';
                            } else {
                                keterangan_error += '<br><br>Nominal bayar lebih besar dari nilai pembelian, cek kembali data yang anda masukkan.';
                            }
                            keterangan_error += '<br><b>'+keterangan_lpb+'</b>';
                        }
                    }
                });
    
                if ( err_coa > 0 ) {
                    return_keterangan_error = 'Ada data coa yang belum di isi, harap cek kembali data anda.';

                    return_status = 0;
                } else {
                    if ( !empty(keterangan_error) ) {
                        return_keterangan_error = keterangan_error;
                        return_keterangan_error += '<br><br>Apakah anda yakin ingin tetap menyimpan data jurnal memorial ?';
                    } else {
                        return_keterangan_error = 'Apakah anda yakin ingin menyimpan data jurnal memorial ?';
                    }
                }
            } else {
                return_keterangan_error = 'Data tidak balance, harap cek kembali.';
            }
        }

        return {'status': return_status, 'keterangan': return_keterangan_error}
    }, // end - cekData

	save: function() {
		var dcontent = $('#action');

        var cek_data = mm.cekData();
        var status = cek_data.status;
        var keterangan = cek_data.keterangan;

        if ( status == 0 ) {
			bootbox.alert( keterangan );
		} else {
			bootbox.confirm( keterangan , function(result) {
                if ( result ) {
                    showLoading('Proses simpan data memorial . . .');

                    var no_urut = 1;
                    var detail = $.map( $(dcontent).find('.tbl_detail tbody tr'), function(tr) {
                        var _detail = {
                            'det_jurnal_trans': $(tr).find('select.det_jurnal_trans').select2().val(),
                            'coa_asal': $(tr).find('select.asal').select2().val(),
                            'coa_asal_nama': $(tr).find('select.asal option:selected').attr('data-nama'),
                            'coa_tujuan': $(tr).find('select.tujuan').select2().val(),
                            'coa_tujuan_nama': $(tr).find('select.tujuan option:selected').attr('data-nama'),
                            'keterangan': $(tr).find('input.keterangan').val().toUpperCase(),
                            'no_invoice': $(tr).find('input.no_invoice').val(),
							'nilai': numeral.unformat($(tr).find('input.nilai').val()),
                            'unit': $(tr).find('select.unit').select2().val()
                        };

                        no_urut++;

                        return _detail;
                    });

                    var data = {
                        'tgl_mm': dateSQL( $(dcontent).find('#TglMm').data('DateTimePicker').date() ),
                        // 'no_coa': $(dcontent).find('select.no_coa_header').select2().val(),
                        'jurnal_trans': $(dcontent).find('select.jurnal_trans').select2().val(),
                        'no_pelanggan': $(dcontent).find('select.no_pelanggan').select2().val(),
                        'pelanggan': $(dcontent).find('input.pelanggan').val().toUpperCase(),
                        'no_supplier': $(dcontent).find('select.no_supplier').select2().val(),
                        'supplier': $(dcontent).find('input.supplier').val().toUpperCase(),
                        'keterangan': $(dcontent).find('textarea.keterangan').val().trim().toUpperCase(),
                        // 'coa_bank': $(dcontent).find('select.bank').select2().val().toUpperCase(),
                        // 'nama_bank': $(dcontent).find('select.bank').find('option:selected').attr('data-nama'),
                        // 'no_giro': $(dcontent).find('input.no_giro').val().toUpperCase(),
						// 'tgl_tempo': !empty($(dcontent).find('#TglTempo input').val()) ? dateSQL( $(dcontent).find('#TglTempo').data('DateTimePicker').date() ) : null,
						// 'tgl_cair': !empty($(dcontent).find('#TglCair input').val()) ? dateSQL( $(dcontent).find('#TglCair').data('DateTimePicker').date() ) : null,
                        'nilai': numeral.unformat($(dcontent).find('div.nilai input').val()),
						// 'unit': $(dcontent).find('select.unit').select2().val(),
                        'detail': detail
                    };

                    $.ajax({
                        url: 'accounting/Memorial/save',
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
                                    mm.loadForm( data.content.id );
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

        var cek_data = mm.cekData();
        var status = cek_data.status;
        var keterangan = cek_data.keterangan;

        if ( status == 0 ) {
			bootbox.alert( keterangan );
		} else {
			bootbox.confirm( keterangan , function(result) {
                if ( result ) {
                    showLoading('Proses simpan data kas masuk . . .');

                    var no_urut = 1;
                    var detail = $.map( $(dcontent).find('.tbl_detail tbody tr'), function(tr) {
                        var _detail = {
                            'det_jurnal_trans': $(tr).find('select.det_jurnal_trans').select2().val(),
                            'coa_asal': $(tr).find('select.asal').select2().val(),
                            'coa_asal_nama': $(tr).find('select.asal option:selected').attr('data-nama'),
                            'coa_tujuan': $(tr).find('select.tujuan').select2().val(),
                            'coa_tujuan_nama': $(tr).find('select.tujuan option:selected').attr('data-nama'),
                            'keterangan': $(tr).find('input.keterangan').val().toUpperCase(),
                            'no_invoice': $(tr).find('input.no_invoice').val(),
							'nilai': numeral.unformat($(tr).find('input.nilai').val()),
                            'unit': $(tr).find('select.unit').select2().val()
                        };

                        no_urut++;

                        return _detail;
                    });

                    var data = {
                        'no_mm': $(elm).attr('data-kode'),
                        'tgl_mm': dateSQL( $(dcontent).find('#TglMm').data('DateTimePicker').date() ),
                        // 'no_coa': $(dcontent).find('select.no_coa_header').select2().val(),
                        'jurnal_trans': $(dcontent).find('select.jurnal_trans').select2().val(),
                        'no_pelanggan': $(dcontent).find('select.no_pelanggan').select2().val(),
                        'pelanggan': $(dcontent).find('input.pelanggan').val().toUpperCase(),
                        'no_supplier': $(dcontent).find('select.no_supplier').select2().val(),
                        'supplier': $(dcontent).find('input.supplier').val().toUpperCase(),
                        'keterangan': $(dcontent).find('textarea.keterangan').val().trim().toUpperCase(),
                        // 'coa_bank': $(dcontent).find('select.bank').select2().val().toUpperCase(),
                        // 'nama_bank': $(dcontent).find('select.bank').find('option:selected').attr('data-nama'),
                        // 'no_giro': $(dcontent).find('input.no_giro').val().toUpperCase(),
						// 'tgl_tempo': !empty($(dcontent).find('#TglTempo input').val()) ? dateSQL( $(dcontent).find('#TglTempo').data('DateTimePicker').date() ) : null,
						// 'tgl_cair': !empty($(dcontent).find('#TglCair input').val()) ? dateSQL( $(dcontent).find('#TglCair').data('DateTimePicker').date() ) : null,
                        'nilai': numeral.unformat($(dcontent).find('div.nilai input').val()),
						// 'unit': $(dcontent).find('select.unit').select2().val(),
                        'detail': detail
                    };

                    $.ajax({
                        url: 'accounting/Memorial/edit',
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
                                    mm.loadForm( data.content.id );
                                });
                            } else {
                                bootbox.alert(data.message);
                            };
                        },
                    });
                }
            });
        }
    }, // end - edit

    delete: function(elm) {
        var dcontent = $('#action');

        bootbox.confirm('Apakah anda yakin ingin meng-hapus data ?', function(result) {
            if ( result ) {
                showLoading();

                var params = {
                    'no_mm': $(elm).attr('data-kode')
                };

                $.ajax({
                    url: 'accounting/Memorial/delete',
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
                                mm.getLists();
                                mm.loadForm();
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

        window.open('accounting/Memorial/printPreview/'+no_so, 'blank');
    }, // end - printPreview

    exportPdf : function (elm) {
        var kode = $(elm).attr('data-kode');

        var params = {
            'kode': kode
        };

        $.ajax({
            url: 'accounting/Memorial/exportPdf',
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

mm.start_up();