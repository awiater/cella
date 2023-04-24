<?= $currentView->includeView('System/form') ?>
<script>
	$(function(){
		var action=$("#id_action").val();
		if (action.startsWith('@')){
			$("#id_action_list").val('custom');
			$("#id_action_custom_field").removeClass('d-none');
			$("#id_action_custom").val(action.substr(1,action.length));
		}
	});
	$("#id_action_list").on("change",function(){
		var val=$("#id_action_list option:selected").val();
		if (val=='custom'){
			$("#id_action_custom_field").removeClass('d-none');
		}else{
			$("#id_action").val(val);
			$("#id_action_custom_field").addClass('d-none');	
		}	
	});
	
	$("#id_action_custom").on("change",function(){
		$("#id_action").val('@'+$(this).val());	
	});
</script>
