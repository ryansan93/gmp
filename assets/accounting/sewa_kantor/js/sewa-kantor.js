var sk = {
    startUp: function() {
        sk.settingUp();
    }, // end - startUp

    settingUp: function() {
        $('select.unit').select2({'placeholder': '-- Pilih Unit --'});
        $('select.perusahaan').select2();

        $('#StartDate, #EndDate').datetimepicker({
            locale: 'id',
            format: 'DD MMM Y'
        });

        $('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal], [data-tipe=decimal3],[data-tipe=decimal4], [data-tipe=number]').each(function(){
            $(this).priceFormat(Config[$(this).data('tipe')]);
        });
    }, // end - settingUp

    changeTabActive: function(elm) {
        var href = $(elm).data('href');
        var edit = $(elm).data('edit');
        var id = $(elm).data('id');

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

        sk.loadForm(id, edit, href);
    }, // end - changeTabActive

    loadForm: function(_id = null, _edit = null, _href = null) {
        var dcontent = $('div#'+_href);

        var params = {
            'id': _id
        };

        $.ajax({
            url : 'accounting/SewaKantor/loadForm',
            data : {
                'params' :  params,
                'edit' :  _edit
            },
            type : 'GET',
            dataType : 'HTML',
            beforeSend : function(){ App.showLoaderInContent(dcontent); },
            success : function(html){
                App.hideLoaderInContent(dcontent, html);

                sk.settingUp();
            },
        });
    }, // end - loadForm

    getLists: function () {
        var div = $('div#riwayat');

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
            var data = {
                'unit': $(div).find('.unit').select2('val')
            };

            $.ajax({
                url : 'accounting/SewaKantor/getLists',
                data : {
                    'params' :  data
                },
                type : 'GET',
                dataType : 'HTML',
                beforeSend : function(){ showLoading; },
                success : function(html){
                    hideLoading();

                    $(div).find('table tbody').html( html );
                },
            });
        }
    }, // end - getLists

    save: function () {
        var div = $('div#action');

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
            bootbox.confirm('Apakah anda yakin ingin menyimpan data sewa kantor ?', function(result) {
                if ( result ) {
                    var data = {
                        'perusahaan': $(div).find('.perusahaan').select2('val'),
                        'unit': $(div).find('.unit').select2('val'),
                        'jangka_waktu': numeral.unformat( $(div).find('.jangka_waktu').val() ),
                        'mulai': dateSQL( $(div).find('#StartDate').data('DateTimePicker').date() ),
                        'akhir': dateSQL( $(div).find('#EndDate').data('DateTimePicker').date() ),
                        'nominal': numeral.unformat( $(div).find('.nominal').val() ),
                    };

                    $.ajax({
                        url : 'accounting/SewaKantor/save',
                        data : {
                            'params' :  data
                        },
                        type : 'POST',
                        dataType : 'JSON',
                        beforeSend : function(){ showLoading; },
                        success : function(data){
                            hideLoading();

                            if ( data.status == 1 ) {
                                bootbox.alert(data.message, function() {
                                    sk.loadForm(null, null, 'action');
                                });
                            } else {
                                bootbox.alert(data.message);
                            }
                        },
                    });
                }
            });
        }
    }, // end - save
};

sk.startUp();