let ks = {
    searchTimer: null,
    start_up: function () {
        ks.load_data();
        ks.bind_tab_events();
        ks.bind_search_filter();
    },

    bind_tab_events: function () {
        $('a[data-toggle="tab"]').off('click.ksTab').on('click.ksTab', function (e) {
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
                ks.add_form();
            }
        });
    },

    open_action_tab: function (html) {
        $('#tab-action-content').html(html);
        $('a[href="#action"]').trigger('click');
    },

    add_form: function () {
        $.get('parameter/KategoriSupplier/add_form', function (data) {
            ks.open_action_tab(data);
        }, 'html');
    },

    edit_form: function (elm) {
        var id = $(elm).data('id');

        $.get('parameter/KategoriSupplier/edit_form', { id: id }, function (data) {
            ks.open_action_tab(data);
        }, 'html');
    },

    bind_search_filter: function () {
        $('#filter_keyword').off('input.ksSearch').on('input.ksSearch', function () {
            window.clearTimeout(ks.searchTimer);
            ks.searchTimer = window.setTimeout(function () {
                ks.filterData();
            }, 250);
        });
    },

    load_data: function () {
        $.ajax({
            url: 'parameter/KategoriSupplier/list_data',
            type: 'GET',
            dataType: 'HTML',
            beforeSend: function () {
                showLoading();
            },
            success: function (data) {
                $('#table-kategori-supplier tbody').html(data);
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
        $('#table-kategori-supplier tbody tr').each(function () {
            var row = $(this);
            var rowText = row.text().toLowerCase();
            var rowMatch = keyword === '' || rowText.indexOf(keyword) !== -1;
            row.toggle(rowMatch);
        });
    },

    save_data: function () {
        var kode_kategori = $('#kode_kategori').val();
        var nama_kategori = $('#nama_kategori').val();

        if ( $.trim(kode_kategori) === '' || $.trim(nama_kategori) === '' ) {
            bootbox.alert('Kode kategori dan nama kategori wajib diisi.');
            return;
        }

        var params = {
            kode_kategori: kode_kategori,
            nama_kategori: nama_kategori
        };

        bootbox.confirm('Apakah anda yakin ingin menyimpan data ?', function (result) {
            if ( result ) {
                $.ajax({
                    url: 'parameter/KategoriSupplier/save_data',
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
                                ks.load_data();
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
        var id = $('#id_kategori_supplier').val();
        var kode_kategori = $('#kode_kategori').val();
        var nama_kategori = $('#nama_kategori').val();

        if ( $.trim(kode_kategori) === '' || $.trim(nama_kategori) === '' ) {
            bootbox.alert('Kode kategori dan nama kategori wajib diisi.');
            return;
        }

        var params = {
            id: id,
            kode_kategori: kode_kategori,
            nama_kategori: nama_kategori
        };

        bootbox.confirm('Apakah anda yakin ingin mengubah data ?', function (result) {
            if ( result ) {
                $.ajax({
                    url: 'parameter/KategoriSupplier/edit_data',
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
                                ks.load_data();
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
                    url: 'parameter/KategoriSupplier/delete_data',
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
                                ks.load_data();
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

window.ks = ks;

$(document).ready(function () {
    ks.start_up();
});
