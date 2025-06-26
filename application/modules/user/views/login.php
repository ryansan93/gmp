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

  <!-- Favicons -->
  <!-- <link href="assets/themes/img/favicon.png" rel="icon">
  <link href="assets/themes/img/apple-touch-icon.png" rel="apple-touch-icon"> -->

  <?php // CSS files ?>
  <?php if (isset($css_files) && is_array($css_files)) : ?>
      <?php foreach ($css_files as $css) : ?>
          <?php if ( ! is_null($css)) : ?>
              <link rel="stylesheet" href="<?php echo $css; ?>?v=<?php echo $this->settings->site_version; ?>"><?php echo "\n"; ?>
          <?php endif; ?>
      <?php endforeach; ?>
  <?php endif; ?>
  
  <!-- =======================================================
    Template Name: Dashio
    Template URL: https://templatemag.com/dashio-bootstrap-admin-template/
    Author: TemplateMag.com
    License: https://templatemag.com/license/
  ======================================================= -->
</head>

<body>
  <!-- **********************************************************************************************************************************************************
      MAIN CONTENT
      *********************************************************************************************************************************************************** -->
  <div id="login-page">
    <div class="container">
      <div class="form-login">
        <div class="login-heading text-center">
          <h2 class="form-login-heading">GMP ERP</h2>
        </div>
        <!-- <div class="dropdown-divider no-padding"></div> -->
        <div class="login-contain">
          <form onsubmit="return login.login()">
            <input id="username" type="text" class="form-control uppercase" placeholder="User ID" autofocus>
            <br>
            <input id="password" type="password" class="form-control" placeholder="PASSWORD">
            <span class="pull-right"><br></span>
            <br>
            <button class="btn btn-theme btn-block" type="submit"><i class="fa fa-lock"></i> SIGN IN</button>
            <div id="divinfo"></div>
            <hr>
            <div class="registration">
              Don't have an account yet?<br/>
              Contact admin for create account
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</body>
<?php // Javascript files ?>
<?php if (isset($js_files) && is_array($js_files)) : ?>
    <?php foreach ($js_files as $js) : ?>
        <?php if ( ! is_null($js)) : ?>
            <?php echo "\n"; ?><script type="text/javascript" src="<?php echo $js; ?>?v=<?php echo $this->settings->site_version; ?>"></script><?php echo "\n"; ?>
        <?php endif; ?>
    <?php endforeach; ?>
<?php endif; ?>
</html>
