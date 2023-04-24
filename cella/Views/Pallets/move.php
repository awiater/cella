<div class="card">
	<div class="card-header">
		<?php if(!empty($_formview_title)) : ?>
    	<h3 class="card-title"><?= $_formview_title; ?></h3>
    	<?php endif ?>
    </div>
    <div class="card-body">
    	<div class="col-xs-12 col-md-8">
    	<?= form_open_multipart(empty($_formview_action) ? '': $_formview_action,empty($_formview_action_attr) ? []: $_formview_action_attr,empty($_formview_action_hidden) ? []: $_formview_action_hidden); ?>
    		<?php $keyid=0; ?>
    		<?= is_array($move_fields) && count($move_fields)==0 ? $currentView->controller->createMessage('system.pallets.move_no_items','info') : null ?>
    		<?php foreach ($move_fields as $key=>$field):?>
    			<div class="row" id="id_pallet_move_item_<?= $keyid ?>">
    				<div class="col-xs-12 col-md-3">
    					<label for="reference_<?= $keyid ?>"><?= lang('system.pallets.index_reference') ?></label>
    					<?php if (!empty($putaway)) :?>
    						<input type="text" class="form-control bg-white" name="pallets[<?= $keyid ?>][reference]" id="id_pallets_<?= $keyid ?>_reference" value="<?= $key ?>" readonly>
    					<?php else : ?>
						<select name="pallets[<?= $keyid ?>][reference_list]" id="id_pallets_<?= $keyid ?>_reference_list" class="form-control reference_list">
							<?php foreach($pallets as $palletk=>$palletv) :?>
							<option value="<?= base64_encode(json_encode($palletv)) ?>"<?= $key==$palletk ? ' selected="true"' : null ?>><?=$palletk?></option>
							<?php endforeach ?>
						</select>
						<input type="hidden" name="pallets[<?= $keyid ?>][reference]" id="id_pallets_<?= $keyid ?>_reference" value="<?= $key ?>">
						<?php endif ?>		
    				</div>
    				<div class="col-xs-12 col-md-3">
    					<label for="location_<?= $keyid ?>"><?= lang('system.pallets.index_location_from') ?></label>
						<input type="text" class="form-control bg-light form-control-sm border-0" value="<?= $field['location']?>" id="id_pallets_<?= $keyid ?>_location" name="pallets[<?= $keyid ?>][old_location]" readonly>
    				</div>
    				<div class="col-xs-12 col-md-3">
    					<label for="id_pallets_<?= $keyid ?>_location"><?= lang('system.pallets.index_location_new') ?></label>
						<input type="text" name="pallets[<?= $keyid ?>][location]" id="id_pallets_<?= $keyid ?>_location_new" class="form-control" list="pallets_location_list" value="<?= $field['putaway']?>">
    				</div>
    				<div class="col-xs-12 col-md-3">
    					<?php if ($keyid>0) :?>
    					<label for="button_Remove_<?= $keyid ?>">&nbsp;</label>
    					<div>
    						<button type="button" class="btn btn-danger btn-sm" onclick="removeItem('<?= $keyid ?>')" id="button_Remove_<?= $keyid ?>">
    							<i class="fa fa-trash"></i>
    						</button>
    					</div>
    					<?php endif ?>
    				</div>
    				<input type="hidden"  value="<?= $field['putaway']?>" id="id_pallets_<?= $keyid ?>_location_putaway" name="pallets[<?= $keyid ?>][putaway]">
    				<input type="hidden" value="<?= $field['sorder']?>" id="id_pallets_<?= $keyid ?>_location_orderinfo" name="pallets[<?= $keyid ?>][info]">
    				<input type="hidden" value="<?= $field['pid']?>" id="id_pallets_<?= $keyid ?>_location_pid" name="pallets[pids][]">
    				<datalist id="pallets_location_list">
    					<?php foreach($locations as $location) :?>
    					<option value="<?= $location ?>"></option>
    					<?php endforeach ?>
					</datalist>
    			</div>
    			<?php $keyid++; ?>
    		<?php endforeach;?>
    		<?php if (!empty($automove) && $automove) :?>
    		<div class="form-check mt-2">
  				<input class="form-check-input" type="checkbox" value="1" name="automove" checked="true">
  				<label class="form-check-label" for="defaultCheck1">
    				<?= lang('system.pallets.move_auto') ?>
  				</label>
			</div>
    		<?php endif ?>
		<?= form_close(); ?>
		</div>
    </div>
    <div class="card-footer d-flex">
        <div class="<?= !$currentView->ismobile(TRUE) ? ' ml-auto' : 'w-100' ?>">
        	<?php if (is_array($move_fields) && count($move_fields)!=0) :?>
        	<button type="button" form="edit-form" class="btn btn-success<?= $currentView->ismobile(TRUE) ? ' btn-lg col-12 mb-2' : '' ?>" id="btnConfig">
            	<i class="far fa-save mr-1"></i><?= lang('system.buttons.save'); ?>
            </button>
            <?php endif ?>
            <a type="button" class="btn btn-outline-danger<?= empty($_formview_urlcancel) ? ' d-none' : '' ?><?= $currentView->ismobile(TRUE) ? ' col-12' : '' ?>" href="<?= !empty($_formview_urlcancel) ? $_formview_urlcancel: '#'  ?>">
              	<i class="fas fa-ban mr-1"></i><?= lang('system.buttons.cancel'); ?>
            </a>
          </div>
	</div>
<script>
	function removeItem(id)
	{
		$('#id_pallet_move_item_'+id).remove();
	}
	$(".reference_list").on("change",function(){
		var id=$(this).attr('id');
		var thisID=id;
		var val=$("#"+thisID+" option:selected").val();
		val=JSON.parse(atob(val));
		id=id.replace('_reference_list','_reference');
		$("#"+id).val($("#"+thisID+" option:selected").text());
		id=id.replace('_reference','_location');
		$("#"+id).val(val.location);
		$("#"+id+"_putaway").val(val.putaway);
		$("#"+id+"_orderinfo").val(val.sorder);
		$("#"+id+"_pid").val(val.pid);
	});
	
	$("#btnConfig").on('click',function(){
		<?php if ($currentView->ismobile(TRUE)) :?>
			if ($("#putawaylocation").val().length > 0 && $("#id_pallets_0_location").val()!=$("#putawaylocation").val()){
				alert('<?= lang('system.pallets.putaway_invalidpallet') ?>');
			}else{
				$('#'+$(this).attr('form')).submit();
			}
		<?php else :?>
		$('#'+$(this).attr('form')).submit();
		<?php endif ?>
	});
</script>