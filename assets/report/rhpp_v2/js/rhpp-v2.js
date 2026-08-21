var rv = {
	startUp: function() {
		rv.settingUp();
		rv.updateScrollButtons();
		rv.positionScrollButtons();

		// Posisi tombol di-refresh berkala krn sidebar bisa di-toggle kapan saja
		// (tanpa event resize/scroll) - polling ringan lebih simpel & robust
		// drpd hook ke mekanisme toggle sidebar yg entah dari mana.
		setInterval( rv.positionScrollButtons, 400 );
	}, // end - startUp

	settingUp: function() {
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

        $('#tbl_rhpp_v2_wrapper').on('scroll', function() {
        	rv.updateScrollButtons();
        });
        $(window).on('resize', function() {
        	rv.updateScrollButtons();
        	rv.positionScrollButtons();
        });

        $('#tbl_rhpp_v2_wrapper').on('click', 'tbody tr.row-clickable', function() {
        	$(this).toggleClass('row-flagged');
        });
	}, // end - settingUp

	positionScrollButtons: function() {
		var wrapper = $('#tbl_rhpp_v2_wrapper').get(0);

		if ( !wrapper ) {
			return;
		}

		var rect = wrapper.getBoundingClientRect();

		if ( rect.width <= 0 ) {
			return;
		}

		$('.btn-scroll-table-left').css({ left: (rect.left + 10) + 'px' });
		$('.btn-scroll-table-right').css({ left: (rect.right - 52) + 'px' });
	}, // end - positionScrollButtons

	updateScrollButtons: function() {
		var wrapper = $('#tbl_rhpp_v2_wrapper').get(0);

		if ( !wrapper || wrapper.scrollWidth <= wrapper.clientWidth ) {
			$('.btn-scroll-table').hide();
			return;
		}

		var scrollLeft = wrapper.scrollLeft;
		var maxScroll = wrapper.scrollWidth - wrapper.clientWidth;

		$('.btn-scroll-table-left').toggle( scrollLeft > 2 );
		$('.btn-scroll-table-right').toggle( scrollLeft < maxScroll - 2 );
	}, // end - updateScrollButtons

	scrollTable: function(direction) {
		var wrapper = $('#tbl_rhpp_v2_wrapper');

		wrapper.stop().animate({ scrollLeft: wrapper.scrollLeft() + (direction * 400) }, 300, function(){
			rv.updateScrollButtons();
		});
	}, // end - scrollTable

	cekData: function() {
        var err = 0;
        $.map( $('[data-required=1]'), function (ipt) {
            if ( empty( $(ipt).val() ) ) {
                $(ipt).parent().addClass('has-error');
                err++;
            } else {
                $(ipt).parent().removeClass('has-error');
            }
		});

		return err;
	}, // end - cekData

	getParams: function() {
		return {
			'unit': $('.unit').select2().val(),
			'start_date': dateSQL( $('#StartDate').data('DateTimePicker').date() ),
			'end_date': dateSQL( $('#EndDate').data('DateTimePicker').date() )
		};
	}, // end - getParams

	getLists: function() {
		if ( rv.cekData() > 0 ) {
			bootbox.alert('Harap lengkapi data terlebih dahulu.');
		} else {
			var params = rv.getParams();

			$.ajax({
                url : 'report/RhppV2/getLists',
                data : {
                    'params' : params
                },
                type : 'POST',
                dataType : 'JSON',
                beforeSend : function(){ showLoading(); },
                success : function(data){
                	hideLoading();

                    if ( data.status == 1 ) {
                        $('.data tbody').html( data.html );
                        rv.updateScrollButtons();
                        rv.positionScrollButtons();
                    } else {
                        bootbox.alert( data.message );
                    }
                }
            });
		}
	}, // end - getLists

	excryptParams: function() {
		if ( rv.cekData() > 0 ) {
			bootbox.alert('Harap lengkapi data terlebih dahulu.');
		} else {
			var params = rv.getParams();

			$.ajax({
                url : 'report/RhppV2/excryptParams',
                data : {
                    'params' : params
                },
                type : 'POST',
                dataType : 'JSON',
                beforeSend : function(){ showLoading(); },
                success : function(data){
                	hideLoading();

                    if ( data.status == 1 ) {
                        rv.exportExcel(data.content);
                    } else {
                        bootbox.alert( data.message );
                    }
                }
            });
		}
	}, // end - excryptParams

	exportExcel: function(params) {
		goToURL('report/RhppV2/exportExcel/'+params);
	}, // end - exportExcel
};

rv.startUp();
