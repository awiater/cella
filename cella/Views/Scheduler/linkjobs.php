ref<?= $this->extend('System/form') ?>

<?= $this->section('form_body') ?>
<table class="table table-striped" id="table_view_datatable" aria-describedby="table_view_datatable_info">
	<thead>
		<tr>
			<th scope="col"><?= lang('scheduler.orders.colref') ?></th>
			<th scope="col"><?= lang('scheduler.orders.colowner') ?></th>
			<th scope="col"><?= lang('scheduler.orders.colstatus') ?></th>
			<th scope="col"><?= lang('scheduler.orders.coldel') ?></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($orders as $order) :?>
		<tr>
			<td><?= $order['reference'] ?></td>
			<td><?= $order['owner'] ?></td>
			<td><?= $status[$order['status']] ?></td>
			<td>
				<input type="hidden" name="jobs[]" value="<?=$order['oid']?>">
				<div class="d-flex">
					<ul class="list-group w-100 mr-2" id='linked_<?= $order['oid'] ?>'>
						<?php if (array_key_exists($order['reference'], $linked)) :?>
						<?php foreach($linked[$order['reference']] as $link) :?>
						<li class="list-group-item d-flex" id="linkedjob_<?= $order['oid'].'_'.$delivery[$link['targetid']]['oid'] ?>">
							<?= $delivery[$link['targetid']]['reference'] ?>
							<input type="hidden" name="orders[<?=$order['oid']?>][targetid]" value="<?=$delivery[$link['targetid']]['oid']?>">
							<input type="hidden" name="orders[<?=$order['oid']?>][value]" value="<?=$order['reference']?>">
							<input type="hidden" name="orders[<?=$order['oid']?>][cfid]" value="<?=$link['cfid']?>">
							<input type="hidden" value="<?=$link['cfid']?>" id="linkedjob_<?= $order['oid'].'_'.$delivery[$link['targetid']]['oid'] ?>_remove">
							<button class="btn btn-sm btn-danger ml-auto" type="button" onclick="removeJob('#linkedjob_<?= $order['oid'].'_'.$delivery[$link['targetid']]['oid'] ?>')">
								<i class="fas fa-trash"></i>
							</button>
						</li>
						<?php endforeach ?>
						<?php endif ?>
					</ul>
					<div class="p-0">
						<button class="btn btn-sm btn-primary ml-auto" type="button" onclick="showLinkEditor('<?= $order['oid'] ?>','<?= $order['reference'] ?>')">
							<i class="far fa-plus-square"></i>
						</button>
					</div>
				</div>
			</td>
		</tr>	
		<?php endforeach ?>
	</tbody>
</table>

<div class="modal" tabindex="-1" role="dialog" id="LinkJobsEditor">
	<div class="modal-dialog modal-lg" role="document">
    	<div class="modal-content">
      		<div class="modal-header">
        		<h5 class="modal-title"><?= lang('scheduler.orders.deliverytitle') ?></h5>
        		<button type="button" class="close" data-dismiss="modal" aria-label="Close">
          			<span aria-hidden="true">&times;</span>
        		</button>
      		</div>
      		<div class="modal-body">
        		<input type="hidden" id="id_orderid">
        		<input type="hidden" id="id_orderef">
        		<table class="table">
        			<thead>
        				<th><?= lang('scheduler.orders.colref') ?></th>
        				<th><?= lang('scheduler.orders.newcoldue') ?></th>
        				<th><?= lang('scheduler.orders.colstatus') ?></th>
        				<th><?= lang('scheduler.orders.newcolqty') ?></th>
        				<th style="width:80px"></th>
        			</thead>
        			<tbody>
        				<?php foreach ($delivery as $order) :?>
        				<tr>
        					<td><?= $order['reference'] ?></td>
        					<td><?= strlen($order['duein']) > 0 ? convertDate($order['duein'],'DB','d M Y'): '' ?></td>
        					<td><?= $status[$order['status']] ?></td>
        					<td>
        						<?php if ($order['palletsdisp']==0 && array_key_exists('ocfg', $order) && substr($order['ocfg'],0,1)=='{') :?>
        							<?php $order['ocfg']=json_decode($order['ocfg'],TRUE); echo $order['ocfg']['pallqty']; ?>
        						<?php else :?>
        						<?= $order['palletsdisp'] ?>
        						<?php endif ?>
        					</td>
        					<td>
        						<button class="btn btn-sm btn-primary ml-auto" type="button" onclick="addJobLink('<?= $order['oid'] ?>','<?= $order['reference'] ?>')">
									<i class="far fa-plus-square"></i>
								</button>
							
        					</td>
        				</tr>
        				<?php endforeach ?>	
        			</tbody>
        		</table>
      		</div>
      		<div class="modal-footer">
        		<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      		</div>
    	</div>
  	</div>
</div>

<script>
	function showLinkEditor(id,ref){
		$("#id_orderid").val(id);
		$("#id_orderef").val(ref);
		$("#LinkJobsEditor").modal('show');
	}
	function removeJob(id){
		$(id).removeClass('d-flex').addClass('d-none');
		$(id+' input').each(function(){
			$(this).attr('name','');
		});
		$(id+'_remove').attr('name','remove[]');
	}
	function addJobLink(order,ref){
		var id=$("#id_orderid").val();
		var html='';
		html+='<li class="list-group-item d-flex" id="linkedjob_'+id+'_'+order+'">'+ref;
		html+='<input type="hidden" name="orders['+id+'][targetid]" value="'+order+'">';
		html+='<input type="hidden" name="orders['+id+'][value]" value="'+$("#id_orderef").val()+'">';
		html+='<button class="btn btn-sm btn-danger ml-auto" type="button" onclick="';
		html+="$('#linkedjob_"+id+"_"+order+"')";
		html+='.remove()">';
		html+='<i class="fas fa-trash"></i></button>';
		html+='</li>';
		$("#linked_"+id).append(html);
		$("#LinkJobsEditor").modal('hide');
	}
</script>
<?= $this->endSection() ?>