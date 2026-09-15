let pa = {
    processedLoaded: false,

    start_up: function () {
        pa.load_pending();
        pa.bind_tab_events();
        pa.bind_checkbox_events();
        pa.init_datepicker();
    },

    init_datepicker: function () {
        if ( $.fn.datetimepicker ) {
            moment.locale('id');

            $('#periode_proses_picker').datetimepicker({
                locale: 'id',
                format: 'DD MMMM YYYY',
                useCurrent: false
            });

            $('#periode_proses').val(moment().format('DD MMMM YYYY'));
        }
    },

    toBackendDate: function (value) {
        var date = moment(value, ['YYYY-MM-DD', 'DD MMMM YYYY'], true);
        return date.isValid() ? date.format('YYYY-MM-DD') : '';
    },

    bind_tab_events: function () {
        $('a[data-toggle="tab"]').off('click.paTab').on('click.paTab', function (e) {
            e.preventDefault();
            var target = $(this).attr('href');

            $('.nav-tabs .nav-item').removeClass('active');
            $('.nav-tabs .nav-link').removeClass('active');
            $('.tab-pane').removeClass('active in');

            $(this).parent('.nav-item').addClass('active');
            $(this).addClass('active');
            $(target).addClass('active in');

            if ( typeof $().tab === 'function' ) {
                $(this).tab('show');
            }

            if ( target === '#processed' && !pa.processedLoaded ) {
                pa.load_processed();
            }
        });
    },

    bind_checkbox_events: function () {
        $('#chk-all-pending, #chk-all-processed').off('change.paChkAll').on('change.paChkAll', function () {
            var table = $(this).closest('table');
            var checked = $(this).is(':checked');
            table.find('.chk-row').prop('checked', checked);
            pa.update_selected_count(table);
        });

        $(document).off('change.paChkRow').on('change.paChkRow', '#table-pending .chk-row, #table-processed .chk-row', function () {
            var table = $(this).closest('table');
            pa.update_selected_count(table);

            var total = table.find('.chk-row').length;
            var checked = table.find('.chk-row:checked').length;
            table.find('thead input[type=checkbox]').prop('checked', total > 0 && total === checked);
        });
    },

    update_selected_count: function (table) {
        var checked = table.find('.chk-row:checked').length;
        var target = table.attr('id') === 'table-pending' ? '#pa-selected-count-pending' : '#pa-selected-count-processed';
        $(target).text(checked > 0 ? checked + ' baris dipilih' : '');
    },

    load_pending: function () {
        $.ajax({
            url: 'sewa/ProsesAmortisasi/list_data',
            type: 'POST',
            dataType: 'HTML',
            beforeSend: function () {
                showLoading();
            },
            success: function (data) {
                $('#table-pending tbody').html(data);
                $('#chk-all-pending').prop('checked', false);
                pa.update_selected_count($('#table-pending'));
                hideLoading();
            },
            error: function () {
                hideLoading();
                console.log('Error get data from ajax');
            }
        });
    },

    load_processed: function () {
        $.ajax({
            url: 'sewa/ProsesAmortisasi/list_processed',
            type: 'POST',
            dataType: 'HTML',
            beforeSend: function () {
                showLoading();
            },
            success: function (data) {
                $('#table-processed tbody').html(data);
                $('#chk-all-processed').prop('checked', false);
                pa.update_selected_count($('#table-processed'));
                pa.processedLoaded = true;
                hideLoading();
            },
            error: function () {
                hideLoading();
                console.log('Error get data from ajax');
            }
        });
    },

    proses: function (event) {
        if ( event ) {
            event.preventDefault();
        }

        var ids = $.map( $('#table-pending .chk-row:checked'), function (elm) {
            return $(elm).val();
        });

        if ( ids.length === 0 ) {
            bootbox.alert('Pilih minimal satu baris amortisasi yang mau diproses.');
            return;
        }

        var periode = pa.toBackendDate( $('#periode_proses').val() );
        if ( periode === '' ) {
            bootbox.alert('Periode proses wajib diisi.');
            return;
        }

        bootbox.confirm('Proses ' + ids.length + ' baris amortisasi terpilih untuk periode ' + $('#periode_proses').val() + ' ?', function (result) {
            if ( result ) {
                $.ajax({
                    url: 'sewa/ProsesAmortisasi/proses',
                    type: 'POST',
                    dataType: 'JSON',
                    data: { ids: ids, periode: periode },
                    beforeSend: function () {
                        showLoading();
                    },
                    success: function (response) {
                        hideLoading();
                        if ( response.status == 1 ) {
                            bootbox.alert(response.message, function () {
                                pa.load_pending();
                                pa.processedLoaded = false;
                            });
                        } else {
                            bootbox.alert(response.message);
                        }
                    },
                    error: function () {
                        hideLoading();
                        bootbox.alert('Terjadi kesalahan saat memproses data.');
                    }
                });
            }
        });
    },

    batal: function (event) {
        if ( event ) {
            event.preventDefault();
        }

        var ids = $.map( $('#table-processed .chk-row:checked'), function (elm) {
            return $(elm).val();
        });

        if ( ids.length === 0 ) {
            bootbox.alert('Pilih minimal satu baris amortisasi yang mau dibatalkan.');
            return;
        }

        bootbox.confirm('Batalkan ' + ids.length + ' baris amortisasi terpilih ? Jurnal yang sudah dibuat akan dihapus.', function (result) {
            if ( result ) {
                $.ajax({
                    url: 'sewa/ProsesAmortisasi/batal',
                    type: 'POST',
                    dataType: 'JSON',
                    data: { ids: ids },
                    beforeSend: function () {
                        showLoading();
                    },
                    success: function (response) {
                        hideLoading();
                        if ( response.status == 1 ) {
                            bootbox.alert(response.message, function () {
                                pa.load_processed();
                                pa.load_pending();
                            });
                        } else {
                            bootbox.alert(response.message);
                        }
                    },
                    error: function () {
                        hideLoading();
                        bootbox.alert('Terjadi kesalahan saat membatalkan data.');
                    }
                });
            }
        });
    }
};

$(document).ready(function () {
    pa.start_up();
});
