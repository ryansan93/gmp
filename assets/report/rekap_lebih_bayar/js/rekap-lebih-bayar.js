var rlb = {
	start_up: function () {
        $('.pelanggan').select2();

        $('.datetimepicker').datetimepicker({
            locale: 'id',
            format: 'DD MMM Y'
        });
	}, // end -start_up

	get_lists: function (elm) {
		var form = $(elm).closest('form');

        var start_date = dateSQL($(form).find('#StartDate_RLB').data('DateTimePicker').date());
        var end_date = dateSQL($(form).find('#EndDate_RLB').data('DateTimePicker').date());
		var no_pelanggan = $(form).find('select.pelanggan').select2('val');

        var err = 0;
        $.map( $(form).find('[data-required=1]'), function(ipt) {
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
                'start_date': start_date,
                'end_date': end_date,
                'no_pelanggan': no_pelanggan
            };

    		$.ajax({
                url : 'report/RekapLebihBayar/get_lists',
                data : {'params' : params},
                dataType : 'JSON',
                type : 'POST',
                beforeSend : function(){ showLoading(); },
                success : function(data){
                    $('table').find('tbody').html(data.list);
                    hideLoading();
                }
            });
        }
	}, // end -get_lists
};

rlb.start_up();