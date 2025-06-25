var pp = {
	startUp: function () {
		pp.settingUp();
	}, // end - startUp

	settingUp: function () {
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
			var params = {
				'start_date': dateSQL( $('#StartDate').data('DateTimePicker').date() ),
				'end_date': dateSQL( $('#EndDate').data('DateTimePicker').date() ),
			};

			$.ajax({
	            url: 'report/PembayaranPlasma/getLists',
	            data: {
	                'params': params
	            },
	            type: 'GET',
	            dataType: 'HTML',
	            beforeSend: function() { showLoading(); },
	            success: function(html) {
	                hideLoading();
	                
	                $('.tbl_list').find('tbody').html( html );
	            }
	        });
		}
	}, // end - getLists

	filter_all: function (elm, sensitive = false) {
		var _target_table = $(elm).data('table');

	    var _table = $('table.'+_target_table);
	    var _tbody = $(_table).find('tbody');
	    var _content, _target;

	    _tbody.find('tr').show();
	    _content = $(elm).val().toUpperCase().trim();

	    if (!empty(_content) && _content != '') {
	        $.map( $(_tbody).find('tr.search'), function(tr){

	            // CEK DI TR ADA ATAU TIDAK
	            var ada = 0;
	            $.map( $(tr).find('td'), function(td){
	                var td_val = $(td).html().trim();
	                if ( !sensitive ) {
	                    if (td_val.toUpperCase().indexOf(_content) > -1) {
	                        ada = 1;
	                    }
	                } else {
	                    if (td_val.toUpperCase() == _content) {
	                        ada = 1;
	                    }
	                }
	            });

	            if ( ada == 0 ) {
	                $(tr).hide();
	            } else {
	                $(tr).show();
	            };
	        });
	    }

	    var total_rhpp = 0;
	    var total_bayar = 0;
	    $.map( $(_tbody).find('tr.data:not([style*="display: none"])'), function (tr) {
	    	var rhpp = numeral.unformat( $(tr).find('td.rhpp').text() );
	    	var bayar = numeral.unformat( $(tr).find('td.bayar').text() );

	    	total_rhpp += rhpp;
			total_bayar += bayar;
	    });

	    $(_tbody).find('tr.total td.total_rhpp').text( numeral.formatDec(total_rhpp) );
	    $(_tbody).find('tr.total td.total_bayar').text( numeral.formatDec(total_bayar) );
	}, // end - filter_all
};

pp.startUp();