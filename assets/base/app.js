var App = {
    collapseRow: function() {
        // NOTE: setup untuk expand collapse row table detail-keranjang
        $('tr.header td span.btn-collapse').click(function() {
            var row = $(this).closest('tr.header');
            $(row).toggleClass('expand').nextUntil('tr.header').slideToggle(100);
            var _el = $(row).closest('tr').find('span.btn-collapse');
            if (_el.hasClass('glyphicon-chevron-right')) {
                _el.removeClass('glyphicon-chevron-right');
                _el.addClass('glyphicon-chevron-down');
            } else {
                _el.removeClass('glyphicon-chevron-down');
                _el.addClass('glyphicon-chevron-right');
            }
        });
    },

    prevLetter: function(ch) {
        return String.fromCharCode(ch.charCodeAt(0) - 1);
    },

    objectLength: function(obj) {
        return Object.keys(obj).length;
    },

    format: function() {
        App.formatNumber();
        App.formatTime();
        App.formatType();
        App.formatAlphaNumeric();
    },

    formatNumber: function() {
        $('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal],[data-tipe=decimal3],[data-tipe=decimal4]').each(function() {
            $(this).priceFormat(Config[$(this).data('tipe')]);
        });
    },

    formatTime: function() {
        moment().local('id');
        $('[format-tipe=time]').datetimepicker({
            format:'HH:mm',
            widgetPositioning: {
                vertical: 'bottom',
                horizontal: 'auto'
            },
            ignoreReadonly: false,
        });

        $('[format-tipe=datetime]').datetimepicker({
            format:'D MMM Y HH:mm',
            widgetPositioning: {
                vertical: 'bottom',
                horizontal: 'auto'
            },
            ignoreReadonly: false,
        });
    },

    formatType: function() {
        $('[type=false]').each(function() {
            $(this).keyup(function() {
                $(this).val("");
            });
        });
    },

    formatAlphaNumeric: function() {
        $('[format-tipe=alpha-numeric]').keyup(function() {
            if (this.value.match(/[^a-zA-Z0-9]/g)) {
                this.value = this.value.replace(/[^a-zA-Z0-9]/g, '');
            } else {
                this.value = this.value.toUpperCase();
            }
        });

        $('[format-tipe=alphabet]').keyup(function() {
            if (this.value.match(/[^a-zA-Z]/g)) {
                this.value = this.value.replace(/[^a-zA-Z]/g, '');
            } else {
                this.value = this.value.toUpperCase();
            }
        });
    },

    getDuration: function(v_minutes) {
        return moment.duration({
            minutes: v_minutes
        });
    },

    hitungWaktu: function(v_jam, v_menit, v_operator) {
        var duration = App.getDuration(v_menit);
        if (v_operator == '-') {
            return moment(v_jam, 'HH:mm').subtract(duration).format('HH:mm');
        } else if (v_operator == '+') {
            return moment(v_jam, 'HH:mm').add(duration).format('HH:mm');
        } else {
            return 'invalid';
        }
    },

    sortIndex: function(obj, ord = 'asc') {
        // NOTE: Data di Order By index asc
        var temp = [];
        $.each(obj, function(k, v) {
            temp.push(k);
        });

        if (ord == 'asc') {
            temp.sort();
        } else if (ord == 'desc') {

        } else {
            console.log('order tidak ditemukan');
            return null;
        }
        var order_by = {};
        $.each(temp, function(k, v) {
            order_by[v] = obj[v];
        });

        return order_by;
    },

    compareTime(t1, opr, t2) {
        var beginningTime = moment(t1, 'HH:mm');
        var endTime = moment(t2, 'HH:mm');
        if (opr == '>') {
            return beginningTime > endTime;
        } else if (opr == '>=') {
            return beginningTime > endTime;
        } else if (opr == '<') {
            return beginningTime < endTime;
        } else if (opr == '<=') {
            return beginningTime <= endTime;
        } else {
            return null;
        }
    },

    formatTimeDateTime: function(stime) {
        if (stime.split(':').length > 1) {
            var _h = stime.split(':')[0];
            var _m = stime.split(':')[1];
            return moment({
                h: parseInt(_h),
                m: parseInt(_m)
            });
        }
        return false;
    },

    selisihWaktuDalamMenit : function(start, end) {
        var m_start = moment(start, 'HH:mm');
        var m_end   = moment(end, 'HH:mm');

        var selisih = m_start.diff(m_end, 'minutes');
        return Math.abs( selisih );
    },

    selisihWaktuDalamHari : function(start, end) {
        var m_start = moment(start);
        var m_end   = moment(end);

        var selisih = m_start.diff(m_end, 'days');
        return Math.abs( selisih );
    },

    subtractMoment : function(start, duration, type) {
        var m_time = moment(start);
        return m_time.subtract( duration, "minutes").format('YYYY-MM-DD HH:mm:ss');
    },

    addMoment : function(start, duration, type) {
        var m_time = moment(start);
        return m_time.add( duration, "minutes").format('YYYY-MM-DD HH:mm:ss');
    },

    confirmRejectDialog : function (message, callback){
        bootbox.prompt({
            title: message,
            inputType: 'textarea',
            placeholder: 'alasan reject',
            buttons: {
                confirm: {
                    label: 'Ya',
                    className: 'btn-primary'
                },
                cancel: {
                    label: 'Tidak',
                    className: 'btn-danger'
                }
            },
            callback: function (result) {
                var st = true;
                if(result != null){
                    if( empty(result) ){
                        bootbox.alert('Mohon isi kolom keterangan alasan penolakan.');
                        st = false;
                    }else{
                      callback(result);
                    }
                }
                return st;
            }
        });
    }, // end -  confirmRejectDialog

    confirmDialog : function (message, callback){
        bootbox.confirm({
            message: message,
            buttons: {
                confirm: {
                    label: 'Ya',
                    className: 'btn-primary'
                },
                cancel: {
                    label: 'Tidak',
                    className: 'btn-danger'
                }
            },
            callback: function (result) {
                callback(result);
            }
        });
    }, // end - confirmDialog

    showLoaderInContent : function(div_content){
        $(div_content).html('<div class="spinner-load"></div>');
    },

    hideLoaderInContent : function(div_content, dHtml = null){
        $(div_content).find('div.spinner-load').remove();
        if (dHtml != null) {
            $(div_content).html(dHtml);
        }
    },

    formatDate : function(){
        $('[data-tipe=date]').datepicker({
            dateFormat : 'dd M yy',
            minDate : 'today',
            locale:'id',
        });
    },

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
};
