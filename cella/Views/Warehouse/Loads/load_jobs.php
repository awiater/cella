<?php $columns=['tick','reference','ALL','NEU','EU','actions']; $id=!empty($pid) ? $pid : 'pid'; $tableID=!empty($tableID) ? $tableID : 'loadItemsTable' ?>

<div class="row">
	<table class="table table-striped<?= !empty($table_class) ? ' '.$table_class : '' ?> tableFixHead bg-light" id="<?= $tableID ?>">
    	<thead class="thead-dark">
    		<th scope="col"></th>
    		<th scope="col"><?= lang('system.warehouse.collections_items_reference')?></th>
    		<th scope="col"><?= lang('system.warehouse.collections_items_iseu')?></th>
    		<!--<th scope="col"><?= lang('system.warehouse.collections_items_location')?></th>-->
    		<th scope="col"><?= lang('system.warehouse.collections_items_ALL')?></th>
    		<th scope="col"><?= lang('system.warehouse.collections_items_NEU')?></th>
    		<th scope="col"><?= lang('system.warehouse.collections_items_EU')?></th>
    		<th scope="col" class="sum"><?= lang('QTY of Stacks')?></th>
    		<?php if(!$readonly) :?>
    			<?php if ($load==null) :?>
    			<th scope="col" style="width:120px;"></th>
    			<?php else :?>
    			<th scope="col"></th>
    			<?php endif ?>
    		<?php endif ?>
    	</thead>
    	<tbody <?= !empty($tbody_id) ? 'id="'.$tbody_id.'"' : null ?>>
    		<?php if (!empty($items) && is_array($items) && count($items)>0) : ?>
    			<?php $max=0; ?>
    			<?php foreach($items as $key=>$pallet) :?>
    				<?php if ($pallet['load']==$load) :?>	
    					<tr id="pallets_table_row_<?= $id.'_'.$key ?>" data-job="<?= $tableID=='loadItemsTable' ? 'old' : 'new'?>" class="border">
    						<td>
    						<?php if ($pallet['status']==0 && $tableID=='loadItemsTableAvaliable') :?>
    						<input type="checkbox" name="<?= $id ?>[]" value="<?= $pallet['reference'] ?>" data-id="<?= $id.'_'.$key ?>" data-stackqty="<?= $pallet['maxstack'] ?>">
    						<?php endif ?>
    						</td>
    						<td><?= $pallet['reference'] ?></td>
    						<td><?= $pallet['iseu'] ? lang('system.general.yes') : lang('system.general.no') ?></td>
    						<!--<td><?= $pallet['location'] ?></td>-->
    						<td><?= $pallet['pallet_qty'] ?></td>
    						<td><?= $pallet['noneur_qty'] ?></td>
    						<td><?= $pallet['eur_qty'] ?></td>
    						<td class="maxstack"><?= $pallet['maxstack'] ?></td>
    						<?php $max=$pallet['maxstack']+$max; ?>
    						<?php if(!$readonly) :?>
    						<td>
    							<a href="<?= url('Warehouse','stackingplan',[$pallet['reference']],['refurl'=>current_url(FALSE,TRUE)])?>" class="mr-1 btn btn-sm btn-secondary" id="id_tableview_btn_label_<?= $key;?>" data-toggle="tooltip" data-placement="left" title="" data-original-title="<?= lang('system.warehouse.collections_stackbtn') ?>" target="_blank">
    								<i class="fab fa-unsplash"></i>
    							</a>
    							<?php if (!empty($tableID) && $tableID=='loadItemsTable') :?>
    								<?php if($pallet['status']==0 || $pallet['status']=='0') :?>
    								<button type="button" class="btn btn-danger btn-sm" onclick="removePallet('<?= $id.'_'.$key ?>')" data-toggle="tooltip" data-placement="right" title="<?= lang('system.warehouse.collections_remordbtn')?>">
            							<i class="fa fa-trash"></i>
            						</button>
            						<input type="hidden" name="pallets[]" value="<?= $pallet['reference'] ?>" class="palletref">
            						<?php else :?>
            							<?php if(array_key_exists('loaded', $pallet) && $pallet['loaded'] < 1) :?>
            							<button type="button" class="btn btn-danger btn-sm" onclick="removePallet('<?= $id.'_'.$key ?>',true)" data-toggle="tooltip" data-placement="right" title="<?= lang('system.warehouse.collections_remordbtn')?>">
            								<i class="fa fa-trash"></i>
            							</button>
            							<?php endif ?>
            						<?php endif ?>
    							<?php endif ?>	
    						</td>
    						<?php endif ?>
    					</tr>
    				<?php endif ?>
    			<?php endforeach ?>
    			<?php if($tableID=='loadItemsTable') :?>
    			<tr id="sumRow" class="bg-secondary">
    				<td></td>
    				<td colspan="5">Total</td>
    				<td id="maxStacks" ><?= $max ?></td>
    				<?php if(!$readonly) :?>
    				<td></td>
    				<?php endif ?>
    			</tr>
    			<?php endif ?>
    		<?php endif ?>
    	</tbody>
    </table>
</div>
