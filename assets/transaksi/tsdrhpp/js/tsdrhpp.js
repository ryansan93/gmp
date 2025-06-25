var tsdrhpp = {
	start_up: function () {
		tsdrhpp.setting_up();
		// tsdrhpp.get_lists();
	}, // end - start_up

    add_row: function(elm) {
        var tr = $(elm).closest('tr');
        var tr_clone = $(tr).clone();

        $(tr_clone).find('input').val('');

        $(tr_clone).find('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal], [data-tipe=decimal3],[data-tipe=decimal4], [data-tipe=number]').each(function(){
            $(this).priceFormat(Config[$(this).data('tipe')]);
        });

        tr.after(tr_clone);
    }, // end - add_row

    remove_row: function(elm) {
        var tr = $(elm).closest('tr');
        var tbody = $(tr).closest('tbody');

        if ($(tbody).find('tr.non_potongan_peralatan').length > 1) {
            $(tr).remove();
        }
    }, // end - remove_row

    remove_row_bonus: function(elm) {
        var tr = $(elm).closest('tr');
        var tbody = $(tr).closest('tbody');

        if ($(tbody).find('tr:not(.top_bottom)').length > 1) {
            $(tr).remove();
        }
    }, // end - remove_row_bonus

	setting_up: function() {
		$("[name=startDate]").datetimepicker({
			locale: 'id',
            format: 'DD MMM Y'
		});
		$("[name=endDate]").datetimepicker({
			locale: 'id',
            format: 'DD MMM Y',
			useCurrent: false //Important! See issue #1075
		});
		$("[name=startDate]").on("dp.change", function (e) {
			$("[name=endDate]").data("DateTimePicker").minDate(e.date);
			// $("[name=endDate]").data("DateTimePicker").date(e.date);
		});
		$("[name=endDate]").on("dp.change", function (e) {
			$('[name=startDate]').data("DateTimePicker").maxDate(e.date);
		});

		$('select.filter').val(1);
		tsdrhpp.filter($('select.filter'));

		$("[name=tutup_siklus]").datetimepicker({
			locale: 'id',
            format: 'DD MMM Y'
		});

		$.map( $('.date'), function (div_date){
			var val_date = $(div_date).find('input').data('tgl');
			if ( !empty( val_date ) ) {
				$(div_date).data('DateTimePicker').date(new Date(val_date));
			}
		});
	}, // end - setting_up

	filter: function (elm) {
		let filter = $(elm).val();
		let div = $(elm).closest('div.action');

		// if ( filter == 1 ) {
		// 	$(div).find('div[name=startDate] input').parent().removeClass('has-error');
		// 	$(div).find('div[name=endDate] input').parent().removeClass('has-error');

		// 	$(div).find('div[name=startDate] input').val(null);
		// 	$(div).find('div[name=endDate] input').val(null);

		// 	$(div).find('div[name=startDate] input').attr('readonly', true);
		// 	$(div).find('div[name=endDate] input').attr('readonly', true);

		// 	$(div).find('div[name=startDate] input').removeAttr('data-required');
		// 	$(div).find('div[name=endDate] input').removeAttr('data-required');
		// } else {
		// 	$(div).find('div[name=startDate] input').removeAttr('readonly', true);
		// 	$(div).find('div[name=endDate] input').removeAttr('readonly', true);

		// 	$(div).find('div[name=startDate] input').attr('data-required', 1);
		// 	$(div).find('div[name=endDate] input').attr('data-required', 1);
		// };
	}, // end - filter

	get_lists: function() {
		let err = 0;

		$.map( $('div.search').find('[data-required=1]'), function(ipt) {
			if ( empty( $(ipt).val() ) ) {
				$(ipt).parent().addClass('has-error');
				err++;
			} else {
				$(ipt).parent().removeClass('has-error');
			};
		});

		if ( err > 0 ) {
			bootbox.alert( 'Harap lengkapi data terlebih dahulu.' );
		} else {
			let start_date = null;
			if ( !empty( $('div[name=startDate] input').val() ) ) {
				start_date = dateSQL($('#StartDate').data('DateTimePicker').date());
			};

			let end_date = null;
			if ( !empty( $('div[name=endDate] input').val() ) ) {
				end_date =dateSQL($('#EndDate').data('DateTimePicker').date());
			};

			let params = {
				'filter': $('select.filter').val(),
				'start_date': start_date,
				'end_date': end_date
			};

			$.ajax({
	            url : 'transaksi/TSDRHPP/get_lists',
	            data : {
	                'params': params
	            },
	            type : 'GET',
	            dataType : 'HTML',
	            beforeSend : function(){ showLoading(); },
	            success : function(html){
	            	$('table.tbl_rhpp').find('tbody').html( html );
	                hideLoading();
	            },
	        });
		};
	}, // end - get_lists

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

        if ( vhref == 'rhpp' ) {
        	let tr = $(elm).closest('tr');
			let noreg = $(elm).attr('data-noreg');

            let v_id = $(elm).attr('data-id');
            tsdrhpp.load_form(noreg, v_id);
        };
    }, // end - changeTabActive

    load_form: function(noreg = null, id = null) {
        var dcontent = $('div#rhpp');

        $.ajax({
            url : 'transaksi/TSDRHPP/load_form',
            data : {
                'noreg' :  noreg,
                'id' :  id
            },
            type : 'GET',
            dataType : 'HTML',
            beforeSend : function(){ 
            	showLoading();
            	// App.showLoaderInContent(dcontent); 
           	},
            success : function(html){
            	hideLoading();
            	$(dcontent).html( html );
                // App.hideLoaderInContent(dcontent, html);
                App.formatNumber();

                tsdrhpp.setting_up();
            },
        });
    }, // end - load_form

    setCn: function(elm) {
        var cn = numeral.unformat( $('input.nilai_cn').val() );
        var biaya_opr = numeral.unformat( $('input.nilai_opr').val() );

        $('td.cn').attr('data-val', cn);
        $('td.cn').text(numeral.formatInt(cn));

		$('td.biaya_opr').attr('data-val', biaya_opr);
        $('td.biaya_opr').text(numeral.formatInt(biaya_opr));

        tsdrhpp.hit_tot_pemasukan_inti();
    }, // end - setCn

    setVal: function(elm) {
        var td = $(elm).closest('td');

        var val = numeral.unformat( $(elm).val() );

        $(td).attr('data-val', val);

        tsdrhpp.hit_tot_pemasukan_inti();
    }, // end - setVal

    cek_jumlah_bayar: function(elm) {
        var td = $(elm).closest('td.jumlah_tagihan');

        var jumlah_bayar = numeral.unformat($(elm).val());
        var jumlah_tagihan = $(td).attr('data-jmltagihan');

        if ( jumlah_bayar > jumlah_tagihan ) {
            $(elm).val(0);

            $(elm).tooltip({
                placement: "top",
                title: "Pembayaran tidak boleh melebihi tagihan",
                trigger: 'manual'
            }).tooltip('show');
        } else {
            tsdrhpp.hit_tot_potongan(elm);

            $(elm).tooltip('hide');
        }
    }, // end - cek_jumlah_bayar

    hit_bonus_insentif_listrik: function(elm) {
    	var div_rhpp_plasma = $('#rhpp_plasma');

    	var populasi = numeral.unformat($(elm).val());
    	var bonus = $(div_rhpp_plasma).find('td.bonus_insentif_listrik').data('bonus');

    	var total_bonus = populasi * bonus;

    	$(div_rhpp_plasma).find('td.bonus_insentif_listrik').attr('data-val', total_bonus);
    	$(div_rhpp_plasma).find('td.bonus_insentif_listrik').html(numeral.formatInt(total_bonus));

    	tsdrhpp.hit_tot_pemasukan_plasma();
    }, // end - hit_bonus_insentif_listrik

    hit_tot_pemasukan_plasma: function() {
    	var div_rhpp_plasma = $('#rhpp_plasma');

    	var tot_penjualan_ayam = parseFloat($(div_rhpp_plasma).find('td.tot_penjualan_ayam').attr('data-val'));
    	var bonus_pasar = parseFloat($(div_rhpp_plasma).find('td.bonus_pasar').attr('data-val'));
    	var bonus_kematian = parseFloat($(div_rhpp_plasma).find('td.bonus_kematian').attr('data-val'));
    	var bonus_insentif_fcr = parseFloat($(div_rhpp_plasma).find('td.bonus_insentif_fcr').attr('data-val'));
    	var bonus_insentif_listrik = parseFloat($(div_rhpp_plasma).find('td.bonus_insentif_listrik').attr('data-val'));

    	var tot_pemasukan_plasma = tot_penjualan_ayam + bonus_pasar + bonus_kematian + bonus_insentif_fcr + bonus_insentif_listrik;

        $(div_rhpp_plasma).find('td.total_pemasukan b').text( numeral.formatInt(tot_pemasukan_plasma) );
    	$(div_rhpp_plasma).find('td.total_pemasukan').attr('data-val', tot_pemasukan_plasma );

    	tsdrhpp.hit_pendapatan_peternak();
    }, // end - hit_tot_pemasukan_plasma

    hit_tot_bonus: function(elm) {
        var tbody = $(elm).closest('tbody');

        var tot_bonus = 0;
        $.map( $(tbody).find('tr.bonus'), function(tr) {
            var jumlah = numeral.unformat($(tr).find('input.jumlah_bonus').val());

            tot_bonus += jumlah;
        });

        $(tbody).find('td.total_bonus').attr('data-val', tot_bonus);
        $(tbody).find('td.total_bonus b').text(numeral.formatDec(tot_bonus));

        tsdrhpp.hit_pendapatan_peternak();
    }, // end - hit_tot_bonus

    hit_tot_pemasukan_inti: function() {
        var div_rhpp_inti = $('#rhpp_inti');

        var tot_penjualan_ayam = parseFloat($(div_rhpp_inti).find('td.tot_penjualan_ayam').attr('data-val'));
        var _cn = $(div_rhpp_inti).find('td.cn').attr('data-val');
        var cn = !empty(_cn) ? parseFloat($(div_rhpp_inti).find('td.cn').attr('data-val')) : 0;

        var tot_pemasukan_plasma = tot_penjualan_ayam + cn;

        $(div_rhpp_inti).find('td.total_pemasukan b').text( numeral.formatInt(tot_pemasukan_plasma) );
        $(div_rhpp_inti).find('td.total_pemasukan').attr('data-val', tot_pemasukan_plasma );

        tsdrhpp.hit_tot_pengeluaran();
    }, // end - hit_tot_pemasukan_inti

    hit_tot_pengeluaran: function(elm) {
    	var tr = $(elm).closest('tr');
    	var tbody = $(tr).closest('tbody');

    	var div_rhpp_plasma = $('#rhpp_plasma');
    	var div_rhpp_inti = $('#rhpp_inti');

    	var total_pembelian_sapronak_plasma = $(div_rhpp_plasma).find('td.tot_pembelian_sapronak').data('val');

        var tot_pembelian_sapronak = parseFloat($(div_rhpp_inti).find('td.tot_pembelian_sapronak').attr('data-val'));
        var pendapatan_peternak_form_inti = parseFloat($(div_rhpp_inti).find('td.pendapatan_peternak_form_inti').attr('data-val'));
        var biaya_opr = parseFloat($(div_rhpp_inti).find('td.biaya_opr').attr('data-val'));
    	var total_pembelian_sapronak_inti = tot_pembelian_sapronak + pendapatan_peternak_form_inti + biaya_opr;

    	var biaya_materai = numeral.unformat( $(elm).val() );

    	$(div_rhpp_plasma).find('span.biaya_materai').html( numeral.formatInt(biaya_materai) );
        $(div_rhpp_inti).find('span.biaya_materai').html( numeral.formatInt(biaya_materai) );
    	$(div_rhpp_inti).find('span.biaya_materai').attr('data-val', biaya_materai);

    	var tot_pengeluaran_plasma = parseInt(total_pembelian_sapronak_plasma) + biaya_materai;
    	var tot_pengeluaran_inti = parseInt(total_pembelian_sapronak_inti) + biaya_materai;

        $(div_rhpp_plasma).find('td.total_pengeluaran b').html( numeral.formatInt(tot_pengeluaran_plasma) );
    	$(div_rhpp_plasma).find('td.total_pengeluaran').attr( 'data-val', tot_pengeluaran_plasma );
    	$(div_rhpp_inti).find('td.total_pengeluaran b').html( numeral.formatInt(tot_pengeluaran_inti) );
        $(div_rhpp_inti).find('td.total_pengeluaran').attr( 'data-val', tot_pengeluaran_inti );

    	tsdrhpp.hit_pendapatan_peternak();
    }, // end - hit_tot_pengeluaran

    hit_tot_potongan: function(elm) {
        var tbody = $(elm).closest('tbody');

        var tot_potongan_peralatan = 0;
        $.map( $(tbody).find('tr.potongan_peralatan'), function(tr) {
            var jumlah = numeral.unformat($(tr).find('input.jumlah_bayar').val());

            tot_potongan_peralatan += jumlah;
        });

        var tot_non_potongan_peralatan = 0;
        $.map( $(tbody).find('tr.non_potongan_peralatan'), function(tr) {
            var jumlah = numeral.unformat($(tr).find('input.jumlah_bayar').val());

            tot_non_potongan_peralatan += jumlah;
        });

        var tot_potongan = tot_potongan_peralatan + tot_non_potongan_peralatan;

        $(tbody).find('td.total_potongan').attr('data-val', tot_potongan);
        $(tbody).find('td.total_potongan b').text(numeral.formatDec(tot_potongan));

        tsdrhpp.hit_pendapatan_peternak();
    }, // end - hit_tot_potongan

    hit_pendapatan_peternak: function() {
    	var div_rhpp_plasma = $('#rhpp_plasma');
    	var div_rhpp_inti = $('#rhpp_inti');

        var tot_pemasukan_plasma = parseFloat($(div_rhpp_plasma).find('td.total_pemasukan').attr('data-val'));
    	var tot_bonus_plasma = numeral.unformat($(div_rhpp_plasma).find('td.total_bonus b').text());
        var tot_pengeluaran_plasma = parseFloat($(div_rhpp_plasma).find('td.total_pengeluaran').attr('data-val'));
    	var tot_potongan_plasma = numeral.unformat($(div_rhpp_plasma).find('td.total_potongan b').text());

    	var tot_pemasukan_inti = $(div_rhpp_inti).find('td.total_pemasukan').attr('data-val');

        var pendapatan_peternak = (tot_pemasukan_plasma + tot_bonus_plasma) - (tot_pengeluaran_plasma + tot_potongan_plasma);

        var pendapatan_peternak_form_inti = pendapatan_peternak;
        var tot_pembelian_sapronak = parseFloat($(div_rhpp_inti).find('td.tot_pembelian_sapronak').attr('data-val'));
        var biaya_opr = parseFloat($(div_rhpp_inti).find('td.biaya_opr').attr('data-val'));
        var biaya_materai = parseFloat($(div_rhpp_inti).find('span.biaya_materai').attr('data-val'));
        var tot_pengeluaran_inti = (pendapatan_peternak_form_inti > 0) ? tot_pembelian_sapronak + biaya_opr + biaya_materai + pendapatan_peternak_form_inti : tot_pembelian_sapronak + biaya_opr + biaya_materai;

        $(div_rhpp_inti).find('td.total_pengeluaran b').html( numeral.formatInt(tot_pengeluaran_inti) );
        $(div_rhpp_inti).find('td.total_pengeluaran').attr( 'data-val', tot_pengeluaran_inti );
    	var pendapatan_inti = tot_pemasukan_inti - tot_pengeluaran_inti;

        var text_pendapatan_peternak = numeral.formatInt(pendapatan_peternak);
        var text_pendapatan_peternak_form_inti = numeral.formatInt(pendapatan_peternak_form_inti);
        var text_pendapatan_inti = numeral.formatInt(pendapatan_inti);
        if ( pendapatan_peternak < 0 ) {
            text_pendapatan_peternak = '('+numeral.formatInt(Math.abs(pendapatan_peternak))+')';
            pendapatan_peternak_form_inti = 0;
            text_pendapatan_peternak_form_inti = '-';
        }
        if ( pendapatan_inti < 0 ) {
            text_pendapatan_inti = '('+numeral.formatInt(Math.abs(pendapatan_inti))+')';
        }

    	$(div_rhpp_plasma).find('td.pendapatan_peternak').attr('data-val', pendapatan_peternak);
    	$(div_rhpp_plasma).find('td.pendapatan_peternak b').html( text_pendapatan_peternak );

        $(div_rhpp_inti).find('td.pendapatan_peternak_form_inti').attr('data-val', pendapatan_peternak_form_inti);
    	$(div_rhpp_inti).find('td.pendapatan_peternak_form_inti').html( text_pendapatan_peternak_form_inti );

        $(div_rhpp_inti).find('td.pendapatan_peternak').attr('data-val', pendapatan_inti);
        $(div_rhpp_inti).find('td.pendapatan_peternak b').html( text_pendapatan_inti );

    	tsdrhpp.hit_potongan_pajak( $('select.prs_potongan') );
    }, // end - hit_pendapatan_peternak

    hit_potongan_pajak: function(elm) {
    	var div_rhpp_plasma = $('#rhpp_plasma');
    	var div_rhpp_inti = $('#rhpp_inti');

    	var prs_potongan = 0;
    	var potongan_pajak_plasma = 0;
    	var potongan_pajak_inti = 0;
    	if ( !empty($(elm).val()) ) {
    		prs_potongan = numeral.unformat($(elm).find('option:selected').text());
    		var pendapatan_peternak = $(div_rhpp_plasma).find('td.pendapatan_peternak').attr('data-val');
    		var pendapatan_inti = $(div_rhpp_plasma).find('td.pendapatan_peternak').attr('data-val');
    		if ( prs_potongan > 0 ) {
    			potongan_pajak_plasma = pendapatan_peternak*(prs_potongan/100);
    			potongan_pajak_inti = pendapatan_inti*(prs_potongan/100);
    		}
    	}

    	$(div_rhpp_plasma).find('span.prs_pajak').text( numeral.formatDec(prs_potongan)+'%' );
    	$(div_rhpp_plasma).find('td.nilai_potongan_pajak').text( numeral.formatInt(potongan_pajak_plasma) );

    	$(div_rhpp_inti).find('span.prs_pajak').text( numeral.formatDec(prs_potongan)+'%' );
    	$(div_rhpp_inti).find('td.nilai_potongan_pajak').text( numeral.formatInt(potongan_pajak_inti) );

    	tsdrhpp.hit_pendapatan_peternak_setelah_pajak(potongan_pajak_plasma, potongan_pajak_inti);
    }, // end - hit_potongan_pajak

    hit_pendapatan_peternak_setelah_pajak : function(potongan_pajak_peternak, potongan_pajak_inti) {
    	var div_rhpp_plasma = $('#rhpp_plasma');
    	var div_rhpp_inti = $('#rhpp_inti');

    	var pendapatan_peternak = $(div_rhpp_plasma).find('td.pendapatan_peternak').attr('data-val');
    	var pendapatan_inti = $(div_rhpp_inti).find('td.pendapatan_peternak').attr('data-val');

    	var pendapatan_peternak_setelah_pajak = pendapatan_peternak - potongan_pajak_peternak;
    	var pendapatan_inti_setelah_pajak = pendapatan_inti - potongan_pajak_inti;

		$(div_rhpp_plasma).find('td.pendapatan_peternak_setelah_pajak').attr( 'data-val', pendapatan_peternak_setelah_pajak );
		$(div_rhpp_inti).find('td.pendapatan_peternak_setelah_pajak b').attr( 'data-val', pendapatan_inti_setelah_pajak );

        var text_pendapatan_peternak_setelah_pajak = numeral.formatInt(pendapatan_peternak_setelah_pajak);
        var text_pendapatan_inti_setelah_pajak = numeral.formatInt(pendapatan_inti_setelah_pajak);
        if ( pendapatan_peternak_setelah_pajak < 0 ) {
            text_pendapatan_peternak_setelah_pajak = '('+numeral.formatInt(Math.abs(pendapatan_peternak_setelah_pajak))+')';
        }
        if ( pendapatan_inti_setelah_pajak < 0 ) {
            text_pendapatan_inti_setelah_pajak = '('+numeral.formatInt(Math.abs(pendapatan_inti_setelah_pajak))+')';
        }

    	$(div_rhpp_plasma).find('td.pendapatan_peternak_setelah_pajak b').html( text_pendapatan_peternak_setelah_pajak );
    	$(div_rhpp_inti).find('td.pendapatan_peternak_setelah_pajak b').html( text_pendapatan_inti_setelah_pajak );

		tsdrhpp.hit_pendapatan_peternak_setelah_potong_hutang();
    }, // end - hit_pendapatan_peternak_setelah_pajak

	hit_tot_bayar_hutang: function(elm) {
        var tbody = $(elm).closest('tbody');

        var tot_bayar_hutang = 0;
        $.map( $(tbody).find('input.nominal'), function(ipt) {
            var nominal = numeral.unformat($(ipt).val());

            tot_bayar_hutang += nominal;
        });

        $(tbody).find('td.tot_bayar_hutang').attr('data-val', tot_bayar_hutang);
        $(tbody).find('td.tot_bayar_hutang b').text(numeral.formatDec(tot_bayar_hutang));

        tsdrhpp.hit_pendapatan_peternak_setelah_potong_hutang();
    }, // end - hit_tot_bayar_hutang

	hit_pendapatan_peternak_setelah_potong_hutang : function() {
    	var div_rhpp_plasma = $('#rhpp_plasma');

    	var pendapatan_peternak_setelah_pajak = $(div_rhpp_plasma).find('td.pendapatan_peternak_setelah_pajak').attr('data-val');
    	var tot_bayar_hutang = !empty($(div_rhpp_plasma).find('td.tot_bayar_hutang').attr('data-val')) ? $(div_rhpp_plasma).find('td.tot_bayar_hutang').attr('data-val') : 0;

		var pendapatan_peternak_setelah_potong_hutang = pendapatan_peternak_setelah_pajak - tot_bayar_hutang;

		$(div_rhpp_plasma).find('td.pendapatan_peternak_setelah_potong_hutang').attr('data-val', pendapatan_peternak_setelah_potong_hutang);

		var text_pendapatan_peternak_setelah_potong_hutang = numeral.formatInt(pendapatan_peternak_setelah_potong_hutang);
		if ( pendapatan_peternak_setelah_potong_hutang < 0 ) {
			text_pendapatan_peternak_setelah_potong_hutang = '('+numeral.formatInt(Math.abs(pendapatan_peternak_setelah_potong_hutang))+')';
		}
		$(div_rhpp_plasma).find('td.pendapatan_peternak_setelah_potong_hutang b').html( text_pendapatan_peternak_setelah_potong_hutang );
    }, // end - hit_pendapatan_peternak_setelah_potong_hutang

    tutup_siklus: function(elm) {
		let div_rhpp = $(elm).closest('div#rhpp');

    	let err = 0;

    	$.map( $(div_rhpp).find('[data-required=1]'), function(ipt) {
    		if ( empty( $(ipt).val() ) ) {
    			$(ipt).parent().addClass('has-error');
    			err++;
    		} else {
    			$(ipt).parent().removeClass('has-error');
    		}
    	});

    	if ( err > 0 ) {
    		bootbox.alert( 'Harap lengkapi data terlebih dahulu.' );
    	} else {
			let noreg = $(elm).data('noreg');

    		bootbox.confirm( 'Apakah anda yakin ingin menutup siklus pada noreg <b>' + noreg + '</b> ?', function(result) {
    			if ( result ) {
					var group = 0;
					bootbox.dialog({
						title: 'Pilih Jenis RHPP',
						message: "<div class='col-xs-12 no-padding'><div class='col-xs-6 no-padding' style='padding-right: 5px;'><button type='button' class='btn btn-primary col-xs-12' onclick='tsdrhpp.save(this)' data-jenis='0' data-noreg='"+noreg+"'><b>NON GROUP</b></button></div><div class='col-xs-6 no-padding' style='padding-left: 5px;'><button type='button' class='btn btn-primary col-xs-12' onclick='tsdrhpp.save(this)' data-jenis='1' data-noreg='"+noreg+"'><b>GROUP</b></button></div></div>",
					});
    			}
    		});
    	}
    }, // end - tutup_siklus

	save: function(elm) {
		$('.modal').modal('hide');

		var jenis = $(elm).attr('data-jenis');
		var noreg = $(elm).attr('data-noreg');

		let div_rhpp = $('div#rhpp');

		let data = [];
		$.map( $(div_rhpp).find('.tab-pane:not(.hide)'), function(tab_pane) {
			let tbody_pemakaian = $(tab_pane).find('table.pemakaian tbody');
			let tbody_penjualan = $(tab_pane).find('table.penjualan_ayam tbody');
			let tbody_potongan = $(tab_pane).find('table.potongan tbody');
			let tbody_bonus = $(tab_pane).find('table.bonus tbody');
			let tbody_piutang = $(tab_pane).find('table.tbl-piutang tbody');

			let data_doc = {
				'tanggal': $(tbody_pemakaian).find('tr.data_doc td.tanggal').data('val'),
				'nota': $(tbody_pemakaian).find('tr.data_doc td.nota').data('val'),
				'barang': $(tbody_pemakaian).find('tr.data_doc td.barang').data('val'),
				'box_zak': $(tbody_pemakaian).find('tr.data_doc td.box_zak').data('val'),
				'jumlah': $(tbody_pemakaian).find('tr.data_doc td.jumlah').data('val'),
				'harga': $(tbody_pemakaian).find('tr.data_doc td.harga').data('val'),
				'total': $(tbody_pemakaian).find('tr.data_doc td.total').data('val'),
				'vaksin': ($(tab_pane).attr('id') == 'rhpp_plasma') ? $(tbody_pemakaian).find('tr.data_vaksin td.vaksin').data('val') : '',
				'harga_vaksin': ($(tab_pane).attr('id') == 'rhpp_plasma') ? $(tbody_pemakaian).find('tr.data_vaksin td.harga_vaksin').data('val') : 0,
				'total_vaksin': ($(tab_pane).attr('id') == 'rhpp_plasma') ? $(tbody_pemakaian).find('tr.data_vaksin td.total_vaksin').data('val') : 0
			};

			let data_pakan = $.map( $(tbody_pemakaian).find('tr.data_pakan'), function(tr) {
				let _data_pakan = {
					'tanggal': $(tr).find('td.tanggal').data('val'),
					'nota': $(tr).find('td.nota').data('val'),
					'barang': $(tr).find('td.barang').data('val'),
					'box_zak': $(tr).find('td.box_zak').data('val'),
					'jumlah': $(tr).find('td.jumlah').data('val'),
					'harga': $(tr).find('td.harga').data('val'),
					'total': $(tr).find('td.total').data('val')
				};

				return _data_pakan;
			});

			let data_oa_pakan = [];
			if ( $(tab_pane).attr('id') == 'rhpp_inti' ) {
				data_oa_pakan = $.map( $(tbody_pemakaian).find('tr.data_oa_pakan'), function(tr) {
					let _data_oa_pakan = {
						'tanggal': $(tr).find('td.tanggal').data('val'),
						'nota': $(tr).find('td.nopol').data('nota'),
						'nopol': $(tr).find('td.nopol').data('val'),
						'barang': $(tr).find('td.barang').data('val'),
						'box_zak': $(tr).find('td.box_zak').data('val'),
						'jumlah': $(tr).find('td.jumlah').data('val'),
						'harga': $(tr).find('td.harga').data('val'),
						'total': $(tr).find('td.total').data('val')
					};

					return _data_oa_pakan;
				});
			}

			let data_pindah_pakan = $.map( $(tbody_pemakaian).find('tr.data_pindah_pakan'), function(tr) {
				let _data_pindah_pakan = {
					'tanggal': $(tr).find('td.tanggal').data('val'),
					'nota': $(tr).find('td.nota').data('val'),
					'barang': $(tr).find('td.barang').data('val'),
					'box_zak': $(tr).find('td.box_zak').data('val'),
					'jumlah': $(tr).find('td.jumlah').data('val'),
					'harga': $(tr).find('td.harga').data('val'),
					'total': $(tr).find('td.total').data('val')
				};

				return _data_pindah_pakan;
			});

			let data_oa_pindah_pakan = [];
			if ( $(tab_pane).attr('id') == 'rhpp_inti' ) {
				data_oa_pindah_pakan = $.map( $(tbody_pemakaian).find('tr.data_oa_pindah_pakan'), function(tr) {
					let _data_oa_pindah_pakan = {
						'tanggal': $(tr).find('td.tanggal').data('val'),
						'nota': $(tr).find('td.nopol').data('nota'),
						'nopol': $(tr).find('td.nopol').data('val'),
						'barang': $(tr).find('td.barang').data('val'),
						'box_zak': $(tr).find('td.box_zak').data('val'),
						'jumlah': $(tr).find('td.jumlah').data('val'),
						'harga': $(tr).find('td.harga').data('val'),
						'total': $(tr).find('td.total').data('val')
					};

					return _data_oa_pindah_pakan;
				});
			}

			let data_retur_pakan = $.map( $(tbody_pemakaian).find('tr.data_retur_pakan'), function(tr) {
				let _data_retur_pakan = {
					'tanggal': $(tr).find('td.tanggal').data('val'),
					'nota': $(tr).find('td.nota').data('val'),
					'barang': $(tr).find('td.barang').data('val'),
					'box_zak': $(tr).find('td.box_zak').data('val'),
					'jumlah': $(tr).find('td.jumlah').data('val'),
					'harga': $(tr).find('td.harga').data('val'),
					'total': $(tr).find('td.total').data('val')
				};

				return _data_retur_pakan;
			});

			let data_oa_retur_pakan = [];
			if ( $(tab_pane).attr('id') == 'rhpp_inti' ) {
				data_oa_retur_pakan = $.map( $(tbody_pemakaian).find('tr.data_oa_retur_pakan'), function(tr) {
					let _data_oa_retur_pakan = {
						'tanggal': $(tr).find('td.tanggal').data('val'),
						'nota': $(tr).find('td.nopol').data('nota'),
						'nopol': $(tr).find('td.nopol').data('val'),
						'barang': $(tr).find('td.barang').data('val'),
						'box_zak': $(tr).find('td.box_zak').data('val'),
						'jumlah': $(tr).find('td.jumlah').data('val'),
						'harga': $(tr).find('td.harga').data('val'),
						'total': $(tr).find('td.total').data('val')
					};

					return _data_oa_retur_pakan;
				});
			}

			let data_voadip = $.map( $(tbody_pemakaian).find('tr.data_voadip'), function(tr) {
				let _data_voadip = {
					'tanggal': $(tr).find('td.tanggal').data('val'),
					'nota': $(tr).find('td.nota').data('val'),
					'barang': $(tr).find('td.barang').data('val'),
					'jumlah': $(tr).find('td.jumlah').data('val'),
					'harga': $(tr).find('td.harga').data('val'),
					'total': $(tr).find('td.total').data('val')
				};

				return _data_voadip;
			});

			let data_retur_voadip = $.map( $(tbody_pemakaian).find('tr.data_retur_voadip'), function(tr) {
				let _data_retur_voadip = {
					'tanggal': $(tr).find('td.tanggal').data('val'),
					'nota': $(tr).find('td.nota').data('val'),
					'barang': $(tr).find('td.barang').data('val'),
					'jumlah': $(tr).find('td.jumlah').data('val'),
					'harga': $(tr).find('td.harga').data('val'),
					'total': $(tr).find('td.total').data('val')
				};

				return _data_retur_voadip;
			});

			let data_penjualan = $.map( $(tbody_penjualan).find('tr.data_penjualan'), function(tr) {
				let _data_penjualan = {
					'tanggal': $(tr).find('td.tanggal').data('val'),
					'nota': $(tr).find('td.nota').data('val'),
					'pembeli': $(tr).find('td.pembeli').data('val'),
					'ekor': $(tr).find('td.ekor').data('val'),
					'tonase': $(tr).find('td.tonase').data('val'),
					'bb': $(tr).find('td.bb').data('val'),
					'harga_kontrak': ($(tab_pane).attr('id') == 'rhpp_plasma') ? $(tr).find('td.harga_kontrak').data('val') : 0,
					'total_kontrak': ($(tab_pane).attr('id') == 'rhpp_plasma') ? $(tr).find('td.total_kontrak').data('val') : 0,
					'harga_pasar': $(tr).find('td.harga_pasar').data('val'),
					'total_pasar': $(tr).find('td.total_pasar').data('val'),
					'selisih': ($(tab_pane).attr('id') == 'rhpp_plasma') ? $(tr).find('td.selisih').data('val') : 0,
					'insentif': ($(tab_pane).attr('id') == 'rhpp_plasma') ? $(tr).find('td.insentif').data('val') : 0,
					'total_insentif': ($(tab_pane).attr('id') == 'rhpp_plasma') ? $(tr).find('td.total_insentif').data('val') : 0
				};

				return _data_penjualan;
			});

			let data_potongan = [];
			$.map( $(tbody_potongan).find('tr.potongan_peralatan'), function(tr) {
				var jumlah_bayar = numeral.unformat($(tr).find('td.jumlah_tagihan input.jumlah_bayar').val());
				if ( jumlah_bayar > 0 ) {
					let _data_potongan = {
						'id_jual': $(tr).data('idjual'),
						'keterangan': $(tr).find('td.keterangan').text(),
						'jumlah_tagihan': $(tr).find('td.jumlah_tagihan').data('jmltagihan'),
						'jumlah_bayar': jumlah_bayar,
					};

					data_potongan.push( _data_potongan );
				}
			});

			$.map( $(tbody_potongan).find('tr.non_potongan_peralatan'), function(tr) {
				var jumlah_bayar = numeral.unformat($(tr).find('input.jumlah_bayar').val());
				if ( jumlah_bayar > 0 ) {
					let _data_potongan = {
						'id_jual': $(tr).data('idjual'),
						'keterangan': $(tr).find('input.ket_potongan').val(),
						'jumlah_tagihan': 0,
						'jumlah_bayar': jumlah_bayar
					};

					data_potongan.push( _data_potongan );
				}
			});

			let data_bonus = $.map( $(tbody_bonus).find('tr.bonus'), function(tr) {
				var jumlah_bonus = numeral.unformat($(tr).find('input.jumlah_bonus').val());
				if ( jumlah_bonus > 0 ) {
					let _data_bonus = {
						'keterangan': $(tr).find('input.ket_bonus').val(),
						'jumlah_bonus': jumlah_bonus
					};

					return _data_bonus;
				}
			});

			let data_piutang = $.map( $(tbody_piutang).find('tr.data'), function(tr) {
				var nominal = numeral.unformat($(tr).find('input.nominal').val());
				if ( nominal > 0 ) {
					let _data_piutang = {
						'piutang_kode': $(tr).find('td.kode').html(),
						'nama_perusahaan': $(tr).find('td.nama_perusahaan').html(),
						'sisa_piutang': numeral.unformat($(tr).find('td.sisa_piutang').html()),
						'nominal': nominal
					};

					return _data_piutang;
				}
			});

			data[ $(tab_pane).attr('id') ] = {
				'jenis': $(tab_pane).attr('id'),
				'mitra': $(tab_pane).find('label.mitra').data('val'),
				'noreg': noreg,
				'populasi': $(tab_pane).find('label.populasi').data('val'),
				'kandang': $(tab_pane).find('label.kandang').data('val'),
				'tgl_docin': $(tab_pane).find('label.tgl_docin').data('tgl'),
				'jml_panen_ekor': $(tab_pane).find('td.jml_ekor_panen').data('val'),
				'jml_panen_kg': $(tab_pane).find('td.jml_panen_kg').data('val'),
				'persen_bonus_pasar': ($(tab_pane).attr('id') == 'rhpp_plasma') ? $(tab_pane).find('td.persen_bonus_pasar').data('val') : 0,
				'bb': $(tab_pane).find('td.bb_panen').data('val'),
				'fcr': $(tab_pane).find('td.fcr').data('val'),
				'deplesi': $(tab_pane).find('td.deplesi').data('val'),
				'rata_umur': $(tab_pane).find('td.rata_umur').data('val'),
				'ip': $(tab_pane).find('td.ip').data('val'),
				'tot_penjualan_ayam': $(tab_pane).find('td.tot_penjualan_ayam').data('val'),
				'tot_pembelian_sapronak': $(tab_pane).find('td.tot_pembelian_sapronak').data('val'),
				'biaya_materai': numeral.unformat($('input.biaya_materai').val()),
				'bonus_pasar': ($(tab_pane).attr('id') == 'rhpp_plasma') ? $(tab_pane).find('td.bonus_pasar').data('val') : 0,
				'bonus_kematian': ($(tab_pane).attr('id') == 'rhpp_plasma') ? $(tab_pane).find('td.bonus_kematian').data('val') : 0,
				'bonus_insentif_fcr': ($(tab_pane).attr('id') == 'rhpp_plasma') ? $(tab_pane).find('td.bonus_insentif_fcr').data('val') : 0,
				'cn': ($(tab_pane).attr('id') == 'rhpp_inti') ? $(tab_pane).find('td.cn').data('val') : 0,
				'biaya_operasional': ($(tab_pane).attr('id') == 'rhpp_inti') ? $(tab_pane).find('td.biaya_opr').data('val') : 0,
				'pdpt_peternak_belum_pajak': ($(tab_pane).attr('id') == 'rhpp_plasma') ? $(tab_pane).find('td.pendapatan_peternak').data('val') : 0,
				'prs_potongan_pajak': numeral.unformat($('select.prs_potongan').find('option:selected').text()),
				'potongan_pajak': numeral.unformat($(tab_pane).find('td.nilai_potongan_pajak').text()),
				'pdpt_peternak_sudah_pajak': $(tab_pane).find('td.pendapatan_peternak_setelah_pajak').data('val'),
				'lr_inti': ($(tab_pane).attr('id') == 'rhpp_inti') ? $(tab_pane).find('td.pendapatan_peternak').data('val') : 0,
				'data_doc': data_doc,
				'data_pakan': data_pakan,
				'data_oa_pakan': data_oa_pakan,
				'data_pindah_pakan': data_pindah_pakan,
				'data_oa_pindah_pakan': data_oa_pindah_pakan,
				'data_retur_pakan': data_retur_pakan,
				'data_oa_retur_pakan': data_oa_retur_pakan,
				'data_voadip': data_voadip,
				'data_retur_voadip': data_retur_voadip,
				'data_penjualan': data_penjualan,
				'populasi_bonus_insentif_listrik': ($(tab_pane).attr('id') == 'rhpp_plasma') ? numeral.unformat($('input.populasi_bonus_insentif_listrik').val()) : 0,
				'bonus_insentif_listrik': ($(tab_pane).attr('id') == 'rhpp_plasma') ? numeral.unformat($(tab_pane).find('td.bonus_insentif_listrik').data('bonus')) : 0,
				'total_bonus_insentif_listrik': ($(tab_pane).attr('id') == 'rhpp_plasma') ? numeral.unformat($(tab_pane).find('td.bonus_insentif_listrik').data('val')) : 0,
				'data_potongan': data_potongan,
				'data_bonus': data_bonus,
				'total_potongan': ($(tab_pane).attr('id') == 'rhpp_plasma') ? numeral.unformat($('td.total_potongan').attr('data-val')) : 0,
				'total_bonus': ($(tab_pane).attr('id') == 'rhpp_plasma') ? numeral.unformat($('td.total_bonus').attr('data-val')) : 0,
				'data_piutang': data_piutang
			};
		});

		let data_rhpp = {
			0: data['rhpp_inti'],
			1: data['rhpp_plasma'],
		};

		var params = {
			'noreg': noreg,
			'tgl_docin': $('label.tgl_docin').data('tgl'),
			'tgl_tutup_siklus': dateSQL( $('[name=tutup_siklus]').data('DateTimePicker').date() ),
			'biaya_materai': numeral.unformat( $('input.biaya_materai').val() ),
			'id_potongan_pajak': $('select.prs_potongan').val(),
			'jenis_rhpp': jenis,
			'data_rhpp': data_rhpp
		};

		$.ajax({
			url : 'transaksi/TSDRHPP/tutup_siklus',
			data : {
				'params' :  params
			},
			type : 'POST',
			dataType : 'JSON',
			beforeSend : function(){ showLoading(); },
			success : function(data){
				hideLoading();

				if ( data.status == 1 ) {
					bootbox.alert( data.message, function() {
						tsdrhpp.load_form(noreg);
						tsdrhpp.get_lists();
					});
				} else {
					bootbox.alert( data.message );
				}
			},
		});
	}, // end - save

    edit: function(elm) {
    	let err = 0;
    	let noreg = $(elm).data('noreg');

    	let div_rhpp = $(elm).closest('div#rhpp');

    	$.map( $(div_rhpp).find('[data-required=1]'), function(ipt) {
    		if ( empty( $(ipt).val() ) ) {
    			$(ipt).parent().addClass('has-error');
    			err++;
    		} else {
    			$(ipt).parent().removeClass('has-error');
    		}
    	});

    	if ( err > 0 ) {
    		bootbox.alert( 'Harap lengkapi data terlebih dahulu.' );
    	} else {
    		bootbox.confirm( 'Apakah anda yakin ingin meng-update data pada noreg <b>' + noreg + '</b> ?', function(result) {
    			if ( result ) {
	    			let data = [];
    				$.map( $(div_rhpp).find('.tab-pane'), function(tab_pane) {
                        let tbody_potongan = $(tab_pane).find('table.potongan tbody');
                        let tbody_bonus = $(tab_pane).find('table.bonus tbody');

                        let data_potongan = [];
                        $.map( $(tbody_potongan).find('tr.potongan_peralatan'), function(tr) {
                            var jumlah_bayar = numeral.unformat($(tr).find('input.jumlah_bayar').val());
                            if ( jumlah_bayar > 0 ) {
                                let _data_potongan = {
                                    'id_jual': $(tr).data('idjual'),
                                    'keterangan': $(tr).find('td.keterangan').text(),
                                    'jumlah_tagihan': $(tr).find('td.jumlah_tagihan').data('jmltagihan'),
                                    'jumlah_bayar': jumlah_bayar,
                                };

                                data_potongan.push( _data_potongan );
                            }
                        });

                        $.map( $(tbody_potongan).find('tr.non_potongan_peralatan'), function(tr) {
                            var jumlah_bayar = numeral.unformat($(tr).find('input.jumlah_bayar').val());
                            if ( jumlah_bayar > 0 ) {
                                let _data_potongan = {
                                    'id_jual': $(tr).data('idjual'),
                                    'keterangan': $(tr).find('input.ket_potongan').val(),
                                    'jumlah_tagihan': 0,
                                    'jumlah_bayar': jumlah_bayar
                                };

                                data_potongan.push( _data_potongan );
                            }
                        });

                        let data_bonus = $.map( $(tbody_bonus).find('tr.bonus'), function(tr) {
                            var jumlah_bonus = numeral.unformat($(tr).find('input.jumlah_bonus').val());
                            if ( jumlah_bonus > 0 ) {
                                let _data_bonus = {
                                    'keterangan': $(tr).find('input.ket_bonus').val(),
                                    'jumlah_bonus': jumlah_bonus
                                };

                                return _data_bonus;
                            }
                        });

    					data[ $(tab_pane).attr('id') ] = {
    						'jenis': $(tab_pane).attr('id'),
    						'mitra': $(tab_pane).find('label.mitra').data('val'),
    						'noreg': noreg,
    						'populasi': $(tab_pane).find('label.populasi').data('val'),
    						'kandang': $(tab_pane).find('label.kandang').data('val'),
    						'tgl_docin': $(tab_pane).find('label.tgl_docin').data('tgl'),
    						'jml_panen_ekor': $(tab_pane).find('td.jml_ekor_panen').data('val'),
                            'jml_panen_kg': $(tab_pane).find('td.jml_panen_kg').data('val'),
    						'persen_bonus_pasar': ($(tab_pane).attr('id') == 'rhpp_plasma') ? $(tab_pane).find('td.persen_bonus_pasar').data('val') : 0,
    						'bb': $(tab_pane).find('td.bb_panen').data('val'),
    						'fcr': $(tab_pane).find('td.fcr').data('val'),
    						'deplesi': $(tab_pane).find('td.deplesi').data('val'),
    						'rata_umur': $(tab_pane).find('td.rata_umur').data('val'),
    						'ip': $(tab_pane).find('td.ip').data('val'),
    						'tot_penjualan_ayam': $(tab_pane).find('td.tot_penjualan_ayam').data('val'),
    						'tot_pembelian_sapronak': $(tab_pane).find('td.tot_pembelian_sapronak').data('val'),
    						'biaya_materai': numeral.unformat($('input.biaya_materai').val()),
    						'bonus_pasar': ($(tab_pane).attr('id') == 'rhpp_plasma') ? $(tab_pane).find('td.bonus_pasar').data('val') : 0,
    						'bonus_kematian': ($(tab_pane).attr('id') == 'rhpp_plasma') ? $(tab_pane).find('td.bonus_kematian').data('val') : 0,
    						'bonus_insentif_fcr': ($(tab_pane).attr('id') == 'rhpp_plasma') ? $(tab_pane).find('td.bonus_insentif_fcr').data('val') : 0,
    						'biaya_operasional': ($(tab_pane).attr('id') == 'rhpp_inti') ? $(tab_pane).find('td.biaya_opr').data('val') : 0,
    						'pdpt_peternak_belum_pajak': ($(tab_pane).attr('id') == 'rhpp_plasma') ? $(tab_pane).find('td.pendapatan_peternak').data('val') : 0,
    						'prs_potongan_pajak': numeral.unformat($('select.prs_potongan').find('option:selected').text()),
    						'potongan_pajak': $(tab_pane).find('td.nilai_potongan_pajak').data('val'),
    						'pdpt_peternak_sudah_pajak': $(tab_pane).find('td.pendapatan_peternak_setelah_pajak').data('val'),
    						'lr_inti': ($(tab_pane).attr('id') == 'rhpp_inti') ? $(tab_pane).find('td.pendapatan_peternak').data('val') : 0,
    						'populasi_bonus_insentif_listrik': ($(tab_pane).attr('id') == 'rhpp_plasma') ? numeral.unformat($('input.populasi_bonus_insentif_listrik').val()) : 0,
    						'bonus_insentif_listrik': ($(tab_pane).attr('id') == 'rhpp_plasma') ? $(tab_pane).find('td.bonus_insentif_listrik').attr('data-bonus') : 0,
    						'total_bonus_insentif_listrik': ($(tab_pane).attr('id') == 'rhpp_plasma') ? $(tab_pane).find('td.bonus_insentif_listrik').attr('data-val') : 0,
                            'data_potongan': data_potongan,
                            'data_bonus': data_bonus,
                            'total_potongan': ($(tab_pane).attr('id') == 'rhpp_plasma') ? numeral.unformat($('td.total_potongan').attr('data-val')) : 0,
                            'total_bonus': ($(tab_pane).attr('id') == 'rhpp_plasma') ? numeral.unformat($('td.total_bonus').attr('data-val')) : 0
    					};
    				});

					let data_rhpp = {
						0: data['rhpp_inti'],
						1: data['rhpp_plasma'],
					};

	    			var params = {
	    				'id': $(elm).data('id'),
	    				'noreg': noreg,
	    				'tgl_docin': $('label.tgl_docin').data('tgl'),
	    				'tgl_tutup_siklus': dateSQL( $('[name=tutup_siklus]').data('DateTimePicker').date() ),
	    				'biaya_materai': numeral.unformat( $('input.biaya_materai').val() ),
	    				'id_potongan_pajak': $('select.prs_potongan').val(),
	    				'data_rhpp': data_rhpp
	    			};

	    			$.ajax({
			            url : 'transaksi/TSDRHPP/edit',
			            data : {
			                'params' :  params
			            },
			            type : 'POST',
			            dataType : 'JSON',
			            beforeSend : function(){ showLoading; },
			            success : function(data){
			            	hideLoading();

			            	if ( data.status == 1 ) {
			            		bootbox.alert( data.message, function() {
			            			tsdrhpp.load_form(noreg);
			            			tsdrhpp.get_lists();
			            		});
			            	} else {
			            		bootbox.alert( data.message );
			            	}
			            },
			        });
    			}
    		});
    	}
    }, // end - edit

    delete: function(elm) {
    	let err = 0;
    	var noreg = $(elm).data('noreg');
		bootbox.confirm( 'Apakah anda yakin ingin meng-hapus data pada noreg <b>' + noreg + '</b> ?', function(result) {
			if ( result ) {
				var params = {
					'id': $(elm).data('id')
				};

				showLoading;
				$.ajax({
		            url : 'transaksi/TSDRHPP/delete',
		            data : {
		                'params' :  params
		            },
		            type : 'POST',
		            dataType : 'JSON',
		            beforeSend : function(){},
		            success : function(data){
		            	hideLoading();

		            	if ( data.status == 1 ) {
		            		bootbox.alert( data.message, function() {
		            			tsdrhpp.load_form(null, null);
		            			tsdrhpp.get_lists();
		            		});
		            	} else {
		            		bootbox.alert( data.message );
		            	}
		            },
		        });
			}
		});
    }, // end - delete

    export_excel : function (elm) {
    	var noreg = $(elm).data('noreg');
		goToURL('transaksi/TSDRHPP/export_excel/'+noreg);
	}, // end - export_excel

    print: function (elm) {
        var noreg = $(elm).data('noreg');

        bootbox.dialog({
            message: '<p><b>CATATAN : </b></p><p><textarea class="form-control keterangan"></textarea></p>',
            buttons: {
                cancel: {
                    label: '<i class="fa fa-times"></i> Batal',
                    className: 'btn-danger',
                    callback: function(){}
                },
                ok: {
                    label: '<i class="fa fa-check"></i> Lanjut',
                    className: 'btn-primary',
                    callback: function(){
                        var keterangan = $('.keterangan:last()').val();

                        if ( !empty(keterangan) ) {
                            $.ajax({
                                url: 'transaksi/TSDRHPP/updateCatatan',
                                data: {
                                    'noreg': noreg,
                                    'keterangan': keterangan
                                },
                                type: 'POST',
                                dataType: 'JSON',
                                beforeSend: function() { showLoading(); },
                                success: function(data) {
                                    hideLoading();
                                    if ( data.status == 1 ) {
                                        tsdrhpp.export_pdf(noreg);
                                    } else {
                                        bootbox.alert(data.message);
                                    }
                                }
                            });
                        } else {
                            $(elm).click();
                            $('.keterangan').parent().addClass('has-error');
                        }
                    }
                }
            }
        });
    }, // end - print

    export_pdf : function (noreg) {
        window.open('transaksi/TSDRHPP/export_pdf/'+noreg, 'blank');
    }, // end - export_excel

	export_excel_inti : function (elm) {
    	var noreg = $(elm).data('noreg');
		goToURL('transaksi/TSDRHPP/export_excel_inti/'+noreg);
	}, // end - export_excel_inti

    submitCn: function(elm) {
        let err = 0;
        let noreg = $(elm).data('noreg');

        let div_rhpp = $(elm).closest('div#rhpp');

        $.map( $(div_rhpp).find('[data-required=1]'), function(ipt) {
            if ( empty( $(ipt).val() ) ) {
                $(ipt).parent().addClass('has-error');
                err++;
            } else {
                $(ipt).parent().removeClass('has-error');
            }
        });

        if ( err > 0 ) {
            bootbox.alert( 'Harap lengkapi data terlebih dahulu.' );
        } else {
            bootbox.confirm( 'Apakah anda yakin ingin menutup siklus pada noreg <b>' + noreg + '</b> ?', function(result) {
                if ( result ) {
                    let data = [];
                    $.map( $(div_rhpp).find('.tab-pane:not(.hide)'), function(tab_pane) {
                        data[ $(tab_pane).attr('id') ] = {
                            'jenis': $(tab_pane).attr('id'),
                            'noreg': noreg,
                            'lr_inti': ($(tab_pane).attr('id') == 'rhpp_inti') ? $(tab_pane).find('td.pendapatan_peternak').data('val') : 0,
                        };
                    });

                    let data_rhpp = {
                        0: data['rhpp_inti'],
                        1: data['rhpp_plasma'],
                    };

                    var params = {
                        'noreg': noreg,
                        'id': $(elm).attr('data-id'),
                        'nilai_cn': numeral.unformat($('input.nilai_cn').val()),
                        'nilai_opr': numeral.unformat($('input.nilai_opr').val()),
                        'data_rhpp': data_rhpp
                    };

                    $.ajax({
                        url : 'transaksi/TSDRHPP/submitCn',
                        data : {
                            'params' :  params
                        },
                        type : 'POST',
                        dataType : 'JSON',
                        beforeSend : function(){ showLoading; },
                        success : function(data){
                            hideLoading();

                            if ( data.status == 1 ) {
                                bootbox.alert( data.message, function() {
                                    tsdrhpp.load_form(noreg);
                                });
                            } else {
                                bootbox.alert( data.message );
                            }
                        },
                    });
                }
            });
        }
    }, // end - submitCn

	modalPiutang: function(elm) {
		var kode = $(elm).attr('data-kode');

		// showLoading();

		$.get('transaksi/TSDRHPP/modalPiutang',{
			'kode': kode
			},function(data){
			var _options = {
				className : 'veryWidth',
				message : data,
				size : 'large',
			};
			bootbox.dialog(_options).bind('shown.bs.modal', function(){
				// hideLoading();

				$(this).find('.modal-body:first()').css({'padding-top': '0px'});
			});
		},'html');
	}, // end - modalPiutang

	addPiutang: function(elm) {
		var tbl_piutang = $('table.tbl-piutang');
		var tr = $(elm).closest('tr');

		var kode_pilih = $(tr).find('td.kode').text();

		var ada = 0;

		$.map( $(tbl_piutang).find('tr.data'), function(tr_data) {
			var kode = $(tr_data).find('td.kode').text();

			if ( kode == kode_pilih ) {
				ada = 1;
			}
		});

		if ( ada == 0 ) {
			var html = '<tr class="data tambahan">';
			html += '<td class="nama_perusahaan">'+$(tr).find('td.nama_perusahaan').text()+'</td>';
			html += '<td>'+$(tr).find('td.tanggal').text()+'</td>';
			html += '<td class="kode">'+$(tr).find('td.kode').text()+'</td>';
			html += '<td>'+$(tr).find('td.keterangan').text()+'</td>';
			html += '<td class="text-right sisa_piutang">'+$(tr).find('td.sisa_piutang').text()+'</td>';
			html += '<td class="text-right"><input type="text" class="form-control text-right nominal" data-tipe="decimal" data-required="1" onblur="tsdrhpp.hit_tot_bayar_hutang(this)"></td>';
			html += '</tr>';

			if ( $(tbl_piutang).find('tr.tot_piutang').length == 0 ) {
				$(tbl_piutang).find('tbody').append( html );

				var html_total = '<tr class="tot_piutang">';
				html_total += '<td colspan="4" class="text-right"><b>TOTAL</b></td>';
				html_total += '<td class="text-right tot_hutang"><b><?php echo angkaDecimal($tot_hutang); ?></b></td>';
				html_total += '<td class="text-right tot_bayar_hutang"><b><?php echo angkaDecimal($tot_bayar_hutang); ?></b></td>';
				html_total += '</tr>';

				$(tbl_piutang).find('tr.non-data').addClass('hide');
				
				$(tbl_piutang).find('tbody').append( html_total );
			} else {
				$(tbl_piutang).find('tbody tr.tot_piutang').before( html );
			}

			var last_tr = $(tbl_piutang).find('tr.data:last()');
			$(last_tr).find('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal], [data-tipe=decimal3],[data-tipe=decimal4], [data-tipe=number]').each(function(){
				$(this).priceFormat(Config[$(this).data('tipe')]);
			});

			$(last_tr).find('td').css({'background-color': 'lightblue'});
		}

		var tot_hutang = 0; var tot_bayar_hutang = 0;
		$.map( $(tbl_piutang).find('tr.data'), function(tr_data) {
			tot_hutang += numeral.unformat($(tr_data).find('td.sisa_piutang').text());
			tot_bayar_hutang += $(tr_data).find('');
		});

		$(tbl_piutang).find('td.tot_hutang b').text( numeral.formatDec(tot_hutang) );
		$(tbl_piutang).find('td.tot_bayar_hutang b').text( numeral.formatDec(tot_bayar_hutang) );

		$('modal').modal('hide');
	}, // end - addPiutang
};

tsdrhpp.start_up();