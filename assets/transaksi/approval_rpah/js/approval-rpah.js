var ar = {
    startUp: function() {
        ar.getListRpahNotApprove();
    }, // end - startUp
    
    getListRpahNotApprove: function() {
        var dcontent = $('div.contain');

        $.ajax({
            url : 'transaksi/ApprovalRpah/getListRpahNotApprove',
            data : {},
            dataType : 'HTML',
            type : 'GET',
            beforeSend : function(){ 
                $('div.refresh').find('button').attr('disabled', 'disabled');
                App.showLoaderInContent( $(dcontent) ); 
            },
            success : function(html){
                App.hideLoaderInContent( $(dcontent), html );
                $('div.refresh').find('button').removeAttr('disabled', 'disabled');
            }
        });
    }, // end - getListRpahNotApprove

    openDetail: function(elm) {
        var tr_head_unit = $(elm).closest('tr.head-unit');
        var tr_detail_unit = $(tr_head_unit).next('tr.detail-unit');

        if ( $(tr_detail_unit).hasClass('hide') ) {
            $(tr_detail_unit).removeClass('hide');
        } else {
            $(tr_detail_unit).addClass('hide');
        }
    }, // end - openDetail

    approve: function(elm) {
        var tr = $(elm).closest('tr.head-unit');

        var id = $(elm).attr('data-id');
        var nama_unit = $(tr).find('.nama-unit b').text();
        var tgl_panen = $(tr).find('.tgl-panen b').text();

        bootbox.confirm('Apakah anda yakin ingin <b style="color: blue;">APPROVE</b> data rpah unit <b>'+nama_unit+'</b> tanggal panen <b>'+tgl_panen+'</b> ?', function(result) {
            if ( result ) {
                var params = {
                    'id': id
                };

                $.ajax({
                    url : 'transaksi/ApprovalRpah/approve',
                    data : {
                        'params': params
                    },
                    type : 'POST',
                    dataType : 'JSON',
                    beforeSend : function(){ showLoading(); },
                    success : function(data){
                        hideLoading();
                        if ( data.status == 1 ) {
                            bootbox.alert( data.message, function() {
                                ar.getListRpahNotApprove();
                            });
                        } else {
                            bootbox.alert( data.message );
                        }
                    }
                });
            }
        });
    }, // end - approve

    reject: function(elm) {
        var tr = $(elm).closest('tr.head-unit');

        var id = $(elm).attr('data-id');
        var nama_unit = $(tr).find('.nama-unit b').text();
        var tgl_panen = $(tr).find('.tgl-panen b').text();

        bootbox.confirm('Apakah anda yakin ingin <b style="color: red;">REJECT</b> data rpah unit <b>'+nama_unit+'</b> tanggal panen <b>'+tgl_panen+'</b> ?', function(result) {
            if ( result ) {
                var params = {
                    'id': id
                };

                $.ajax({
                    url : 'transaksi/ApprovalRpah/reject',
                    data : {
                        'params': params
                    },
                    type : 'POST',
                    dataType : 'JSON',
                    beforeSend : function(){ showLoading(); },
                    success : function(data){
                        hideLoading();
                        if ( data.status == 1 ) {
                            bootbox.alert( data.message, function() {
                                ar.getListRpahNotApprove();
                            });
                        } else {
                            bootbox.alert( data.message );
                        }
                    }
                });
            }
        });
    }, // end - reject
};

ar.startUp();