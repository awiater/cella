<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= $currentView->getTitle() ?></title>
  
  <!-- Default styles definition-->
  <?= $currentView->getCSS() ?>
  <script>
  	
  </script>
</head>
<body onload="window.print();">
<?= $currentView->getScripts() ?>
<script>
	$(function () {
  		$('[data-toggle="tooltip"]').tooltip();
  		$('.alert').alert();
	});
</script>
	<?= !empty($_content) ? $_content : null ?>
</body>
</html>

