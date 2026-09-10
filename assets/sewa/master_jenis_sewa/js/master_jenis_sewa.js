let mjs = {
    searchTimer: null,
    start_up: function () {
        mjs.load_data();
        mjs.bind_tab_events();
        mjs.bind_search_filter();
    },

    bind_tab_events: function () {
        $('a[data-toggle="tab"]').off('click.mjsTab').on('click.mjsTab', function (e) {
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
                $('#tab-action-content').empty();
            }

            if ( target === '#action' && $('#tab-action-content').is(':empty') ) {
                mjs.add_form();
            }
        });
    },

    open_action_tab: function (html) {
        $('#tab-action-content').html(html);
        $('a[href="#action"]').trigger('click');
    },

    add_form: function () {
        $.get('sewa/MasterJenisSewa/add_form', function (data) {
            mjs.open_action_tab(data);
        }, 'html');
    },

    edit_form: function (elm) {
        var id = $(elm).data('id');

        $.get('sewa/MasterJenisSewa/edit_form', { id: id }, function (data) {
            mjs.open_action_tab(data);
        }, 'html');
    },

    bind_search_filter: function () {
        $('#filter_keyword').off('input.mjsSearch').on('input.mjsSearch', function () {
            var keyword = $(this).val() || '';
            window.clearTimeout(mjs.searchTimer);
            mjs.searchTimer = window.setTimeout(function () {
                mjs.filterData();
            }, 250);
        });
    },

    load_data: function () {
        $.ajax({
            url: 'sewa/MasterJenisSewa/list_data',
            type: 'GET',
            dataType: 'HTML',
            beforeSend: function () {
                showLoading();
            },
            success: function (data) {
                $('#table-jenis-sewa tbody').html(data);
                hideLoading();
            },
            error: function () {
                hideLoading();
                console.log('Error get data from ajax');
            }
        });
    },

    filterData: function (event) {
        if ( event ) {
            event.preventDefault();
        }

        var keyword = ($('#filter_keyword').val() || '').toLowerCase().trim();
        $('#table-jenis-sewa tbody tr').each(function () {
            var row = $(this);
            var rowText = row.text().toLowerCase();
            var rowMatch = keyword === '' || rowText.indexOf(keyword) !== -1;
            row.toggle(rowMatch);
        });
    },

    save_data: function () {
        var kode_jenis_sewa = $('#kode_jenis_sewa').val();
        var nama_jenis_sewa = $('#nama_jenis_sewa').val();
        var keterangan = $('#keterangan').val();

        if ( $.trim(kode_jenis_sewa) === '' || $.trim(nama_jenis_sewa) === '' ) {
            bootbox.alert('Kode jenis sewa dan nama jenis sewa wajib diisi.');
            return;
        }

        var params = {
            kode_jenis_sewa: kode_jenis_sewa,
            nama_jenis_sewa: nama_jenis_sewa,
            keterangan: keterangan
        };

        bootbox.confirm('Apakah anda yakin ingin menyimpan data ?', function (result) {
            if ( result ) {
                $.ajax({
                    url: 'sewa/MasterJenisSewa/save_data',
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
                                mjs.load_data();
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
        var id = $('#id_jenis_sewa').val();
        var kode_jenis_sewa = $('#kode_jenis_sewa').val();
        var nama_jenis_sewa = $('#nama_jenis_sewa').val();
        var keterangan = $('#keterangan').val();

        if ( $.trim(kode_jenis_sewa) === '' || $.trim(nama_jenis_sewa) === '' ) {
            bootbox.alert('Kode jenis sewa dan nama jenis sewa wajib diisi.');
            return;
        }

        var params = {
            id: id,
            kode_jenis_sewa: kode_jenis_sewa,
            nama_jenis_sewa: nama_jenis_sewa,
            keterangan: keterangan
        };

        bootbox.confirm('Apakah anda yakin ingin mengubah data ?', function (result) {
            if ( result ) {
                $.ajax({
                    url: 'sewa/MasterJenisSewa/edit_data',
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
                                mjs.load_data();
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
                    url: 'sewa/MasterJenisSewa/delete_data',
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
                                mjs.load_data();
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
    }
};

window.mjs = mjs;

$(document).ready(function () {
    mjs.start_up();
});
