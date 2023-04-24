<?php $truckLength=13600; $truckHeight=2650; $truckWidth=2450; $noOfPallSpaces=33;$palletnr=1;$eurLength=1200; $stdWidth=1000; $eurWidth=800; $truckPallets=[];?>
<?php if ($currentView->ismobile(FALSE)) :?>
	<?= $currentView->controller->createMessage('system.warehouse.mobileaccess_error','danger'); ?>
<?php else :?>
<div class="col-12">
	<div class="card">
		<div class="card-header">
    		<button type="button" onclick="printTruck()" class="btn btn-sm btn-secondary">
    			<i class="fas fa-print mr-2"></i><?= lang('system.buttons.printbtn') ?>
    		</button>
    	</div>
    	<div class="card-body">
    		<?= form_open_multipart($form_action,['id'=>'saveTruckForm']) ?>
			<div class="row">
				<div class="col-3 hide_scroll" style="overflow-y:scroll;height:1200px;">
					<div style="background-image: url(<?= parsePath('@assets/files/images/truck/truck_cab.png') ?>);background-repeat: no-repeat;background-size:100% 100%;width:350px;height:300px;">
					</div>
					<div style="background-image: url(<?= parsePath('@assets/files/images/truck/truck_tr1.png') ?>);background-repeat: no-repeat;background-size:100% 100%;width:350px;height:17px;">
					</div>
					<div style="background-image: url(<?= parsePath('@assets/files/images/truck/truck_tr2.png') ?>);background-repeat: no-repeat;background-size:100% 100%;width:350px;">
						<table class="table table-bordered" style="margin:0px 5px 0px 5px;width:340px;">
							<thead>
								<tr>
									<th></th>
									<th></th>
									<th></th>
									<th></th>
								</tr>
							</thead>
							<tbody>
								<?php $pid=0 ?>
							<?php for($i=0;$i<16;$i++) :?>
							<tr class="bg-danger" style="height:150px;">
								
								<?php if ($i<15) : ?>
								<td class="bg-white" colspan="2" id="truck_space_<?= $pid ?>">
									<div class="d-flex">
										<button type="button" class="btn btn-success btn-sm" onClick="addStackToTruck(<?= $pid ?>)">
											<i class="fa fa-plus"></i>
										</button>
										<button type="button" class="btn btn-danger btn-sm ml-auto d-none" onClick="removeStackFromTruck(<?= $pid ?>)">
											<i class="fa fa-trash"></i>
										</button>
									</div>
								</td>
								<?php $pid=$pid+1 ?>
								<td class="bg-white" colspan="2" id="truck_space_<?= $pid ?>"> 
									<div class="d-flex">
										<button type="button" class="btn btn-success btn-sm" onClick="addStackToTruck(<?= $pid ?>)">
											<i class="fa fa-plus"></i>
										</button>
										<button type="button" class="btn btn-danger btn-sm ml-auto d-none" onClick="removeStackFromTruck(<?= $pid ?>)">
											<i class="fa fa-trash"></i>
										</button>
									</div>
								</td>
								<?php $pid=$pid+1 ?>
								<?php else :?>
								<td class="bg-white" style="width:33%" id="truck_space_31">
									<div class="d-flex">
										<button type="button" class="btn btn-success btn-sm" onClick="addStackToTruck(31)">
											<i class="fa fa-plus"></i>
										</button>
										<button type="button" class="btn btn-danger btn-sm ml-auto d-none" onClick="removeStackFromTruck(31)">
											<i class="fa fa-trash"></i>
										</button>
									</div>
								</td>
								<td class="bg-white" colspan="2" style="width:34%" id="truck_space_32">
									<div class="d-flex">
										<button type="button" class="btn btn-success btn-sm" onClick="addStackToTruck(32)">
											<i class="fa fa-plus"></i>
										</button>
										<button type="button" class="btn btn-danger btn-sm ml-auto d-none" onClick="removeStackFromTruck(32)">
											<i class="fa fa-trash"></i>
										</button>
									</div>
								</td>
								<td class="bg-white" style="width:33%" id="truck_space_33">
									<div class="d-flex">
										<button type="button" class="btn btn-success btn-sm" onClick="addStackToTruck(33)">
											<i class="fa fa-plus"></i>
										</button>
										<button type="button" class="btn btn-danger btn-sm ml-auto d-none" onClick="removeStackFromTruck(33)">
											<i class="fa fa-trash"></i>
										</button>
									</div>
								</td>	
								<?php endif ?>
							</tr>
							<?php endfor ?>
							</tbody>
						</table>
					</div>
					<div style="background-image: url(<?= parsePath('@assets/files/images/truck/truck_tr3.png') ?>);background-repeat: no-repeat;background-size:100% 100%;width:350px;height:17px;">
					</div>
				</div>
				<div class="col-3">
					<div class="d-flex" style="background-image: url(<?= parsePath('@assets/files/images/truck/forklift.png') ?>);background-repeat: no-repeat;background-size:100% 70%;width:250px;height:880px;">
						<div class="mt-auto" style="height:450px;width:100%">
							<div class="border border-danger bg-secondary d-flex" style="height:25px;">
								HEIGHT:
								<p id="id_stack_info" data-height="<?= $truckHeight ?>" class="ml-auto"><?= $truckHeight ?></p>
							</div>
							<div class="border border-danger bg-warning" style="height:150px;">
								TOP
								<div id="id_stack_top" class="droppable_pallet h-100 w-100" data-pos="T"></div>
							</div>
							<div class="border border-danger bg-warning" style="height:150px;">
								MIDDLE
								<div id="id_stack_mid" class="droppable_pallet h-100 w-100" data-pos="M"></div>
							</div>
							<div class="border border-danger bg-warning" style="height:150px;">
								BOTTOM
								<div id="id_stack_bot" class="droppable_pallet h-100 w-100" data-pos="B"></div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-6">
					<table class="table table-striped" id="avalPalletsTbl">
					<thead>
						<tr>
							<th style="width:80px;"></th>
							<th><?= lang('system.warehouse.stackwizard_reference') ?></th>
							<th><?= lang('system.warehouse.stackwizard_lmh') ?></th>
							<th><?= lang('system.warehouse.stackwizard_length') ?></th>
							<th><?= lang('system.warehouse.stackwizard_width') ?></th>
							<th><?= lang('system.warehouse.stackwizard_height') ?></th>
							<th><?= lang('system.pallets.pallet_customer') ?></th>
							<th>B/T</th>
						</tr>
					</thead>
					<tbody id="avalPalletsTblBody">
						<?php foreach($data as $key=>$pallet) : ?>
						<tr<?= strlen($pallet['stacktruck']) > 0 ? ' class="d-none bg-danger" data-stack="'.$pallet['stacktruck'].'" ': ''; ?> id="<?= $pallet['reference']?>"  data-rowid="<?= $key ?>" data-src="<?= base64_encode(json_encode($pallet)) ?>" data-height="<?= $pallet['height'] ?>" data-lmh="<?= $pallet['lmh'] ?>"> 
							<td>
								<p class="btn btn-sm btn-primary">
									<i class="fas fa-arrows-alt"></i>
								</p>
							</td>
							<td><?= $pallet['reference'] ?></td>
							<td><?= $pallet['lmh'] ?></td>
							<td><?= $pallet['length'] ?></td>
							<td><?= $pallet['width'] ?></td>
							<td><?= $pallet['height'] ?></td>
							<td><?= $pallet['customer'] ?></td>
							<td><?= $pallet['stacknr'].$pallet['stackpos'] ?></td>
						</tr>
						<?php endforeach ?>
					</tbody>
					</table>
					<div class="d-none">
					<table class="table table-striped" id="setPalletsTbl">
					<thead>
						<tr>
							<th><?= lang('system.warehouse.stackwizard_reference') ?></th>
							<th><?= lang('system.warehouse.stackwizard_lmh') ?></th>
							<th><?= lang('system.warehouse.stackwizard_stack') ?></th>
						</tr>
					</thead>
					<tbody id="setPalletsTblBody">
						<?php foreach($data as $key=>$pallet) : ?>
						<?php if(strlen($pallet['stacktruck']) > 0) :?>
							<?php preg_match_all('!\d+!', $pallet['stacktruck'], $matches); ?>
							<?php if (is_array($matches) && count($matches) >0 && is_array($matches[0]) && count($matches[0])>0) : ?>
							<tr class="truck_stack_<?= $matches[0][0]  ?>">
							<?php elseif  ($pallet['stacktruck']=='N') :?>
							<tr class="truck_stack_N">
							<?php else :?>
							<tr class="truck_stack">
							<?php endif ?>
								<td><?= $pallet['reference'] ?></td>
								<td><?= $pallet['lmh'] ?></td>
								<td><?= $pallet['stacktruck'] ?></td><!-- stacktruck -->
							</tr>
						<?php endif ?>
						<?php endforeach ?>	
					</tbody>
					</table>
					</div>
				</div>
			</div>
    	</div>
    	<div class="card-footer d-flex">
    		<div class="ml-auto">
    		<button type="submit" form="saveTruckForm" class="btn btn-success">
            	<i class="far fa-save mr-1"></i><?= lang('system.buttons.save'); ?>
            </button>
            <a type="button" class="btn btn-outline-danger" href="<?= url('Warehouse') ?>">
              	<i class="fas fa-ban mr-1"></i><?= lang('system.buttons.cancel'); ?>            
            </a>
            </div>
    	</div>
	</div>
</div>
<?= form_close() ?>

<script>
	var PalletsTabel=null;
	var maxPall=11;
	var curTruckLength=<?= $truckLength ?>;
	
	$(function(){
				
		PalletsTabel=$('#avalPalletsTbl').DataTable({'ordering':false,'pageLength':25});
		truckPlanTbl=$('#setPalletsTbl').DataTable(
			{
				'ordering':false,
				dom: 'Bfrtip',
				buttons:[
					{
						 extend: 'print',
            			 title: 'Stacking Plan for <?= $load['reference']?>',
					}
				]
			});
		deleteUsedPallets();
		addDragItems();
	
		 $(".droppable_pallet").droppable({
		 	accept : function(ui){
		 		var palH=parseInt(ui.attr('data-height'));
		 		var spcH=parseInt($('#id_stack_info').attr('data-height'));
		 		return (spcH>=palH);
		 	},
		 	drop: function( event, ui ){
		 		var item=ui.draggable;
		 		var palH=parseInt(item.attr('data-height'));
		 		var spcH=parseInt($('#id_stack_info').attr('data-height'));
		 		spcH=spcH-palH;
		 		$('#id_stack_info').html(spcH);
		 		$('#id_stack_info').attr('data-height',spcH);
		 		item.attr('data-pos',$(this).attr('data-pos'))
		 		$(this).append(createHelper(item));
		 		PalletsTabel.row(item).remove().draw();
		 	}
		 });
		 
		 $("#avalPalletsTblBody").find('.bg-danger').each(function(){
		 	var item=$(this);
		 	item.attr('id',item.attr('id')+' - '+item.attr('data-stack'));
		 	item=createHelper(item);
		 	var id=$(this).attr('data-stack');
		 	id=id.match(/\d+/);
		 	id=id-1;
		 	
		 	var space=$('#truck_space_'+id+'_stack');
		 	var html=$('<div id="truck_space_'+id+'_stack"></div>');
		 	if (space.html()==undefined ){
		 		$("#truck_space_"+id).append(html);
		 		html=$("#truck_space_"+id).find('#truck_space_'+id+'_stack');
		 	}else{
		 		html=space;
		 	}
		 	html.append(item);
		 	$("#truck_space_"+id).find('.btn-success').addClass('d-none');
	   		$("#truck_space_"+id).find('.btn-danger').removeClass('d-none');
	   		PalletsTabel.row($(this)).remove().draw();
		 });
		 
		  $( ".droppable_pallet_remove" ).droppable({
		 	drop: function( event, ui ){
		 		var item=ui.draggable;
		 		var row=$(atob(item.attr('data-object')));
		 		var palH=parseInt(row.attr('data-height'));
		 		var spcH=parseInt($(".droppable_pallet").attr('data-height'));
		 		spcH=spcH+palH;
		 		$(".droppable_pallet").attr('data-height',spcH);
		 		$("#spaceHeightLBL").text(spcH);
		 		
		 		PalletsTabel.row.add(row).draw();
		 		item.remove();
		 		
		 		if ($(".droppable_pallet").html().length<114){
		 			$("#spaceWidthLBL").text('<?= $eurLength?>');
		 			$(".droppable_pallet").attr('data-length',0);	
		 		}
		 	}
		 });
	});
	
	$('#avalPalletsTbl').on( 'draw.dt', function () {
		addDragItems(true);
	});
	
	function deleteUsedPallets(){
		var reference=[];
		if ($("#truck_pallets").text().length > 0){
				reference=JSON.parse($("#truck_pallets").text());
		}
		$.each(reference, function( index, value ){
			var item=$('tr[id="'+value+'"]');
			PalletsTabel.row(item).remove().draw();
		});	
	}
	
	function addStackToTruck(id){
		var html='<div id="truck_space_'+id+'_stack">';
		var item=$("#truck_space_"+id);
		html+=$("#id_stack_top").html();
		html+=$("#id_stack_mid").html();
		html+=$("#id_stack_bot").html();
		html+='</div>';
		item.append(html);
	   	item.find('.btn-success').addClass('d-none');
	   	item.find('.btn-danger').removeClass('d-none');
	   	
	   	id++;
	   	
	   	item.find('.truckstack').each(function(){
	   		var val=$(this).val();
	   		val=id+val;
	   		$(this).val(val);
	   	});
	   	
	   	item.find('.pallet').each(function(){
	   		var html='<tr class="truck_stack_'+id+'">';
	   		html+='<td>'+$(this).find('.reference').val()+'</td>';
	   		html+='<td>'+$(this).find('.lmh').val()+'</td>';
	   		html+='<td>'+$(this).find('.truckstack').val()+'</td>';
	   		html+='</tr>';
	   		truckPlanTbl.row.add($(html)).draw();
	   		truckPlanTbl.order([2,'asc']).draw();
	   		$(this).text($(this).text()+' - '+$(this).find('.truckstack').val());
	   	});
	   	
	   	
	   	$('#id_stack_info').attr('data-height','<?= $truckHeight ?>');
	   	$('#id_stack_info').html('<?= $truckHeight ?>');
	   	
	   	$("#id_stack_top").html('');
	   	$("#id_stack_mid").html('');
	   	$("#id_stack_bot").html('');
	}
	
	function removeStackFromTruck(id){
		var space=$("#truck_space_"+id);
		var stack=$("#truck_space_"+id+'_stack');
		ConfirmDialog("<?= lang('system.general.msg_delete_ques')?>",function(){
			stack.remove();
			space.find('.btn-success').removeClass('d-none');
	   		space.find('.btn-danger').addClass('d-none');
	   		stack.children('.pallet').each(function(){
	   			var value=atob($(this).attr('data-object'));
	   			id++;
	   			$('#setPalletsTblBody').find('.truck_stack_'+id).each(function(){
	   				$(this).remove();
	   			});
	   			value=$(value);
	   			value.removeClass('d-none bg-danger');
	   			PalletsTabel.row.add(value).draw();
	   		});
		});
	}
		
	function addDragItems(id='#avalPalletsTblBody TR'){
		 $(id).each(function(){	 	
		 	$(this).draggable({
		 		handle:"p",
		 		helper: function() {
		 			console.log();
		 			if ($(this).attr('data-object')!=undefined)
		 			{
		 				return createHelper($(atob($(this).attr('data-object'))));
		 			}
		 			return createHelper($(this));
		 		}
		 	});
		 });
	}
	
	function createHelper(object){
		var html=$('<div class="border bg-primary pallet" data-object="'+btoa(object.prop('outerHTML'))+'">'+object.attr('id')+'</div>');
		html.append('<input type="hidden" name="pallets['+"'"+object.attr('id')+"'"+'][reference]" class="reference" value="'+object.attr('id')+'">');
		html.append('<input type="hidden" name="pallets['+"'"+object.attr('id')+"'"+'][stacktruck]" class="truckstack" value="'+object.attr('data-pos')+'">');
		html.append('<input type="hidden" class="lmh" value="'+object.attr('data-lmh')+'">');
		return html;
	}
	
	function printTruck() {
        truckPlanTbl.button('.buttons-print').trigger();
     }
</script>
<?php endif ?>

