var div_pagination = $('div#pagination-ry');

var first_btn_pagination = '<span class="angle-double-left btn-page-first"><i class="fa fa-angle-double-left"></i></span>';
var prev_btn_pagination = '<span class="btn-page-prev"><i class="fa fa-angle-left"></i></span>';
var next_btn_pagination = '<span class="btn-page-next"><i class="fa fa-angle-right"></i></span>';
var last_btn_pagination = '<span class="angle-double-right btn-page-last"><i class="fa fa-angle-double-right"></i></span>';

var jml_page_btn_view = 5;

var _btn_pagination = '';
var pagination = {
	set_pagination: function (row, page, list, action) {
		var btn_pagination = '';
		for (var i = 0; i < page; i++) {
			var json = JSON.stringify( list[i] );

			var no = i+1;
			var hide = null;
			if ( i >= jml_page_btn_view ) {
				hide = 'hide';
			}
			var btn = '<span class="btn-page '+hide+'" data-list_id_page='+json+'>'+no+'</span>';
			btn_pagination += btn;
		}

		_btn_pagination = first_btn_pagination+prev_btn_pagination+btn_pagination+next_btn_pagination+last_btn_pagination;
		$(div_pagination).addClass('pull-right');
		$(div_pagination).html( _btn_pagination );

		$('span.btn-page').on('click', function() {
			pagination.cek_btn_aktif( $(this) );
			action(this);
		});

		$('span.btn-page-first').on('click', function() {
			$('span.btn-page').attr('data-aktif', 0);
			$('span.btn-page:first').attr('data-aktif', 1);

			$('span.btn-page').addClass('hide');
			var jml_span = 0;
			$.map( $('span.btn-page'), function(span) {
				if ( jml_span < jml_page_btn_view ) {
					$(span).removeClass('hide');
				}

				jml_span++;
			});

			action($('span.btn-page:first'));
		});

		$('span.btn-page-last').on('click', function() {
			$('span.btn-page').attr('data-aktif', 0);
			$('span.btn-page:last').attr('data-aktif', 1);

			$('span.btn-page').addClass('hide');
			var jml_span = 0;
			$.map( $('span.btn-page'), function(span) {
				if ( jml_span >= (page-5) && jml_span < page ) {
					$(span).removeClass('hide');
				}

				jml_span++;
			});

			action($('span.btn-page:last'));
		});

		$('span.btn-page-prev').on('click', function() {
			var span_aktif = $('span.btn-page[data-aktif=1]');
			var prev_span = $(span_aktif).prev();

			if ( $(prev_span).hasClass('btn-page') ) {
				pagination.cek_btn_aktif( prev_span );
				action(prev_span);
			}
		});

		$('span.btn-page-next').on('click', function() {
			var span_aktif = $('span.btn-page[data-aktif=1]');
			var next_span = $(span_aktif).next();

			if ( $(next_span).hasClass('btn-page') ) {
				pagination.cek_btn_aktif( next_span );
				action(next_span);
			}
		});

		if ( list.length > 0 ) {
			if ( $('span.btn-page[data-aktif=1]').length > 0 ) {
				$('span.btn-page[data-aktif=1]').click();
			} else {
				$('span.btn-page:first').click();
			}
		} else {
			action($('span.btn-page-first'));
		}
	}, // end - start_up;

	cek_btn_aktif: function(span) {
		var next_btn = $(span).next();
		var prev_btn = $(span).prev();

		var jml_prev_btn = 0;
		while( prev_btn.hasClass('btn-page') ) {
			jml_prev_btn++;
			prev_btn = $(prev_btn).prev();
		}

		var jml_next_btn = 0;
		while( next_btn.hasClass('btn-page') ) {
			jml_next_btn++;
			next_btn = $(next_btn).next();
		}

		var _jml_btn = 0;

		$batas = 4;
		if ( jml_prev_btn > 1 && jml_next_btn > 1 ) {
			$batas = 2;

			$('span.btn-page').addClass('hide');
			$('span.btn-page').attr('data-aktif', 0);
			$(span).attr('data-aktif', 1);
			$(span).removeClass('hide');

			var _prev_btn = $(span).prev();
			var _jml_btn_prev = 0;
			while( _prev_btn.hasClass('btn-page') && _jml_btn < 4 && _jml_btn_prev < $batas ) {
				$(_prev_btn).removeClass('hide');
				_jml_btn++;
				_jml_btn_prev++;
				_prev_btn = $(_prev_btn).prev();
			}
			var _next_btn = $(span).next();
			var _jml_btn_next = 0;
			while( _next_btn.hasClass('btn-page') && _jml_btn < 4 && _jml_btn_next < $batas ) {
				$(_next_btn).removeClass('hide');
				_jml_btn++;
				_jml_btn_next++;
				_next_btn = $(_next_btn).next();
			}
		} else {
			$('span.btn-page').attr('data-aktif', 0);
			$(span).attr('data-aktif', 1);
			$(span).removeClass('hide');
		}
	}, // end - cek_btn_aktif
};