var bbp = {
	start_up: function () {
        // bbp.set_table_page('.tbl_ktp');
        bbp.setting_up();
	},

 	// set_table_page : function(tbl_id){
  //       let _t_rdim = TUPageTable;
  //       _t_rdim.destroy();
  //       _t_rdim.setTableTarget(tbl_id);
  //       _t_rdim.setPages(['page1', 'page2']);
  //       _t_rdim.setHideButton(true);
  //       _t_rdim.onClickNext(function(){
  //           // console.log('Log onClickNext');
  //       });
  //       _t_rdim.start();
  //   }, // end - set_table_page

	setting_up: function() {
		$('.jenis').select2();
		$('.jenis').on('select2:select', function () {
		    bbp.getRekening();
		});

		$('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal], [data-tipe=decimal3],[data-tipe=decimal4], [data-tipe=number]').each(function(){
			$(this).priceFormat(Config[$(this).data('tipe')]);
		});

		$("#StartDate").datetimepicker({
            locale: 'id',
            format: 'DD MMM Y'
        });

        $("#EndDate").datetimepicker({
            locale: 'id',
            format: 'DD MMM Y'
        });

        $("#StartDate").on("dp.change", function (e) {
            var minDate = dateSQL($("#StartDate").data("DateTimePicker").date())+' 00:00:00';
            $("#EndDate").data("DateTimePicker").minDate(moment(new Date(minDate)));
        });
        var start_date = $("#StartDate").find('input').data('tgl');
        if ( !empty(start_date) && empty($("#StartDate").find('input').val()) ) {
        	$("#StartDate").data('DateTimePicker').date(moment(new Date(start_date)));
        }
	}, // end - setting_up

	getRekening: function() {
    	var jurnal_trans_id = $('.jenis').val();

    	if ( !empty(jurnal_trans_id) ) {
    		$.ajax({
	            url: 'report/BukuBankPajak/getRekening',
	            data: { 'jurnal_trans_id': jurnal_trans_id },
	            type: 'POST',
	            dataType: 'JSON',
	            beforeSend: function(){ showLoading() },
	            success: function(data){
	            	$('.rekening').removeAttr('disabled');

	            	var option = '<option value="all">ALL</option>';
	            	if ( !empty(data.content) && data.content.length > 0 ) {
	            		for (var i = 0; i < data.content.length; i++) {
	            			option += '<option value="'+data.content[i].id+'">'+data.content[i].nama+'</option>';
	            		}
	            	}

	            	$('.rekening').removeAttr('disabled');
    				$('.rekening').html(option);

    				$('.rekening').select2({multiple: true, placeholder: 'Pilih Rekening'}).on("select2:select", function (e) {
						var rekening = $('.rekening').select2().val();

						for (var i = 0; i < rekening.length; i++) {
							if ( rekening[i] == 'all' ) {
								$('.rekening').select2().val('all').trigger('change');

								i = rekening.length;

							}
						}

						$('.rekening').next('span.select2').css('width', '100%');
					});

					$('.rekening').next('span.select2').css({'width': '100%'});

	                hideLoading();
	            }
	        });
    	} else {
    		var option = '<option value="">Pilih Noreg</option>';

    		$('.rekening').select2('destroy');

    		$('.rekening').attr('disabled', 'disabled');
    		$('.rekening').html(option);
    		$('.rekening').val('');

    		$('.rekening').removeAttr('aria-hidden');
    		$('.rekening').removeAttr('data-select2-id');
    		$('.rekening').removeAttr('multiple');
    		$('.rekening').removeAttr('tabindex');
    	}
    }, // end - getRekening

	getLists: function () {
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
				'start_date' : dateSQL($('#StartDate').data('DateTimePicker').date()),
				'end_date' : dateSQL($('#EndDate').data('DateTimePicker').date()),
				'rekening' : $('.rekening').val(),
				'jurnal_trans_id': $('.jenis').val()
			};

			$.ajax({
	            url : 'report/BukuBankPajak/getLists',
	            data : {
	            	'params' : params
	            },
	            type : 'GET',
	            dataType : 'HTML',
	            beforeSend : function(){
	            	showLoading();
	            },
	            success : function(html){
	                $('table.tbl_laporan tbody').html( html );

	                hideLoading();
	            }
	        });
		};
	}, // end - getLists

	preview_file_attachment: function(elm) {
    	var div_attachment = $(elm).prev('div.attachment');

    	var data_url = $(elm).attr('data-url');

		var judul = $(elm).data('title');
		var _url = [];
    	if ( empty(data_url) ) {
    		if ( $(div_attachment).length > 0 ) {
		    	var files = $(div_attachment).find('.file_lampiran')[0].files;
		    	
				for (var i = 0; i < files.length; i++) {
					_temp_url = URL.createObjectURL(files[i]);

					_url.push(_temp_url);
				}
    		}
    	} else {
    		var _data_url = JSON.parse(data_url);
    		for(var i in _data_url) {
    			_url.push('uploads/'+_data_url[i]);
    		}
    	}

    	if ( _url.length > 0 ) {
			$.get('report/LHK/preview_file_attachment',{
					'params': _url,
					'judul': judul
				},function(data){
				var _options = {
					className : 'veryWidth',
					message : data,
					size : 'large',
				};
				bootbox.dialog(_options).bind('shown.bs.modal', function(){
					var modal_body = $(this).find('.modal-body');
					var table = $(modal_body).find('table');
					var tbody = $(table).find('tbody');
					if ( $(tbody).find('.modal-body tr').length <= 1 ) {
				        $(this).find('tr #btn-remove').addClass('hide');
				    };

				    $(this).find('button.close').click(function() {
				    	$('div.modal.show').css({'overflow': 'auto'});
				    });
				});
			},'html');
    	} else {
    		bootbox.alert('Tidak ada file yang akan di tampilkan.');
    	}
	}, // end - preview_file_attachment

	nekropsi: function(elm) {
    	var id = $(elm).data('id');

		$.get('report/LHK/nekropsi',{
				'id': id
			},function(data){
			var _options = {
				className : 'veryWidth',
				message : data,
				size : 'large',
			};
			bootbox.dialog(_options).bind('shown.bs.modal', function(){
				var modal_body = $(this).find('.modal-body');
				var table = $(modal_body).find('table');
				var tbody = $(table).find('tbody');
				if ( $(tbody).find('.modal-body tr').length <= 1 ) {
			        $(this).find('tr #btn-remove').addClass('hide');
			    };

			    $(this).find('button.close').click(function() {
			    	$('div.modal.show').css({'overflow': 'auto'});
			    });
			});
		},'html');
	}, // end - nekropsi

	solusi: function(elm) {
    	var id = $(elm).data('id');

		$.get('report/LHK/solusi',{
				'id': id
			},function(data){
			var _options = {
				className : 'veryWidth',
				message : data,
				size : 'large',
			};
			bootbox.dialog(_options).bind('shown.bs.modal', function(){
				var modal_body = $(this).find('.modal-body');
				var table = $(modal_body).find('table');
				var tbody = $(table).find('tbody');
				if ( $(tbody).find('.modal-body tr').length <= 1 ) {
			        $(this).find('tr #btn-remove').addClass('hide');
			    };

			    $(this).find('button.close').click(function() {
			    	$('div.modal.show').css({'overflow': 'auto'});
			    });
			});
		},'html');
	}, // end - solusi

	sekat: function(elm) {
    	var id = $(elm).data('id');

		$.get('report/LHK/sekat',{
				'id': id
			},function(data){
			var _options = {
				className : 'veryWidth',
				message : data,
				size : 'large',
			};
			bootbox.dialog(_options).bind('shown.bs.modal', function(){
				var modal_body = $(this).find('.modal-body');
				var modal_content = $(modal_body).closest('.modal-content');
				var modal_dialog = $(modal_content).closest('.modal-dialog');

				$(modal_dialog).css({'width': '30%'})

				var table = $(modal_body).find('table');
				var tbody = $(table).find('tbody');
				if ( $(tbody).find('.modal-body tr').length <= 1 ) {
			        $(this).find('tr #btn-remove').addClass('hide');
			    };

			    $(this).find('button.close').click(function() {
			    	$('div.modal.show').css({'overflow': 'auto'});
			    });
			});
		},'html');
	}, // end - sekat
};

bbp.start_up();