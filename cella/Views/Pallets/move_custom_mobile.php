<div class="card mx-auto">
	<div class="card-header">
		<?php if(!empty($_formview_title)) : ?>
    	<h3 class="card-title"><?= $_formview_title; ?></h3>
    	<?php endif ?>
    </div>
    <div class="card-body">
    	<div class="col-xs-12 col-md-8">
    	<?= form_open_multipart(empty($_formview_action) ? '': $_formview_action,empty($_formview_action_attr) ? []: $_formview_action_attr,empty($_formview_action_hidden) ? []: $_formview_action_hidden); ?>
    			<div class="row">
    				<div class="col-12">
    					<label for="id_reference"><?= lang('system.pallets.putaway_move_reference') ?></label>
    					<div class="input-group mb-3">
    						<input class="form-control form-control-lg " type="text" name="pallets[0][reference]" id="id_reference">
    						<div class="input-group-append">
    							<button type="button" class="btn btn-secondary" onClick="$('#id_reference').val('');$('#btnConfig').addClass('d-none')"><i class="fas fa-broom"></i></button>
    						</div>
    					</div>
    					<small id="id_reference_error" class="form-text text-danger"></small>
    				</div>
    				<div class="col-12 mb-3">
    					<label for="id_location"><?= lang('system.pallets.putaway_location_from') ?></label>
    					<input class="form-control form-control-lg bg-light" type="text" value="<?= $move_fields['location'] ?>" id="id_from" readonly>
    				</div>
    				<div class="col-12 mb-3">
    					<label for="id_location"><?= lang('system.pallets.putaway_location_to') ?></label>
    					<div class="input-group mb-3">
    						<input class="form-control form-control-lg" type="text" value="<?= $move_fields['location'] ?>" id="id_location" name="pallets[0][location]" readonly>
    						<div class="input-group-append">
    							<button type="button" class="btn btn-secondary" onClick="$('#id_location').val('');$('#btnConfig').addClass('d-none')"><i class="fas fa-broom"></i></button>
    						</div>
    					</div>
    					<small id="id_location_error" class="form-text text-danger"></small>
    				</div>
				</div>
    				<input type="hidden"  value="<?= $move_fields['putaway']?>" id="id_putaway" name="pallets[0][putaway]">
    				<input type="hidden" value="<?= $move_fields['sorder']?>" name="pallets[0][info]">
    				<input type="hidden" value="<?= $move_fields['location']?>" id="id_old_location" name="pallets[0][old_location]">
    				<input type="hidden" value="<?= $move_fields['pid']?>"  id="id_pids" name="pallets[pids][]">
    				<input type="hidden" value="1" name="automove">
		<?= form_close(); ?>
		</div>
    </div>
    <div class="card-footer">
    	<div class="row col-12">
          <button type="button" form="edit-form" class="btn btn-success btn-lg col-12 mb-3 d-none" id="btnConfig">
            	<i class="far fa-save mr-1"></i><?= lang('system.pallets.putaway_move_putaway'); ?>
            </button>
        </div>
        <div class="row col-12">
            <a type="button" class="btn btn-md btn-outline-danger<?= empty($_formview_urlcancel) ? ' d-none' : '' ?><?= $currentView->ismobile(TRUE) ? ' col-12' : '' ?>" href="<?= !empty($_formview_urlcancel) ? $_formview_urlcancel: '#'  ?>">
              	<i class="fas fa-ban mr-1"></i><?= lang('system.buttons.cancel'); ?>
            </a>
          </div>
          
	</div>
<script>
	$(function(){
		$("#id_reference").focus();
	});
	$("#btnConfig").on("click",function(){
		$("#edit-form").submit();
	});
	
	$("#id_reference").on("change",function(){
		var pallets=JSON.parse(atob('<?= base64_encode(json_encode($pallets)) ?>'));
		var val=$(this).val();
		$("#id_location").attr('readonly','true');
		$("#id_reference_error").text('');
		if ( val in pallets){
			$("#id_from").val(pallets[val]['location']);
			$("#id_old_location").val(pallets[val]['location']);
			$("#id_pids").val(pallets[val]['pid']);
			$("#id_location").removeAttr('readonly');
			$("#id_location").focus();
		}else{
			alert('<?= lang('system.pallets.putaway_move_reference_error')?>');
			$("#id_reference_error").text('<?= lang('system.pallets.putaway_move_reference_error')?>');
			$(this).val('');
		}
	});
	
	$("#id_location").on("change",function(){
		var locations=JSON.parse(atob('<?= base64_encode(json_encode($locations)) ?>'));
		var val=$(this).val();
		$("#id_location_error").text('');
		if (val in locations){
			$("#btnConfig").removeClass('d-none');
		}else
		if (val.length > 0){
			alert('<?= lang('system.pallets.putaway_move_putaway_error')?>');
			$("#id_location_error").text('<?= lang('system.pallets.putaway_move_putaway_error')?>');
			$(this).val('');
		}
	});
	
</script>