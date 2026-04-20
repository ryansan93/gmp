<style type="text/css">
	table.border-field {
		border-collapse: collapse;
	}

	table.border-field td, table.border-field th {
		border: 1px solid;
		padding-left: 5px;
		padding-right: 5px;
	}

	.header-title{
		font-size: 14px;
		text-align: center;
	}

	.sapronak{
		width: 100%;
		border-spacing: 0;
		border-collapse: collapse;
		margin-bottom: 1px;
		font-size: 12px;
	}

	.ttah{
		width: 100%;
		border-spacing: 0;
		border-collapse: collapse;
		margin-bottom: 1px;
		font-size: 12px;
	}

	.table-bordered{
		border: 1px solid #000;
	}

	.table-nobordered{
		border: 0px solid #000;
	}

	th.bordered, td.bordered{
		border: 1px solid #000;	
		background-color: #d1d1d1;
	}
    
    .col-sm-1{
        width: 8.333333333333333%;
    }

	.col-sm-2{
		width: 16.66666666666667%;
	}
    
	.col-sm-3{
		width: 25%;
	}

	.col-sm-4 {
		width: 33.33333333333333%;
	}

    .col-sm-5{
        width: 41.66666666666667%;
    }

	.col-sm-6{
		width: 50%;
	}

    .col-sm-7{
		width: 58.33333333333333%;
	}

	.col-sm-9{
		width: 75%;
	}

    .col-sm-12{
		width: 100%;
	}

    .text-center {
        text-align: center;
    }

	.text-right {
        text-align: right;
    }
	
	.table-nobordered-padding td, .table-nobordered-padding th{
		padding-left: 3px;
	}

	.angka {
		text-align: right;
		padding-right: 3px;
	}

	.sapronak td, .sapronak th{
		padding: 3px;
	}

	/* @page{
		margin: 2em 1em 1em 1em;
	} */

	@page{
		size: a5 landscape;
		margin: 0.5em 1em 1em 0.5em;
		width: 210mm;
		height: 148mm;
	}

	/* @media print {
		html, body {
			width: 210mm;
			height: 297mm;
		}
	} */
</style>
<div style="font-style: Calibri; width: 100%; border: 1px solid black; padding: 5px;">
    <table class="col-sm-12">
        <tr>
            <td class="col-sm-7" style="vertical-align: top; font-size: 12pt;">
                <b>PT. GRIYA MITRA POULTRY</b><br>
                Jl. Gajah Mada Gang XVIII No. 14<br>
                Kaliwates, Jember
            </td>
            <td class="col-sm-5 text-center" style="vertical-align: top;">
                <div class="text-center" style="border: 1px solid black; font-size: 12pt;"><b>NOTA KIRIMAN PAKAN</b></div>
                <b style="font-size: 11pt;"><?php echo $data[0]['no_sj']; ?></b>
            </td>
        </tr>
    </table>
    <br>
    <table class="col-sm-12" style="font-size: 11pt;">
        <tr>
            <td class="col-sm-6" style="vertical-align: top;">
				<table class="col-sm-12">
					<tr>
						<td class="col-sm-2" style="vertical-align: top;">Tanggal</td>
						<td class="col-sm-1" style="vertical-align: top;">:</td>
						<td class="col-sm-9" style="vertical-align: top;"><?php echo strtoupper(tglIndonesia($data[0]['tanggal'], '-', ' ', true)); ?></td>
					</tr>
					<tr>
						<td class="col-sm-2" style="vertical-align: top;">No. Pol Truck</td>
						<td class="col-sm-1" style="vertical-align: top;">:</td>
						<td class="col-sm-9" style="vertical-align: top;"><?php echo strtoupper($data[0]['no_polisi']); ?></td>
					</tr>
					<tr>
						<td class="col-sm-4" style="vertical-align: top;">Sopir</td>
						<td class="col-sm-1" style="vertical-align: top;">:</td>
						<td class="col-sm-7" style="vertical-align: top;"><?php echo strtoupper($data[0]['sopir']); ?></td>
					</tr>
				</table>
            </td>
            <td class="col-sm-6" style="vertical-align: top;">
				<table class="col-sm-12">
					<tr>
						<td class="col-sm-2" style="vertical-align: top;">Kepada</td>
						<td class="col-sm-1" style="vertical-align: top;">:</td>
						<td class="col-sm-9" style="vertical-align: top;"><?php echo strtoupper($data[0]['nama']); ?></td>
					</tr>
					<tr>
						<td class="col-sm-2" style="vertical-align: top;">Alamat</td>
						<td class="col-sm-1" style="vertical-align: top;">:</td>
						<?php
							$rt_kdg = !empty($data[0]['alamat_rt']) ? ', RT.'.$data[0]['alamat_rt'] : null;
							$rw_kdg = !empty($data[0]['alamat_rw']) ? ' / RW.'.$data[0]['alamat_rw'] : null;
							$kelurahan_kdg = !empty($data[0]['alamat_kelurahan']) ? ', Kel. '.$data[0]['alamat_kelurahan'] : null;
							$kecamatan_kdg = !empty($data[0]['alamat_kecamatan']) ? ', Kec. '.$data[0]['alamat_kecamatan'] : null;
							$kab_kota_kdg = !empty($data[0]['alamat_kab_kota']) ? ', '.str_replace('Kota ', '', str_replace('Kab ', '', $data[0]['alamat_kab_kota'])) : null;

							$alamat_kdg = $data[0]['alamat_jalan'] . $rt_kdg . $rw_kdg . $kelurahan_kdg . $kecamatan_kdg . $kab_kota_kdg;
						?>
						<td class="col-sm-9" style="vertical-align: top;"><?php echo strtoupper($alamat_kdg); ?></td>
					</tr>
				</table>
            </td>
        </tr>
    </table>
	<hr>
	<table class="col-sm-12 border-field" style="font-size: 11pt;">
		<thead>
			<tr>
				<th class="col-sm-1">NO</th>
				<th class="col-sm-3">JENIS PAKAN</th>
				<th class="col-sm-2">ZAK</th>
				<th class="col-sm-2">KG</th>
				<th class="col-sm-4">KETERANGAN</th>
			</tr>
		</thead>
		<tbody>
			<?php $tot_zak = 0; $tot_jumlah = 0; ?>
			<?php for ($i=0; $i < 6; $i++) { ?>
				<?php if ( isset($data[$i]) ) { ?>
					<tr>
						<td class="text-center"><?php echo $i+1; ?></td>
						<td><?php echo $data[$i]['nama_barang']; ?></td>
						<td class="text-right"><?php echo angkaRibuan($data[$i]['zak']); ?></td>
						<td class="text-right"><?php echo angkaRibuan($data[$i]['jumlah']); ?></td>
						<td><?php echo $data[$i]['keterangan']; ?></td>
					</tr>
					<?php $tot_zak += $data[$i]['zak']; $tot_jumlah += $data[$i]['jumlah']; ?>
				<?php } else { ?>
					<tr>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td class="text-right">&nbsp;</td>
						<td class="text-right">&nbsp;</td>
						<td>&nbsp;</td>
					</tr>
				<?php } ?>
			<?php } ?>
		</tbody>
		<tfoot>
			<tr>
				<th class="text-right" colspan="2">TOTAL</th>
				<th class="text-right"><?php echo angkaRibuan($tot_zak); ?></th>
				<th class="text-right"><?php echo angkaRibuan($tot_jumlah); ?></th>
				<th></th>
			</tr>
		</tfoot>
	</table>
	<hr>
	<table class="col-sm-12">
		<tbody>
			<tr>
				<td class="col-sm-1">
					&nbsp;
				</td>
				<td class="col-sm-4 text-center">
					Tanda Terima
					<br>
					<br>
					<br>
					<br>
					<br>
					<hr>
				</td>
				<td class="col-sm-2">
					&nbsp;
				</td>
				<td class="col-sm-4 text-center">
					Hormat kami
					<br>
					<br>
					<br>
					<br>
					<br>
					<hr>
				</td>
				<td class="col-sm-1">
					&nbsp;
				</td>
			</tr>
		</tbody>
	</table>
</div>