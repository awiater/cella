<div class="alert alert-warning d-none" role="alert" id="id_pallet_error">
 	<p id="id_pallet_error_p"><p>
</div>
<div class="card card-primary card-outline card-tabs">
	<div class="card-header p-0 pt-1 border-bottom-0">
   		<ul class="nav nav-tabs" id="tabs-tab" role="tablist">
 			<?php if ($edit_access) :?>
 			<li class="nav-item">
   				<a class="nav-link active" id="tabs-cfg-tab" data-toggle="pill" href="#tabs-cfg" role="tab" aria-controls="tabs-cfg" aria-selected="true">
   					<?= lang('system.pallets.pallet_tab_cfg') ?>
   				</a>
 			</li>
 			<?php if (!empty($content_table) && is_object($content_table) > 0) :?>
 			<li class="nav-item">
   				<a class="nav-link" id="tabs-stock-tab" data-toggle="pill" href="#tabs-stock" role="tab" aria-controls="tabs-stock" aria-selected="false">
   					<?= lang('system.pallets.pallet_tab_stock') ?>
   				</a>
 			</li>
 			<?php endif ?>
 			<?php endif ?>
 			<?php if ($isnew!=TRUE && $edit_access && !$currentView->isMobile()) :?>
 			<li class="nav-item">
   				<a class="nav-link" id="tabs-hist-tab" data-toggle="pill" href="#tabs-hist" role="tab" aria-controls="tabs-hist" aria-selected="false">
   					<?= lang('system.pallets.pallet_tab_hist') ?>
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
    		<?php if (!empty($content_table) && is_object($content_table) > 0) :?>
    		<div class="tab-pane fade w-50" id="tabs-stock" role="tabpanel" aria-labelledby="tabs-stock-tab">
    			<?= $currentView->includeView('Warehouse/products_list') ?>
    		</div>
    		<?php endif ?>
     	</div>
     	<?= form_close(); ?>
	</div>
	<div class="card-footer d-flex">
		<?php if (array_key_exists('pid', $record) && is_numeric($record['pid'])) :?>
		<a href="<?= url('Pallets','label',[$record['pid']],['refurl'=>current_url(FALSE,TRUE)]) ?>" target="_blank" class="mr-1 btn btn-secondary">
			<i class="fas fa-barcode mr-1"></i><?= lang('system.pallets.index_lblbtn') ?>
		</a>
		<?php endif ?>
        <div class="ml-auto">
        	<?php  if (!empty($apiurl) && $record['status'] > -1) : ?>
        	<button type="button" class="btn btn-success" id="id_pallet_save">
            	<i class="far fa-save mr-1"></i><?= lang('system.buttons.save'); ?>
            </button>
            <?php endif ?>
            <a type="button" class="btn btn-outline-danger<?= empty($_formview_urlcancel) ? ' d-none' : '' ?>" href="<?= !empty($_formview_urlcancel) ? $_formview_urlcancel: '#'  ?>">
              	<i class="fas fa-ban mr-1"></i><?= lang('system.buttons.cancel'); ?>
            </a>
          </div>
	</div>
</div>

<script>
	
	$(function(){	
		if ($("#id_corder").attr("readonly")==undefined){
			addRefButton('id_corder','getPallRefernce(null,1)');
		}	
	});
	
	<?php if (!empty($record) && is_array($record)) :?>
	<?php if (array_key_exists('statusname', $record)) :?>
	addMovement('#id_status','change_status','pallets','<?= $record['reference'] ?>','<?= $record['statusname'] ?>',['#id_status option:selected','text'],'');
	<?php endif ?>
	addMovement('#id_size','change_details','pallets','<?= $record['reference'] ?>','<?= $record['size'] ?>',['#id_size option:selected','text'],'<?= lang('system.pallets.pallet_size')?>');
	addMovement('#id_stack','change_details','pallets','<?= $record['reference'] ?>','<?= trim($record['stack']) ?>',['#id_stack option:selected','text'],'<?= lang('system.pallets.pallet_stack')?>');
	addMovement('#id_height','change_details','pallets','<?= $record['reference'] ?>','<?= $record['height'] ?>',['#id_height ','val'],'<?= lang('system.pallets.pallet_height')?>');
	addMovement('#id_corder','order_assign','pallets','<?= $record['reference'] ?>','','',['#id_corder ','val']);
	addMovement('#id_location','change_details','pallets','<?= $record['reference'] ?>','<?= $record['location'] ?>',['#id_location ','val'],'<?= lang('system.pallets.pallet_location')?>');
	addMovement('#id_location','locations_moveout','locations','<?= $record['location'] ?>','','','<?= $record['reference'] ?>');
	addMovement('#id_location','locations_movein','locations',['#id_location ','val'],'','','<?= $record['reference'] ?>');
	<?php endif ?>
	$("#id_pallet_save").on("click",function(){
		$("#edit-form").submit();
	});
	
	function addRefButton(id,func)
	{
		$("#"+id).wrap('<div class="input-group mb-3">');
		$("#"+id).after(function(){
			var refhtml='<div class="input-group-append">';
			refhtml+='<button type="button" class="btn btn-secondary btn-sm" onClick="'+func+'" id="bolt_btn_'+id+'"><i class="fas fa-bolt"></i></button>';
			refhtml+='</div>';
			return refhtml;
		});		
	}
	$("#id_customer").on("change",function(){
		getPallRefernce($(this).val(),1);
	});
	
	function getPallRefernce(cust,tp=0,rmode='customer'){
		if (cust==null){
			//cust=$('#id_customer option:selected').val();
			cust=$('#id_customer').val();
		}
		
		$("#id_pallet_error").addClass('d-none');
		var orders_inpick='<?= !empty($orders_inpick) ? implode(',',$orders_inpick) : '' ?>';
		$.ajax({
            type: 'GET',
            dataType: 'json',
            url: '<?= !empty($apiurl) ? $apiurl :''; ?>',
            data:{ mode:rmode,code:cust,type:tp},
            success: function(data) {
            	if (data['result']!=null){
            		if (tp==0){
            			$("#id_reference").val(data['result']);
            			$("#id_reference_cust").val(data['result']);
            		}else{
            			$("#id_corder").val(data['result']);
            			
            			if (orders_inpick.length > 0 && orders_inpick.search(data['result']) >= 0){
            				$("#id_pallet_error").removeClass("d-none");
            				$("#id_pallet_error").html('<?= lang('system.orders.error_order_in_pick')?>');
            				Dialog('<?= lang('system.orders.error_order_in_pick')?>','warning');
            			}
            		}
            	}		
            },
            error: function(data) {
              alert(JSON.stringify(data));
            }
        });           
	}
	
	
</script>