<?= $this->extend('Scheduler/board_index') ?>

<?= $this->section('boardbody') ?>
<?php $boardTiles=[]; ?>
<div class="col-12 p-0 m-0 scheduler_boards_container"  style="overflow-x: auto">
  <table class="table table-sm">
  	<thead class="table-dark">	
  		<tr>
    	<?php for($col=0;$col<$board['weekdays'];$col++) :?>
    		<th  style="width:120px;">
    			<?= lang(parseDayData($board['colcfg']['headers'][$col],$weekdays_names,$dateFrom,floor($col/$board['perdaycol']))) ?>
    		</th>	
    	<?php endfor ?>
    	</tr>
    </thead>
    <tbody>
    	<?php for($row=0;$row<$board['rowqty'];$row++) :?>
    		<tr>
    			<?php for($col=0;$col<$board['weekdays'];$col++) :?>
    				<?php $coltype=$board['colcfg']['cardstype'][$col]; ?>
    				<?php if(!is_array($coltype)) :?>
    					<?php $coltype=[$coltype] ?>
    				<?php endif ?>
    				<?php $empty=TRUE;?>
    					<?php foreach ($board['data'] as $flag=>$item) :?>    						
    						<?php foreach ($item as $cardkey=>$card) :?>
    							<?php if(count($coltype)>1 && array_key_exists($coltype[1], $card) && $flag.'.'.$card[$coltype[1]]==parseDayData($coltype[0],$weekdays_names,$dateFrom,floor($col/$board['perdaycol']))) :?>
    								<?php $empty=FALSE; ?>
    							<?php elseif(count($coltype)>1 && array_key_exists($coltype[1], $card) && $flag.'.'.$card[$coltype[1]]==$coltype[0]) :?>
    								<?php $empty=FALSE; ?>
    							<?php elseif ($flag==$coltype[0]) :?>
    								<?php $empty=FALSE; ?>
    							<?php else :?> 
    								<?php $empty=TRUE; ?>
    							<?php endif ?>	
    							
    							<?php if (!$empty && array_key_exists('id', $card) && !in_array($card['id'], $boardTiles)) :?>
    								<td>
    									<div class="info-box bg-light mb-1 cell p-0" style="min-height: <?= $board['cardheight'] ?>px!important;" data-type="empty" id="cell_<?= $col.'_'.$row ?>">
    									<?php if(is_array($cardstpls) && array_key_exists($card['cardtpl'], $cardstpls)) :?>
    										<?= $currentView->includeView('#'.$cardstpls[$card['cardtpl']],['data'=>$card,'board'=>$board,'movable_card'=>(array_key_exists('movable_cards', $board['colcfg']) && array_key_exists($col, $board['colcfg']['movable_cards'])) ? $board['colcfg']['movable_cards'][$col] : [] ]) ?>
										<?php else :?>
											<?= $currentView->includeView('Scheduler/cards/'.$card['cardtpl'],['data'=>$card,'board'=>$board]) ?>
										<?php endif ?>
										</div>	
    								</td>
    								<?php $boardTiles[]=$card['id']; goto endloop;?>
    							<?php else :?> 
    								<?php $empty=TRUE; ?>
    							<?php endif ?>	
    								
    						<?php endforeach ?>
    					<?php endforeach ?>
    		
    					<?php if($empty) :?>
    					<td>
    					<div class="info-box bg-light mb-1 cell p-0" style="min-height: <?= $board['cardheight'] ?>px!important;" data-type="empty" id="cell_<?= $col.'_'.$row ?>"></div>
    					</td>
    					<?php endif ?>
    					<?php endloop: ?>	
    			<?php endfor ?>
    		</tr>
    	<?php endfor ?>
    </tbody>
  </table>
</div>

<?= $this->endSection() ?>