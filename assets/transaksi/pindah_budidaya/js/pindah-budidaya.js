var pb = {
	start_up: function () {	
		pb.setting_up();
	}, // end - start_up

	setting_up: function() {
		$('#tgl_docin, #tgl_pindah').datetimepicker({
            locale: 'id',
            format: 'DD MMM Y'
        });

        $("#tgl_docin").on("dp.change", function (e) {
            pb.get_data_asal(this);
        });
        $("#tgl_pindah").on("dp.change", function (e) {
            pb.get_data_tujuan(this);
        });

        $('div.panel_asal').find('select.mitra').on('change', function() {
        	var populasi = $(this).find('option:selected').data('populasi');

        	if ( !empty(populasi) ) {
        		$('div.panel_asal').find('[name=populasi]').val( numeral.formatInt(populasi) );
        	} else {
        		$('div.panel_asal').find('[name=populasi]').val( numeral.formatInt(0) );
        	}
        });

        $('div.panel_tujuan').find('select.mitra').on('change', function() {
        	pb.get_data_kandang_tujuan(this);
        });
	}, // end - setting_up

	get_data_asal: function(elm) {
		var div = $(elm).closest('div.panel-body');

		var _tanggal = $(div).find('#tgl_docin input').val();
		var unit = $(div).find('.panel_asal select.unit').val();
		if ( !empty(_tanggal) && !empty(unit) ) {
			var tanggal = dateSQL( $(div).find('#tgl_docin').data('DateTimePicker').date() );

			var params = {
				'tanggal': tanggal,
				'unit': unit
			};

			$.ajax({
	            url : 'transaksi/PindahBudidaya/get_data_asal',
	            data : {
	                'params' :  params
	            },
	            type : 'POST',
	            dataType : 'JSON',
	            beforeSend : function(){ showLoading(); },
	            success : function(data){
	                hideLoading();

	                var html = '<option value="">Pilih Plasma</option>';
	                if ( !empty(data.data) ) {
	                	for (var i = 0; i < data.data.length; i++) {
	                		html += '<option value="'+data.data[i].noreg+'" data-populasi="'+data.data[i].populasi+'">'+data.data[i].mitra+' ('+data.data[i].noreg+')'+'</option>';
	                	}
	                }

	                $(div).find('.panel_asal select.mitra').html( html );
	            },
	        });
		}
	}, // end - get_data_asal

	get_data_tujuan: function(elm) {
		var div = $(elm).closest('div.panel-body');

		var unit = $(div).find('.panel_tujuan select.unit').val();
		if ( !empty(unit) ) {
			var params = {
				'unit': unit
			};

			$.ajax({
	            url : 'transaksi/PindahBudidaya/get_data_tujuan',
	            data : {
	                'params' :  params
	            },
	            type : 'POST',
	            dataType : 'JSON',
	            beforeSend : function(){ showLoading(); },
	            success : function(data){
	                hideLoading();

	                var html_mitra = '<option value="">Pilih Plasma</option>';
	                if ( !empty(data.data.mitra) ) {
	                	for (var i = 0; i < data.data.mitra.length; i++) {
	                		html_mitra += '<option value="'+data.data.mitra[i].nomor+'">'+data.data.mitra[i].mitra+'</option>';
	                	}
	                }

	                var html_kanit = '<option value="">Pilih Kepala Unit</option>';
	                if ( !empty(data.data.karyawan.kanit) ) {
	                	for (var i = 0; i < data.data.karyawan.kanit.length; i++) {
	                		html_kanit += '<option value="'+data.data.karyawan.kanit[i].nik+'">'+data.data.karyawan.kanit[i].nama.toUpperCase()+'</option>';
	                	}
	                }

	                var html_ppl = '<option value="">Pilih PPL</option>';
	                if ( !empty(data.data.karyawan.ppl) ) {
	                	for (var i = 0; i < data.data.karyawan.ppl.length; i++) {
	                		html_ppl += '<option value="'+data.data.karyawan.ppl[i].nik+'">'+data.data.karyawan.ppl[i].nama.toUpperCase()+'</option>';
	                	}
	                }

	                var html_marketing = '<option value="">Pilih Marketing</option>';
	                if ( !empty(data.data.karyawan.marketing) ) {
	                	for (var i = 0; i < data.data.karyawan.marketing.length; i++) {
	                		html_marketing += '<option value="'+data.data.karyawan.marketing[i].nik+'">'+data.data.karyawan.marketing[i].nama.toUpperCase()+'</option>';
	                	}
	                }

	                var html_koar = '<option value="">Pilih Koordinator Area</option>';
	                if ( !empty(data.data.karyawan.koordinator) ) {
	                	for (var i = 0; i < data.data.karyawan.koordinator.length; i++) {
	                		html_koar += '<option value="'+data.data.karyawan.koordinator[i].nik+'">'+data.data.karyawan.koordinator[i].nama.toUpperCase()+'</option>';
	                	}
	                }

	                var html_kontrak = '<option value="">Pilih Kontrak</option>';
	                if ( !empty(data.data.kontrak) ) {
	                	for (var i = 0; i < data.data.kontrak.length; i++) {
	                		html_kontrak += '<option value="'+data.data.kontrak[i].nik+'" data-perusahaan="" data-pola="">'+data.data.kontrak[i].nama.toUpperCase()+'</option>';
	                	}
	                }

	                $(div).find('.panel_tujuan select.mitra').html( html_mitra );
	                $(div).find('.panel_tujuan select.kanit').html( html_kanit );
	                $(div).find('.panel_tujuan select.kanit').removeAttr('readonly');
	                $(div).find('.panel_tujuan select.ppl').html( html_ppl );
	                $(div).find('.panel_tujuan select.ppl').removeAttr('readonly');
	                $(div).find('.panel_tujuan select.marketing').html( html_marketing );
	                $(div).find('.panel_tujuan select.marketing').removeAttr('readonly');
	                $(div).find('.panel_tujuan select.koar').html( html_koar );
	                $(div).find('.panel_tujuan select.koar').removeAttr('readonly');
	                $(div).find('.panel_tujuan select.kontrak').html( html_kontrak );
	                $(div).find('.panel_tujuan select.kontrak').removeAttr('readonly');
	            },
	        });
		}
	}, // end - get_data_tujuan

	get_data_kandang_tujuan: function(elm) {
		var div = $(elm).closest('div.panel-body');

		var mitra = $(div).find('.panel_tujuan select.mitra').val();
		if ( !empty(mitra) ) {
			var params = {
				'mitra': mitra
			};

			$.ajax({
	            url : 'transaksi/PindahBudidaya/get_data_kandang_tujuan',
	            data : {
	                'params' :  params
	            },
	            type : 'POST',
	            dataType : 'JSON',
	            beforeSend : function(){ showLoading(); },
	            success : function(data){
	                hideLoading();

	                var html = '<option value="">Pilih Kandang</option>';
	                if ( !empty(data.data) ) {
	                	for (var i = 0; i < data.data.length; i++) {
	                		html += '<option value="'+data.data[i].id+'" data-nim="'+data.data[i].nim+'" data-kdg="'+data.data[i].kandang+'">'+data.data[i].kandang+'</option>';
	                	}
	                }

	                $(div).find('.panel_tujuan select.kandang').html( html );
	            },
	        });
		}
	}, // end - get_data_kandang_tujuan

	save: function(elm) {
		var div = $(elm).closest('div.panel-body');

		var err = 0;
		$.map( $(div).find('[data-required=1]'), function(ipt) {
			if ( empty($(ipt).val()) ) {
				$(ipt).parent().addClass('has-error');
				err++;
			} else {
				$(ipt).parent().removeClass('has-error');
			}
		});

		if ( err > 0 ) {
			bootbox.alert('Harap lengkapi data terlebih dahulu.');
		} else {
			bootbox.confirm('Harap lengkapi data terlebih dahulu.', function(result) {
				if ( result ) {
					var asal = {
						'tgl_docin': dateSQL($(div).find('.panel_asal #tgl_docin').data('DateTimePicker').date()),
						'unit': $(div).find('.panel_asal .unit').val(),
						'noreg': $(div).find('.panel_asal .mitra').val(),
						'populasi': numeral.unformat($(div).find('.panel_asal [name=populasi]').val())
					};
					var tujuan = {
						'tgl_pindah': dateSQL($(div).find('.panel_tujuan #tgl_pindah').data('DateTimePicker').date()),
						'unit': $(div).find('.panel_tujuan .unit').val(),
						'mitra': $(div).find('.panel_tujuan .mitra').val(),
						'id_kandang': $(div).find('.panel_tujuan .kandang').val(),
						'nim': $(div).find('.panel_tujuan .kandang option:selected').data('nim'),
						'kandang': $(div).find('.panel_tujuan .kandang option:selected').data('kdg'),
						'populasi': numeral.unformat($(div).find('.panel_tujuan [name=populasi]').val())
					};

					var params = {
						'asal': asal,
						'tujuan': tujuan
					};

					$.ajax({
			            url : 'transaksi/PindahBudidaya/save',
			            data : {
			                'params' :  params
			            },
			            type : 'POST',
			            dataType : 'JSON',
			            beforeSend : function(){ showLoading(); },
			            success : function(data){
			                hideLoading();

			                if ( data.status == 1 ) {
			                	bootbox.alert(data.message, function() {});
			                } else {
			                	bootbox.alert(data.message, function() {});
			                }
			            },
			        });
				}
			});
		}
	}, // end - save
};

pb.start_up();