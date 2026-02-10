var stok = {
    startUp: function() {
        stok.settingUp();
    }, // end - stok

    setSelect2Plasma: function(elm) {
        $(elm).select2({
            ajax: {
                // delay: 500,
                // quietMillis: 150,
                url: 'report/PosisiStokSiklus/getPlasma',
                dataType: 'json',
                type: 'GET',
                data: function (params, jenis) {
                    var query = {
                        search: params.term,
                        type: 'item_search',
						unit: $('select.unit').select2().val(),
						tutup_siklus: $('select.tutup_siklus').select2().val(),
                    }
    
                    // Query parameters will be ?search=[term]&type=user_search
                    return query;
                },
                processResults: function (data) {
					// $('li.select2-results__option').attr('aria-selected', false);

                    return {
                        results: !empty(data) ? data : []
                    };
                },
                error: function (jqXHR, status, error) {
                    // console.log(error + ": " + jqXHR.responseText);
                    return { results: [] }; // Return dataset to load after error
                }
            },
            cache: true,
            placeholder: 'Search for a Plasma ...',
            // minimumInputLength: 2,
            escapeMarkup: function (markup) { return markup; }, // let our custom formatter work
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

                $('.nilai_cn').val(numeral.formatDec(tot_cn));

                return data.text;
            },
        });
    }, // end - setSelect2Plasma

    settingUp: function() {
        $('select.bulan').select2();
        $('select.unit').select2();
        $('select.tutup_siklus').select2();
        $('select.jenis_barang').select2().on("select2:select", function (e) {
            var jenis = e.params.data.id;

            $('select.barang').find('option').removeAttr('disabled');
            if ( jenis != 'all' ) {
                $('select.barang').find('option:not([data-jenis="'+jenis+'"])').attr('disabled', 'disabled');
                $('select.barang').find('option[value="all"]').removeAttr('disabled');
            }
            $('select.barang').select2();
        });
        $('select.barang').select2();

        $('#Tahun').datetimepicker({
            locale: 'id',
            format: 'Y'
        });

        $(document).ready(function () {
            stok.setSelect2Plasma( $('.plasma') );
        });
    }, // end - stok

    getData: function() {
        var err = 0;
        $.map( $('[data-required=1]'), function (ipt) {
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
            var dcontent = $('table tbody');
			var params = {
				'bulan': $('.bulan').select2().val(),
				'unit': $('.unit').select2().val(),
				'tutup_siklus': $('.tutup_siklus').select2().val(),
				'plasma': $('.plasma').select2().val(),
				'jenis_barang': $('.jenis_barang').select2().val(),
				'barang': $('.barang').select2().val(),
				'tahun': dateSQL( $('#Tahun').data('DateTimePicker').date() )
			};

			$.ajax({
                url : 'report/PosisiStokSiklus/getData',
                data : {
                    'params' : params
                },
                type : 'GET',
                dataType : 'HTML',
                beforeSend : function(){ App.showLoaderInContent( $(dcontent) ); },
                success : function(html){
                	App.hideLoaderInContent( $(dcontent), html );

                    // $(document).ready(function () {
                        stok.setSelect2Plasma( $('.plasma') );
                    // });
                }
            });
		}
    }, // end - getData
};

stok.startUp();