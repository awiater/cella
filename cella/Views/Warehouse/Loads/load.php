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
   		<ul class="nav nav-tabs<?= empty($load_items) ? ' d-none' : '' ?>" id="tabs-tab" role="tablist">
   			<li class="nav-item">
   				<a class="nav-link<?= $curtab==0 ? ' active' : '' ?>" id="tabs-cfg-tab" data-toggle="pill" href="#tabs-cfg" role="tab" aria-controls="tabs-cfg" aria-selected="true">
   					<?= lang('system.warehouse.collections_tab_cfg') ?>
   				</a>
 			</li>
 			<li class="nav-item<?= empty($load_items) ? ' d-none' : '' ?>">
   				<a class="nav-link <?= $curtab==1 ? ' active' : '' ?>" id="tabs-tasks-tab" data-toggle="pill" href="#tabs-tasks" role="tab" aria-controls="tabs-tasks" aria-selected="true">
   					<?= lang('system.warehouse.collections_tab_tasks') ?>
   				</a>
 			</li>
 			<li class="nav-item">
   				<a class="nav-link" id="tabs-hist-tab" data-toggle="pill" href="#tabs-hist" role="tab" aria-controls="tabs-hist" aria-selected="false">
   					<?= lang('system.pallets.pallet_tab_hist') ?>
   				</a>
 			</li>
 			<li class="nav-item <?= is_array($stackingplan) ? '' : 'd-none' ?>">
   				<a class="nav-link" id="tabs-plan-tab" data-toggle="pill" href="#tabs-plan" role="tab" aria-controls="tabs-plan" aria-selected="false">
   					<?= lang('system.warehouse.collections_tab_plan') ?>
   				</a>
 			</li>
   		</ul>
   		<?= form_open_multipart(empty($_formview_action) ? '': $_formview_action,empty($_formview_action_attr) ? []: $_formview_action_attr,empty($_formview_action_hidden) ? []: $_formview_action_hidden); ?>	
		<div class="tab-content<?= !empty($load_items) ? ' border border-top-0 p-2' : '' ?>" id="tabs-tabContent">
		
		<div class="tab-pane fade" id="tabs-hist" role="tabpanel" aria-labelledby="tabs-hist-tab">
    		<?= !empty($movements) ? $movements : '' ?>	
    	</div>
		
		<div class="tab-pane fade" id="tabs-plan" role="tabpanel" aria-labelledby="tabs-plan-tab">
    		<?= $currentView->includeView('Warehouse/Loads/stacking_table',['stackingdata'=>$stackingplan,'stackingedit'=>TRUE]) ?>
    	</div>
		
		<div class="tab-pane fade<?= $curtab==1 ? ' show active' : '' ?>" id="tabs-tasks" role="tabpanel" aria-labelledby="tabs-tasks-tab">
			<?= $currentView->includeView('Warehouse/Loads/load_tasks'); ?> 
		</div>
		
		<div class="tab-pane fade<?= $curtab==0 ? ' show active' : '' ?>" id="tabs-cfg" role="tabpanel" aria-labelledby="tabs-cfg-tab">
		<div class="<?= $currentView->isMobile() ? 'w-100' : 'w-75' ?>">
			<?= $currentView->includeView('System/form_fields',[]); ?>
		</div>
		<div class="form-group col-9" id="id_pallets_field">
    		<!-- Removed in version 2.0
    		<div class="card card-body bg-light <?= !empty($readonly) && $readonly ? 'd-none' : '' ?>">
    			<h5><?= lang('system.warehouse.collections_avalpall') ?></h5>
    			<div style="overflow-y:auto;max-height:350px;overflow-x: hidden;">
    			< $currentView->includeView('Warehouse/Loads/'.$items_layout,['tbody_id'=>'pallets_table_body','load'=>null,'tableID'=>'loadItemsTableAvaliable']); ?>
    			</div>
    			<div>
    			<button type="button" class="btn btn-primary btn-sm mt-2" id="id_addpallet">
            		<i class="fa fa-plus mr-1"></i><?= lang('system.buttons.add'); ?>
            	</button>
            	</div>
    		</div>
    		<div class="card card-body bg-light">
    			<h5><?= lang('system.warehouse.collections_setpall') ?></h5>
    			<div style="overflow-y:auto;max-height:350px;overflow-x: hidden;">
    			<? $currentView->includeView('Warehouse/Loads/'.$items_layout,['tbody_id'=>'setpallets_table_body','columns'=>['tick','reference','size','stack','height','corder','loadstatus'],'pallets'=>$set_pallets,'pid'=>'pallets','load'=>$load,'tableID'=>'loadItemsTable']); ?></div>
    		</div>-->
    		<label for="loadItemsTableContainer" class="mr-2 d-flex mt-4">
    			<?= lang('system.warehouse.collections_setpall') ?>
    			<button type="button" class="btn btn-primary btn-sm ml-auto <?= !empty($readonly) && $readonly ? 'd-none' : '' ?>" id="id_openjobswindow" data-toggle="tooltip" data-placement="left" title="<?= lang('system.warehouse.collections_addordbtn')?>">
            		<i class="fa fa-plus mr-1"></i><?= lang('system.buttons.add'); ?>
            	</button>
    		</label>
    		<div style="overflow-y:auto;max-height:350px;overflow-x: hidden;" id="loadItemsTableContainer">
    			<?= $currentView->includeView('Warehouse/Loads/'.$items_layout,['tbody_id'=>'setpallets_table_body','columns'=>['tick','reference','size','stack','height','corder','loadstatus'],'pallets'=>$set_pallets,'pid'=>'pallets','load'=>$load,'tableID'=>'loadItemsTable']); ?>
    		</div>
   		</div>
   		</div>
   		</div>
                <div id="id_movements_orders" class='d-none'></div>
     	<?= form_close(); ?>
	</div>
	<div class="card-footer d-flex">
		<a target='_blank' href="<?= url('Warehouse','collectionmanifest',[$record['reference']],['refurl'=>current_url(FALSE,TRUE)])?>" class="btn btn-secondary<?= (!empty($readonly) && $readonly) ? '' : ' d-none' ?>">
            <i class="fas fa-clipboard-list mr-1"></i><?= lang('system.warehouse.collections_manifest'); ?>
        </a>
        <div class="ml-auto">
        	<button type="button" class="btn btn-success<?= (!empty($readonly) && $readonly) && !$savevis ? ' d-none' : '' ?>" id="id_pallet_save">
            	<i class="far fa-save mr-1"></i><?= lang('system.buttons.save'); ?>
            </button>
            <a type="button" class="btn btn-outline-danger<?= empty($_formview_urlcancel) ? ' d-none' : '' ?>" href="<?= !empty($_formview_urlcancel) ? $_formview_urlcancel: '#'  ?>">
              	<i class="fas fa-ban mr-1"></i><?= lang('system.buttons.cancel'); ?>
            </a>
          </div>
	</div>
</div>
</div>

<?php if (empty($readonly)) :?>
<div class="modal" tabindex="-1" role="dialog" id="avalJobsWindow">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><?= lang('system.warehouse.collections_avalpall') ?></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="<?= lang('system.buttons.close');?>">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
      	<div style="overflow-y:auto;max-height:350px;overflow-x: hidden;width:100%" id="avalJobsWindowContent">
    		<?= $currentView->includeView('Warehouse/Loads/'.$items_layout,['tbody_id'=>'pallets_table_body','load'=>null,'tableID'=>'loadItemsTableAvaliable']); ?>
    		<input type="hidden" id="avalJobsWindowMaxStacks">
    	</div>
      </div>
      <div class="modal-footer">
        <div id="avalJobsWindowMaxStacksInfo" class="mr-auto"></div>
        <button type="button" class="btn btn-primary" id="id_addpallet">
        	<?= lang('system.buttons.add'); ?>
        </button>
        <button type="button" class="btn btn-danger" id="id_closenewpalletwindow">
        	<?= lang('system.buttons.close');?>
        </button>
      </div>
    </div>
  </div>
</div>
<?php endif ?>

<script>	
	$(function(){	
		addRefButton('id_newpallet','');	
		$("#loadItemsTableAvaliable_wrapper").css('width','100%!important');
		if ($("#setpallets_table_body tr").length < 1) {
			$("#id_removepallet").addClass('d-none');
			$("#id_comppallet").addClass('d-none');
		}
		<?php if (array_key_exists('isnew', $record) && !$record['isnew']) :?>
		addMovement('#id_status','change_status','loads','<?= $record['reference'] ?>','<?= $record['status_name'] ?>',['#id_status option:selected','text'],'');
		<?php elseif (array_key_exists('isnew', $record) && $record['isnew']) :?> 
		addMovement('#id_reference','collections_created','loads',['#id_reference','val'],'','',['#id_duein','val'],true);
		<?php endif ?>
		$("#loadItemsTableAvaliable").find('tr th:nth-child(8)').css('width','20px');
		loadItemsTableAvaliable.columns.adjust().draw();
		
	});
	
	$("#id_openjobswindow").on("click",function(){
		$("#avalJobsWindowMaxStacks").val($("#maxStacks").text());
		updateStacksInfo(0);
		$("#avalJobsWindow").modal('show');
	});
	
	
		
	$('input[name*=\'pallets\']').on('change',function(){
		var val=parseInt($("#avalJobsWindowMaxStacks").val());
		var qty=parseInt($(this).attr('data-stackqty'));
		if($(this).is(':checked')){
			val=val+qty;
		}else{
			val=val-qty;
		}
		$("#avalJobsWindowMaxStacks").val(val);
		updateStacksInfo(val);
	});
	
	function updateStacksInfo(qty)
	{
		$("#avalJobsWindowMaxStacksInfo").text(qty+' / '+$("#maxStacks").text());
	}
	
	$('#id_duein').on("change",function(){
		var val=$(this).datepicker("getDate");
		val=$.datepicker.formatDate('yymd', val);
		var refurl='<?= url('Warehouse','getcollectionref', ['-id-']) ?>';
		refurl=refurl.replace('-id-',val);
		$.ajax({
            type: 'GET',
            dataType: 'json',
            url: refurl,
            data:{ },
            success: function(data) {
            	if (data['result']!=null){
            		<?php if(!array_key_exists('lid', $record)) :?>
            		$("#id_reference").val(data['result']);           		
            		$("#id_reference").trigger("change");
            		<?php else :?>
            		$("#id_reference").attr('data-newref',data['result']);
            		
            		addRefGenBtn();
            		<?php endif ?>
            	}		
            },
            error: function(data) {
              console.log(JSON.stringify(data));
            }
        });
	});
	
	$("#id_status").on("change",function(){
		$("#id_changePalletsStatus").remove();	
		var val=$('#id_status option:selected').val();
		if (val=='3' || val==3){
			ConfirmDialog('<?= lang('system.warehouse.collections_changestatusmsg')?>',function(){
			$("#edit-form").append('<input type="hidden" name="changePalletsStatus" value="1" id="id_changePalletsStatus">');	
			});
		}
	});
	
	$("#id_pallet_save").on("click",function(){
		$("tr[data-status='added']").each(function(){
			if ($(this).attr('data-job')=='new'){
			addMovementData('collections_addorder','loads',['#id_reference','val'],'','',$(this).children('td').eq(1).text());
			}
		});
		$("tr[data-status='remove']").each(function(){
			if ($(this).attr('data-job')=='old'){
				addMovementData('collections_remorder','loads',['#id_reference','val'],'','',$(this).children('td').eq(1).text());
			}
			
		});
		
		$('input[name*=\'pallets\']').each(function(){
			$(this).prop('checked',true);
		});
		var validate=true;
		$('[required]').each(function(){
			 if( $(this).val().length < 3 ){
          		$(this).addClass('border border-danger');
          		$("#id_load_error").removeClass("d-none").text('<?= lang('system.warehouse.load_requirederror') ?>');
          		validate=false;
        	}
		})
		if (validate){
			$("#edit-form").attr('action','<?= empty($_formview_action) ? '': $_formview_action ?>').submit();
		}
		
	});
	
	$("#id_closenewpalletwindow").on("click",function(){
		$('#avalJobsWindow').modal('hide');
		$('input[name*=\'pallets\']').prop('checked',false);
	});
	
	$("#id_addpallet").on("click",function(){
		$('#setpallets_table_body .dataTables_empty').addClass('d-none');
		$('input[name*=\'pallets\']').each(function(){
			if ($(this).prop('checked')==true){
				
				var row=$('#pallets_table_row_'+$(this).attr('data-id'));
				$("#setpallets_table_body").append(row);
				row.children('td').eq(0).html('');
				
				var html='<button type="button" class="btn btn-danger btn-sm" onclick="removePallet(';
				html+="'"+$(this).attr('data-id')+"'";
				html+=')" data-toggle="tooltip" data-placement="right" title="<?= lang('system.warehouse.collections_remordbtn')?>"><i class="fa fa-trash"></i></button>';
				html+='<input type="hidden" name="pallets[]" value="'+row.children('td').eq(1).text()+'" class="palletref">';
				row.children('td').eq(7).append(html);
				row.attr('data-status','added');
					
				var maxstack=parseInt(row.find('.maxstack').html());
				var max=parseInt($("#maxStacks").html());
				
				max=max+maxstack;
				$("#maxStacks").html(max);
				$('#setpallets_table_body').append($("#sumRow"));
				$('[data-toggle="tooltip"]').tooltip();
				<?php if (array_key_exists('isnew', $record) && !$record['isnew']) :?>
				
				<?php endif ?>
				/*var orders=$('#id_movements_orders').val();
				orders=orders.replace(row.find('td:nth-child(2)').text(),'').replace(';;',';');
				$('#id_movements_orders').val(orders);
				*/
                               $(this).prop('checked',false);
                               $('input[data-remove="'+row.find('td:nth-child(2)').text()+'"]').remove();
			}
		});
		$('#avalJobsWindow').modal('hide');
	});
	
	function calcMaxStack(){
		var max=0;
		$("#loadItemsTable tr").each(function(){
			if ($(this).attr('id')!='sumRow'){
				var item=$(this).find('td:nth-child(7)').text();
				item=parseInt(item);
				if (item > 0){
					max=max+item;
				}
			}
		});
		$("#maxStacks").html(max);
	}
	
	function removePallet(id,loading=false){
		var row=$('#pallets_table_row_'+id);
		//row.children('td').eq(6).remove();
		var palletref=row.find('td:nth-child(2)').text();
                
		var max=$("#loadItemsTableAvaliable tr").length;
		max=max+2;
		row.children('td').eq(0).append('<input type="checkbox" name="pallets[]" value="'+row.find('td:nth-child(2)').text()+'" data-id="'+id+'">');
		
		$("#pallets_table_body").append(row);
		$(this).prop('checked',false);
		$(this).attr('name','pid[]');
		$("#id_addpallet").removeClass('d-none');
				
		if ($("#setpallets_table_body tr").length > 0) {
			$("#id_comppallet").removeClass("d-none");
		}else{
			$("#id_comppallet").addClass("d-none");
		}
		
		//row.attr('id',row.attr('id').replace('_pallets_','_pid_'));
		//row.attr('data-status','remove');
		if(!loading){
			$("#id_movements_orders").append('<input type="text" name="removedJobs[]" value="'+palletref+'" data-remove="'+palletref+'">');
		}
                row.find('.btn-danger').remove();
                row.find('.palletref').remove();
		calcMaxStack();
	}
	
	$("#id_comppallet").on("click",function(){
		$("#edit-form").attr('action','<?= empty($complpalurl) ? '': $complpalurl ?>').submit();
	});
	
	function addRefButton(id,func)
	{
		$("#"+id).wrap('<div class="input-group mb-3">');
		$("#"+id).after(function(){
			var refhtml='<div class="input-group-append">';
			refhtml+='<button type="button" class="btn btn-primary btn-sm" onClick="'+func+'" id="bolt_btn_'+id+'"><i class="fa fa-plus"></i></button>';
			refhtml+='</div>';
			return refhtml;
		});		
	}
	
	function addRefGenBtn()
	{
		if ($("#bolt_btn_id_reference").length < 1)
		{
			$("#id_reference").wrap('<div class="input-group mb-3">');
			$("#id_reference").after(function(){
				var refhtml='<div class="input-group-append">';
				refhtml+='<button type="button" class="btn btn-secondary btn-sm" onClick="$(';
				refhtml+="'#id_reference').val($('#id_reference').attr('data-newref'));";
				refhtml+='" id="bolt_btn_id_reference"><i class="fas fa-bolt"></i></button>';
				refhtml+='</div>';
				return refhtml;
			});
		}		
	}
</script>