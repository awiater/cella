<?= $currentView->includeView('System/table') ?>
<script>
	$(function(){
		$(".actBtn").each(function(){
			var orders='1_<?= implode(',1_',$readytocompl)?>,2_<?= implode(',2_',$comp)?>,S_<?= implode(',S_',$stack)?>';
			$(this).addClass('d-none');
			
			if (orders!=null && orders.length > 0 && orders.indexOf($(this).attr('data-status')) >= 0){
				$(this).removeClass('d-none');
			}else
			if (orders!=null && orders.length > 0 && orders.indexOf($(this).attr('data-status')) >= 0){
				$(this).removeClass('d-none');
			}
		});
		
		table_view_datatable.on( 'draw', function () {
    			changeEditButton();
			} );
			
		changeEditButton();
	});
	
	function changeEditButton(){
		$(".edtBtn").each(function(){
			if ($(this).attr('data-status') == 2){
				$(this).attr('data-original-title','<?= lang('system.warehouse.collections_infobtn') ?>');
				$(this).html('<i class="fas fa-info-circle"></i>');
				$(this).removeClass('btn-primary').addClass('btn-info');
			}			
		});
	}
</script>
