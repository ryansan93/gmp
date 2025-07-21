var jns = {
	start_up : function () {
		jns.getLists();
	}, // end - start_up

	getLists : function () {
		var dContent = $('tbody');

		$.ajax({
            url : 'parameter/Jenis/getLists',
            data : {},
            type : 'GET',
            dataType : 'HTML',
            beforeSend : function(){ App.showLoaderInContent(dContent); },
            success : function(html){
                App.hideLoaderInContent(dContent, html);
            }
        });
	}, // end - getLists

	addForm : function () {
		$.get('parameter/Jenis/addForm',{
        },function(data){
            var _options = {
                className : 'large',
                message : data,
                addClass : 'form',
            };
            bootbox.dialog(_options).bind('shown.bs.modal', function(){
                $('input').keyup(function(){
                    $(this).val($(this).val().toUpperCase());
                });

                $('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal]').each(function(){
                    $(this).priceFormat(Config[$(this).data('tipe')]);
                });
            });
        },'html');
	}, // end - add_form

	editForm : function (elm) {
		var id = $(elm).attr('data-id');

		$.get('parameter/Jenis/editForm',{
			'id' : id
        },function(data){
            var _options = {
                className : 'large',
                message : data,
                addClass : 'form',
            };
            bootbox.dialog(_options).bind('shown.bs.modal', function(){
                $('input').keyup(function(){
                    $(this).val($(this).val().toUpperCase());
                });

                $('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal]').each(function(){
                    $(this).priceFormat(Config[$(this).data('tipe')]);
                });
            });
        },'html');
	}, // end - editForm

	cekKodeJenis: function(_kode, _id, callback) {
		var params = {
            'kode': _kode,
            'id': _id
        };

        $.ajax({
            url: 'parameter/Jenis/cekKodeJenis',
            dataType: 'json',
            type: 'post',
            data: {
                'params': params
            },
            beforeSend: function() { showLoading('Cek kode jenis . . .'); },
            success: function(data) {
                hideLoading();

                var status = data.status;
                var keterangan = data.message;

                callback( {'status': status, 'ket': keterangan} );
            },
        });
	}, // end - cekKodeJenis

	save: function () {
		var err = 0;

		$.map( $('[data-required=1]'), function (ipt) {
			if ( empty($(ipt).val()) ) {
				$(ipt).parent().addClass('has-error');
				err++;
			} else {
				$(ipt).parent().removeClass('has-error');
			};
		});

		if ( err > 0 ) {
			bootbox.alert('Harap lengkapi data yang anda input.');
		} else {
			bootbox.confirm('Apakah anda yakin ingin menyimpan data jenis ?', function (result) {
				if ( result ) {
					var kode = $('input.kode').val().toUpperCase();
					var nama = $('input.nama').val().toUpperCase();

					jns.cekKodeJenis(kode, null, function(data) {
						var status = data.status;
						var keterangan = data.ket;

						if ( status == 0 ) {
							var params = {
								'kode' : kode,
								'nama' : nama
							};

							$.ajax({
								url : 'parameter/Jenis/save',
								data : {'params' : params},
								type : 'POST',
								dataType : 'JSON',
								beforeSend : function(){ showLoading(); },
								success : function(data){
									hideLoading();
									if (data.status) {
										bootbox.alert(data.message, function(){
											jns.getLists();
											bootbox.hideAll();
										});
									} else {
										bootbox.alert(data.message);
									}
								}
							});
						} else {
							bootbox.alert( keterangan );
						}
					});
				};
			});
		};
	}, // end - save

	edit : function (elm) {
		var err = 0;

		$.map( $('[data-required=1]'), function (ipt) {
			if ( empty($(ipt).val()) ) {
				$(ipt).parent().addClass('has-error');
				err++;
			} else {
				$(ipt).parent().removeClass('has-error');
			};
		});

		if ( err > 0 ) {
			bootbox.alert('Harap lengkapi data yang anda input.');
		} else {
			bootbox.confirm('Apakah anda yakin ingin meng-update data jenis ?', function (result) {
				if ( result ) {
					var id = $(elm).data('id');
					var kode = $('input.kode').val().toUpperCase();
					var nama = $('input.nama').val().toUpperCase();

					jns.cekKodeJenis(kode, null, function(data) {
						var status = data.status;
						var keterangan = data.ket;

						if ( status == 0 ) {
							var params = {
								'id' : id,
								'kode' : kode,
								'nama' : nama
							};

							$.ajax({
								url : 'parameter/Jenis/edit',
								data : {'params' : params},
								type : 'POST',
								dataType : 'JSON',
								beforeSend : function(){ showLoading(); },
								success : function(data){
									hideLoading();
									if (data.status) {
										bootbox.alert(data.message, function(){
											jns.getLists();
											bootbox.hideAll();
										});
									} else {
										bootbox.alert(data.message);
									}
								}
							});
						} else {
							bootbox.alert( keterangan );
						}
					});
				};
			});
		};
	}, // end - edit
};

jns.start_up();