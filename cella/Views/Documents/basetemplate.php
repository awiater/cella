<html>
	<head>
    	<link href="<?= parsePath('@vendor/bootstrap/css/bootstrap.min.css')?>" rel="stylesheet" type="text/css" />
  	</head>
  	<style>
 		.document-page {
    		page-break-after: always;
		}

		.document-page:last-child {
    		page-break-after: avoid;
		}
		
		 @page {
            margin: 0px 0px 0px 0px !important;
            padding: 0px 0px 0px 0px !important;
        }
  	</style>
	<body class="p-0">
		<?php foreach($content as $page) : ?>
			<div class="document-page"><!-- DO NOT DELETE THIS LINE -->
				<?= $page; ?>
			</div>
		<?php endforeach ?>
	</body>
</html>