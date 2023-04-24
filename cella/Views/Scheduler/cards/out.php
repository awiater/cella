<div class="small-box cell card m-0 mb-1" style="height:<?= $board['cardheight']?>px!important;background-color: <?= $data['color'][0]?>;color:rgb(<?= $data['color'][1]?>)" id="id_card_<?=$data['id']?>" data-editable="true" data-editable="true" data-infopanel=".overlay_container">
	<div class="inner">
		<ul class="list-unstyled">
			<li>
				<h5><i class="fas fa-tag mr-1 fa-sm mr-1" style="color:rgb(<?= $data['color'][1] ?>,.5)!important"></i><?= $data['reference']?></h5>
			</li>
			<li><i class="fas fa-pallet mr-1 fa-sm" style="color:rgb(<?= $data['color'][1] ?>,.5)!important"></i><?= $data['pallqty']?></li>
		</ul>	
	</div>
	<div class="icon">
		<i class="fas fa-truck-moving" style="color:rgb(<?= $data['color'][1] ?>,.05)!important"></i> 
	</div>
	<div class="d-none overlay_container" id="id_card_<?=$data['id']?>_overlay">
	 	<div class="overlay"></div>
	 	<div class="overlay_i border">
	 		<?php if ($data['status']=='out') :?>
	 		<button type="button" class="btn btn-link text-white editButton"  id="id_card_<?=$data['id']?>_orderbtn">
	 			<i class="fas fa-pen-square fa-lg"></i>
	 		</button>
	 		<?php endif ?>
	 		<?php if ($data['status']!='out' && $data['invoiced']==0) :?>
	 		<button type="button" class="btn btn-link text-white Button" onClick="window.location='<?= str_replace('-id-', $data['id'], $invoiceurl) ?>'" id="id_card_<?=$data['id']?>_orderbtn" tooltip='<?= lang('scheduler.cards.invoice')?>'>
	 			<i class="fas fa-file-invoice-dollar fa-lg"></i>
	 		</button>
	 		<?php endif ?>
	 		<button type="button" class="btn btn-link text-white infobtn tooltipButton" id="id_card_<?=$data['id']?>_infobtn">
	 			<i class="fas fa-info-circle fa-lg"></i> 
	 		</button>
	 	</div>
	 </div>
 </div>

 
 <div class="d-none" id="id_card_<?=$data['id']?>_tooltip">
 	<div class="text-left">
 		<div class="border-bottom">
 		<?= $data['reference']?>
 		</div>
 		<div class="text-white">
 			<?php if ($data['status']!='out') :?>
 			Invoiced:&nbsp;<?= $data['invoiced'] > 0 ? lang('system.general.yes') : lang('system.general.no')?>
 			<br>Collection:&nbsp;<?= $data['inload'] > 0 ? lang('system.general.yes') : lang('system.general.no')?><br>
 			<?php else :?>
 			Due Out:&nbsp;<?= convertDate($data['duein'],'YmdHi','d M Y')?><br>
 			<?php endif ?>
 			Pallets Qty:&nbsp;<?= $data['pallqty']?>
 		</div>
 	</div>
 </div>
 
 
 <script>
 	$(function(){
 		$('#id_card_<?=$data['id']?>1').tooltip({
			placement : 'right',
			title : $('#id_card_<?=$data['id']?>_tooltip').html(),
			trigger : 'manual',
			html : true
		});
 	});
 	function id_card_<?=$data['id']?>ToogleEdit(){
 		<?php if ($data['status']=='out') :?>
 		window.location='<?= url('Warehouse','collection',[$data['id']],['refurl'=>current_url(FALSE,TRUE)]) ?>';
 		<?php else :?>
 		window.location='<?= url('Orders','order',[$data['id']],['refurl'=>current_url(FALSE,TRUE)]) ?>';
 		<?php endif ?>
 	}
 </script>
