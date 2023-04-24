<?= $this->extend('System/form') ?>
<?= $this->section('form_body') ?>
	<ul class="nav nav-tabs" id="tabs-tab" role="tablist">
		<li class="nav-item">
   			<a class="nav-link active" id="tabs-cfg-tab" data-toggle="pill" href="#tabs-cfg" role="tab" aria-controls="tabs-cfg" aria-selected="true">
   				<?= lang('system.documents.tpls_tab_cfg') ?>
   			</a>
 		</li>
 		<li class="nav-item">
   			<a class="nav-link" id="tabs-body-tab" data-toggle="pill" href="#tabs-body" role="tab" aria-controls="tabs-body" aria-selected="true">
   				<?= lang('system.documents.tpls_file') ?>
   			</a>
 		</li>
	</ul>
	<div class="tab-content border-left border-bottom border-right p-3 body-full" id="tabs-tabContent">
		<div class="tab-pane fade show active w-50" id="tabs-cfg" role="tabpanel" aria-labelledby="tabs-cfg-tab">
 			<?= $currentView->includeView('System/form_fields',['fields'=>array_slice($fields,0,8)]); ?>
    	</div>
    	<div class="tab-pane fade h-100" id="tabs-body" role="tabpanel" aria-labelledby="tabs-body-tab">
 			<?= $currentView->includeView('System/form_fields',['fields'=>array_slice($fields,8,1)]); ?>
    	</div>
	</div>
	<script>
		$('.nav-tabs a').on('shown.bs.tab', function(e) {
    $('.CodeMirror').each(function(i, el){
        el.CodeMirror.refresh();
    });
});
	</script>
<?= $this->endSection() ?>
