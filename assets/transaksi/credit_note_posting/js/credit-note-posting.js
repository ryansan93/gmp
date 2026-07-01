var cn = {
	startUp: function() {
        cn.settingUp();
	}, // end - startUp

    setSelect2Cn: function(elm) {
        $(elm).select2({
            ajax: {
                url: 'transaksi/CreditNotePosting/getCn',
                dataType: 'json',
                type: 'GET',
                data: function (params, jenis) {
                    var query = {
                        search: params.term,
                        type: 'item_search',
						jenis_cn: $('div#action').find('select.jenis_cn').select2().val(),
						id: $('div#action').find('select.cn').attr('data-kode')
                    }
                    return query;
                },
                processResults: function (data) {
                    return {
                        results: !empty(data) ? data : []
                    };
                },
                error: function (jqXHR, status, error) {
                    return { results: [] };
                }
            },
            cache: true,
            placeholder: 'Search for a CN ...',
            escapeMarkup: function (markup) { return markup; },
            templateResult: function (data) {
                var markup = "<option value='"+data.id+"'>"+data.text+"</option>";
                return markup;
            },
            templateSelection: function (data, container) {
                var dataset = null;
                if ( typeof data.element !== 'undefined' ) {
                    if ( typeof data.element.dataset !== 'undefined' ) {
                        dataset = data.element.dataset;
                    }
                }

                var tot_cn = !empty(data.tot_cn) ? data.tot_cn : (!empty(dataset) ? dataset.totcn : null);

                $(data.element).attr('data-totcn', data.tot_cn);
                if ( !empty(data.supplier) ) {
                    $(data.element).attr('data-supplier', data.supplier);
                }

                $('.nilai_cn').val(numeral.formatDec(tot_cn));

                return data.text;
            },
        });
    }, // end - setSelect2Cn

    /* ============ MODAL PILIH SJ ============ */

    openModalSj: function() {
        var div = $('#action');
        var jenis_cn = $(div).find('.jenis_cn').select2().val();
        var no_cn = $(div).find('.cn').select2().val();

        if ( empty(jenis_cn) ) {
            bootbox.alert('Harap pilih Jenis CN terlebih dahulu.');
            return;
        }

        if ( empty(no_cn) ) {
            bootbox.alert('Harap pilih No. CN terlebih dahulu.');
            return;
        }

        $('#modalSj').find('.sj_search').val('');
        $('#modalSj').find('.sj_check_all').prop('checked', false);
        $('#modalSj').modal('show');

        cn.loadSjModal('');
    }, // end - openModalSj

    loadSjModal: function(search) {
        var div = $('#action');
        var body = $('#modalSj').find('.sj_modal_body');

        var params = {
            search: search,
            type: 'item_search',
            jenis_cn: $(div).find('.jenis_cn').select2().val(),
            id: $(div).find('.cn').attr('data-kode'),
            supplier: $(div).find('.cn option:selected').attr('data-supplier')
        };

        body.html('<tr><td colspan="4" class="text-center">Memuat data ...</td></tr>');

        $.ajax({
            url: 'transaksi/CreditNotePosting/getSj',
            data: params,
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                cn.renderSjModal(data);
            },
            error: function() {
                body.html('<tr><td colspan="4" class="text-center">Gagal memuat data.</td></tr>');
            }
        });
    }, // end - loadSjModal

    renderSjModal: function(data) {
        var body = $('#modalSj').find('.sj_modal_body');

        if ( empty(data) || data.length == 0 ) {
            body.html('<tr><td colspan="4" class="text-center">Tidak ada SJ yang belum lunas.</td></tr>');
            return;
        }

        // kumpulkan nomor SJ yg sudah ada di tabel detail (cegah dobel)
        var selected = {};
        $('#action').find('.detail_sj tr.head .no_sj_val').each(function() {
            selected[ $(this).val() ] = true;
        });

        var html = '';
        $.each(data, function(i, d) {
            var already = (typeof selected[d.id] !== 'undefined');

            html += '<tr class="sj-row"'
                  + ' data-id="' + d.id + '"'
                  + ' data-text="' + d.text + '"'
                  + ' data-tagihan="' + d.tagihan + '"'
                  + ' data-sisa="' + d.sisa_tagihan + '">'
                  + '<td class="text-center">'
                  + '<input type="checkbox" class="sj_check"' + (already ? ' disabled' : '') + '>'
                  + '</td>'
                  + '<td>' + d.text + (already ? ' <span class="text-muted">(sudah dipilih)</span>' : '') + '</td>'
                  + '<td class="text-right">' + numeral.formatDec(d.tagihan) + '</td>'
                  + '<td class="text-right">' + numeral.formatDec(d.sisa_tagihan) + '</td>'
                  + '</tr>';
        });

        body.html(html);
    }, // end - renderSjModal

    toggleAllSj: function(elm) {
        var checked = $(elm).prop('checked');
        $('#modalSj').find('.sj_modal_body tr:visible .sj_check:not(:disabled)').prop('checked', checked);
    }, // end - toggleAllSj

    _sjSearchTimer: null,

    filterSjModal: function(elm) {
        var val = $(elm).val();

        // cari di server (debounce) supaya tidak memuat semua SJ sekaligus
        clearTimeout(cn._sjSearchTimer);
        cn._sjSearchTimer = setTimeout(function() {
            cn.loadSjModal(val);
        }, 400);
    }, // end - filterSjModal

    pilihSj: function() {
        var rows = $('#modalSj').find('.sj_modal_body tr.sj-row');
        var added = 0;

        $.each(rows, function(i, tr) {
            var chk = $(tr).find('.sj_check');
            if ( chk.is(':checked') && !chk.is(':disabled') ) {
                cn.appendSjRow(
                    $(tr).data('id'),
                    $(tr).data('text'),
                    $(tr).data('tagihan'),
                    $(tr).data('sisa'),
                    ''
                );
                added++;
            }
        });

        if ( added == 0 ) {
            bootbox.alert('Belum ada SJ yang dicentang.');
            return;
        }

        cn.hitTotalPakai();
        $('#modalSj').modal('hide');
    }, // end - pilihSj

    appendSjRow: function(id, text, tagihan, sisa, pakai) {
        var tbody = $('#action').find('.detail_sj');

        tbody.find('tr.empty-row').remove();

        var row = '<tr class="head">'
                + '<td>'
                + '<input type="hidden" class="no_sj_val" value="' + id + '">'
                + '<span class="no_sj_text">' + text + '</span>'
                + '</td>'
                + '<td class="text-right tagihan">' + numeral.formatDec(tagihan) + '</td>'
                + '<td class="text-right sisa">' + numeral.formatDec(sisa) + '</td>'
                + '<td>'
                + '<div class="col-xs-2 no-padding" style="padding-right: 5px;">'
                + '<button type="button" class="col-xs-12 btn btn-default" onclick="cn.samakanSisaTagihan(this)"><i class="fa fa-arrow-right"></i></button>'
                + '</div>'
                + '<div class="col-xs-10 no-padding" style="padding-left: 5px;">'
                + '<input type="text" class="col-xs-12 form-control pakai text-right" data-tipe="decimal" data-required="1" onblur="cn.hitTotalPakai()" value="' + (empty(pakai) ? '' : numeral.formatDec(pakai)) + '">'
                + '</div>'
                + '</td>'
                + '<td>'
                + '<button type="button" class="col-xs-12 btn btn-danger" onclick="cn.removeRow(this)"><i class="fa fa-minus"></i></button>'
                + '</td>'
                + '</tr>';

        var $row = $(row);
        tbody.append($row);

        $row.find('[data-tipe=decimal]').each(function(){
            $(this).priceFormat(Config[$(this).data('tipe')]);
        });
    }, // end - appendSjRow

    samakanSisaTagihan: function(elm) {
        var _tr = $(elm).closest('tr');

        var sisa_tagihan = numeral.unformat($(_tr).find('td.sisa').text());

        // sisa CN yang belum dipakai (utk baris ini) = nilai CN - total pakai di baris LAIN
        var nilai_cn = numeral.unformat($('#action').find('.nilai_cn').val());

        var pakai_lain = 0;
        $.map( $('#action').find('.detail_sj tr.head'), function(tr) {
            if ( tr !== _tr.get(0) ) {
                pakai_lain += numeral.unformat($(tr).find('input.pakai').val());
            }
        });

        var sisa_cn = nilai_cn - pakai_lain;
        if ( sisa_cn < 0 ) { sisa_cn = 0; }

        // jika sisa tagihan melebihi sisa CN yang belum dipakai, batasi ke sisa CN
        var pakai = ( sisa_tagihan > sisa_cn ) ? sisa_cn : sisa_tagihan;

        $(_tr).find('.pakai').val( numeral.formatDec(pakai) );

        cn.hitTotalPakai();
    }, // end - samakanSisaTagihan

    hitTotalPakai: function() {
        var total_pakai = 0;
        $.map( $('div#action').find('.detail_sj tr.head'), function(tr) {
            var pakai = numeral.unformat($(tr).find('input.pakai').val());

            total_pakai += pakai;
        });

        $('div#action').find('input.pakai_cn').val( numeral.formatDec(total_pakai) );
    }, // end - hitTotalPakai

    removeRow: function (elm) {
        var tr = $(elm).closest('tr.head');
        var tbody = $(tr).closest('tbody');

        $(tr).remove();

        if ( $(tbody).find('tr.head').length == 0 ) {
            $(tbody).append('<tr class="empty-row"><td colspan="5" class="text-center">Belum ada SJ dipilih. Klik "Pilih No. SJ".</td></tr>');
        }

        cn.hitTotalPakai();
    }, // end - removeRow

    clearDetail: function() {
        var tbody = $('#action').find('.detail_sj');
        tbody.html('<tr class="empty-row"><td colspan="5" class="text-center">Belum ada SJ dipilih. Klik "Pilih No. SJ".</td></tr>');
        $('#action').find('.pakai_cn').val('');
    }, // end - clearDetail

    // reset dropdown No. CN (destroy + init ulang) supaya query & cache mengikuti jenis baru
    resetCn: function() {
        var $cn = $('#action').find('.cn');

        if ( $cn.hasClass('select2-hidden-accessible') ) {
            $cn.select2('destroy');
        }
        $cn.empty().val(null);

        cn.setSelect2Cn( $cn );

        // rebind: ganti CN oleh user -> reset SJ terpilih
        $cn.off('select2:select').on('select2:select', function() {
            cn.clearDetail();
        });

        $('#action').find('.nilai_cn').val('');
        cn.clearDetail();
    }, // end - resetCn

    settingUp: function() {
        $('.date').datetimepicker({
            locale: 'id',
            format: 'DD MMM Y',
            useCurrent: true,
        });

        $.map( $('.date'), function(div) {
            var tgl = $(div).find('input').attr('data-tgl');

            if ( !empty(tgl) ) {
                $(div).data('DateTimePicker').date(new Date(tgl));
            }
        });

		$('#riwayat').find('.jenis_cn').select2().select2();
		$('#action').find('.jenis_cn').select2().select2().on('select2:select', function() {
            // ganti jenis -> reset dropdown No. CN + detail SJ agar tidak tercampur
            cn.resetCn();
        });

        $('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal], [data-tipe=decimal3],[data-tipe=decimal4], [data-tipe=number]').each(function(){
            $(this).priceFormat(Config[$(this).data('tipe')]);
        });

        $(document).ready(function () {
            cn.setSelect2Cn( $('.cn') );

            // ganti CN oleh user -> reset SJ terpilih (supplier bisa berbeda)
            $('#action').find('.cn').on('select2:select', function() {
                cn.clearDetail();
            });
        });

        App.setTutupBulan();
    }, // end - settingUp

    getLists: function() {
        var div = $('#riwayat');
		var dcontent = $(div).find('.tbl_riwayat tbody');

        var err = 0;
        $.map( $(div).find('[data-required=1]'), function(ipt) {
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
                'start_date': dateSQL( $(div).find('#StartDate').data('DateTimePicker').date() ),
                'end_date': dateSQL( $(div).find('#EndDate').data('DateTimePicker').date() ),
                'jenis_cn': $(div).find('.jenis_cn').select2().val()
            };

            $.ajax({
                url: 'transaksi/CreditNotePosting/getLists',
                data: { 'params': params },
                type: 'GET',
                dataType: 'HTML',
                beforeSend: function(){ App.showLoaderInContent( $(dcontent) ) },
                success: function(html){
					App.hideLoaderInContent( $(dcontent), html );
                }
            });
        }
    }, // end - getLists

	changeTabActive: function(elm) {
		var id = $(elm).data('kode');
		var edit = $(elm).data('edit');
		var href = $(elm).data('href');

		$('a.nav-link').removeClass('active');
		$('div.tab-pane').removeClass('active');
		$('div.tab-pane').removeClass('show');

		$('a[data-tab='+href+']').addClass('active');
		$('div.tab-content').find('div#'+href).addClass('show');
		$('div.tab-content').find('div#'+href).addClass('active');

		cn.loadForm(id, edit, href);
	}, // end - changeTabActive

	loadForm: function(id, edit, href) {
		var params = {
			'id': id,
			'edit': edit
		};

		$.ajax({
            url: 'transaksi/CreditNotePosting/loadForm',
            data: { 'params': params },
            type: 'GET',
            dataType: 'HTML',
            beforeSend: function(){ showLoading() },
            success: function(html){
                $('div#'+href).html( html );

                cn.settingUp();

                hideLoading();
            }
        });
	}, // end - loadForm

	save: function() {
		var div = $('#action');

		var err = 0;
		$.map( $(div).find('[data-required="1"]'), function(ipt) {
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
            var tot_pakai_cn = numeral.unformat($(div).find('.pakai_cn').val());
            var tot_cn = numeral.unformat($(div).find('.nilai_cn').val());

            var detail = cn.getDetail(div);

            if ( detail.length == 0 ) {
                bootbox.alert('Harap pilih minimal satu No. SJ.');
            } else if ( tot_pakai_cn > tot_cn ) {
                bootbox.alert('Total pemakaian CN yang anda post melebihi nilai CN. Harap cek kembali data yang anda masukkan.');
            } else {
                bootbox.confirm('Apakah anda yakin ingin menyimpan data ?', function (result) {
                    if ( result ) {
                        var params = {
                            'tanggal': dateSQL( $(div).find('#Tanggal').data('DateTimePicker').date() ),
                            'jenis_cn': $(div).find('.jenis_cn').select2('val'),
                            'no_cn': $(div).find('.cn').select2('val'),
                            'tot_pakai': numeral.unformat($(div).find('.pakai_cn').val()),
                            'detail': detail
                        };

                        $.ajax({
                            url : 'transaksi/CreditNotePosting/save',
                            data: { 'params': params },
                            type: 'POST',
                            dataType: 'JSON',
                            beforeSend : function(){ showLoading() },
                            success : function(data){
                                hideLoading();
                                if ( data.status == 1 ) {
                                    bootbox.alert(data.message, function() {
                                        cn.loadForm(data.content.id, null, 'action');
                                        cn.getLists();
                                    });
                                } else {
                                    bootbox.alert(data.message);
                                }
                            }
                        });
                    }
                });
            }
		}
	}, // end - save

    edit: function(elm) {
		var div = $('#action');

		var err = 0;
		$.map( $(div).find('[data-required="1"]'), function(ipt) {
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
            var tot_pakai_cn = numeral.unformat($(div).find('.pakai_cn').val());
            var tot_cn = numeral.unformat($(div).find('.nilai_cn').val());

            var detail = cn.getDetail(div);

            if ( detail.length == 0 ) {
                bootbox.alert('Harap pilih minimal satu No. SJ.');
            } else if ( tot_pakai_cn > tot_cn ) {
                bootbox.alert('Total pemakaian CN yang anda post melebihi nilai CN. Harap cek kembali data yang anda masukkan.');
            } else {
                bootbox.confirm('Apakah anda yakin ingin meng-edit data ?', function (result) {
                    if ( result ) {
                        var params = {
                            'id': $(elm).attr('data-kode'),
                            'tanggal': dateSQL( $(div).find('#Tanggal').data('DateTimePicker').date() ),
                            'jenis_cn': $(div).find('.jenis_cn').select2('val'),
                            'no_cn': $(div).find('.cn').select2('val'),
                            'tot_pakai': numeral.unformat($(div).find('.pakai_cn').val()),
                            'detail': detail
                        };

                        $.ajax({
                            url : 'transaksi/CreditNotePosting/edit',
                            data: { 'params': params },
                            type: 'POST',
                            dataType: 'JSON',
                            beforeSend : function(){ showLoading() },
                            success : function(data){
                                hideLoading();
                                if ( data.status == 1 ) {
                                    bootbox.alert(data.message, function() {
                                        cn.loadForm(data.content.id, null, 'action');
                                        cn.getLists();
                                    });
                                } else {
                                    bootbox.alert(data.message);
                                }
                            }
                        });
                    }
                });
            }
		}
	}, // end - edit

    getDetail: function(div) {
        var detail = [];
        $.map( $(div).find('.detail_sj tr.head'), function(tr) {
            var nomor = $(tr).find('.no_sj_val').val();
            if ( !empty(nomor) ) {
                detail.push({
                    'nomor': nomor,
                    'pakai': numeral.unformat($(tr).find('.pakai').val()),
                    // snapshot tampilan modal (utk view/edit semua jenis)
                    'no_sj': $.trim( $(tr).find('.no_sj_text').text() ),
                    'tagihan': numeral.unformat( $(tr).find('td.tagihan').text() ),
                    'sisa': numeral.unformat( $(tr).find('td.sisa').text() )
                });
            }
        });
        return detail;
    }, // end - getDetail

    delete: function(elm) {
		var div = $('#action');

        bootbox.confirm('Apakah anda yakin ingin meng-hapus data ?', function (result) {
            if ( result ) {
                var params = {
                    'id': $(elm).attr('data-kode')
                };

                $.ajax({
                    url : 'transaksi/CreditNotePosting/delete',
                    data: { 'params': params },
					type: 'POST',
					dataType: 'JSON',
                    beforeSend : function(){ showLoading() },
                    success : function(data){
                        hideLoading();
                        if ( data.status == 1 ) {
                            bootbox.alert(data.message, function() {
                                cn.loadForm(null, null, 'action');
                                cn.getLists();
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

cn.startUp();
