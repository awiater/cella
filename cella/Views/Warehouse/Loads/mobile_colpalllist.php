<table class="table">
	<thead>
		<tr>
			<th scope="col"><?= lang('system.warehouse.collections_pallref') ?></th>
			<th scope="col"><?= lang('system.pallets.pallet_customer') ?></th>
			<th scope="col"><?= lang('system.warehouse.stackwizard_stack') ?></th>
			<th scope="col"><?= lang('system.warehouse.stackwizard_lmh') ?></th>
			<th scope="col"><?= lang('system.warehouse.collections_location') ?></th>
			<th scope="col" wtyle="width:50%"></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach($data as $key=>$row) :?>
		<tr>
			<td><?= $row['pallref'] ?></td>
			<td><?= $row['customer'] ?></td>
			<td><?= $row['stacking'] ?></td>			
			<td><?= $row['lmh'] ?></td>
			<td><?= $row['location'] ?></td>
			<td>
				<a class="btn btn-primary btn-lg w-100" href="<?= url('Warehouse','collection',[$row['loadref']],['palref'=>$row['pallref']])?>">MOVE</a>
			</td>
		</tr>
		<?php endforeach ?>
	</tbody>
</table>