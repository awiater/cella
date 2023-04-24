<?= $_tableview_table ?>
<?= !empty($_tableview_pagination) ? $_tableview_pagination : null?>
<script>
	<?= $currentView->includeView('System/table_datatable') ?>
</script>