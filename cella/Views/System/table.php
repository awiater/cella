<div class="card border child-full">
	<div class="card-header">
		<div class="d-flex">
		<?php if (!empty($_tableview_card_title) && strlen($_tableview_card_title) > 0 ) :?>
		<h4><?= $_tableview_card_title ?></h4>
		<?php endif ?>	
    	<?php if (!empty($_tableview_btns) && is_array($_tableview_btns) && count($_tableview_btns)>0) : ?>
    		<div class="ml-auto">
        	<?php foreach ($_tableview_btns as $button) : ?>
        	<?= $button; ?>
        	<?php endforeach;?>
        	</div>
       <?php endif ?>
      </div>
  </div>
    <div class="card-body overflow-auto">
    	<?php if (!$_tableview_custom) : ?>
    		<?php if(!empty($_tableview_filters)) : ?>
    			<?= form_open(!empty($_tableview_filters_url) ? $_tableview_filters_url : '',['id'=>'id_tableview_search_form']) ?>
    				<div class="form-row mb-3">
    					<div class="col">
    						<?php if (!empty($_tableview_datatable)) :?>
    						<input type="text" class="form-control" placeholder="Filter" id="id_tableview_search_form_filter_value" value="<?= !empty($_tableview_datatable_filter) ? base64_decode($_tableview_datatable_filter) : ''; ?>">
      						<input type="hidden" id="id_tableview_search_form_filter" name="filter">
      						<?php else : ?>
      						<input type="text" class="form-control" placeholder="Filter" id="id_tableview_search_form_filter_value" value="<?= !empty($_tableview_filter_value) ? $_tableview_filter_value : ''; ?>">
      						<input type="hidden" class="form-control" placeholder="Filter" id="id_tableview_search_form_filter">
      						<?php endif ?>
    					</div>
    					<div class="col row" id="id_tableview_search_form_buttons">
    						<?php if (!empty($_tableview_filters_fixed) && is_array($_tableview_filters_fixed) && count($_tableview_filters_fixed)>0) : ?>
    						<div class="btn-group">
    							<button type="button" class="btn btn-secondary" onclick="tableviewSearchFilterGo()" >
    								<i class="fas fa-filter"></i>
    							</button>
    							<button class="btn btn-secondary dropdown-toggle dropdown-toggle-split" type="button" id="id_tableview_filter_enabled" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
   	 								
  								</button>
  								<div class="dropdown-menu" aria-labelledby="id_tableview_filter_enabled" id="id_tableview_search_form_filterbuttons">
    								<?php foreach($_tableview_filters_fixed as $label=>$fiter) : ?>
    								<button type="button" class="dropdown-item btn btn-link" onclick="tableviewSearchFilterGo('<?= $fiter ?>')" >
    									<?= lang($label); ?>
    								</button>
    								<?php endforeach ?>	
    							</div>	
    						</div>
    						<?php else : ?>
    						<button type="button" class="btn btn-secondary btn-sm" onclick="tableviewSearchFilterGo()"><i class="fas fa-filter"></i></button>
    						<?php endif ?>	
    					</div>	
    				</div>
    			<?= form_close() ?>
    		<?php endif ?>
    		<?= form_open('',['id'=>'id_tableview_form'],['model'=>empty($_tableview_model) ? '' : $_tableview_model]) ?>
			<?= $_tableview_table ?>
			<?= !empty($_tableview_pagination) ? $_tableview_pagination : null?>
			<?= form_close() ?>
		<?php else : ?>
			<?= $this->renderSection('form_body') ?>
		<?php endif ?>
    </div>
    <?php if(!empty($_formview_footer)) : ?>
    <div class="card-footer">
        <?= $_formview_footer ?>
	</div>
	<?php endif ?>
</div>

<script>
	<?= $currentView->includeView('System/table_datatable') ?>
	$(".tableview_def_btns").on('click',function(){
		var confirmed=true;
		if ($(this).attr('id')=='id_tableview_btn_del'){
			if (confirm("<?= lang('system.general.msg_delete_ques')?>")){
				confirmed=true;
			}else{
				confirmed=false;
			}	
		}
		if (confirmed){
			var action=$(this).attr('data-action');
			$('#id_tableview_form').attr('action', action).submit();
		}
		
	});
	
	<?php if(!empty($_tableview_filters)) : ?>
	
	function tableviewSearchFilterGo(filter=null){
		if (filter==null){
			filter=$("#id_tableview_search_form_filter_value").val();
		}
		if (filter.search('=')>=0){
			filter=filter.split('=');
			filter=filter[1];
		}
		$("#id_tableview_search_form_filter_value").val(filter);
		$("#id_tableview_search_form_filter").val(btoa(filter));
		$("#id_tableview_search_form").submit();
	}
	
	function tableviewSearchFilterSearch(){
		var filter=$("#id_tableview_search_form_filter_value").val();
		table_view_datatable.search(filter).draw();
	}

	$('#id_tableview_search_form_filter_value').on('keypress', function (e) {
    	if (e.which==13){
    		tableviewSearchFilterGo();
    	}
	} );
	
	function tableviewSearchFilterGo1(filter=null){
		var url='<?= !empty($_tableview_filters_url) ? $_tableview_filters_url : '' ?>';
		if (filter!=null && filter.length>0){
			$("#id_tableview_search_form_filter_value").val(filter);
		}
		filter='<?= $_tableview_filters ?>';
		
		var value=$("#id_tableview_search_form_filter_value").val();
		filter=atob(filter);
		
		if (value.length>0){
			if (value.indexOf('=') >= 0){
				value=value.split('=');
				filter='{"-value-":"%value%","'+value[0]+'":"'+value[1]+'"}';
				value=value[1];
			}
			value=value.replace('<?= lang('system.general.yes') ?>',1);
			value=value.replace('<?= lang('system.general.no') ?>',0);
			filter=filter.replace(/%value%/g,value);
			
			url=url+'?filtered='+btoa(filter);
		}
		$("#id_tableview_search_form").attr('action',url).submit();
	}
	
	<?php endif ?>
</script>



