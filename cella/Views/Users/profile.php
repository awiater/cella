<?php $curtab=$curtab==null ? 0 : $curtab ?>
<?php $menuaccess=is_array($menuaccess) ? $menuaccess : [] ?>
<div class="alert alert-danger d-none" role="alert" id="id_load_error">
</div>
<div class="col-12">
<div class="card card-primary card-outline card-tabs">
	<div class="card-header">
		<?php if(!empty($_formview_title)) : ?>
    	<h3 class="card-title"><?= $_formview_title; ?></h3>
    	<?php endif ?>
    </div>
   	<div class="card-body">
   		<ul class="nav nav-tabs<?= empty($menuaccess_list) ? ' d-none': ''?>" id="tabs-tab" role="tablist">
   			<li class="nav-item">
   				<a class="nav-link<?= $curtab==0 ? ' active' : '' ?> border-left" id="tabs-cfg-tab" data-toggle="pill" href="#tabs-cfg" role="tab" aria-controls="tabs-cfg" aria-selected="true">
   					<?= lang('system.auth.tab_cfg') ?>
   				</a>
 			</li>
 			<li class="nav-item">
   				<a class="nav-link<?= $curtab==1 ? ' active' : '' ?> border-left" id="tabs-access-tab" data-toggle="pill" href="#tabs-access" role="tab" aria-controls="tabs-cfg" aria-selected="true">
   					<?= lang('system.auth.tab_access') ?>
   				</a>
 			</li>
 			<li class="nav-item">
   				<a class="nav-link<?= $curtab==2 ? ' active' : '' ?>" id="tabs-menu-tab" data-toggle="pill" href="#tabs-menu" role="tab" aria-controls="tabs-menu" aria-selected="true">
   					<?= lang('system.auth.tab_menu') ?>
   				</a>
 			</li>
 			<li class="nav-item">
   				<a class="nav-link<?= $curtab==3 ? ' active' : '' ?>" id="tabs-dash-tab" data-toggle="pill" href="#tabs-dash" role="tab" aria-controls="tabs-dash" aria-selected="true">
   					<?= lang('system.auth.tab_dash') ?>
   				</a>
 			</li>
   		</ul>
   		<?= form_open_multipart(empty($_formview_action) ? '': $_formview_action,empty($_formview_action_attr) ? []: $_formview_action_attr,empty($_formview_action_hidden) ? []: $_formview_action_hidden); ?>	
			<div class="tab-content<?= empty($menuaccess_list) ? '' : ' border border-top-0' ?> p-2" id="tabs-tabContent">
				<div class="tab-pane fade<?= $curtab==0 ? ' show active' : '' ?> w-50" id="tabs-cfg" role="tabpanel" aria-labelledby="tabs-cfg-tab">
    				<?= $currentView->includeView('System/form_fields',['fields'=>array_slice($fields, 0,5)]); ?>
    			</div>
    			<div class="tab-pane fade<?= $curtab==1 ? ' show active' : '' ?> w-50" id="tabs-access" role="tabpanel" aria-labelledby="tabs-access-tab">
    				<?= $currentView->includeView('System/form_fields',['fields'=>array_slice($fields, 5,4)]); ?>
    			</div>
    			<div class="tab-pane fade<?= $curtab==2 ? ' show active' : '' ?> w-50" id="tabs-dash" role="tabpanel" aria-labelledby="tabs-dash-tab">
    				<?= $currentView->includeView('System/form_fields',['fields'=>array_slice($fields, 9,1)]); ?>
    			</div>
    			
    			<?php if (!empty($menuaccess_list)) :?>
    			<div class="tab-pane fade<?= $curtab==1 ? ' show active' : '' ?>" id="tabs-menu" role="tabpanel" aria-labelledby="tabs-menu-tab">
    				<?php foreach($menuaccess_list as $name=>$group) :?>
    					<div class="form-group">
    						<h3>
    							<input type="checkbox" value="" onclick="$('input[data-group=\'<?= $name ?>\']').prop('checked', this.checked);">
    							<?= ucwords($name) ?>
    						</h3>
    						<?php foreach($group as $key=>$item) :?>
    						<div class="form-check ">
    							<input class="form-check-input" data-group="<?= $name ?>" type="checkbox" value="<?= $key?>" id="id_menuaccess_option_<?= $key ?>" name="menuaccess[]" <?= in_array($key, $menuaccess) ? 'checked="true"' : '' ?>>
    							<label class="form-check-label" for="id_menuaccess_option_<?= $key ?>"><?= lang($item) ?></label>
    						</div>
    						<?php endforeach ?>	
    					</div>
    				<?php endforeach ?>	
    			</div>
    			<?php endif ?>
			</div>
     	<?= form_close(); ?>
	</div>
	<div class="card-footer d-flex">
        <div class="ml-auto">
        	<button type="submit" form="edit-form" class="btn btn-success<?= (!empty($readonly) && $readonly) && !$savevis ? ' d-none' : '' ?>" id="id_pallet_save">
            	<i class="far fa-save mr-1"></i><?= lang('system.buttons.save'); ?>
            </button>
            <a type="button" class="btn btn-outline-danger<?= empty($_formview_urlcancel) ? ' d-none' : '' ?>" href="<?= !empty($_formview_urlcancel) ? $_formview_urlcancel: '#'  ?>">
              	<i class="fas fa-ban mr-1"></i><?= lang('system.buttons.cancel'); ?>
            </a>
          </div>
	</div>
</div>
</div>
<script>
	$("#tabs-menu-tab").on('click',function(){
		$("#tabs-cfg-tab").removeClass('border-left');
	});
	
	$("#tabs-cfg-tab").on('click',function(){
		$(this).addClass('border-left');
	});
	
	$("#tabs-cfg-tab").on('mouseover',function(){
		$(this).addClass('border-left');
	});
	
	$("#tabs-cfg-tab").on('mouseout',function(){
		if (!$(this).hasClass('active')){
			$(this).removeClass('border-left');
		}
		
	});
</script>

