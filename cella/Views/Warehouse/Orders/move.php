<?= $currentView->includeView('System/form') ?>
<script>	
	$("#id_reference").on("change",function(){
		var orders=JSON.parse(atob('<?= base64_encode(json_encode($orders)) ?>'));
		$("#id_location").attr('readonly','true');
		$("#id_location").val('');
		$("#id_oid").val('');
		var loaction=$("#reference_list").find("option[value='" + $(this).val() + "']");
		$(this).removeClass("border-danger");
		if($("#reference_list").html().length>0 && $(this).val().length>0){
			if(loaction != null && loaction.length > 0){
				$("#id_location").removeAttr('readonly');
				$("#id_oid").val(orders[$(this).val()]);
			}else{
				$(this).addClass("border-danger");
				$(this).val("");
				alert('<?= lang('system.orders.mobile_ref_error') ?>');
			}
		}
	});
	
	$("#id_location").on("change",function(){
		$("#id_formview_submit").addClass('d-none');
		var loaction=$("#location_list").find("option[value='" + $(this).val() + "']");
		$(this).removeClass("border-danger");
		if($("#reference_list").html().length>0 && $(this).val().length>0){
			if(loaction != null && loaction.length > 0){
				$("#id_formview_submit").removeClass('d-none');
			}else{
				$(this).addClass("border-danger");
				$(this).val("");
				alert('<?= lang('system.warehouse.invalid_location') ?>');
			}
		}
	});
	
	$("#id_formview_submit").on("click",function(){
		var val=$("#id_oid").val();
		if (val.length>0){
			$("#"+$("#id_formview_submit").attr('form')).submit();
		}
	});
</script>
