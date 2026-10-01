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


    config_form : function () {
        var isLocked = $('#config-form').val();

        if (isLocked == '1') {
            
            $('form.form-horizontal').find('input:not([type="hidden"]), select, textarea').prop('disabled', true);
            $('form.form-horizontal').find('.select2').css('background-color', '#eee');
            $('button[onclick="ms.edit_data()"]').prop('disabled', true).hide();
        
            $('form.form-horizontal').css('opacity', '0.8');
        }
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
            $('#jenis_sewa, #filter_jenis_sewa, #filter_supplier, #no_supplier, #unit').each(function () {
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
        var nominalTargets = $('#nominal_sewa, #dp, #nominal_cicilan');

        if ( nominalTargets.length ) {
            nominalTargets.each(function () {
                var target = $(this);
                target.val(ms.formatRupiah(target.val()));
                target.off('input.msNominalSewa').on('input.msNominalSewa', function () {
                    $(this).val(ms.formatRupiah($(this).val()));
                });
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

        ms.config_form();
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
        var dp = $('#dp').val();
        var nominal_cicilan = $('#nominal_cicilan').val();
        var durasi_cicilan = $('#durasi_cicilan').val();
        var tgl_jatuh_tempo = $('#tgl_jatuh_tempo').val();
        var unit = $('#unit').val();

        if ( $.trim(nama_sewa) === '' ) { ms.showFieldError('#nama_sewa', 'Nama sewa wajib diisi.'); return; }
        if ( $.trim(no_kontrak) === '' ) { ms.showFieldError('#no_kontrak', 'No kontrak wajib diisi.'); return; }
        if ( $.trim(jenis_sewa) === '' ) { ms.showFieldError('#jenis_sewa', 'Jenis sewa wajib diisi.'); return; }
        if ( $.trim(tanggal_mulai) === '' ) { ms.showFieldError('#tanggal_mulai', 'Tanggal mulai wajib diisi.'); return; }
        if ( $.trim(no_supplier) === '' ) { ms.showFieldError('#no_supplier', 'No supplier wajib diisi.'); return; }


        // var dp_clean = dp ? parseInt(dp.toString().replace(/\./g, '')) : 0;
        // if ( dp_clean > 0 && (!durasi_cicilan || parseInt(durasi_cicilan) <= 0) ) {
        //     ms.showFieldError('#durasi_cicilan', 'Durasi cicilan wajib diisi jika ada DP.');
        //     return;
        // }

        var formData = new FormData();

        formData.append('params[nama_sewa]', nama_sewa);
        formData.append('params[no_kontrak]', no_kontrak);
        formData.append('params[jumlah_bulan]', jumlah_bulan);
        formData.append('params[jumlah_siklus]', jumlah_siklus);
        formData.append('params[jenis_sewa]', jenis_sewa);
        formData.append('params[tanggal_mulai]', ms.toBackendDate(tanggal_mulai));
        formData.append('params[no_supplier]', no_supplier);
        formData.append('params[nominal_sewa]', ms.toBackendNominal(nominal_sewa));
        formData.append('params[dp]', ms.toBackendNominal(dp));
        formData.append('params[nominal_cicilan]', ms.toBackendNominal(nominal_cicilan));
        formData.append('params[durasi_cicilan]', durasi_cicilan);
        formData.append('params[tgl_jatuh_tempo]', tgl_jatuh_tempo);
        formData.append('params[unit]', unit);

        var fileInput = document.getElementById('file_dokumen');
        if (fileInput && fileInput.files.length > 0) {
            formData.append('file_dokumen', fileInput.files[0]);
        }

        bootbox.confirm('Apakah anda yakin ingin menyimpan data ?', function (result) {
            if ( result ) {
                $.ajax({
                    url: 'sewa/MasterSewa/save_data',
                    type: 'POST',
                    dataType: 'JSON',
                    data: formData,  
                    processData: false, 
                    contentType: false, 
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
        var dp              = $('#dp').val();
        var nominal_cicilan = $('#nominal_cicilan').val();
        var durasi_cicilan  = $('#durasi_cicilan').val();
        var tgl_jatuh_tempo = $('#tgl_jatuh_tempo').val();
        var unit            = $('#unit').val();

        if ( $.trim(no_kontrak) === '' ) { ms.showFieldError('#no_kontrak', 'No kontrak wajib diisi.'); return; }
        if ( $.trim(nama_sewa) === '' ) { ms.showFieldError('#nama_sewa', 'Nama sewa wajib diisi.'); return; }
        if ( $.trim(jenis_sewa) === '' ) { ms.showFieldError('#jenis_sewa', 'Jenis sewa wajib diisi.'); return; }
        if ( $.trim(tanggal_mulai) === '' ) { ms.showFieldError('#tanggal_mulai', 'Tanggal mulai wajib diisi.'); return; }
        if ( $.trim(unit) === '' ) { ms.showFieldError('#unit', 'Unit wajib diisi.'); return; }
        if ( $.trim(no_supplier) === '' ) { ms.showFieldError('#no_supplier', 'No supplier wajib diisi.'); return; }

        // var dp_clean = dp ? parseInt(dp.toString().replace(/\./g, '')) : 0;
        // if ( dp_clean > 0 && (!durasi_cicilan || parseInt(durasi_cicilan) <= 0) ) {
        //     ms.showFieldError('#durasi_cicilan', 'Durasi cicilan wajib diisi jika ada DP.');
        //     return;
        // }

        
        var formData = new FormData();
        
        
        formData.append('params[id]', id);
        formData.append('params[no_sewa]', no_sewa);
        formData.append('params[nama_sewa]', nama_sewa);
        formData.append('params[no_kontrak]', no_kontrak);
        formData.append('params[jumlah_bulan]', jumlah_bulan);
        formData.append('params[jumlah_siklus]', jumlah_siklus);
        formData.append('params[jenis_sewa]', jenis_sewa);
        formData.append('params[tanggal_mulai]', ms.toBackendDate(tanggal_mulai));
        formData.append('params[no_supplier]', no_supplier);
        formData.append('params[nominal_sewa]', ms.toBackendNominal(nominal_sewa));
        formData.append('params[dp]', ms.toBackendNominal(dp));
        formData.append('params[nominal_cicilan]', ms.toBackendNominal(nominal_cicilan));
        formData.append('params[durasi_cicilan]', durasi_cicilan);
        formData.append('params[tgl_jatuh_tempo]', tgl_jatuh_tempo);
        formData.append('params[unit]', unit);

        
        var fileInput = document.getElementById('file_dokumen');
        if (fileInput && fileInput.files.length > 0) {
            formData.append('file_dokumen', fileInput.files[0]);
        }

        
        bootbox.confirm('Apakah anda yakin ingin mengubah data ?', function (result) {
            if ( result ) {
                $.ajax({
                    url: 'sewa/MasterSewa/edit_data',
                    type: 'POST',
                    dataType: 'JSON',
                    data: formData,             // <-- Ganti object params dengan formData
                    processData: false,         // <-- WAJIB: Mencegah jQuery memproses data
                    contentType: false,         // <-- WAJIB: Mencegah jQuery menimpa Content-Type
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

    open_amortisasi_bootbox: function () {
        var id = $('#id_sewa').val();
        var nilai_perolehan = $("#nominal_sewa").val() || 0; 

        if (!id) {
            bootbox.alert('Simpan data sewa terlebih dahulu untuk mengedit amortisasi.');
            return;
        }

        $.ajax({
            url: 'sewa/MasterSewa/detailAmortisasi',
            type: 'POST',
            dataType: 'HTML',
            data: { params: id },
            beforeSend: function () {
                showLoading();
            },
            success: function (html) {
                hideLoading();
                bootbox.dialog({
                    title: 'Edit Amortisasi',
                    message: html,
                    size: 'large',
                    buttons: {
                        simpan: {
                            label: '<i class="fa fa-save"></i> Simpan Perubahan',
                            className: 'btn-primary',
                            callback: function () {
                                $('.bootbox').modal('hide');
                                ms.simpanDataAmortisasi(id); 
                                return false; 
                            }
                        },
                        close: {
                            label: '<i class="fa fa-times"></i> Batal',
                            className: 'btn-default'
                        }
                    }
                });

                setTimeout(function() {
                    $("#perolehan_val").val(nilai_perolehan);

                    if (typeof $.fn.maskMoney !== 'undefined') {
                        $('.format-rupiah').each(function() {
                            var $el = $(this);
                            $el.maskMoney('destroy');
                            $el.maskMoney({
                                thousands: '.',
                                decimal: ',',
                                precision: 0,
                                allowZero: true,
                                showSymbol: false,
                                allowNegative: false
                            });
                        
                            var val = $el.val().replace(/\./g, '').replace(',', '.');
                            $el.val(val).maskMoney('mask');
                        });
                    } 
                    
                    else {
                        $('.format-rupiah').each(function() {
                            var $el = $(this);
                            var originalVal = $el.val();
                            
                            
                            if (originalVal && !originalVal.includes('.')) {
                                var num = parseInt(originalVal.replace(/[^0-9]/g, ''));
                                $el.val(num.toLocaleString('id-ID'));
                            }
                            
                            
                            $el.on('input keyup', function(e) {
                                var val = $(this).val().replace(/[^0-9]/g, '');
                                if (val) {
                                    $(this).val(parseInt(val).toLocaleString('id-ID'));
                                } else {
                                    $(this).val('');
                                }
                                
                                if (typeof ms.cekTotalAmortisasi === 'function') {
                                    ms.cekTotalAmortisasi();
                                }
                            });
                        });
                    }

                    
                    if (typeof ms.cekTotalAmortisasi === 'function') {
                        ms.cekTotalAmortisasi();
                    }

                    ms.updateTotalDisplay();

                }, 200);
            },
            error: function () {
                hideLoading();
                bootbox.alert('Terjadi kesalahan saat memuat amortisasi.');
            }
        });
    },


    formatDanCek: function(input) {
        // 1. Bersihkan semua karakter kecuali angka
        var value = input.value.replace(/[^0-9]/g, '');
        
        if (value === '') {
            input.value = '';
            this.cekTotalAmortisasi();
            return;
        }

        // 2. Format dengan titik setiap 3 digit
        var rupiah = '';
        var jumlahDigit = value.length;
        var sisa = jumlahDigit % 3;
        
        if (sisa > 0) {
            rupiah = value.substr(0, sisa) + '.';
        }
        
        for (var i = sisa; i < jumlahDigit; i += 3) {
            rupiah += value.substr(i, 3) + (i + 3 < jumlahDigit ? '.' : '');
        }
        
        input.value = rupiah;

        // 3. Langsung cek total
        this.cekTotalAmortisasi();
        ms.updateTotalDisplay();
    },

    hapusDanCek: function(btn, event) {
        // Mencegah event bubbling
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }

        var row = $(btn).closest('tr');
        var isDeletedInput = row.find('.is-deleted-input');
        var currentVal = isDeletedInput.val();

        // Toggle Status Hapus
        if (currentVal === '1') {
            // Batalkan Hapus
            isDeletedInput.val('0');
            row.css({'opacity': '1', 'text-decoration': 'none'});
            $(btn).html('<i class="fa fa-trash"></i>').removeClass('btn-warning').addClass('btn-danger');
            
            // Langsung cek total (tanpa alert untuk restore)
            this.cekTotalAmortisasi();
        } else {
            // Tandai Hapus - TERAPKAN EFEK VISUAL DULU
            isDeletedInput.val('1');
            row.css({'opacity': '0.4', 'text-decoration': 'line-through'});
            $(btn).html('<i class="fa fa-undo"></i>').removeClass('btn-danger').addClass('btn-warning');
            
            // Cek total dulu
            this.cekTotalAmortisasi();
            ms.updateTotalDisplay();
            
            // TUNGGU sebentar agar efek visual ter-render, baru tampilkan alert
            setTimeout(function() {
                var perolehanVal = parseFloat($('#perolehan_val').val().replace(/\./g, '')) || 0;
                var totalAmortisasi = 0;
                
                $('#tbody-amortisasi tr').each(function() {
                    if ($(this).find('.is-deleted-input').val() !== '1') {
                        var nilai = parseFloat($(this).find('input[name$="[nilai]"]').val().replace(/\./g, '')) || 0;
                        totalAmortisasi += nilai;
                    }
                });

                // if (totalAmortisasi !== perolehanVal) {
                //     var selisih = Math.abs(perolehanVal - totalAmortisasi);
                //     var statusText = (perolehanVal - totalAmortisasi) > 0 ? 'KURANG' : 'LEBIH';
                //     var formatRupiah = function(angka) { return angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, "."); };

                //     bootbox.alert({
                //         title: '<i class="fa fa-exclamation-triangle text-danger"></i> Peringatan',
                //         message: 'Total amortisasi sekarang <strong>TIDAK SESUAI</strong>.<br>' +
                //                 'Selisih: <strong>Rp ' + formatRupiah(selisih) + ' (' + statusText + ')</strong>.<br><br>' +
                //                 '<em>Silakan tambahkan nilai pada baris lain untuk menutupi kekurangan ini.</em>',
                //         className: 'bootbox-small'
                //     });
                // }
            }, 150); // Delay 150ms agar efek delete terlihat dulu
        }
    },

    cekTotalAmortisasi: function() {
        var perolehanVal = parseFloat($('#perolehan_val').val().replace(/\./g, '')) || 0;
        var totalAmortisasi = 0;

        $('#tbody-amortisasi tr').each(function() {
            if ($(this).find('.is-deleted-input').val() !== '1') {
                var nilai = parseFloat($(this).find('input[name$="[nilai]"]').val().replace(/\./g, '')) || 0;
                totalAmortisasi += nilai;
            }
        });

        var $alertBox = $('#alert-validasi-amortisasi');
        if ($alertBox.length === 0) {
            $alertBox = $('<div id="alert-validasi-amortisasi" class="alert alert-danger" style="margin-bottom: 15px; padding: 10px 15px;"></div>');
            $('#tbody-amortisasi').closest('div[style*="max-height"]').before($alertBox);
        }

        var $btnSimpan = $('.bootbox .modal-footer .btn-primary');
        var formatRupiah = function(angka) { return angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, "."); };

        if (totalAmortisasi !== perolehanVal) {
            var selisih = Math.abs(perolehanVal - totalAmortisasi);
            var statusText = (perolehanVal - totalAmortisasi) > 0 ? 'Kurang' : 'Lebih';

            $alertBox.html(
                '<i class="fa fa-exclamation-triangle"></i> <strong>Total Tidak Sesuai!</strong><br>' +
                'Perolehan: Rp ' + formatRupiah(perolehanVal) + ' | Total: Rp ' + formatRupiah(totalAmortisasi) + '<br>' +
                '<strong>Selisih: Rp ' + formatRupiah(selisih) + ' (' + statusText + ')</strong>'
            ).show();

            if ($btnSimpan.length > 0) {
                $btnSimpan.prop('disabled', true).addClass('disabled').css({'opacity': '0.6', 'cursor': 'not-allowed'});
            }
        } else {
            $alertBox.hide().empty();
            if ($btnSimpan.length > 0) {
                $btnSimpan.prop('disabled', false).removeClass('disabled').css({'opacity': '1', 'cursor': 'pointer'});
            }
        }
    },

    simpanDataAmortisasi : function (idSewa) {
        var dataAmortisasi = [];
        
        $('#tbody-amortisasi tr').each(function() {
            var id = $(this).find('input[name$="[id]"]').val();
            var nilai = $(this).find('input[name$="[nilai]"]').val();
            // AMBIL NILAI IS_DELETED
            var isDeleted = $(this).find('input[name$="[is_deleted]"]').val();

            if (id) {
                var nilaiClean = nilai ? nilai.replace(/\./g, '') : 0;
                
                dataAmortisasi.push({
                    id: id,
                    nilai: nilaiClean,
                    is_deleted: isDeleted // Kirim status delete ke server
                });
            }
        });

        if (dataAmortisasi.length === 0) {
            bootbox.alert('Tidak ada data amortisasi untuk disimpan.');
            return false;
        }

        $.ajax({
            url: 'sewa/MasterSewa/saveAmortisasi',
            type: 'POST',
            dataType: 'JSON',
            data: { 
                params: {
                    id_sewa: idSewa,
                    amortisasi: dataAmortisasi
                }
            },
            beforeSend: function() {
                if (typeof showLoading === 'function') showLoading();
            },
            success: function(response) {
                if (typeof hideLoading === 'function') hideLoading();
                $('.bootbox').modal('hide');

                if (response.status == 1) {
                    bootbox.alert({
                        message: '<i class="fa fa-check-circle text-success"></i> ' + response.message,
                        callback: function() {
                            

                            // if (typeof ms !== 'undefined' && typeof ms.load_data === 'function') {
                            //     ms.load_data(); 
                            // } else {
                            //     location.reload();
                            // }

                            window.location.reload()
                        }
                    });
                } else {
                    bootbox.alert('<i class="fa fa-exclamation-triangle text-danger"></i> ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                if (typeof hideLoading === 'function') hideLoading();
                bootbox.alert('Terjadi kesalahan sistem saat menyimpan data.');
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
    },


    hitungCicilan: function () {
        
        // ============================================================
        // 1. HELPER: Parse & Format Angka
        // ============================================================
        function parseNominal(val) {
            // if (!val) return 0;
            // Hapus "Rp", spasi, titik (pemisah ribuan), ganti koma jadi titik desimal
            return parseFloat(
                String(val)
                    .replace(/Rp/gi, '')
                    .replace(/\./g, '')      // hapus pemisah ribuan
                    .replace(/,/g, '.')      // koma → titik desimal
                    .replace(/\s/g, '')
            ) || 0;
        }

        function formatNominal(angka) {
            if (isNaN(angka) || angka === 0) return '';
            // Bulatkan ke integer, lalu beri pemisah ribuan
            return Math.round(angka).toLocaleString('id-ID');
        }

        // ============================================================
        // 2. CORE: Hitung Cicilan
        // ============================================================
        function hitung() {
            var nominalSewa   = parseNominal($('#nominal_sewa').val());
            var dp            = parseNominal($('#dp').val());
            var durasi        = parseInt($('#durasi_cicilan').val()) || 0;
            var jumlahBulan   = parseInt($('#jumlah_bulan').val()) || 0;


             // 1. AUTO-CORRECT: Jika DP lebih besar dari Nominal Sewa, paksa DP = Nominal Sewa
            if (nominalSewa > 0 && dp > nominalSewa) {
                dp = nominalSewa;
                $('#dp').val(formatNominal(dp)); // Langsung update tampilan input DP
                
                // Opsional: Beri feedback visual sesaat (misal border kuning)
                $('#dp').closest('.form-group').addClass('has-warning');
                setTimeout(function() {
                    $('#dp').closest('.form-group').removeClass('has-warning');
                }, 1500);
            }

            // Jika durasi kosong, fallback ke jumlah_bulan
            // if (durasi <= 0 && jumlahBulan > 0) {
            //     durasi = jumlahBulan;
            //     $('#durasi_cicilan').val(durasi);
            // }

            // Sisa yang harus dicicil = nominal_sewa - dp
            var sisa = nominalSewa - dp;
            if (sisa < 0) sisa = 0;

            // Nominal cicilan per bulan
            var cicilanPerBulan = (durasi > 0) ? (sisa / durasi) : 0;

            // Tampilkan hasil
            $('#nominal_cicilan').val(formatNominal(cicilanPerBulan));

            // ============================================================
            // 3. Validasi & Info (opsional)
            // ============================================================
            var totalBayar = dp + (cicilanPerBulan * durasi);
            var selisih    = nominalSewa - totalBayar;

            // Reset warna
            $('#nominal_cicilan').closest('.input-group')
                .find('.input-group-addon').css('background-color', '');

            if (nominalSewa > 0 && Math.abs(selisih) > 1) {
                // Ada selisih pembulatan → tandai
                $('#nominal_cicilan').closest('.form-group')
                    .find('label').append(
                        ' <small class="text-warning">(selisih: ' +
                        formatNominal(selisih) + ')</small>'
                    );
            } else {
                $('#nominal_cicilan').closest('.form-group')
                    .find('label small').remove();
            }
        }

        // ============================================================
        // 4. EVENT BINDING (hanya sekali)
        // ============================================================
        if (!$('#nominal_sewa').data('cicilan-bound')) {

            // Field yang memicu perhitungan ulang
            var triggers = '#nominal_sewa, #dp, #durasi_cicilan, #jumlah_bulan';

            $(triggers).on('change keyup', function () {
                // Delay sedikit agar value terbaru terbaca
                setTimeout(hitung, 50);
            });

            // Saat pilih jenis_sewa → auto-fill nama_sewa (dari data-attr)
            $('#jenis_sewa').on('change', function () {
                var nama = $(this).find('option:selected').data('nama-sewa') || '';
                $('#nama_sewa').val(nama);
            });

            // Tandai sudah di-bind
            $('#nominal_sewa').data('cicilan-bound', true);
        }

        // Panggil sekali saat pertama load (untuk mode edit)
        hitung();
    },


    updateTotalDisplay: function() {
        var totalAmortisasi = 0;

        // Hitung total hanya dari baris yang TIDAK dihapus (is_deleted !== '1')
        $('#tbody-amortisasi tr').each(function() {
            if ($(this).find('.is-deleted-input').val() !== '1') {
                var nilai = parseFloat($(this).find('input[name$="[nilai]"]').val().replace(/\./g, '')) || 0;
                totalAmortisasi += nilai;
            }
        });

        // Format angka ke Rupiah
        var formatRupiah = function(angka) {
            return 'Rp ' + angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        };

        // Update teks di HTML
        $('#label_total_amortisasi').text(formatRupiah(totalAmortisasi));
    },

    // Preview file sebelum upload
    previewFile: function(input) {
        var file = input.files[0];
        var previewArea = document.getElementById('file_preview_area');
        var previewName = document.getElementById('file_preview_name');
        var previewSize = document.getElementById('file_preview_size');
        var previewImage = document.getElementById('file_preview_image');
        var previewImg = document.getElementById('preview_img');
        
        // Sembunyikan alert file lama saat user memilih file baru
        var oldFileAlert = document.getElementById('old_file_alert');
        if (oldFileAlert) {
            oldFileAlert.style.display = 'none';
        }

        if (!file) {
            previewArea.style.display = 'none';
            return;
        }

        // Validasi tipe file
        var allowedTypes = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'];
        if (allowedTypes.indexOf(file.type) === -1) {
            alert('File harus berformat PDF, JPG, atau PNG!');
            input.value = '';
            previewArea.style.display = 'none';
            // Tampilkan kembali alert file lama jika ada
            if (oldFileAlert) oldFileAlert.style.display = 'block';
            return;
        }

        // Validasi ukuran (maks 5MB)
        var maxSize = 5 * 1024 * 1024;
        if (file.size > maxSize) {
            alert('Ukuran file maksimal 5MB!');
            input.value = '';
            previewArea.style.display = 'none';
            if (oldFileAlert) oldFileAlert.style.display = 'block';
            return;
        }

        // Tampilkan info file baru
        previewName.textContent = file.name;
        previewSize.textContent = '(' + (file.size / 1024 / 1024).toFixed(2) + ' MB)';
        previewArea.style.display = 'block';

        // Preview gambar jika tipe image
        if (file.type.startsWith('image/')) {
            var reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                previewImage.style.display = 'block';
            };
            reader.readAsDataURL(file);
        } else {
            previewImage.style.display = 'none';
        }
    },

    // Hapus file yang dipilih
    clearFile: function() {
        var fileInput = document.getElementById('file_dokumen');
        fileInput.value = '';
        document.getElementById('file_preview_area').style.display = 'none';
        document.getElementById('file_preview_image').style.display = 'none';
        
        // Tampilkan kembali alert file lama jika ada
        var oldFileAlert = document.getElementById('old_file_alert');
        if (oldFileAlert) {
            oldFileAlert.style.display = 'block';
        }
    },
    
};

$(document).ready(function () {
    ms.start_up();
});

// $(document).on('click', '.btn-delete-amort', function() {
//     var btn = $(this);
//     var row = btn.closest('tr');
//     var isDeletedInput = row.find('.is-deleted-input');
    
//     // Cek status saat ini (0 = aktif, 1 = akan dihapus)
//     var isCurrentlyMarkedForDeletion = isDeletedInput.val() === '1';

//     if (!isCurrentlyMarkedForDeletion) {
//         // Tandai untuk dihapus
//         isDeletedInput.val('1');
//         row.css({ 'opacity': '0.4', 'text-decoration': 'line-through' });
//         btn.html('<i class="fa fa-undo"></i>').removeClass('btn-danger').addClass('btn-warning'); // Ubah jadi tombol Undo
//     } else {
//         // Batalkan penghapusan
//         isDeletedInput.val('0');
//         row.css({ 'opacity': '1', 'text-decoration': 'none' });
//         btn.html('<i class="fa fa-trash"></i>').removeClass('btn-warning').addClass('btn-danger'); // Kembali jadi tombol Delete
//     }
// });


