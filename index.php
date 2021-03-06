<?php
$p = (isset($_REQUEST['p']) ? $_REQUEST['p'] : 'index');
?>
<!doctype html>
<!--[if lt IE 7]> <html class="no-js lt-ie9 lt-ie8 lt-ie7" lang="en"> <![endif]-->
<!--[if IE 7]>    <html class="no-js lt-ie9 lt-ie8" lang="en"> <![endif]-->
<!--[if IE 8]>    <html class="no-js lt-ie9" lang="en"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" lang="en"> <!--<![endif]-->
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

	<title>BandClash</title>
	<meta name="description" content="A linked data application, in which two Bands clash together in a heroic fight over their dominance in their greatestness">
	<meta name="author" content="Felix Epp, Thomas Grah">

	<meta name="viewport" content="width=device-width">

	<link rel="stylesheet" href="css/bootstrap.min.css">
	<style>
	body {
	  padding-top: 60px;
	  padding-bottom: 40px;
	}
	</style>
	<link rel="stylesheet" href="css/bootstrap-responsive.min.css">
	<link rel="stylesheet" href="css/style.css">

	<script src="js/libs/modernizr-2.5.3-respond-1.1.0.min.js"></script>
</head>
<body>
    <div class="navbar navbar-fixed-top">
      <div class="navbar-inner">
        <div class="container">
          <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </a>
          <a class="brand" href="./">BandClash</a>
          <div class="nav-collapse">
            <ul class="nav">
              <li<?php echo ($p == 'index' ? ' class="active"' : '') ?>><a href="./">Home</a></li>
              <li<?php echo ($p == 'admin' ? ' class="active"' : '') ?>><a href="?p=admin">Admin Panel</a></li>
              <li<?php echo ($p == 'about' ? ' class="active"' : '') ?>><a href="?p=about">About</a></li>
            </ul>
          </div><!--/.nav-collapse -->
        </div>
      </div>
    </div>

    <div class="container">

      <?php
      include($p . '.inc.php');
      ?>

      <hr>

      <footer>
        <p>&copy; 2013 Felix Epp, Thomas Grah | h_da</p>
      </footer>

    </div> <!-- /container -->
    <script src="js/libs/jquery-1.7.2.min.js"></script>
<!-- <script src="//ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
<script>window.jQuery || document.write('<script src="js/libs/jquery-1.7.2.min.js"><\/script>')</script> -->

<script src="js/libs/bootstrap/bootstrap.min.js"></script>

<script src="js/plugins.js"></script>
<script src="js/<?php echo $p ?>.js"></script>
</body>
</html>