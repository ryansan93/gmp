var ktp = {
	start_up: function () {
		$('.datetimepicker').datetimepicker({
            locale: 'id',
            format: 'DD MMM Y'
        });

        ktp.set_table_page('.tbl_ktp');
	},

	set_table_page : function(tbl_id){
        let _t_rdim = TUPageTable;
        _t_rdim.destroy();
        _t_rdim.setTableTarget(tbl_id);
        _t_rdim.setPages(['page1', 'page2']);
        _t_rdim.setHideButton(true);
        _t_rdim.onClickNext(function(){
            // console.log('Log onClickNext');
        });
        _t_rdim.start();
    }, // end - set_table_page

	get_lists: function () {
		var err = 0;

		$.map( $('[data-required=1]'), function(ipt) {
			if ( empty($(ipt).val()) ) {
				err++;
				$(ipt).parent().addClass('has-error');
			} else {
				$(ipt).parent().removeClass('has-error');
			};
		});

		if ( err > 1 ) {
			bootbox.alert('Data belum lengkap, mohon cek kembali data yang anda masukkan.');
		} else {
			var params = {
				'start_date' : dateSQL( $('#StartDate_SPM').data('DateTimePicker').date() ),
				'end_date' : dateSQL( $('#EndDate_SPM').data('DateTimePicker').date() )
			};

			$.ajax({
	            url : 'report/KirimTerimaPakan/list_ktp',
	            data : {
	            	'params' : params
	            },
	            dataType : 'JSON',
	            type : 'POST',
	            beforeSend : function(){
	            	showLoading();
	            },
	            success : function(data){
	                $('table.tbl_ktp tbody').html(data.content);

	                ktp.set_table_page('.tbl_ktp');

	                hideLoading();
	            }
	        });
		};
	},
};

ktp.start_up();