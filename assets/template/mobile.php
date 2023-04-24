<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= $currentView->getTitle() ?></title>
  
  <!-- Default styles definition-->
  <?= $currentView->getCSS() ?>
</head>
<body class="hold-transition sidebar-mini">

<?= $currentView->getScripts() ?>
<script>
	$(function () {
  		$('[data-toggle="tooltip"]').tooltip();
  		$('.alert').alert();
	});
	window.onload = function() {
    var el = document.documentElement,
        rfs = el.requestFullScreen
        || el.webkitRequestFullScreen
        || el.mozRequestFullScreen;
    rfs.call(el);
};
</script>
			
<!-- Site wrapper -->
<div class="wrapper">
  <!-- Navbar -->
  <nav class="main-header navbar navbar-expand navbar-white navbar-light">
  	<ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" href="<?= site_url() ?>" role="button"><i class="fas fa-bars"></i></a>
      </li>
    </ul>
    <!-- Right navbar links-->
    <img src="<?= parsePath('@template/img/logo.jpg'); ?>" alt="<?= $config->company ?>" class="img-thumbnai" style="opacity: .8;height:53px;">
    <?php  if(ENVIRONMENT=='development') : ?>
    <h2 class="text-danger ml-3">SYSTEM IN DEV/TEST MODE</h2>
    <?php endif ?>
    <ul class="navbar-nav ml-auto">
      <li class="nav-item<?= $currentView->isMobile(FALSE) ? ' d-none' : ''?>">
        <a class="nav-link"  href="<?= url('Users','mode',[],['refurl'=>current_url(FALSE,TRUE)])?>" role="button">
          <?php if (loged_user('interface')==1) :?>
          <i class="fas fa-desktop" data-toggle="tooltip" data-placement="left" title="<?= lang('system.auth.interface_desktop') ?>"></i>
          <?php else :?>
          <i class="fas fa-mobile-alt" data-toggle="tooltip" data-placement="left" title="<?= lang('system.auth.interface_mobile') ?>"></i>	
          <?php endif ?>
        </a>
      </li>
    </ul>
  </nav>
  <!-- /.navbar -->

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Main content -->
    <section class="content body-full mx-auto">
    	
    <?= !empty($error) ? $error : ''; ?>
	<?= $_content ?>
    </section>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->

  <footer class="main-footer">
    <div>
      <b>Version</b> <?= config('APP')->appVersion ?>
    </div>
    <strong>Copyright &copy; <?= Date('Y') ?>&nbsp;&nbsp;<?= $config->company ?>.</strong> All rights reserved.
  </footer>

  <!-- Control Sidebar -->
  <aside class="control-sidebar control-sidebar-dark">
    <!-- Control sidebar content goes here -->
  </aside>
  <!-- /.control-sidebar -->
</div>
<!-- ./wrapper -->
</body>
</html>
