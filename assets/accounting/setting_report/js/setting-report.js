var sr = {
	startUp: function () {
		sr.getLists();
		sr.settingUp();
	}, // end - startUp

	settingUp: function () {
		$('.item').select2();
		$('.nama_coa').select2().on('select2:select', function(e) {
			var _tr = $(this).closest('tr');

			var no_coa = e.params.data.id;
			$(_tr).find('td.coa').text( no_coa );
		});
		$('.posisi').select2();
		$('.posisi_jurnal').select2();
		$('.posisi_data').select2();
	}, // end - settingUp

	addRowGroup: function (elm) {
		let row_group = $(elm).closest('tr.group');
		let row_item_group = $(row_group).next('tr.item-group');

        let tbody = $(row_group).closest('tbody');

        $(row_item_group).find('select.item, select.nama_coa, select.posisi, select.posisi_jurnal, select.posisi_data').select2('destroy')
                                   .removeAttr('data-live-search')
                                   .removeAttr('data-select2-id')
                                   .removeAttr('aria-hidden')
                                   .removeAttr('tabindex');
        $(row_item_group).find('select.item option, select.nama_coa option, select.posisi option, select.posisi_jurnal option, select.posisi_data option').removeAttr('data-select2-id');

        let newRowGroup = row_group.clone();
        let newRowItemGroup = row_item_group.clone();

        newRowGroup.find('input, select, textarea').val('');
        newRowItemGroup.find('input, select, textarea').val('');
        newRowItemGroup.find('td.coa').text('-');

        $(newRowItemGroup).find('tbody tr:not(:first)').remove();

        row_item_group.after(newRowItemGroup);
        row_item_group.after(newRowGroup);

        $.map( $(tbody).find('tr'), function(tr) {
            $(tr).find('.item').select2();
			$(tr).find('.nama_coa').select2().on('select2:select', function(e) {
				var _tr = $(this).closest('tr');

				var no_coa = e.params.data.id;
				$(_tr).find('td.coa').text( no_coa );
			});
			$(tr).find('.posisi').select2();
			$(tr).find('.posisi_jurnal').select2();
			$(tr).find('.posisi_data').select2();
        });

        $('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal], [data-tipe=decimal3],[data-tipe=decimal4], [data-tipe=number]').each(function(){
			$(this).priceFormat(Config[$(this).data('tipe')]);
		});
	}, // end - addRowGroup

	removeRowGroup: function (elm) {
		let row_group = $(elm).closest('tr.group');
		let row_item_group = $(row_group).next('tr.item-group');

		let tbody = $(row_group).closest('tbody');

		if ( $(tbody).find('tr.group').length > 1 ) {
			$(row_group).remove();
			$(row_item_group).remove();
		}
	}, // end - removeRowGroup

	addRowItemGroup: function (elm) {
		let row = $(elm).closest('tr');

        let tbody = $(row).closest('tbody');

        $(row).find('select.item, select.nama_coa, select.posisi, select.posisi_jurnal, select.posisi_data').select2('destroy')
                                   .removeAttr('data-live-search')
                                   .removeAttr('data-select2-id')
                                   .removeAttr('aria-hidden')
                                   .removeAttr('tabindex');
        $(row).find('select.item option, select.nama_coa option, select.posisi option, select.posisi_jurnal option, select.posisi_data option').removeAttr('data-select2-id');

        let newRow = row.clone();

        newRow.find('input, select, textarea').val('');
        newRow.find('td.coa').text('-');

        row.after(newRow);

        $.map( $(tbody).find('tr'), function(tr) {
            $(tr).find('.item').select2();
			$(tr).find('.nama_coa').select2().on('select2:select', function(e) {
				var _tr = $(this).closest('tr');

				var no_coa = e.params.data.id;
				$(_tr).find('td.coa').text( no_coa );
			});
			$(tr).find('.posisi').select2();
			$(tr).find('.posisi_jurnal').select2();
			$(tr).find('.posisi_data').select2();
        });

        $('[data-tipe=integer],[data-tipe=angka],[data-tipe=decimal], [data-tipe=decimal3],[data-tipe=decimal4], [data-tipe=number]').each(function(){
			$(this).priceFormat(Config[$(this).data('tipe')]);
		});
	}, // end - addRowItemGroup

	removeRowItemGroup: function (elm) {
		let row = $(elm).closest('tr');

		let tbody = $(row).closest('tbody');

		if ( $(tbody).find('tr').length > 1 ) {
			$(row).remove();
		}
	}, // end - removeRowItemGroup

	changeTabActive: function(elm) {
        var href = $(elm).data('href');
        var edit = $(elm).data('edit');
        // change tab-menu
        $('.nav-tabs').find('a').removeClass('active');
        $('.nav-tabs').find('a').removeClass('show');
        $('.nav-tabs').find('li a[data-tab='+href+']').addClass('show');
        $('.nav-tabs').find('li a[data-tab='+href+']').addClass('active');

        // change tab-content
        $('.tab-pane').removeClass('show');
        $('.tab-pane').removeClass('active');
        $('div#'+href).addClass('show');
        $('div#'+href).addClass('active');

        var id = $(elm).attr('data-id');

        sr.loadForm(id, edit, href);
    }, // end - changeTabActive

    loadForm: function(id, edit = null, href = null) {
        var dcontent = $('div#'+href);

        var params = {
            'id': id
        };

        $.ajax({
            url : 'accounting/SettingReport/loadForm',
            data : {
                'params' :  params,
                'edit' :  edit
            },
            type : 'GET',
            dataType : 'HTML',
            beforeSend : function(){ App.showLoaderInContent(dcontent); },
            success : function(html){
                App.hideLoaderInContent(dcontent, html);

                sr.settingUp();
            },
        });
    }, // end - loadForm

	getLists: function () {
		var div = $('div#riwayat');

		$.ajax({
            url : 'accounting/SettingReport/getLists',
            data : {},
            type : 'GET',
            dataType : 'HTML',
            beforeSend : function(){ App.showLoaderInContent( $(div).find('tbody') ); },
            success : function(html){
                App.hideLoaderInContent( $(div).find('tbody'), $(html) );
            },
        });
	}, // end - getLists

	save: function () {
		var div = $('div#action');

		var err = 0;
		$.map( $(div).find('[data-required=1]'), function (ipt) {
			if ( empty( $(ipt).val() ) ) {
				$(ipt).parent().addClass('has-error');
				err++;
			} else {
				$(ipt).parent().removeClass('has-error');
			}
		});

		if ( err > 0 ) {
			bootbox.alert('Harap lengkapi data terlebih dahulu.');
		} else {
			bootbox.confirm('Apakah anda yakin ingin menyimpan data setting report ?', function (result) {
				if ( result ) {
					var data_group = $.map( $(div).find('tr.group'), function (tr_group) {
						var tr_item_group = $(tr_group).next('tr.item-group');
						var data_item_group = $.map( $(tr_item_group).find('tbody tr'), function (tr) {
							var _data_item_group = {
								'item': $(tr).find('select.item').select2('val'),
								'coa': $(tr).find('td.coa').text(),
								'posisi': $(tr).find('select.posisi').select2('val'),
								'posisi_jurnal': $(tr).find('select.posisi_jurnal').select2('val'),
								'posisi_data': $(tr).find('select.posisi_data').select2('val'),
								'urut': numeral.unformat($(tr).find('input.urut').val())
							};

							return _data_item_group;
						});

						var _data_group = {
							'nama_group': $(tr_group).find('.nama_group').val(),
							'detail': data_item_group
						};

						return _data_group;
					});

					var nama_laporan = $(div).find('.nama_laporan').val();

					var params = {
						'nama_laporan': nama_laporan,
						'data_group': data_group
					};

					$.ajax({
			            url : 'accounting/SettingReport/save',
			            data : {
			                'params' :  params
			            },
			            type : 'POST',
			            dataType : 'JSON',
			            beforeSend : function(){ showLoading(); },
			            success : function(data){
			                hideLoading();

			                if ( data.status == 1 ) {
			                	bootbox.alert(data.message, function () {
			                		sr.loadForm(data.content.id, null, 'action');
			                		sr.getLists();
			                	});
			                } else {
			                	bootbox.alert(data.message);
			                }
			            },
			        });
				}
			});
		}
	}, // end - save

	edit: function (elm) {
		var div = $('div#action');

		var err = 0;
		$.map( $(div).find('[data-required=1]'), function (ipt) {
			if ( empty( $(ipt).val() ) ) {
				$(ipt).parent().addClass('has-error');
				err++;
			} else {
				$(ipt).parent().removeClass('has-error');
			}
		});

		if ( err > 0 ) {
			bootbox.alert('Harap lengkapi data terlebih dahulu.');
		} else {
			bootbox.confirm('Apakah anda yakin ingin menyimpan data setting report ?', function (result) {
				if ( result ) {
					var data_group = $.map( $(div).find('tr.group'), function (tr_group) {
						var tr_item_group = $(tr_group).next('tr.item-group');
						var data_item_group = $.map( $(tr_item_group).find('tbody tr'), function (tr) {
							var _data_item_group = {
								'item': $(tr).find('select.item').select2('val'),
								'coa': $(tr).find('td.coa').text(),
								'posisi': $(tr).find('select.posisi').select2('val'),
								'posisi_jurnal': $(tr).find('select.posisi_jurnal').select2('val'),
								'posisi_data': $(tr).find('select.posisi_data').select2('val'),
								'urut': numeral.unformat($(tr).find('input.urut').val())
							};

							return _data_item_group;
						});

						var _data_group = {
							'nama_group': $(tr_group).find('.nama_group').val(),
							'detail': data_item_group
						};

						return _data_group;
					});

					var nama_laporan = $(div).find('.nama_laporan').val();

					var params = {
						'id': $(elm).attr('data-id'),
						'nama_laporan': nama_laporan,
						'data_group': data_group
					};

					$.ajax({
			            url : 'accounting/SettingReport/edit',
			            data : {
			                'params' :  params
			            },
			            type : 'POST',
			            dataType : 'JSON',
			            beforeSend : function(){ showLoading(); },
			            success : function(data){
			                hideLoading();

			                if ( data.status == 1 ) {
			                	bootbox.alert(data.message, function () {
			                		sr.loadForm(data.content.id, null, 'action');
			                		sr.getLists();
			                	});
			                } else {
			                	bootbox.alert(data.message);
			                }
			            },
			        });
				}
			});
		}
	}, // end - edit

	delete: function (elm) {
		var div = $('div#action');

		bootbox.confirm('Apakah anda yakin ingin meng-hapus data setting report ?', function (result) {
			if ( result ) {
				var params = {
					'id': $(elm).attr('data-id')
				};

				$.ajax({
		            url : 'accounting/SettingReport/delete',
		            data : {
		                'params' :  params
		            },
		            type : 'POST',
		            dataType : 'JSON',
		            beforeSend : function(){ showLoading(); },
		            success : function(data){
		                hideLoading();

		                if ( data.status == 1 ) {
		                	bootbox.alert(data.message, function () {
		                		sr.loadForm(null, null, 'action');
		                		sr.getLists();
		                	});
		                } else {
		                	bootbox.alert(data.message);
		                }
		            },
		        });
			}
		});
	}, // end - delete
};

sr.startUp();