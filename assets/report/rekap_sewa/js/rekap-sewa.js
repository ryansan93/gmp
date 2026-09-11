let rs = {
    searchTimer: null,

    start_up: function () {
        rs.init_select2();
        rs.bind_search_filter();
        rs.load_data();
    },

    init_select2: function () {
        if ( $.fn.select2 ) {
            $('#filter_jenis_sewa, #filter_supplier, #filter_status').each(function () {
                $(this).select2({
                    width: '100%',
                    minimumResultsForSearch: 6,
                    dropdownParent: $(this).closest('.rs-panel')
                });
            });
        }
    },

    bind_search_filter: function () {
        $('#search_sewa').off('input.rsSearchFilter').on('input.rsSearchFilter', function () {
            clearTimeout(rs.searchTimer);
            rs.searchTimer = setTimeout(function () {
                rs.filterData();
            }, 300);
        });
    },

    getFilterParams: function () {
        return {
            jenis_sewa: $('#filter_jenis_sewa').val() || '',
            supplier: $('#filter_supplier').val() || '',
            status: $('#filter_status').val() || '',
            search: $('#search_sewa').val() || ''
        };
    },

    load_data: function () {
        $.ajax({
            url: 'report/RekapSewa/getLists',
            type: 'POST',
            data: rs.getFilterParams(),
            dataType: 'HTML',
            beforeSend: function () {
                showLoading();
            },
            success: function (data) {
                $('#table-rekap-sewa tbody').html(data);
                rs.update_summary();
                hideLoading();
            },
            error: function () {
                hideLoading();
                console.log('Error get data from ajax');
            }
        });
    },

    update_summary: function () {
        var rows = $('#table-rekap-sewa tbody tr.tr_loop');

        var total = rows.length;
        var aktif = 0;
        var selesai = 0;

        rows.each(function () {
            var status = $(this).data('status');
            if ( status === 'Aktif' ) {
                aktif++;
            } else if ( status === 'Selesai' ) {
                selesai++;
            }
        });

        $('#stat-total').text(total);
        $('#stat-aktif').text(aktif);
        $('#stat-selesai').text(selesai);

        $('#rs-row-count').text(total > 0 ? (total + ' kontrak') : '');
    },

    filterData: function (event) {
        if ( event ) {
            event.preventDefault();
        }

        rs.load_data();
    },

    resetFilter: function (event) {
        if ( event ) {
            event.preventDefault();
        }

        $('#filter_jenis_sewa').val('').trigger('change');
        $('#filter_supplier').val('').trigger('change');
        $('#filter_status').val('').trigger('change');
        $('#search_sewa').val('');

        rs.filterData(event);
    },

    exportData: function (event) {
        if ( event ) {
            event.preventDefault();
        }

        let row = $('#table-rekap-sewa tbody tr.tr_loop').length;
        if ( row < 1 ) {
            bootbox.alert('Tidak ada data untuk di export');
            return;
        }

        $.ajax({
            url: 'report/RekapSewa/encryptParams',
            type: 'POST',
            data: { params: rs.getFilterParams() },
            dataType: 'JSON',
            beforeSend: function () {
                showLoading();
            },
            success: function (response) {
                hideLoading();
                if ( response.status == 1 ) {
                    goToURL('report/RekapSewa/exportExcel/' + response.content);
                } else {
                    bootbox.alert(response.message);
                }
            },
            error: function () {
                hideLoading();
                bootbox.alert('Terjadi kesalahan saat export data.');
            }
        });
    }
};

$(document).ready(function () {
    rs.start_up();
});
