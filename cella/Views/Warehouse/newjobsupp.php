<div class="alert alert-warning d-none" role="alert" id="id_pallet_error">
 	<p id="id_pallet_error_p"><p>
</div>
<?= $currentView->includeView('System/form'); ?>
<script>
	$(function(){
		<?php if (!empty($newjobc)) :?>
		addRefButton('id_corder','getPallRefernce(null,'+"'id_customer','id_corder','0'"+')');
		addRefButton('id_sorder','getPallRefernce(null,'+"'id_supplier','id_sorder'"+')');
		<?php else :?>	
		addRefButton('id_reference','getPallRefernce(null,'+"'id_owner','id_reference'"+')');
		<?php endif ?>
		$("#id_formview_submit1").attr('type','button');
		$("#id_pallets_qty_field").append('<div class="form-group" id="id_palletsinfo_field"></div>');
	});
	

	$("#id_pallets_qty").on("change",function(){
		var val=parseInt($(this).val());
		$("#id_palletsinfo_field").html('');
		for (let i = 0; i < val; i++){
			$("#pallets_info").clone().attr('id','pallets_info_'+i).removeClass('d-none').appendTo('#id_palletsinfo_field');
			$('#pallets_info_'+i+' h5').html('Pallet '+(i+1));
		}
	});
		
	function addRefButton(id,func)
	{
		$("#"+id).wrap('<div class="input-group mb-3">');
		$("#"+id).after(function(){
			var refhtml='<div class="input-group-append">';
			refhtml+='<button type="button" class="btn btn-secondary btn-sm" onClick="'+func+'" id="bolt_btn_'+id+'"><i class="fas fa-bolt"></i></button>';
			refhtml+='</div>';
			return refhtml;
		});		
	}
	
	function getPallRefernce(cust,id,ref,mode='1'){
		if (cust==null){
			cust=$('#'+id+' option:selected').val();
		}
		mode=mode=='1' ? {supplier:cust} : {customer:cust};
		var orders_inpick='<?= !empty($orders_inpick) ? implode(',',$orders_inpick) : '' ?>';
		$.ajax({
            type: 'GET',
            dataType: 'json',
            url: '<?= $apiurl; ?>',
            data:mode,
            success: function(data) {
            	if (data['result']!=null){
            		$("#"+ref).val(data['result']);
            		if (orders_inpick.length > 0 && orders_inpick.search(data['result']) >= 0){
            			$("#id_pallet_error").removeClass("d-none");
            			$("#id_pallet_error").html('<?= lang('system.orders.error_order_in_pick')?>');
            			Dialog('<?= lang('system.orders.error_order_in_pick')?>','warning');
            		}
            	}
            			
            },
            error: function(data) {
              //alert('error');
            }
        });   
  	}   
  	$("#id_reference").on('change',function(){
  		$("#id_reference_old").val($(this).val())
  	});  
  	
  	$("#id_owner").on("change",function(){
  		getPallRefernce($("#id_owner option:selected").val(),'id_owner','id_reference');
  	});
  	
  	$("#id_customer").on("change",function(){
  		getPallRefernce($("#id_customer option:selected").val(),'id_customer','id_corder',0);
  	});
</script>
