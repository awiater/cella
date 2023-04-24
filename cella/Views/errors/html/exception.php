<div class="alert alert-<?= $type ?> <?php if (strlen($msg) < 1) : ?> d-none <?php endif ?>" role="alert">
 	<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
  <?= $msg ?>
</div>