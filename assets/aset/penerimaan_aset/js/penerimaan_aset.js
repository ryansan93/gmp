let pa = {
    currentMode: 'add',
    searchTimer: null,

    start_up: function () {
        pa.load_data();
        pa.bind_tab_events();
        pa.init_select2();
        pa.init_datepickers();
        pa.bind_search_filter();
    },

    

    config_form: function () {
        var isLocked = $('#config-form').val();

        if (isLocked == '1') {
            $('form.form-horizontal').find('input:not([type="hidden"]), select, textarea').prop('disabled', true);
            $('form.form-horizontal').find('.select2').css('background-color', '#eee');
            $('button[onclick="pa.edit_data()"]').prop('disabled', true).hide();
            $('form.form-horizontal').css('opacity', '0.8');
        }
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

            if (typeof $().tab === 'function') {
                $(this).tab('show');
            }

            if (target === '#history') {
                if (pa.currentMode === 'edit') {
                    $('#tab-action-content').empty();
                }
            }

            if (target === '#action' && $('#tab-action-content').is(':empty')) {
                pa.add_form();
            }
        });
    },

    init_select2: function () {
        if ($.fn.select2) {
            $('#kode_aset, #filter_kategori_aset, #unit_pengguna, #filter_pic, #pic, #lokasi_pengguna').each(function () {
                var select = $(this);
                
                var isFilter = select.attr('id').indexOf('filter_') === 0;

                if (select.data('select2')) {
                    select.select2('destroy');
                }

                select.select2({
                    width: '100%',
                    allowClear: !isFilter,
                    placeholder: isFilter ? "Semua" : "Pilih data...", 
                    dropdownParent: isFilter ? $('#history') : $('#tab-action-content'),
                    theme: 'bootstrap'
                });

                var currentVal = select.val();
                if (currentVal !== undefined && currentVal !== "") {
                    select.trigger('change');
                }
            });
        }
    },

    bind_search_filter: function () {
        $(document).off('input.paSearchFilter', '#search_penerimaan').on('input.paSearchFilter', '#search_penerimaan', function () {
            clearTimeout(pa.searchTimer);
            pa.searchTimer = setTimeout(function () {
                pa.filterData();
            }, 300);
        });
    },

    init_datepickers: function () {
        if ($.fn.datetimepicker) {
            moment.locale('id');
            $('#tgl_penerimaan, #filter_tanggal_mulai').each(function () {
                var value = $(this).val();
                var date = moment(value, ['YYYY-MM-DD', 'DD MMMM YYYY'], true);
                if (value && date.isValid()) {
                    $(this).val(date.format('DD MMMM YYYY'));
                }
            });

            $('#tgl_penerimaan_picker, #filter_tanggal_mulai_picker').datetimepicker({
                locale: 'id',
                format: 'DD MMMM YYYY',
                useCurrent: false
            });
        }
    },

    bind_kode_aset_change: function () {
        $('#kode_aset').off('change.paKodeAsset').on('change.paKodeAsset', function () {
            var selectedOption = $(this).find(':selected');
            var tglPerolehan = selectedOption.attr('tgl_perolehan');
            var doc_no = selectedOption.attr('document_no');
        
            var tglPenerimaanInput = $('#tgl_penerimaan');
            
            if (tglPerolehan) {
                var minDate = moment(tglPerolehan, 'YYYY-MM-DD');
                
                $('#tgl_penerimaan_picker').data('DateTimePicker').minDate(minDate);

                var currentVal = tglPenerimaanInput.val();
                if (currentVal) {
                    var currentDate = moment(currentVal, 'DD MMMM YYYY');
                    if (currentDate.isBefore(minDate)) {
                        tglPenerimaanInput.val('');
                        bootbox.alert({
                            title: 'Perhatian',
                            message: 'Tanggal Penerimaan tidak boleh lebih kecil dari Tanggal Perolehan (' + minDate.format('DD MMMM YYYY') + '). Tanggal telah direset.',
                            buttons: { ok: { label: 'OK', className: 'btn-warning' } }
                        });
                    }
                }
                
                var helperText = '<small class="text-info"><i class="fa fa-info-circle"></i> Tanggal Perolehan: <b>' + minDate.format('DD MMMM YYYY') + '</b>. Tanggal penerimaan minimal tanggal ini.</small>';
                $('#tgl_penerimaan_helper').html(helperText);
                // $('#no_bukti_terima').val(doc_no);
            } else {
                $('#tgl_penerimaan_picker').data('DateTimePicker').minDate(false);
                $('#tgl_penerimaan_helper').html('');
            }

            pa.applyDateRestriction();
        });
    },

    applyDateRestriction: function() {
        var selectedOption = $('#kode_aset').find(':selected');
        var tglPerolehanStr = selectedOption.attr('tgl_perolehan');

        if (tglPerolehanStr) {
            var minDate = moment(tglPerolehanStr, 'YYYY-MM-DD');
        
            var dtp = $('#tgl_penerimaan_picker').data('DateTimePicker');
            if (dtp) {
                dtp.minDate(minDate);
            }

            if ($('#tgl_penerimaan_helper').length === 0) {
                $('#tgl_penerimaan_picker').after('<div id="tgl_penerimaan_helper" style="margin-top: 5px;"></div>');
            }
            $('#tgl_penerimaan_helper').html('<small class="text-info"><i class="fa fa-info-circle"></i> Minimal tanggal: <b>' + minDate.format('DD MMMM YYYY') + '</b></small>');
        } else {
            var dtp = $('#tgl_penerimaan_picker').data('DateTimePicker');
            if (dtp) {
                dtp.minDate(false);
            }
            $('#tgl_penerimaan_helper').html('');
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

    open_action_tab: function (html) {
        $('#tab-action-content').html(html);
        pa.currentMode = $('#id_penerimaan').length && $('#id_penerimaan').val() ? 'edit' : 'add';
        pa.init_select2();
        pa.init_datepickers();
        pa.bind_kode_aset_change();
        pa.applyDateRestriction(); 
        $('a[href="#action"]').trigger('click');
        pa.config_form();
    },

    add_form: function () {
        pa.currentMode = 'add';
        $.get('aset/PenerimaanAset/add_form', function (data) {
            pa.open_action_tab(data);
        }, 'html');
    },

    edit_form: function (elm) {
        var id = $(elm).data('id');
        pa.currentMode = 'edit';
        $.get('aset/PenerimaanAset/edit_form', { id: id }, function (data) {
            pa.open_action_tab(data);
        }, 'html');
    },

    showFieldError: function (selector, message) {
        var target = $(selector);
        var selection = target.next('.select2-container');

        bootbox.alert(message);

        if (target.length) {
            target.css({
                'border': '1px solid #d9534f',
                'box-shadow': '0 0 0 1px rgba(217, 83, 79, 0.2)'
            });
        }

        if (selection.length) {
            selection.find('.select2-selection').css({
                'border': '1px solid #d9534f',
                'box-shadow': '0 0 0 1px rgba(217, 83, 79, 0.2)'
            });
        }

        if (target.attr('id') === 'tgl_penerimaan') {
            target.closest('#tgl_penerimaan_picker').find('.input-group-addon').css('border-color', '#d9534f');
        }

        if (target.length) {
            target.focus();
        }
    },

    load_data: function () {
        $.ajax({
            url: 'aset/PenerimaanAset/list_data',
            type: 'GET',
            dataType: 'HTML',
            beforeSend: function () {
                showLoading();
            },
            success: function (data) {
                // Pastikan ID tabel di HTML Anda adalah #table-penerimaan
                $('#table-penerimaan tbody').html(data);
                hideLoading();
            },
            error: function () {
                hideLoading();
                console.log('Error get data from ajax');
            }
        });
    },

    save_data: function () {
        var kode_aset      = $('#kode_aset').val();
        var tgl_penerimaan  = $('#tgl_penerimaan').val();
        var bukti_terima    = $('#bukti_terima').val();
        var pic             = $('#pic').val();
        var lokasi_pengguna = $('#lokasi_pengguna').val();
        var no_bukti_terima = $('#no_bukti_terima').val();

        if ($.trim(kode_aset) === '') { pa.showFieldError('#kode_aset', 'Kode Aset wajib diisi.'); return; }
        if ($.trim(pic) === '') { pa.showFieldError('#pic', 'PIC Penerima wajib diisi.'); return; }
        if ($.trim(lokasi_pengguna) === '') { pa.showFieldError('#lokasi_pengguna', 'Lokasi Pengguna wajib diisi.'); return; }
        if ($.trim(tgl_penerimaan) === '') { pa.showFieldError('#tgl_penerimaan', 'Tanggal Penerimaan wajib diisi.'); return; }
        if ($.trim(no_bukti_terima) === '') { pa.showFieldError('#no_bukti_terima', 'No. bukti penerimaan wajib diisi.'); return; }

        // if ($.trim(pic) === '' && $.trim(lokasi_pengguna) === '') {
        //     bootbox.alert('Mohon isi minimal salah satu: <b>PIC Penerima</b> atau <b>Lokasi Pengguna</b>.');
        //     $('#pic').css('border', '1px solid #d9534f');
        //     $('#lokasi_pengguna').css('border', '1px solid #d9534f');

        //     return;
        // }

        var formData = new FormData();
        formData.append('params[kode_aset]', kode_aset);
        formData.append('params[tgl_penerimaan]', pa.toBackendDate(tgl_penerimaan));
        formData.append('params[bukti_terima]', bukti_terima);
        formData.append('params[pic]', pic);
        formData.append('params[lokasi_pengguna]', lokasi_pengguna);
        formData.append('params[no_bukti_terima]', no_bukti_terima);

        var fileInput = document.getElementById('file_dokumen');
        if (fileInput && fileInput.files.length > 0) {
            formData.append('file_dokumen', fileInput.files[0]);
        }

        bootbox.confirm('Apakah anda yakin ingin menyimpan data penerimaan ini?', function (result) {
            if (result) {
                $.ajax({
                    url: 'aset/PenerimaanAset/save_data',
                    type: 'POST',
                    dataType: 'JSON',
                    data: formData,
                    processData: false,
                    contentType: false,
                    beforeSend: function () { showLoading(); },
                    success: function (response) {
                        hideLoading();
                        if (response.status == 1) {
                            bootbox.alert(response.message, function () {
                                // pa.load_data();
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
        var id              = $('#id_penerimaan').val();
        var kode_aset      = $('#kode_aset').val();
        var tgl_penerimaan  = $('#tgl_penerimaan').val();
        var bukti_terima       = $('#bukti_terima').val();
        var pic             = $('#pic').val();
        var lokasi_pengguna = $('#lokasi_pengguna').val();
        var no_bukti_terima = $('#no_bukti_terima').val();
        

        if ($.trim(kode_aset) === '') { pa.showFieldError('#kode_aset', 'Kode Aset wajib diisi.'); return; }
        if ($.trim(pic) === '') { pa.showFieldError('#pic', 'PIC Penerima wajib diisi.'); return; }
        if ($.trim(lokasi_pengguna) === '') { pa.showFieldError('#lokasi_pengguna', 'Lokasi Pengguna wajib diisi.'); return; }
        if ($.trim(tgl_penerimaan) === '') { pa.showFieldError('#tgl_penerimaan', 'Tanggal Penerimaan wajib diisi.'); return; }
        if ($.trim(no_bukti_terima) === '') { pa.showFieldError('#no_bukti_terima', 'No. bukti penerimaan wajib diisi.'); return; }


        // if ($.trim(pic) === '' && $.trim(lokasi_pengguna) === '') {
        //     bootbox.alert('Mohon isi minimal salah satu: <b>PIC Penerima</b> atau <b>Lokasi Pengguna</b>.');
        //     $('#pic').css('border', '1px solid #d9534f');
        //     $('#lokasi_pengguna').css('border', '1px solid #d9534f');
        //     return;
        // }

        var formData = new FormData();
        formData.append('params[id]', id);
        formData.append('params[kode_aset]', kode_aset);
        formData.append('params[tgl_penerimaan]', pa.toBackendDate(tgl_penerimaan));
        formData.append('params[bukti_terima]', bukti_terima);
        formData.append('params[pic]', pic);
        formData.append('params[lokasi_pengguna]', lokasi_pengguna);
        formData.append('params[no_bukti_terima]', no_bukti_terima);

        var fileInput = document.getElementById('file_dokumen');
        if (fileInput && fileInput.files.length > 0) {
            formData.append('file_dokumen', fileInput.files[0]);
        }

        bootbox.confirm('Apakah anda yakin ingin mengubah data penerimaan ini?', function (result) {
            if (result) {
                $.ajax({
                    url: 'aset/PenerimaanAset/edit_data',
                    type: 'POST',
                    dataType: 'JSON',
                    data: formData,
                    processData: false,
                    contentType: false,
                    beforeSend: function () { showLoading(); },
                    success: function (response) {
                        hideLoading();
                        if (response.status == 1) {
                            bootbox.alert(response.message, function () {
                                // pa.load_data();
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

        bootbox.confirm('Apakah anda yakin ingin menghapus data penerimaan ini?', function (result) {
            if (result) {
                $.ajax({
                    url: 'aset/PenerimaanAset/delete_data',
                    type: 'POST',
                    dataType: 'JSON',
                    data: { params: id },
                    beforeSend: function () { showLoading(); },
                    success: function (response) {
                        hideLoading();
                        if (response.status == 1) {
                            bootbox.alert(response.message, function () {
                                pa.load_data();
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

    resetFilter: function (event) {
        if (event) { event.preventDefault(); }
        $('#filter_kategori_aset').val('').trigger('change');
        $('#filter_tanggal_mulai').val('');
        $('#search_penerimaan').val('');
        pa.filterData(event);
    },

    filterData: function (event) {
        if (event) { event.preventDefault(); }

        $.ajax({
            url: 'aset/PenerimaanAset/list_data',
            type: 'POST',
            data: {
                id_kategori: $('#filter_kategori_aset').val() || '',
                tanggal_mulai: pa.toBackendDate($('#filter_tanggal_mulai').val() || ''),
                search: $('#search_penerimaan').val() || ''
            },
            dataType: 'HTML',
            success: function (data) {
                $('#table-penerimaan tbody').html(data);
            },
            error: function () {
                console.log('Error filter data from ajax');
            }
        });
    },

    previewFile: function (input) {
        var file = input.files[0];
        var previewArea = document.getElementById('file_preview_area');
        var previewName = document.getElementById('file_preview_name');
        var previewSize = document.getElementById('file_preview_size');
        var previewImage = document.getElementById('file_preview_image');
        var previewImg = document.getElementById('preview_img');
        
        var oldFileAlert = document.getElementById('old_file_alert');
        if (oldFileAlert) { oldFileAlert.style.display = 'none'; }

        if (!file) {
            previewArea.style.display = 'none';
            return;
        }

        var allowedTypes = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'];
        if (allowedTypes.indexOf(file.type) === -1) {
            alert('File harus berformat PDF, JPG, atau PNG!');
            input.value = '';
            previewArea.style.display = 'none';
            if (oldFileAlert) oldFileAlert.style.display = 'block';
            return;
        }

        var maxSize = 5 * 1024 * 1024;
        if (file.size > maxSize) {
            alert('Ukuran file maksimal 5MB!');
            input.value = '';
            previewArea.style.display = 'none';
            if (oldFileAlert) oldFileAlert.style.display = 'block';
            return;
        }

        previewName.textContent = file.name;
        previewSize.textContent = '(' + (file.size / 1024 / 1024).toFixed(2) + ' MB)';
        previewArea.style.display = 'block';

        if (file.type.startsWith('image/')) {
            var reader = new FileReader();
            reader.onload = function (e) {
                previewImg.src = e.target.result;
                previewImage.style.display = 'block';
            };
            reader.readAsDataURL(file);
        } else {
            previewImage.style.display = 'none';
        }
    },

    clearFile: function () {
        var fileInput = document.getElementById('file_dokumen');
        if(fileInput) fileInput.value = '';
        
        var previewArea = document.getElementById('file_preview_area');
        var previewImage = document.getElementById('file_preview_image');
        if(previewArea) previewArea.style.display = 'none';
        if(previewImage) previewImage.style.display = 'none';
        
        var oldFileAlert = document.getElementById('old_file_alert');
        if (oldFileAlert) { oldFileAlert.style.display = 'block'; }
    },

    show_detail: function(elm) {
        var id = $(elm).data('id');

        $.ajax({
            url: 'aset/PenerimaanAset/detail_data',
            type: 'POST',
            dataType: 'HTML',
            data: { params: id },
            success: function (html) {
                bootbox.alert({
                    title: 'Detail Penerimaan Aset',
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

    exportData: function (event) {
        let hasData = $("#table-penerimaan tbody .tr_loop").filter(function() {
            return $(this).find('td').text().trim() !== 'Tidak ada data tersedia';
        }).length > 0;

        if (!hasData) {
            bootbox.alert('Tidak ada data untuk di export');
            return;
        }
        
        if (event) {
            event.preventDefault();
        }

        var filters = {
            id_kategori: $('#filter_kategori_aset').val() || '',
            tanggal_mulai: pa.toBackendDate($('#filter_tanggal_mulai').val() || ''),
            search: $('#search_penerimaan').val() || ''
        };

        var url = 'aset/PenerimaanAset/export_data?type=xlsx&' + $.param(filters);
        window.location.href = url;
    },

};

$(document).ready(function () {
    pa.start_up();
});