<div class="card card-primary card-outline card-tabs">
	<div class="card-header p-0 pt-1 border-bottom-0">
   		<ul class="nav nav-tabs" id="tabs-tab" role="tablist">
 			<li class="nav-item">
   				<a class="nav-link active" id="tabs-home-tab" data-toggle="pill" href="#tabs-home" role="tab" aria-controls="tabs-home" aria-selected="true">
   					<?= lang('system.settings.index_tab_home') ?>
   				</a>
 			</li>
 			<li class="nav-item">
   				<a class="nav-link" id="tabs-mailer-tab" data-toggle="pill" href="#tabs-mailer" role="tab" aria-controls="tabs-mailer" aria-selected="false">
   					<?= lang('system.settings.index_tab_mailer') ?>
   				</a>
 			</li>
 			<li class="nav-item">
   				<a class="nav-link" id="tabs-messages-tab" data-toggle="pill" href="#tabs-messages" role="tab" aria-controls="tabs-messages" aria-selected="false">
   					<?= lang('system.settings.index_tab_msg') ?>
   				</a>
 			</li>
 			
 			<?php if(!empty($customtabs) && is_array($customtabs) && count($customtabs) >0 ) :?>
 				<?php foreach (array_keys($customtabs) as $key => $value) :?>
 					<?php $key=strtolower($value) ?>
 					<li class="nav-item">
 						<a class="nav-link" id="tabs-<?= $key ?>-tab" data-toggle="pill" href="#tabs-<?= $key ?>" role="tab" aria-controls="tabs-<?= $key ?>" aria-selected="false">
 							<?= lang($value) ?>
 						</a>
 					</li>
 				<?php endforeach ?>
 			<?php endif ?>
     	</ul>
   	</div>
   	<div class="card-body">
   		<?= form_open_multipart(empty($_formview_action) ? '': $_formview_action,empty($_formview_action_attr) ? []: $_formview_action_attr,empty($_formview_action_hidden) ? []: $_formview_action_hidden); ?>	
		<div class="tab-content" id="tabs-tabContent">
			<div class="tab-pane fade show active w-50" id="tabs-home" role="tabpanel" aria-labelledby="tabs-home-tab">
 				<?= $currentView->includeView('System/form_fields',['fields'=>array_slice($fields, 0,6)]); ?>
    		</div>
    		<div class="tab-pane fade w-50" id="tabs-mailer" role="tabpanel" aria-labelledby="tabs-mailer-tab">
    			<?= $currentView->includeView('System/form_fields',['fields'=>array_slice($fields, 6,8)]); ?>
    		</div>
    		<div class="tab-pane fade w-75" id="tabs-messages" role="tabpanel" aria-labelledby="tabs-messages-tab">
    			<?= $currentView->includeView('System/form_fields',['fields'=>array_slice($fields, 14,8),'_form_fields_group_class'=>'row p-2']); ?>
    		</div>
    		<?php if(!empty($customtabs) && is_array($customtabs) && count($customtabs) >0 ) :?>
 				<?php foreach ($customtabs as $key => $value) :?>
 					<?php $key=strtolower($key) ?>
 					<?php if(is_array($value) && array_key_exists('view', $value) && array_key_exists('data', $value)) :?>
 					<div class="tab-pane fade w-75" id="tabs-<?= $key ?>" role="tabpanel" aria-labelledby="tabs-<?= $key ?>-tab">
 						<?= $currentView->includeView($value['view'],$value['data']); ?>
 					</div>
 					<?php endif ?>
 				<?php endforeach ?>
 			<?php endif ?>
     	</div>
     	<?= form_close(); ?>
	</div>
	<div class="card-footer d-flex">
        <div class="ml-auto">
        	<button type="button" form="edit-form" class="btn btn-success" id="id_btn_settings_save">
            	<i class="far fa-save mr-1"></i><?= lang('system.buttons.save'); ?>
            </button>
            <a type="button" class="btn btn-outline-danger<?= empty($_formview_urlcancel) ? ' d-none' : '' ?>" href="<?= !empty($_formview_urlcancel) ? $_formview_urlcancel: '#'  ?>" >
              	<i class="fas fa-ban mr-1"></i><?= lang('system.buttons.cancel'); ?>
            </a>
          </div>
	</div>
</div>
<script>
	$("#id_btn_settings_save").on("click",function(){
		<?php if(!empty($customtabs) && is_array($customtabs) && count($customtabs) >0 ) :?>
			<?php foreach ($customtabs as $key => $value) :?>
			if(typeof(beforeSave<?= $key ?>) !== 'undefined')
			{
				beforeSave<?= $key ?>();
			}
			<?php endforeach ?>
		<?php endif ?>
		$("#edit-form").submit();
	});
	
	$(function(){
		setActiveTabToUrl();
	});
</script>