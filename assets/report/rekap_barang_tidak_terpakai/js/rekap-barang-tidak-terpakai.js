var rbtt = {
    startUp: function() {
        rbtt.settingUp();
    }, // end - startUp

    settingUp: function() {
        $('select.jenis').select2().on("select2:select", function (e) {
            var val = e.params.data.id;

            $('select.barang').find('option').removeAttr('disabled');
            $('select.barang').find('option:not(.all, .'+val+')').attr('disabled', 'disabled');

            $('select.barang').select2({placeholder: 'Pilih Barang'}).on("select2:select", function (e) {
                var unit = $('select.barang').select2().val();
    
                for (var i = 0; i < unit.length; i++) {
                    if ( unit[i] == 'all' ) {
                        $('select.barang').select2().val('all').trigger('change');
    
                        i = unit.length;
                    }
                }
    
                $('select.barang').next('span.select2').css('width', '100%');
            });
            $('select.barang').next('span.select2').css('width', '100%');
        });

        $('select.perusahaan').select2({placeholder: 'Pilih Perusahaan'}).on("select2:select", function (e) {
            var perusahaan = $('select.perusahaan').select2().val();

            for (var i = 0; i < perusahaan.length; i++) {
                if ( perusahaan[i] == 'all' ) {
                    $('select.perusahaan').select2().val('all').trigger('change');

                    i = perusahaan.length;
                }
            }

            $('select.perusahaan').next('span.select2').css('width', '100%');
        });
        $('select.perusahaan').next('span.select2').css('width', '100%');

        $('select.unit').select2({placeholder: 'Pilih Unit'}).on("select2:select", function (e) {
            var unit = $('select.unit').select2().val();

            for (var i = 0; i < unit.length; i++) {
                if ( unit[i] == 'all' ) {
                    $('select.unit').select2().val('all').trigger('change');

                    i = unit.length;
                }
            }

            $('select.unit').next('span.select2').css('width', '100%');
        });
        $('select.unit').next('span.select2').css('width', '100%');

        $('select.barang').select2({placeholder: 'Pilih Barang'}).on("select2:select", function (e) {
            var unit = $('select.barang').select2().val();

            for (var i = 0; i < unit.length; i++) {
                if ( unit[i] == 'all' ) {
                    $('select.barang').select2().val('all').trigger('change');

                    i = unit.length;
                }
            }

            $('select.barang').next('span.select2').css('width', '100%');
        });
        $('select.barang').next('span.select2').css('width', '100%');
    }, // end - settingUp

    getLists: function() {
        var err = 0;
        $.map( $('[data-required="1"]'), function(ipt) {
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
                'perusahaan': $('select.perusahaan').select2().val(),
                'jenis': $('select.jenis').select2().val(),
                'unit': $('select.unit').select2().val(),
                'barang': $('select.barang').select2().val()
            };

            $.ajax({
                url : 'report/RekapBarangTidakTerpakai/getLists',
                data : {'params' : params},
                dataType : 'HTML',
                type : 'GET',
                beforeSend : function(){ showLoading(); },
                success : function(html){
                    hideLoading();

                    $('table tbody').html( html );
                    // if ( data.status == 1 ) {
                    // } else {
                    //     bootbox.alert(data.message);
                    // }
                }
            });
        }
    }, // end - getLists
};

rbtt.startUp();