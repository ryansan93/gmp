var pm = {
    startUp: function() {
        pm.settingUp();
    }, // end - startUp

    settingUp: function() {
        $('select.mitra').select2({placeholder: '-- Pilih Plasma --'}).on("select2:select", function (e) {
            var mitra = $('.mitra').select2().val();

            for (var i = 0; i < mitra.length; i++) {
                if ( mitra[i] == 'all' ) {
                    $('.mitra').select2().val('all').trigger('change');

                    i = mitra.length;
                }
            }
        });
        $('select.perusahaan').select2({placeholder: '-- Pilih Perusahaan --'}).on("select2:select", function (e) {
            var perusahaan = $('.perusahaan').select2().val();

            for (var i = 0; i < perusahaan.length; i++) {
                if ( perusahaan[i] == 'all' ) {
                    $('.perusahaan').select2().val('all').trigger('change');

                    i = perusahaan.length;
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
                'mitra': $('select.mitra').select2().val(),
                'perusahaan': $('select.perusahaan').select2().val()
            };

            var dtbody = $('table tbody');

            $.ajax({
                url :'report/PiutangMitra/getLists',
                dataType: 'html',
				type: 'get',
                data : {
                    'params': data
                },
                beforeSend : function(){ App.showLoaderInContent(dtbody); },
                success : function(html){
                    App.hideLoaderInContent(dtbody, html);

                    pm.hitTotal();
                },
            });
        }
    }, // end - getLists

    hitTotal: function () {
        var dtable = $('table');
        var dthead = $(dtable).find('thead');
        var dtbody = $(dtable).find('tbody');

        var totPiutang = 0;
        var totBayar = 0;
        var sisaPiutang = 0;
        $.map( $(dtbody).find('tr.head'), function (tr) {
            var piutang = parseFloat(numeral.unformat($(tr).find('td.piutang').text()));
            var bayar = parseFloat(numeral.unformat($(tr).find('td.bayar').text()));
            var sisa = parseFloat(numeral.unformat($(tr).find('td.sisa').text()));

            totPiutang += piutang;
            totBayar += bayar;
            sisaPiutang += sisa;
        });

        $(dthead).find('td.tot_piutang b').text( numeral.formatDec(totPiutang) );
        $(dthead).find('td.tot_bayar b').text( numeral.formatDec(totBayar) );
        $(dthead).find('td.sisa_piutang b').text( numeral.formatDec(sisaPiutang) );
    }, // end - hitTotal
};

pm.startUp();