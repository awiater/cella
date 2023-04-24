<?= $currentView->includeView('System/table') ?>
<div id="buttons">
	<div class="w-100">
		<button type="submit" form="id_tableview_form" class="btn btn-success btn-lg w-100 mb-5" id="id_formview_submit" style="height:80px;">
        	<i class="fas fa-truck-loading mr-1"></i><?= lang('system.warehouse.collections_despall'); ?>
    	</button>
        <a type="button" class="btn btn-outline-danger btn-lg w-100" href="<?= url('Warehouse','despatch') ?>" id="id_formview_cancel">
        	<i class="fas fa-ban mr-1"></i><?= lang('system.buttons.cancel'); ?>
    	</a>
	</div>
</div>
<script>
	$(function(){
		var element = $('#buttons').detach();
		$(".card-footer").html(element);
		$("#id_tableview_form").append('<input type="hidden" name="reference" value="<?= !empty($reference) ? $reference : '' ?>">');
	});	
	//
</script>
