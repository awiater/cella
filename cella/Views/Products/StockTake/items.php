<?php $columns=!empty($columns) && is_array($columns) && count($columns) >0 ? $columns : ['location','operator','status','pallet','new_pallet'] ?>
<?php $items=!empty($items) && is_array($items) && count($items) >0 ? $items : [] ?>
<table class="table" id="stockTakeItemsTable">
  <thead class="thead-dark">
  	<tr>
  		<th scope="col">
  			<!--<input type="checkbox" onclick="$('input[name*=\'siid\']').prop('checked', this.checked);">-->
  			<button type="button" class="btn btn-primary btn-sm mr-2  d-none" onclick='assignOperator()' id="assignOperatorBtn">
  				<i class="fas fa-user-plus"></i>
  			</button>
  		</th>
  		<?php foreach($columns as $column) :?>
  		<th scope="col"><?= lang('system.products.stocktake_items_'.$column) ?></th>
  		<?php endforeach ?>
  	</tr>
  </thead>
  <tbody>
  	<?php foreach($items as $row) :?>
  		<tr class="bg-<?= $row['match']== -1 ? 'light' : ($row['match']==1 || $row['match']=='1' ? 'lightgreen' : 'lightpink') ?>">
  			<td>
  				<!--<input type="checkbox" name="siid[]" value="<?= $row['siid'] ?>">-->
  			</td>
  			<?php foreach($columns as $col) :?>
  				<?php if (array_key_exists($col, $row)) :?>
  					<?php if ($col=='pallet' || $col=='new_pallet') :?>
  						<td>
  							<?php if ($col=='new_pallet') :?>
  								<?php if ($row['match']==1 || $row['match']=='1') :?>
  									<i class="far fa-check-circle fa-lg text-green"></i>
  								<?php elseif ($row['match']==0 || $row['match']=='0') :?>
  									<i class="fas fa-ban fa-lg text-danger mr-2"></i><a href="<?= str_replace('-id-', $row[$col], $pallet_url) ?>"><?= $row[$col] ?></a>
  								<?php elseif ($row['match']==2 || $row['match']=='2') :?>
  									<i class="fas fa-ban fa-lg text-danger"></i>
  									<i class="fas fa-exclamation-triangle mr-2 text-danger"></i><?= $row[$col] ?>
  								<?php endif ?>
  							<?php else :?>
  								<?= $row[$col] ?>
  							<?php endif ?>
  						</td>
  					<?php elseif ($col=='operator') :?>
  					<td>
  						<?php if(substr($row[$col],1)!='@') :?>
  							<?= $row[$col]; ?>
  						<?php endif ?>	
  					</td>
  					<?php elseif ($col=='pallet') :?>
  						<td>1</td>
  					<?php else : ?>
  					<td><?=  $col=='status' ? lang('system.products.stocktake_status_list')[$row[$col]] : $row[$col] ?></td>
  					<?php endif ?>
  				<?php else :?>
  				<td>b</td>
  				<?php endif ?>
  			<?php endforeach ?>	
  		</tr>
  	<?php endforeach ?>	
  </tbody>
</table>
<div class="modal" tabindex="-1" role="dialog" id="assignOperatorModal">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><?= lang('system.products.stocktake_assignuser_title') ?></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
      	<h6><?= lang('system.products.stocktake_assignuser_msg') ?></h6>
      	<?= form_dropdown('',$operators,[],['class'=>'form-control']) ?>
      </div>
      <div class="modal-footer">
        <button type="submit" form="checkingform" class="btn btn-primary"><?= lang('system.buttons.confirm') ?></button>
        <button type="button" class="btn btn-secondary" data-dismiss="modal"><?= lang('system.buttons.cancel') ?></button>
      </div>
    </div>
  </div>
</div>
<script>
	$(function(){
		$("#stockTakeItemsTable_length").prepend($("#assignOperatorBtn"));
	});
	function assignOperator(){
		if ($('input[name*=\'siid\']:checked').length<1){
			Dialog('<?= lang('system.products.stocktake_assignuser_noseltasks') ?>','warning');exit;
		}
		$('#assignOperatorModal').modal('show');
	}
</script>