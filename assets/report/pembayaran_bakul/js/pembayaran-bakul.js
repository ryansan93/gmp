var pb = {
	start_up : function () {
		pb.setting_up();
	}, // end - start_up

	setting_up: function(resubmit = null) {
		$('select.pelanggan').select2();

		$('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal], [data-tipe=decimal3],[data-tipe=decimal4], [data-tipe=number]').each(function(){
		    $(this).priceFormat(Config[$(this).data('tipe')]);
		});

        $('.datetimepicker').datetimepicker({
            locale: 'id',
            format: 'DD MMM Y'
        });
	}, // end - setting_up
};

pb.start_up();