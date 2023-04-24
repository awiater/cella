<?= $currentView->includeView('System/table') ?>
<?= $checkingform ?>
<script>
	$(function(){
		$('.dataTable tr').each(function() {
    		var date = $(this).find("td").eq(3).html();
    		if (date=='<?= $red_alert ?>'){
    			$(this).find("td").eq(3).html(date+'<i class="fas fa-exclamation-triangle text-danger ml-3"></i>');
    		}  
		});
	});
	$(".btnCheck").on('click',function(){
		showPredelCheckingWindow($(this).attr('data-urlid'));
	});
</script>
