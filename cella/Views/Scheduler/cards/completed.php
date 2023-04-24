<div class="small-box cell card m-0 mb-1" style="color:rgb(<?= $data['color'][1]?>)!important;background-color: <?= $data['color'][0]?>;min-height: <?= $board['cardheight'] ?>px!important;" id="id_card_<?=$data['lid']?>" data-editable="true" data-infopanel=".overlay_container">
	<div class="inner">
		<ul class="list-unstyled">
			<li>
				<h6><b><?= $data['reference']?></b></h6>
			</li>
			<li><i class="fas fa-pallet mr-2" style="color:rgb(<?= $data['color'][1] ?>,.5)!important"></i><?= $data['pallqty']?></li>
			<li><i class="far fa-plus-square mr-2" style="color:rgb(<?= $data['color'][1] ?>,.5)!important"></i><?= convertDate($data['created'],'DB','d M Y')?></li>
			<li><i class="far fa-calendar-alt mr-1" style="color:rgb(<?= $data['color'][1] ?>,.5)!important"></i><?= convertDate($data['loaded'],'DB','d M Y H:i')?></li>
		</ul>		
	</div>
	<div class="icon">
		<i class="fas fa-shipping-fast" style="color:rgb( <?= $data['color'][1]?>,.08)!important"></i> 
	</div>
	 <div class="d-none overlay_container" id="id_card_<?=$data['lid']?>_overlay">
	 	<div class="overlay"></div>
	 	<div class="overlay_i border">
	 		<button type="button" class="btn btn-link text-white tooltipButton" onClick="window.location='<?= url('Warehouse','collection',[$data['lid']],['refurl'=>current_url(FALSE,TRUE)]) ?>'" id="id_card_<?=$data['lid']?>_infobtn">
	 			<i class="fas fa-info-circle fa-lg"></i>
	 		</button>
	 	</div>
	 </div>
 </div>
 <div class="d-none" id="id_card_<?=$data['lid']?>_tooltip">
 	<div class="text-left">
 		<?= lang('scheduler.collections.infobtn') ?>
 	</div>
 </div>
 
<script>
	$("#id_card1_<?=$data['lid']?>_infobtn").on('mouseover',function(){
					$('#id_card_<?=$data['lid']?>_infobtn').attr('data-original-title', '<?= lang('scheduler.collections.infobtn') ?>')
						 .tooltip('update')
						 .tooltip('show');
				}); 
				
	$("#id_card1_<?=$data['lid']?>_infobtn").on('mouseout',function(){
					$('#id_card_<?=$data['lid']?>_infobtn').tooltip('hide');
				});
</script>