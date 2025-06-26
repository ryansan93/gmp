<!DOCTYPE html>
<html lang="en">

<head>
  <base href="<?php echo base_url() ?>" />
  <link rel="shortcut icon" type="image/x-icon" href="assets/images/logo-mgb.jpg">
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="">
  <meta name="author" content="Dashboard">
  <meta name="keyword" content="Dashboard, Bootstrap, Admin, Template, Theme, Responsive, Fluid, Retina">

  <title>GMP ERP</title>

  <?php // CSS files ?>
  <?php if (isset($css_files) && is_array($css_files)) : ?>
      <?php foreach ($css_files as $css) : ?>
          <?php if ( ! is_null($css)) : ?>
              <link rel="stylesheet" href="<?php echo $css; ?>?v=<?php echo $this->settings->site_version; ?>"><?php echo "\n"; ?>
          <?php endif; ?>
      <?php endforeach; ?>
  <?php endif; ?>

</head>

<body>

  <div class="d-flex" id="wrapper">

    <!-- Sidebar -->
    <div class="bg-light-black" id="sidebar-wrapper" style="width: 17rem;">
      <div class="sidebar-heading">
        <!-- <img src="https://d3ki9tyy5l5ruj.cloudfront.net/obj/3ac85a538c3fc5bb08d0206ede04ae8aa13c20b2/inapp__logo_color_ondark_horizontal.svg" width="80%" height="80%"> -->
        <img src="assets/images/logo-mgb.jpg" width="20%" height="20%">
        GMP ERP
      </div>
      <div class="divider-heading" style="padding: 0rem 1rem;">
        <div class="dropdown-divider" style="margin-top: 0rem;"></div>
      </div>
      <div class="list-group list-group-flush content mCustomScrollbar" style="max-width: 20rem; width: 17rem;">
        <ul class="list-unstyled components">
          <li class="active">
            <a class="list-group-item list-group-item-action bg-light-black menu" data-txt="Dashboard" href="#">
              <i class="fa fa-dashboard"></i>
              <span>Dashboard</span>
            </a>
          </li>
          <?php $arr_fitur = $this->session->userdata()['Fitur']; ?>
          <?php foreach ($arr_fitur as $key => $v_fitur): ?>
            <li>
              <a href="<?php echo '#'.$v_fitur['id_header_fitur'] ?>" data-toggle="collapse" aria-expanded="false" data-val="0" class="dropdown-toggle list-group-item list-group-item-action bg-light-black">
                <?php echo $v_fitur['header_fitur']; ?>
              </a>
              <ul class="collapse list-unstyled" id="<?php echo $v_fitur['id_header_fitur'] ?>">
                <?php foreach ($v_fitur['detail'] as $key => $v_mdetail): ?>
                  <li class="menu">
                    <a href="<?php echo $v_mdetail['path_detfitur']; ?>" class="list-group-item list-group-item-action bg-light-black menu" data-txt="<?php echo $v_mdetail['nama_detfitur']; ?>"><?php echo $v_mdetail['nama_detfitur']; ?></a>
                  </li>
                <?php endforeach ?>
              </ul>
            </li>
          <?php endforeach ?>
        </ul>
      </div>
    </div>
    <!-- /#sidebar-wrapper -->

    <!-- Page Content -->
    <div id="page-content-wrapper">

      <nav class="navbar navbar-expand-lg navbar-light bg-light border-bottom no-padding">
        <!-- <button class="btn btn-primary" id="menu-toggle">Toggle Menu</button> -->
        <a id="menu-toggle" title="Hide Menu">
          <i class="fa fa-angle-left cursor-p left"></i> 
          <i class="fa fa-navicon cursor-p"></i> 
          <i class="fa fa-angle-right cursor-p right" hidden="true"></i>
        </a>

        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
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
                    $data = Modules::run( $v_mdetail['path_detfitur'].'/model', $status);

                    if ( !empty($data) ) {
                      if ( is_array($data) ) {
                        $data = (!empty($data) && $data->count() > 0) ? $data->toArray() : null;
                      }

                      $notif[$v_mdetail['path_detfitur']]['data'] = $data;
                      $notif[$v_mdetail['path_detfitur']]['path'] = $v_mdetail['path_detfitur'];
                      $notif[$v_mdetail['path_detfitur']]['nama_fitur'] = $v_mdetail['nama_detfitur'];
                    }
                  }
                } else if ( $akses['a_approve'] == 1 ) {
                  $status = getStatus('ack');

                  $isi = Modules::run( $v_mdetail['path_detfitur'].'/model', $status);

                  if ( !empty($isi) ) {
                    $data = Modules::run( $v_mdetail['path_detfitur'].'/model', $status);

                    if ( !empty($data) ) {
                      if ( is_array($data) ) {
                        $data = ($data->count() > 0) ? $data->toArray() : null;
                      }

                      $notif[$v_mdetail['path_detfitur']]['data'] = $data;
                      $notif[$v_mdetail['path_detfitur']]['path'] = $v_mdetail['path_detfitur'];
                      $notif[$v_mdetail['path_detfitur']]['nama_fitur'] = $v_mdetail['nama_detfitur'];
                    }
                  }
                }
              }
            }

            $list_notif = $notif;
            $jml_notif = 0;
            if ( !empty($list_notif) && count($list_notif) > 0 ) {
              foreach ($list_notif as $key => $v_notif) {
                $jml_notif += empty($v_notif['data']) ? 0 : count($v_notif['data']);
              }
            }
            // $content['list_notif'] = $this->list_notif();
            // $content['jml_notif'] = count($this->list_notif());
          ?>

          <ul class="navbar-nav ml-auto mt-2 mt-lg-0 pull-left notify-row">
            <li id="header_notification_bar" class="nav-item dropdown">
              <a data-toggle="dropdown" class="notif">
                <i class="fa fa-bell-o cursor-p" style="border: 1px solid black; padding: 7px; border-radius: 5px;"></i>
                <?php if ( $jml_notif > 0 ): ?>
                  <span class="badge bg-warning">
                    <?php echo $jml_notif; ?>
                  </span>
                <?php endif ?>
              </a>
              <ul class="dropdown-menu dropdown-menu-left extended notification no-padding">
                <!-- <div class="notify-arrow notify-arrow-yellow" style="left: 197px;"></div> -->
                <li class="dropdown-item setting bg-warning">
                  <div class="yellow"><i class="fa fa-exclamation-circle"></i> <b>You have <?php echo $jml_notif; ?> new notifications</b></div>
                </li>
                <!-- <div class="dropdown-divider no-padding"></div> -->
                <?php if ( !empty($list_notif) && count($list_notif) > 0 ) { ?>
                  <?php foreach ($list_notif as $key => $v_notif) { ?>
                    <?php foreach ($v_notif['data'] as $k_data => $v_data) { ?>
                      <li class="dropdown-item no-padding cursor-p">
                        <?php 
                          $params = null;
                          if ( isset($v_data['params']) && !empty($v_data['params']) ) {
                            $params = '/index/'.$v_data['params'];
                          }

                          $status = $v_data['status_data'];
                          if ( is_numeric($status) ) {
                            $status = getStatus($status);
                          }

                          $status = strtoupper(substr($status, 0, 1)) . substr($status, 1);

                          $keterangan = $v_notif['nama_fitur'];
                          if ( isset($v_data['keterangan']) && !empty($v_data['keterangan']) ) {
                            $keterangan .= " (".strtoupper($v_data['keterangan']).")";
                          }
                        ?>
                        <a href="<?php echo $v_notif['path'].$params; ?>" title="<?php echo $v_notif['nama_fitur'] . ' (' . $v_data['next_state'] . ')' ; ?>">
                          <!-- <span class="label"><i class="fa fa-info" style="color: black; font-size: 14px;"></i></span> -->
                          <!-- <div class="col-md-8 no-padding"><?php echo $v_notif['nama_fitur']; ?></div> -->
                          <!-- <span class="col-md-8 italic text-left"><?php echo $v_notif['nama_fitur']; ?></span> -->
                          <div class="col-md-12 no-padding">
                            <div class="col-md-8 text-left"><?php echo $keterangan; ?></div>
                            <div class="col-md-4 text-right"><?php echo '(' . $status . ') ' . $v_data['jumlah']; ?></div>
                          </div>
                          <!-- <div class="col-md-4 no-padding"> -->
                            <!-- <span class="col-md-4 italic text-right"><?php echo '(' . $status . ') ' . $v_data['jumlah']; ?></span> -->
                          <!-- </div> -->
                        </a>
                      </li>
                    <?php } ?>
                  <?php } ?>
                <?php } ?>
              </ul>
            </li>
          </ul>

          <div class="col-md-8 title"><?php echo $title_menu; ?></div>

          <ul class="navbar-nav ml-auto mt-2 mt-lg-0 pull-right">
            <!-- <li>
              <div class="dropdown" class="logout">
                <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown">Dropdown</button>
                <div class="dropdown-menu dropdown-menu-right">
                  <a class="dropdown-item" href="#">Link1</a>
                </div>
              </div>
            </li> -->
            <li id="header_notification_bar" class="nav-item dropdown">
              <span class="control-label" style="margin-right: 1rem;" ><?php echo $this->session->userdata()['detail_user']['nama_detuser']; ?></span class="control-label">
              <?php
                $src = 'assets/images/icon-user.png';
                if ( isset($this->session->userdata()['detail_user']['avatar_detuser']) ) {
                  $src = 'uploads/'.$this->session->userdata()['detail_user']['avatar_detuser'];
                }
              ?>
              <img data-toggle="dropdown" src="assets/images/icon-user.png" class="img-circle" aria-expanded="true" width="30" height="30">
              <ul class="dropdown-menu dropdown-menu-right extended notification">
                <li class="dropdown-item setting">
                  Setting
                </li>
                <div class="dropdown-divider no-padding"></div>
                <li class="dropdown-item">
                  <a class="cursor-p" onclick="go_to_profile()">
                    <i class="fa fa-user m-r-5 m-l-5"></i>
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;My Profile
                  </a>
                </li>
                <li class="dropdown-item">
                  <a class="cursor-p" data-toggle="modal" data-target="#logoutModal">
                    <i class="fa fa-power-off m-r-5 m-l-5"></i>
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Logout
                  </a>
                </li>
              </ul>
            </li>
          </ul>
        </div>
      </nav>

      <div class="container-fluid">

        <div class="main">
        <!-- <h1 class="mt-4">Simple Sidebar</h1>
        <p>The starting state of the menu will appear collapsed on smaller screens, and will appear non-collapsed on larger screens. When toggled using the button below, the menu will change.</p>
        <p>Make sure to keep all page content within the <code>#page-content-wrapper</code>. The top navbar is optional, and just for demonstration. Just create an element with the <code>#menu-toggle</code> ID which will toggle the menu when clicked.</p> -->
          <?php echo $view; ?>
        </div>
      </div>
    </div>
    <!-- /#page-content-wrapper -->

  </div>
  <!-- /#wrapper -->

  <!-- Logout Modal-->
  <!-- <div aria-hidden="true" aria-labelledby="myModalLabel" role="dialog" tabindex="-1" id="logoutModal" class="modal fade">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
          <span class="modal-title">Apakah anda yakin ingin keluar ?</span>
        </div>
        <div class="modal-footer">
          <button data-dismiss="modal" class="btn btn-default" type="button">Tidak</button>
          <a class="btn btn-theme" href="user/Login/logout">Ya</a>
        </div>
      </div>
    </div>
  </div> -->
  <div class="modal" id="logoutModal">
    <div class="modal-dialog">
      <div class="modal-content">

        <!-- Modal Header -->
        <div class="modal-header">
          <h4 class="modal-title">Alert</h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>

        <!-- Modal body -->
        <div class="modal-body">
          <span class="modal-title">Apakah anda yakin ingin keluar ?</span>
        </div>

        <!-- Modal footer -->
        <div class="modal-footer">
          <a class="btn btn-primary" href="user/Login/logout">Ya</a>
          <button data-dismiss="modal" class="btn btn-danger" type="button">Tidak</button>
        </div>

      </div>
    </div>
  </div>

  <?php // Javascript files ?>
  <?php if (isset($js_files) && is_array($js_files)) : ?>
      <?php foreach ($js_files as $js) : ?>
          <?php if ( ! is_null($js)) : ?>
              <?php echo "\n"; ?><script type="text/javascript" src="<?php echo $js; ?>?v=<?php echo $this->settings->site_version; ?>"></script><?php echo "\n"; ?>
          <?php endif; ?>
      <?php endforeach; ?>
  <?php endif; ?>

  <!-- Menu Toggle Script -->
  <script>
    $("#menu-toggle").click(function(e) {
      e.preventDefault();
      $("#wrapper").toggleClass("toggled");
      var togled = $("#wrapper").attr('class').split(" ");

      if ( togled.length > 1 ) {
        $("#wrapper").find('a#menu-toggle').attr('title', 'Show Menu');
        $("#wrapper").find('i.left').attr('hidden', true);
        $("#wrapper").find('i.right').removeAttr('hidden');
        $(".tu-float-btn-left").removeClass('toggled');
      } else {
        $("#wrapper").find('a#menu-toggle').attr('title', 'Hide Menu');
        $("#wrapper").find('i.left').removeAttr('hidden');
        $("#wrapper").find('i.right').attr('hidden', true);
        $(".tu-float-btn-left").addClass('toggled');
      };
    });

    $(".dropdown-toggle").click(function(e) {
      $(this).closest('li').toggleClass("open");
    });

    $("a.menu").click(function(e) {
      _getDataSummaryPanenDanDoc.abort();
      _getDataPanjualanDanHarga.abort();
      _getDataPlasmaMerah.abort();
    });

    (function($){
      $(window).on("load",function(){
        
        $("#content-1").mCustomScrollbar({
          theme:"minimal"
        });
        
      });
    })(jQuery);

    function go_to_profile (elm) {
      var url = 'master/User/profile';
      goToURL(url);
    }
  </script>

</body>

</html>
