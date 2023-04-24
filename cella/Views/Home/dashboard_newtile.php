
<div class="modal fade" id="dashNewItemModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle"><?= lang('system.dashboard.new_item_title') ?></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
      	<?= form_open_multipart(empty($_formview_action) ? '': $_formview_action,empty($_formview_action_attr) ? []: $_formview_action_attr,empty($_formview_action_hidden) ? []: $_formview_action_hidden); ?>
    	<?= $currentView->includeView('System/form_fields'); ?>
		<?= form_close(); ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal"><?= lang('system.buttons.cancel') ?></button>
        <button type="submit" class="btn btn-primary" form="id_dashboard_tiles_form"><?= lang('system.dashboard.add_tile') ?></button>
      </div>
    </div>
  </div>
</div
<script>	
	
</script>