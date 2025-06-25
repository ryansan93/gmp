var tu_page_index = 0;
var tu_page_hide_button = false;
var table_target = '';
var TUPageTable = {

    pages: [],

    setViewDefault : function(index = 0){
      tu_page_index = index;
    },

    start: function() {
        $('.tu-table-prev').click(function(){
            TUPageTable.prev();
        });

        $('.tu-table-next').click(function(){
            TUPageTable.next();
        });

        TUPageTable.showHideColumn(tu_page_index, TUPageTable.pages);
    },

    destroy : function () {
      $('.tu-table-prev').unbind();
      $('.tu-table-next').unbind();
    },

    setTableTarget : function(e){
      table_target = e;
    },

    setPages: function(t_pages = []) {
        TUPageTable.pages = t_pages;
    },

    setHideButton : function( _hide = false ){
        tu_page_hide_button = _hide;
    },

    prev: function() {
        if (tu_page_index > 0) {
            TUPageTable.showHideColumn(--tu_page_index, TUPageTable.pages);
        }
    },

    next: function() {
        if (tu_page_index < (TUPageTable.pages.length - 1)) {
            TUPageTable.showHideColumn(++tu_page_index, TUPageTable.pages);
        }
    },

    showHideColumn: function(tu_page_index, pages) {

        var v_show = [];
        var v_hide = [];
        $.each(pages, function(k, v) {

            if (k == tu_page_index) {
                v_show.push('.' + v);
            } else {
                v_hide.push('.' + v);
            }
        });

        var e_show = v_show.join(', ');
        var e_hide = v_hide.join(', ');

        if (empty(table_target)) {
          $(e_hide).hide();
          $(e_show).show();
        }else{
          $(table_target).find(e_hide).hide();
          $(table_target).find(e_show).show();
        }

        if ( tu_page_hide_button ) {
            TUPageTable.setDisaledButton();
        }

    },

    setDisaledButton : function(){

        if ( tu_page_index > 0 ) {
            $('.tu-table-prev').show();
        } else {
            $('.tu-table-prev').hide();
        }

        if ( tu_page_index < (TUPageTable.pages.length - 1) ) {
            $('.tu-table-next').show();
        } else {
            $('.tu-table-next').hide();
        }
    },

    goToPage : function(page){
      tu_page_index = TUPageTable.pages.indexOf(page);
      if ( tu_page_index > 0 && tu_page_index < TUPageTable.pages.length ) {
          TUPageTable.showHideColumn( tu_page_index , TUPageTable.pages);
      }
    },

    goToPageIndex : function(iPage){
      tu_page_index = iPage;
      if ( tu_page_index > 0 && tu_page_index < TUPageTable.pages.length ) {
          TUPageTable.showHideColumn( tu_page_index , TUPageTable.pages);
      }
    },

    onClickNext : function(func){
      $('.tu-table-next').click(func);
    },

    onClickPrev : function(func){
      $('.tu-table-prev').click(func);
    }

};
