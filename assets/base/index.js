'use strict';

$(function(){
  $.ajaxSetup({
    statusCode:{
      401 : function(data){
        bootbox.alert(data);
      },
      403 : function(xhr, status, text){
        bootbox.alert(text,function(){
          window.location.href='user/Login/login';
        })
      },
    },
    error:function(xhr, status, text){
      var pesan = xhr.responseText;
      hideLoading();
      bootbox.alert('Terjadi error di server \n'+ pesan,function(){});
    }
  });

  /* update lebar td pada tbody supaya bisa fixheader */
  var tb = $('table.fixed-header>tbody');
  var _maxHeight = 370 ;
  if($('table.fixed-header>tbody').height() >= _maxHeight){
    $('table.fixed-header>thead>tr:last>th').each(function(i){
        var _idClass = $(this).data('id');
        tb.find('td.'+_idClass).css({
          'min-width' : $(this).outerWidth()
        });
    });
  }
  $('input[name$=Date]').datepicker({
    dateFormat : 'dd M yy',
	   locale:'id',
    onSelect: function(date) {
      var _n = $(this).attr('name');
      if(_n == 'startDate'){
        $('input[name=endDate]').datepicker('option','minDate',date);
      }else{
        $('input[name=startDate]').datepicker('option','maxDate',date);
      }
    },
  });

  $('[data-tipe=date]').datepicker({
    dateFormat : 'dd M yy',
	  locale:'id',
    changeMonth: true,
    changeYear: true,
  //  dateFormat : 'yy-mm-dd',
  });

  $('[data-tipe=month]').MonthPicker({
    Button: false,
    MonthFormat: 'MM, yy',
    locale:'id',
  });

  $('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal], [data-tipe=decimal3],[data-tipe=decimal4], [data-tipe=number]').each(function(){
    $(this).priceFormat(Config[$(this).data('tipe')]);
  });

  $('[data-tipe=alpha-numeric]').keyup(function() {
      if (this.value.match(/[^a-zA-Z0-9]/g)) {
          this.value = this.value.replace(/[^a-zA-Z0-9]/g, '');
      }
  });

  $(document).on("hidden.bs.modal", ".bootbox.modal", function (e) {
      if ( $('.bootbox.modal.show').length > 0 ) {
        $("body").addClass("modal-open");
      };
  });

  // $(document).on("show.bs.modal", ".bootbox.modal", function (e) {
  //     $('.bootbox.modal').removeClass('fade');
  //     $('.bootbox.modal').find('.modal-dialog').removeClass('modal-lg');    
  // });

  $(".tu-float-btn-left").addClass('toggled');

  $(function () {
      $("[name=startDate]").datetimepicker();
      $("[name=endDate]").datetimepicker({
          useCurrent: false //Important! See issue #1075
      });
      $("[name=startDate]").on("dp.change", function (e) {
          var minDate = dateSQL($("[name=startDate]").data("DateTimePicker").date())+' 00:00:00';
          if ( !empty(minDate) && minDate != 'Invalid date 00:00:00' ) {
            $("[name=endDate]").data("DateTimePicker").minDate(moment(new Date(minDate)));
          }
          // $("[name=endDate]").data("DateTimePicker").minDate(e.date);
          // $("[name=endDate]").data("DateTimePicker").date(e.date);
      });
      $("[name=endDate]").on("dp.change", function (e) {
          var maxDate = dateSQL($("[name=endDate]").data("DateTimePicker").date())+' 23:59:59';
          if ( !empty(maxDate) && maxDate != 'Invalid date 23:59:59' ) {
            $('[name=startDate]').data("DateTimePicker").maxDate(moment(new Date(maxDate)));
          }
          // $('[name=startDate]').data("DateTimePicker").maxDate(e.date);
      });
  });

  $('.check_all').change(function() {
    var data_target = $(this).data('target');

    if ( this.checked ) {
      $.map( $('.check[target='+data_target+']'), function(checkbox) {
        $(checkbox).prop( 'checked', true );
      });
    } else {
      $.map( $('.check[target='+data_target+']'), function(checkbox) {
        $(checkbox).prop( 'checked', false );
      });
    }
  });

  $('.check').change(function() {
    var target = $(this).attr('target');

    var length = $('.check[target='+target+']').length;
    var length_checked = $('.check[target='+target+']:checked').length;

    if ( length == length_checked ) {
      $('.check_all').prop( 'checked', true );
    } else {
      $('.check_all').prop( 'checked', false );
    }
  });

  /* set default filter content */
  // filter_content($('input:first'));

  // $('input').keyboard({
  //   usePreview: false,
  //   useCombos: false,
  //   autoAccept: true,
  //   layout: 'custom',
  //   customLayout: {
  //     'normal': [
  //       '` 1 2 3 4 5 6 7 8 9 0 - = {del} {b}',
  //       '{tab} q w e r t y u i o p [ ] \\',
  //       'a s d f g h j k l ; \' {enter}',
  //       '{shift} z x c v b n m , . / {shift}',
  //       '{accept} {space} {left} {right} {undo:Undo} {redo:Redo}'
  //     ],
  //     'shift': [
  //       '~ ! @ # $ % ^ & * ( ) _ + {del} {b}',
  //       '{tab} Q W E R T Y U I O P { } |',
  //       'A S D F G H J K L : " {enter}',
  //       '{shift} Z X C V B N M < > ? {shift}',
  //       '{accept} {space} {left} {right} {undo:Undo} {redo:Redo}'
  //     ]
  //   },
  //   display: {
  //     del: '\u2326:Delete',
  //     redo: '↻',
  //     undo: '↺'
  //   },
  //   autoAccept: true,
  //   change: function(event, keyboard, el) {
  //     $(el).trigger("input"); // 'input' event triggered on textarea
  //   }
  // });
})
