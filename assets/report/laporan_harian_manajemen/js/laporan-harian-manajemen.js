var lhm = {
	startUp: function() {
		lhm.settingUp();
	}, // end - startUp

	settingUp: function() {
		$('[name=tanggal]').datetimepicker({
            locale: 'id',
            format: 'DD MMM Y'
        });
	}, // end - settingUp

	copyText: function() {
        var lines = [];
        var $data = $('div.data');

        var getText = function($el) {
            return $el.clone().children().remove().end().text();
        };

        $data.find('[class*="no-padding"]').each(function() {
            if ( $(this).hasClass('hide') ) {
                return true;
            }
            if ( $(this).is('#btn-copy-lhm') ) {
                return true;
            }
            var t = getText( $(this) ).replace(/\u00a0/g, ' ');
            if ( t.trim() === '' ) {
                lines.push( '' );
            } else {
                lines.push( t.trim() );
            }
        });

        var text = lines.join('\n');

        if ( navigator.clipboard && navigator.clipboard.writeText ) {
            navigator.clipboard.writeText( text ).then(function() {
                bootbox.alert('Data berhasil di-copy.');
            }, function() {
                lhm.fallbackCopy( text );
            });
        } else {
            lhm.fallbackCopy( text );
        }
    },

    fallbackCopy: function(text) {
        var $ta = $('<textarea>').val( text ).css({ position: 'fixed', opacity: 0 });
        $('body').append( $ta );
        $ta.focus().select();
        try {
            document.execCommand('copy');
            bootbox.alert('Data berhasil di-copy.');
        } catch (e) {
            bootbox.alert('Gagal copy data.');
        }
        $ta.remove();
    },

	getLists: function() {
        var err = 0;
        $.map( $('[data-required=1]'), function (ipt) {
            if ( empty( $(ipt).val() ) ) {
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
				'tanggal': dateSQL( $('#Tanggal').data('DateTimePicker').date() )
			};

			$.ajax({
                url : 'report/LaporanHarianManajemen/getLists',
                data : {
                    'params' : params
                },
                type : 'POST',
                dataType : 'JSON',
                beforeSend : function(){ showLoading(); },
                success : function(data){
                	hideLoading();

                    if ( data.status == 1 ) {
                        $('div.data').html( data.html );
                    } else {
                        bootbox.alert( data.message );
                    }
                }
            });
		}
    }, // end - getLists
};

lhm.startUp();