<?= $this->extend('System/form') ?>
<?= $this->section('form_body') ?>
<?= !empty($msg) && strlen($msg) > 0 ? $msg : '' ?>
<div class="alert alert-danger d-none" role="alert" id="id_check_error">
 	<p id="id_check_error_p"><p>
</div>
<?= $currentView->includeView('System/form_fields'); ?>
<script>
	$(function(){
		$("#id_formview_cancel").removeClass("btn-lg").addClass("btn-md");
	});
	
	$("#id_formview_submit").on("click",function(){
		$("#"+$(this).attr('form')).submit();	
	});
	
	$("#id_location_new").on("change",function(){
		if ($(this).val()!=$("#id_location_text").val()){
			$("#id_check_error").removeClass("d-none");
            $("#id_check_error_p").text('<?= lang('system.products.invalidlocationerror')?>');
            $(this).addClass('border-danger');
            $(this).val('');
		}else{
			$("#id_new_pallet").removeAttr('readonly');
			$("#id_location_new").attr('readonly',1);
		}
	});
</script>
<?= $this->endSection() ?>