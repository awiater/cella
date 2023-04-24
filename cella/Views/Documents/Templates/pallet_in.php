<!--  Help 	
	
	<?= GenerateBarcode($reference) ?> generates barcode	
	options: 
	w  - width			 
	h  - height			 
	ts - size of text 1-5
-->
<div style="text-align: center;">
	<div style="height:25%;width:100%;">
		<h2>PALLET REF: <?= $reference ?></h2>		
		<?= GenerateBarcode($reference,['w'=>600,'h'=>200,'ts'=>5]) ?>
	</div>
	<div style="height:25%">
		<h2>JOB CODE: <?= $sorder ?></h2>		
		<?= GenerateBarcode($sorder,['w'=>600,'h'=>200,'ts'=>5],TRUE) ?>
	</div>
  	<p><?= $page.' / '.$maxpages ?></p>
  	<p style="font-size:120px;">GOODS IN</p>
	<!-- Auto print script works only on html templates-->
	<?php if (!empty($autoprint) && $autoprint==TRUE) :?>
	<script>  
		window.onload = function (){
			window.print();	
		}
	</script>
	<?php endif ?>
	
</div>