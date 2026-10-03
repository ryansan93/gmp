var sr = {
	startUp: function () {
		sr.getLists();
		sr.settingUp();
	},

	settingUp: function () {
		var div = $('div#action');

		$(div).find('.item').select2();
		$(div).find('.nama_coa').select2().on('select2:select', function (e) {
			$(this).closest('tr').find('td.coa').text(e.params.data.id);
		});
		$(div).find('.sign').select2();
		$(div).find('.tipe_group').select2().on('select2:select', function (e) {
			sr.toggleTipeGroup($(this).closest('tr.group'), e.params.data.id);
		});

		// Re-apply toggle state for already-rendered groups
		$(div).find('tr.group').each(function () {
			var tipe = $(this).find('.tipe_group').val();
			if (tipe) sr.toggleTipeGroup($(this), tipe);
		});
	},

	toggleTipeGroup: function (row_group, tipe) {
		var row_item_group = $(row_group).next('tr.item-group');
		var ref_wrapper    = $(row_group).find('.ref-group-wrapper');

		if (tipe === 'subtotal') {
			$(row_item_group).hide();
			$(ref_wrapper).show();
		} else {
			$(row_item_group).show();
			$(ref_wrapper).hide();
		}
	},

	updateGroupBadges: function () {
		var n = 1;
		$('div#action tr.group').each(function () {
			$(this).find('.group-badge').text(n++);
		});
	},

	addRowGroup: function (elm) {
		var row_group      = $(elm).closest('tr.group');
		var row_item_group = $(row_group).next('tr.item-group');
		var tbody          = $(row_group).closest('tbody');

		// Ambil HTML opsi bersih dari row pertama item group sebelum select2 di-destroy
		var $firstItemRow = $(row_item_group).find('tbody tr:first');
		var itemHtml = '', coaHtml = '';
		$firstItemRow.find('select.item option').each(function () {
			itemHtml += '<option value="' + $(this).val() + '">' + $(this).text() + '</option>';
		});
		$firstItemRow.find('select.nama_coa option').each(function () {
			coaHtml += '<option value="' + $(this).val() + '">' + $(this).text() + '</option>';
		});
		var signHtml = '<option value="">-- Pilih --</option><option value="1">+1 (Normal)</option><option value="-1">-1 (Balik Saldo)</option>';

		// Destroy select2 before cloning
		var selects = 'select.item, select.nama_coa, select.sign, select.tipe_group';
		$(row_item_group).find(selects).add($(row_group).find('select.tipe_group'))
			.select2('destroy')
			.removeAttr('data-live-search data-select2-id aria-hidden tabindex');

		var newRowGroup     = row_group.clone();
		var newRowItemGroup = row_item_group.clone();

		row_item_group.after(newRowItemGroup);
		row_item_group.after(newRowGroup);

		// Reset newRowGroup
		newRowGroup.find('input.nama_group, input.ref_group_ids, input.urut_group').val('');
		newRowGroup.find('select.tipe_group').html('<option value="data">DATA</option><option value="subtotal">SUBTOTAL</option>');
		newRowGroup.find('.ref-group-wrapper').hide();

		// Reset newRowItemGroup — rebuild select HTML dari nol agar tidak ada selected state
		newRowItemGroup.find('tbody tr:not(:first)').remove();
		var $newFirstRow = newRowItemGroup.find('tbody tr:first');
		$newFirstRow.find('input, textarea').val('');
		$newFirstRow.find('td.coa').text('-');
		$newFirstRow.find('select.item').html(itemHtml);
		$newFirstRow.find('select.nama_coa').html(coaHtml);
		$newFirstRow.find('select.sign').html(signHtml);
		newRowItemGroup.show();

		sr._initRow(row_group);
		$(row_item_group).find('tbody tr').each(function () { sr._initRow(this); });
		sr._initRow(newRowGroup);
		$(newRowItemGroup).find('tbody tr').each(function () { sr._initRow(this); });
		sr.updateGroupBadges();

		$('[data-tipe=integer]').each(function () {
			$(this).priceFormat(Config[$(this).data('tipe')]);
		});
	},

	removeRowGroup: function (elm) {
		var row_group      = $(elm).closest('tr.group');
		var row_item_group = $(row_group).next('tr.item-group');
		var tbody          = $(row_group).closest('tbody');

		if ($(tbody).find('tr.group').length > 1) {
			$(row_group).remove();
			$(row_item_group).remove();
			sr.updateGroupBadges();
		}
	},

	addRowItemGroup: function (elm) {
		var row   = $(elm).closest('tr');
		var tbody = $(row).closest('tbody');

		// Ambil HTML opsi bersih sebelum select2 di-destroy
		var itemHtml = '', coaHtml = '';
		$(row).find('select.item option').each(function () {
			itemHtml += '<option value="' + $(this).val() + '">' + $(this).text() + '</option>';
		});
		$(row).find('select.nama_coa option').each(function () {
			coaHtml += '<option value="' + $(this).val() + '">' + $(this).text() + '</option>';
		});
		var signHtml = '<option value="">-- Pilih --</option><option value="1">+1 (Normal)</option><option value="-1">-1 (Balik Saldo)</option>';

		var selects = 'select.item, select.nama_coa, select.sign';
		$(row).find(selects)
			.select2('destroy')
			.removeAttr('data-live-search data-select2-id aria-hidden tabindex');

		var newRow = row.clone();
		row.after(newRow);

		// Rebuild HTML opsi dari nol — tidak ada selected state
		newRow.find('input, textarea').val('');
		newRow.find('td.coa').text('-');
		newRow.find('select.item').html(itemHtml);
		newRow.find('select.nama_coa').html(coaHtml);
		newRow.find('select.sign').html(signHtml);

		sr._initRow(row);
		sr._initRow(newRow);

		$('[data-tipe=integer]').each(function () {
			$(this).priceFormat(Config[$(this).data('tipe')]);
		});
	},

	removeRowItemGroup: function (elm) {
		var row   = $(elm).closest('tr');
		var tbody = $(row).closest('tbody');

		if ($(tbody).find('tr').length > 1) {
			$(row).remove();
		}
	},

	_initRow: function (tr) {
		$(tr).find('.item').select2();
		$(tr).find('.nama_coa').select2().on('select2:select', function (e) {
			$(this).closest('tr').find('td.coa').text(e.params.data.id);
		});
		$(tr).find('.sign').select2();
		$(tr).find('.tipe_group').select2().on('select2:select', function (e) {
			sr.toggleTipeGroup($(this).closest('tr.group'), e.params.data.id);
		});
	},

	changeTabActive: function (elm) {
		var href = $(elm).data('href');
		var edit = $(elm).data('edit');

		$('.nav-tabs').find('a').removeClass('active show');
		$('.nav-tabs').find('li a[data-tab=' + href + ']').addClass('show active');
		$('.tab-pane').removeClass('show active');
		$('div#' + href).addClass('show active');

		sr.loadForm($(elm).attr('data-id'), edit, href);
	},

	loadForm: function (id, edit, href) {
		href = href || 'action';
		var dcontent = $('div#' + href);
		var params   = { 'id': id };

		$.ajax({
			url      : 'accounting/SettingReport/loadForm',
			data     : { 'params': params, 'edit': edit },
			type     : 'GET',
			dataType : 'HTML',
			beforeSend: function () { App.showLoaderInContent(dcontent); },
			success  : function (html) {
				App.hideLoaderInContent(dcontent, html);
				sr.settingUp();
			}
		});
	},

	getLists: function () {
		var div = $('div#riwayat');
		$.ajax({
			url      : 'accounting/SettingReport/getLists',
			data     : {},
			type     : 'GET',
			dataType : 'HTML',
			beforeSend: function () { App.showLoaderInContent($(div).find('tbody')); },
			success  : function (html) { App.hideLoaderInContent($(div).find('tbody'), $(html)); }
		});
	},

	_collectGroups: function (div) {
		return $.map($(div).find('tr.group'), function (tr_group) {
			var tipe_group  = $(tr_group).find('select.tipe_group').val() || 'data';
			var ref_ids_val = $(tr_group).find('input.ref_group_ids').val();
			var detail      = [];

			if (tipe_group === 'data') {
				var tr_item = $(tr_group).next('tr.item-group');
				detail = $.map($(tr_item).find('tbody tr'), function (tr) {
					var item_val = $(tr).find('select.item').val();
					var coa_val  = $.trim($(tr).find('td.coa').text());
					if ( !item_val || !coa_val || coa_val === '-' ) return null;
					return {
						'item' : item_val,
						'coa'  : coa_val,
						'sign' : $(tr).find('select.sign').val() || '1',
						'urut' : numeral.unformat($(tr).find('input.urut').val())
					};
				});
			}

			return {
				'nama_group'    : $(tr_group).find('.nama_group').val(),
				'tipe_group'    : tipe_group,
				'ref_group_ids' : ref_ids_val,
				'urut_group'    : numeral.unformat($(tr_group).find('input.urut_group').val()),
				'detail'        : detail
			};
		});
	},

	save: function () {
		var div = $('div#action');
		var err = 0;

		$.map($(div).find('[data-required=1]'), function (ipt) {
			// skip field di dalam elemen tersembunyi (misal: item-group subtotal)
			if ( !$(ipt).is(':visible') ) {
				$(ipt).parent().removeClass('has-error');
				return;
			}
			if (empty($(ipt).val())) {
				$(ipt).parent().addClass('has-error'); err++;
			} else {
				$(ipt).parent().removeClass('has-error');
			}
		});

		if (err > 0) { bootbox.alert('Harap lengkapi data terlebih dahulu.'); return; }

		bootbox.confirm('Apakah anda yakin ingin menyimpan data setting report?', function (result) {
			if (!result) return;

			var params = {
				'nama_laporan' : $(div).find('.nama_laporan').val(),
				'data_group'   : sr._collectGroups(div)
			};

			$.ajax({
				url      : 'accounting/SettingReport/save',
				data     : { 'params': params },
				type     : 'POST',
				dataType : 'JSON',
				beforeSend: function () { showLoading(); },
				success  : function (data) {
					hideLoading();
					if (data.status == 1) {
						bootbox.alert(data.message, function () {
							sr.loadForm(data.content.id, null, 'action');
							sr.getLists();
						});
					} else {
						bootbox.alert(data.message);
					}
				}
			});
		});
	},

	edit: function (elm) {
		var div = $('div#action');
		var err = 0;

		$.map($(div).find('[data-required=1]'), function (ipt) {
			// skip field di dalam elemen tersembunyi (misal: item-group subtotal)
			if ( !$(ipt).is(':visible') ) {
				$(ipt).parent().removeClass('has-error');
				return;
			}
			if (empty($(ipt).val())) {
				$(ipt).parent().addClass('has-error'); err++;
			} else {
				$(ipt).parent().removeClass('has-error');
			}
		});

		if (err > 0) { bootbox.alert('Harap lengkapi data terlebih dahulu.'); return; }

		bootbox.confirm('Apakah anda yakin ingin menyimpan perubahan?', function (result) {
			if (!result) return;

			var params = {
				'id'           : $(elm).attr('data-id'),
				'nama_laporan' : $(div).find('.nama_laporan').val(),
				'data_group'   : sr._collectGroups(div)
			};

			$.ajax({
				url      : 'accounting/SettingReport/edit',
				data     : { 'params': params },
				type     : 'POST',
				dataType : 'JSON',
				beforeSend: function () { showLoading(); },
				success  : function (data) {
					hideLoading();
					if (data.status == 1) {
						bootbox.alert(data.message, function () {
							sr.loadForm(data.content.id, null, 'action');
							sr.getLists();
						});
					} else {
						bootbox.alert(data.message);
					}
				}
			});
		});
	},

	exportExcel: function (elm) {
		var id = $(elm).attr('data-id');
		goToURL('accounting/SettingReport/exportExcel/' + id);
	},

	delete: function (elm) {
		bootbox.confirm('Apakah anda yakin ingin menghapus data setting report?', function (result) {
			if (!result) return;

			$.ajax({
				url      : 'accounting/SettingReport/delete',
				data     : { 'params': { 'id': $(elm).attr('data-id') } },
				type     : 'POST',
				dataType : 'JSON',
				beforeSend: function () { showLoading(); },
				success  : function (data) {
					hideLoading();
					if (data.status == 1) {
						bootbox.alert(data.message, function () {
							sr.loadForm(null, null, 'action');
							sr.getLists();
						});
					} else {
						bootbox.alert(data.message);
					}
				}
			});
		});
	}
};

sr.startUp();
