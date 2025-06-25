var pb = {
    startUp: function () {
        pb.settingUp();
    }, // end - startUp

    settingUp: function () {
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
			// $("[name=endDate]").data("DateTimePicker").date(e.date);
		});
		$("[name=endDate]").on("dp.change", function (e) {
			$('[name=startDate]').data("DateTimePicker").maxDate(e.date);
		});

        $('select.jenis').select2();
        $('select.jenis_filter').select2().on("select2:select", function (e) {
            var jenis = $('select.jenis_filter').select2().val();

            pb.jenisFilter( jenis );
        });
    }, // end - settingUp

    jenisFilter: function (jenis) {
        var div = $('div.'+jenis);

        $('div.jenis_filter').addClass('hide');
        $('div.jenis_filter').find('input, select').val('');
        $('div.jenis_filter').find('input, select').removeAttr('data-required');

        $(div).removeClass('hide');
        $(div).find('input, select').attr('data-required', 1);
    }, // end - jenisFilter

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
            var jenis = $('select.jenis').select2().val();
            var jenis_filter = $('select.jenis_filter').select2().val();
            var start_date = ( !$('#StartDate').closest('div.jenis_filter').hasClass('hide') ) ? dateSQL( $('#StartDate').data('DateTimePicker').date() ) : null;
            var end_date = ( !$('#EndDate').closest('div.jenis_filter').hasClass('hide') ) ? dateSQL( $('#EndDate').data('DateTimePicker').date() ) : null;
            var no_sj_asal = ( !$('input.no_sj_asal').closest('div.jenis_filter').hasClass('hide') ) ? $('input.no_sj_asal').val() : null;

            var params = {
                'jenis': jenis,
                'jenis_filter': jenis_filter,
                'start_date': start_date,
                'end_date': end_date,
                'no_sj_asal': no_sj_asal
            };

            var dcontent = $('table').find('tbody');
            $.ajax({
	            url : 'report/PindahBarang/getLists',
	            data : {'params' : params},
	            dataType : 'HTML',
	            type : 'GET',
	            beforeSend : function(){ App.showLoaderInContent( $(dcontent) ); },
	            success : function(html){
	                App.hideLoaderInContent( $(dcontent), html );
	            }
	        });
        }
    }, // end - getLists
};

pb.startUp();