var rpah = {
	start_up: function () {
	}, // end - start_up

	addRow: function(elm) {
        let row = $(elm).closest('tr');
        let newRow = row.clone();

        newRow.find('input, select').val('');
        row.find('.btn-ctrl').hide();
        row.after(newRow);

        App.formatNumber();
    }, // end - addRow

    removeRow: function(elm) {
        let table = $(elm).closest('table.detail');
        let row = $(elm).closest('tr');
        if ($(row).prev('tr').length > 0) {
            $(row).prev('tr').find('.btn-ctrl').show();
            $(row).remove();
        }else{
            $(row).prev('tr').find('.btn-ctrl').show();
        }
    }, // end - removeRow

    changeTabActive: function(elm) {
        var vhref = $(elm).data('href');
        var resubmit = $(elm).data('resubmit');
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

            rpah.load_form(v_id, resubmit, elm);
        };
    }, // end - changeTabActive

    load_form: function(v_id = null, resubmit = null) {
        var dcontent = $('div#action');

        $.ajax({
            url : 'transaksi/RPAH/load_form',
            data : {
                'id' :  v_id,
                'resubmit' :  resubmit
            },
            type : 'GET',
            dataType : 'HTML',
            beforeSend : function(){ App.showLoaderInContent(dcontent); },
            success : function(html){
                App.hideLoaderInContent(dcontent, html);
                App.formatNumber();

                // if ( empty(resubmit) ) {
                //     $("#tgl_timbang").datetimepicker({
                //         locale: 'id',
                //         format: 'DD MMM Y',
                //         maxDate : new Date()
                //     }).on("dp.change", function (e) {
                //         Hk.getUmur($("#tgl_timbang"));
                //     });
                // };

                // if ( resubmit == 'edit' ) {
                //     var id_rdim_submit = $('select[name=noreg]').data('idrdimsubmit');
                //     Hk.getNoregMitraByRdim( $('select[name=periode]'), id_rdim_submit );
                // };
            },
        });
    }, // end - load_form
};

rpah.start_up();