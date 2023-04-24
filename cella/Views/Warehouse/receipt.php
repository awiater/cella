<div class="card card-primary card-outline card-tabs">
	<div class="card-header p-0 pt-1 border-bottom-0">
   		<ul class="nav nav-tabs" id="tabs-tab" role="tablist">
 			<li class="nav-item">
   				<a class="nav-link active" id="tabs-cfg-tab" data-toggle="pill" href="#tabs-cfg" role="tab" aria-controls="tabs-cfg" aria-selected="true">
   					<?= lang('system.orders.receipt_tab_cfg') ?>
   				</a>
 			</li>
 			<?php if (!empty($content_table) && is_object($content_table)) :?>
 			<li class="nav-item">
   				<a class="nav-link" id="tabs-stock-tab" data-toggle="pill" href="#tabs-stock" role="tab" aria-controls="tabs-stock" aria-selected="false">
   					<?= lang('system.orders.receipt_tab_stock') ?>
   				</a>
 			</li>
 			<?php endif ?>
 			<?php if (!empty($pallets) && is_array($pallets) && count($pallets)>0) : ?>
 				<li class="nav-item">
   				<a class="nav-link" id="tabs-pall-tab" data-toggle="pill" href="#tabs-pall" role="tab" aria-controls="tabs-pall" aria-selected="false">
   					<?= lang('system.orders.receipt_tab_pallets') ?>
   				</a>
 			</li>
 			<?php endif ?>
 			<?php if (!empty($movements) && $movements!=null) :?>
 			<li class="nav-item">
   				<a class="nav-link" id="tabs-hist-tab" data-toggle="pill" href="#tabs-hist" role="tab" aria-controls="tabs-hist" aria-selected="false">
   					<?= lang('system.orders.receipt_tab_hist') ?>
   				</a>
 			</li>
 			<?php endif ?>
 			<?php if (!empty($content_stacking) && is_array($content_stacking)) :?>
 			<li class="nav-item">
   				<a class="nav-link" id="tabs-stack-tab" data-toggle="pill" href="#tabs-stack" role="tab" aria-controls="tabs-stack" aria-selected="false">
   					<?= lang('system.orders.customer_stack_tab') ?>
   				</a>
 			</li>
 			<?php endif ?>
 			
     	</ul>
   	</div>
   	<div class="card-body">
   		<?= form_open_multipart(empty($_formview_action) ? '': $_formview_action,empty($_formview_action_attr) ? []: $_formview_action_attr,empty($_formview_action_hidden) ? []: $_formview_action_hidden); ?>	
		<div class="tab-content" id="tabs-tabContent">
			<div class="tab-pane fade show active w-50" id="tabs-cfg" role="tabpanel" aria-labelledby="tabs-cfg-tab">
 				<?= $currentView->includeView('System/form_fields',[]); ?>
    		</div>
    		<div class="tab-pane fade w-75" id="tabs-hist" role="tabpanel" aria-labelledby="tabs-hist-tab">
    			<?= !empty($movements) ? $movements : '' ?>	
    		</div>
    		<?php if (!empty($content_table) && is_object($content_table)) :?>
    		<div class="tab-pane fade w-50" id="tabs-stock" role="tabpanel" aria-labelledby="tabs-stock-tab">
    			<?= $currentView->includeView('Warehouse/products_list') ?>
    		</div>
    		<?php endif ?>
    		<?php if (!empty($pallets) && is_array($pallets) && count($pallets)>0) : ?>
    		<div class="tab-pane fade" id="tabs-pall" role="tabpanel" aria-labelledby="tabs-pall-tab">
    			<ul class="nav flex-column mb-3 col-2">
                  <li class="nav-item">
                    <?= lang('system.orders.pallets_status_all') ?>
                    <span class="float-right badge bg-info"><?= $pallets_status_all ?></span>
                  </li>
                  <?php if (!empty($pallets_status_notcomp)) :?>
                  <li class="nav-item">
                    <?= lang('system.orders.pallets_status_notcomp') ?>
                    <span class="float-right badge bg-info"><?= $pallets_status_notcomp ?></span>
                  </li>
                  <?php endif ?>
                  <?php if (!empty($pallets_status_comp)) :?>
                  <li class="nav-item">
                    <?= lang('system.orders.pallets_status_comp') ?>
                    <span class="float-right badge bg-info"><?= $pallets_status_comp ?></span>
                  </li>
                  <?php endif ?>
                </ul>
    			<?= $currentView->includeView('Pallets/pallets_list',(!empty($_pallets_list_columns)) && is_array($_pallets_list_columns) && count($_pallets_list_columns) > 0 ? ['columns'=>$_pallets_list_columns] : []) ?>
    		</div>
    		<?php endif ?>
    		<?= form_close(); ?>
    		<?php if (!empty($content_stacking) && is_array($content_stacking)) :?>
    		<div class="tab-pane fade" id="tabs-stack" role="tabpanel" aria-labelledby="tabs-stack-tab">
    			<?= $currentView->includeView('Warehouse/Loads/stacking_dims',$content_stacking) ?>
    		</div>
    		<?php endif ?>
    		
     	</div>
     	
	</div>
	<div class="card-footer d-flex">
        <div class="ml-auto">
        	<button type="button" form="edit-form" class="btn btn-success <?= $save_dis ? 'd-none':''?>" id="order_save_btn">
            	<i class="far fa-save mr-1"></i><?= lang('system.buttons.save'); ?>
            </button>
            <a type="button" class="btn btn-outline-danger<?= empty($_formview_urlcancel) ? ' d-none' : '' ?>" href="<?= !empty($_formview_urlcancel) ? $_formview_urlcancel: '#'  ?>">
              	<i class="fas fa-ban mr-1"></i><?= lang('system.buttons.cancel'); ?>
            </a>
          </div>
	</div>
</div>
<script>
	var pallets_list_table=null;
	$(function(){
		if ($("#id_reference").attr('readonly')!='readonly'){
			$("#id_reference").wrap('<div class="input-group mb-3">');
			$("#id_reference").after(function(){
				var refhtml='<div class="input-group-append">';
				refhtml+='<button type="button" class="btn btn-secondary btn-sm" onClick="fetchOrderNr()"><i class="fas fa-bolt"></i></button>';
				refhtml+='</div>';
				return refhtml;
			});
		}
		<?php if (!empty($record) && is_array($record)) :?>
		addMovement('#id_status','change_status','orders',['#id_reference','val'],'<?= $record['status_name'] ?>',['#id_status option:selected','text'],'',true);
		<?php endif ?>
		
		<?php if (!empty($save_dis) && $save_dis==TRUE) :?>
			$('.btn[type="submit"]').remove();
			$('.btn-danger[type="button"]').remove();
			$('.btn-primary').remove();
			$('.tableview_btn_del').remove();
			$('#id_product_add_field').remove();
		<?php endif ?>
		if ($("[name='oid']").val()!=undefined && $("[name='oid']").val().length<1){
			$("#id_status_field").addClass("d-none");
		}
		
		pallets_list_table=$('#pallets_list_table').DataTable({
			'autoWidth':false,
			'ordering':false,
			'searching':true,
			'pageLength':100,
			dom:'Bfrtip',
			buttons:[
			<?php if (!empty($scan_link) && strlen($scan_link)>0) : ?>
				{ 
					className: 'btn btn-secondary btn-sm mb-2',
					text:'<i class="fas fa-barcode mr-1"></i><?= lang('system.orders.supplier_scanbtn') ?>',
					action: function ( e, dt, node, config ) {
						scanPallModalShow();
            		} 
				},
			<?php else :?>
			{
				extend: 'pdf',
            	className: 'ml-1 mb-2 btn btn-sm btn-warning',
				text:'<i class="far fa-file-pdf mr-2"></i><?= lang('system.buttons.exportpdfbtn') ?>',
            	exportOptions: {
                modifier: {
                    search: 'none'
                	}
            	}
        	},
        	{ 
				extend: 'csv', 
				className: 'ml-1 mb-2 btn btn-sm btn-primary',
				text:'<i class="fas fa-file-csv mr-2"></i><?= lang('system.buttons.exportbtn') ?>',
				filename:$("#id_reference").val(),
			},
			<?php endif ?>
			],
			
		});
		$(".dt-button").removeClass('dt-button');
		$("#pallets_list_table_filter").html('<input type="text" class="form-control mb-1 form-control-sm" placeholder="Filter" id="pallets_list_table_filter_value" value="">');
		$("#pallets_list_table_filter").append('<button type="button" class="btn btn-secondary btn-sm ml-1" onclick="filterTable()"><i class="fas fa-filter"></i></button>');
		
		<?php if (!empty($print_pall) && $print_pall!=null) : ?>
			if ($("#id_status option:selected").val()==5){
				ConfirmDialog('<?= lang('system.orders.order_pdfprint')?>',function(){
					window.open('<?= $pdfBtn ?>', '_blank');
				});
			}else{
				if ($("#id_oid").val()!='<?= $print_pall ?>'){
				ConfirmDialog('<?= lang('system.pallets.print_question')?>',function(){
					window.open('<?= str_replace('-id-', $print_pall, $print_pall_url) ?>', '_blank');
				});
				}
			}
				
		<?php endif ?>
	});
	
	$("#id_owner").on("change",function(){
   		fetchOrderNr();
    });
    
    $('#pallets_list_table_filter_value').on('keypress', function (e) {
    	if (e.which==13){
    		filterTable();
    	}
	} );
    
    $("#order_save_btn").on("click",function(){
    	if (typeof(getStackingData) == 'function') {
			$("#id_ocfg").val(getStackingData());
		}
    	
    	$("#"+$(this).attr('form')).submit();
    });
    
    
    function filterTable(){
    	pallets_list_table.search($("#pallets_list_table_filter_value").val()).draw();
    }
    
    function fetchOrderNr(){
    	var val=$("#id_owner option:selected").val();
    	val=val.substr(0,3);
    	var ref='<?= $receiptref; ?>'; 
    	ref=ref.replace('%',val);
    	$("#id_reference").val(ref.toUpperCase());
    }
    

    
    </script>
    <?= $currentView->includeView('Pallets/scan') ?>