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
	$(function(){
		setActiveTabFromUrl();
	});
	<?php if (loged_user('autologoff')==1 || loged_user('autologoff')=='1') :?>
	var time = new Date().getTime();
	
	$(document.body).bind("mousemove keypress", function(e) {
         time = new Date().getTime();
     });
	
	$(function () {
  		$('[data-toggle="tooltip"]').tooltip();
  		$('.alert').alert();
  		setTimeout(refresh, 10000);
	});
	
	function refresh() {
         if(new Date().getTime() - time >= 600000) 
             window.location='<?= $config->app->baseURL ?>logout';
         else 
             setTimeout(refresh, 10000);
     }
     <?php endif ?>
</script>				
<!-- Site wrapper -->
<div class="wrapper">
  <!-- Navbar -->
  <nav class="main-header navbar navbar-expand navbar-white navbar-light">
  	<ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
      </li>
    </ul>
    <!-- Right navbar links-->
    <img src="<?= parsePath('@template/img/logo.jpg'); ?>" alt="<?= $config->company ?>" class="img-thumbnai" style="opacity: .8;height:53px;">
    <?php  if(ENVIRONMENT=='development') : ?>
    <h2 class="text-danger ml-3">SYSTEM IN DEV/TEST MODE</h2>
    <?php endif ?>
    <ul class="navbar-nav ml-auto">
      <li class="nav-item">
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

  <!-- Main Sidebar Container -->
  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="<?= site_url() ?>" class="brand-link bg-primary">
      <img src="<?= parsePath('@template/img/logo1.jpg'); ?>" alt="" class="brand-image elevation-3" style="opacity: .9;">
      <span class="brand-text font-weight-light"><?= $config->company ?></span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
      <!-- Sidebar user (optional) -->
      <div class="user-panel mt-3 pb-3 mb-3 d-flex">
       <div class="image">
          <img src="<?= $_User->avatar ?>" class="img-circle elevation-2" alt="User Image">
        </div>
        
        <div class="info text-light">
        	<ul class="nav flex-column">
        	 	<li class="nav-item">
        	 		<?= $_User->name; ?>
        	 	</li>
        	 	<li class="nav-item">
        	 		
        	 	</li>
        	 	<li class="nav-item mt-3">
        	 		<a class="nav-link p-0 text-light" href="<?= url('/logout') ?>"><?= lang('system.auth.logout') ?></a>
        	 	</li>
        	</ul>
        </div>
      </div>

      <!-- Sidebar Menu -->
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
          <!-- Add icons to the links using the .nav-icon class
               with font-awesome or any other icon font library -->
          <li class="nav-item mb-2">
          	<?= $currentView->getHTMLMenu('mainmenu',['image'=>'fas fa-long-arrow-alt-right fa-sm mr-1','ul'=>FALSE]); ?>
          </li>
          <li class="nav-item mt-3">
              <div class="card p-0 m-0">
                  <h5 class="card-header p-1 bg-primary">Recent Pallets</h5>
                  <?= loadModule('Home','lastpallets',['8',['reference','customer','mhdate']]) ?>
              </div>
          </li>
        </ul>
      </nav>
      <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
  </aside>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-12 d-flex bg-white border">
          	<div class="p-1">
          		<?php if ($currentView->isHelpObjectEnabled()) :?>
          			<button type="button" class="btn btn-sm m-0 btn-info p-0 pl-1 pr-1" id="helpContentButton" data-widget="control-sidebar" data-slide="true">
						<i class="far fa-question-circle" data-toggle="tooltip" data-placement="right" title="<?= lang('system.general.helpbtn_tooltip') ?>"></i>
					</button>
					<script>
						$("#helpContentButton").on("click",function(){
							<?php $helpContentModaContent=$currentView->getHelpObject(); if($helpContentModaContent['mode']=='pdf') :?>
							$("#helpContentModalFrame").attr('src','<?= loadModule('Documents','createUrl',[$helpContentModaContent['file'],'aaa']) ?>');
							<?php else :?>
							$("#helpContentModalFrame").attr('srcdoc',atob('<?= $helpContentModaContent['content']?>'));
							<?php endif ?>
						});	
					</script>
          		<?php endif ?>
          	</div>
          	<div class="ml-auto p-1">
          		<?= $currentView->getBreadcrumbs('float-sm-right',TRUE); ?>
          	</div>
          </div>
        </div>
      </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content body-full">
    	<div class="row">
    		<div class="col-12" id="contentContainer">
    			<?= !empty($error) ? $error : ''; ?>
				<?= $_content ?>
    		</div>
		</div>
    </section>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->

  <footer class="main-footer">
    <div class="float-right d-none d-sm-block">
      <b>Version</b> <?= config('APP')->appVersion ?>
    </div>
    <strong>Copyright &copy; <?= Date('Y') ?>&nbsp;&nbsp;<?= $config->company ?>.</strong> All rights reserved.
  </footer>

  <!-- Control Sidebar -->
  <aside class="control-sidebar control-sidebar-dark" style="padding: 20px 5px 5px 5px;width:40%;overflow: hidden;" id="helpContentContainer">
    <!-- Control sidebar content goes here -->
    <iframe src="" name="test" class="w-100 h-100 hide_scroll bg-white" frameborder="0" id="helpContentModalFrame"></iframe>
  </aside>
  <!-- /.control-sidebar -->
</div>
<!-- ./wrapper -->
</body>
</html>
