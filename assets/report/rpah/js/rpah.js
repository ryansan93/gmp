var rpah = {
	start_up: function () {
		$("[name=startDate]").datetimepicker({
			locale: 'id',
            format: 'DD MMM Y'
		});
		$("[name=endDate]").datetimepicker({
			locale: 'id',
            format: 'DD MMM Y',
			useCurrent: false //Important! See issue #1075
		});
		$("[name=startDate]").on("dp.change", function (e) {
			$("[name=endDate]").data("DateTimePicker").minDate(e.date);
			$("[name=endDate]").data("DateTimePicker").date(e.date);
		});
		$("[name=endDate]").on("dp.change", function (e) {
			$('[name=startDate]').data("DateTimePicker").maxDate(e.date);
		});
	}, // end - start_up

	get_lists: function() {
		let err = 0;
		$.map( $('[data-required=1]'), function(ipt) {
			if ( empty( $(ipt).val() ) ) {
				$(ipt).parent().addClass('has-error');
				err++;
			} else {
				$(ipt).parent().removeClass('has-error');
			}
		});

		if ( err > 0 ) {
			bootbox.alert( 'Harap isi periode terlebih dahulu.' );
		} else {
			let params = {
				'start_date': dateSQL( $("[name=startDate]").data('DateTimePicker').date() ),
				'end_date': dateSQL( $("[name=endDate]").data('DateTimePicker').date() )
			};

			$.ajax({
	            url : 'report/RPAH/get_lists',
	            data : {'params' : params},
	            dataType : 'HTML',
	            type : 'GET',
	            beforeSend : function(){ showLoading(); },
	            success : function(html){
	                $('table.tbl_rpah').find('tbody').html(html);
	                hideLoading();
	                // console.log(html);
	            }
	        });
		}
	}, // end - get_lists
};

rpah.start_up();