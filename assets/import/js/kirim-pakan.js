var kp = {
	start_up: function () {
		kp.setting_up();
	}, // end - start_up

	setBindSHA1 : function(){
        $('input:file').off('change.sha1');
        $('input:file').on('change.sha1',function(){
            var elm = $(this);
            var file = elm.get(0).files[0];
            elm.attr('data-sha1', '');
            sha1_file(file).then(function (sha1) {
                elm.attr('data-sha1', sha1);
            });
        });
    }, // end - setBindSHA1

    showNameFile : function(elm, isLable = 1) {
        var _label = $(elm).closest('label');
        var _a = _label.prev('a[name=dokumen]');
        _a.removeClass('hide');
        // var _allowtypes = $(elm).data('allowtypes').split('|');
        var _dataName = $(elm).data('name');
        var _allowtypes = ['xlsx'];
        var _type = $(elm).get(0).files[0]['name'].split('.').pop();
        var _namafile = $(elm).val();
        var _temp_url = URL.createObjectURL($(elm).get(0).files[0]);
        _namafile = _namafile.substring(_namafile.lastIndexOf("\\") + 1, _namafile.length);

        if (in_array(_type, _allowtypes)) {
            if (isLable == 1) {
                if (_a.length) {
                    _a.attr('title', _namafile);
                    _a.attr('href', _temp_url);
                    if ( _dataName == 'name' ) {
                        $(_a).text( _namafile );  
                    }
                }
            } else if (isLable == 0) {
                $(elm).closest('label').attr('title', _namafile);
            }
            $(elm).attr('data-filename', _namafile);
        } else {
            $(elm).val('');
            $(elm).closest('label').attr('title', '');
            $(elm).attr('data-filename', '');
            _a.addClass('hide');
            bootbox.alert('Format file tidak sesuai. Mohon attach ulang.');
        }
    }, // end - showNameFile

	setting_up: function(){
		$('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal], [data-tipe=decimal3],[data-tipe=decimal4], [data-tipe=number]').each(function(){
            $(this).priceFormat(Config[$(this).data('tipe')]);
        });

        $('.date').datetimepicker({
			locale: 'id',
            format: 'DD MMM Y'
		});

		kp.setBindSHA1();
	}, // end - setting_up

	download: function() {
		window.open('uploads/import_file_example/kirim_pakan_mgb.xlsx', '_blank');
	}, // end - download

	upload: function() {
		var file_tmp = $('.file_lampiran').get(0).files[0];

		if ( !empty($('.file_lampiran').val()) ) {
			var formData = new FormData();
	        formData.append('file', file_tmp);

			$.ajax({
				url: 'import/KirimPakan/upload',
				dataType: 'json',
	            type: 'post',
	            async:false,
	            processData: false,
	            contentType: false,
	            data: formData,
				beforeSend: function() {
					showLoading();
				},
				success: function(data) {
					hideLoading();
					if ( data.status == 1 ) {
						bootbox.alert(data.message, function() {
							location.reload();
						});
					} else {
						bootbox.alert(data.message);
					};
				},
		    });
		} else {
			bootbox.alert('Harap isi lampiran terlebih dahulu.');
		}
	}, // end - upload
};

kp.start_up();