var dn = [];
var cn = [];

var bakul = {
	start_up: function() {
		bakul.setting_up();
	}, // end - start_up

	filter_all(elm, sensitive = false) {
	    var _target_table = $(elm).data('table');

	    var _table = $('table.'+_target_table);
	    var _tbody = $(_table).find('tbody');
	    var _content, _target;

	    _tbody.find('tr').removeClass('hide');
	    _content = $(elm).val().toUpperCase().trim();

	    if (!empty(_content) && _content != '') {
	        $.map( $(_tbody).find('tr.search'), function(tr){

	            // CEK DI TR ADA ATAU TIDAK
	            var ada = 0;
	            $.map( $(tr).find('td'), function(td){
	                var td_val = $(td).html().trim();
	                if ( !sensitive ) {
	                    if (td_val.toUpperCase().indexOf(_content) > -1) {
	                        ada = 1;
	                    }
	                } else {
	                    if (td_val.toUpperCase() == _content) {
	                        ada = 1;
	                    }
	                }
	            });

	            if ( ada == 0 ) {
	                $(tr).addClass('hide');
	            } else {
	                $(tr).removeClass('hide');
	            };
	        });
	    }

	    bakul.hit_total_riwayat();
	}, // end - filter_all

	setting_up: function(resubmit = null) {
		$('.unit').select2({placeholder: 'Pilih Unit'}).on("select2:select", function (e) {
            var unit = $('.unit').select2().val();

            for (var i = 0; i < unit.length; i++) {
                if ( unit[i] == 'all' ) {
                    $('.unit').select2().val('all').trigger('change');

                    i = unit.length;
                }
            }

            $('.unit').next('span.select2').css('width', '100%');
        });
        $('.unit').next('span.select2').css('width', '100%');

        $('.pelanggan').selectpicker();
        $('.perusahaan').selectpicker();

		$('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal], [data-tipe=decimal3],[data-tipe=decimal4], [data-tipe=number]').each(function(){
		    $(this).priceFormat(Config[$(this).data('tipe')]);
		});

        $('#tglBayar, .datetimepicker').datetimepicker({
            locale: 'id',
            format: 'DD MMM Y'
        });

		var tgl = $('div#tglBayar input').attr('data-val');
		if ( !empty(tgl) ) {
			$("#tglBayar").data("DateTimePicker").date( new Date(tgl) );
		}
	}, // end - setting_up

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

		if ( vhref == 'action' ) {
			var v_id = $(elm).attr('data-id');
			var resubmit = $(elm).attr('data-resubmit');

			bakul.load_form(v_id, resubmit);
		};
	}, // end - changeTabActive

	load_form: function(v_id = null, resubmit = null) {
		var div_action = $('div#action');

		$.ajax({
			url : 'pembayaran/Bakul/load_form',
			data : {
				'id' :  v_id,
				'resubmit' : resubmit
			},
			type : 'GET',
			dataType : 'HTML',
			beforeSend : function(){
				showLoading();
			},
			success : function(html){
				$(div_action).html(html);
				bakul.setting_up(resubmit);

				cn = [];
				dn = [];

				hideLoading();
			},
		});
	}, // end - load_form

	modalPilihDN: function(elm) {
        let div = $('div#action');
        var pelanggan = $(div).find('select.pelanggan').val();
		var id = $(div).find('input#id').val();

        var params = {
            'pelanggan': pelanggan,
            'id': id
        };

        $.get('pembayaran/Bakul/modalPilihDN',{
            'params': params
        },function(data){
            var _options = {
                className : 'veryWidth',
                message : data,
                size : 'large',
            };
            bootbox.dialog(_options).bind('shown.bs.modal', function(){
                var modal_dialog = $(this).find('.modal-dialog');
                var modal_body = $(this).find('.modal-body');

                $(modal_dialog).css({'max-width' : '60%'});
                $(modal_dialog).css({'width' : '100%'});

                var modal_header = $(this).find('.modal-header');
                $(modal_header).css({'padding-top' : '0px'});

                $(modal_body).find('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal], [data-tipe=decimal3],[data-tipe=decimal4], [data-tipe=number]').each(function(){
                    $(this).priceFormat(Config[$(this).data('tipe')]);
                });

				if ( !empty(dn) && dn.length > 0 ) {
                    $.map( $(modal_body).find('table tbody tr.data'), function(tr) {
                        var id_dn = $(tr).find('input[type="checkbox"]').attr('data-id');

                        for (var i = 0; i < dn.length; i++) {
                            if ( id_dn == dn[i].id ) {
                                $(tr).find('input[type="checkbox"]').prop('checked', true);
                                $(tr).find('input.pakai').val(numeral.formatDec(dn[i].pakai));
                            }
                        }
                    });
                }
            });
        },'html');
    }, // end - modalPilihDN

    cekPakaiDN: function(elm) {
        var tr = $(elm).closest('tr');

        var saldo = numeral.unformat( $(tr).find('td.saldo').text() );
        var pakai = numeral.unformat( $(tr).find('input.pakai').val() );

        if ( pakai > saldo ) {
            bootbox.alert('DN yang anda masukkan melebihi saldo DN, harap cek kembali.', function () {
                $(tr).find('input.pakai').val( 0 );
            });
        }
    }, // end - cekPakaiDN

    pilihDN: function(elm) {
        var modal_dialog = $(elm).closest('.modal-dialog');
        var div = $(modal_dialog).find('.modal_dn');

        dn = [];
        var total_dn = 0;
        if ( $(div).find('[type=checkbox]:checked').length > 0 ) {
            var idx = 0;
            $.map( $(div).find('[type=checkbox]:checked'), function(check) {
                var tr = $(check).closest('tr');

                var saldo = parseFloat($(tr).find('td.saldo').attr('data-val'));
                var sisa_saldo = parseFloat($(tr).find('td.saldo').attr('data-val'));
                var pakai = numeral.unformat( $(tr).find('input.pakai').val() );

                dn[idx] = {
                    'id': $(check).attr('data-id'),
                    'saldo': saldo,
                    'sisa_saldo': sisa_saldo,
                    'pakai': pakai
                };  

                total_dn += pakai;

                idx++;
            });
        }

        $('.tot_dn').val(numeral.formatDec(total_dn));

        $(modal_dialog).find('.btn-danger').click();

        bakul.hit_total_uang();
    }, // end - pilihDN

    modalPilihCN: function(elm) {
        let div = $('div#action');
        var pelanggan = $(div).find('select.pelanggan').val();
        var id = $(div).find('input#id').val();

        var params = {
            'pelanggan': pelanggan,
            'id': id
        };

        $.get('pembayaran/Bakul/modalPilihCN',{
            'params': params
        },function(data){
            var _options = {
                className : 'veryWidth',
                message : data,
                size : 'large',
            };
            bootbox.dialog(_options).bind('shown.bs.modal', function(){
                var modal_dialog = $(this).find('.modal-dialog');
                var modal_body = $(this).find('.modal-body');

                $(modal_dialog).css({'max-width' : '60%'});
                $(modal_dialog).css({'width' : '100%'});

                var modal_header = $(this).find('.modal-header');
                $(modal_header).css({'padding-top' : '0px'});

                $(modal_body).find('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal], [data-tipe=decimal3],[data-tipe=decimal4], [data-tipe=number]').each(function(){
                    $(this).priceFormat(Config[$(this).data('tipe')]);
                });

				if ( !empty(cn) && cn.length > 0 ) {
                    $.map( $(modal_body).find('table tbody tr.data'), function(tr) {
                        var id_cn = $(tr).find('input[type="checkbox"]').attr('data-id');

                        for (var i = 0; i < cn.length; i++) {
                            if ( id_cn == cn[i].id ) {
                                $(tr).find('input[type="checkbox"]').prop('checked', true);
                                $(tr).find('input.pakai').val(numeral.formatDec(cn[i].pakai));
                            }
                        }
                    });
                }
            });
        },'html');
    }, // end - modalPilihCN

    cekPakaiCN: function(elm) {
        var tr = $(elm).closest('tr');

        var saldo = numeral.unformat( $(tr).find('td.saldo').text() );
        var pakai = numeral.unformat( $(tr).find('input.pakai').val() );

        if ( pakai > saldo ) {
            bootbox.alert('CN yang anda masukkan melebihi saldo CN, harap cek kembali.', function () {
                $(tr).find('input.pakai').val( 0 );
            });
        }
    }, // end - cekPakaiCN

    pilihCN: function(elm) {
        var modal_dialog = $(elm).closest('.modal-dialog');
        var div = $(modal_dialog).find('.modal_cn');

        cn = [];
        var total_cn = 0;
        if ( $(div).find('[type=checkbox]:checked').length > 0 ) {
            var idx = 0;
            $.map( $(div).find('[type=checkbox]:checked'), function(check) {
                var tr = $(check).closest('tr');

                var saldo = parseFloat($(tr).find('td.saldo').attr('data-val'));
                var sisa_saldo = parseFloat($(tr).find('td.saldo').attr('data-val'));
                var pakai = numeral.unformat( $(tr).find('input.pakai').val() );

                cn[idx] = {
                    'id': $(check).attr('data-id'),
                    'saldo': saldo,
                    'sisa_saldo': sisa_saldo,
                    'pakai': pakai
                };  

                total_cn += pakai;

                idx++;
            });
        }

        $('.tot_cn').val(numeral.formatDec(total_cn));

        $(div).find('.btn-danger').click();

        bakul.hit_total_uang();
    }, // end - pilihCN

	get_list_pembayaran: function() {
		var start_date = $('div#StartDate_PP input').val();
		var end_date = $('div#EndDate_PP input').val();

		$('#StartDate_PP').parent().removeClass('has-error')
		$('#EndDate_PP').parent().removeClass('has-error')
		if ( empty(start_date) || empty(end_date) ) {
			if ( empty(start_date) ) { $('#StartDate_PP').parent().addClass('has-error'); }
			if ( empty(end_date) ) { $('#EndDate_PP').parent().addClass('has-error'); }
			bootbox.alert('Harap lengkapi periode terlebih dahulu.');
		} else  {
			var params = {
				'start_date': dateSQL( $('#StartDate_PP').data('DateTimePicker').date() ),
				'end_date': dateSQL( $('#EndDate_PP').data('DateTimePicker').date() )
			};

			$.ajax({
	            url : 'pembayaran/Bakul/get_list_pembayaran',
	            data : {
	            	'params' : params
	            },
	            dataType : 'JSON',
	            type : 'POST',
	            beforeSend : function(){
	            	showLoading();
	            },
	            success : function(data){
	            	$('table.tbl_list_pembayaran').find('tbody').html(data.html);
	                hideLoading();
	            }
	        });
		}
	}, // end - get_list_pembayaran

	hit_total_riwayat: function() {
		var jml_transfer = 0;
		$.map( $('table.tbl_list_pembayaran').find('tbody tr.data:not(.hide)'), function(tr) {
			var _jml_transfer = numeral.unformat($(tr).find('td.jml_transfer').text());
			jml_transfer += _jml_transfer;
		});

		$('table.tbl_list_pembayaran').find('tbody td.grand_total b').text(numeral.formatDec(jml_transfer));
	}, // end - hit_total_riwayat

	get_list_do: function() {
		var id = ($('.btn-get-list-do').length > 0) ? $('.btn-get-list-do').data('id') : null;
		var pelanggan = $('select.pelanggan').val();
		var unit = $('select.unit').val();
		var tgl_bayar = $('div#tglBayar input').val();
		var perusahaan = $('select.perusahaan').val();
		var jenis_mitra = $('select.perusahaan option:selected').attr('data-jenismitra');

		$('select.pelanggan').parent().removeClass('has-error');
		$('select.unit').parent().removeClass('has-error');
		if ( empty(pelanggan) || empty(unit) ) {
			if ( empty(pelanggan) ) {
				$('select.pelanggan').parent().addClass('has-error');
			}
			if ( empty(unit) ) {
				$('select.unit').parent().addClass('has-error');
			}
			bootbox.alert('Harap isi unit dan pelanggan terlebih dahulu.');
		} else {
			$.ajax({
	            url : 'pembayaran/Bakul/get_list_do',
	            data : {
	            	'id' : id,
	            	'pelanggan' : pelanggan,
	            	'unit' : unit,
	            	'tgl_bayar' : dateSQL( $('#tglBayar').data('DateTimePicker').date() ),
	            	'perusahaan' : perusahaan,
	            	'jenis_mitra': jenis_mitra
	            },
	            dataType : 'JSON',
	            type : 'POST',
	            beforeSend : function(){
	            	showLoading();
	            },
	            success : function(data){
	            	$('table.tbl_list_do').find('tbody').html(data.html);
	            	bakul.setting_up();

	            	$('input.saldo').val(numeral.formatDec(data.saldo));

					if ( !empty(data.min_date) ) {
						var minDate = data.min_date+' 00:00:00';
						$('#tglBayar').data('DateTimePicker').minDate(moment(new Date(minDate)));
					}

	            	bakul.hit_total_uang();

	                hideLoading();
	            }
	        });
		}
	}, // end - get_list_do

	hit_total_uang: function() {
		var jml_trf = numeral.unformat($('input.jml_transfer').val());
		var saldo = numeral.unformat($('input.saldo').val());
		var nil_pajak = numeral.unformat($('input.nilai_pajak').val());
		var lebih_bayar_non_saldo = numeral.unformat($('input.lebih_bayar_non_saldo').val());

		var total_dn = ($('.tot_dn').length > 0) ? parseFloat(numeral.unformat($('.tot_dn').val())) : 0;
        var total_cn = ($('.tot_cn').length > 0) ? parseFloat(numeral.unformat($('.tot_cn').val())) : 0;

		var tot_nilai = 0;
		var tot_penyesuaian = 0;
		$.map( $('table.tbl_list_do tbody tr.data'), function(tr) {
			var nilai = parseFloat(numeral.unformat($(tr).find('td.nilai').text()));
			var penyesuaian = numeral.unformat($(tr).find('input.penyesuaian').val());

			tot_nilai += nilai;
			tot_penyesuaian += penyesuaian;
		});
		var tot_tagihan = (tot_nilai+total_dn)-total_cn;
		var total_uang = jml_trf + saldo + nil_pajak + lebih_bayar_non_saldo;
		var lebih_kurang = (total_uang + tot_penyesuaian)-tot_tagihan;
		
		$('input.total_penyesuaian').val(numeral.formatDec(tot_penyesuaian));
		$('input.total_uang').val(numeral.formatDec(total_uang));
		$('input.tot_nilai').val(numeral.formatDec(tot_nilai));
		$('input.tot_tagihan').val(numeral.formatDec(tot_tagihan));
		$('input.lebih_kurang').val(numeral.formatDec(lebih_kurang));

		if ( !empty(dn) && dn.length > 0 ) {
            for (var i = 0; i < dn.length; i++) {
                dn[i].sisa_saldo = dn[i].pakai;
            }
        }

		if ( !empty(cn) && cn.length > 0 ) {
            for (var i = 0; i < cn.length; i++) {
                cn[i].sisa_saldo = cn[i].pakai;
            }
        }

		var idx_cn = 0;
        var stts_cn = 1;
        var idx_dn = 0;
		$.map( $('table.tbl_list_do tbody tr.data'), function(tr) {
			var no_inv = $(tr).find('td.no_inv').text();
			var penyesuaian = numeral.unformat($(tr).find('input.penyesuaian').val());
			var nilai = parseFloat(numeral.unformat($(tr).find('td.nilai').text()));

			var _dn = 0;
            var tagihan = parseFloat(nilai);
            if ( total_dn > 0 ) {
                var prs = nilai/tot_nilai;
                var _dn = total_dn * (prs);

                $(tr).find('td.dn').attr('data-val', _dn);
                $(tr).find('td.dn').text(numeral.formatDec(_dn));

                tagihan += parseFloat(_dn);

                while ( !empty(dn[idx_dn]) && dn[idx_dn].sisa_saldo > 0 &&_dn > 0) {
                    if ( dn[idx_dn].sisa_saldo >= _dn ) {
                        dn[idx_dn].sisa_saldo -= _dn;

                        if ( typeof dn[idx_dn].detail == 'undefined' ) {
                            dn[idx_dn].detail = { [no_inv]: {
                                    'no_inv': no_inv,
                                    'jml_bayar': _dn,
                                    'id_dn': dn[idx_dn].id
                                }
                            };
                        } else {
                            dn[idx_dn].detail[no_inv] = {
                                'no_inv': no_inv,
                                'jml_bayar': _dn,
                                'id_dn': dn[idx_dn].id
                            };
                        }

                        _dn = 0;
                    } else {
                        _dn -= dn[idx_dn].sisa_saldo;
                        
                        if ( typeof dn[idx_dn].detail == 'undefined' ) {
                            dn[idx_dn].detail = { [no_inv]: {
                                    'no_inv': no_inv,
                                    'jml_bayar': dn[idx_dn].sisa_saldo,
                                    'id_dn': dn[idx_dn].id
                                }
                            };
                        } else {
                            dn[idx_dn].detail[no_inv] = {
                                'no_inv': no_inv,
                                'jml_bayar': dn[idx_dn].sisa_saldo,
                                'id_dn': dn[idx_dn].id
                            };
                        }
                        
                        dn[idx_dn].sisa_saldo -= dn[idx_dn].sisa_saldo;

                        idx_dn++;
                    }
                }
            } else {
				$(tr).find('td.dn').attr('data-val', 0);
                $(tr).find('td.dn').text(numeral.formatDec(0));
			}

			var _cn = 0;
			while ( tagihan > 0 ) {
				if ( !empty(cn[idx_cn]) && cn[idx_cn].sisa_saldo > 0 ) {
					if ( cn[idx_cn].sisa_saldo >= tagihan ) {
						cn[idx_cn].sisa_saldo -= tagihan;

						if ( typeof cn[idx_cn].detail == 'undefined' ) {
							cn[idx_cn].detail = { [no_inv]: {
									'no_inv': no_inv,
									'jml_bayar': tagihan,
									'id_cn': cn[idx_cn].id
								}
							};
						} else {
							cn[idx_cn].detail[no_inv] = {
								'no_inv': no_inv,
								'jml_bayar': tagihan,
								'id_cn': cn[idx_cn].id
							};
						}

						_cn += tagihan;

						tagihan = 0;
					} else {
						tagihan -= cn[idx_cn].sisa_saldo;
						
						if ( typeof cn[idx_cn].detail == 'undefined' ) {
							cn[idx_cn].detail = { [no_inv]: {
									'no_inv': no_inv,
									'jml_bayar': cn[idx_cn].sisa_saldo,
									'id_cn': cn[idx_cn].id
								}
							};
						} else {
							cn[idx_cn].detail[no_inv] = {
								'no_inv': no_inv,
								'jml_bayar': cn[idx_cn].sisa_saldo,
								'id_cn': cn[idx_cn].id
							};
						}
						
						_cn += cn[idx_cn].sisa_saldo;
						
						cn[idx_cn].sisa_saldo -= cn[idx_cn].sisa_saldo;

						stts_cn = 0;

						idx_cn++;
					}
				} else {
					stts_cn = 0;

					break;
				}
			}

			var sisa_tagihan = (tagihan - penyesuaian);
			var _transfer = 0;
			while ( sisa_tagihan > 0 && stts_cn == 0 ) {
                if ( sisa_tagihan > 0 ) {
                    if ( total_uang > 0 ) {
                        if ( total_uang >= sisa_tagihan ) {
                            total_uang -= sisa_tagihan;

                            _transfer += sisa_tagihan;

                            sisa_tagihan = 0;
                        } else {
                            sisa_tagihan -= total_uang;

                            _transfer += total_uang;

                            total_uang = 0;
                        }
                    } else {
                        break;
                    }
                }
            }

			$(tr).find('td.cn').attr('data-val', _cn);
            $(tr).find('td.cn').text(numeral.formatDec(_cn));

			$(tr).find('td.jml_bayar').attr('data-val', _transfer);
            $(tr).find('td.jml_bayar').text(numeral.formatDec(_transfer));

			$(tr).find('td.tagihan').text(numeral.formatDec(tagihan));
			$(tr).find('td.sisa_tagihan').text(numeral.formatDec(sisa_tagihan));

			bakul.cek_status_pembayaran( $(tr) );
		});
	}, // end - hit_total_uang

	cek_status_pembayaran: function(elm) {
		var tr = $(elm).closest('tr');

		var penyesuaian = parseFloat(numeral.unformat($(tr).find('td.penyesuaian input').val()));
		var sisa_tagihan = parseFloat(numeral.unformat($(tr).find('td.sisa_tagihan').text()));

		if ( (sisa_tagihan-penyesuaian) <= 0 ) {
			var span = '<span style="color: blue;"><b>LUNAS</b></span>';
			$(tr).find('td.status').html(span);
		} else {
			var span = '<span style="color: red;"><b>BELUM</b></span>';
			$(tr).find('td.status').html(span);
		}
	}, // end - cek_status_pembayaran

	save: function() {
		var div = $('div#action');

		var err = 0;
		$.map( $(div).find('[data-required=1]'), function(ipt) {
			if ( empty($(ipt).val()) ) {
				if ( $(ipt).hasClass('file_lampiran') ) {
					var label = $(ipt).closest('label');
					$(label).find('i').css({'color': '#a94442'});
				} else {
					$(ipt).parent().addClass('has-error');
				}
				err++;
			} else {
				if ( $(ipt).hasClass('file_lampiran') ) {
					var label = $(ipt).closest('label');
					$(label).find('i').css({'color': '#000000'});
				} else {
					$(ipt).parent().removeClass('has-error');
				}
			}
		});

		if ( err > 0 ) {
			bootbox.alert('Harap lengkapi data terlebih dahulu.');
		} else {
			var detail = $.map( $(div).find('table.tbl_list_do tbody tr.data'), function(tr) {
				var _detail = {
					'no_sj': $(tr).find('td.no_sj').text(),
					'no_inv': $(tr).find('td.no_inv').text(),
					'cn': numeral.unformat($(tr).find('td.cn').text()),
					'dn': numeral.unformat($(tr).find('td.dn').text()),
					'nilai': numeral.unformat($(tr).find('td.nilai').text()),
					'tagihan': numeral.unformat($(tr).find('td.tagihan').text()),
					'jml_bayar': $(tr).find('td.jml_bayar').data('bayar'),
					'penyesuaian': numeral.unformat($(tr).find('td.penyesuaian input').val()),
					'ket_penyesuaian': $(tr).find('textarea').val(),
					'sisa_tagihan': numeral.unformat($(tr).find('td.sisa_tagihan').text()),
					'status': $(tr).find('td.status').text()
				};

				return _detail;
			});

			if ( detail.length > 0 ) {
				bootbox.confirm('Apakah anda yakin ingin menyimpan data pembayaran ?', function(result) {
					if ( result ) {
						var data = {
							'tgl_bayar': dateSQL( $('#tglBayar').data('DateTimePicker').date() ),
							'pelanggan': $('select.pelanggan').val(),
							'urut_tf': ( $('select.urut_tf').length > 0 ) ? $('select.urut_tf').val() : null,
							'kode_umb': ( $('input.kode_umb').length > 0 ) ? $('input.kode_umb').val() : null,
							'jml_transfer': numeral.unformat($('input.jml_transfer').val()),
							'saldo': numeral.unformat($('input.saldo').val()),
							'nil_pajak': numeral.unformat($('input.nilai_pajak').val()),
							'lebih_bayar_non_saldo': numeral.unformat($('input.lebih_bayar_non_saldo').val()),
							'total_uang': numeral.unformat($('input.total_uang').val()),
							'total_penyesuaian': numeral.unformat($('input.total_penyesuaian').val()),
							'total_cn': numeral.unformat($('input.tot_cn').val()),
							'total_dn': numeral.unformat($('input.tot_dn').val()),
							'total_tagihan': numeral.unformat($('input.tot_tagihan').val()),
							'lebih_kurang': numeral.unformat($('input.lebih_kurang').val()),
							'perusahaan': $('select.perusahaan').val(),
							'cn': cn,
							'dn': dn,
							'detail': detail
						};

						var formData = new FormData();

						if ( !empty($('.file_lampiran').val()) ) {
							var _file = $('.file_lampiran').get(0).files[0];
							formData.append('files', _file);
						}
						formData.append('data', JSON.stringify(data));

						bakul.execute_save(formData);
					}
				});
			} else {
				bootbox.alert('Tidak ada data DO yang akan anda bayar.');
			}
		}
	}, // end - save

	execute_save: function(formData) {
		$.ajax({
			url :'pembayaran/Bakul/save',
			type : 'post',
			data : formData,
			beforeSend : function(){
				showLoading();
			},
			success : function(data){
				hideLoading();
                if ( data.status == 1 ) {
                    bootbox.alert(data.message, function(){
                    	var start_date = $('div#StartDate_PP input').val();
						var end_date = $('div#EndDate_PP input').val();
						if ( !empty(start_date) && !empty(end_date) ) {
							bakul.get_list_pembayaran();
						}
                    	bakul.load_form(data.content.id);

						cn = [];
						dn = [];
                    });
                } else {
                    bootbox.alert(data.message);
                }
			},
			contentType : false,
			processData : false,
		});
	}, // end - execute_save

	edit: function(elm) {
		var div = $('div#action');

		var err = 0;
		$.map( $(div).find('[data-required=1]'), function(ipt) {
			if ( empty($(ipt).val()) ) {
				if ( $(ipt).hasClass('file_lampiran') ) {
					var label = $(ipt).closest('label');
					$(label).find('i').css({'color': '#a94442'});
				} else {
					$(ipt).parent().addClass('has-error');
				}
				err++;
			} else {
				if ( $(ipt).hasClass('file_lampiran') ) {
					var label = $(ipt).closest('label');
					$(label).find('i').css({'color': '#000000'});
				} else {
					$(ipt).parent().removeClass('has-error');
				}
			}
		});

		if ( err > 0 ) {
			bootbox.alert('Harap lengkapi data terlebih dahulu.');
		} else {
			var detail = $.map( $(div).find('table.tbl_list_do tbody tr.data'), function(tr) {
				var _detail = {
					'id': $(tr).data('id'),
					'total': numeral.unformat($(tr).find('td.total').text()),
					'jml_bayar': numeral.unformat($(tr).find('td.jml_bayar').data('bayar')),
					'penyesuaian': numeral.unformat($(tr).find('td.penyesuaian input').val()),
					'ket_penyesuaian': $(tr).find('textarea').val(),
					'status': $(tr).find('td.status').text()
				};

				return _detail;
			});

			if ( detail.length > 0 ) {
				bootbox.confirm('Apakah anda yakin ingin meng-edit data pembayaran ?', function(result) {
					if ( result ) {
						var data = {
							'id': $(elm).data('id'),
							'tgl_bayar': dateSQL( $('#tglBayar').data('DateTimePicker').date() ),
							'pelanggan': $('select.pelanggan').val(),
							'jml_transfer': numeral.unformat($('input.jml_transfer').val()),
							'saldo': numeral.unformat($('input.saldo').val()),
							'nil_pajak': numeral.unformat($('input.nilai_pajak').val()),
							'lebih_bayar_non_saldo': numeral.unformat($('input.lebih_bayar_non_saldo').val()),
							'total_uang': numeral.unformat($('input.total').val()),
							'total_penyesuaian': numeral.unformat($('input.total_penyesuaian').val()),
							'total_bayar': numeral.unformat($('input.jml_bayar').val()),
							'lebih_kurang': numeral.unformat($('input.lebih_kurang').val()),
							'perusahaan': $('select.perusahaan').val(),
							'detail': detail
						};

						var formData = new FormData();

						var _file = $('.file_lampiran').get(0).files[0];
						if ( !empty(_file) ) {
							formData.append('files', _file);
						}
						formData.append('data', JSON.stringify(data));

						bakul.execute_edit(formData);
					}
				});
			} else {
				bootbox.alert('Tidak ada data DO yang akan anda bayar.');
			}
		}
	}, // end - edit

	execute_edit: function(formData) {
		$.ajax({
			url :'pembayaran/Bakul/edit',
			type : 'post',
			data : formData,
			beforeSend : function(){
				showLoading();
			},
			success : function(data){
				hideLoading();
                if ( data.status == 1 ) {
                    bootbox.alert(data.message, function(){
                    	var start_date = $('div#StartDate_PP input').val();
						var end_date = $('div#EndDate_PP input').val();
						if ( !empty(start_date) && !empty(end_date) ) {
							bakul.get_list_pembayaran();
						}
                    	bakul.load_form();
                    });
                } else {
                    bootbox.alert(data.message);
                }
			},
			contentType : false,
			processData : false,
		});
	}, // end - execute_edit

	delete: function(elm) {
		var id = $(elm).data('id');
		bootbox.confirm('Apakah anda yakin ingin meng-hapus data pembayaran ?', function(result) {
			if ( result ) {
				$.ajax({
					url :'pembayaran/Bakul/delete',
					data : {
						'params': id
					},
					dataType : 'JSON',
	            	type : 'POST',
					beforeSend : function(){
						showLoading();
					},
					success : function(data){
						hideLoading();
		                if ( data.status == 1 ) {
		                    bootbox.alert(data.message, function(){
		                    	var start_date = $('div#StartDate_PP input').val();
								var end_date = $('div#EndDate_PP input').val();
								if ( !empty(start_date) && !empty(end_date) ) {
									bakul.get_list_pembayaran();
								}
		                    	bakul.load_form();
		                    });
		                } else {
		                    bootbox.alert(data.message);
		                }
					}
				});
			}
		});
	}, // end - delete
};

bakul.start_up();