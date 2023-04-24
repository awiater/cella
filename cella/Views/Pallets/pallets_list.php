<?php $columns=!empty($columns) && is_array($columns) ? $columns : ['reference','location','size','stack','height','status','actions'] ?>
<div class="row">
	<table class="table table-striped<?= !empty($table_class) ? ' '.$table_class : '' ?>" id='pallets_list_table'>
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
    			<tr id="pallets_table_row_<?= $key ?>" <?= $pallet['status']==0 ? 'class="text-danger"' : '' ?>>
    				<?php if (in_array('tick', $columns)) :?>
    					<td>
    						<input type="checkbox" name="pid[]" value="<?= $pallet['pid'] ?>" data-id="<?= $key ?>">
    					</td>
    				<?php endif ?>
    				<?php foreach($columns as $column) :?>
    					<?php if ($column!='status' && $column!='actions' && $column!='tick') :?>
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
    					<button type="button" data-id='<?= $pallet['pid'] ?>' class="mr-1 btn btn-sm btn-danger tableview_btn_del" id="id_tableview_btn_del_<?= $key;?>" data-toggle="tooltip" data-placement="bottom" title="" data-original-title="<?= lang('system.pallets.index_delbtn') ?>">
    						<i class="fa fa-trash"></i>
    					</button>
    				</td>
    				<?php endif ?>
    			</tr>
    			<?php endforeach ?>
    		<?php endif ?>
    	</tbody>
    </table>
</div>
<script>
	$(".tableview_btn_del").on('click',function(){
		var url='<?= url('Pallets','deletepallet',['-id-'],['refurl'=>current_url(FALSE,TRUE)])?>';
		url=url.replace('-id-',$(this).attr('data-id'));
		
		ConfirmDialog('<?= lang('system.general.msg_delete_ques')?>',function(){
					window.location=url;
				});
	});
</script>
