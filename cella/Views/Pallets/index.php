<?= $currentView->includeView('System/table',['_tableview_custom'=>FALSE],TRUE); ?>
<?= $currentView->includeView('Pallets/scan') ?>
<script>
		$(function(){
			<?php if($currentView->ismobile(TRUE)) : ?>
			$("#id_tableview_form").append('<input type="hidden" name="pid[]" id="id_pallet_id">');
			<?php endif ?>
			table_view_datatable.on( 'draw', function () {
    			changeEditButton();
			} );
			
			changeEditButton();
			
			$(".putawayPalletBtn").attr('onClick','putawayPalletBtnClick()');
			
			<?php if (!empty($print_pall) && $print_pall!=null) : ?>
				ConfirmDialog('<?= lang('system.pallets.print_question')?>',function(){
					window.open('<?= str_replace('-id-', $print_pall, $print_pall_url) ?>', '_blank');
				});
			<?php endif ?>
		});
	function changeEditButton(){
		$(".edtBtn").each(function(){
					if ($(this).attr('data-status')==100){
						$(this).attr('data-original-title','<?= lang('system.pallets.index_info') ?>');
						$(this).html('<i class="fas fa-info-circle"></i>');
						$(this).removeClass('btn-primary').addClass('btn-info');
					}
					
				});
	}
	function putawayPalletBtnClick(){
		$("#id_pallet_id").val($(this).attr('data-urlid'));
		$('#id_tableview_form').attr('action', '<?= url('Pallets','move',[],['refurl'=>current_url(FALSE,TRUE)]) ?>').submit();	
	}
</script>