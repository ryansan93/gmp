var ncx = {
	start_up: function () {
		ncx.setBindSHA1();
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

	download_template: function() {
		var tipe = $('#tipe_import_nama_coretax').val();

		if ( empty(tipe) ) {
			bootbox.alert('Harap pilih Jenis Data terlebih dahulu.');
		} else {
			window.open('import/NamaCoretax/download_template?tipe='+tipe, '_blank');
		}
	}, // end - download_template

	upload: function() {
		var tipe = $('#tipe_import_nama_coretax').val();
		var file_tmp = $('.file_lampiran').get(0).files[0];

		if ( empty(tipe) ) {
			bootbox.alert('Harap pilih Jenis Data terlebih dahulu.');
		} else if ( !empty($('.file_lampiran').val()) ) {
			var formData = new FormData();
	        formData.append('file', file_tmp);
	        formData.append('tipe', tipe);

			$.ajax({
				url: 'import/NamaCoretax/upload',
				dataType: 'json',
	            type: 'post',
	            async: false,
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

ncx.start_up();
