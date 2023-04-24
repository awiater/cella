<div class="card mx-auto">
	<div class="card-header">
		<?php if(!empty($_formview_title)) : ?>
    	<h3 class="card-title"><?= $_formview_title; ?></h3>
    	<?php endif ?>
    </div>
    <div class="card-body">
    	<div class="col-xs-12 col-md-8">
    	<?= form_open_multipart(empty($_formview_action) ? '': $_formview_action,empty($_formview_action_attr) ? []: $_formview_action_attr,empty($_formview_action_hidden) ? []: $_formview_action_hidden); ?>
    		<?php if (is_array($move_fields) && count($move_fields)==0) : ?>
    			<?= $currentView->controller->createMessage('system.pallets.move_no_items','info') ?>
    		<?php else : ?>
    			<div class="row" id="id_palletmove_1">
    				<div class="col-12 mb-3">
    					<label for="id_location"><?= lang('system.pallets.putaway_move_location') ?></label>
    					<input class="form-control form-control-lg bg-light" type="text" value="<?= $move_fields['location'] ?>" id="id_location" readonly>
    				</div>
    				<div class="col-12">
    					<label for="id_reference"><?= lang('system.pallets.putaway_move_reference') ?></label>
    					<input class="form-control form-control-lg bg-light mb-2" type="text" value="<?= $move_fields['reference'] ?>" id="id_reference_lbl" readonly>
    					<input class="form-control form-control-lg " type="text" name="pallets[0][reference]" id="id_reference">
    					<small id="id_reference_error" class="form-text text-danger"></small>
    				</div>
				</div>
				<div class="row d-none" id="id_palletmove_2">
					<div class="col-12 mb-3">
    					<label for="id_putaway_loc"><?= lang('system.pallets.putaway_move_putaway') ?></label>
    					<input class="form-control form-control-lg bg-light" type="text" value="<?= $move_fields['reference'] ?>" id="id_putaway_loc" readonly>
    					<?php if (!empty($move_fields['stacking'] )) :?>
    					<input class="form-control form-control-lg bg-light mt-2" type="text" value="<?= $move_fields['stacking'] ?>" readonly>
    					<?php endif ?>
    				</div>
    				<div class="col-12">
    					<label for="id_putaway"><?= lang('system.pallets.putaway_location_to') ?></label>
    					<?php if (!is_array($move_fields['putaway'] )) :?>
    					<input class="form-control form-control-lg bg-light mb-2" type="text" value="<?= $move_fields['putaway'] ?>" id="id_putaway_lbl" readonly>
    					<?php endif ?>
    					
    					<input class="form-control form-control-lg " type="text" name="pallets[0][location]" id="id_putaway">
    					<small id="id_putaway_error" class="form-text text-danger"></small>
    					
    				</div>
				</div>
				
    				<input type="hidden"  value="<?= is_array($move_fields['putaway']) ? '' : $move_fields['putaway'] ?>" name="pallets[0][putaway]" id="id_putaway">
    				<input type="hidden" value="<?= array_key_exists('sorder', $move_fields) ? $move_fields['sorder'] : '' ?>" name="pallets[0][info]">
    				<input type="hidden" value="<?= $move_fields['location']?>" name="pallets[0][old_location]">
    				<input type="hidden" value="<?= array_key_exists('pid', $move_fields) ? $move_fields['pid'] : '' ?>"  name="pallets[pids][]">
			<?php endif ?>
		<?= form_close(); ?>
		</div>
    </div>
    <div class="card-footer d-flex">
        <div class="w-100">
            <a type="button" class="btn btn-lg btn-outline-danger<?= empty($_formview_urlcancel) ? ' d-none' : '' ?><?= $currentView->ismobile(TRUE) ? ' col-12' : '' ?>" href="<?= !empty($_formview_urlcancel) ? $_formview_urlcancel: '#'  ?>">
              	<i class="fas fa-ban mr-1"></i><?= lang('system.buttons.cancel'); ?>
            </a>
          </div>
	</div>
<script>
	$(function(){
		$("#id_reference").focus();
	});
	
	$("#id_reference").on("change",function(){
		var val=$(this).val();
		$("#id_reference_error").text('');
		if (val!=$("#id_reference_lbl").val()){
			errorDialog('<?= lang('system.pallets.putaway_move_reference_error')?>');
			$("#id_reference_error").text('<?= lang('system.pallets.putaway_move_reference_error')?>');
			$(this).val('');
		}else{
			$("#id_palletmove_1").addClass("d-none");
			$("#id_palletmove_2").removeClass("d-none");
			$("#id_putaway").focus();
		}
	});
	
	$("#id_putaway").on("change",function(){
		var val=$(this).val();
		$("#id_putaway_error").text('');
		<?php if (!is_array($move_fields['putaway'] )) :?>
		if (val!=$("#id_putaway_lbl").val()){
			errorDialog('<?= lang('system.pallets.putaway_move_putaway_error')?>');
			$("#id_putaway_error").text('<?= lang('system.pallets.putaway_move_putaway_error')?>');
			$(this).val('');
		}else{
			$("#edit-form").submit();
		}
		<?php else : ?>
		var loc=JSON.parse(atob('<?= base64_encode(json_encode($move_fields['putaway'])) ?>'));
		if (val in loc){
			$("#edit-form").submit();		
		}else{
			errorDialog('<?= lang('system.pallets.putaway_move_putaway_error')?>');
			$("#id_putaway_error").text('<?= lang('system.pallets.putaway_move_putaway_error')?>');
			$(this).val('');
		}
		<?php endif ?>
	});
	
</script>