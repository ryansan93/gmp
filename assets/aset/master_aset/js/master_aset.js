let ma = {
    currentMode: 'add',
    searchTimer: null,

    start_up: function () {
        ma.load_data();
        ma.bind_tab_events();
        ma.init_select2();
        ma.init_datepickers();
        ma.init_nominal_aset();
        ma.bind_search_filter();
    },

    config_form: function () {
        var isLocked = $('#config-form').val();

        if (isLocked == '1') {
            $('form.form-horizontal').find('input:not([type="hidden"]), select, textarea').prop('disabled', true);
            $('form.form-horizontal').find('.select2').css('background-color', '#eee');
            $('button[onclick="ma.edit_data()"]').prop('disabled', true).hide();
            $('form.form-horizontal').css('opacity', '0.8');
        }
    },

    bind_tab_events: function () {
        $('a[data-toggle="tab"]').off('click.maTab').on('click.maTab', function (e) {
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
                if (ma.currentMode === 'edit') {
                    $('#tab-action-content').empty();
                }
            }

            if (target === '#action' && $('#tab-action-content').is(':empty')) {
                ma.add_form();
            }
        });
    },

    init_select2: function () {
        if ($.fn.select2) {
            $('#id_kategori, #filter_kategori_aset, #unit_pengguna, #filter_pic, #pic, #lokasi_pengguna').each(function () {
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
     
        $(document).off('input.maSearchFilter', '#search_aset').on('input.maSearchFilter', '#search_aset', function () {
            clearTimeout(ma.searchTimer);
            ma.searchTimer = setTimeout(function () {
                // console.log('Menjalankan ma.filterData()...');
                ma.filterData();
            }, 300);
        });
    },

    init_datepickers: function () {
        if ($.fn.datetimepicker) {
            moment.locale('id');
            $('#tgl_perolehan, #filter_tanggal_mulai').each(function () {
                var value = $(this).val();
                var date = moment(value, ['YYYY-MM-DD', 'DD MMMM YYYY'], true);
                if (value && date.isValid()) {
                    $(this).val(date.format('DD MMMM YYYY'));
                }
            });

            $('#tgl_perolehan_picker, #filter_tanggal_mulai_picker').datetimepicker({
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

    init_nominal_aset: function () {
        var nominalTargets = $('#nilai_perolehan');
        if (nominalTargets.length) {
            nominalTargets.each(function () {
                var target = $(this);
                target.val(ma.formatRupiah(target.val()));
                target.off('input.maNominalAset').on('input.maNominalAset', function () {
                    $(this).val(ma.formatRupiah($(this).val()));
                });
            });
        }
    },

    open_action_tab: function (html) {
        $('#tab-action-content').html(html);
        ma.currentMode = $('#id_aset').length && $('#id_aset').val() ? 'edit' : 'add';
        ma.init_select2();
        ma.init_datepickers();
        ma.init_nominal_aset();
        $('a[href="#action"]').trigger('click');
        ma.config_form();
    },

    add_form: function () {
        ma.currentMode = 'add';
        $.get('aset/MasterAset/add_form', function (data) {
            ma.open_action_tab(data);
        }, 'html');
    },

    edit_form: function (elm) {
        var id = $(elm).data('id');
        ma.currentMode = 'edit';
        $.get('aset/MasterAset/edit_form', { id: id }, function (data) {
            ma.open_action_tab(data);
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

        if (target.attr('id') === 'tgl_perolehan') {
            target.closest('#tgl_perolehan_picker').find('.input-group-addon').css('border-color', '#d9534f');
        }

        if (target.length) {
            target.focus();
        }
    },

    load_data: function () {
        $.ajax({
            url: 'aset/MasterAset/list_data',
            type: 'GET',
            dataType: 'HTML',
            beforeSend: function () {
                showLoading();
            },
            success: function (data) {
                $('#table-aset tbody').html(data);
                hideLoading();
            },
            error: function () {
                hideLoading();
                console.log('Error get data from ajax');
            }
        });
    },

    // save_data: function () {

    //     var kode_aset      = $('#kode_aset').val() ?? null;
    //     var id_kategori     = $('#id_kategori').val();
    //     var deskripsi_aset = $('#deskripsi_aset').val();
    //     var document_no     = $('#document_no').val();
    //     var tgl_perolehan   = $('#tgl_perolehan').val();
    //     var nilai_perolehan = $('#nilai_perolehan').val();
    //     var unit_pengguna   = $('#unit_pengguna').val();
    //     var lokasi_pengguna = $('#lokasi_pengguna').val();
    //     var pic             = $('#pic').val();
    //     // var status          = $('#status').val() || 'Aktif';
    //     var keterangan      = $('#keterangan').val();

    //     // if ($.trim(kode_aset) === '') { ma.showFieldError('#kode_aset', 'Kode aset wajib diisi.'); return; }
    //     if ($.trim(id_kategori) === '') { ma.showFieldError('#id_kategori', 'Kategori aset wajib diisi.'); return; }
    //     if ($.trim(tgl_perolehan) === '') { ma.showFieldError('#tgl_perolehan', 'Tanggal perolehan wajib diisi.'); return; }
    //     // if ($.trim(lokasi_pengguna) === '') { ma.showFieldError('#lokasi_pengguna', 'Lokasi fisik wajib diisi.'); return; }
    //     if ($.trim(deskripsi_aset) === '') { ma.showFieldError('#deskripsi_aset', 'Deskripsi aset wajib diisi.'); return; }
    //     if ($.trim(nilai_perolehan) === '' || parseFloat(ma.toBackendNominal(nilai_perolehan)) <= 0) {
    //         ma.showFieldError('#nilai_perolehan', 'Nilai perolehan wajib diisi dan lebih dari 0.'); return;
    //     }

    //     var formData = new FormData();
    //     formData.append('params[kode_aset]', kode_aset);
    //     formData.append('params[id_kategori]', id_kategori);
    //     formData.append('params[deskripsi_aset]', deskripsi_aset);
    //     formData.append('params[document_no]', document_no);
    //     formData.append('params[tgl_perolehan]', ma.toBackendDate(tgl_perolehan));
    //     formData.append('params[nilai_perolehan]', ma.toBackendNominal(nilai_perolehan));
    //     formData.append('params[unit_pengguna]', unit_pengguna);
    //     formData.append('params[lokasi_pengguna]', lokasi_pengguna);
    //     formData.append('params[pic]', pic);
    //     formData.append('params[status]', status);
    //     formData.append('params[keterangan]', keterangan);

    //     var fileInput = document.getElementById('file_dokumen');
    //     if (fileInput && fileInput.files.length > 0) {
    //         formData.append('file_dokumen', fileInput.files[0]);
    //     }

    //     bootbox.confirm('Apakah anda yakin ingin menyimpan data ?', function (result) {
    //         if (result) {
    //             $.ajax({
    //                 url: 'aset/MasterAset/save_data',
    //                 type: 'POST',
    //                 dataType: 'JSON',
    //                 data: formData,
    //                 processData: false,
    //                 contentType: false,
    //                 beforeSend: function () { showLoading(); },
    //                 success: function (response) {
    //                     hideLoading();
    //                     if (response.status == 1) {
    //                         bootbox.alert(response.message, function () {
    //                             ma.load_data();
    //                             $('a[href="#history"]').trigger('click');
    //                         });
    //                     } else {
    //                         bootbox.alert(response.message);
    //                     }
    //                 },
    //                 error: function () {
    //                     hideLoading();
    //                     bootbox.alert('Terjadi kesalahan saat menyimpan data.');
    //                 }
    //             });
    //         }
    //     });
    // },

    save_data: function () {

        var id_kategori     = $('#id_kategori').val();
        var deskripsi_aset = $('#deskripsi_aset').val();
        var document_no     = $('#document_no').val();
        var tgl_perolehan   = $('#tgl_perolehan').val();
        var nilai_perolehan = $('#nilai_perolehan').val();
        var unit_pengguna   = $('#unit_pengguna').val();
        var keterangan      = $('#keterangan').val();
        
        var kode_aset      = $('#kode_aset').val() || null;

        if ($.trim(id_kategori) === '') { ma.showFieldError('#id_kategori', 'Kategori aset wajib diisi.'); return; }
        if ($.trim(tgl_perolehan) === '') { ma.showFieldError('#tgl_perolehan', 'Tanggal perolehan wajib diisi.'); return; }
        if ($.trim(deskripsi_aset) === '') { ma.showFieldError('#deskripsi_aset', 'Deskripsi aset wajib diisi.'); return; }
        if ($.trim(nilai_perolehan) === '' || parseFloat(ma.toBackendNominal(nilai_perolehan)) <= 0) {
            ma.showFieldError('#nilai_perolehan', 'Nilai perolehan wajib diisi dan lebih dari 0.'); return;
        }

        var formData = new FormData();

        if (kode_aset) {
            formData.append('params[kode_aset]', kode_aset);
        }
        
        formData.append('params[id_kategori]', id_kategori);
        formData.append('params[deskripsi_aset]', deskripsi_aset);
        formData.append('params[document_no]', document_no);
        formData.append('params[tgl_perolehan]', ma.toBackendDate(tgl_perolehan));
        formData.append('params[nilai_perolehan]', ma.toBackendNominal(nilai_perolehan));
        formData.append('params[unit_pengguna]', unit_pengguna);
        formData.append('params[keterangan]', keterangan);

        var fileInput = document.getElementById('file_dokumen');
        if (fileInput && fileInput.files.length > 0) {
            formData.append('file_dokumen', fileInput.files[0]);
        }

        bootbox.confirm('Apakah anda yakin ingin menyimpan data ?', function (result) {
            if (result) {
                $.ajax({
                    url: 'aset/MasterAset/save_data',
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
                                // ma.load_data();
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
        var id = $('#id_aset').val();
        var kode_aset = $('#kode_aset').val();
        var id_kategori = $('#id_kategori').val();
        var deskripsi_aset = $('#deskripsi_aset').val();
        var document_no = $('#document_no').val();
        var tgl_perolehan = $('#tgl_perolehan').val();
        var nilai_perolehan = $('#nilai_perolehan').val();
        var unit_pengguna = $('#unit_pengguna').val();
        // var lokasi_pengguna = $('#lokasi_pengguna').val();
        // var pic = $('#pic').val();
        // var status = $('#status').val() || 'Aktif';
        var keterangan = $('#keterangan').val();

        if ($.trim(kode_aset) === '') { ma.showFieldError('#kode_aset', 'Kode aset wajib diisi.'); return; }
        if ($.trim(id_kategori) === '') { ma.showFieldError('#id_kategori', 'Kategori aset wajib diisi.'); return; }
        if ($.trim(tgl_perolehan) === '') { ma.showFieldError('#tgl_perolehan', 'Tanggal perolehan wajib diisi.'); return; }
        if ($.trim(nilai_perolehan) === '' || parseFloat(ma.toBackendNominal(nilai_perolehan)) <= 0) {
            ma.showFieldError('#nilai_perolehan', 'Nilai perolehan wajib diisi dan lebih dari 0.'); return;
        }

        var formData = new FormData();
        formData.append('params[id]', id);
        formData.append('params[kode_aset]', kode_aset);
        formData.append('params[id_kategori]', id_kategori);
        formData.append('params[deskripsi_aset]', deskripsi_aset);
        formData.append('params[document_no]', document_no);
        formData.append('params[tgl_perolehan]', ma.toBackendDate(tgl_perolehan));
        formData.append('params[nilai_perolehan]', ma.toBackendNominal(nilai_perolehan));
        formData.append('params[unit_pengguna]', unit_pengguna);
        // formData.append('params[lokasi_pengguna]', lokasi_pengguna);
        // formData.append('params[pic]', pic);
        // formData.append('params[status]', status);
        formData.append('params[keterangan]', keterangan);

        var fileInput = document.getElementById('file_dokumen');
        if (fileInput && fileInput.files.length > 0) {
            formData.append('file_dokumen', fileInput.files[0]);
        }

        bootbox.confirm('Apakah anda yakin ingin mengubah data ?', function (result) {
            if (result) {
                $.ajax({
                    url: 'aset/MasterAset/edit_data',
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
                                // ma.load_data();
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
            if (result) {
                $.ajax({
                    url: 'aset/MasterAset/delete_data',
                    type: 'POST',
                    dataType: 'JSON',
                    data: { params: id },
                    beforeSend: function () { showLoading(); },
                    success: function (response) {
                        hideLoading();
                        if (response.status == 1) {
                            bootbox.alert(response.message, function () {
                                ma.load_data();
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
        $('#search_aset').val('');
        ma.filterData(event);
    },

    filterData: function (event) {
        if (event) { event.preventDefault(); }

        $.ajax({
            url: 'aset/MasterAset/list_data',
            type: 'POST',
            data: {
                id_kategori: $('#filter_kategori_aset').val() || '',
                filter_status: ma.toBackendDate($('#filter_status').val() || ''),
                search: $('#search_aset').val() || ''
            },
            dataType: 'HTML',
            success: function (data) {
                $('#table-aset tbody').html(data);
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
            bootbox.alert('File harus berformat PDF, JPG, atau PNG!');
            input.value = '';
            previewArea.style.display = 'none';
            if (oldFileAlert) oldFileAlert.style.display = 'block';
            return;
        }

        var maxSize = 5 * 1024 * 1024;
        if (file.size > maxSize) {
            bootbox.alert('Ukuran file maksimal 5MB!');
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
        fileInput.value = '';
        document.getElementById('file_preview_area').style.display = 'none';
        document.getElementById('file_preview_image').style.display = 'none';
        
        var oldFileAlert = document.getElementById('old_file_alert');
        if (oldFileAlert) { oldFileAlert.style.display = 'block'; }
    },

    show_detail : function(elm){

        var id = $(elm).data('id');

        // console.log(id)

        $.ajax({
            url: 'aset/MasterAset/detail_data',
            type: 'POST',
            dataType: 'HTML',
            data: { params: id },
            success: function (html) {
                bootbox.alert({
                    title: 'Detail Aset',
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
        
        let hasData = $("#table-aset tbody tr").filter(function() {
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
            tanggal_mulai: ma.toBackendDate($('#filter_tanggal_mulai').val() || ''),
            search: $('#search_aset').val() || ''
        };

        var url = 'aset/MasterAset/export_data?type=xlsx&' + $.param(filters);
        window.location.href = url;
    },




    // Import Excel   
    triggerImportExcel: function() {
        $('#file_excel').val('');
        $('#file_excel').trigger('click');
    },

    processImportExcel: function(element) {
        var file = element.files[0];
        if (!file) return;

        var allowedExtensions = ['xls', 'xlsx'];
        var fileExtension = file.name.split('.').pop().toLowerCase();
        if (!allowedExtensions.includes(fileExtension)) {
            bootbox.alert("Format file salah! Hanya diperbolehkan .xls atau .xlsx");
            return;
        }

        if (file.size > 10 * 1024 * 1024) {
            bootbox.alert("Ukuran file terlalu besar! Maksimal 10MB.");
            return;
        }

        var loadingBox = bootbox.dialog({
            message: '<div class="text-center"><i class="fa fa-spin fa-spinner"></i> Membaca file Excel...</div>',
            closeButton: false
        });

        var reader = new FileReader();
        reader.onload = function(e) {
            var data = new Uint8Array(e.target.result);
            var workbook = XLSX.read(data, { type: 'array' });
            
            var firstSheetName = workbook.SheetNames[0];
            var worksheet = workbook.Sheets[firstSheetName];
            
            // Skip 2 baris pertama (baris 1 kosong, baris 2 header)
            var jsonData = XLSX.utils.sheet_to_json(worksheet, { 
                header: 1, 
                range: 2,
                defval: null
            }); 

            loadingBox.modal('hide');

            // FILTER: Hanya ambil baris yang kolom pertamanya (id_kategori) ada datanya
            var filteredData = [];
            for (var i = 0; i < jsonData.length; i++) {
                var row = jsonData[i];
                
                if (!Array.isArray(row)) continue;
                
                // Cek kolom pertama (index 0 = id_kategori) harus ada datanya
                var firstCell = row[0];
                
                // Validasi: kolom pertama tidak boleh kosong/null/undefined
                if (firstCell !== null && firstCell !== undefined && firstCell !== '') {
                    var firstCellStr = String(firstCell).trim();
                    if (firstCellStr.length > 0 && firstCellStr !== 'null' && firstCellStr !== 'undefined') {
                        filteredData.push(row);
                    }
                }
            }

            console.log('Total baris terbaca:', jsonData.length);
            console.log('Baris valid (ada id_kategori):', filteredData.length);

            if (filteredData.length === 0) {
                bootbox.alert("File Excel kosong atau tidak ada data yang bisa dibaca.");
                return;
            }

            ma.showPreviewDialog(filteredData);
        };
        reader.readAsArrayBuffer(file);
    },

    showPreviewDialog: function(data) {
        var html = '<div class="table-responsive" style="max-height: 400px; overflow-y: auto;">';
        html += '<table class="table table-bordered table-striped table-sm table-hover">';
        html += '<thead class="thead-dark"><tr>';
        
        var headers = ['ID Kategori', 'Unit Pengguna', 'Nilai Perolehan', 'Lokasi', 'Doc No', 'Deskripsi', 'Tgl Perolehan', 'PIC'];
        
        for (var i = 0; i < headers.length; i++) {
            html += '<th>' + headers[i] + '</th>';
        }
        html += '</tr></thead><tbody>';

        var maxPreviewRows = 100;
        var totalRows = data.length;
        var loopLimit = Math.min(totalRows, maxPreviewRows);

        for (var i = 0; i < loopLimit; i++) {
            var row = data[i];
            html += '<tr>';
            for (var j = 0; j < headers.length; j++) {
                var cellValue = (row[j] !== undefined && row[j] !== null) ? row[j] : '';
                
                // Format tanggal untuk kolom Tgl Perolehan (index 6)
                if (j === 6 && typeof cellValue === 'number') { 
                    cellValue = ma.ExcelDate(cellValue);
                }
                
                html += '<td>' + cellValue + '</td>';
            }
            html += '</tr>';
        }

        html += '</tbody></table></div>';
        
        if (totalRows > maxPreviewRows) {
            html += '<p class="text-muted text-center mt-2"><i>* Hanya menampilkan ' + maxPreviewRows + ' baris pertama dari total ' + totalRows + ' baris.</i></p>';
        }

        bootbox.dialog({
            title: '<i class="fa fa-table"></i> Preview Data (' + totalRows + ' Baris)',
            message: html,
            size: 'large',
            buttons: {
                close: {
                    label: "Tutup",
                    className: "btn-secondary",
                    callback: function () {
                        bootbox.hideAll();
                    }
                }
            }
        });
    },

    ExcelDate: function(serial) {
        var utc_days  = Math.floor(serial - 25569);
        var utc_value = utc_days * 86400;
        var date_info = new Date(utc_value * 1000);
        var year = date_info.getFullYear();
        var month = ("0" + (date_info.getMonth() + 1)).slice(-2);
        var day = ("0" + date_info.getDate()).slice(-2);
        return year + "-" + month + "-" + day;
    }

    // End Import Excel  
};

$(document).ready(function () {
    ma.start_up();
});