<?php $error_id = uniqid('error', true); ?>
<!doctype html>
<html>
<head>
	<meta charset="UTF-8">
	<meta name="robots" content="noindex">

	<title><?= esc($title) ?></title>
  	<link href="<?= site_url() ?>/assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
	<link href="<?= site_url() ?>/assets/vendor/bootstrap/css/bootstrap-switch-button.min.css" rel="stylesheet" type="text/css" />
	<link href="<?= site_url() ?>/assets/vendor/jquery/jquery-ui.min.css" rel="stylesheet" type="text/css" />
	<link href="<?= site_url() ?>/assets/template/css/adminlte.min.css" rel="stylesheet" type="text/css" />
	<link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback" rel="stylesheet" type="text/css" />
</head>
<body>
	<script src="<?= site_url() ?>/assets/vendor/jquery/jquery.min.js" type="text/javascript"></script>
	<script src="<?= site_url() ?>/assets/vendor/jquery/jquery-ui.min.js" type="text/javascript"></script>
	<script src="<?= site_url() ?>/assets/vendor/jquery/popper.js" type="text/javascript"></script>
	<script src="<?= site_url() ?>/assets/vendor/bootstrap/js/bootstrap.bundle.min.js" type="text/javascript"></script>
	<script src="<?= site_url() ?>/assets/vendor/bootstrap/js/bootstrap-switch-button.js" type="text/javascript"></script>
	<script src="<?= site_url() ?>/assets/template/js/adminlte.min.js" type="text/javascript"></script>
<section class="content">
      <div class="error-page">
        <h2 class="headline text-danger">500</h2>

        <div class="error-content">
          <h3><i class="fas fa-exclamation-triangle text-danger"></i> Oops! Something went wrong.</h3>

          <p>
            System crash with fata error.  
            Meanwhile, you may <a href="<?= site_url() ?>">return to dashboard</a>
          </p>
          <?php $errorCode=base64_encode(
          	(esc($title).esc($exception->getCode() ? ' #' . $exception->getCode() : '')).PHP_EOL.
          	(esc($exception->getMessage())).PHP_EOL.
          	(esc(static::cleanPath($file, $line)).' at line '.esc($line))     	
          	); ?>          <p class="text-danger">
          	Please send below error code to <a href="mailto:<?= config('APP')->supportEmail ?>?subject=Error on <?= ' '.site_url().', Code:'.esc($exception->getCode()) ?>&body=<?= $errorCode ?>"><?= config('APP')->supportEmail ?></a>
          </p>
          <div class="border" style="word-wrap: break-word;">
          	<?= $errorCode ?>
          </div>
        </div>
      </div>
      <!-- /.error-page -->

    </section>

</body>
</html>
