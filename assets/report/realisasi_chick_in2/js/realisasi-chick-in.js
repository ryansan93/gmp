var rci = {
    startUp: function() {
        rci.settingUp();
    }, // end - startUp

    settingUp: function() {
        $("#StartDate").datetimepicker({
            locale: 'id',
            format: 'DD MMM Y',
            useCurrent:true,
            // daysOfWeekDisabled: [0, 2, 3, 4, 5, 6],
        });
        $("#EndDate").datetimepicker({
            locale: 'id',
            format: 'DD MMM Y',
            useCurrent:true,
            // daysOfWeekDisabled: [1, 2, 3, 4, 5, 6],
        });

        var startDate = $("#StartDate").find('input').attr('data-tgl');
        var endDate = $("#EndDate").find('input').attr('data-tgl');

        if ( !empty(startDate) ) {
            $("#StartDate").data("DateTimePicker").date(moment(new Date(startDate)));
            var minDate = dateSQL($("#StartDate").data("DateTimePicker").date())+' 00:00:00';
            $("#EndDate").data("DateTimePicker").minDate(moment(new Date(minDate)));
        }

        if ( !empty(endDate) ) {
            $("#EndDate").data("DateTimePicker").date(moment(new Date(endDate)));
            var maxDate = dateSQL($("#EndDate").data("DateTimePicker").date())+' 23:59:59';
            $("#StartDate").data("DateTimePicker").maxDate(moment(new Date(maxDate)));
        }

        // var today = moment(new Date()).format('YYYY-MM-DD');
        $("#StartDate").on("dp.change", function (e) {
            var minDate = dateSQL($("#StartDate").data("DateTimePicker").date())+' 00:00:00';
            $("#EndDate").data("DateTimePicker").minDate(moment(new Date(minDate)));
        });
        $("#EndDate").on("dp.change", function (e) {
            var maxDate = dateSQL($("#EndDate").data("DateTimePicker").date())+' 23:59:59';
            $("#StartDate").data("DateTimePicker").maxDate(moment(new Date(maxDate)));
            // if ( maxDate >= (today+' 00:00:00') ) {
            // }
        });

        $('.jenis').select2({placeholder: " -- Pilih Unit --"});
        $('.unit').select2({placeholder: " -- Pilih Jenis --"});

        $('[data-tipe=integer], [data-tipe=angka], [data-tipe=decimal], [data-tipe=decimal3],[data-tipe=decimal4], [data-tipe=number]').each(function(){
            $(this).priceFormat(Config[$(this).data('tipe')]);
        });
    }, // end - settingUp

    getLists: function() {
        var dcontent = $('table.tblRiwayat tbody');

        var params = {
            'start_date': dateSQL( $('#StartDate').data('DateTimePicker').date() ),
            'end_date': dateSQL( $('#EndDate').data('DateTimePicker').date() ),
            'unit': $('.unit').select2('val'),
            'jenis': $('.jenis').select2('val'),
        };

        $.ajax({
            url: 'report/RealisasiChickIn2/getLists',
            data: {
                'params': params
            },
            type: 'GET',
            dataType: 'HTML',
            beforeSend: function(){ App.showLoaderInContent(dcontent); },
            success: function(html){
                App.hideLoaderInContent(dcontent, html);
            }
        });
    }, // end - getLists

    encryptParams: function() {
		var err = 0;
		
		$.map( $('[data-required=1]'), function (ipt) {
			if ( empty($(ipt).val()) ) {
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
            'start_date': dateSQL( $('#StartDate').data('DateTimePicker').date() ),
            'end_date': dateSQL( $('#EndDate').data('DateTimePicker').date() ),
            'unit': $('.unit').select2('val'),
            'jenis': $('.jenis').select2('val'),
        };

			$.ajax({
	            url: 'report/RealisasiChickIn2/encryptParams',
	            data: {
	                'params': params
	            },
	            type: 'POST',
	            dataType: 'JSON',
	            beforeSend: function() { showLoading(); },
	            success: function(data) {
	                hideLoading();

	                if ( data.status == 1 ) {
		                rci.exportExcel(data.content);
	                } else {
	                	bootbox.alert( data.message );
	                }
	            }
	        });
		}
	}, // end - excryptParams

	exportExcel : function (params) {
		goToURL('report/RealisasiChickIn2/exportExcel/'+params);
	}, // end - exportExcel
};

rci.startUp();