<div class="row">
    				<div class="col-12">
    					<div class="form-group" id="id_product_add_field">
    						<label for="id_product_add" class="mr-2">
    							<?= lang('system.pallets.pallet_products_list') ?>    			    		
    						</label>
    						<div class="input-group mb-3">
								<input list="id_prod_list" id="id_pallet_prod" class="form-control">
								<datalist id="id_prod_list">
									<?php foreach($products_list as $option) :?>
										<option value="<?= $option ?>"></option>
									<?php endforeach ?>
								</datalist>
								<div class="input-group-append">
									<button type="button" class="btn btn-primary" onclick="addProduct()">
										<i class="fa fa-plus"></i>
									</button>
								</div>
							</div>
    						<small id="id_pallet_prod_tooltip" class="form-text text-muted">
    							<?php $tooltip=lang('system.pallets.pallet_products_list'); ?>
    							<?= $tooltip!='system.pallets.pallet_products_list' ? $tooltip : null  ?>
    			    		</small>
 						</div>
    				</div>
    			</div>
    			<div class="row">
    				<table class="table table-striped">
    					 <thead class="thead-dark">
    					 	<tr>
    					 		<?php foreach($content_table->headers as $key=>$header) :?>
    					 			<?php if ($currentView->ismobile() && $key>0) : ?>
    					 				<th scope="col" class="d-none"><?= lang($header) ?></th>
    					 			<?php else : ?>
    					 				<th scope="col"><?= lang($header) ?></th>
    					 			<?php endif ?>
    					 		<?php endforeach ?>
    					 		<th scope="col" style="width:120px;"><?= lang('system.pallets.pallet_products_actions') ?></th>	
     						</tr>
    					 </thead>
    					 <tbody id="id_pallet_prod_list">
    					 	<?php foreach($content_table->list as $key=>$value) :?>
    					 		<tr id="id_pallet_prod_list_row_<?= $key ?>">
    					 			<?php if ($currentView->ismobile() && $key>0) : ?>
    					 				<td class="d-none"><?= $value->code ?></td>
    					 			<?php else : ?>
    					 				<td><?= $value->code ?></td>
    					 			<?php endif ?>
    					 			<td>
    					 				<input type="hidden" name="stock[<?= $key ?>][code]" value="<?= $value->code ?>">
    					 				<button type="button" class="btn btn-danger btn-sm" onclick="removeRow('<?= $key ?>')"><i class="fa fa-trash"></i></button>
    					 			</td>
    					 		</tr>
    					 	<?php endforeach ?>
    					 </tbody>
    				</table>
    			</div>
<script>
	function addProduct(val=null)
	{
		if (val==null){
			val=$("#id_pallet_prod").val();
		}
		var html=$("#id_pallet_prod_list").html();
		var id=html.length;
		html+='<tr id="id_pallet_prod_list_row_'+id+'">';
		html+='<td>'+val+'</td>'
		html+='<td><button type="button" class="btn btn-danger btn-sm" onClick="removeRow(';
		html+="'"+id+"'";
		html+=')"><i class="fa fa-trash"></i></button>';
		html+='<input type="hidden" name="stock['+id+'][code]" value="'+val+'">'
		html+='</tr>';
		$("#id_pallet_prod_list").html(html);
		$("#id_pallet_prod").val('');
	}
	
	function removeRow(id){
		$("#id_pallet_prod_list_row_"+id).remove();	
	}
</script>