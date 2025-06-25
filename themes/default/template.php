<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Default Public Template
 */
?><!DOCTYPE html>
<html>
<head>
    <base href="<?php echo base_url() ?>" />
    <link rel="shortcut icon" type="image/x-icon" href="assets/images/wonokoyo-icon.ico">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
	  <link rel="shortcut icon" type="image/x-icon" href="/favicon.ico?v=<?php echo $this->settings->site_version; ?>">
	  <link rel="icon" type="image/x-icon" href="/favicon.ico?v=<?php echo $this->settings->site_version; ?>">
    <title><?php echo $page_title; ?> - <?php echo $this->settings->site_name; ?></title>
    <meta name="keywords" content="<?php echo $this->settings->meta_keywords; ?>">
    <meta name="description" content="<?php echo $this->settings->meta_description; ?>">

    <?php // CSS files ?>
    <?php if (isset($css_files) && is_array($css_files)) : ?>
        <?php foreach ($css_files as $css) : ?>
            <?php if ( ! is_null($css)) : ?>
                <link rel="stylesheet" href="<?php echo $css; ?>?v=<?php echo $this->settings->site_version; ?>"><?php echo "\n"; ?>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>
<body>

    <?php // Fixed navbar ?>
    <nav class="navbar navbar-default navbar-static-top">
        <div class="container">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                    <span class="sr-only"><?php echo lang('core button toggle_nav'); ?></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="<?php echo base_url() ?>"><?php echo $this->settings->site_name; ?></a>
            </div>
            <div id="navbar" class="navbar-collapse collapse">
                <?php // Nav bar left ?>
                <ul class="nav navbar-nav">
                    <li class="dropdown<?php echo (strstr(uri_string(), 'master')) ? ' active' : ''; ?>">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">Master<span class="caret"></span></a>
                        <ul class="dropdown-menu" role="menu">
                            <li class="<?php echo (uri_string() == 'master/kendaraan') ? 'active' : ''; ?>"><a href="master/kendaraan">Kendaraan</a></li>
                            <li class="<?php echo (uri_string() == 'master/sopir') ? 'active' : ''; ?>"><a href="master/sopir">Sopir</a></li>
                            <li class="<?php echo (uri_string() == 'master/mitra') ? 'active' : ''; ?>"><a href="master/mitra">Mitra</a></li>
                            <li class="<?php echo (uri_string() == 'master/rpa') ? 'active' : ''; ?>"><a href="master/rpa">RPA</a></li>
                            <li class="<?php echo (uri_string() == 'master/group') ? 'active' : ''; ?>"><a href="master/group">Group</a></li>
                            <li class="<?php echo (uri_string() == 'master/users') ? 'active' : ''; ?>"><a href="master/users">User</a></li>
                            <li class="<?php echo (uri_string() == 'master/panen') ? 'active' : ''; ?>"><a href="master/panen">Panen</a></li>
                            <li class="<?php echo (uri_string() == 'master/StandartPerforma') ? 'active' : ''; ?>"><a href="master/StandartPerforma">Standar Performa</a></li>
                            <li class="<?php echo (uri_string() == 'master/harga_bottom_price') ? 'active' : ''; ?>"><a href="master/harga_bottom_price">Bottom Price</a></li>
                            <li class="<?php echo (uri_string() == 'master/PembayaranSopir') ? 'active' : ''; ?>"><a href="master/PembayaranSopir">Ketentuan Pembayaran Sopir</a></li>
                            <li class="<?php echo (uri_string() == 'master/harga') ? 'active' : ''; ?>"><a href="master/harga">Harga</a></li>
                            <li class="<?php echo (uri_string() == 'master/TglForecast') ? 'active' : ''; ?>"><a href="master/TglForecast">Tanggal Forecast</a></li>
                            <li class="<?php echo (uri_string() == 'master/Pelanggan') ? 'active' : ''; ?>"><a href="master/Pelanggan">Pelanggan</a></li>
                        </ul>
                    </li>
                    <li class="dropdown<?php echo (strstr(uri_string(), 'forecast')) ? ' active' : ''; ?>">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">Forecast<span class="caret"></span></a>
                        <ul class="dropdown-menu" role="menu">
                            <li class="<?php echo (uri_string() == 'forecast/panen') ? 'active' : ''; ?>"><a href="forecast/panen">Forecast Panen</a></li>
                            <li class="<?php echo (uri_string() == 'forecast/konfirmasipanen') ? 'active' : ''; ?>"><a href="forecast/konfirmasipanen">Konfirmasi Data Panen</a></li>
                            <li class="<?php echo (uri_string() == 'forcast/daftarkonfirmasiayamsisa') ? 'active' : ''; ?>"><a href="forecast/daftarkonfirmasiayamsisa">Daftar Konfirmasi Ayam Sisa</a></li>
                            <!-- <li class="<?php echo (uri_string() == 'forecast/approvaldirut') ? 'active' : ''; ?>"><a href="forecast/approvaldirut">Laporan Realisasi DOC In & Rencana Panen</a></li> -->
                        </ul>
                    </li>
                    <li class="dropdown<?php echo (strstr(uri_string(), 'spj')) ? ' active' : ''; ?>">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">SPJ<span class="caret"></span></a>
                        <ul class="dropdown-menu" role="menu">
                            <li class="<?php echo (uri_string() == 'spj/rekapitulasi') ? 'active' : ''; ?>"><a href="spj/rekapitulasi">Rekapitulasi SPJ</a></li>
                            <li class="<?php echo (uri_string() == 'spj/so_do') ? 'active' : ''; ?>"><a href="spj/so_do">SO / DO</a></li>
                            <li class="<?php echo (uri_string() == 'spj/so_do_bakul') ? 'active' : ''; ?>"><a href="spj/so_do_bakul">SO / DO Bakul</a></li>
                            <li class="<?php echo (uri_string() == 'spj/so_do/batal_do') ? 'active' : ''; ?>"><a href="spj/so_do/batal_do">SO / DO Batal</a></li>
                            <li class="<?php echo (uri_string() == 'spj/so_do/confirm') ? 'active' : ''; ?>"><a href="spj/so_do/confirm">ACK SO/DO</a></li>
                            <li class="<?php echo (uri_string() == 'spj/so_do/cetak_do') ? 'active' : ''; ?>"><a href="spj/so_do/cetak_do">Cetak DO</a></li>
                            <li class="<?php echo (uri_string() == 'spj/so_do_bakul/cetak_do_bakul') ? 'active' : ''; ?>"><a href="spj/so_do_bakul/cetak_do_bakul">Cetak DO Bakul Scan</a></li>
                            <li class="<?php echo (uri_string() == 'spj/so_do_bakul/index_cetak_do') ? 'active' : ''; ?>"><a href="spj/so_do_bakul/index_cetak_do">Cetak DO Bakul</a></li>
                            <li class="<?php echo (uri_string() == 'spj/so_do/cetak_so_index') ? 'active' : ''; ?>"><a href="spj/so_do/cetak_so_index">Cetak SO</a></li>
                            <li class="<?php echo (uri_string() == 'spj/so_do/laporan_do') ? 'active' : ''; ?>"><a href="spj/so_do/laporan_do">Laporan DO</a></li>
                       </ul>
                    </li>
					<li class="dropdown<?php echo (strstr(uri_string(), 'report/')) ? ' active' : ''; ?>">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">Report<span class="caret"></span></a>
                        <ul class="dropdown-menu" role="menu">
                            <li class="<?php echo (uri_string() == 'report/rekaptimpanen') ? 'active' : ''; ?>"><a href="report/rekaptimpanen">Laporan Rekapitulasi Tim Panen</a></li>
                            <li class="<?php echo (uri_string() == 'report/laba_rugi_sopir') ? 'active' : ''; ?>"><a href="report/laba_rugi_sopir">Laporan Analisa Laba Rugi Sopir</a></li>
                            <li class="<?php echo (uri_string() == 'report/PerformaSopir') ? 'active' : ''; ?>"><a href="report/PerformaSopir">Laporan Performa Sopir</a></li>
                            <li class="<?php echo (uri_string() == 'report/KPAH') ? 'active' : ''; ?>"><a href="report/KPAH">Laporan KPAH</a></li>
                            <li hidden class="<?php echo (uri_string() == 'report/KontrolPenerimaanAyamHidup') ? 'active' : ''; ?>"><a href="report/KontrolPenerimaanAyamHidup">Kontrol Penerimaan Ayam Hidup</a></li>
                            <li class="<?php echo (uri_string() == 'report/PengajuanPembayaranPremiTimPanen') ? 'active' : ''; ?>"><a href="report/PengajuanPembayaranPremiTimPanen">Pengajuan Pembayaran Premi Tim Panen</a></li>
                            <li class="<?php echo (uri_string() == 'report/Summary_pembayaran_sopir') ? 'active' : ''; ?>"><a href="report/Summary_pembayaran_sopir">Summary Pembayaran Sopir</a></li>
                            <li class="<?php echo (uri_string() == 'report/harga_harian') ? 'active' : ''; ?>"><a href="report/harga_harian">Laporan Harga Harian</a></li>
                            <li class="<?php echo (uri_string() == 'report/pembayaran_hutang_sopir') ? 'active' : ''; ?>"><a href="report/pembayaran_hutang_sopir">Laporan PHS</a></li>
                            <li class="<?php echo (uri_string() == 'report/RPAH') ? 'active' : ''; ?>"><a href="report/RPAH">RPAH</a></li>
                            <li class="<?php echo (uri_string() == 'report/RPAH/index_bakul') ? 'active' : ''; ?>"><a href="report/RPAH/index_bakul">RPAH Pelanggan</a></li>
                            <li class="<?php echo (uri_string() == 'spj/so_do/report_batal_do') ? 'active' : ''; ?>"><a href="spj/so_do/report_batal_do">Laporan DO Batal</a></li>
                            <li class="<?php echo (uri_string() == 'report/Analisa_laba_rugi_sopir') ? 'active' : ''; ?>"><a href="report/Analisa_laba_rugi_sopir">Laporan Analisa Raba Rugi Sopir</a></li>
                        </ul>
                    <li class="dropdown<?php echo (strstr(uri_string(), 'penimbangan')) ? ' active' : ''; ?>">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">Penimbangan<span class="caret"></span></a>
                        <ul class="dropdown-menu" role="menu">
                            <li class="<?php echo (uri_string() == 'penimbangan/timbang_ayam_hidup/scan_do') ? 'active' : ''; ?>"><a href="penimbangan/timbang_ayam_hidup/scan_do">Scan DO</a></li>
                            <li class="<?php echo (uri_string() == 'penimbangan/timbang_ayam_hidup/selesai_potong_do') ? 'active' : ''; ?>"><a href="penimbangan/timbang_ayam_hidup/selesai_potong_do">Scan DO/SJ Selesai</a></li>
                            <li class="<?php echo (uri_string() == 'penimbangan/input_ekor_timbang') ? 'active' : ''; ?>"><a href="penimbangan/input_ekor_timbang">Input Ekor</a></li>
                            <li class="<?php echo (uri_string() == 'penimbangan/pusat/input_ttah') ? 'active' : ''; ?>"><a href="penimbangan/pusat/input_ttah">Input TTAH</a></li>
                            <li class="<?php echo (uri_string() == 'penimbangan/input_data_sj') ? 'active' : ''; ?>"><a href="penimbangan/input_data_sj">Input SJ</a></li>
                            <li class="<?php echo (uri_string() == 'penimbangan/timbang_ayam_hidup/reportPPAH') ? 'active' : ''; ?>"><a href="penimbangan/timbang_ayam_hidup/reportPPAH">Report</a></li>
                            <li class="<?php echo (uri_string() == 'penimbangan/realisasi_penerimaan_ayam_hidup') ? 'active' : ''; ?>"><a href="penimbangan/realisasi_penerimaan_ayam_hidup">RPAH</a></li>
                       </ul>
                    </li>
                    <li class="hide dropdown<?php echo (strstr(uri_string(), 'pum')) ? ' active' : ''; ?>">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">PUM<span class="caret"></span></a>
                        <ul class="dropdown-menu" role="menu">
                            <li class="<?php echo (uri_string() == 'pum/pengajuan_pum') ? 'active' : ''; ?>"><a href="pum/pengajuan_pum">Pengajuan PUM</a></li>
                       </ul>
                    </li>
                    <li class="dropdown<?php echo (strstr(uri_string(), 'penjulan')) ? ' active' : ''; ?>">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">Penjulan<span class="caret"></span></a>
                        <ul class="dropdown-menu" role="menu">
                            <li class="<?php echo (uri_string() == 'penjualan/rpah/indx/rpa') ? 'active' : ''; ?>"><a href="penjualan/RPAH/index/RPA">Rencana Penjualan Ayam Hidup RPA</a></li>
                            <li class="<?php echo (uri_string() == 'penjualan/rpah/indx/bakul') ? 'active' : ''; ?>"><a href="penjualan/RPAH/index/BAKUL">Rencana Penjualan Ayam Hidup Pelanggan</a></li>
                            <li class="<?php echo (uri_string() == 'spj/so_do_bakul/index_list_ver_sodo') ? 'active' : ''; ?>"><a href="spj/so_do_bakul/index_list_ver_sodo">Verifikasi SO/DO Bakul</a></li>
                       </ul>
                    </li>
                    <li class="dropdown<?php echo (strstr(uri_string(), 'pembayaranHutangSopir')) ? ' active' : ''; ?>">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">PHS<span class="caret"></span></a>
                        <ul class="dropdown-menu" role="menu">
                            <li class="<?php echo (uri_string() == 'phs/PembayaranHutangSopir') ? 'active' : ''; ?>"><a href="phs/PembayaranHutangSopir">Pembayaran Hutang Sopir</a></li>
                            <li class="<?php echo (uri_string() == 'phs/pum') ? 'active' : ''; ?>"><a href="phs/pum">PUM Sopir</a></li>
                       </ul>
                    </li>
                    <li class="hide <?php echo (uri_string() == 'sinkronisasi/index') ? 'active' : ''; ?>"><a href="sinkronisasi/index">Sinkronisasi</a></li>
                    <li class="<?php echo (uri_string() == 'sinkronisasi/index') ? 'active' : ''; ?>"><a href="sinkronisasi/index">Ganti Warnet</a></li>
                </ul>
                <?php // Nav bar right ?>
                <ul class="nav navbar-nav navbar-right">
                    <li class="dropdown">
                      <a class="dropdown-toggle" href="#" data-toggle="dropdown" role="button" aria-expanded="false"><?php echo $this->session->userdata('Nama_User'); ?><span class="caret"></span></a>
                      <ul class="dropdown-menu" role="menu">
                        <li><a href="<?php echo base_url('master/users/ubah_password'); ?>">Ganti Password</a></li>
                        <li><a href="<?php echo base_url('logout'); ?>"><?php echo lang('core button logout'); ?></a></li>
                      </ul>
                    </li>

                </ul>
            </div>
        </div>
    </nav>

    <?php // Main body ?>
    <div class="container theme-showcase col-md-12" role="main">

        <?php // System messages ?>
        <?php if ($this->session->flashdata('message')) : ?>
            <div class="row alert alert-success alert-dismissable">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <?php echo $this->session->flashdata('message'); ?>
            </div>
        <?php elseif ($this->session->flashdata('error')) : ?>
            <div class="row alert alert-danger alert-dismissable">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <?php echo $this->session->flashdata('error'); ?>
            </div>
        <?php elseif (validation_errors()) : ?>
            <div class="row alert alert-danger alert-dismissable">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <?php echo validation_errors(); ?>
            </div>
        <?php elseif ($this->error) : ?>
            <div class="row alert alert-danger alert-dismissable">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <?php echo $this->error; ?>
            </div>
        <?php endif; ?>

        <?php // Main content ?>
        <?php echo $content; ?>

    </div>

    <?php // Footer ?>
    <!--<footer>
        <div class="container">
            <div class="clearfix"><hr /></div>
            <p class="text-muted">Page rendered in <strong>{elapsed_time}</strong> seconds | CodeIgniter v<?php echo CI_VERSION; ?> | <?php echo $this->settings->site_name; ?> v<?php echo $this->settings->site_version; ?></p>
        </div>
    </footer>-->

    <?php // Javascript files ?>
    <?php if (isset($js_files) && is_array($js_files)) : ?>
        <?php foreach ($js_files as $js) : ?>
            <?php if ( ! is_null($js)) : ?>
                <?php echo "\n"; ?><script type="text/javascript" src="<?php echo $js; ?>?v=<?php echo $this->settings->site_version; ?>"></script><?php echo "\n"; ?>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php endif; ?>
    <?php if (isset($js_files_i18n) && is_array($js_files_i18n)) : ?>
        <?php foreach ($js_files_i18n as $js) : ?>
            <?php if ( ! is_null($js)) : ?>
                <?php echo "\n"; ?><script type="text/javascript"><?php echo "\n" . $js . "\n"; ?></script><?php echo "\n"; ?>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
