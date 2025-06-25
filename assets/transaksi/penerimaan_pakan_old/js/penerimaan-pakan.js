var pp = {
	start_up : function () {
		$('#datetimepicker1').datetimepicker({
            locale: 'id',
            format: 'DD MMM Y HH:mm:ss',
        	sideBySide: true
        });
	}, // end - start_up

	set_noreg : function (elm) {
		var div = $(elm).closest('div#penerimaan-pakan');
		var unit = $(elm).val();

		$.ajax({
			url: 'transaksi/PenerimaanPakan/get_noreg',
			data: {
				'params': unit
			},
			type: 'POST',
			dataType: 'JSON',
			beforeSend: function() {
				showLoading();
			},
			success: function(data) {
				hideLoading();
				if ( data.status == 1 ) {
					$(div).find('select[name=noreg] option').remove();
					var opt = '<option value="">-- Pilih No. Reg --</option>';
					
					if ( data.content.length > 0 ) {
						for (var i = 0; i < data.content.length; i++) {
							opt += '<option value=' + data.content[i].noreg + '>' + data.content[i].noreg + '</option>';
						};
					};

					$(div).find('select[name=noreg]').append(opt);
				} else {
					bootbox.alert(data.message);
				};
			},
	    });
	}, // end - set_noreg

	set_value : function (elm) {
		var div = $(elm).closest('div#penerimaan-pakan');
		var noreg = $(elm).val();
		var table = $(div).find('table.tbl_list_pakan');

		$.ajax({
			url: 'transaksi/PenerimaanPakan/set_value',
			data: {
				'params': noreg
			},
			type: 'POST',
			dataType: 'JSON',
			beforeSend: function() {
				showLoading();
			},
			success: function(data) {
				hideLoading();
				if ( data.status == 1 ) {
					// console.log(data.content.detail);
					$(div).find('input[name=peternak]').val(data.content.mapping[noreg].peternak);
					$(div).find('input[name=ekspedisi]').val(data.content.mapping[noreg].ekspedisi);
					$(div).find('input[name=ekspedisi]').attr('data-idekspedisi', data.content.mapping[noreg].id_ekspedisi);
					$(div).find('input[name=kandang]').val(data.content.mapping[noreg].kandang);
					$(div).find('input[name=populasi]').val( numeral.formatInt(data.content.mapping[noreg].populasi) );

					$(table).find('tbody').html(data.content.detail);

					$('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal], [data-tipe=decimal3],[data-tipe=decimal4], [data-tipe=number]').each(function(){
			            $(this).priceFormat(Config[$(this).data('tipe')]);
			        });
				} else {
					bootbox.alert(data.message);
				};
			},
	    });
	}, // end - set_value

	save : function() {
		var err = 0;

		$.map( $('[data-required=1]'), function(ipt) {
			if ( empty($(ipt).val()) ) {
				$(ipt).parent().addClass('has-error');
				err++;
			} else {
				$(ipt).parent().removeClass('has-error');
			};
		});

		if ( err > 0 ) {
			bootbox.alert('Data belum lengkap, mohon cek kembali data yang anda masukkan.');
		} else {
			bootbox.confirm('Apakah anda yakin ingin menyimpan data Penerimaan Pakan ?', function(result) {
				if ( result ) {
					var unit = $('select[name=unit]').val();
					var noreg = $('select[name=noreg]').val();
					var tgl_terima = dateTimeSQL( $('#datetimepicker1').data('DateTimePicker').date() );
					var no_sj = $('input[name=no_sj]').val();
					var ekspedisi = $('input[name=ekspedisi]').data('idekspedisi');
					var nama_sopir = $('input[name=nama_sopir]').val();
					var nopol = $('input[name=nopol]').val();

					var data_pakan_terima = $.map( $('table.tbl_list_pakan').find('tr.data'), function(tr) {
						var id_detspm = $(tr).data('iddetspm');
						var pakan_terima = $(tr).find('select[name=pakan]').val();
						var zak_terima = numeral.unformat( $(tr).find('input[name=zak]').val() );
						var kg_terima = numeral.unformat( $(tr).find('input[name=tonase]').val() );

						if ( zak_terima != 0 && kg_terima != 0 ) {
							var data = {
								'id_detspm' : id_detspm,
								'pakan_terima' : pakan_terima,
								'zak_terima' : zak_terima,
								'kg_terima' : kg_terima
							};

							return data;
						};
					});

					var data = {
						'unit' : unit,
						'noreg' : noreg,
						'tgl_terima' : tgl_terima,
						'no_sj' : no_sj,
						'ekspedisi' : ekspedisi,
						'nama_sopir' : nama_sopir,
						'nopol' : nopol,
						'detail' : data_pakan_terima
					};

					if ( data_pakan_terima.length > 0 ) {
						pp.execute_save(data);
					} else {
						bootbox.alert('Data pakan belum ada yang anda masukkan.');
					};
				};
			});
		};
	}, // end - save

	execute_save : function(params) {
		$.ajax({
			url: 'transaksi/PenerimaanPakan/save',
			data: {
				'params': params
			},
			type: 'POST',
			dataType: 'JSON',
			beforeSend: function() {
				showLoading();
			},
			success: function(data) {
				hideLoading();
				if ( data.status == 1 ) {
					bootbox.alert( data.message, function () {
						location.reload();
					});
				} else {
					bootbox.alert(data.message);
				};
			},
	    });
	}, // end - excecute_save
};

pp.start_up();