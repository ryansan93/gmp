var st = {
    startUp: function () {
        st.settingUp();
    }, // end - startUp

    settingUp: function () {
		$('.jenis').select2({placeholder: 'Pilih Jenis'}).on("select2:select", function (e) {
            var jenis = $('.jenis').select2().val();

            for (var i = 0; i < jenis.length; i++) {
                if ( jenis[i] == 'all' ) {
                    $('.jenis').select2().val('all').trigger('change');

                    i = jenis.length;
                }
            }

            $('.jenis').next('span.select2').css('width', '100%');
        });
        $('.jenis').next('span.select2').css('width', '100%');

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

        $('.perusahaan').select2({placeholder: 'Pilih Perusahaan'}).on("select2:select", function (e) {
            var perusahaan = $('.perusahaan').select2().val();

            for (var i = 0; i < perusahaan.length; i++) {
                if ( perusahaan[i] == 'all' ) {
                    $('.perusahaan').select2().val('all').trigger('change');

                    i = perusahaan.length;
                }
            }

            $('.perusahaan').next('span.select2').css('width', '100%');
        });
        $('.perusahaan').next('span.select2').css('width', '100%');

        $("#Tanggal").datetimepicker({
            locale: 'id',
            format: 'DD MMM Y'
        });
    }, // end - settingUp

    getLists: function () {
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
            var dcontent = $('table').find('tbody');

			var params = {
                'jenis' : $('.jenis').select2().val(),
                'unit' : $('.unit').select2().val(),
                'perusahaan' : $('.perusahaan').select2().val(),
				'tanggal': dateSQL( $('#Tanggal').data('DateTimePicker').date() ),
			};

			$.ajax({
                url : 'report/SisaStokBelumTutupSiklus/getLists',
                data : {
                    'params' : params
                },
                type : 'GET',
                dataType : 'HTML',
                beforeSend : function(){ App.showLoaderInContent( $(dcontent) ); },
                success : function(html){
                	App.hideLoaderInContent( $(dcontent), html );

                    st.hitTotal();
                }
            });
		}
	}, // end - getLists

    hitTotal: function() {
        $.map( $('thead').find('td.hit_total'), function(td) {
            var target = $(td).attr('data-target');

            var total = 0;
            $.map( $('tbody').find('td[target="'+target+'"]'), function(td_target) {
                var nilai = parseFloat($(td_target).attr('data-val'));

                total += nilai;
            });

			console.log( total );

            $(td).find('b').text( numeral.formatDec( total ) );
        });
    }, // end - hitTotal

    encryptParams: function() {
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
            var dcontent = $('table').find('tbody');

			var params = {
                'jenis' : $('.jenis').select2().val(),
                'unit' : $('.unit').select2().val(),
                'perusahaan' : $('.perusahaan').select2().val(),
				'tanggal': dateSQL( $('#Tanggal').data('DateTimePicker').date() ),
			};

			$.ajax({
                url : 'report/SisaStokBelumTutupSiklus/encryptParams',
                data : {
                    'params' : params
                },
                type : 'POST',
                dataType : 'JSON',
                beforeSend : function(){ showLoading() },
                success : function(data){
                	hideLoading();

                    if ( data.status == 1 ) {
                        st.exportExcel(data.content);
                    } else {
                        bootbox.alert( data.message );
                    }
                }
            });
		}
	}, // end - encryptParams

    exportExcel : function (params) {
		goToURL('report/SisaStokBelumTutupSiklus/exportExcel/'+params);
	}, // end - exportExcel
};

st.startUp();