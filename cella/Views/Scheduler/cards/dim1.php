<div class="small-box cell card m-0 mb-1 " style="height:<?= $board['cardheight']?>px!important;background-color: <?= $data['color'][0]?>;color:rgb(<?= $data['color'][1]?>)" id="id_card_<?=$data['oid']?>" data-editable="true" data-infopanel=".overlay_container"
data-ref="<?=$data['reference']?>" 
 <?php if(array_key_exists('linked_ref',$data) && strlen($data['linked_ref']) > 0) :?>
     data-linked="true" data-link="<?= $data['linked_ref'] ?>"
	<?php endif ?>	
	>
	<div class="inner">
		<ul class="list-unstyled">
			<li>
				<h5><i class="fas fa-tag mr-1 fa-sm mr-1" style="color:rgb(<?= $data['color'][1] ?>,.5)!important"></i><?= $data['reference']?></h5>
			</li>
			<li><i class="fas fa-pallet mr-1 fa-sm" style="color:rgb(<?= $data['color'][1] ?>,.5)!important"></i><?= $data['ocfg']['pallqty']?></li>
		</ul>	
	</div>
	<div class="icon">
		<i class="fas fa-clipboard-list" style="color:rgb(<?= $data['color'][1] ?>,.05)!important"></i> 
	</div>
	<div class="d-none overlay_container" id="id_card_<?=$data['id']?>_overlay">
	 	<div class="overlay"></div>
	 	<div class="overlay_i border">
	 		<button type="button" class="btn btn-link text-white editButton col-3"  id="id_card_<?=$data['id']?>_orderbtn">
	 			<i class="fas fa-pen-square fa-lg"></i>
	 		</button>
	 		<a href="<?= str_replace('-id-',$data['id'],$urlremovecard) ?>" class="btn btn-link text-white editButton col-3"  id="id_card_<?=$data['id']?>_delbtn>">
	 			<i class="fas fa-trash fa-lg"></i>
	 		</a>
	 		
	 		<button type="button" class="btn btn-link text-white infobtn tooltipButton col-3" id="id_card_<?=$data['id']?>_infobtn">
	 			<i class="fas fa-info-circle fa-lg"></i> 
	 		</button>
	 	</div>
	 </div>
 </div>
 
 <div class="d-none" id="id_card_<?=$data['oid']?>_tooltip">
 	<div class="text-left">
 		<div class="border-bottom">
 		<?= $data['reference']?><br>
 		</div>
 		Pallets Qty:&nbsp;<?= $data['ocfg']['pallqty']?><br>
 		Location:&nbsp;<?= $data['location']?>
 	</div>
 </div>

 <script>
 	$(function(){
 		$('#id_card_<?=$data['oid']?>').tooltip({
			placement : 'right',
			title : $('#id_card_<?=$data['oid']?>_tooltip').html(),
			trigger : 'hover',
			html : true
		});
 	});
 	function id_card_<?=$data['oid']?>ToogleEdit(){
 		window.location='<?= $data['url'] ?>';
 	}
 </script>
