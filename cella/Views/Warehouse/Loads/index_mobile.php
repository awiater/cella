<?= $this->extend('System/form') ?>
<?= $this->section('form_body') ?>
<ul class="list-group">
	<?php foreach($loads as $load) :?>
  	<li class="list-group-item">
		<button type="button" form="edit-form" class="btn btn-lg btn-outline-primary w-100 submit" data-ref="<?= $load ?>" style="height:80px;">
			<?= $load ?>
		</button>
	</li>
  	<?php endforeach ?>
</ul>
<script>
	$(function(){
		$("#id_formview_submit").addClass('d-none');
		$(".submit").on("click",function(){
			var formid="#"+$(this).attr('form'); 
			$("<input />").attr("type", "hidden")
          		.attr("name", "reference")
          		.attr("value", $(this).attr('data-ref'))
          		.appendTo(formid);
			$(formid).submit();
			
		});
	});
</script>
<?= $this->endSection() ?>