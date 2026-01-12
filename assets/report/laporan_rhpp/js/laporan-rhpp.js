var lr = {
	startUp: function() {
		lr.settingUp();
	}, // end - startUp

	settingUp: function() {
        $('.jenis_rhpp').select2();
        $('.unit').select2();

		$("#StartDate").datetimepicker({
            locale: 'id',
            format: 'DD MMM Y'
        });
        $("#EndDate").datetimepicker({
            locale: 'id',
            format: 'DD MMM Y'
        });
        var today = moment(new Date()).format('YYYY-MM-DD');
        $("#StartDate").on("dp.change", function (e) {
            var minDate = dateSQL($("#StartDate").data("DateTimePicker").date())+' 00:00:00';
            $("#EndDate").data("DateTimePicker").minDate(moment(new Date(minDate)));
        });
        $("#EndDate").on("dp.change", function (e) {
            var maxDate = dateSQL($("#EndDate").data("DateTimePicker").date())+' 23:59:59';
            if ( maxDate >= (today+' 00:00:00') ) {
                $("#StartDate").data("DateTimePicker").maxDate(moment(new Date(maxDate)));
            }
        });
	}, // end - settingUp

	getLists: function() {
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
			var params = {
				'jenis_rhpp': $('.jenis_rhpp').select2().val(),
				'unit': $('.unit').select2().val(),
				'start_date': dateSQL( $('#StartDate').data('DateTimePicker').date() ),
				'end_date': dateSQL( $('#EndDate').data('DateTimePicker').date() )
			};

			$.ajax({
                url : 'report/LaporanRhpp/getLists',
                data : {
                    'params' : params
                },
                type : 'POST',
                dataType : 'JSON',
                beforeSend : function(){ showLoading(); },
                success : function(data){
                	hideLoading();

                    if ( data.status == 1 ) {
                        $('tbody').html( data.html );
                    } else {
                        bootbox.alert( data.message );
                    }
                }
            });
		}
    }, // end - getLists
};

lr.startUp();