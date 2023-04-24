<?= $currentView->includeView('System/form') ?>
<?= $checkingform ?>
<script>
	$(function(){
		if(!$('#id_reference').prop('readonly')){
			addRefButton('id_reference','getPallRefernce()');
		}else
		{
			
			var html='<button type="button" class="btn btn-primary btnCheck" id="id_formview_btncheck">';
           	html+='<i class="fas fa-clipboard-check mr-1"></i><?= lang('scheduler.predel.checkbtn')?></button>';
			<?php if((array_key_exists('attached', $record) && is_array($record['attached'])) || $record['status']=='0') :?>
			html+='<button type="button" class="btn btn-secondary ml-2" id="id_formview_btnattach">';
				<?php if(array_key_exists('attached', $record) && is_array($record['attached'])) :?>
				html+='<i class="fas fa-paperclip mr-1"></i><?= lang('scheduler.predel.attached')?></button>';
				<?php else :?>
				html+='<i class="fas fa-paperclip mr-1"></i><?= lang('scheduler.predel.attach')?></button>';
				<?php endif ?>
			<?php endif ?>
			html+=$('.card-footer').html();
			$('.card-footer').html(html);
			$("#edit-form").append('<input type="file" name="attached" id="id_attached" class="d-none">');
		}
		
		$("#id_formview_btnattach").on("click",function (e){
			<?php if(array_key_exists('attached', $record) && is_array($record['attached'])) :?>
			window.open('<?= parsePath($record['attached'][0]) ?>');
			<?php else :?>
  			var fileDialog = $('#id_attached');
  			fileDialog.click();
  			return false;   			
   			<?php endif ?>
		});
		
		$('#id_attached').on('change',function(){
			if ($('#id_attached').val().length>0){
   				$('#edit-form').submit();
   			}
		});
		
		$("#id_formview_btncheck").on('click',function(){
			showPredelCheckingWindow('<?= $record['oid'] ?>');
		});
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
	
	function getPallRefernce(){
		var supp=$("#id_owner option:selected").val();
		supp=supp.substring(0,3);
		$('#id_reference').val(supp+($('#id_duein_value').val()).substring(0,8));
  	}    
  	
  	$("#id_owner").on("change",function(){
  		getPallRefernce();
  	});
</script>