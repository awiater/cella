<?= $this->extend('System/form') ?>
<?= $this->section('form_body') ?>
<ul class="nav nav-tabs<?= !empty($items) && is_array($items) && count($items) ? '' : ' d-none' ?>" id="tabs-tab" role="tablist">
	<li class="nav-item">
   		<a class="nav-link active" id="tabs-cfg-tab" data-toggle="pill" href="#tabs-cfg" role="tab" aria-controls="tabs-cfg" aria-selected="true">
   			<?= lang('system.products.stocktake_tabcfg') ?>
   		</a>
 	</li>
 	<li class="nav-item">
 		<a class="nav-link" id="tabs-items-tab" data-toggle="pill" href="#tabs-items" role="tab" aria-controls="tabs-items" aria-selected="true">
   			<?= lang('system.products.stocktake_tabitems') ?>
   		</a>
   	</li>
   	<li class="nav-item<?= empty($movements) ? ' d-none' : '' ?>">
   		<a class="nav-link" id="tabs-hist-tab" data-toggle="pill" href="#tabs-hist" role="tab" aria-controls="tabs-hist" aria-selected="false">
   			<?= lang('system.pallets.pallet_tab_hist') ?>
   		</a>
 	</li>
</ul>
<div class="tab-content<?= !empty($items) && is_array($items) && count($items) ? ' border-left border-bottom border-right  ' : '' ?> p-2" id="tabs-tabContent">
   	<div class="tab-pane fade p-2" id="tabs-items" role="tabpanel" aria-labelledby="tabs-items-tab">
		<?= $currentView->includeView('Products/StockTake/items') ?>
   	</div>
   	<div class="tab-pane fade show active w-50" id="tabs-cfg" role="tabpanel" aria-labelledby="tabs-cfg-tab">
		<?= $currentView->includeView('System/form_fields',[]); ?>
   	</div>
   	<div class="tab-pane fade" id="tabs-hist" role="tabpanel" aria-labelledby="tabs-hist-tab">
    	<?= !empty($movements) ? $movements : '' ?>	
    </div>
</div>
<script>
	$(function(){
		hideFields();
		$("#id_type_cfg_"+$("#id_type_cfg").val()+'_field').removeClass('d-none');
		$("#id_type_cfg").val($("#id_type_cfg_"+$("#id_type_cfg").val()+' option:selected').val());
		
		<?php if (!empty($record) && is_array($record)) :?>
		addMovement('#id_status','change_status','stocktakes',['#id_reference','val'],'<?= $record['status_name'] ?>',['#id_status option:selected','text'],'',true);
		<?php endif ?>
	});
	
	$("#id_type").on("change",function(){
		hideFields();	
		
		var val=$("#id_type option:selected").val();
		$("#id_type_cfg_"+val+'_field').removeClass('d-none');
		$("#id_type_cfg").val($("#id_type_cfg_"+val+' option:selected').val());
	});
	
	$(".type_cfg").on("change",function(){
		$("#id_type_cfg").val($("#"+$(this).attr("id")+' option:selected').val());
	});
	
	function hideFields(){
		$(".type_cfg").each(function(){
			$("#"+$(this).attr('id')+'_field').addClass('d-none');
		});
	}
	
</script>
<?= $this->endSection() ?>
