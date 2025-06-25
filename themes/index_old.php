<!DOCTYPE html>
<html lang="en">

<head>
  <base href="<?php echo base_url() ?>" />
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="">
  <meta name="author" content="Dashboard">
  <meta name="keyword" content="Dashboard, Bootstrap, Admin, Template, Theme, Responsive, Fluid, Retina">
  <title>EKSPEDISIERP</title>

  <?php // CSS files ?>
  <?php if (isset($css_files) && is_array($css_files)) : ?>
      <?php foreach ($css_files as $css) : ?>
          <?php if ( ! is_null($css)) : ?>
              <link rel="stylesheet" href="<?php echo $css; ?>?v=<?php echo $this->settings->site_version; ?>"><?php echo "\n"; ?>
          <?php endif; ?>
      <?php endforeach; ?>
  <?php endif; ?>

  <!-- Favicons -->
  <!-- <link href="assets/themes/img/favicon.png" rel="icon"> -->
  <!-- <link href="assets/themes/img/apple-touch-icon.png" rel="apple-touch-icon"> -->

  <script src="assets/themes/lib/chart-master/Chart.js"></script>

  <!-- =======================================================
    Template Name: Dashio
    Template URL: https://templatemag.com/dashio-bootstrap-admin-template/
    Author: TemplateMag.com
    License: https://templatemag.com/license/
  ======================================================= -->
</head>

<body>
  <section id="container">
    <!-- **********************************************************************************************************************************************************
        TOP BAR CONTENT & NOTIFICATIONS
        *********************************************************************************************************************************************************** -->
    <!--header start-->
    <header class="header black-bg">
      <div class="sidebar-toggle-box">
        <div class="fa fa-bars tooltips" data-placement="right" data-original-title="Toggle Navigation"></div>
      </div>
      <!--logo start-->
      <!-- <a href="index.html" class="logo"><b>DAI<span>SHO</span></b></a> -->
      <a href="index.php" class="logo"><b>EKSPEDISI<span>ERP</span></b></a>
      <!--logo end-->

      <div class="nav notify-row pull-right" id="top_menu">
        <ul class="nav pull-right top-menu">
          <!-- <li>
            <div class="dropdown" class="logout">
              <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown">Dropdown</button>
              <div class="dropdown-menu dropdown-menu-right">
                <a class="dropdown-item" href="#">Link1</a>
              </div>
            </div>
          </li> -->
          <li id="header_notification_bar" class="dropdown">
            <span class="label" style="font-size: 14px;"><?php echo $this->session->userdata()['detail_user']['nama_detuser']; ?></span>
            <?php
              $src = 'uploads/icon-user.png';
              if ( isset($this->session->userdata()['detail_user']['avatar_detuser']) ) {
                $src = 'uploads/'.$this->session->userdata()['detail_user']['avatar_detuser'];
              }
            ?>
            <img data-toggle="dropdown" src="<?php echo $src; ?>" class="img-circle" width="30" height="30">
            <ul class="dropdown-menu dropdown-menu-right extended notification">
              <li>
                <p class="red">Setting</p>
              </li>
              <li>
                <a onclick="go_to_profile()">
                  <i class="fa fa-user m-r-5 m-l-5"></i>
                  &nbsp&nbsp&nbsp&nbsp&nbspMy Profile
                </a>
              </li>
              <li>
                <a data-toggle="modal" data-target="#logoutModal">
                  <i class="fa fa-power-off m-r-5 m-l-5"></i>
                  &nbsp&nbsp&nbsp&nbsp&nbspLogout
                </a>
              </li>
            </ul>
          </li>
        </ul>
      </div>

      <?php 
        $notif = null;
        $arr_fitur = $this->session->userdata()['Fitur']; 
        foreach ($arr_fitur as $key => $v_fitur) {
          foreach ($v_fitur['detail'] as $key => $v_mdetail) {
            $akses = hakAkses('/'.$v_mdetail['path_detfitur']);
            if ( $akses['a_ack'] == 1 ) {
              $status = getStatus('submit');

              $isi = Modules::run( $v_mdetail['path_detfitur'].'/model', $status);

              if ( !empty($isi) ) {
                $data = Modules::run( $v_mdetail['path_detfitur'].'/model', $status)->first();

                if ( !empty($data) ) {
                  $notif[$v_mdetail['path_detfitur']] = $data->toArray();
                  $notif[$v_mdetail['path_detfitur']]['path'] = $v_mdetail['path_detfitur'];
                  $notif[$v_mdetail['path_detfitur']]['nama_fitur'] = $v_mdetail['nama_detfitur'];
                }
              }


            } else if ( $akses['a_approve'] == 1 ) {
              $status = getStatus('ack');

              $isi = Modules::run( $v_mdetail['path_detfitur'].'/model', $status);

              if ( !empty($isi) ) {
                $data = Modules::run( $v_mdetail['path_detfitur'].'/model', $status)->first();

                if ( !empty($data) ) {
                  $notif[$v_mdetail['path_detfitur']] = $data->toArray();
                  $notif[$v_mdetail['path_detfitur']]['path'] = $v_mdetail['path_detfitur'];
                  $notif[$v_mdetail['path_detfitur']]['nama_fitur'] = $v_mdetail['nama_detfitur'];
                }
              }
            }
          }
        }

        $list_notif = $notif;
        $jml_notif = count($notif);
        // $content['list_notif'] = $this->list_notif();
        // $content['jml_notif'] = count($this->list_notif());
      ?>

      <div class="nav notify-row pull-right" id="top_menu">
        <!--  notification start -->
        <ul class="nav top-menu">
          <!-- notification dropdown start-->
          <li id="header_notification_bar" class="dropdown">
            <a data-toggle="dropdown" class="dropdown-toggle" href="index.html#">
              <i class="fa fa-bell-o"></i>
              <?php if ( $jml_notif > 0 ): ?>
                <span class="badge bg-warning">
                  <?php echo $jml_notif; ?>
                </span>
              <?php endif ?>
            </a>
            <ul class="dropdown-menu dropdown-menu-right extended notification">
              <div class="notify-arrow notify-arrow-yellow" style="left: 197px;"></div>
              <li>
                <p class="yellow">You have <?php echo $jml_notif; ?> new notifications</p>
              </li>
              <li>
                <?php if ( count($list_notif) > 0 ): ?>
                  <?php foreach ($list_notif as $key => $v_notif): ?>
                    <a href="<?php echo $v_notif['path']; ?>" title="<?php echo $v_notif['nama_fitur'] . ' (' . $v_notif['next_state'] . ')' ; ?>">
                      <!-- <span class="label"><i class="fa fa-info" style="color: black; font-size: 14px;"></i></span> -->
                      <?php echo $v_notif['nama_fitur']; ?>
                      <?php 
                        $status = $v_notif['status_data'];
                        if ( is_numeric($status) ) {
                          $status = getStatus($status);
                        }

                        $status = strtoupper(substr($status, 0, 1)) . substr($status, 1);
                      ?>
                      <span class="italic pull-right"><?php echo '(' . $status . ') ' . $v_notif['jumlah']; ?></span>
                    </a>
                  <?php endforeach ?>
                <?php endif ?>
              </li>
              <?php if ( $jml_notif > 5 ): ?>
                <li>
                  <a href="index.html#">See all notifications</a>
                </li>
              <?php endif ?>
            </ul>
          </li>
          <!-- notification dropdown end -->
        </ul>
        <!--  notification end -->
      </div>
    </header>
    <!--header end-->
    <!-- **********************************************************************************************************************************************************
        MAIN SIDEBAR MENU
        *********************************************************************************************************************************************************** -->
    <!--sidebar start-->
    <aside>
      <div id="sidebar" class="nav-collapse ">
        <!-- sidebar menu start-->
        <ul class="sidebar-menu" id="nav-accordion">
          <li class="mt">
            <a class="active" href="index.php">
              <i class="fa fa-dashboard"></i>
              <span>Dashboard</span>
              </a>
          </li>

          <?php $arr_fitur = $this->session->userdata()['Fitur']; ?>
          <?php foreach ($arr_fitur as $key => $v_fitur): ?>
            <li class="sub-menu">
              <a id="sub" href="javascript:;" onclick="change_arrow(this)" data-val="0">
                <span><?php echo $v_fitur['header_fitur']; ?></span>
                <i id="sub_hide" class="fa fa-angle-double-right pull-right" aria-hidden="true"></i>
                <i id="sub_show" class="fa fa-angle-double-down pull-right hide" aria-hidden="true"></i>
              </a>
              <ul class="sub">
                <?php foreach ($v_fitur['detail'] as $key => $v_mdetail): ?>
                  <li>
                    <a href="<?php echo $v_mdetail['path_detfitur']; ?>"><?php echo $v_mdetail['nama_detfitur']; ?></a>
                  </li>
                <?php endforeach ?>
              </ul>
            </li>
          <?php endforeach ?>
        </ul>
        <!-- sidebar menu end-->
      </div>
    </aside>
    <!--sidebar end-->
    <!-- **********************************************************************************************************************************************************
        MAIN CONTENT
        *********************************************************************************************************************************************************** -->
    <!--main content start-->
    <section id="main-content">
      <section class="wrapper">

        <!-- Logout Modal-->
        <div aria-hidden="true" aria-labelledby="myModalLabel" role="dialog" tabindex="-1" id="logoutModal" class="modal fade">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Apakah Anda Yakin Ingin Keluar ?</h4>
              </div>
              <div class="modal-footer">
                <button data-dismiss="modal" class="btn btn-default" type="button">Tidak</button>
                <a class="btn btn-theme" href="user/Login/logout">Ya</a>
              </div>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-lg-12 main-chart">
            <?php echo $view; ?>
          </div>
        </div>
        <!-- /row -->
      </section>
    </section>
  </section>
</body>
<?php // Javascript files ?>
<?php if (isset($js_files) && is_array($js_files)) : ?>
    <?php foreach ($js_files as $js) : ?>
        <?php if ( ! is_null($js)) : ?>
            <?php echo "\n"; ?><script type="text/javascript" src="<?php echo $js; ?>?v=<?php echo $this->settings->site_version; ?>"></script><?php echo "\n"; ?>
        <?php endif; ?>
    <?php endforeach; ?>
<?php endif; ?>
<!-- <script type="text/javascript" src="assets/jquery/jquery.redirect.js"></script> -->
<script type="text/javascript">
    // NOTE : GO TO URL WITH JQUERY.REDIRECT
    // jQuery(function($){
    //   $("a.go_to_profile").click(function(){
    //       var id_user = $(this).data('id');
    //       $.redirect('master/User/profile',
    //       {
    //         id_user: id_user
    //       },
    //       "POST",null,null,true);
    //   });
    // });

    function go_to_profile (elm) {
      var url = 'master/User/profile';
      goToURL(url);
    }
</script>
</html>
