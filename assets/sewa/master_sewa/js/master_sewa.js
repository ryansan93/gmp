let ms = {
    currentMode: 'add',
    searchTimer: null,

    start_up: function () {
        ms.load_data();
        ms.bind_tab_events();
        ms.init_select2();
        ms.init_datepickers();
        ms.init_nominal_sewa();
        ms.bind_search_filter();

    },

    bind_tab_events: function () {
        $('a[data-toggle="tab"]').off('click.msTab').on('click.msTab', function (e) {
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

            if ( target === '#history' ) {
                if ( ms.currentMode === 'edit' ) {
                    $('#tab-action-content').empty();
                }
            }

            if ( target === '#action' && $('#tab-action-content').is(':empty') ) {
                ms.add_form();
            }
        });
    },

    init_select2: function () {
        if ( $.fn.select2 ) {
            $('#jenis_sewa, #filter_jenis_sewa, #filter_supplier, #no_supplier').each(function () {
                var select = $(this);
                var isFilter = select.attr('id') === 'filter_jenis_sewa' || select.attr('id') === 'filter_supplier';

                if ( select.data('select2') ) {
                    select.select2('destroy');
                }

                select.select2({
                    // placeholder: isFilter ? (select.attr('id') === 'filter_supplier' ? 'Semua Supplier' : 'Semua Jenis Sewa') : 'Pilih Jenis Sewa',
                    width: '100%',
                    allowClear: !isFilter,
                    dropdownParent: isFilter ? $('#history') : $('#tab-action-content'),
                    theme: 'bootstrap'
                });

                select.val(select.val() || null).trigger('change');

                select.off('select2:select.select2JenisSewa select2:clear.select2JenisSewa')
                    .on('select2:select.select2JenisSewa select2:clear.select2JenisSewa', function () {
                        var value = $(this).val();
                        $(this).attr('data-selected', value || '');
                    });

                if ( !isFilter ) {
                    select.off('change.msNamaSewa').on('change.msNamaSewa', function () {
                        if ( $(this).attr('id') === 'jenis_sewa' ) {
                            var namaSewa = $(this).find('option:selected').attr('data-nama-sewa') || '';
                            $('#nama_sewa').val(namaSewa);
                        }
                    });

                    select.trigger('change');
                }
            });
        }
    },

    bind_search_filter: function () {
        $('#search_sewa').off('input.msSearchFilter').on('input.msSearchFilter', function () {
            clearTimeout(ms.searchTimer);
            ms.searchTimer = setTimeout(function () {
                ms.filterData();
            }, 300);
        });
    },

    init_datepickers: function () {
        if ( $.fn.datetimepicker ) {
            moment.locale('id');

            $('#tanggal_mulai, #filter_tanggal_mulai').each(function () {
                var value = $(this).val();
                var date = moment(value, ['YYYY-MM-DD', 'DD MMMM YYYY'], true);

                if ( value && date.isValid() ) {
                    $(this).val(date.format('DD MMMM YYYY'));
                }
            });

            $('#tanggal_mulai_picker, #filter_tanggal_mulai_picker').datetimepicker({
                locale: 'id',
                format: 'DD MMMM YYYY',
                useCurrent: false
            });
        }
    },

    toBackendDate: function (value) {
        var date = moment(value, ['YYYY-MM-DD', 'DD MMMM YYYY'], true);
        return date.isValid() ? date.format('YYYY-MM-DD') : '';
    },

    formatRupiah: function (value) {
        var number = String(value || '').replace(/\D/g, '');
        return number.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    },

    toBackendNominal: function (value) {
        return String(value || '').replace(/\D/g, '') || '0';
    },

    init_nominal_sewa: function () {
        var nominal = $('#nominal_sewa');

        if ( nominal.length ) {
            nominal.val(ms.formatRupiah(nominal.val()));
            nominal.off('input.msNominalSewa').on('input.msNominalSewa', function () {
                $(this).val(ms.formatRupiah($(this).val()));
            });
        }
    },

    open_action_tab: function (html) {
        $('#tab-action-content').html(html);
        ms.currentMode = $('#id_sewa').length && $('#id_sewa').val() ? 'edit' : 'add';
        ms.init_select2();
        ms.init_datepickers();
        ms.init_nominal_sewa();
        ms.configJumlah($('#jumlah_bulan'), null);
        $('a[href="#action"]').trigger('click');
    },

    add_form: function () {
        ms.currentMode = 'add';
        $.get('sewa/MasterSewa/add_form', function (data) {
            ms.open_action_tab(data);
        }, 'html');
    },

    edit_form: function (elm) {
        var id = $(elm).data('id');
        ms.currentMode = 'edit';

        $.get('sewa/MasterSewa/edit_form', { id: id }, function (data) {
            ms.open_action_tab(data);
        }, 'html');
    },

    showFieldError: function (selector, message) {
        var target = $(selector);
        var selection = target.next('.select2-container');

        bootbox.alert(message);

        if ( target.length ) {
            target.css({
                'border': '1px solid #d9534f',
                'box-shadow': '0 0 0 1px rgba(217, 83, 79, 0.2)'
            });
        }

        if ( selection.length ) {
            selection.find('.select2-selection').css({
                'border': '1px solid #d9534f',
                'box-shadow': '0 0 0 1px rgba(217, 83, 79, 0.2)'
            });
        }

        if ( target.attr('id') === 'tanggal_mulai' ) {
            target.closest('#tanggal_mulai_picker').find('.input-group-addon').css('border-color', '#d9534f');
        }

        if ( target.length ) {
            target.focus();
        }
    },

    load_data: function () {
        $.ajax({
            url: 'sewa/MasterSewa/list_data',
            type: 'GET',
            dataType: 'HTML',
            beforeSend: function () {
                showLoading();
            },
            success: function (data) {
                $('#table-sewa tbody').html(data);
                hideLoading();
            },
            error: function () {
                hideLoading();
                console.log('Error get data from ajax');
            }
        });
    },

    save_data: function () {
        var nama_sewa = $('#nama_sewa').val();
        var no_kontrak = $('#no_kontrak').val();
        var jumlah_bulan = $('#jumlah_bulan').val();
        var jumlah_siklus = $('#jumlah_siklus').val();
        var jenis_sewa = $('#jenis_sewa').val();
        var tanggal_mulai = $('#tanggal_mulai').val();
        var no_supplier = $('#no_supplier').val();
        var nominal_sewa = $('#nominal_sewa').val();

        if ( $.trim(nama_sewa) === '' ) {
            ms.showFieldError('#nama_sewa', 'Nama sewa wajib diisi.');
            return;
        }

        if ( $.trim(no_kontrak) === '' ) {
            ms.showFieldError('#no_kontrak', 'No kontrak wajib diisi.');
            return;
        }

        if ( $.trim(jenis_sewa) === '' ) {
            ms.showFieldError('#jenis_sewa', 'Jenis sewa wajib diisi.');
            return;
        }

        if ( $.trim(tanggal_mulai) === '' ) {
            ms.showFieldError('#tanggal_mulai', 'Tanggal mulai wajib diisi.');
            return;
        }

        if ( $.trim(no_supplier) === '' ) {
            ms.showFieldError('#no_supplier', 'No supplier wajib diisi.');
            return;
        }

        var params = {
            nama_sewa: nama_sewa,
            no_kontrak: no_kontrak,
            jumlah_bulan: jumlah_bulan,
            jumlah_siklus: jumlah_siklus,
            jenis_sewa: jenis_sewa,
            tanggal_mulai: ms.toBackendDate(tanggal_mulai),
            no_supplier: no_supplier,
            nominal_sewa: ms.toBackendNominal(nominal_sewa)
        };

        bootbox.confirm('Apakah anda yakin ingin menyimpan data ?', function (result) {
            if ( result ) {
                $.ajax({
                    url: 'sewa/MasterSewa/save_data',
                    type: 'POST',
                    dataType: 'JSON',
                    data: { params: params },
                    beforeSend: function () {
                        showLoading();
                    },
                    success: function (response) {
                        hideLoading();
                        if ( response.status == 1 ) {
                            bootbox.alert(response.message, function () {
                                ms.load_data();
                                $('a[href="#history"]').trigger('click');
                            });
                        } else {
                            bootbox.alert(response.message);
                        }
                    },
                    error: function () {
                        hideLoading();
                        bootbox.alert('Terjadi kesalahan saat menyimpan data.');
                    }
                });
            }
        });
    },

    edit_data: function () {
        var id              = $('#id_sewa').val();
        var no_sewa         = $('#no_sewa').val();
        var nama_sewa       = $('#nama_sewa').val();
        var no_kontrak      = $('#no_kontrak').val();
        var jumlah_bulan    = $('#jumlah_bulan').val();
        var jumlah_siklus   = $('#jumlah_siklus').val();
        var jenis_sewa      = $('#jenis_sewa').val();
        var tanggal_mulai   = $('#tanggal_mulai').val();
        var no_supplier     = $('#no_supplier').val();
        var nominal_sewa    = $('#nominal_sewa').val();

     
        if ( $.trim(no_kontrak) === '' ) {
            ms.showFieldError('#no_kontrak', 'No kontrak wajib diisi.');
            return;
        }

        if ( $.trim(nama_sewa) === '' ) {
            ms.showFieldError('#nama_sewa', 'Nama sewa wajib diisi.');
            return;
        }

        if ( $.trim(jenis_sewa) === '' ) {
            ms.showFieldError('#jenis_sewa', 'Jenis sewa wajib diisi.');
            return;
        }

        if ( $.trim(tanggal_mulai) === '' ) {
            ms.showFieldError('#tanggal_mulai', 'Tanggal mulai wajib diisi.');
            return;
        }

        if ( $.trim(no_supplier) === '' ) {
            ms.showFieldError('#no_supplier', 'No supplier wajib diisi.');
            return;
        }

        var params = {
            id: id,
            no_sewa: no_sewa,
            nama_sewa: nama_sewa,
            no_kontrak: no_kontrak,
            jumlah_bulan: jumlah_bulan,
            jumlah_siklus: jumlah_siklus,
            jenis_sewa: jenis_sewa,
            tanggal_mulai: ms.toBackendDate(tanggal_mulai),
            no_supplier: no_supplier,
            nominal_sewa: ms.toBackendNominal(nominal_sewa)
        };

        bootbox.confirm('Apakah anda yakin ingin mengubah data ?', function (result) {
            if ( result ) {
                $.ajax({
                    url: 'sewa/MasterSewa/edit_data',
                    type: 'POST',
                    dataType: 'JSON',
                    data: { params: params },
                    beforeSend: function () {
                        showLoading();
                    },
                    success: function (response) {
                        hideLoading();
                        if ( response.status == 1 ) {
                            bootbox.alert(response.message, function () {
                                ms.load_data();
                                $('a[href="#history"]').trigger('click');
                            });
                        } else {
                            bootbox.alert(response.message);
                        }
                    },
                    error: function () {
                        hideLoading();
                        bootbox.alert('Terjadi kesalahan saat mengubah data.');
                    }
                });
            }
        });
    },

    delete_data: function (elm) {
        var id = $(elm).data('id');

        bootbox.confirm('Apakah anda yakin ingin menghapus data ini ?', function (result) {
            if ( result ) {
                $.ajax({
                    url: 'sewa/MasterSewa/delete_data',
                    type: 'POST',
                    dataType: 'JSON',
                    data: { params: id },
                    beforeSend: function () {
                        showLoading();
                    },
                    success: function (response) {
                        hideLoading();
                        if ( response.status == 1 ) {
                            bootbox.alert(response.message, function () {
                                ms.load_data();
                            });
                        } else {
                            bootbox.alert(response.message);
                        }
                    },
                    error: function () {
                        hideLoading();
                        bootbox.alert('Terjadi kesalahan saat menghapus data.');
                    }
                });
            }
        });
    },

    exportData: function (event) {
        let row = $("#table-sewa tbody tr.tr_loop").length;
        if (row < 1) {
            bootbox.alert('Tidak ada data untuk di export');
            return;
        }
        
        if ( event ) {
            event.preventDefault();
        }

        var filters = {
            jenis_sewa: $('#filter_jenis_sewa').val() || '',
            tanggal_mulai: ms.toBackendDate($('#filter_tanggal_mulai').val() || ''),
            supplier: $('#filter_supplier').val() || '',
            search: $('#search_sewa').val() || ''
        };

        bootbox.dialog({
            title: 'Export Data Sewa',
            message: 'Pilih format export:',
            size: 'small',
            buttons: {
                pdf: {
                    label: 'PDF',
                    className: 'btn-primary',
                    callback: function () {
                        var url = 'sewa/MasterSewa/export_data?type=pdf&' + $.param(filters);
                        window.location.href = url;
                    }
                },
                excel: {
                    label: 'Excel',
                    className: 'btn-success',
                    callback: function () {
                        var url = 'sewa/MasterSewa/export_data?type=xlsx&' + $.param(filters);
                        window.location.href = url;
                    }
                },
                cancel: {
                    label: 'Batal',
                    className: 'btn-default'
                }
            }
        });
    },

    resetFilter: function (event) {
        if ( event ) {
            event.preventDefault();
        }

        $('#filter_jenis_sewa').val('').trigger('change');
        $('#filter_supplier').val('').trigger('change');
        $('#filter_tanggal_mulai').val('');
        $('#search_sewa').val('');

        ms.filterData(event);
    },

    filterData: function (event) {
        if ( event ) {
            event.preventDefault();
        }

        $.ajax({
            url: 'sewa/MasterSewa/list_data',
            type: 'POST',
            data: {
                jenis_sewa: $('#filter_jenis_sewa').val() || '',
                tanggal_mulai: ms.toBackendDate($('#filter_tanggal_mulai').val() || ''),
                supplier: $('#filter_supplier').val() || '',
                search: $('#search_sewa').val() || ''
            },
            dataType: 'HTML',
            success: function (data) {
                $('#table-sewa tbody').html(data);
            },
            error: function () {
                console.log('Error filter data from ajax');
            }
        });
    },

    show_detail: function (elm) {
        var id = $(elm).data('id');

        $.ajax({
            url: 'sewa/MasterSewa/detail_data',
            type: 'POST',
            dataType: 'HTML',
            data: { params: id },
            success: function (html) {
                bootbox.alert({
                    title: 'Detail Sewa',
                    message: html,
                    size: 'large',
                    buttons: {
                        ok: {
                            label: 'Tutup',
                            className: 'btn-primary'
                        }
                    }
                });
            },
            error: function () {
                bootbox.alert('Terjadi kesalahan saat memuat detail data.');
            }
        });
    },


    configJumlah: function (elm, e) {
        let init = $(elm).closest('.config-jumlah');

        let jumlahBulan = init.find('#jumlah_bulan');
        let jumlahSiklus = init.find('#jumlah_siklus');

        if (jumlahBulan.val() !== '') {
            jumlahBulan.prop('disabled', false);
            jumlahSiklus.prop('disabled', true);
        } else if (jumlahSiklus.val() !== '') {
            jumlahSiklus.prop('disabled', false);
            jumlahBulan.prop('disabled', true);
        } else {
            jumlahBulan.prop('disabled', false);
            jumlahSiklus.prop('disabled', false);
        }
    }
};

$(document).ready(function () {
    ms.start_up();
});