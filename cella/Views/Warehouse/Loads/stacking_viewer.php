<div class="card border child-full">
	<div class="card-header">
		<div class="d-flex">
		<h4>Stacking Plan for <?= $data['reference'] ?></h4>
      </div>
  </div>
    <div class="card-body overflow-auto">
	<?= $currentView->includeView('Warehouse/Loads/stacking_table') ?>
	</div>
</div>

