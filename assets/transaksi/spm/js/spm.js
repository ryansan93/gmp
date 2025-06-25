var spm = {
	start_up: function () {
        spm.getLists_sp();
        spm.getLists_spm();

        $("[name=filter-tgl]").datetimepicker({
			locale: 'id',
            format: 'DD MMM Y'
		});
	}, // end - start_up

	getLists_sp : function(keyword = null){
        $.ajax({
            url : 'transaksi/SPM/list_sp',
            data : {'keyword' : keyword},
            dataType : 'HTML',
            type : 'GET',
            beforeSend : function(){},
            success : function(data){
                $('table.tbl_spm tbody').html(data);

                $('[name=tglRcnKirim]').datetimepicker({
		            locale: 'id',
		            format: 'DD MMM Y'
		        });

		        $('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal], [data-tipe=decimal3],[data-tipe=decimal4], [data-tipe=number]').each(function(){
		            $(this).priceFormat(Config[$(this).data('tipe')]);
		        });
            }
        });
    }, // end - getLists_sp

    getLists_spm : function(keyword = null){
        $.ajax({
            url : 'transaksi/SPM/list_spm',
            data : {'keyword' : keyword},
            dataType : 'HTML',
            type : 'GET',
            beforeSend : function(){},
            success : function(data){
                $('div#pme').html(data);

		        $('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal], [data-tipe=decimal3],[data-tipe=decimal4], [data-tipe=number]').each(function(){
		            $(this).priceFormat(Config[$(this).data('tipe')]);
		        });
            }
        });
    }, // end - getLists_spm

	hit_jml_zak: function (elm) {
		var tr = $(elm).closest('tr');
		var ipt_zak = $(tr).find('input#rcn_zak');

		var kg = numeral.unformat( $(elm).val() );
		var zak = Math.ceil( kg / 50 );

		ipt_zak.val( numeral.formatInt(zak) );
	}, // end - hit_jml_zak

	save_per_unit: function (elm) {
		var tr = $(elm).closest('tr');

		var id_unit = $(elm).data('id');
		var nama_unit = $(tr).find('td.nama_unit').text();

		var err = 0;
		$.map( $('tr[data-id='+id_unit+']').find('input, select'), function(ipt) {
			if ( empty( $(ipt).val() ) ) {
				$(ipt).parent().addClass('has-error');
				err++;
			} else {
				$(ipt).parent().removeClass('has-error');
			};
		});

		if ( err > 0 ) {
			bootbox.alert('Data yang anda masukkan pada unit ' + nama_unit + ' belum lengkap.');
		} else {
			bootbox.confirm('Apakan anda yakin ingin menyimpan data ?', function(result) {
				if ( result ) {
					var data = $.map( $('tr[data-id='+id_unit+']'), function(tr) {
						var _data = {
							'unit' 					: id_unit,
							'noreg' 				: $(tr).find('td.noreg').text(),
							'umur' 					: $(tr).find('td.umur').text(),
							'pakan' 				: $(tr).find('td.pakan').data('kode'),
							'tgl_rcn_kirim' 		: dateSQL($('[name=tglRcnKirim]').data('DateTimePicker').date()),
							'kg_rcn_kirim'			: numeral.unformat( $(tr).find('input#rcn_kg').val() ),
							'zak_rcn_kirim'			: numeral.unformat( $(tr).find('input#rcn_zak').val() ),
							'ekspedisi_rcn_kirim'	: $(tr).find('select[name=ekspedisi]').val(),
						};

						return _data;
					});

					spm.execute_save(data);
				};
			});
		};
	}, // end - save_per_unit

	execute_save: function(params) {
        $.ajax({
            url: 'transaksi/SPM/save_per_unit',
            data: {'params': params },
            type: 'POST',
            dataType: 'JSON',
            beforeSend: function(){showLoading()},
            success: function(data){
                hideLoading();
                if (data.status == 1) {
                    bootbox.alert(data.message, function() {
                    	spm.getLists_sp();
                    	spm.getLists_spm();
                    });
                }else{
                    alertDialog(data.message);
                }
            }
        });
    }, // end - execute_save

    save_spm: function (elm) {
		var tr = $(elm).closest('tr');

		var id_unit = $(elm).data('id');
		var nama_unit = $(tr).find('td.nama_unit').text();

		var jml = 0;
		$.map( $('tr.data').find('[type=checkbox]'), function(check) {
			if ( $(check).is(':checked') ) {
				jml++;
			};
		});

		if ( jml == 0 ) {
			bootbox.alert('Belum ada data yang tercentang.');
		} else {
			bootbox.confirm('Apakan anda yakin ingin menyimpan data SPM ?', function(result) {
				if ( result ) {
					var ekspedisi = $('select.ekspedisi').val();
					var tot_tonase = numeral.unformat( $('input.total-kg').val() );
    				var tot_zak = numeral.unformat( $('input.total-zak').val() );

					var data = $.map( $('tr.data').find('[type=checkbox]'), function(check) {
						if ( $(check).is(':checked') ) {
							var tr = $(check).closest('tr');
							var _data = {
								'id' : $(tr).data('idrcnkirim')
							};

							return _data;
						};
					});

					var _spm = {
						'ekspedisi' : ekspedisi,
						'tot_tonase' : tot_tonase,
						'tot_zak' : tot_zak,
						'detail' : data
					};

					// console.log(_spm);
					spm.execute_save_spm(_spm);
				};
			});
		};
	}, // end - save_spm

	execute_save_spm: function(params) {
        $.ajax({
            url: 'transaksi/SPM/save_spm',
            data: {'params': params },
            type: 'POST',
            dataType: 'JSON',
            beforeSend: function(){showLoading()},
            success: function(data){
                hideLoading();
                if (data.status == 1) {
                    bootbox.alert(data.message, function() {
                    	spm.getLists_sp();
                    	spm.getLists_spm();
                    });
                }else{
                    alertDialog(data.message);
                }
            }
        });
    }, // end - execute_save_spm

    load_form_cetak_spm: function() {
		$.get('transaksi/SPM/load_form_cetak_spm',{
			},function(data){
			var _options = {
				className : 'veryWidth',
				message : data,
				size : 'large',
			};
			bootbox.dialog(_options).bind('shown.bs.modal', function(){
				$(this).addClass('cetak_spm');

				var modal_body = $(this).find('.modal-body');
				var table = $(modal_body).find('table');
				var tbody = $(table).find('tbody');
				if ( $(tbody).find('.modal-body tr').length <= 1 ) {
			        $(this).find('tr #btn-remove').addClass('hide');
			    };

			    spm.getLists_cetkSPM();
			});
		},'html');
	}, // end - load_form_cetak_spm

	getLists_cetkSPM : function(keyword = null){
        $.ajax({
            url : 'transaksi/SPM/list_cetak_spm',
            data : {'keyword' : keyword},
            dataType : 'HTML',
            type : 'GET',
            beforeSend : function(){},
            success : function(data){
                $('table.cetak_spm').find('tbody').html(data);
            }
        });
    }, // end - getLists_spm

    cetak_spm : function(elm) {
    	var href = $(elm).data('href');
    	var nospm = $(elm).data('nospm');

    	$.ajax({
            url: 'transaksi/SPM/update_tgl_cetak_spm',
            data: {'params': nospm },
            type: 'POST',
            dataType: 'JSON',
            beforeSend: function(){showLoading()},
            success: function(data){
                hideLoading();
                if (data.status == 1) {
                    spm.getLists_cetkSPM();
                }else{
                    alertDialog(data.message);
                }
            }
        });
    }, // end - cetak_spm

    mark_view: function(elm) {
    	set_mark(elm);

    	var div = $(elm).closest('div#pme');
    	var tbody = $(elm).closest('tbody');
    	var tot_tonase = 0;
    	var tot_zak = 0;

    	$.map( $(tbody).find('tr.data'), function(tr) {
    		var check = $(tr).find('[type=checkbox]');
    		if ( $(check).is(':checked') ) {
	    		var tr = $(check).closest('tr');
	    		var tonase = numeral.unformat( $(tr).find('td.tonase').text() );
	    		var zak = numeral.unformat( $(tr).find('td.zak').text() );

	    		tot_tonase += tonase;
	    		tot_zak += zak;
    		};
    	});

    	$(div).find('input.total-kg').val( numeral.formatDec( tot_tonase ) );
    	$(div).find('input.total-zak').val( numeral.formatInt( tot_zak ) );
	}, // end - mark_view

	mark_view_all: function(elm) {
		set_mark_all(elm);

		var div = $(elm).closest('div#pme');
    	var tbody = $(elm).closest('tbody');
    	var tot_tonase = 0;
    	var tot_zak = 0;

    	$.map( $(tbody).find('tr.data'), function(tr) {
    		var check = $(tr).find('[type=checkbox]');
    		if ( $(check).is(':checked') ) {
	    		var tr = $(check).closest('tr');
	    		var tonase = numeral.unformat( $(tr).find('td.tonase').text() );
	    		var zak = numeral.unformat( $(tr).find('td.zak').text() );

	    		tot_tonase += tonase;
	    		tot_zak += zak;
    		};
    	});

    	$(div).find('input.total-kg').val( numeral.formatDec( tot_tonase ) );
    	$(div).find('input.total-zak').val( numeral.formatInt( tot_zak ) );
	}, // end - mark_view

	filter: function() {
		let filter_tgl = $('[name=filter-tgl]').find('input').val();

		if ( !empty(filter_tgl) ) {
			$.map( $('table.tbl_spm').find('tbody tr.data'), function(tr) {
				let tgl = $(tr).find('td.setting_tgl').text().trim();

				if ( filter_tgl != tgl ) {
					$(tr).addClass('hide');
				} else {
					$(tr).removeClass('hide');
				}
			});
		} else {
			$('table.tbl_spm').find('tbody tr.data').removeClass('hide');
		}
	}, // end - filter
};

spm.start_up();