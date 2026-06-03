var aksesKhusus = {

    start_up: function() {
        aksesKhusus.get_lists();
    },

    get_lists: function() {
        $.ajax({
            url: 'master/AksesKhusus/get_lists',
            type: 'GET',
            dataType: 'HTML',
            beforeSend: function() { showLoading(); },
            success: function(data) {
                $('table.tbl_akses_khusus tbody').html(data);
                hideLoading();
            }
        });
    },

    _open_form: function(id) {
        $.get('master/AksesKhusus/get_form', { id: id || '' }, function(data) {
            var _options = {
                message: data,
                size: 'large',
            };
            bootbox.dialog(_options).bind('shown.bs.modal', function() {
                $(this).find('select.ak-group').select2({
                    theme: 'classic',
                    dropdownParent: $(this)
                });
                $(this).find('select.ak-fitur').select2({
                    theme: 'classic',
                    dropdownParent: $(this),
                    allowClear: true,
                    placeholder: '-- Tidak Spesifik / Semua Fitur --'
                });
            });
        }, 'html');
    },

    add_form: function() {
        aksesKhusus._open_form(null);
    },

    edit_form: function(id) {
        aksesKhusus._open_form(id);
    },

    _validate: function() {
        var err = 0;
        $.map($('[data-required=1]'), function(el) {
            if ( empty($(el).val()) ) {
                $(el).closest('.form-group').addClass('has-error');
                err++;
            } else {
                $(el).closest('.form-group').removeClass('has-error');
            }
        });

        var sel_group = $('select.ak-group');
        if ( empty(sel_group.val()) ) {
            sel_group.closest('.form-group').addClass('has-error');
            err++;
        } else {
            sel_group.closest('.form-group').removeClass('has-error');
        }
        return err;
    },

    _collect: function() {
        return {
            id:           $('input.ak-id').val(),
            id_group:     $('select.ak-group').val(),
            id_detfitur:  $('select.ak-fitur').val() || '',
            akses_khusus: $('input.ak-key').val().trim()
        };
    },

    save: function() {
        if ( aksesKhusus._validate() > 0 ) {
            bootbox.alert('Harap lengkapi data yang wajib diisi.');
            return;
        }
        // Kumpulkan data SEBELUM confirm dibuka
        // (bootbox stacked dialog menyebabkan elemen form tidak bisa diakses di dalam callback)
        var data = aksesKhusus._collect();

        bootbox.confirm('Simpan data Akses Khusus?', function(result) {
            if ( result ) {
                $.ajax({
                    url: 'master/AksesKhusus/save_data',
                    type: 'POST',
                    dataType: 'json',
                    data: { params: data },
                    beforeSend: function() { showLoading(); },
                    success: function(res) {
                        hideLoading();
                        if ( res.status == 1 ) {
                            bootbox.alert(res.message, function() {
                                aksesKhusus.get_lists();
                                bootbox.hideAll();
                            });
                        } else {
                            bootbox.alert(res.message);
                        }
                    }
                });
            }
        });
    },

    update: function() {
        if ( aksesKhusus._validate() > 0 ) {
            bootbox.alert('Harap lengkapi data yang wajib diisi.');
            return;
        }
        // Kumpulkan data SEBELUM confirm dibuka
        var data = aksesKhusus._collect();

        bootbox.confirm('Simpan perubahan data Akses Khusus?', function(result) {
            if ( result ) {
                $.ajax({
                    url: 'master/AksesKhusus/update_data',
                    type: 'POST',
                    dataType: 'json',
                    data: { params: data },
                    beforeSend: function() { showLoading(); },
                    success: function(res) {
                        hideLoading();
                        if ( res.status == 1 ) {
                            bootbox.alert(res.message, function() {
                                aksesKhusus.get_lists();
                                bootbox.hideAll();
                            });
                        } else {
                            bootbox.alert(res.message);
                        }
                    }
                });
            }
        });
    },

    delete_data: function(id) {
        bootbox.confirm('Apakah anda yakin ingin menghapus data Akses Khusus ini?', function(result) {
            if ( result ) {
                $.ajax({
                    url: 'master/AksesKhusus/delete_data',
                    type: 'POST',
                    dataType: 'json',
                    data: { params: id },
                    beforeSend: function() { showLoading(); },
                    success: function(res) {
                        hideLoading();
                        if ( res.status == 1 ) {
                            bootbox.alert(res.message, function() {
                                aksesKhusus.get_lists();
                                bootbox.hideAll();
                            });
                        } else {
                            bootbox.alert(res.message);
                        }
                    }
                });
            }
        });
    }

};

aksesKhusus.start_up();
