<!--  Help 	
	
	<?= GenerateBarcode($reference) ?> generates barcode	
	options: 
	w  - width			 
	h  - height			 
	ts - size of text 1-5
-->
<div style="text-align: center;">
	<p style="font-size:80px;"><?= $corder; ?></p>
	<p style="font-size:80px;"><?= $stack; ?></p>
  	<p style="font-size:80px;"><?= $status; ?></p>
  	<div style="height:25%;width:100%;">	
		<?= GenerateBarcode($reference,['w'=>600,'h'=>200,'ts'=>5]) ?>
	</div>
  	<p style="font-size:40px;"><?= $reference; ?></p>
	<!-- Auto print script works only on html templates-->
	<?php if (!empty($autoprint) && $autoprint==TRUE) :?>
	<script>  
		window.onload = function (){
			window.print();	
		}
	</script>
	<?php endif ?>
</div>