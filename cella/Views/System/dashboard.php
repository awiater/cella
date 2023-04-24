<?php  if (!empty($editable) && $editable) :?>
<div class="row col-12 d-flex">
	<button type="button" class="btn btn-primary btn-sm ml-auto" id="id_dashboard_view_new_tile">
		<i class="fa fa-plus"></i>
	</button>
	<?php if (!empty($_delete_btn) && $_delete_btn==1) : ?>
	<button type="button" class="btn btn-danger btn-sm ml-1" id="tile_delete_btn">
		<i class="fa fa-trash"></i>
	</button>
	<?php endif ?>
</div>
<?php endif ?>
<?= !empty($_form_action_model) ? form_open($_form_action,['id'=>'id_dashboard_form'],['model'=>$_form_action_model]) : null ?>
<div class="row">
	<?php  if (!empty($_tiles) && is_array($_tiles) && count($_tiles) >0) :?>	
	<?php foreach($_tiles as $tile) :?>
		<div class="col-xs-12 col-md-3">
			<?php if ($tile['type']=='0') : ?>
			<div class="small-box bg-<?= $tile['background'] ?>" id="<?= $tile['name'] ?>">
				<div class="inner">
					<?= $currentView->includeView('Home/tile_admin',['editable'=>$editable,'tile'=>$tile,'right_align'=>1]); ?>
                	<h3><?= $tile['header'] ?></h3>
                	<p><?= $tile['text'] ?></p>
              	</div>
              	<div class="icon">
                	<i class="<?= $tile['icon'] ?>"></i>
              	</div>
              	<?php if($tile['url']!=null) : ?>
              	<a href="<?= $tile['url'] ?>" class="small-box-footer"><?= lang('system.general.more_info') ?> <i class="fas fa-arrow-circle-right"></i></a>
              	<?php endif ?>
              	
			</div>
			<?php elseif ($tile['type']=='1') : ?>
			<div class="info-box" id="<?= $tile['name'] ?>">
            	<span class="info-box-icon bg-<?= $tile['background'] ?>"><i class="<?= $tile['icon'] ?>"></i></span>
             	<div class="info-box-content">
             		<?= $currentView->includeView('Home/tile_admin',['editable'=>$editable,'tile'=>$tile]); ?>
              		<span class="info-box-text"><?= $tile['text'] ?></span>
                	<span class="info-box-number"><?= $tile['header'] ?></span>
              	</div>
            </div>	
			<?php elseif ($tile['type']=='2') : ?>
			<div class="card card-widget widget-user-2">
				<?= $currentView->includeView('Home/tile_admin',['editable'=>$editable,'tile'=>$tile]); ?>
              <!-- Add the bg color to the header using any of the bg-* classes -->
              <div class="<?= (!empty($editable) && $editable) ? 'p-2' : 'widget-user-header' ?> bg-<?= $tile['background'] ?> d-flex">
                <!--<div class="widget-user-image">
                  <div class="img-circle elevation-2 p-2">
                  	<i class="<?= $tile['icon'] ?> fa-3x"></i>aa
                  </div>
                </div>
                <!-- /.widget-user-image -->
                <div style="color:rgba(255,255,255,.65)">
                	<i class="<?= $tile['icon'] ?> fa-3x"></i>
                </div>
                
                <div>
                	<h3 class="widget-user-username"><?= $tile['header'] ?></h3>
                	<h5 class="widget-user-desc"><?= $tile['text'] ?></h5>
                </div>
              </div>
              <div class="card-footer p-0">
                <ul class="nav flex-column">
                	<?php $key_id=0; ?>
                <?php foreach($tile['options'] as $key=>$option) : ?>
                 	<li class="nav-item p-1">
                 		<?php if (is_array($option) && array_key_exists('url', $option) && array_key_exists('text', $option) && array_key_exists('color', $option) && array_key_exists('value', $option)) :?>
                    	<a href="<?= url($option['url']) ?>" class="nav-link">
                      	<?= lang($option['text']) ?><span class="float-right badge text-white" style="background-color: <?= $_dashboardview_colors[$key_id]['hex'] ?>!important;"><?= $option['value'] ?></span>
                      	<?php else : ?>
                      		<?= lang($key) ?><span class="float-right badge text-white" style="background-color: <?= $_dashboardview_colors[$key_id]['hex'] ?>!important;"><?= $option ?></span>
                      	<?php endif ?>
                    	</a>
                  	</li>
                  	<?php $key_id++; ?>
                <?php endforeach ?>
                </ul>
              </div>
            </div>
            <?php elseif ($tile['type']=='3' || $tile['type']=='4') : ?>
            <div class="card card-<?= $tile['background'] ?>">
            	<div class="card-header">
            		<h3 class="card-title"><?= $tile['text'] ?></h3>
					<div class="card-tools">
						<?= $currentView->includeView('Home/tile_admin',['editable'=>$editable,'tile'=>$tile]); ?>	
            		</div>
            	</div>
            	<div class="card-body p-1">
                	<div class="chart">
                		<canvas id="<?= $tile['name'] ?>" width="400" height="400"></canvas>
                	</div>
             	 </div>
            </div>
            <script>
            		Chart.register(ChartDataLabels);
					var ctx = document.getElementById('<?= $tile['name'] ?>').getContext('2d');
					var myChart = new Chart(ctx, {
						type: '<?= $tile['type']=='3' ? 'doughnut' : 'pie' ?>',
						options: {
							responsive: true,
							plugins: {
								 legend: {
								 	position: 'bottom',
								 	align: 'start',
								 },
								 datalabels: {
        							color: '#FFF',
        							font: {weight: 'bold',size:22}
      							}	
							}
						},
						data:{
							labels: 
							[
								<?php foreach (array_keys($tile['options']) as $key=>$value) :?>
									<?= "'".lang($value)."'," ?>
								<?php endforeach ?>	
							],
							datasets: 
							[{
								label: '#',
								data : 
								[
									<?= implode(',',array_values($tile['options'])) ?>
								],
								backgroundColor: 
								[
									<?php foreach (array_keys($tile['options']) as $key=>$value) :?>
										<?= "'".$_dashboardview_colors[$key]['hex']."'," ?>
									<?php endforeach ?>
									
								],
							}],
						},
						
					});
				</script>
			<?php elseif ($tile['type']=='5') : ?>
			<?php $admin=$currentView->includeView('Home/tile_admin',['editable'=>$editable,'tile'=>$tile]); ?>
			<div class="card p-1" style="height:<? strlen($admin)>0 ? '120' : '80' ?>px;">
				<?php if(strlen($admin)>1) :?>
				<div class="card-header">
					<?= $admin ?>
				</div>
				<?php endif ?>
				<div class="card-body p-2">
					<a class="w-100 h-100 btn text-white btn-lg btn-<?= $tile['background'] ?>" href="<?= $tile['url'] ?>">
						<i class="<?= $tile['icon'] ?> fa-2x mr-2"></i>
						<?= $tile['text'] ?>
					</a>
				</div>
			</div>
			<?php endif ?>
		</div>
	<?php endforeach ?>
	<?php endif ?>
</div>
<?= !empty($_form_action_model) ? form_close() : null ?>

<div class="modal fade" id="dashNewItemModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle"><?= lang('system.dashboard.new_item_title') ?></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
      	<?= form_open($_new_tile_url ,['id'=>'id_dashboard_tiles_form']) ?>
			<input type="hidden" name="board" value="home">
			<input type="hidden" name="name" value="">
			<input type="hidden" name="did" value="" id="id_did">
    		<div class="form-group" id="id_text_field">
    			<label for="id_text" class="mr-2"><?= lang('system.dashboard.item_text') ?></label>
   	 			<input type="text" name="text" value="" maxlength="50" id="id_text" class="form-control">
			</div>
 			<div class="form-group" id="id_back_field">
    			<label for="id_back" class="mr-2"><?= lang('system.dashboard.item_back') ?></label>
   	 			<select name="back" id="id_back" class="form-control">
   	 				<?php foreach(lang('system.dashboard.item_back_list') as $key=>$item) : ?>
					<option value="<?= $key ?>"><?= $item ?></option> 
					<?php endforeach ?>
				</select>
 			</div>
 			<div class="form-group" id="id_icon_field">
    			<label for="id_icon" class="mr-2"><?= lang('system.dashboard.item_icon') ?></label>
   	 			<div class="input-group mb-3">
   	 				<input type="text" name="icon" value="" maxlength="50" id="id_icon" class="form-control">
   	 				<div class="input-group-append">
   	 					<span class="input-group-text"><i class="" id="id_icon_preview"></i></span>
   	 				</div>
   	 			</div>
 			</div>
 			<div class="form-group" id="id_type_field">
    			<label for="id_type" class="mr-2"><?= lang('system.dashboard.item_type') ?></label>
   	 			<select name="type" id="id_type" class="form-control">
					<?php foreach(lang('system.dashboard.item_type_list') as $key=>$item) : ?>
					<option value="<?= $key ?>"><?= $item ?></option> 
					<?php endforeach ?>
				</select>
 			</div>
 		<div class="form-group" id="id_sql_field">
    		<label for="id_sql" class="mr-2"><?= lang('system.dashboard.item_sql') ?></label>
    		<?php if(!empty($_tiles_commands) && is_array($_tiles_commands) && count($_tiles_commands) > 0) :?>
    		<?= form_dropdown('',array_keys($_tiles_commands),[],['class'=>'form-control mb-2','id'=>'id_sql_commands']) ?>	
    		<?php endif ?>
   	 		<textarea name="sql" cols="40" rows="4" value="" id="id_sql" class="form-control"></textarea>
 		</div>
 		<div class="form-group" id="id_dorder_field">
    		<label for="id_sql" class="mr-2"><?= lang('system.dashboard.item_dorder') ?></label>
   	 		<input type="number" name="dorder" value="" max="100" min='0' id="id_dorder" class="form-control">
 		</div>
 		
		</form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal"><?= lang('system.buttons.cancel') ?></button>
        <button type="button" class="btn btn-primary" id="dashNewItemModal_save"><?= lang('system.dashboard.add_tile') ?></button>
      </div>
    </div>
  </div>
</div
<script>	
	
</script>

<script>
	<?php if (!empty($_form_action)) :?>
	$("#tile_delete_btn").on("click",function(){
		if (confirm("<?= lang('system.general.msg_delete_ques')?>")){
			$("#id_dashboard_form").attr('action','<?= $_form_action;?>').submit();
		}
	});
	<?php endif ?>
	
	$("#id_icon").on("change",function(){
		$("#id_icon_preview").attr('class',$(this).val());
	});
	
	$("#id_sql_commands").on('change',function(){
		var val=$("#id_sql_commands option:selected").text();
		$("#id_sql").text('#'+val);//Pallet/PalletModel::getQtyOfPalletsWithStatus@1,25,50,75
	});
	
	$("#id_dashboard_view_new_tile").on("click",function(){
		$("#id_text").val('');
		$("#id_back").val('');
		$("#id_icon").val('');
		$("#id_type").val('');
		$("#id_sql").val('');
		$("#id_did").val('');
		$("#id_dorder").val('');
		$("#dashNewItemModal").modal('show');
	});
	
	$("#dashNewItemModal_save").on("click",function(){
		$("#id_dashboard_tiles_form").submit();
		$("#id_text").val('');
		$("#id_back").val('');
		$("#id_icon").val('');
		$("#id_type").val('');
		$("#id_sql").val('');
		$("#id_did").val('');
		$("#id_dorder").val('');
	});
	
	
	function dashboardTileEdit(item)
	{
		item=JSON.parse(atob(item));
		$("#id_text").val(item.text);
		$("#id_back").val(item.background);
		$("#id_icon").val(item.icon);
		$("#id_type").val(item.type);
		$("#id_sql").text(item.sql);
		$("#id_did").val(item.id);
		$("#id_dorder").val(item.dorder);
		$("#dashNewItemModal").modal('show');
	}
	
</script>

<?= $currentView->includeView('Pallets/scan') ?>

