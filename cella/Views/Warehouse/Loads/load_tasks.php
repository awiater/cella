<table class="table table-striped" id="loadTasksTable">
	<thead class="thead-dark">
    	<tr>
    		<th><?= lang('system.warehouse.collection_pallref') ?></td>
    		<th><?= lang('system.warehouse.collection_taskslocation') ?></th>
    		<th><?= lang('system.warehouse.collection_taskoperator') ?></th>
    		<th><?= lang('system.warehouse.collection_taskassign') ?></th>
    		<th><?= lang('system.warehouse.collection_taskcomp') ?></th>
    		<th><?= lang('system.warehouse.collection_taskstatus') ?></th>
    		<?php if (!empty($readonly) && !$readonly) : ?>
    		<th></th>
    		<?php endif ?>
    	</tr>
    </thead>
    <tbody>
    	<?php if (!empty($load_items) && is_array($load_items)) :?>
    	<?php foreach($load_items as $row) :?>
    		<tr>
    			<td><?= $row['pallref'] ?></td>
    			<td><?= $row['location'] ?></td>
    			<td><?= $row['operator'] ?></td>
    			<td><?= formatDate($row['assign'],FALSE,'d M Y H:i') ?></td>
    			<td><?= formatDate($row['completed'],FALSE,'d M Y H:i') ?></td>
    			<td><?= array_key_exists($row['status'], $load_items_status) ? $load_items_status[$row['status']] : $load_items_status[0]?></td>
    			<?php if (!empty($readonly) && !$readonly) : ?>
    			<td>
    				<a href="<?=url('Pallets','pallet',[$row['pallref']],['refurl'=>base64url_encode(current_url(FALSE,FALSE).'&tab=1')])?>" class="btn btn-primary btn-sm">
    					<i class="fa fa-edit" data-toggle="tooltip" data-placement="left" title="<?= lang('system.warehouse.collection_palledittool') ?>"></i>
    				</a>
    				<?php if ($row['status']==0) : ?>
    				<a href="<?=url('Warehouse','delete',[],['model'=>'items','id'=>$row['iid'],'refurl'=>base64url_encode(current_url(FALSE,FALSE).'&tab=1')])?>" class="btn btn-danger btn-sm">
    					<i class="fa fa-trash" data-toggle="tooltip" data-placement="left" title="<?= lang('system.warehouse.collection_pallremo') ?>"></i>
    				</a>
    				<?php endif ?>
    			</td>
    			<?php endif ?>
    		</tr>
    	<?php endforeach ?>
    	<?php endif ?>
    </tbody>
</table>