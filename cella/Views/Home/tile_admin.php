<?php if(array_key_exists('id', $tile) && (!empty($editable) && $editable) ) : ?>
<div class="row">
	<div class="col-12 d-flex">
		<div class="dropdown <?= !empty($right_align) ? '' : 'ml-auto'?>">
			<button class="btn btn-secondary btn-sm dropdown-toggle" type="button" id="dropTileMenuBtn_<?= $tile['id'] ?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
   				 <i class="fas fa-cog"></i>
  			</button>
			<div class="dropdown-menu" aria-labelledby="dropTileMenuBtn_<?= $tile['id'] ?>">
				<a class="dropdown-item" href="<?= str_replace('-id-', $tile['id'], $_delete_tile_url)?>">
					<i class="fa fa-trash mr-1"></i><?= lang('system.buttons.remove') ?>
				</a>
				<button type="button" class="dropdown-item" onClick="dashboardTileEdit('<?= base64_encode(json_encode($tile))?>')">
					<i class="fas fa-edit mr-1"></i><?= lang('system.buttons.edit') ?>
				</button>
			</div>
			<!--
				<button type="button" class="btn btn-sm btn-primary" onClick="dashboardTileEdit('<?= base64_encode(json_encode($tile))?>')"><i class="fas fa-edit"></i></button>
			-->
		</div>	
	</div>
</div>

<?php endif ?>	