<?php if($showform && !empty($form)) :?>
	<?= $form ?>
<?php else :?>
<?= $currentView->includeView('System/table') ?>
<div class="modal" tabindex="-1" role="dialog" id="filtersDialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><?= lang('system.reports.repfilters') ?></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <?= $form ?>
    </div>
  </div>
</div>
<script>
	$(function(){
		$('#id_formview_cancel').attr('href','#').attr('data-dismiss','modal');
		$('#filtersDialog .card-header').addClass("d-none");
		$('#filtersDialog .col-xs-12').removeClass("col-xs-12").removeClass("col-md-8").addClass('col-12');
		$('#table_view_datatable').DataTable({
			dom:'Bfrtip',
			'pageLength':50,
			'ordering':true,
			"order":[],
			buttons: {
    			dom: {
      				button: {
        						tag: 'button',
       							className: ''
     				 		}
    				},
			buttons:[
						 { 
						 	className: 'btn btn-danger btn-sm',
						 	text:'<i class="far fa-arrow-alt-circle-left mr-2"></i><?= lang('system.buttons.back') ?>',
						 	action: function ( e, dt, node, config ) {
                				window.location='<?= url('Reports') ?>';
            				} 
						 },
						 <?php if(!empty($form)) :?>
						 { 
						 	className: 'btn btn-dark btn-sm ml-2',
						 	text:'<i class="fas fa-filter mr-2"></i><?= lang('system.buttons.filter') ?>',
						 	action: function ( e, dt, node, config ) {
                				$("#filtersDialog").modal("show");
            				} 
						 },
						 <?php endif ?>
						 { 
						 	extend: 'print', 
						 	className: 'btn btn-secondary btn-sm ml-5',
						 	text:'<i class="fas fa-print mr-2"></i><?= lang('system.buttons.printbtn') ?>',
						 	title: '<?= $rname ?>' 
						 },
						 { 
						 	extend: 'csv', 
						 	className: 'btn btn-primary btn-sm',
						 	text:'<i class="fas fa-file-csv mr-2"></i><?= lang('system.buttons.exportbtn') ?>',
						 	filename:'<?= $fname ?>' 
						 },
						 { 
						 	extend: 'pdf', 
						 	className: 'btn btn-warning btn-sm',
						 	text:'<i class="far fa-file-pdf mr-2"></i><?= lang('system.buttons.exportpdfbtn') ?>',
						 	filename:'<?= $fname ?>',
						 	title: '<?= $rname ?>'  
						 },
					]
		}
		});
	});
	
	
	
	<?php if (!empty($_tableview_data) && is_array($_tableview_data)) :?>
	<?php $_tableview_data['rname']=$rname; ?>
	<?php endif ?>
	$(".csvBtn").on("click",function(){
		var data='<?= !empty($_tableview_data) ? base64_encode(json_encode($_tableview_data)) : null ?>';
		var url='<?= $exportcsv ?>';
		url=url.replace('-data-',data);
		window.open(url, '_blank');
	});
	
	$(".pdfBtn").on("click",function(){
		var data='<?= !empty($_tableview_data) ? base64_encode(json_encode($_tableview_data)) : null ?>';
		var url='<?= $exportpdf ?>';
		url=url.replace('-data-',data);
		window.open(url, '_blank');
	})
</script>
<?php endif ?>
