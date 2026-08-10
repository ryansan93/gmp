var tutupSiklus = {
	start_up: function () {
		tutupSiklus.setting_up();
	}, // end - start_up

	setting_up: function() {
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
		});
		$("[name=endDate]").on("dp.change", function (e) {
			$('[name=startDate]').data("DateTimePicker").maxDate(e.date);
		});

		$("[name=tgl_tutup_siklus]").datetimepicker({
			locale: 'id',
            format: 'DD MMM Y'
		});

		$('select.filter').val(1);
	}, // end - setting_up

	filter: function (elm) {
		let filter = $(elm).val();
	}, // end - filter

	get_lists: function() {
		let err = 0;

		$.map( $('div.search').find('[data-required=1]'), function(ipt) {
			if ( empty( $(ipt).val() ) ) {
				$(ipt).parent().addClass('has-error');
				err++;
			} else {
				$(ipt).parent().removeClass('has-error');
			};
		});

		if ( err > 0 ) {
			bootbox.alert( 'Harap lengkapi data terlebih dahulu.' );
		} else {
			let start_date = null;
			if ( !empty( $('div[name=startDate] input').val() ) ) {
				start_date = dateSQL($('#StartDate').data('DateTimePicker').date());
			};

			let end_date = null;
			if ( !empty( $('div[name=endDate] input').val() ) ) {
				end_date = dateSQL($('#EndDate').data('DateTimePicker').date());
			};

			let params = {
				'filter': $('select.filter').val(),
				'start_date': start_date,
				'end_date': end_date
			};

			$.ajax({
	            url : 'transaksi/TutupSiklus/get_lists',
	            data : {
	                'params': params
	            },
	            type : 'GET',
	            dataType : 'HTML',
	            beforeSend : function(){ showLoading(); },
	            success : function(html){
	            	$('table.tbl_tutup_siklus').find('tbody').html( html );
	                hideLoading();
	            },
	        });
		}
	}, // end - get_lists

	openModal: function(elm) {
		let noreg = $(elm).data('noreg');
		let tgl_docin = $(elm).data('tgl_docin');
		let tgl_docin_display = $(elm).data('tgl_docin_display');

		let modal = $('#modal-tutup-siklus');

		$(modal).find('.noreg').text(noreg);
		$(modal).find('[name=noreg]').val(noreg);

		$(modal).find('.tgl_docin').text(tgl_docin_display);
		$(modal).find('[name=tgl_docin]').val(tgl_docin);

		$(modal).find('[name=tgl_tutup_siklus]').data('DateTimePicker').clear();

		$(modal).find('.has-error').removeClass('has-error');

		$(modal).modal('show');
	}, // end - openModal

	cekLhk: function(elm) {
		let modal = $(elm).closest('.modal');

		let err = 0;
		$.map( $(modal).find('[data-required=1]'), function(ipt) {
			if ( empty( $(ipt).val() ) ) {
				$(ipt).parent().addClass('has-error');
				err++;
			} else {
				$(ipt).parent().removeClass('has-error');
			}
		});

		if ( err > 0 ) {
			bootbox.alert( 'Harap lengkapi data terlebih dahulu.' );
			return;
		}

		let noreg = $(modal).find('[name=noreg]').val();

		let params = {
			'noreg': noreg,
		};

		$.ajax({
			url : 'transaksi/TutupSiklus/cek_lhk',
			data : {
				'params' :  params
			},
			type : 'POST',
			dataType : 'JSON',
			beforeSend : function(){ showLoading('Cek data LHK . . .'); },
			success : function(data){
				hideLoading();
				if ( data.status == 1 ) {
					bootbox.confirm( 'Apakah anda yakin ingin menutup siklus pada noreg <b>' + noreg + '</b> ?', function(result) {
						if ( result ) {
							tutupSiklus.simpan();
						}
					});
				} else {
					bootbox.alert( data.message );
				}
			},
		});
	}, // end - cekLhk

	simpan: function() {
		let modal = $('#modal-tutup-siklus');

		let params = {
			'noreg': $(modal).find('[name=noreg]').val(),
			'tgl_docin': $(modal).find('[name=tgl_docin]').val(),
			'tgl_tutup_siklus': dateSQL( $(modal).find('[name=tgl_tutup_siklus]').data('DateTimePicker').date() ),
		};

		$.ajax({
			url : 'transaksi/TutupSiklus/simpan',
			data : {
				'params' :  params
			},
			type : 'POST',
			dataType : 'JSON',
			beforeSend : function(){ showLoading('Menyimpan . . .'); },
			success : function(data){
				hideLoading();
				if ( data.status == 1 ) {
					$(modal).modal('hide');
					bootbox.alert( data.message, function(){
						tutupSiklus.get_lists();
					});
				} else {
					bootbox.alert( data.message );
				}
			},
		});
	}, // end - simpan

	hapus: function(elm) {
		let noreg = $(elm).data('noreg');

		let params = {
			'noreg': noreg,
		};

		$.ajax({
			url : 'transaksi/TutupSiklus/cek_hapus',
			data : {
				'params' :  params
			},
			type : 'POST',
			dataType : 'JSON',
			beforeSend : function(){ showLoading('Cek data . . .'); },
			success : function(data){
				hideLoading();
				if ( data.status == 1 ) {
					bootbox.confirm( 'Apakah anda yakin ingin menghapus data tutup siklus pada noreg <b>' + noreg + '</b> ?', function(result) {
						if ( result ) {
							tutupSiklus.eksekusiHapus(noreg);
						}
					});
				} else {
					bootbox.alert( data.message );
				}
			},
		});
	}, // end - hapus

	eksekusiHapus: function(noreg) {
		let params = {
			'noreg': noreg,
		};

		$.ajax({
			url : 'transaksi/TutupSiklus/delete',
			data : {
				'params' :  params
			},
			type : 'POST',
			dataType : 'JSON',
			beforeSend : function(){ showLoading('Menghapus . . .'); },
			success : function(data){
				hideLoading();
				if ( data.status == 1 ) {
					bootbox.alert( data.message, function(){
						tutupSiklus.get_lists();
					});
				} else {
					bootbox.alert( data.message );
				}
			},
		});
	}, // end - eksekusiHapus
};

$(document).ready(function(){
	tutupSiklus.start_up();
});
