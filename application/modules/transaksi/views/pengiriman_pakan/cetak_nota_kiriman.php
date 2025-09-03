<style type="text/css">
	table.border-field td, table.border-field th {
		border: 1px solid;
		border-collapse: collapse;
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
        width: 10%;
    }

	.col-sm-2{
		width: 20%;
	}
    
	.col-sm-3{
		width: 30%;
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

    .col-sm-12{
		width: 100%;
	}

    .text-center {
        text-align: center;
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
		margin: 2em 1em 1em 1em;
		width: 210mm;
		height: 148mm;
	}

	/* @media print {
		html, body {
			width: 210mm;
			height: 297mm;
		}
		/* ... the rest of the rules ... */
	} */
</style>
<div style="font-size: 12pt; font-style: Calibri; width: 100%; border: 1px solid black;">
    <table class="col-sm-12">
        <tr>
            <td class="col-sm-7" style="vertical-align: top;">
                <b>PT. GRIYA MITRA POULTRY</b><br>
                Jl. Gajah Mada Gang XVIII No. 14<br>
                Kaliwates, Jember
            </td>
            <td class="col-sm-5" style="vertical-align: top;">
                <div class="text-center" style="border: 1px solid black;"><b>NOTA KIRIMAN PAKAN</b></div>
                <b><?php echo $data[0]['no_sj']; ?></b>
            </td>
        </tr>
    </table>
    <br>
    <table class="col-sm-12">
        <tr>
            <td class="col-sm-7" style="vertical-align: top;">
            </td>
            <td class="col-sm-5" style="vertical-align: top;">
            </td>
        </tr>
    </table>
</div>