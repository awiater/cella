<?= $currentView->includeView('System/form') ?>
<script>
	$(function(){
		$(".btn-success").addClass("d-none");
		$("#id_reference").focus();
	});
	$("#id_reference").on("change",function(){
		$("#edit-form").submit();
	});
</script>