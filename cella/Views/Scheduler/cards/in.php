<div class="small-box card m-0" style="height:<?= $board['cardheight']?>px!important;background-color: <?= $data['color'][0]?>;color:rgb(<?= $data['color'][1]?>)" id="id_card_<?=$data['oid']?>" data-editable="true" data-type="movablecard"
     data-ref="<?=$data['oid']?>"
     <?php if(array_key_exists('linked_id',$data) && strlen($data['linked_id']) > 0) :?>
     data-linked="true" data-link="<?= $data['linked_id'] ?>"
	<?php endif ?>     
>
	<div class="inner">
		<?php if (is_array($data['ocfg']) && array_key_exists('boxqty', $data['ocfg']) && strlen($data['ocfg']['boxqty']) > 1) :?>
 			<h4><i class="fas fa-pallet mr-1 fa-sm" style="color:rgb(<?= $data['color'][1] ?>,.5)!important"></i><?= $data['ocfg']['pallqty']?></h4>
 			<h4><i class="fas fa-archive mr-1 fa-sm" style="color:rgb(<?= $data['color'][1] ?>,.5)!important"></i><?= $data['ocfg']['boxqty']?></h4>
 		<?php else :?>
			<h3><i class="fas fa-pallet mr-1 fa-xs" style="color:rgb(<?= $data['color'][1] ?>,.5)!important"></i><?= $data['ocfg']['pallqty']?></h3>
		<?php endif ?>
		<b><i class="fas fa-tag mr-1 fa-sm" style="color:rgb(<?= $data['color'][1] ?>,.5)!important"></i><?= $data['reference']?></b>
	</div>
	<div class="icon">
		<?php if (is_array($data) && array_key_exists('overdue', $data)) :?>
		<i class="fas fa-exclamation-triangle" style="color:rgb(<?= $data['color'][1] ?>,.08)!important"></i>
		<?php else :?>
		<i class="fas fa-dolly-flatbed" style="color:rgb(<?= $data['color'][1] ?>,.08)!important"></i>
		<?php endif ?>
	</div>
</div>
 
 <div class="d-none" id="id_card_<?=$data['oid']?>_tooltip">
 	<div class="text-left">
 		<div class="border-bottom">
 		<?= $data['reference']?>
 		</div>
 		<div>
 			Due In:&nbsp;<?= convertDate($data['duein'],'YmdHi','d M Y')?><br>
 			Pallets Qty:&nbsp;<?= $data['ocfg']['pallqty']?><br>
 			<?php if (is_array($data['ocfg']) && array_key_exists('boxqty', $data['ocfg'])) :?>
 			Boxes Qty:&nbsp;<?= $data['ocfg']['boxqty']?><br>
 			<?php endif ?>
 			<?php if (is_array($data) && array_key_exists('overdue', $data)) :?>
 				<h5>
 					<?= lang('scheduler.errors.delivery_overdue'); ?>
 				</h5>
 			<?php endif ?>
 			
 		</div>
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
		document.getElementById('id_card_<?=$data['oid']?>').style.color=invertHex('<?= $data['color'][0]?>');
 	});
 	function id_card_<?=$data['oid']?>ToogleEdit(){
 		window.location='<?= $data['url'] ?>';
 	}
 	
 	function invertHex(hex) {
  return (Number(`0x1${hex}`) ^ 0xFFFFFF).toString(16).substr(1).toUpperCase()
}
 </script>
