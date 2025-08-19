var real_sj = {
	start_up: function () {
		$("[name=tgl_panen]").datetimepicker({
			locale: 'id',
            format: 'DD MMM Y'
		});
		// $("[name=tgl_panen]").on("dp.change", function (e) {
		// 	real_sj.get_mitra(this);
		// });
	}, // end - start_up

	addRow: function(elm) {
        let row = $(elm).closest('tr');
        let newRow = row.clone();

        newRow.find('input, select').val('');
        newRow.attr('data-id', '');
        row.after(newRow);

        App.formatNumber();
    }, // end - addRow

    removeRow: function(elm) {
        let table = $(elm).closest('table.detail');
        let tbody = $(elm).closest('tbody');
        let row = $(elm).closest('tr');
        if ($(tbody).find('tr').length > 1) {
            $(row).prev('tr').find('.btn-ctrl').show();
            $(row).remove();
        }else{
            $(row).prev('tr').find('.btn-ctrl').show();
        }
    }, // end - removeRow

	get_mitra: function(elm) {
        let form = $(elm).closest('form');
        
        let unit = $(form).find('select.unit').val();
        let tgl_panen = dateSQL( $("[name=tgl_panen]").data('DateTimePicker').date() );

        if ( !empty( $("[name=tgl_panen]").find('input').val() ) && !empty( unit ) ) {
	        let params = {
	            'unit': unit,
	            'tgl_panen': tgl_panen
	        };

	        $.ajax({
	            url : 'transaksi/RealisasiSJ/get_mitra',
	            data : {
	                'params' :  params
	            },
	            type : 'POST',
	            dataType : 'JSON',
	            beforeSend : function(){ showLoading(); },
	            success : function(data){
	            	hideLoading();
	            	if ( data.status == 1 ) {
	            		let opt = '<option value="">Pilih Mitra</option>';
	            		if ( !empty( data.content ) ) {
	            			for (var i = 0; i < data.content.length; i++) {
	            				opt += '<option value="'+data.content[i].noreg+'">'+ data.content[i].kode_unit + ' | ' + data.content[i].mitra + ' (' +  data.content[i].noreg + ')'+'</option>';
	            			}
	            		}

	            		$('select.mitra').html( opt );
	            	}
	            },
	        });
        } else {
        	let opt = '<option value="">Pilih Mitra</option>';
			$('select.mitra').html( opt );

			real_sj.get_data( $('select.mitra') );
        }
    }, // end - get_mitra

    get_data: function(elm) {
    	let form = $(elm).closest('form');

    	let unit = $(form).find('select.unit').val();
    	let tgl_panen = dateSQL( $("[name=tgl_panen]").data('DateTimePicker').date() );
    	let noreg = $(form).find('select.mitra').val();
    	let resubmit = $(elm).data('resubmit');
    	if ( empty(resubmit) ) { resubmit = null; }

    	let params = {
    		'unit': unit,
    		'tgl_panen': tgl_panen,
    		'noreg': noreg,
    		'resubmit': resubmit
    	};

    	$.ajax({
            url : 'transaksi/RealisasiSJ/get_data',
            data : {
                'params' :  params
            },
            type : 'GET',
            dataType : 'HTML',
            beforeSend : function(){ showLoading(); },
            success : function(html){
            	hideLoading();
            	$('div.data_sj').html( html );

            	if ( !empty(resubmit) ) {
            		$('select.unit').attr('disabled', 'disabled');
            		$('[name=tgl_panen] input').attr('disabled', 'disabled');
            		$('select.mitra').attr('disabled', 'disabled');
            	} else {
            		$('select.unit').removeAttr('disabled');
            		$('[name=tgl_panen] input').removeAttr('disabled');
            		$('select.mitra').removeAttr('disabled');
            	}

            	real_sj.hit_total();

            	App.formatNumber();
            },
        });
    }, // end - get_data

    hit_total: function() {
    	let data = $('table.tbl_list_plg').find('tbody tr.data').length;

    	if ( data > 0 ) {
    		let tot_ekor = 0;
    		let tot_kg = 0;
    		let tot_bb = 0;
    		$.map( $('table.tbl_list_plg').find('tbody tr.data'), function(tr) {
    			let tr_realisasi = $(tr).next('tr.realisasi');
				$.map( $(tr_realisasi).find('table tbody tr'), function(tr_real) {
	    			let ekor = numeral.unformat( $(tr_real).find('input.ekor').val() );
	    			tot_ekor += ekor;

	    			let kg = numeral.unformat( $(tr_real).find('input.tonase').val() );
	    			tot_kg += kg;
				});
    		});

    		if ( tot_ekor > 0 && tot_kg > 0 ) {
    			tot_bb = tot_kg / tot_ekor;
    		} else {
    			tot_bb = 0;
    		}

    		$('input.tot_ekor').val( numeral.formatInt(tot_ekor) );
			$('input.tot_kg').val( numeral.formatDec(tot_kg) );
			$('input.tot_bb').val( numeral.formatDec(tot_bb) );
    	} else {
    		$('input.tot_ekor').val(0);
			$('input.tot_kg').val(0);
			$('input.tot_bb').val(0);

			$('input.netto_ekor').val(0);
			$('input.netto_kg').val(0);
			$('input.netto_bb').val(0);
    	}

    	real_sj.hit_total_netto();
    }, // end - hit_total

    hit_bb: function(elm) {
    	let tr = $(elm).closest('tr');

    	let ekor = numeral.unformat( $(tr).find('input.ekor').val() );
		let kg = numeral.unformat( $(tr).find('input.tonase').val() );

		let bb = 0;
		if ( ekor != 0 && kg != 0 ) {
			bb = kg / ekor;
		}
		$(tr).find('input.bb').val( numeral.formatDec(bb) );

		real_sj.hit_total();
    }, // end - hit_bb

    hit_total_netto: function() {
    	let ekor = numeral.unformat( $('input.tot_ekor').val() );
		let kg = numeral.unformat( $('input.tot_kg').val() );
		let tara = numeral.unformat( $('input.tara').val() );

		let bb = 0;
		if ( ekor != 0 && kg != 0 ) {
			bb = kg / ekor;
		}

		let kg_netto = kg - tara;
		let bb_netto = 0
		if ( ekor != 0 && kg_netto != 0 ) {
			bb_netto = kg_netto / ekor;
		}

		$('input.netto_ekor').val( numeral.formatInt(ekor) );
		$('input.netto_kg').val( numeral.formatDec(kg_netto) );
		$('input.netto_bb').val( numeral.formatDec(bb_netto) );
    }, // end - hit_bb

	save: function() {
		let err = 0;

		$.map( $('[data-required=1]'), function(ipt) {
			if ( empty( $(ipt).val() ) ) {
				$(ipt).parent().addClass( 'has-error' );
				err++;
			} else {
				$(ipt).parent().removeClass( 'has-error' );
			}
		});

		if ( err > 0 ) {
			bootbox.alert( 'Harap lengkapi data terlebih dahulu.' );
		} else {
			$('.btn_save').disabled = true;
			bootbox.confirm( 'Apakah anda yakin ingin menyimpan data Realisasi SJ ?', function(result) {
				if ( result ) {
					let id_unit = $('select.unit').val();
					let unit = $('select.unit').find('option:selected').text().trim();
					let tgl_panen = dateSQL( $("[name=tgl_panen]").data('DateTimePicker').date() );
					let noreg = $('select.mitra').val();
					let ekor = numeral.unformat( $('input.tot_ekor').val() );
					let kg = numeral.unformat( $('input.tot_kg').val() );
					let bb = numeral.unformat( $('input.tot_bb').val() );
					let tara = numeral.unformat( $('input.tara').val() );
					let netto_ekor = numeral.unformat( $('input.netto_ekor').val() );
					let netto_kg = numeral.unformat( $('input.netto_kg').val() );
					let netto_bb = numeral.unformat( $('input.netto_bb').val() );

					let data_detail = $.map( $('table.tbl_list_plg').find('tbody tr.data'), function(tr) {
						let tr_realisasi = $(tr).next('tr.realisasi');
						let realisasi = $.map( $(tr_realisasi).find('table tbody tr'), function(tr_real) {
							let real = {
								'tonase' : numeral.unformat( $(tr_real).find('input.tonase').val() ),
								'ekor' : numeral.unformat( $(tr_real).find('input.ekor').val() ),
								'bb' : numeral.unformat( $(tr_real).find('input.bb').val() ),
								'harga' : numeral.unformat( $(tr_real).find('input.harga').val() ),
								'jenis_ayam' : $(tr_real).find('select.jenis_ayam').val(),
								'no_nota' : $(tr_real).find('input.no_nota').val()
							};

							return real;
						});

						let data = {
							'id_det_rpah' : $(tr).data('id'),
							'no_do' : $(tr).find('td.no_do').text(),
							'no_sj' : $(tr).find('td.no_sj').text(),
							'no_pelanggan' : $(tr).find('td.pelanggan').data('nomor'),
							'pelanggan' : $(tr).find('td.pelanggan').text(),
							'realisasi' : realisasi
							// 'tonase' : numeral.unformat( $(tr).find('input.tonase').val() ),
							// 'ekor' : numeral.unformat( $(tr).find('input.ekor').val() ),
							// 'bb' : numeral.unformat( $(tr).find('input.bb').val() ),
							// 'harga' : numeral.unformat( $(tr).find('input.harga').val() )
						};

						return data;
					});

					let data = {
						'id_unit' : id_unit,
						'unit' : unit,
						'tgl_panen' : tgl_panen,
						'noreg' : noreg,
						'ekor' : ekor,
						'kg' : kg,
						'bb' : bb,
						'tara' : tara,
						'netto_ekor' : netto_ekor,
						'netto_kg' : netto_kg,
						'netto_bb' : netto_bb,
						'detail' : data_detail
					};

					// console.log( data );
					real_sj.execute_save( data );
				} else {
					$('.btn_save').disabled = false;
				}
			});
		}
	}, // end - save

	execute_save: function(params) {
		$.ajax({
            url : 'transaksi/RealisasiSJ/save',
            data : {
                'params' :  params
            },
            type : 'POST',
            dataType : 'JSON',
            beforeSend : function(){ },
            success : function(data){
				$('.btn_save').disabled = false;
				
            	if ( data.status == 1 ) {
            		bootbox.alert( data.message, function() {
            			let select = $('select.mitra');
            			real_sj.get_data( select );
            		});
            	} else {
            		bootbox.alert( data.message );
            	}
            },
        });
	}, // end - execute_save

	edit: function(elm) {
		let err = 0;

		$.map( $('[data-required=1]'), function(ipt) {
			if ( empty( $(ipt).val() ) ) {
				$(ipt).parent().addClass( 'has-error' );
				err++;
			} else {
				$(ipt).parent().removeClass( 'has-error' );
			}
		});

		if ( err > 0 ) {
			bootbox.alert( 'Harap lengkapi data terlebih dahulu.' );
		} else {
			bootbox.confirm( 'Apakah anda yakin ingin meng-ubah data Realisasi SJ ?', function(result) {
				if ( result ) {
					let id_real_sj = $(elm).data('id');
					let id_unit = $('select.unit').val();
					let unit = $('select.unit').find('option:selected').text().trim();
					let tgl_panen = dateSQL( $("[name=tgl_panen]").data('DateTimePicker').date() );
					let noreg = $('select.mitra').val();
					let ekor = numeral.unformat( $('input.tot_ekor').val() );
					let kg = numeral.unformat( $('input.tot_kg').val() );
					let bb = numeral.unformat( $('input.tot_bb').val() );
					let tara = numeral.unformat( $('input.tara').val() );
					let netto_ekor = numeral.unformat( $('input.netto_ekor').val() );
					let netto_kg = numeral.unformat( $('input.netto_kg').val() );
					let netto_bb = numeral.unformat( $('input.netto_bb').val() );

					let data_detail = $.map( $('table.tbl_list_plg').find('tbody tr.data'), function(tr) {
						let tr_realisasi = $(tr).next('tr.realisasi');
						let realisasi = $.map( $(tr_realisasi).find('table tbody tr'), function(tr_real) {
							let real = {
								'id' : $(tr_real).data('id'),
								'tonase' : numeral.unformat( $(tr_real).find('input.tonase').val() ),
								'ekor' : numeral.unformat( $(tr_real).find('input.ekor').val() ),
								'bb' : numeral.unformat( $(tr_real).find('input.bb').val() ),
								'harga' : numeral.unformat( $(tr_real).find('input.harga').val() ),
								'jenis_ayam' : $(tr_real).find('select.jenis_ayam').val(),
								'no_nota' : $(tr_real).find('input.no_nota').val()
							};

							return real;
						});
						
						let data = {
							'id_det_rpah' : $(tr).data('id'),
							'no_do' : $(tr).find('td.no_do').text(),
							'no_sj' : $(tr).find('td.no_sj').text(),
							'no_pelanggan' : $(tr).find('td.pelanggan').data('nomor'),
							'pelanggan' : $(tr).find('td.pelanggan').text(),
							'realisasi' : realisasi
							// 'tonase' : numeral.unformat( $(tr).find('input.tonase').val() ),
							// 'ekor' : numeral.unformat( $(tr).find('input.ekor').val() ),
							// 'bb' : numeral.unformat( $(tr).find('input.bb').val() ),
							// 'harga' : numeral.unformat( $(tr).find('input.harga').val() )
						};

						return data;
					});

					let data = {
						'id_real_sj' : id_real_sj,
						'id_unit' : id_unit,
						'unit' : unit,
						'tgl_panen' : tgl_panen,
						'noreg' : noreg,
						'ekor' : ekor,
						'kg' : kg,
						'bb' : bb,
						'tara' : tara,
						'netto_ekor' : netto_ekor,
						'netto_kg' : netto_kg,
						'netto_bb' : netto_bb,
						'detail' : data_detail
					};

					// console.log( data );
					real_sj.execute_edit( data );
				}
			});
		}
	}, // end - edit

	execute_edit: function(params) {
		$.ajax({
            url : 'transaksi/RealisasiSJ/edit',
            data : {
                'params' :  params
            },
            type : 'POST',
            dataType : 'JSON',
            beforeSend : function(){ },
            success : function(data){
            	if ( data.status == 1 ) {
            		bootbox.alert( data.message, function() {
            			let select = $('select.mitra');
            			real_sj.get_data( select );
            		});
            	} else {
            		bootbox.alert( data.message );
            	}
            },
        });
	}, // end - execute_edit

	delete: function(elm) {
        let id = $(elm).data('id');

        bootbox.confirm( 'Apakah anda yakin ingin meng-hapus data Realisasi SJ ?', function(result) {
            if ( result ) {
                $.ajax({
                    url : 'transaksi/RealisasiSJ/delete',
                    data : {
                        'id' : id
                    },
                    type : 'POST',
                    dataType : 'JSON',
                    beforeSend : function(){ showLoading(); },
                    success : function(data){
                        hideLoading();
                        if ( data.status == 1 ) {
                            bootbox.alert( data.message, function() {
                            	location.reload();
                            });
                        } else {
                            bootbox.alert( data.message );
                        };
                    },
                });
            }
        });
    }, // end - delete
};

real_sj.start_up();