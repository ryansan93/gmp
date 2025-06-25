/**
 * @title : Kumpulan fungsi-fungsi(baca: method) u/ global proses.
 * @author: mursito&prends <02-04@mitraunggassejati.com>
 *
 */


function number_only(e) {
    var pola = "^";
    pola += "[.0-9]*";
    pola += "$";
    rx = new RegExp(pola);

    if (!e.value.match(rx)) {
        if (e.lastMatched) {
            e.value = e.lastMatched;
        } else {
            e.value = "";
        }
    } else {
        e.lastMatched = e.value;
    }
}


function upper_text(elm) {
    elm.value = elm.value.toUpperCase();
}

function number_format(number, decimals, dec_point, thousands_sep) {
    //  discuss at: http://phpjs.org/functions/number_format/
    // original by: Jonas Raoni Soares Silva (http://www.jsfromhell.com)
    // improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // improved by: davook
    // improved by: Brett Zamir (http://brett-zamir.me)
    // improved by: Brett Zamir (http://brett-zamir.me)
    // improved by: Theriault
    // improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // bugfixed by: Michael White (http://getsprink.com)
    // bugfixed by: Benjamin Lupton
    // bugfixed by: Allan Jensen (http://www.winternet.no)
    // bugfixed by: Howard Yeend
    // bugfixed by: Diogo Resende
    // bugfixed by: Rival
    // bugfixed by: Brett Zamir (http://brett-zamir.me)
    //  revised by: Jonas Raoni Soares Silva (http://www.jsfromhell.com)
    //  revised by: Luke Smith (http://lucassmith.name)
    //    input by: Kheang Hok Chin (http://www.distantia.ca/)
    //    input by: Jay Klehr
    //    input by: Amir Habibi (http://www.residence-mixte.com/)
    //    input by: Amirouche
    //   example 1: number_format(1234.56);
    //   returns 1: '1,235'
    //   example 2: number_format(1234.56, 2, ',', ' ');
    //   returns 2: '1 234,56'
    //   example 3: number_format(1234.5678, 2, '.', '');
    //   returns 3: '1234.57'
    //   example 4: number_format(67, 2, ',', '.');
    //   returns 4: '67,00'
    //   example 5: number_format(1000);
    //   returns 5: '1,000'
    //   example 6: number_format(67.311, 2);
    //   returns 6: '67.31'
    //   example 7: number_format(1000.55, 1);
    //   returns 7: '1,000.6'
    //   example 8: number_format(67000, 5, ',', '.');
    //   returns 8: '67.000,00000'
    //   example 9: number_format(0.9, 0);
    //   returns 9: '1'
    //  example 10: number_format('1.20', 2);
    //  returns 10: '1.20'
    //  example 11: number_format('1.20', 4);
    //  returns 11: '1.2000'
    //  example 12: number_format('1.2000', 3);
    //  returns 12: '1.200'
    //  example 13: number_format('1 000,50', 2, '.', ' ');
    //  returns 13: '100 050.00'
    //  example 14: number_format(1e-8, 8, '.', '');
    //  returns 14: '0.00000001'

    number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
    var n = !isFinite(+number) ? 0 : +number,
        prec = !isFinite(+decimals) ? 0 :
        Math.abs(decimals),
        sep = (typeof thousands_sep === 'undefined') ? ',' :
        thousands_sep,
        dec = (typeof dec_point === 'undefined') ? '.' :
        dec_point,
        s = '',
        toFixedFix = function(n, prec) {
            var k = Math.pow(10, prec);
            return '' + (Math.round(n * k) / k).toFixed(prec);
        };
    // Fix for IE parseFloat(0.55).toFixed(0) = 0;
    s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
    if (s[0].length > 3) {
        s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
    }
    if ((s[1] || '').length < prec) {
        s[1] = s[1] || '';
        s[1] += new Array(prec - s[1].length + 1).join('0');
    }
    return s.join(dec);
}

function angkaIndonesia(string_number, dec) {
    return number_format(string_number, dec, ',', '.');
}

function parse_number(string_number, thousands_sep, decimal_sep) {
    string_number = string_number.toString();
    thousands_sep = (typeof thousands_sep == 'undefined') ? '.' : thousands_sep;
    decimal_sep = (typeof decimal_sep == 'undefined') ? ',' : decimal_sep;
    var thousand = new RegExp('\\' + thousands_sep + '', 'g');
    var decimal = new RegExp('\\' + decimal_sep + '', 'g');
    var tanpa_ribuan = string_number.replace(thousand, '');
    var replace_decimal = tanpa_ribuan.replace(decimal, '.');
    return parseFloat(replace_decimal);
}

function dariAngkaIndonesia(string_number) {
    return parse_number(string_number, '.', ',');
}

function empty(data) {
    if (typeof(data) == 'number' || typeof(data) == 'boolean') {
        return false;
    }
    if (typeof(data) == 'undefined' || data === null || data == 'null') {
        return true;
    }
    if (typeof(data.length) != 'undefined') {
        return data.length == 0;
    }
    var count = 0;
    for (var i in data) {
        if (data.hasOwnProperty(i)) {
            count++;
        }
    }
    return count == 0;
}


function in_array(item, arr) {
    if (!arr) {
        return false;
    } else {
        for (var p = 0; p < arr.length; p++) {
            if (item == arr[p]) {
                return true;
            }
        }
        return false;
    }
}

function array_sum(array) {
    //  discuss at: http://phpjs.org/functions/array_sum/
    // original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // bugfixed by: Nate
    // bugfixed by: Gilbert
    // improved by: David Pilia (http://www.beteck.it/)
    // improved by: Brett Zamir (http://brett-zamir.me)
    //   example 1: array_sum([4, 9, 182.6]);
    //   returns 1: 195.6
    //   example 2: total = []; index = 0.1; for (y=0; y < 12; y++){total[y] = y + index;}
    //   example 2: array_sum(total);
    //   returns 2: 67.2

    var key, sum = 0;

    if (array && typeof array === 'object' && array.change_key_case) { // Duck-type check for our own array()-created PHPJS_Array
        return array.sum.apply(array, Array.prototype.slice.call(arguments, 0));
    }

    // input sanitation
    if (typeof array !== 'object') {
        return null;
    }

    for (key in array) {
        if (!isNaN(parseFloat(array[key]))) {
            sum += parseFloat(array[key]);
        }
    }

    return sum;
}

function selisihHari(d1, d2) {
    var _t1 = d1.getTime();
    var _t2 = d2.getTime();
    return parseInt((_t2 - _t1) / (24 * 3600 * 1000));
}

function createTree(arr) {
    if (!empty(arr)) {
        var tmp = '<ul>';
        for (var i in arr) {
            var val = arr[i];
            if (typeof(val) === 'string') {
                tmp += '<li><a href="#">' + val + '</a></li>';
            } else {
                var _id = getRandomInt(1001, 2000);
                tmp += '<li><input type="checkbox" id="' + _id + '" /><label for="' + _id + '">' + i + '</label>';
                tmp += createTree(val);
            }
        }
        tmp += '</ul>';
        return tmp;
    }
}

/**
 * Returns a random integer between min (inclusive) and max (inclusive)
 * Using Math.round() will give you a non-uniform distribution!
 */
function getRandomInt(min, max) {
    return Math.floor(Math.random() * (max - min + 1)) + min;
}

function filter_content(elm) {
    var _tr = $(elm).closest('tr');
    var _tbody = _tr.closest('table').find('tbody');
    var _content, _target;
    _tbody.find('tr').show();
    _tr.find('input,select').each(function() {
        _content = $(this).val().toUpperCase();
        if (!empty(_content)) {
            _target = $(this).data('target');
            $.map($(_tbody).find('tr td.'+_target), function(td){
                var tr = $(td).closest('tr');
                var td_val = $(td).html();
                if (td_val.trim().toUpperCase().indexOf(_content) > -1) {
                    tr.show(); 
                } else {
                    tr.hide();
                }
            });
        }
    });
}

function filter_all(elm, sensitive = false) {
    var _target_table = $(elm).data('table');

    var _table = $('table.'+_target_table);
    var _tbody = $(_table).find('tbody');
    var _content, _target;

    _tbody.find('tr').show();
    _content = $(elm).val().toUpperCase().trim();

    if (!empty(_content) && _content != '') {
        $.map( $(_tbody).find('tr.search'), function(tr){

            // CEK DI TR ADA ATAU TIDAK
            var ada = 0;
            $.map( $(tr).find('td'), function(td){
                var td_val = $(td).html().trim();
                if ( !sensitive ) {
                    if (td_val.toUpperCase().indexOf(_content) > -1) {
                        ada = 1;
                    }
                } else {
                    if (td_val.toUpperCase() == _content) {
                        ada = 1;
                    }
                }
            });

            if ( ada == 0 ) {
                $(tr).hide();
            } else {
                $(tr).show();
            };
        });
    }
}

/* seperti str_pad di php */
function str_pad(str, len, replace) {
    var str = "" + str;
    var p = len;
    var pad = [];
    while (p > 0) {
        pad.push(replace);
        p--;
    }
    pad = pad.join('');
    var ans = pad.substring(0, pad.length - str.length) + str;
    return ans;
}

function sha1_file(file) {
    return new Promise(function(resolve, reject) {
        var reader = new FileReader();
        var result;
        reader.onload = function(e) {
            var contents = e.target.result;
            var array_contents = CryptoJS.lib.WordArray.create(contents);
            var sha1_contents = CryptoJS.SHA1(array_contents);
            result = sha1_contents.toString();
            result ? resolve(result) : reject(result);
        };
        reader.readAsArrayBuffer(file);
    });
}

// function showNameFile(elm) {
//     var _label = $(elm).closest('label');
//     var _spanfile = _label.prev('span.file');
//     var _allowtypes = $(elm).data('allowtypes').split('|');
//     var _type = $(elm).get(0).files[0]['name'].split('.').pop();

//     if (in_array(_type, _allowtypes)) {
//         if (_spanfile.length) {
//             _spanfile.html($(elm).val());
//         } else {
//             $('<span class="file">' + $(elm).val() + '</span>').insertBefore(_label);
//         }
//     } else {
//         $(elm).val('');
//         bootbox.alert('Format file tidak sesuai. Mohon attach ulang.');
//     }
// }

function showNameFile(elm, isLable = 1) {
    var _label = $(elm).closest('label');
    var _spanfile = _label.prev('span');
    var _allowtypes = $(elm).data('allowtypes').split('|');
    var _type = $(elm).get(0).files[0]['name'].split('.').pop();
    var _namafile = $(elm).val();
    _namafile = _namafile.substring(_namafile.lastIndexOf("\\") + 1, _namafile.length);
    var temp_url = URL.createObjectURL($(elm).get(0).files[0]);

    if (in_array(_type, _allowtypes)) {
      var _nameHtml = '<u>' + _namafile + '</u> ';
      if (isLable == 1) {
        if (_spanfile.length) {
          _spanfile.html(_nameHtml);
        } else {
          if ( $(_label).prev('a').length > 0 ) {
            $(_label).prev('a').removeClass('hide');
            $(_label).prev('a').attr('href', temp_url);
            $(_label).prev('a').html( _nameHtml );
        } else if ( $(_label).next('a').length > 0 ) {
            $(_label).next('a').removeClass('hide');
            $(_label).next('a').attr('href', temp_url);
            $(_label).next('a').html( _nameHtml );
          } else {
            $('<a href='+temp_url+' target="_blank">' + _nameHtml + '</a>').insertBefore(_label);
          }
        }
      }else if (isLable == 0) {
        $(elm).closest('label').attr('title', _namafile);
      }
      $(elm).attr('data-filename', _namafile);
    } else {
        $(elm).val('');
        $(elm).closest('label').attr('title', '');
        $(elm).attr('data-filename', '');
        _spanfile.html('');
        bootbox.alert('Format file tidak sesuai. Mohon attach ulang.');
    }
}

function showMultipleNameFile(elm) {
    var _label = $(elm).closest('label');
    var _spanfile = _label.next('span');
    var _namafile = [];
    var _files = $(elm).get(0).files;

    for (var i in _files) {
        if (typeof _files[i] == 'object') {
            _namafile.push(_files[i]['name']);
        }
    }
    if (_spanfile.length) {
        _spanfile.html(_namafile.join('<br />'));
    } else {
        $('<span>' + _namafile.join('<br />') + '</span>').insertAfter(_label);
    }
}

function isJson(str) {
    try {
        JSON.parse(str);
    } catch (e) {
        return false;
    }
    return true;
}

function goToURL(_url) {
    location.href = _url;
}

function updateCountdown(elm, target) {
    $(elm).keyup(function() {
        var maxLengh = $(elm).attr('maxlength');
        var remaining = maxLengh - $(elm).val().length;
        $(target).text(remaining);
        console.log('fs\n');
    });
}

function formatJam(datetime) {
    return moment(datetime).format('HH:mm');
}

function c2n(s, i = 0) {
    return parseInt(s.charAt(i), 36) - 9;
}

function sumchars(s) {
    var i = s.length,
        r = 0;
    while (--i >= 0) r += c2n(s, i);
    return r;
}


function prevObjectIndexOf(obj, term) {
    var _temp = [];
    $.each(obj, function(k, v) {
        _temp.push(k);
    });

    var prev_term = _temp[_temp.indexOf(term) - 1];
    return obj[prev_term];
}

function roundUp(num, precision = 2) {
    precision = Math.pow(10, precision)
    return Math.ceil(num * precision) / precision
}

var numeral = {
    unformat: function(string_number) {
        string_number = ( empty(string_number) ) ? 0 : string_number;
        return parse_number(string_number, '.', ',');
    },

    format : function(string_number, dec = 2) {
        string_number = ( empty(string_number) ) ? 0 : string_number;
        return number_format(string_number, dec, ',', '.');
    },

    formatInt : function(string_number, dec = 2) {
        string_number = ( empty(string_number) ) ? 0 : string_number;
        return number_format(string_number, 0, ',', '.');
    },


    formatDec : function(string_number, dec = 2) {
        string_number = ( empty(string_number) ) ? 0 : string_number;
        return number_format(string_number, dec, ',', '.');
    },

    formatDec3 : function(string_number, dec = 3) {
        string_number = ( empty(string_number) ) ? 0 : string_number;
        return number_format(string_number, dec, ',', '.');
    }
};

var clock = {

    startTime : function(elm){
        setInterval(function() {
            var today = new Date();
            var date = moment(today).format('DD MMMM Y');
            var h = today.getHours();
            var m = today.getMinutes();
            var s = today.getSeconds();
            m = clock.checkTime(m);
            s = clock.checkTime(s);
            $(elm).html(date + ", " + h + ":" + m + ":" + s);
        }, 1000);
    },

    checkTime : function (i) {
        if (i < 10) {i = "0" + i};  // add zero in front of numbers < 10
        return i;
    }

};
function clearconsole() {
  console.log(window.console);
  if(window.console || window.console.firebug) {
   console.clear();
  }
}

var s_loading = null;
function showLoading(pesan = "Please wait . . . "){
    if (s_loading == null ) {
        s_loading = bootbox.dialog({
            message: '<div class="text-center"><i class="fa fa-spin fa-spinner"></i> '+ pesan +'</div>',
            closeButton : false,
        });
    }
}

// function hideLoading(){
    // if (s_loading != null ) {
    //     s_loading.modal('hide');
    //     s_loading = null;
    // }
// }

function hideLoading(){
     if (s_loading != null ) {
         $(s_loading).next('.modal-backdrop').remove();
        s_loading.remove();
        $('body').removeClass('modal-open');
        $('body').css(
            'padding-right',0
        );
        s_loading = null;
    }
}

function formatURL(str){
    return str.replace('/', '_URT_F');
}

function unformatURL(str){
    return str.replace('_URT_F', '/');
}

function alertDialog(pesan, ukuran = 'small'){
    bootbox.alert(
        {
            message : pesan,
            size: ukuran,
            closeButton : false,
        }
    );
}

function clog(msg){
    console.log(msg);
}

function dateSQL( tanggal ){
    return moment(tanggal).format('YYYY-MM-DD');
}

function monthSQL( tanggal ){
    return moment(tanggal, ['MM/YYYY']).format('YYYY-MM');
}

function dateTimeSQL( tanggal ){
    return moment(tanggal).format('YYYY-MM-DD HH:mm:ss');
}

$.fn.enterKey = function (fnc, mod) {
    return this.each(function () {
        $(this).keypress(function (ev) {
            var keycode = (ev.keyCode ? ev.keyCode : ev.which);
            if ((keycode == '13' || keycode == '10') && (!mod || ev[mod + 'Key'])) {
                fnc.call(this, ev);
            }
        })
    })
}

function change_arrow(elm){
    var a = $("a#sub");
    $.map(a, function(attr){
        if ( attr == elm ) {
            var val = $(attr).data("val");
            if ( val == 0 ) {
                $(attr).data("val", 1);
                $(attr).find("i#sub_hide").addClass("hide");
                $(attr).find("i#sub_show").removeClass("hide");
            } else {
                $(attr).data("val", 0);
                $(attr).find("i#sub_hide").removeClass("hide");
                $(attr).find("i#sub_show").addClass("hide");
            };
        } else {
            $(attr).find("i#sub_hide").removeClass("hide");
            $(attr).find("i#sub_show").addClass("hide");

            var val = $(attr).data("val");
            if ( val == 1 ) {
                $(attr).data("val", 0);
            }
        };
    });
}

function add_row (elm) {
    var btn_add = $(elm);
    var tr = $(btn_add).closest('tr');
    var btn_remove = $(tr).find('#btn-remove');
    var tbody = $(btn_add).closest('tbody');


    var tr_clone  = tr.clone();
    tr_clone.find('input').val(null);

    tbody.append(tr_clone);

    $(tbody).find('tr #btn-remove').removeClass('hide');
}

function remove_row (elm) {
    var btn_remove = $(elm);
    var tr = $(btn_remove).closest('tr');
    var tbody = $(tr).closest('tbody');

    if ( tbody.find('tr').length > 1 ) {
        tr.remove();
        if ( tbody.find('tr').length == 1 ) {
            $(tbody).find('tr #btn-remove').addClass('hide');
        }
    }
}

function set_mark (elm) {
    var target = $(elm).attr('class');
    var parent_target = $(elm).data('parent');
    var all_mark = $('.'+target);
    var row_marking = $.map($(all_mark), function(ipt) {
        if ($(ipt).is(':checked')) {
            return $(ipt);
        }
    });

    if ($(all_mark).length == $(row_marking).length) {
        $('.'+parent_target).prop('checked', true);
    } else {
        $('.'+parent_target).prop('checked', false);
    }
}

function set_mark_all (elm) {
    var target = $(elm).data('target');
    if ($(elm).is(':checked')) {
        // console.log($('.check').length);
        $('.'+target).prop('checked', true);
    } else {
        $('.'+target).prop('checked', false);
    }
}

function cek_attachment (elm) {
    var file = $(elm).get(0).files[0];

    var extension = $(elm).val().split('.').pop();

    var file_size = file.size;
    var file_name = file.name;

    var name_length = file_name.length;

    if (name_length > 200) {
        alertDialog('Nama dokumen yang anda masukkan terlalu panjang (MAX : 200 Huruf)');  
    } else {
        if (['doc', 'DOC', 'docx', 'DOCX', 'jpg', 'JPG', 'jpeg', 'JPEG', 'pdf', 'PDF', 'png', 'PNG', 'bak', 'BAK'].indexOf(extension) > -1) {
            if (file_size < 20000000) {
                $(elm).closest('div.row').find('a[name=dokumen]').removeClass('hide');
                $(elm).closest('div.row').find('label[name=dokumen]').addClass('hide');

                $('label[name=dokumen]').css('color','#000');
                $('i.glyphicon-paperclip ').css('color','#000');

                var temp_url = URL.createObjectURL($(elm).get(0).files[0]);
                $(elm).closest('div.row').find('a[name=dokumen]').text(file_name);
                $(elm).closest('div.row').find('a[name=dokumen]').attr('href', temp_url);
            }else{
                alertDialog('File yang anda masukkan terlalu besar');  
            };
        } else {
            alertDialog('Ekstensi yang anda masukkan salah.');
        }
    }
} // end - attachment

function getValueOf(elm) {
    let dataType = $(elm).attr('data-tipe');
    let result = null;
    switch (dataType) {
        case 'integer':
            result = numeral.unformat($(elm).val());
            break;
        case 'decimal':
            result = numeral.unformat($(elm).val());
            break;
        case 'text':
            result = $(elm).val();
            break;
        default:
            result = $(elm).val();
    }

    // NOTE: tambahkan jika ada value yang lain
    if ($(elm).hasClass('hasDatepicker')) {
        result = dateSQL($(elm).datepicker('getDate'));
    }

    return result;
}