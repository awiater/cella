<?php $columns=!empty($columns) && is_array($columns) ? $columns : ['tick','reference','size','stack','height','corder']; $id=!empty($pid) ? $pid : 'pid'; ?>
<div class="row">
	<table class="table table-striped<?= !empty($table_class) ? ' '.$table_class : '' ?>">
    	<thead class="thead-dark">
    		<?php if (in_array('tick', $columns)) :?>
    		<th scope="col"></th>
    		<?php endif ?>
    		<?php foreach($columns as $column) :?>
    			<?php if ($column!='actions' && $column!='tick') :?>
    			<th scope="col"><?= lang('system.pallets.pallet_'.$column)?></th>
    			<?php endif ?>	
    		<?php endforeach ?>
    		<?php if (in_array('actions', $columns)) :?>
    		<th scope="col" style="width:120px;"><?= lang('system.pallets.pallet_products_actions') ?></th>
    		<?php endif ?>
    	</thead>
    	<tbody <?= !empty($tbody_id) ? 'id="'.$tbody_id.'"' : null ?>>
    		<?php if (!empty($pallets) && is_array($pallets) && count($pallets)>0) : ?>
    			<?php foreach($pallets as $key=>$pallet) :?>
    			<tr id="pallets_table_row_<?= $id.'_'.$key ?>">
    					<td>
    						<?php if (!array_key_exists('loadstatus', $pallet) || (array_key_exists('loadstatus', $pallet)&& $pallet['loadstatus']==0)) :?>
    						<input type="checkbox" name="<?= $id ?>[]" value="<?= $pallet['pid'] ?>" data-id="<?= $id.'_'.$key ?>">
    						<?php endif ?>
    					</td>
    				
    				<?php foreach($columns as $column) :?>
    					<?php if ($column=='loadstatus') :?>
    						<td><?= lang('system.warehouse.collections_status_list')[$pallet[$column]]; ?></td>
    					<?php elseif ($column!='status' && $column!='actions' && $column!='tick') :?>	
    						<td><?= $pallet[$column] ?></td>
    					<?php endif ?>
    				<?php endforeach ?>
    				<?php if (in_array('status', $columns)) :?>
    					<td><?= array_key_exists($pallet['status'], $pallets_types) ? $pallets_types[$pallet['status']] : null ?></td>
    				<?php endif ?>
    				<?php if (in_array('actions', $columns)) :?>
    				<td>
    					<a href="<?= url('Pallets','label',[$pallet['pid']],['refurl'=>current_url(FALSE,TRUE)])?>" class="mr-1 btn btn-sm btn-secondary" id="id_tableview_btn_label_<?= $key;?>" data-toggle="tooltip" data-placement="left" title="" data-original-title="<?= lang('system.pallets.index_lblbtn') ?>" target="_blank">
    						<i class="fas fa-barcode"></i>
    					</a>	
    					<a href="<?= url('Pallets','pallet',[$pallet['pid']],['refurl'=>current_url(FALSE,TRUE)])?>" class="mr-1 btn btn-sm btn-primary" id="id_tableview_btn_edit_<?= $key;?>" data-toggle="tooltip" data-placement="left" title="" data-original-title="<?= lang('system.pallets.index_editbtn') ?>">
    						<i class="fa fa-edit"></i>
    					</a>
    				</td>
    				<?php endif ?>
    			</tr>
    			<?php endforeach ?>
    		<?php endif ?>
    	</tbody>
    </table>
</div>
