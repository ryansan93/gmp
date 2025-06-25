var hu = {
    startUp: function() {
        hu.settingUp();
    }, // end - startUp

    settingUp: function() {
        $('select.perusahaan').select2({placeholder: '-- Pilih Perusahaan --'}).on("select2:select", function (e) {
            var perusahaan = $('.perusahaan').select2().val();

            for (var i = 0; i < perusahaan.length; i++) {
                if ( perusahaan[i] == 'all' ) {
                    $('.perusahaan').select2().val('all').trigger('change');

                    i = perusahaan.length;
                }
            }
        });
        $('select.jenis').select2({placeholder: '-- Pilih Jenis --'}).on("select2:select", function (e) {
            var jenis = $('.jenis').select2().val();

            for (var i = 0; i < jenis.length; i++) {
                if ( jenis[i] == 'all' ) {
                    $('.jenis').select2().val('all').trigger('change');

                    i = jenis.length;
                }
            }
        });
        $('select.supplier').select2({placeholder: '-- Pilih Supplier --'}).on("select2:select", function (e) {
            var supplier = $('.supplier').select2().val();

            for (var i = 0; i < supplier.length; i++) {
                if ( supplier[i] == 'all' ) {
                    $('.supplier').select2().val('all').trigger('change');

                    i = supplier.length;
                }
            }
        });
    }, // end - settingUp

    collapseRow: function(elm) {
        var tr = $(elm);
        var tr_detail = $(elm).next('tr.detail');

        if ( $(tr_detail).hasClass('hide') ) {
            $(tr_detail).removeClass('hide');
        } else {
            $(tr_detail).addClass('hide');
        }
    }, // end - collapseRow

    getLists: function() {
        var err = 0;
        $.map( $('[data-required=1]'), function( ipt ) {
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
            var data = {
                'perusahaan': $('select.perusahaan').select2().val(),
                'jenis': $('select.jenis').select2().val(),
                'supplier': $('select.supplier').select2().val()
            };

            var dtbody = $('table tbody');

            $.ajax({
                url :'report/HutangUsaha/getLists',
                dataType: 'html',
				type: 'get',
                data : {
                    'params': data
                },
                beforeSend : function(){ App.showLoaderInContent(dtbody); },
                success : function(html){
                    App.hideLoaderInContent(dtbody, html);

                    hu.hitTotal();
                },
            });
        }
    }, // end - getLists

    hitTotal: function () {
        var dtable = $('table');
        var dthead = $(dtable).find('thead');
        var dtbody = $(dtable).find('tbody');

        var totHutang = 0;
        var totBayar = 0;
        var sisaHutang = 0;
        $.map( $(dtbody).find('tr.head'), function (tr) {
            var hutang = parseFloat(numeral.unformat($(tr).find('td.hutang').text()));
            var bayar = parseFloat(numeral.unformat($(tr).find('td.bayar').text()));
            var sisa = parseFloat(numeral.unformat($(tr).find('td.sisa').text()));

            totHutang += hutang;
            totBayar += bayar;
            sisaHutang += sisa;
        });

        $(dthead).find('td.tot_hutang b').text( numeral.formatDec(totHutang) );
        $(dthead).find('td.tot_bayar b').text( numeral.formatDec(totBayar) );
        $(dthead).find('td.sisa_hutang b').text( numeral.formatDec(sisaHutang) );
    }, // end - hitTotal
};

hu.startUp();