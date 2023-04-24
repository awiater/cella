<?= $currentView->includeView('System/table',[]); ?>
<!-- Extra Buttons -->
<div class="d-none" id="id_receipts_extra_filers">
	<h6 class="dropdown-header"><?= lang('system.orders.filters_status'); ?></h6>
	<?php foreach($order_statuses['orders_status_types'] as $key=>$value) :?>
	<button type="button" class="dropdown-item btn btn-link" onclick="tableviewSearchFilterGo('status=<?= $key ?>')" >
    	<?= lang($value); ?>
    </button>
    <?php endforeach ?>	
</div>
<script>
	$(function(){
		var orders=JSON.parse(atob('<?= base64_encode(json_encode($orders)) ?>'));
		
		$(".actBtn").each(function(){
			$(this).addClass('d-none');
			var items=$(this).attr('data-status');
			var id=';'+$(this).attr('data-orderid');
			var btn=$(this);
			$.each(items.split('|'),function(key,item){	
				if ( item in orders && orders[item]!==null && orders[item]!==undefined){
					
					if (orders[item].indexOf(id) >= 0){
						btn.removeClass('d-none');
					}
				}
			});
		});
		
		//If prioritybtn is visible Scheduler column change size
		if ($(".prioritybtn").attr('type')=='button'){
			$("table tr td:nth-child(2)").css('width','140px');
		}
		
		$("table tr td:nth-child(1)").css('width','35px');
		table_view_datatable.columns.adjust().draw();
		
		table_view_datatable.on( 'draw', function () {
    			changeEditButton();
			} );
			
		changeEditButton();
		
		<?php if (!empty($print_pall) && $print_pall!=null) : ?>
				ConfirmDialog('<?= lang('system.pallets.print_question')?>',function(){
					window.open('<?= str_replace('-id-', $print_pall, $print_pall_url) ?>', '_blank');
				});
		<?php endif ?>
	});
	
	function getColumnCount(e) { //Expects jQuery table object
    	var c= 0;
    	e.find('tbody tr:first td').map(function(i,o) { c += ( $(o).attr('colspan') === undefined ? 1 : parseInt($(o).attr('colspan')) ) } );
    	return c;
	}
	
	function changeEditButton(){
		$(".edtBtn").each(function(){
			var orders=JSON.parse(atob('<?= base64_encode(json_encode($completed)) ?>'));
			
			if (orders!==null && orders.indexOf($(this).attr('data-orderid')) >= 0){
				$(this).attr('data-original-title','<?= lang('system.orders.suppliers_info') ?>');
				$(this).html('<i class="fas fa-info-circle"></i>');
				$(this).removeClass('btn-primary').addClass('btn-info');
			}			
		});
	}
</script>
<?= $currentView->includeView('Pallets/scan') ?>
