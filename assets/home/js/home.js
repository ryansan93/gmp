var _getDataPanjualanDanHarga;
var _getDataPlasmaMerah;
var _getDataSummaryPanenDanDoc;

var home = {
	startUp: function () {
		window.onload = function () {
			home.getDataPanjualanDanHarga();
			home.getDataPlasmaMerah();

			if ( $('.notifContain').length > 0 ) {
				home.getDataNotifikasi();
			}

			if ( $('.dashboard_dirut').length > 0 ) {
				home.getDataSummaryPanenDanDoc();
			}
		};
	}, // end - startUp

	getDataNotifikasi: function () {
		$.ajax({
            url: 'home/Home/getDataNotifikasi',
            data: {},
            type: 'GET',
            dataType: 'HTML',
            beforeSend: function() { App.showLoaderInContent( $('.notifContain') ); },
            success: function(html) {
				App.hideLoaderInContent( $('.notifContain'), html );
            }, error: function(xhr, ajaxOptions, thrownError) { console.log(thrownError); }
        });
	}, // end - getDataNotifContain

	getDataSummaryPanenDanDoc: function () {
		_getDataSummaryPanenDanDoc = $.ajax({
            url: 'home/Home/getDataSummaryPanenDanDoc',
            data: {},
            type: 'POST',
            dataType: 'JSON',
            beforeSend: function() { 
            	$('.dashboard_dirut').find('div.docin div.data').addClass('hide');
				$('.dashboard_dirut').find('div.panen div.data').addClass('hide');
            	App.showLoaderInContent( $('.dashboard_dirut').find('div.docin div.loading') );
            	App.showLoaderInContent( $('.dashboard_dirut').find('div.panen div.loading') );
            	/* showLoading(); */ 
        	},
            success: function(data) {
                /* hideLoading(); */
                $('.dashboard_dirut').find('div.docin div.data').removeClass('hide');
				$('.dashboard_dirut').find('div.panen div.data').removeClass('hide');
                App.hideLoaderInContent( $('.dashboard_dirut').find('div.docin div.loading') );
                App.hideLoaderInContent( $('.dashboard_dirut').find('div.panen div.loading') );

                if ( data.status == 1 ) {
                	var _data_docin = data.content.docin;
                	var _data_panen = data.content.panen;

                	$('.dashboard_dirut').find('label.jml_ekor').text(numeral.formatInt( _data_docin.jml_ekor ));
                	$('.dashboard_dirut').find('label.jml_kdg').text(numeral.formatInt( _data_docin.jml_kdg ));
                	$('.dashboard_dirut').find('label.rata_harga_doc').text(numeral.formatDec( _data_docin.rata_harga_doc ));
                	$('.dashboard_dirut').find('label.rata_harga_pakan').text(numeral.formatDec( _data_docin.rata_harga_pakan ));

                	$('.dashboard_dirut').find('label.tonase').text(numeral.formatDec( _data_panen.tonase ));
					$('.dashboard_dirut').find('label.ekor').text(numeral.formatInt( _data_panen.ekor ));
					$('.dashboard_dirut').find('label.rata_lama_panen').text(numeral.formatDec( _data_panen.rata_lama_panen ));
					$('.dashboard_dirut').find('label.rata_harga').text(numeral.formatDec( _data_panen.rata_harga ));
                } else {
                	bootbox.alert( data.message );
                }
            }, error: function(xhr, ajaxOptions, thrownError) { console.log(thrownError); }
        });
	}, // end - getDataSummaryPanenDanDoc

	getDataPanjualanDanHarga: function () {
		_getDataPanjualanDanHarga = $.ajax({
            url: 'home/Home/getDataPanjualanDanHarga',
            data: {},
            type: 'POST',
            dataType: 'JSON',
            beforeSend: function() { /* showLoading(); */ },
            success: function(data) {
                /* hideLoading(); */
				if ( !empty(data.content) ) {
					home.chartPenjualanDanHarga(data.content.tgl_panen, data.content.harga, data.content.tonase, data.content.tgl_panen_real);
				}
            }, error: function(xhr, ajaxOptions, thrownError) { console.log(thrownError); }
        });
	}, // end - getDataPenjualan

	chartPenjualanDanHarga: function (tgl_panen, harga, tonase, tgl_panen_real) {
		new Chart("chart_penjualan_dan_harga", {
		    data: {
		        datasets: [
			        {
			        	yAxisID: 'A',
			            type: 'line',
			            label: 'HARGA',
			            data: harga,
			            backgroundColor: "rgba(109, 112, 255, 1.0)",
						borderColor: "rgba(109, 112, 255, 0.5)"
			        },
			        {
			        	yAxisID: 'B',
			            type: 'bar',
			            label: 'TONASE',
			            data: tonase,
			            backgroundColor: "rgba(251, 165, 51, 1.0)",
						borderColor: "rgba(251, 165, 51, 0.5)"
			        }
		        ],
		        labels: tgl_panen
		    },
		    options: {
		    	responsive: true,
			    scales: {
					A: {
						type: 'linear',
						position: 'left',
						ticks: { 
							beginAtZero: true, 
							color: "rgba(109, 112, 255, 1.0)",
							callback: function(value, index, ticks) {
		                        return value/1000;
		                    }
						},
						// Hide grid lines, otherwise you have separate grid lines for the 2 y axes
						grid: { display: false }
					},
					B: {
						type: 'linear',
						position: 'right',
						ticks: { 
							beginAtZero: true, 
							color: "rgba(251, 165, 51, 1.0)",
							callback: function(value, index, ticks) {
		                        return value/1000;
		                    }
						},
						grid: { display: false }
					},
				},
				plugins: {
					title: {
						display: true,
						text: 'Penjualan dan Harga Rata-Rata'
					},
					tooltip: {
						displayColors: false,
			            callbacks: {
			            	title: function(tooltipItems, data) {
					            //Return value for title
					            return tgl_panen_real[tooltipItems[0].dataIndex];
					        },
			            }
			        }
				},
		    }
		});
	}, // end - chart

	getDataPlasmaMerah: function () {
		_getDataPlasmaMerah = $.ajax({
            url: 'home/Home/getDataPlasmaMerah',
            data: {},
            type: 'POST',
            dataType: 'JSON',
            beforeSend: function() { /* showLoading(); */ },
            success: function(data) {
                /* hideLoading(); */
				if ( !empty(data.content) ) {
					home.chartPlasmaMerah(data.content.kode_unit, data.content.jumlah, data.content.mitra);
				}
            }, error: function(xhr, ajaxOptions, thrownError) { console.log(thrownError); }
        });
	}, // end - getDataPenjualan

	chartPlasmaMerah: function (kode_unit, jumlah, mitra) {
		var chart = new Chart("chart_plasma_merah", {
		    data: {
		        datasets: [
			        {
			        	yAxisID: 'A',
			            type: 'bar',
			            data: jumlah,
			            label: 'UNIT',
			            backgroundColor: "red",
						borderColor: "red"
			        }
		        ],
		        labels: kode_unit
		    },
		    options: {
		    	responsive: true,
			    scales: {
					A: {
						type: 'linear',
						position: 'left',
						ticks: { 
							beginAtZero: true, 
							color: "black",
							callback: function(value, index, ticks) {
		                        return value;
		                    }
						},
						// Hide grid lines, otherwise you have separate grid lines for the 2 y axes
						grid: { display: false }
					}
				},
				plugins: {
					tooltip: {
						displayColors: false,
			            callbacks: {
			            	title: function(tooltipItems, data) {
					            //Return value for title
					            return tooltipItems[0].label+' (JML : '+tooltipItems[0].formattedValue+')';
					        },
			                label: function(context) {
			                    let label = '';

			                	for (var i = 0; i < mitra[context.dataIndex].length; i++) {
			                		label += mitra[context.dataIndex][i]+'\n';
			                	}

			                    return mitra[context.dataIndex];
			                }
			            }
			        }
				},
		    }
		});
	}, // end - chart
};

home.startUp();