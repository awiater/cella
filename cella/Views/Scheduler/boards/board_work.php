<?= $currentView->includeView('Scheduler/boards/board_table') ?>
<script>
	$(function(){
	
		
		$( "div[data-type='movablecard']").each(function(){
			
			$(this).addClass('movablecard');
			var overlay_i=$(this).find('.overlay_i');
			overlay_i.append('<div class="btn btn-sm text-white movablecard_handle col-3"><i class="fas fa-arrows-alt"></i></div>');
		});
		
		$( "div[data-type='empty']").each(function(){
			var id=$(this).attr('id');
			if (id!=undefined){
				id=id.substring(7,5);
				if (id=='1_' || id=='0_' || id=='2_' || id=='3_' || id=='4_'){
					$(this).addClass('droppable')
					$(this).droppable({
     					accept:'.movablecard',
     					drop: function( event, ui ) {
     						ui.draggable.css('top', 0);
     						ui.draggable.css('left', 0);
        					$(this).append(ui.draggable);
        					id=id.replace('_','');
        					var loc=JSON.parse(atob('<?= base64_encode(json_encode($board['colcfg']['movable_cards']))?>'));
        					changelocation(ui.draggable.find('b').text(),loc[id]);
      					}
    				});
				}
			}
		});
		
		$( "div[data-type='movablecard']").draggable({ 
			revert: true,
			handle: ".movablecard_handle",
			drag: function() {
				$(this).css('z-index',9000);
				$("div[data-type='empty']").css('opacity','0.3');//removeClass('bg-light').addClass('bg-dark');
				$('.droppable').css('opacity','2');
			},
			stop:function(){
				$("div[data-type='empty']").css('opacity','1');//.removeClass('bg-dark').addClass('bg-light');
			}
			 
		});
    	$( "div[data-linked='true']").each(function(){
    		var linked=$(this).attr('data-link');
    		if (linked.search(',')<0){
    			linked+=',';
    		}
    		var d1=$(this);
    		var linkbtn=$('<div class="btn btn-sm text-white linkbtn w-100"><i class="fas fa-link"></i></div>');
    		$(this).find('.overlay_i').append(linkbtn);
    		
    		$(this).find('.linkbtn').on('mouseover',function(){
    			$("div[data-type='empty']").css('opacity', '0.3');
    			$.each(linked.split(','), function( index, value ){
    				$("div[data-ref='"+value+"']").parent().css('opacity', '1');
    			});			
    		});
    		$(this).find('.linkbtn').on('mouseout',function(){
    			$("div[data-type='empty']").css('opacity', '1');
    		});
    		
    		
    	});
	});
	function changelocation(job,location)
	{
		var geturl='<?= url('scheduler','changejoblocation',['-job-','-loc-'],['refurl'=>current_url(FALSE,TRUE)]); ?>';
        geturl=geturl.replace('-job-',job);
        geturl=geturl.replace('-loc-',location);
		$.ajax({
            type: 'GET',
            dataType: 'json',
            url: geturl,
            data:{ },
            success: function(data) {},
            error: function(data) {}
        }); 
	}
</script>
