<div class="alert alert-danger d-none" role="alert" id="id_load_error">
</div>
<div class="col-xs-12 col-md-8">
<div class="card card-primary card-outline card-tabs">
   	<div class="card-body">
		<div class="<?= $currentView->isMobile() ? 'w-100' : 'w-75' ?>">
			<?= $currentView->includeView('System/form_fields',[]); ?>
		</div>
		<div class="form-group" id="id_pallets_field">
    		<div class="card card-body bg-light">
    			<h5><?= lang('system.warehouse.collections_setpall') ?></h5>
    			<div style="overflow-y:auto;max-height:350px;overflow-x: hidden;">
    			<?= $currentView->includeView('Pallets/pallets_list',['tbody_id'=>'setpallets_table_body','columns'=>['reference','size','stack','height','corder'],'pallets'=>$set_pallets]); ?>
    			</div>
    		</div>
   		</div>
	</div>
	<div class="card-footer d-flex">
        <div class="ml-auto">
            <a type="button" class="btn btn-outline-danger<?= empty($_formview_urlcancel) ? ' d-none' : '' ?>" href="<?= !empty($_formview_urlcancel) ? $_formview_urlcancel: '#'  ?>">
              	<i class="fas fa-ban mr-1"></i><?= lang('system.buttons.cancel'); ?>
            </a>
          </div>
	</div>
</div>
</div>
<script>

</script>