var rg = {
	start_up: function () {
		rg.setting();
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

	setting: function () {
		$("[name=startDate], [name=startDateRiwayat]").datetimepicker({
			locale: 'id',
            format: 'DD MMM Y'
		});
		$("[name=endDate], [name=endDateRiwayat]").datetimepicker({
			locale: 'id',
            format: 'DD MMM Y',
			useCurrent: false //Important! See issue #1075
		});
		$("[name=startDate]").on("dp.change", function (e) {
			$("[name=endDate]").data("DateTimePicker").minDate(e.date);
			$("[name=endDate]").data("DateTimePicker").date(e.date);
		});
		$("[name=endDate]").on("dp.change", function (e) {
			$('[name=startDate]').data("DateTimePicker").maxDate(e.date);
		});
		$("[name=startDateRiwayat]").on("dp.change", function (e) {
			$("[name=endDateRiwayat]").data("DateTimePicker").minDate(e.date);
			$("[name=endDateRiwayat]").data("DateTimePicker").date(e.date);
		});
		$("[name=endDateRiwayat]").on("dp.change", function (e) {
			$('[name=startDateRiwayat]').data("DateTimePicker").maxDate(e.date);
		});

		$('.selectpicker').selectpicker();
	}, // end - setting

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
    }, // end - changeTabActive

    get_lists: function(elm) {
    	var div_riwayat = $(elm).closest('div#riwayat');
		var div = $(div_riwayat).find('div.filter_noreg');

		var err = 0;
		$.map( $(div).find('[data-required=1]'), function(ipt) {
			if ( empty($(ipt).val()) ) {
				if ( $(ipt).hasClass('selectpicker') ) {
					$(ipt).next('button').addClass('has-error');
				} else {
					$(ipt).parent().addClass('has-error');
				}
				err++;
			} else {
				if ( $(ipt).hasClass('selectpicker') ) {
					$(ipt).next('button').removeClass('has-error');
				} else {
					$(ipt).parent().removeClass('has-error');
				}
			}
		});

		if ( err > 0 ) {
			bootbox.alert('Harap lengkapi data terlebih dahulu.');
		} else {
			var start_date = dateSQL($(div).find('#StartDateRiwayat').data('DateTimePicker').date());
			var end_date =dateSQL($(div).find('#EndDateRiwayat').data('DateTimePicker').date());

			let params = {
				'nomor': $(div).find('select.mitra').val(),
				'start_date': start_date,
				'end_date': end_date
			};

			$.ajax({
	            url : 'transaksi/RhppGroup/getLists',
	            data : {
	                'params': params
	            },
	            type : 'POST',
	            dataType : 'JSON',
	            beforeSend : function(){ showLoading(); },
	            success : function(data){
	            	$('table.tbl_list_rhpp').find('tbody').html( data.content );

	                hideLoading();
	            },
	        });
		}
    }, // end - get_lists

	get_noreg: function(elm) {
		var div_noreg_tutup_siklus = $(elm).closest('div#noreg_tutup_siklus');
		var div = $(div_noreg_tutup_siklus).find('div.filter_noreg');

		var err = 0;
		$.map( $(div).find('[data-required=1]'), function(ipt) {
			if ( empty($(ipt).val()) ) {
				if ( $(ipt).hasClass('selectpicker') ) {
					$(ipt).next('button').addClass('has-error');
				} else {
					$(ipt).parent().addClass('has-error');
				}
				err++;
			} else {
				if ( $(ipt).hasClass('selectpicker') ) {
					$(ipt).next('button').removeClass('has-error');
				} else {
					$(ipt).parent().removeClass('has-error');
				}
			}
		});

		if ( err > 0 ) {
			bootbox.alert('Harap lengkapi data terlebih dahulu.');
		} else {
			var start_date = dateSQL($(div).find('#StartDate').data('DateTimePicker').date());
			var end_date =dateSQL($(div).find('#EndDate').data('DateTimePicker').date());

			let params = {
				'nomor': $(div).find('select.mitra').val(),
				'start_date': start_date,
				'end_date': end_date
			};

			$.ajax({
	            url : 'transaksi/RhppGroup/get_noreg',
	            data : {
	                'params': params
	            },
	            type : 'GET',
	            dataType : 'HTML',
	            beforeSend : function(){ showLoading(); },
	            success : function(html){
	            	$('table.tbl_tutup_siklus').find('tbody').html( html );

	            	$('.check').change(function() {
					    var target = $(this).attr('target');

					    var length_checkbox = $('.check[target='+target+']').length;
					    var length_checkbox_checked = $('.check[target='+target+']:checked').length;

					    if ( length_checkbox == length_checkbox_checked ) {
					    	$('.check_all[data-target='+target+']').prop('checked', true);
					    } else {
					    	$('.check_all[data-target='+target+']').prop('checked', false);
					    }

					});

	                hideLoading();
	            },
	        });
		}
	}, // end - get_noreg

	proses_hit_rhpp_group: function(elm) {
		var id = $(elm).data('id');

		var jml_checked = 0;
		var list_noreg = [];
		if ( !empty(id) ) {
			jml_checked = 1;
		} else {
			var div_list_noreg = $(elm).closest('div#noreg_tutup_siklus');

			var idx = 0;
			$.map( $(div_list_noreg).find('table.tbl_tutup_siklus tbody tr'), function(tr) {
				var checkbox = $(tr).find('.check');
				if ( $(checkbox).is(':checked') ) {
					jml_checked++;

					var _noreg = $(tr).find('td.noreg').text();
					list_noreg[idx] = {
						'noreg': _noreg
					};

					idx++;
				}
			});
		}

		if ( jml_checked == 0 ) {
			bootbox.alert('Tidak ada noreg yang anda pilih.');
		} else {
			var params = {
				'id': id,
				'list_noreg': list_noreg
			};

			$.ajax({
	            url : 'transaksi/RhppGroup/proses_hitung',
	            data : {
	                'params': params
	            },
	            type : 'GET',
	            dataType : 'HTML',
	            beforeSend : function(){ showLoading(); },
	            success : function(html){
	            	rg.changeTabActive(elm);

	            	$('#rhpp').html( html );

	            	$('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal], [data-tipe=decimal3],[data-tipe=decimal4], [data-tipe=number]').each(function(){
			            $(this).priceFormat(Config[$(this).data('tipe')]);
			        });

	            	$("[name=tglTutup]").datetimepicker({
						locale: 'id',
			            format: 'DD MMM Y'
					});

	            	var tglTutup = $("[name=tglTutup]").find('input').attr('data-val');
	            	if ( !empty(tglTutup) ) {
	            		$("[name=tglTutup]").data("DateTimePicker").date( new Date(tglTutup) );
	            	}

	                hideLoading();
	            },
	        });
		}
	}, // end - proses_hit_rhpp_group

	setCn: function(elm) {
        // var val = numeral.unformat( $(elm).val() );

        // $('td.cn').attr('data-val', val);
        // $('td.cn').text(numeral.formatInt(val));

		var cn = numeral.unformat( $('input.nilai_cn').val() );
        var biaya_opr = numeral.unformat( $('input.nilai_opr').val() );

        $('td.cn').attr('data-val', cn);
        $('td.cn').text(numeral.formatInt(cn));

		$('td.biaya_opr').attr('data-val', biaya_opr);
        $('td.biaya_opr').text(numeral.formatInt(biaya_opr));

        rg.hit_tot_pemasukan_inti();
    }, // end - setCn

	setVal: function(elm) {
        var td = $(elm).closest('td');

        var val = numeral.unformat( $(elm).val() );

        $(td).attr('data-val', val);

        rg.hit_tot_pemasukan_inti();
    }, // end - setVal

    hit_tot_pemasukan_inti: function() {
        var div_rhpp_inti = $('#rhpp_inti');

        var tot_penjualan_ayam = parseFloat($(div_rhpp_inti).find('td.tot_penjualan_ayam').attr('data-val'));
        var _cn = $(div_rhpp_inti).find('td.cn').attr('data-val');
        var cn = !empty(_cn) ? parseFloat($(div_rhpp_inti).find('td.cn').attr('data-val')) : 0;

        var tot_pemasukan_plasma = tot_penjualan_ayam + cn;

        $(div_rhpp_inti).find('td.total_pemasukan b').text( numeral.formatInt(tot_pemasukan_plasma) );
        $(div_rhpp_inti).find('td.total_pemasukan').attr('data-val', tot_pemasukan_plasma );

        rg.hit_tot_pengeluaran();
    }, // end - hit_tot_pemasukan_inti

	hit_tot_bonus: function() {
        var tbody = $('table.bonus').find('tbody');

        var tot_bonus = 0;
        $.map( $(tbody).find('tr.bonus'), function(tr) {
            var jumlah = numeral.unformat($(tr).find('input.jumlah_bonus').val());

            tot_bonus += jumlah;
        });

        $(tbody).find('td.total_bonus').attr('data-val', tot_bonus);
        $(tbody).find('td.total_bonus b').text(numeral.formatDec(tot_bonus));

        rg.hit_pendapatan_peternak();
    }, // end - hit_tot_bonus

	hit_tot_pengeluaran: function(elm) {
    	// var tr = $(elm).closest('tr');
    	// var tbody = $(tr).closest('tbody');

    	var div_rhpp_plasma = $('#rhpp_plasma');
    	var div_rhpp_inti = $('#rhpp_inti');

    	var total_pembelian_sapronak_plasma = $(div_rhpp_plasma).find('td.total_pengeluaran').data('val');
    	var total_pembelian_sapronak_inti = $(div_rhpp_inti).find('td.total_pengeluaran').data('val');
    	var biaya_materai = numeral.unformat( $(elm).val() );

    	$(div_rhpp_plasma).find('span.biaya_materai').html( numeral.formatInt(biaya_materai) );
    	$(div_rhpp_plasma).find('span.biaya_materai').attr( 'data-val', biaya_materai );
    	$(div_rhpp_inti).find('span.biaya_materai').html( numeral.formatInt(biaya_materai) );
    	$(div_rhpp_inti).find('span.biaya_materai').attr( 'data-val', biaya_materai );

    	var tot_pengeluaran_plasma = total_pembelian_sapronak_plasma + biaya_materai;
    	var tot_pengeluaran_inti = total_pembelian_sapronak_inti + biaya_materai;

    	$(div_rhpp_plasma).find('td.total_pengeluaran').attr('data-val', tot_pengeluaran_plasma);
		$(div_rhpp_inti).find('td.total_pengeluaran').attr('data-val', tot_pengeluaran_inti);
    	$(div_rhpp_plasma).find('td.total_pengeluaran b').html( numeral.formatInt(tot_pengeluaran_plasma) );
    	$(div_rhpp_inti).find('td.total_pengeluaran b').html( numeral.formatInt(tot_pengeluaran_inti) );

    	rg.hit_pendapatan_peternak();
    }, // end - hit_tot_pengeluaran

    hit_tot_potongan: function() {
        var tbody = $('table.potongan').find('tbody');

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

        rg.hit_pendapatan_peternak();
    }, // end - hit_tot_potongan

    hit_pendapatan_peternak: function() {
    	var div_rhpp_plasma = $('#rhpp_plasma');
    	var div_rhpp_inti = $('#rhpp_inti');

        var tot_pemasukan_plasma = parseFloat($(div_rhpp_plasma).find('td.total_pemasukan').attr('data-val'));
    	var tot_bonus_plasma = parseFloat($(div_rhpp_plasma).find('td.total_bonus').attr('data-val'));
        var tot_pengeluaran_plasma = parseFloat($(div_rhpp_plasma).find('td.total_pengeluaran').attr('data-val'));
    	var tot_potongan_plasma = parseFloat($(div_rhpp_plasma).find('td.total_potongan').attr('data-val'));

    	var tot_pemasukan_inti = parseFloat($(div_rhpp_inti).find('td.total_pemasukan').attr('data-val'));
    	// var tot_pengeluaran_inti = parseFloat($(div_rhpp_inti).find('td.total_pengeluaran').attr('data-val'));

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

    	rg.hit_potongan_pajak( $('select.prs_potongan') );
    }, // end - hit_pendapatan_peternak

    hit_potongan_pajak: function(elm) {
    	var div_rhpp_plasma = $('#rhpp_plasma');
    	var div_rhpp_inti = $('#rhpp_inti');

    	var prs_potongan = 0;
    	var potongan_pajak_plasma = 0;
    	var potongan_pajak_inti = 0;
    	if ( !empty($(elm).val()) ) {
    		prs_potongan = numeral.unformat($(elm).find('option:selected').text());
    		var pendapatan_peternak = parseInt($(div_rhpp_plasma).find('td.pendapatan_peternak').attr('data-val'));
    		var pendapatan_inti = parseInt($(div_rhpp_plasma).find('td.pendapatan_peternak').attr('data-val'));
    		if ( prs_potongan > 0 ) {
    			potongan_pajak_plasma = pendapatan_peternak*(prs_potongan/100);
    			potongan_pajak_inti = pendapatan_inti*(prs_potongan/100);
    		}
    	}

    	$(div_rhpp_plasma).find('span.prs_pajak').text( numeral.formatDec(prs_potongan)+'%' );
    	$(div_rhpp_plasma).find('span.prs_pajak').attr( 'data-val', prs_potongan );
    	$(div_rhpp_plasma).find('td.nilai_potongan_pajak').text( numeral.formatInt(potongan_pajak_plasma) );
    	$(div_rhpp_plasma).find('td.nilai_potongan_pajak').attr( 'data-val',  potongan_pajak_plasma);

    	$(div_rhpp_inti).find('span.prs_pajak').text( numeral.formatDec(prs_potongan)+'%' );
    	$(div_rhpp_inti).find('span.prs_pajak').attr( 'data-val', prs_potongan );
    	$(div_rhpp_inti).find('td.nilai_potongan_pajak').text( numeral.formatInt(potongan_pajak_inti) );
    	$(div_rhpp_inti).find('td.nilai_potongan_pajak').attr( 'data-val',  potongan_pajak_inti);

    	rg.hit_pendapatan_peternak_setelah_pajak(potongan_pajak_plasma, potongan_pajak_inti);
    }, // end - hit_potongan_pajak

    hit_pendapatan_peternak_setelah_pajak : function(potongan_pajak_peternak, potongan_pajak_inti) {
    	var div_rhpp_plasma = $('#rhpp_plasma');
    	var div_rhpp_inti = $('#rhpp_inti');

    	var pendapatan_peternak = $(div_rhpp_plasma).find('td.pendapatan_peternak').attr('data-val');
    	var pendapatan_inti = $(div_rhpp_inti).find('td.pendapatan_peternak').attr('data-val');

    	var pendapatan_peternak_setelah_pajak = pendapatan_peternak - potongan_pajak_peternak;
    	var pendapatan_inti_setelah_pajak = pendapatan_inti - potongan_pajak_inti;

		$(div_rhpp_plasma).find('td.pendapatan_peternak_setelah_pajak').attr( 'data-val', pendapatan_peternak_setelah_pajak );
    	$(div_rhpp_inti).find('td.pendapatan_peternak_setelah_pajak').attr( 'data-val', pendapatan_inti_setelah_pajak );

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

		rg.hit_pendapatan_peternak_setelah_potong_hutang();
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

        rg.hit_pendapatan_peternak_setelah_potong_hutang();
    }, // end - hit_tot_bayar_hutang

	hit_pendapatan_peternak_setelah_potong_hutang : function() {
    	var div_rhpp_plasma = $('#rhpp_plasma');

    	var pendapatan_peternak_setelah_pajak = $(div_rhpp_plasma).find('td.pendapatan_peternak_setelah_pajak').attr('data-val');
    	var tot_bayar_hutang = !empty($(div_rhpp_plasma).find('td.tot_bayar_hutang').attr('data-val')) ? $(div_rhpp_plasma).find('td.tot_bayar_hutang').attr('data-val') : 0;

		var pendapatan_peternak_setelah_potong_hutang = parseFloat(pendapatan_peternak_setelah_pajak) - parseFloat(tot_bayar_hutang);

		$(div_rhpp_plasma).find('td.pendapatan_peternak_setelah_potong_hutang').attr('data-val', pendapatan_peternak_setelah_potong_hutang);

		var text_pendapatan_peternak_setelah_potong_hutang = numeral.formatInt(pendapatan_peternak_setelah_potong_hutang);
		if ( pendapatan_peternak_setelah_potong_hutang < 0 ) {
			text_pendapatan_peternak_setelah_potong_hutang = '('+numeral.formatInt(Math.abs(pendapatan_peternak_setelah_potong_hutang))+')';
		}
		$(div_rhpp_plasma).find('td.pendapatan_peternak_setelah_potong_hutang b').html( text_pendapatan_peternak_setelah_potong_hutang );
    }, // end - hit_pendapatan_peternak_setelah_potong_hutang

	save: function() {
		var div_rhpp = $('#rhpp');

		let data = [];
		var mitra = null;
		var nomor = null;
		var tgl_tutup = null;
		$.map( $(div_rhpp).find('.tab-pane:not(.hide)'), function(tab_pane) {
			let tbody_pemakaian = $(tab_pane).find('table.pemakaian tbody');
			let tbody_penjualan = $(tab_pane).find('table.penjualan_ayam tbody');
			let tbody_list_noreg = $(tab_pane).find('table.tbl_list_noreg tbody');
			let tbody_potongan = $(tab_pane).find('table.potongan tbody');
			let tbody_bonus = $(tab_pane).find('table.bonus tbody');
			let tbody_piutang = $(tab_pane).find('table.tbl-piutang tbody');

			let data_doc = $.map( $(tbody_pemakaian).find('tr.data_doc'), function(tr) {
				let key = $(tr).data('key');

				let _data_doc = {
					'tanggal': $(tr).find('td.tanggal').data('val'),
					'nota': $(tr).find('td.nota').data('val'),
					'barang': $(tr).find('td.barang').data('val'),
					'box_zak': $(tr).find('td.box_zak').data('val'),
					'jumlah': $(tr).find('td.jumlah').data('val'),
					'harga': $(tr).find('td.harga').data('val'),
					'total': $(tr).find('td.total').data('val'),
					'vaksin': ($(tab_pane).attr('id') == 'rhpp_plasma') ? $(tbody_pemakaian).find('tr.data_vaksin[data-key="'+key+'"] td.vaksin').data('val') : '',
					'harga_vaksin': ($(tab_pane).attr('id') == 'rhpp_plasma') ? $(tbody_pemakaian).find('tr.data_vaksin[data-key="'+key+'"] td.harga_vaksin').data('val') : 0,
					'total_vaksin': ($(tab_pane).attr('id') == 'rhpp_plasma') ? $(tbody_pemakaian).find('tr.data_vaksin[data-key="'+key+'"] td.total_vaksin').data('val') : 0
				};

				return _data_doc;
			});

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

			let data_list_noreg = $.map( $(tbody_list_noreg).find('tr'), function(tr) {
				let _data_list_noreg = {
					'noreg': $(tr).find('td.noreg').data('val'),
					'kandang': $(tr).find('td.kandang').data('val'),
					'populasi': $(tr).find('td.populasi').data('val'),
					'tgl_docin': $(tr).find('td.tgl_docin').data('val'),
					'tgl_tutup': $(tr).find('td.tgl_tutup').data('val')
				};

				return _data_list_noreg;
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

			mitra = $(tab_pane).find('label.mitra').data('val');
			nomor = $(tab_pane).find('label.mitra').data('nomor');
			tgl_tutup = dateSQL( $('#TglTutup').data('DateTimePicker').date() );

			data[ $(tab_pane).attr('id') ] = {
				'jenis': $(tab_pane).attr('id'),
				'mitra': mitra,
				'tot_populasi': $(tab_pane).find('label.tot_populasi').data('val'),
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
				'biaya_materai': $('span.biaya_materai').attr('data-val'),
				'bonus_pasar': ($(tab_pane).attr('id') == 'rhpp_plasma') ? $(tab_pane).find('td.bonus_pasar').data('val') : 0,
				'bonus_kematian': ($(tab_pane).attr('id') == 'rhpp_plasma') ? $(tab_pane).find('td.bonus_kematian').data('val') : 0,
				'bonus_insentif_fcr': ($(tab_pane).attr('id') == 'rhpp_plasma') ? $(tab_pane).find('td.bonus_insentif_fcr').data('val') : 0,
				'biaya_operasional': ($(tab_pane).attr('id') == 'rhpp_inti') ? $(tab_pane).find('td.biaya_opr').attr('data-val') : 0,
				'cn': ($(tab_pane).attr('id') == 'rhpp_inti') ? (!empty($(tab_pane).find('td.cn').data('val')) ? $(tab_pane).find('td.cn').data('val') : null) : null,
				'pdpt_peternak_belum_pajak': ($(tab_pane).attr('id') == 'rhpp_plasma') ? $(tab_pane).find('td.pendapatan_peternak').data('val') : 0,
				'prs_potongan_pajak': $(tab_pane).find('span.prs_pajak').attr('data-val'),
				'potongan_pajak': $(tab_pane).find('td.nilai_potongan_pajak').attr('data-val'),
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
				'data_list_noreg': data_list_noreg,
				'bonus_insentif_listrik': ($(tab_pane).attr('id') == 'rhpp_plasma') ? numeral.unformat($(tab_pane).find('td.bonus_insentif_listrik').data('bonus')) : 0,
				'total_bonus_insentif_listrik': ($(tab_pane).attr('id') == 'rhpp_plasma') ? $(tab_pane).find('td.bonus_insentif_listrik').data('val') : 0,
				'data_potongan': data_potongan,
				'data_bonus': data_bonus,
				'total_potongan': ($(tab_pane).attr('id') == 'rhpp_plasma') ? numeral.unformat($('td.total_potongan').attr('data-val')) : 0,
                'total_bonus': ($(tab_pane).attr('id') == 'rhpp_plasma') ? numeral.unformat($('td.total_bonus').attr('data-val')) : 0,
				'data_piutang': data_piutang
			};
		});

		bootbox.confirm('Apakah anda yakin ingin menyimpan data RHPP Group mitra <b>'+mitra.toUpperCase()+'</b>', function(result) {
			if ( result ) {
				showLoading;

				let data_rhpp = {
					0: data['rhpp_inti'],
					1: data['rhpp_plasma'],
				};

				let params = {
					'mitra': mitra,
					'nomor': nomor,
					'tgl_tutup': tgl_tutup,
					'data_rhpp': data_rhpp
				};

				$.ajax({
		            url : 'transaksi/RhppGroup/save',
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
		            			var a = '<a class="cursor-p lihat" onclick="rg.proses_hit_rhpp_group(this)" data-href="rhpp" data-id="'+data.content.id+'">Lihat</a>';
		            			$(a).click();

		            			var div = $('#noreg_tutup_siklus');
		            			if ( !empty($(div).find('select.mitra').val()) && !empty($(div).find('#StartDate input').val()) && !empty($(div).find('#EndDate input').val()) ) {
		            				$('button#btn-tampil-noreg').click();
		            			}
		            		});
		            	} else {
		            		bootbox.alert( data.message );
		            	}
		            },
		        });
			}
		});
	}, // end - save

	delete: function(elm) {
		var div_rhpp = $('#rhpp');

		let data = [];
		var mitra = null;
		var nomor = null;
		$.map( $(div_rhpp).find('.tab-pane'), function(tab_pane) {
			mitra = $(tab_pane).find('label.mitra').data('val');
		});

		bootbox.confirm('Apakah anda yakin ingin meng-hapus data RHPP Group mitra <b>'+mitra.toUpperCase()+'</b>', function(result) {
			if ( result ) {
				var id = $(elm).data('id');

				$.ajax({
		            url : 'transaksi/RhppGroup/delete',
		            data : {
		                'params' : id
		            },
		            type : 'POST',
		            dataType : 'JSON',
		            beforeSend : function(){ showLoading; },
		            success : function(data){
		            	hideLoading();

		            	if ( data.status == 1 ) {
		            		bootbox.alert( data.message, function() {
		            			$('#rhpp').html('<h4>RHPP Group</h4>');
		            			var div = $('#riwayat');
		            			if ( !empty($(div).find('select.mitra').val()) && !empty($(div).find('#StartDateRiwayat input').val()) && !empty($(div).find('#EndDateRiwayat input').val()) ) {
		            				$('button#btn-tampil').click();
		            			}
		            		});
		            	} else {
		            		bootbox.alert( data.message );
		            	}
		            },
		        });
			}
		});
	}, // end - delete

	export_excel_plasma : function (elm) {
    	var id = $(elm).data('id');
		goToURL('transaksi/RhppGroup/export_excel_plasma/'+id);
	}, // end - export_excel_plasma

	export_excel_inti : function (elm) {
    	var id = $(elm).data('id');
		goToURL('transaksi/RhppGroup/export_excel_inti/'+id);
	}, // end - export_excel_inti

	print: function (elm) {
        var id = $(elm).data('id');

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
                                url: 'transaksi/RhppGroup/updateCatatan',
                                data: {
                                    'id': id,
                                    'keterangan': keterangan
                                },
                                type: 'POST',
                                dataType: 'JSON',
                                beforeSend: function() { showLoading(); },
                                success: function(data) {
                                    hideLoading();
                                    if ( data.status == 1 ) {
                                        rg.export_pdf(id);
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

    export_pdf : function (id) {
        window.open('transaksi/RhppGroup/export_pdf/'+id, 'blank');
    }, // end - export_excel

    submitCn: function(elm) {
        let err = 0;

        let div_rhpp = $(elm).closest('div#rhpp');
        let id = $(elm).attr('data-id');

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
        	let mitra = null;
            let data = [];
            $.map( $(div_rhpp).find('.tab-pane:not(.hide)'), function(tab_pane) {
            	mitra = $(tab_pane).find('label.mitra').data('val');

                data[ $(tab_pane).attr('id') ] = {
                    'jenis': $(tab_pane).attr('id'),
                    'lr_inti': ($(tab_pane).attr('id') == 'rhpp_inti') ? $(tab_pane).find('td.pendapatan_peternak').data('val') : 0,
                };
            });

            bootbox.confirm( 'Apakah anda yakin ingin menyimpan data RHPP Group mitra <b>'+mitra.toUpperCase()+'</b> ?', function(result) {
                if ( result ) {
                    let data_rhpp = {
                        0: data['rhpp_inti'],
                        1: data['rhpp_plasma'],
                    };

                    var params = {
                        'id': id,
                        'nilai_cn': numeral.unformat($('input.nilai_cn').val()),
						'nilai_opr': numeral.unformat($('input.nilai_opr').val()),
                        'data_rhpp': data_rhpp
                    };

                    $.ajax({
                        url : 'transaksi/RhppGroup/submitCn',
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
                                    var a = '<a class="cursor-p lihat" onclick="rg.proses_hit_rhpp_group(this)" data-href="rhpp" data-id="'+id+'">Lihat</a>';
		            				$(a).click();
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

		$.get('transaksi/RhppGroup/modalPiutang',{
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
			html += '<td class="text-right"><input type="text" class="form-control text-right nominal" data-tipe="decimal" data-required="1" onblur="rg.hit_tot_bayar_hutang(this)"></td>';
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

rg.start_up();