var sld = {
	startUp: function () {
		sld.settingUp();
	}, // end - startUp

	settingUp: function () {
		$('#action select.perusahaan').select2().on('select2:select', function(e) {
            var kode = e.params.data.element.value;
            var aktif = e.params.data.element.dataset.aktif;

            sld.cekPerusahaan( kode, aktif );
        });
		$('select.supplier').select2();

		$('#riwayat select.perusahaan').select2({placeholder: '-- Pilih Perusahaan --'});

		$('#Tanggal, #StartDate, #EndDate').datetimepicker({
            locale: 'id',
            format: 'DD MMM Y'
        });

        var tgl = $('#Tanggal').find('input').data('tgl');
        if ( !empty(tgl) ) {
        	$('#Tanggal').data('DateTimePicker').date( moment(new Date(tgl)) );
        }

        $('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal], [data-tipe=decimal3],[data-tipe=decimal4], [data-tipe=number]').each(function(){
			$(this).priceFormat(Config[$(this).data('tipe')]);
		});

		$('input.hutang').blur(function() {
			sld.hitHutang();
		});

		$('input.lr').blur(function() {
			sld.hitLr();
		});
	}, // end - startUp

	changeTabActive: function(elm) {
        var href = $(elm).data('href');
        var edit = $(elm).data('edit');
        // change tab-menu
        $('.nav-tabs').find('a').removeClass('active');
        $('.nav-tabs').find('a').removeClass('show');
        $('.nav-tabs').find('li a[data-tab='+href+']').addClass('show');
        $('.nav-tabs').find('li a[data-tab='+href+']').addClass('active');

        // change tab-content
        $('.tab-pane').removeClass('show');
        $('.tab-pane').removeClass('active');
        $('div#'+href).addClass('show');
        $('div#'+href).addClass('active');

        var id = $(elm).attr('data-id');

        sld.loadForm(id, edit, href);
    }, // end - changeTabActive

    addRow: function(elm) {
    	var tr = $(elm).closest('tr');
    	var tbody = $(tr).closest('tbody');

    	$(tr).find('select.supplier').select2('destroy')
               .removeAttr('data-live-search')
               .removeAttr('data-select2-id')
               .removeAttr('aria-hidden')
               .removeAttr('tabindex');
        $(tr).find('select.supplier option').removeAttr('data-select2-id');

    	var tr_clone = $(tr).clone();
    	tr_clone.find('input, select').val('');

    	$(tr_clone).find('input.hutang').blur(function() {
			sld.hitHutang();
		});

    	$(tbody).append( $(tr_clone) );

    	$.map( $(tbody).find('tr'), function(tr) {
            $(tr).find('select.supplier').select2();
        });

        $('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal], [data-tipe=decimal3],[data-tipe=decimal4], [data-tipe=number]').each(function(){
			$(this).priceFormat(Config[$(this).data('tipe')]);
		});
    }, // end - addRow

    removeRow: function(elm) {
    	var tr = $(elm).closest('tr');
    	var tbody = $(tr).closest('tbody');

    	if ( $(tbody).find('tr').length > 1 ) {
    		$(tr).remove();
    	}
    }, // end - removeRow

    hitHutang: function() {
    	var dcontent = $('#action');

    	var tot_hutang = 0;
    	$.map( $(dcontent).find('input.hutang'), function (ipt) {
    		var nilai = numeral.unformat( $(ipt).val() );

    		tot_hutang += nilai;
    	});

    	$(dcontent).find('input.tot_hutang').val( numeral.formatDec(tot_hutang) );
    }, // end - hitHutang

    hitLr: function() {
    	var dcontent = $('#action');

    	var tot_lr = 0;
    	$.map( $(dcontent).find('input.lr'), function (ipt) {
    		var nilai = numeral.unformat( $(ipt).val() );

    		tot_lr += nilai;
    	});

    	$(dcontent).find('input.tot_lr').val( numeral.formatDec(tot_lr) );
    }, // end - hitLr

    cekPerusahaan: function(perusahaan, aktif) {
        var dcontent = $('#action');

        $(dcontent).find('div.contain input').val('');
        // $(dcontent).find('.supplier').select2().val('');
        // $(dcontent).find('.supplier').select2().trigger('change');

        if ( aktif == 0 ) {
            $(dcontent).find('.btn-ambil-data').addClass('hide');
        } else {
            $(dcontent).find('.btn-ambil-data').removeClass('hide');
        }
    }, // end - cekPerusahaan

    getData: function() {
        var dcontent = $('#action');
        var dheader = $(dcontent).find('div.header');
        var dcontain = $(dcontent).find('div.contain');

        var err = 0;
        $.map( $(dheader).find('[data-required=1]'), function(ipt) {
            if ( empty( $(ipt).val() ) ) {
                $(ipt).parent().addClass('has-error');
                err++;
            } else {
                $(ipt).parent().removeClass('has-error');
            }
        });

        if ( err > 0 ) {
            bootbox.alert('Harap lengkapi data tanggal dan perusahaan terlebih dahulu.');
        } else {
            var params = {
                'tanggal': dateSQL( $(dcontent).find('#Tanggal').data('DateTimePicker').date() ),
                'perusahaan': $(dcontent).find('.perusahaan').select2('val'),
            };

            $.ajax({
                url : 'accounting/SaldoHarian/getData',
                data : {
                    'params' :  params
                },
                type : 'POST',
                dataType : 'JSON',
                beforeSend : function(){ App.showLoaderInContent( $(dcontain) ) },
                success : function( data ){
                    if ( data.status == 1 ) {
                        App.hideLoaderInContent( $(dcontain), data.content.html );

                        sld.settingUp();
                    } else {
                        App.hideLoaderInContent( $(dcontain), null );

                        bootbox.alert( data.message );
                    }
                },
            });
        }
    }, // end - getData

    loadForm: function(id = null, edit = null, href = null) {
        var dcontent = $('div#'+href);

        var params = {
            'id': id
        };

        $.ajax({
            url : 'accounting/SaldoHarian/loadForm',
            data : {
                'params' :  params,
                'edit' :  edit
            },
            type : 'GET',
            dataType : 'HTML',
            beforeSend : function(){ App.showLoaderInContent(dcontent); },
            success : function(html){
                App.hideLoaderInContent(dcontent, html);

                sld.settingUp();
            },
        });
    }, // end - loadForm

    getLists: function() {
    	var dcontent = $('#riwayat');

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
    		var params = {
    			'start_date': dateSQL( $(dcontent).find('#StartDate').data('DateTimePicker').date() ),
    			'end_date': dateSQL( $(dcontent).find('#EndDate').data('DateTimePicker').date() ),
    			'perusahaan': $(dcontent).find('select.perusahaan').select2('val')
    		};

    		$.ajax({
	            url : 'accounting/SaldoHarian/getLists',
	            data : {
	                'params' :  params
	            },
	            type : 'GET',
	            dataType : 'HTML',
	            beforeSend : function(){ showLoading(); },
	            success : function(html){
	            	$(dcontent).find('table.tbl_riwayat tbody').html( html );

	            	hideLoading();
	            },
	        });
    	}
    }, // end - getLists

    save: function() {
    	var dcontent = $('#action');

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
    		bootbox.confirm('Apakah anda yakin ingin menyimpan data saldo harian ?', function (result) {
    			if ( result ) {
    				var hutang = $.map( $(dcontent).find('table.list_data_hutang tbody tr'), function (tr) {
    					var _d_hutang = {
    						'supplier': $(tr).find('select.supplier').select2('val'),
    						'nilai': numeral.unformat( $(tr).find('input.nilai_hutang').val() ),
    						'hari': numeral.unformat( $(tr).find('input.nota_terlama').val() )
    					};

    					return _d_hutang;
    				});

    				var params = {
    					'tanggal': dateSQL( $(dcontent).find('#Tanggal').data('DateTimePicker').date() ),
    					'perusahaan': $(dcontent).find('.perusahaan').select2('val'),
    					'saldo_bank': numeral.unformat( $(dcontent).find('.saldo_bank').val() ),
						'tot_transfer': numeral.unformat( $(dcontent).find('.tot_transfer').val() ),
						'hutang': hutang,
						'hut_bca': numeral.unformat( $(dcontent).find('.hut_bca').val() ),
						'tot_hutang': numeral.unformat( $(dcontent).find('.tot_hutang').val() ),
						'lr_sebelumnya': numeral.unformat( $(dcontent).find('.lr_sebelumnya').val() ),
						'lr_hari_ini': numeral.unformat( $(dcontent).find('.lr_hari_ini').val() ),
						'cn_pakan': numeral.unformat( $(dcontent).find('.cn_pakan').val() ),
						'cn_doc': numeral.unformat( $(dcontent).find('.cn_doc').val() ),
						'tot_lr': numeral.unformat( $(dcontent).find('.tot_lr').val() ),
						'rhpp_selesai': numeral.unformat( $(dcontent).find('.rhpp_selesai').val() ),
						'rhpp_selesai_box': numeral.unformat( $(dcontent).find('.rhpp_selesai_box').val() ),
						'laba_per_ekor': numeral.unformat( $(dcontent).find('.laba_per_ekor').val() ),
						'harga_rata_ayam': numeral.unformat( $(dcontent).find('.harga_rata_ayam').val() ),
						'harga_rata_doc': numeral.unformat( $(dcontent).find('.harga_rata_doc').val() )
    				};

    				$.ajax({
			            url : 'accounting/SaldoHarian/save',
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
			                		sld.loadForm(data.content.id, null, 'action');
			                	});
			                } else {
			                	bootbox.alert( data.message );
			                }
			            },
			        });
    			}
    		});
    	}
    }, // end - save

    edit: function(elm) {
    	var dcontent = $('#action');

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
    		bootbox.confirm('Apakah anda yakin ingin meng-ubah data saldo harian ?', function (result) {
    			if ( result ) {
    				var hutang = $.map( $(dcontent).find('table.list_data_hutang tbody tr'), function (tr) {
    					var _d_hutang = {
    						'supplier': $(tr).find('select.supplier').select2('val'),
    						'nilai': numeral.unformat( $(tr).find('input.nilai_hutang').val() ),
    						'hari': numeral.unformat( $(tr).find('input.nota_terlama').val() )
    					};

    					return _d_hutang;
    				});

    				var params = {
    					'id': $(elm).attr('data-id'),
    					'tanggal': dateSQL( $(dcontent).find('#Tanggal').data('DateTimePicker').date() ),
    					'perusahaan': $(dcontent).find('.perusahaan').select2('val'),
    					'saldo_bank': numeral.unformat( $(dcontent).find('.saldo_bank').val() ),
						'tot_transfer': numeral.unformat( $(dcontent).find('.tot_transfer').val() ),
						'hutang': hutang,
						'hut_bca': numeral.unformat( $(dcontent).find('.hut_bca').val() ),
						'tot_hutang': numeral.unformat( $(dcontent).find('.tot_hutang').val() ),
						'lr_sebelumnya': numeral.unformat( $(dcontent).find('.lr_sebelumnya').val() ),
						'lr_hari_ini': numeral.unformat( $(dcontent).find('.lr_hari_ini').val() ),
						'cn_pakan': numeral.unformat( $(dcontent).find('.cn_pakan').val() ),
						'cn_doc': numeral.unformat( $(dcontent).find('.cn_doc').val() ),
						'tot_lr': numeral.unformat( $(dcontent).find('.tot_lr').val() ),
						'rhpp_selesai': numeral.unformat( $(dcontent).find('.rhpp_selesai').val() ),
						'rhpp_selesai_box': numeral.unformat( $(dcontent).find('.rhpp_selesai_box').val() ),
						'laba_per_ekor': numeral.unformat( $(dcontent).find('.laba_per_ekor').val() ),
						'harga_rata_ayam': numeral.unformat( $(dcontent).find('.harga_rata_ayam').val() ),
						'harga_rata_doc': numeral.unformat( $(dcontent).find('.harga_rata_doc').val() )
    				};

    				$.ajax({
			            url : 'accounting/SaldoHarian/edit',
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
			                		sld.loadForm(data.content.id, null, 'action');
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
    	var dcontent = $('#action');

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
    		bootbox.confirm('Apakah anda yakin ingin meng-hapus data saldo harian ?', function (result) {
    			if ( result ) {
    				var params = {
    					'id': $(elm).attr('data-id')
    				};

    				$.ajax({
			            url : 'accounting/SaldoHarian/delete',
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
			                		sld.loadForm(null, null, 'action');
			                	});
			                } else {
			                	bootbox.alert( data.message );
			                }
			            },
			        });
    			}
    		});
    	}
    }, // end - delete
};

sld.startUp();