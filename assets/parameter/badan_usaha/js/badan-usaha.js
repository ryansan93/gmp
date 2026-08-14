var bu = {
	start_up : function () {
		bu.getLists();
	}, // end - start_up

	getLists : function () {
		var dContent = $('tbody');

		$.ajax({
            url : 'parameter/BadanUsaha/getLists',
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
		$.get('parameter/BadanUsaha/addForm',{
        },function(data){
            var _options = {
                className : 'large',
                message : data,
                addClass : 'form',
            };
            bootbox.dialog(_options).bind('shown.bs.modal', function(){
                $(this).find('.modal-dialog').css({'width': '50%', 'max-width': '100%'});

                $('input').keyup(function(){
                    $(this).val($(this).val().toUpperCase());
                });
            });
        },'html');
	}, // end - addForm

	editForm : function (elm) {
		var id = $(elm).attr('data-id');

		$.get('parameter/BadanUsaha/editForm',{
			'id' : id
        },function(data){
            var _options = {
                className : 'large',
                message : data,
                addClass : 'form',
            };
            bootbox.dialog(_options).bind('shown.bs.modal', function(){
                $(this).find('.modal-dialog').css({'width': '50%', 'max-width': '100%'});

                $('input').keyup(function(){
                    $(this).val($(this).val().toUpperCase());
                });
            });
        },'html');
	}, // end - editForm

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
			bootbox.confirm('Apakah anda yakin ingin menyimpan data badan usaha ?', function (result) {
				if ( result ) {
					var params = {
						'nama_badan_usaha' : $('input.nama_badan_usaha').val().toUpperCase(),
						'singkatan' : $('input.singkatan').val().toUpperCase(),
						'status_hukum' : $('select.status_hukum').val(),
						'is_terbuka' : $('input.is_terbuka').is(':checked') ? 1 : 0
					};

					$.ajax({
						url : 'parameter/BadanUsaha/save',
						data : {'params' : params},
						type : 'POST',
						dataType : 'JSON',
						beforeSend : function(){ showLoading(); },
						success : function(data){
							hideLoading();
							if (data.status) {
								bootbox.alert(data.message, function(){
									bu.getLists();
									bootbox.hideAll();
								});
							} else {
								bootbox.alert(data.message);
							}
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
			bootbox.confirm('Apakah anda yakin ingin meng-update data badan usaha ?', function (result) {
				if ( result ) {
					var id = $(elm).data('id');

					var params = {
						'id_badan_usaha' : id,
						'nama_badan_usaha' : $('input.nama_badan_usaha').val().toUpperCase(),
						'singkatan' : $('input.singkatan').val().toUpperCase(),
						'status_hukum' : $('select.status_hukum').val(),
						'is_terbuka' : $('input.is_terbuka').is(':checked') ? 1 : 0
					};

					$.ajax({
						url : 'parameter/BadanUsaha/edit',
						data : {'params' : params},
						type : 'POST',
						dataType : 'JSON',
						beforeSend : function(){ showLoading(); },
						success : function(data){
							hideLoading();
							if (data.status) {
								bootbox.alert(data.message, function(){
									bu.getLists();
									bootbox.hideAll();
								});
							} else {
								bootbox.alert(data.message);
							}
						}
					});
				};
			});
		};
	}, // end - edit
};

bu.start_up();
