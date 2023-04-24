<div>
	<div class="row">
		<div class="col-8">
			<?= $currentView->includeView('System/form_fields',['fields'=>array_slice($data['fields'], 0,$data['maincfgfieldsqty'])]); ?>
		</div>
	</div>
	<div class="row">
		<div class="form-group p-2 col-12" id="id_settings_boardnames_field">
    		<label for="id_settings_boardnames" class="mr-2 d-flex">
    			<?= lang('scheduler.settings.boardnameslist') ?>
    			<div class="ml-auto text-right">
    					<button type="button" onClick="showBoardEditor('<?= $data['defaultboard'] ?>')" class="d-none btn btn-sm btn-success newbtn">
    						<i class="fas fa-plus-square"></i>
    				</button>
    				<a href="#id_settings_boardnames_collapse" class="btn btn-secondary btn-sm collapsebtn" data-toggle="collapse" role="button" aria-expanded="false" aria-controls="id_settings_boardnames_collapse">
    					<i class="far fa-caret-square-down"></i>
  					</a>
    			</div>
    		</label>
    		<div class="collapse" id="id_settings_boardnames_collapse">
    		<ul class="list-group">
    			<?php foreach ($data['boardsdata'] as $key => $value) :?>
    				<li class="list-group-item d-flex">
    					<?= $value['name'] ?>
    					<div class="ml-auto">
    						<button type="button" onClick="deleteBoardEditor('<?= $value['sbid'] ?>')" class="btn btn-sm btn-danger mr-2">
    							<i class="fas fa-trash-alt"></i>
    						</button>
    						<button type="button" onClick="showBoardEditor('<?= base64_encode(json_encode($value)) ?>')" class="btn btn-sm btn-primary">
    							<i class="fas fa-edit"></i>
    						</button>
    					</div>
    				</li>
    				<input type="hidden" value="boards" id="id_settings_boardnames_delmodel">
    			<?php endforeach ?>
    		</ul>
    		</div>
 		</div>
	</div>
	
	<div class="row">
		<div class="form-group p-2 col-12" id="id_settings_boardcardscolors_field">
    		<label for="id_settings_boardnames" class="mr-2 d-flex">
    			<?= lang('scheduler.settings.boardcardscolors') ?>
    			<a href="#id_settings_boardcardscolors_collapse" class="btn btn-secondary btn-sm ml-auto collapsebtn" data-toggle="collapse" role="button" aria-expanded="false" aria-controls="id_settings_boardcardscolors_collapse">
    				<i class="far fa-caret-square-down"></i>
  				</a>
    		</label>
    		<div class="collapse" id="id_settings_boardcardscolors_collapse">
    		<ul class="list-group">
    			<input type="text" name="settings[orders_status_types_colors]" id="orders_status_types_colors" value="<?= $data['orders_status_types_colors'] ?>"><!--  id_types_colors_txt_-->
    			<?php $types_key=0; ?>
    			<?php foreach ($data['settings_colors'] as $key => $value) :?>
    			<li class="list-group-item">
    				<div class="row">
    					<div class="col-6">
    						<?= array_key_exists($key, $data['settings_orders']) ? $data['settings_orders'][$key] : $key ?>
    					</div>
    					<?php $value=explode(':',$value); $value[1]=count($value) <2 ? '0,0,0' : $value[1];?>
    					<div class="col-3">
    						<input type="text" class="form-control picker picker_back" data-key="<?= $key ?>" data-id="<?= $types_key ?>" data-type="back" value="<?= $value[0] ?>" style="width:50px;color:<?= $value[0] ?>;background-color:<?= $value[0] ?>;cursor:pointer;">
    					</div>
    					<div class="col-3">
    						<input type="text" class="form-control picker" data-key="<?= $key ?>" data-id="<?= $types_key ?>" data-type="txt" value="rgb(<?= count($value) > 1 ? $value[1] : '0,0,0'?>)" style="width:50px;color:rgb(<?= $value[1] ?>);background-color:rgb(<?= $value[1] ?>);cursor:pointer;">
    					</div>
    				</div>
    				
    			</li>
    			<?php $types_key++; ?>
    			<?php endforeach ?>
    		</ul>
    		</div>
 		</div>
	</div>
	
	<div class="row">
		<div class="form-group p-2 col-12" id="id_settings_boardcardstpls_field">
    		<label for="id_settings_boardnames" class="mr-2 d-flex">
    			<?= lang('scheduler.settings.boardcardstpls') ?>
    			<div class="ml-auto text-right">
    				<a href="<?= url('Scheduler','cardedit',['new'],['refulr'=>current_url(FALSE,TRUE)]) ?>" class="btn btn-sm btn-success newbtn d-none">
    					<i class="fas fa-plus-square"></i>
    				</a>
    				<a href="#id_settings_boardcardstpls_collapse" class="btn btn-secondary btn-sm ml-auto collapsebtn" data-toggle="collapse" role="button" aria-expanded="false" aria-controls="id_settings_boardcardstpls_collapse">
    					<i class="far fa-caret-square-down"></i>
  					</a>
    			</div>
    		</label>
    		<div class="collapse" id="id_settings_boardcardstpls_collapse">
    		<ul class="list-group">
    			<?php foreach ($data['cardstpls'] as $value) :?>
    			<li class="list-group-item">
    				<div class="row">
    					<div class="col-5">
    						<?= $value['name'] ?>
    					</div>
    					<div class="col-5">
    						<?= $value['desc'] ?>
    					</div>
    					<div class="col-2 text-right">
    						<button type="button" onClick="deleteBoardCardTpl('<?= $value['did'] ?>')" class="btn btn-sm btn-danger mr-2">
    							<i class="fas fa-trash-alt"></i>
    						</button>
    						<a href="<?= url('scheduler','cardedit',[$value['did']],['refurl'=>url('Settings',null,[],['tab'=>'scheduler'],TRUE)]) ?>" class="btn btn-sm btn-primary">
    							<i class="fas fa-edit"></i>
    						</a>
    					</div>
    				</div>
    				
    			</li>
    			<?php endforeach ?>
    		</ul>
    		</div>
 		</div>
	</div>
	
</div>
<div class="modal" tabindex="-1" role="dialog" id="BoardEditor">
  <div class="modal-dialog modal-xl" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><?= lang('scheduler.settings.boardseditor_title') ?></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
      	<ul class="nav nav-tabs" id="BoardEditorTabs" role="tablist">
      		<li class="nav-item">
   				<a class="nav-link active" id="BoardEditorTabs-hometab" data-toggle="pill" href="#BoardEditorTabs-home" role="tab" aria-controls="BoardEditorTabs-home" aria-selected="true">
   					<?= lang('scheduler.settings.tabhome') ?>
   				</a>
 			</li>
 			<li class="nav-item">
 				<a class="nav-link" id="BoardEditorTabs-filetab" data-toggle="pill" href="#BoardEditorTabs-file" role="tab" aria-controls="BoardEditorTabs-file" aria-selected="true">
   					<?= lang('scheduler.settings.tabfile') ?>
   				</a>
 			</li>
      	</ul>
      	<div class="tab-content" id="BoardEditorTabsContent">
      		<div class="tab-pane fade show active" id="BoardEditorTabs-home" role="tabpanel" aria-labelledby="BoardEditorTabs-hometab">
      			<?= $currentView->includeView('System/form_fields',['fields'=>array_slice($data['fields'], $data['maincfgfieldsqty'],7)]); ?>
      		</div>
      		<div class="tab-pane fade" id="BoardEditorTabs-file" role="tabpanel" aria-labelledby="BoardEditorTabs-filetab">
      			<?= $currentView->includeView('System/form_fields',['fields'=>array_slice($data['fields'], $data['maincfgfieldsqty']+7,7)]); ?>
      		</div>
      	</div>
        
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" id="BoardEditorSave" ><?= lang('scheduler.settings.boardseditor_okbtn') ?></button>
        <button type="button" class="btn btn-secondary" data-dismiss="modal"><?= lang('scheduler.settings.boardseditor_cncbtn') ?></button>
      </div>
    </div>
  </div>
</div>
<script>
	$(function(){
		$('.picker').colorpicker();
		$('.picker').on('colorpickerChange', function(event) {
			//alert($(this).css());
        	$(this).css('background-color',event.color.toString());
        	$(this).css('color',event.color.toString());
      	});
	});
	
	function beforeSaveScheduler(){
		$('.picker').each(function(){
        		var arr=$("#orders_status_types_colors").val();
        		arr=arr.split('|');
        		var type=$(this).attr('data-type');
        		var id=$(this).attr('data-id');
        		var val=arr[id];
        		val=val.split('=');
        		
        		var color=val[1].split(':');
        		
        		if (type=='back'){
        			color[0]=$(this).val();
        		}else
        		{
        			color[1]=$(this).val();
        			color[1]=color[1].replace('rgb(','').replace(')','');
        		}
        		color=color.join(':');
        		
        		val[1]=color;
        		val=val.join('=');
        		arr[id]=val;
 				arr=arr.join('|');
        		$("#orders_status_types_colors").val(arr);
        	});
	}
	
	$("#id_btn_settings_save").attr('type',button);
	
	$(".collapsebtn").on("click",function(){
		var id=$(this).attr('aria-controls');
		var parent=id.replace('_collapse','');
		if ($("#"+id).hasClass('show')){
			$(this).find('i').attr('class','far fa-caret-square-down');
			$("#"+parent+'_field').find('.newbtn').addClass('d-none');
		}else{
			$(this).find('i').attr('class','far fa-caret-square-up');
			$("#"+parent+'_field').find('.newbtn').removeClass('d-none');			
		}
	});
	
	function showBoardEditor(data){
		$("#BoardEditorSave").attr('data-board',data);
		var data=JSON.parse(atob(data));
		$.each(data, function(key,valueObj){
    		$("#id_"+key).val(valueObj);
		});
		$("#BoardEditor").modal('show');
	}
	
	function deleteBoardEditor(id){
		if (confirm("<?= lang('system.general.msg_delete_ques')?>")){
			$("#id_sbid").attr('name','sbid[]').val(id);
			$("#id_settings_boardnames_delmodel").attr('name','model');
			$("#edit-form").attr('action','<?= $data['boardDelUrl'] ?>').submit();
		}
	}
	
	function deleteBoardCardTpl(id){
		if (confirm("<?= lang('system.general.msg_delete_ques')?>")){
			$("#id_sbid").attr('name','did[]').val(id);
			$("#id_settings_boardnames_delmodel").attr('name','model').val('doc');
			$("#edit-form").attr('action','<?= $data['boardDelUrl'] ?>').submit();
		}
	}
	
	$("#BoardEditorSave").on('click',function(){
		$("#refurl_ok").attr('name','refurl_ok');
		$("#edit-form").attr('action','<?= $data['boardSaveUrl'] ?>').submit();
	});
	
</script>