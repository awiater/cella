
<div class="card<?=!empty($viewmode) ? ' '.$viewmode : '' ?>" id="Scheduler_board_container">
	<div class="card-header">
		<div class="row">
			<div class="col-2 d-flex">
			<button type="button" class="btn btn-dark mr-2" onclick="toggleFullscreen('Scheduler_board_container');">
				<i class="fas fa-expand"></i>
			</button>
			<?= form_dropdown('boardnames',$views,$board['sbid'],['id'=>'id_boardsnames','class'=>'form-control']) ?>
			</div>
			<div class="col-10 <?= $board['usedate']==1 ? 'd-flex' : 'd-none' ?>">
				<a href="<?= $urlprev ?>" class="btn btn-dark ml-5" data-toggle="tooltip" data-placement="left" title="<?= lang('scheduler.boards.prevbtn_tooltip') ?>">
					<i class="fas fa-chevron-circle-left"></i>
				</a>
				<a href="<?= $urlnow ?>" class="btn btn-dark ml-2" data-toggle="tooltip" data-placement="bottom" title="<?= lang('scheduler.boards.todaybtn_tooltip') ?>">
					<i class="fas fa-dot-circle"></i>
				</a>
				<a href="<?= $urlnxt ?>" class="btn btn-dark ml-2" data-toggle="tooltip" data-placement="right" title="<?= lang('scheduler.boards.nexttn_tooltip') ?>">
					<i class="fas fa-chevron-circle-right"></i>
				</a>
				<h4 class="ml-5">
					<?php if (is_array($board['view'])) :?>
						<?= str_replace(['$dateFrom','$dateTo'], 
						[
							convertDate($dateFrom,'DB',$board['view']['format']),
							convertDate($dateTo,'DB',$board['view']['format'])
						]
						, $board['view']['label']) ?>
						
					<?php endif ?>		
					
				</h4>
				<div class="p-0 ml-auto">
					<?php if (array_key_exists('buttons', $board['colcfg']) && is_array($board['colcfg']['buttons'])) :?>
						<?php if (in_array('new', $board['colcfg']['buttons'])) :?>
						<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#new_card_modal">
							<i class="fas fa-plus-square" data-toggle="tooltip" data-placement="right" title="<?= lang('scheduler.boards.newcardbtn_tooltip') ?>"></i>
						</button>
						<?php endif ?>
					<?php endif ?>
				</div>
			</div>	
		</div>
	</div>
  	<div class="card-body p-2">
    	<?= $this->renderSection('boardbody') ?>
  	</div>
</div>
<div class="d-none overlay_container" id="id_card_overlay">
	<div class="overlay"></div>
	 <div class="overlay_i">
	 	<button type="button" class="btn btn-link editButton col-3" data-id="#cardid#">
	 		<i class="fas fa-pen-square fa-lg"></i>
	 	</button>
	 	<button type="button" class="btn btn-link tooltipButton col-3" data-id="#cardid#">
	 		<i class="fas fa-info-circle fa-lg infobtn"></i>
	 	</button>
	 </div>
</div>


<div class="modal" tabindex="-1" role="dialog" id="new_card_modal">
  		<div class="modal-dialog" role="document">
    		<div class="modal-content">
      			<div class="modal-header">
        			<h5 class="modal-title"><?= lang('scheduler.boards.new_card_modal_title') ?></h5>
        			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
          				<span aria-hidden="true">&times;</span>
        			</button>
      			</div>
      			<div class="modal-body">
        			<ul class="nav flex-column">
        				<li class="nav-item">
        					<a class="nav-link text-dark" href="<?= url('scheduler','predelivery',['new'],['refurl'=>current_url(FALSE,TRUE)]) ?>">
        						<div class="info-box">
              						<span class="info-box-icon" style="background-color: #FF8C00;">
              							<i class="fas fa-dolly-flatbed"></i>
              						</span>
									<div class="info-box-content">
                						<span class="info-box-number"><?= lang('scheduler.predel.card_title') ?></span>
                						<span class="info-box-text"><?= lang('scheduler.predel.card_tooltip') ?></span>
              						</div>
            					</div>
        					</a>
        				</li>
        				<li class="nav-item">
        					<a class="nav-link text-dark" href="<?= url('warehouse','collection',['new'],['refurl'=>current_url(FALSE,TRUE)]) ?>">
        						<div class="info-box">
              						<span class="info-box-icon text-white" style="background-color: grey;">
              							<i class="fas fa-truck-moving"></i>
              						</span>
									<div class="info-box-content">
                						<span class="info-box-number"><?= lang('scheduler.collections.card_title') ?></span>
                						<span class="info-box-text"><?= lang('scheduler.collections.card_tooltip') ?></span>
              						</div>
            					</div>
        					</a>
        				</li>
        			</ul>
      			</div>
      			<div class="modal-footer"></div>
    		</div>
  		</div>
	</div>
	
<div class="modal" tabindex="-1" role="dialog" id="single_pririoty_change_modal">
	<div class="modal-dialog" role="document">
    	<div class="modal-content">
      		<div class="modal-header">
        		<h5 class="modal-title"><?= lang('scheduler.cards.priority_modal_title')?></h5>
        		<button type="button" class="close" data-dismiss="modal" aria-label="Close">
          			<span aria-hidden="true">&times;</span>
        		</button>
      		</div>
      		<div class="modal-body">
      			<?= form_open_multipart($changeorderurl,['id'=>'single_pririoty_change_modal_form']); ?>
      	  		<div class="form-group" id="id_reference_field">
    				<label for="single_pririoty_change_modal_ref" class="mr-2">
    					<?= lang('scheduler.cards.priority_modal_jobcode')?>
    				</label>
   	 				<input type="text" name="reference" required="true" maxlength="120" id="single_pririoty_change_modal_ref" class="form-control bg-white" readonly>
   	 				<input type="hidden" id="single_pririoty_change_modal_id"  name="id">
   	 				<input type="hidden" id="single_pririoty_change_modal_refurl_ok"  name="refurl_ok" value="<?= current_url(FALSE,FALSE) ?>">
 				</div>
 				<div class="form-group" id="id_priority_field">
    				<label for="single_pririoty_change_modal_order" class="mr-2">
    					<?= lang('scheduler.cards.priority_modal_priority')?>
    				</label>
   	 				<?= form_dropdown('value',lang('scheduler.cards.priority_modal_priority_list'),null,['id'=>'single_pririoty_change_modal_order','class'=>'form-control']) ?>
 				</div>
 				</form>
      		</div>
      		<div class="modal-footer">
        		<button type="submit" form="single_pririoty_change_modal_form" class="btn btn-primary"><?= lang('scheduler.cards.priority_modal_okbtn')?></button>
        		<button type="button" class="btn btn-secondary" data-dismiss="modal"><?= lang('scheduler.cards.priority_modal_cncbtn')?></button>
      		</div>
    	</div>
  	</div>
</div>

<script>
function single_pririoty_change_modal(ref,id){
	$("#single_pririoty_change_modal_ref").val(ref);
	$("#single_pririoty_change_modal_id").val(id);
	$("#single_pririoty_change_modal").modal('show');	
}

$(function(){
	<?php if (!empty($settings) && is_array($settings) && array_key_exists('autorefresh',$settings) && $settings['autorefresh'] > 0) :?>
	setInterval(function() {
    	window.location.reload(true);
  	}, <?= $settings['autorefresh'] ?>*1000);
  	<?php endif ?>
  	
	$(".card").on('dblclick',function(){
		var id=$(this).attr('id');
		id=id+'ToogleEdit';
		id=window[id];
		id();
	});
	
	$(".infobtn").attr('style','cursor:help!important');

	$('.card').each(function(){
		$(this).css('\-index',3000);
		$(this).css('width','100%');
		if ($(this).attr('data-editable')){
			var id=$(this).attr('id');
			$('#'+id).tooltip({
					placement : 'left',
					title : 'aa',
					trigger : 'manual',
					html : true
			});
			var div=null;
			if ($(this).attr('data-infopanel')==undefined || $(this).attr('data-infopanel')=='true')
			{
				div=$('#id_card_overlay').clone();
				div.attr('id',id+'_overlay');
			}else
			if($(this).attr('data-infopanel')!=undefined){
				div=$(this).find($(this).attr('data-infopanel'));
			}
			
			if (div!=null){
				div.find(".tooltipButton").attr('data-id',id);
				div.find(".editButton").attr('data-id',id);
				if(div.find(".editButton").attr('onclick')==undefined)
				{
					div.find(".editButton").attr('onClick',id+'ToogleEdit()');
				}
				
				div.find(".editButton").on('mouseover',function(){
					$('#'+id).attr('data-original-title', '<?= lang('scheduler.boards.editcardbtn') ?>')
						 .tooltip('update')
						 .tooltip('show');
				}); 
				div.find(".editButton").on('mouseout',function(){
					$('#'+id).tooltip('hide');
				});
				
				div.find(".Button").on('mouseover',function(){
					$('#'+id).attr('data-original-title',div.find(".Button").attr('tooltip'))
						 .tooltip('update')
						 .tooltip('show');
				}); 
				div.find(".Button").on('mouseout',function(){
					$('#'+id).tooltip('hide');
				});
			
				div.find(".tooltipButton").on('mouseover',function(){
					$('#'+id).attr('data-original-title', $('#'+id+'_tooltip').html())
						 .tooltip('update')
						 .tooltip('show');
				}); 
				
				div.find(".tooltipButton").on('mouseout',function(){
					$('#'+id).tooltip('hide');
				});
				$(this).append(div);
			}
			
		}		
	});
	
	$(".card").on('mouseover',function(){
		if ($(this).attr('data-editable')){
			var id=$(this).attr('id');
			$("#"+id+'_overlay').removeClass('d-none');
		}	
	});
	
	$(".card").on('mouseout',function(){
		if ($(this).attr('data-editable')){
			var id=$(this).attr('id');
			$("#"+id+'_overlay').addClass('d-none');
		}	
	});
	
	$(".tooltipButton").on('mouseover',function(){
		$("#"+$(this).attr('data-id')).tooltip('show');
	});
	
	$(".tooltipButton").on('mouseout',function(){
		$("#"+$(this).attr('data-id')).tooltip('hide');
	});
	
	$("#id_boardsnames").on('change',function(){
		var url='<?= $urlboard ?>';
		url=url.replace('-board-',$("#id_boardsnames option:selected").val());
		if ($("#Scheduler_board_container").hasClass('fullscreen')){
			url=url+'?viewmode=fullscreen';
		}
		window.location=url;
	});
});
</script>