let mka = {
    searchTimer: null,
    start_up: function () {
        mka.load_data();
        mka.bind_tab_events();
        mka.bind_search_filter();
    },

    bind_tab_events: function () {
        $('a[data-toggle="tab"]').off('click.mkaTab').on('click.mkaTab', function (e) {
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
                mka.add_form();
            }
        });
    },

    open_action_tab: function (html) {
        $('#tab-action-content').html(html);
        $('a[href="#action"]').trigger('click');
    },

    add_form: function () {
        $.get('aset/MasterKategoriAset/add_form', function (data) {
            mka.open_action_tab(data);
        }, 'html');
    },

    edit_form: function (elm) {
        var id = $(elm).data('id');

        $.get('aset/MasterKategoriAset/edit_form', { id: id }, function (data) {
            mka.open_action_tab(data);
        }, 'html');
    },

    bind_search_filter: function () {
        $('#filter_keyword').off('input.mkaSearch').on('input.mkaSearch', function () {
            var keyword = $(this).val() || '';
            window.clearTimeout(mka.searchTimer);
            mka.searchTimer = window.setTimeout(function () {
                mka.filterData();
            }, 250);
        });
    },

    load_data: function () {
        $.ajax({
            url: 'aset/MasterKategoriAset/list_data',
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
        $('#table-kategori-aset tbody tr').each(function () {
            var row = $(this);
            var rowText = row.text().toLowerCase();
            var rowMatch = keyword === '' || rowText.indexOf(keyword) !== -1;
            row.toggle(rowMatch);
        });
    },

    save_data: function () {
        var kategori_kode = $('#kategori_kode').val();
        var kategori_name = $('#kategori_name').val();
        var masa_manfaat_komersial = $('#masa_manfaat_komersial').val();
        var masa_manfaat_fiskal = $('#masa_manfaat_fiskal').val();
        var id_kelompok = $('#id_kelompok').val();

        if ( $.trim(kategori_kode) === '' || $.trim(kategori_name) === '' ) {
            bootbox.alert('Kode kategori dan nama kategori wajib diisi.');
            return;
        }

        var params = {
            kategori_kode: kategori_kode,
            kategori_name: kategori_name,
            masa_manfaat_komersial: masa_manfaat_komersial,
            masa_manfaat_fiskal: masa_manfaat_fiskal,
            id_kelompok: id_kelompok
        };

        bootbox.confirm('Apakah anda yakin ingin menyimpan data ?', function (result) {
            if ( result ) {
                $.ajax({
                    url: 'aset/MasterKategoriAset/save_data',
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
                                mka.load_data();
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
        var id = $('#id_kategori').val();
        var kategori_kode = $('#kategori_kode').val();
        var kategori_name = $('#kategori_name').val();
        var masa_manfaat_komersial = $('#masa_manfaat_komersial').val();
        var masa_manfaat_fiskal = $('#masa_manfaat_fiskal').val();
        var id_kelompok = $('#id_kelompok').val();

        if ( $.trim(kategori_kode) === '' || $.trim(kategori_name) === '' ) {
            bootbox.alert('Kode kategori dan nama kategori wajib diisi.');
            return;
        }

        var params = {
            id: id,
            kategori_kode: kategori_kode,
            kategori_name: kategori_name,
            masa_manfaat_komersial: masa_manfaat_komersial,
            masa_manfaat_fiskal: masa_manfaat_fiskal,
            id_kelompok: id_kelompok
        };

        bootbox.confirm('Apakah anda yakin ingin mengubah data ?', function (result) {
            if ( result ) {
                $.ajax({
                    url: 'aset/MasterKategoriAset/edit_data',
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
                                mka.load_data();
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
                    url: 'aset/MasterKategoriAset/delete_data',
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
                                mka.load_data();
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

window.mka = mka;

$(document).ready(function () {
    mka.start_up();
});