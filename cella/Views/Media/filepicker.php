<?php if ($picker_type=='images' && $previewimage==1) : ?>
	<img src="<?= $value ?>" class="img-thumbnail mb-2<?= $showpath!=1 ? ' cursor-pointer' : '' ?>" id="<?= $id ?>_picker_image" <?= !empty($height) && !empty($width) ? 'style="height:'.$height.';width:'.$width.';"' : '' ?>>
<?php endif ?>	
<div class="input-group mb-3">	
 	<input type="<?= $showpath ? 'text':'hidden' ?>" class="form-control bg-white" id="<?= $id ?>_picker_path" value="<?= $value ?>" name="<?= $name ?>">
  	<?php if ($showpath) : ?>
  	<div class="input-group-append">
    	<span class="input-group-text p-0" id="<?= $id ?>_picker_btn_span">
    		<button type="button" class="btn btn-sm <?= $picker_btn_class ?>" id="<?= $id ?>_picker_btn">
    			<i class="fa fa-folder-open"></i>
    		</button>
    	</span>
  	</div>
  	<?php endif ?>
</div>
<?php if (empty($noscript) || (!empty($noscript) && $noscript!=1)) : ?>
<script>
<?= $currentView->includeView('Media/editortiny',['editorid'=>$id]); ?>
$(document).on('click', "#<?= $id ?>_picker_<?= $showpath!=1 ? 'image':'btn' ?>", function (e) {
	vcmsFileEditor_<?= $id ?>.browser(function(file,data){
        $("#<?= $id ?>").val(file);
		$("#<?= $id ?>_picker_image").attr('src',file); 
		$("#<?= $id ?>_picker_path").val(file);
	}, 'value', {filetype:'file'}); 
	$("#elfinder").attr('style',$("#elfinder").attr('style')+'z-index:1400;');
});	

</script>
<?php endif ?>
