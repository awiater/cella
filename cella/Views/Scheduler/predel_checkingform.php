<div class="modal" tabindex="-1" role="dialog" id="id_predel_checkingmodal">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><?= lang('scheduler.predel.checkform_title') ?></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
      	<h6><?= lang('scheduler.predel.checkform_msg') ?></h6>
        <?= form_open_multipart(empty($_formview_action) ? '': $_formview_action,empty($_formview_action_attr) ? []: $_formview_action_attr,empty($_formview_action_hidden) ? []: $_formview_action_hidden); ?>
        <?= $currentView->includeView('System/form_fields') ?>
      	</form>
      </div>
      <div class="modal-footer">
        <button type="submit" form="checkingform" class="btn btn-primary"><?= lang('system.buttons.confirm') ?></button>
        <button type="button" class="btn btn-secondary" data-dismiss="modal"><?= lang('system.buttons.cancel') ?></button>
      </div>
    </div>
  </div>
</div>
<script>
	function showPredelCheckingWindow(orderid){
		$('#id_jobid').val(orderid);
		$('#id_predel_checkingmodal').modal('show');
	}
</script>
