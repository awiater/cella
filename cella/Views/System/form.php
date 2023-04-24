<div class="col-xs-12 col-md-8" id="form_container">
<div class="card<?= !empty($_formview_card_class) ? ' '.$_formview_card_class : null ?>">
	<div class="card-header">
		<?php if(!empty($_formview_title)) : ?>
    	<h3 class="card-title"><?= $_formview_title; ?></h3>
    	<?php endif ?>
    	<?php if (!empty($_formview_custom) && $_formview_custom) : ?>
			<?= $this->renderSection('form_header') ?>
		<?php endif ?>
    </div>
    <div class="card-body">
    	<?= !empty($_form_error) && strlen($_form_error) ? $_form_error : '' ?>
    	
    	<?= form_open_multipart(empty($_formview_action) ? '': $_formview_action,empty($_formview_action_attr) ? []: $_formview_action_attr,empty($_formview_action_hidden) ? []: $_formview_action_hidden); ?>
    	<?php if(!empty($_formview_validation)) : ?>
    		<?= $_formview_validation->listErrors() ?>
    	<?php endif ?>
    	<?php if (empty($_formview_custom) || (!empty($_formview_custom) && !$_formview_custom)) : ?>
				<?= $currentView->includeView('System/form_fields',['fields'=>$fields]); ?>
		<?php else : ?>
			<?= $this->renderSection('form_body') ?>
		<?php endif ?>
		<?= form_close(); ?>
    </div>
    <div class="card-footer d-flex">
        <div class="<?= $currentView->isMobile() ? 'w-100' : 'ml-auto' ?>">
        	<?php if (!empty($_formview_action) && !empty($_formview_savebtn) && is_array($_formview_savebtn)) : ?>
        	<button type="<?= $_formview_savebtn['type'] ?>" form="edit-form" class="<?= $_formview_savebtn['class'] ?><?= $currentView->isMobile() ? ' mb-2 w-100 btn-lg" style="font-size:2.05rem"' : '"'?> id="<?= $_formview_savebtn['id'] ?>">
            	<?php if (!empty($_formview_custom_save) && is_array($_formview_custom_save)) :?>
              		<i class="<?= $_formview_custom_save[0]; ?>"></i><?= $_formview_custom_save[1]; ?>
              	<?php else :?>
              		<i class="<?= $_formview_savebtn['icon'] ?> mr-1"></i><?= lang($_formview_savebtn['text']); ?>
              	<?php endif ?>
            </button>
            <?php endif ?>
            <a type="button" class="btn btn-outline-danger<?= empty($_formview_urlcancel) ? ' d-none' : '' ?><?= $currentView->isMobile() ? ' btn-lg w-100' : null ?>" href="<?= !empty($_formview_urlcancel) ? $_formview_urlcancel: '#'  ?>" id="id_formview_cancel">
              	<i class="fas fa-ban mr-1"></i><?= lang('system.buttons.cancel'); ?>
            </a>
          </div>
	</div>
</div>
</div>

