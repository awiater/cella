<!--  Help 	
	
	GenerateBarcode($reference) generates barcode	
	options: 
	w  - width			 
	h  - height			 
	ts - size of text 1-5
-->
<style>
	table, th, td {
  		border: 1px solid black;
  		border-collapse: collapse;
	}
	
	td { 
    	padding: 10px;
	}
	.value{
		text-align: right;
		width:300px;
	}
	.header{
		font-weight: bold;
		background-color:yellow;
	}
	.palet_value{
		padding: 1px;
		max-width:60px;
		word-wrap:break-word;
		text-align: center;
	}
</style>
<div style="text-align: center;">
	<table>		
	  <tr>
        <td class="header">CTD COLLECTION REFERENCE</td>
        <td class="value"></td>
      </tr>
      <tr>
        <td class="header">CUSTOMER ACCOUNT REFERENCE</td>
        <td class="value"><?= $data['reference'] ?></td>
      </tr>
      <tr>
        <td class="header">NUMBER OF PALLETS</td>
        <td class="value"><?= count($data['ocfg']) ?></td>
      </tr>
    </table>
    <br>
    <table style="">
    	<tr>
    	<?php foreach(array_keys($data['ocfg'][0]) as $record) :?>
    		<td class="header"><?= strtoupper($record) ?></td>
    	<?php endforeach ?>
    	</tr>
    	<?php foreach($data['ocfg'] as $row) :?>
    	<tr>
    		<?php foreach($row as $col) :?>
    			<td class="palet_value"><?= $col ?></td>
    		<?php endforeach ?>
    	</tr>
    	<?php endforeach ?>
    </table>
    
	<!-- Auto print script works only on html templates-->
	<?php if (!empty($autoprint) && $autoprint==TRUE) :?>
	<script>  
		window.onload = function (){
			window.print();	
		}
	</script>
	<?php endif ?>
</div>