var cim = {
    startUp: function() {
        cim.settingUp();
    }, // end - startUp

    settingUp: function() {
        $("#StartDate").datetimepicker({
            locale: 'id',
            format: 'DD MMM Y',
            useCurrent:false,
            daysOfWeekDisabled: [0, 2, 3, 4, 5, 6],
        });
        $("#EndDate").datetimepicker({
            locale: 'id',
            format: 'DD MMM Y',
            useCurrent:false,
            daysOfWeekDisabled: [1, 2, 3, 4, 5, 6],
        });

        var startDate = $("#StartDate").find('input').attr('data-tgl');
        var endDate = $("#EndDate").find('input').attr('data-tgl');

        if ( !empty(startDate) ) {
            $("#StartDate").data("DateTimePicker").date(moment(new Date(startDate)));
            var minDate = dateSQL($("#StartDate").data("DateTimePicker").date())+' 00:00:00';
            $("#EndDate").data("DateTimePicker").minDate(moment(new Date(minDate)));
        }

        if ( !empty(endDate) ) {
            $("#EndDate").data("DateTimePicker").date(moment(new Date(endDate)));
            var maxDate = dateSQL($("#EndDate").data("DateTimePicker").date())+' 23:59:59';
            $("#StartDate").data("DateTimePicker").maxDate(moment(new Date(maxDate)));
        }

        // var today = moment(new Date()).format('YYYY-MM-DD');
        $("#StartDate").on("dp.change", function (e) {
            var minDate = dateSQL($("#StartDate").data("DateTimePicker").date())+' 00:00:00';
            $("#EndDate").data("DateTimePicker").minDate(moment(new Date(minDate)));
        });
        $("#EndDate").on("dp.change", function (e) {
            var maxDate = dateSQL($("#EndDate").data("DateTimePicker").date())+' 23:59:59';
            $("#StartDate").data("DateTimePicker").maxDate(moment(new Date(maxDate)));
            // if ( maxDate >= (today+' 00:00:00') ) {
            // }
        });

        $('.perusahaan').select2({placeholder: " -- Pilih Perusahaan --"});
        $('.unit').select2({placeholder: " -- Pilih Unit --"});

        $('[data-tipe=integer], [data-tipe=angka], [data-tipe=decimal], [data-tipe=decimal3],[data-tipe=decimal4], [data-tipe=number]').each(function(){
            $(this).priceFormat(Config[$(this).data('tipe')]);
        });
    }, // end - settingUp

    getLists: function() {
        var dcontent = $('table.tblRiwayat tbody');

        var params = {
            'start_date': dateSQL( $('#StartDate').data('DateTimePicker').date() ),
            'end_date': dateSQL( $('#EndDate').data('DateTimePicker').date() ),
            'perusahaan': $('.perusahaan').select2('val'),
            'unit': $('.unit').select2('val'),
        };

        $.ajax({
            url: 'report/ChickInMingguan/getLists',
            data: {
                'params': params
            },
            type: 'GET',
            dataType: 'HTML',
            beforeSend: function(){ App.showLoaderInContent(dcontent); },
            success: function(html){
                App.hideLoaderInContent(dcontent, html);
            }
        });
    }, // end - getLists
};

cim.startUp();