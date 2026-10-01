let mk = {
    searchTimer: null,
    start_up: function () {
        mk.load_data();
        mk.bind_tab_events();
        mk.bind_search_filter();
    },

    bind_tab_events: function () {
        $('a[data-toggle="tab"]').off('click.mkTab').on('click.mkTab', function (e) {
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
                mk.add_form();
            }
        });
    },

    open_action_tab: function (html) {
        $('#tab-action-content').html(html);
        $('a[href="#action"]').trigger('click');
    },

    add_form: function () {
        $.get('aset/MasterKelompok/add_form', function (data) {
            mk.open_action_tab(data);
        }, 'html');
    },

    edit_form: function (elm) {
        var id = $(elm).data('id');

        $.get('aset/MasterKelompok/edit_form', { id: id }, function (data) {
            mk.open_action_tab(data);
        }, 'html');
    },

    bind_search_filter: function () {
        $('#filter_keyword').off('input.mkSearch').on('input.mkSearch', function () {
            var keyword = $(this).val() || '';
            window.clearTimeout(mk.searchTimer);
            mk.searchTimer = window.setTimeout(function () {
                mk.filterData();
            }, 250);
        });
    },

    load_data: function () {
        $.ajax({
            url: 'aset/MasterKelompok/list_data',
            type: 'GET',
            dataType: 'HTML',
            beforeSend: function () {
                showLoading();
            },
            success: function (data) {
                $('.table-area').html(data);
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
        $('#table-kelompok-aset tbody tr').each(function () {
            var row = $(this);
            var rowText = row.text().toLowerCase();
            var rowMatch = keyword === '' || rowText.indexOf(keyword) !== -1;
            row.toggle(rowMatch);
        });
    },

    save_data: function () {
        var nama_kelompok     = $('#nama_kelompok').val();
        var umur_ekonomis     = $('#umur_ekonomis').val();
        var deskripsi         = $('#deskripsi').val();
        var trf_garis_lurus   = $('#trf_garis_lurus').val();
        var trf_saldo_menurun = $('#trf_saldo_menurun').val();

        if ( $.trim(nama_kelompok) === '' ) {
            bootbox.alert('Nama kelompok wajib diisi.');
            return;
        }

        if ( umur_ekonomis === '' || parseInt(umur_ekonomis) <= 0 ) {
            bootbox.alert('Umur ekonomis wajib diisi dan harus lebih dari 0.');
            return;
        }

        var params = {
            nama_kelompok     : nama_kelompok,
            umur_ekonomis     : umur_ekonomis,
            deskripsi         : deskripsi,
            trf_garis_lurus   : trf_garis_lurus,
            trf_saldo_menurun : trf_saldo_menurun
        };

        bootbox.confirm('Apakah anda yakin ingin menyimpan data ?', function (result) {
            if ( result ) {
                $.ajax({
                    url: 'aset/MasterKelompok/save_data',
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
                                // mk.load_data();
                                // $('a[href="#history"]').trigger('click');
                                window.location.reload(true);
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
        var id                = $('#id').val();
        var nama_kelompok     = $('#nama_kelompok').val();
        var umur_ekonomis     = $('#umur_ekonomis').val();
        var deskripsi         = $('#deskripsi').val();
        var trf_garis_lurus   = $('#trf_garis_lurus').val();
        var trf_saldo_menurun = $('#trf_saldo_menurun').val();

        if ( $.trim(nama_kelompok) === '' ) {
            bootbox.alert('Nama kelompok wajib diisi.');
            return;
        }

        if ( umur_ekonomis === '' || parseInt(umur_ekonomis) <= 0 ) {
            bootbox.alert('Umur ekonomis wajib diisi dan harus lebih dari 0.');
            return;
        }

        var params = {
            id                : id,
            nama_kelompok     : nama_kelompok,
            umur_ekonomis     : umur_ekonomis,
            deskripsi         : deskripsi,
            trf_garis_lurus   : trf_garis_lurus,
            trf_saldo_menurun : trf_saldo_menurun
        };

        bootbox.confirm('Apakah anda yakin ingin mengubah data ?', function (result) {
            if ( result ) {
                $.ajax({
                    url: 'aset/MasterKelompok/edit_data',
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
                                // mk.load_data();
                                // $('a[href="#history"]').trigger('click');
                                window.location.reload(true);
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
                    url: 'aset/MasterKelompok/delete_data',
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
                                mk.load_data();
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

window.mk = mk;

$(document).ready(function () {
    mk.start_up();
});