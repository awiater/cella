<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta http-equiv="x-ua-compatible" content="ie=edge">
  <title><?= $currentView->getTitle() ?></title>
  
  <!-- Default styles definition-->
  <?= $currentView->getCSS() ?>
</head>
<body class="hold-transition login-page">	
	<?= $currentView->getScripts() ?>
	<script>
		$(function () {
  			$('[data-toggle="tooltip"]').tooltip();
  			$('.alert').alert();
		});
	</script>
	<div class="login-box bg-white card">
  		<div class="login-logo">
  			<img src="<?= parsePath('@template/img/logo.jpg'); ?>" alt="" class="img-fluid mt-1" style="opacity: .8;height:60px;">
  		</div>
  		<div id="loginindex_error"><?= $error ?></div>
  		
  		<?= $this->renderSection('body') ?>
	</div>
</body>
</html>
