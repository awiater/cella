
<div class="table_excel w-100">
<table  id="stackingplan_table" style="width:100%"><!-- class="table table-sm table-striped"-->
	<thead>
		<tr>
                    <th scope="col" class="no-sort"><button type="button" class="btn btn-link sort_custom" data-column="0">CUSTREF</button></th>
			<th scope="col" class="no-sort">SUPPLIER</th>
			<th scope="col" class="no-sort "><button type="button" class="btn btn-link sort_custom" data-column="2">REFERENCE</button></th>
			<th scope="col" class="no-sort">LOCATION</th>
			<th scope="col" class="no-sort">PALNO</th>
			<th scope="col" class="no-sort">LMH</th>
			<th scope="col" class="no-sort">LENGTH</th>
			<th scope="col" class="no-sort">WIDTH</th>
			<th scope="col" class="no-sort">HEIGHT</th>
			<th scope="col"<?= (!empty($stackingedit) && $stackingedit) ? ' style="width:140px' : ''?>" class="no-sort">STACKING</br>FOR</br>CUSTOMER</th>
                        <th scope="col" class="no-sort"><button type="button" class="btn btn-link sort_custom" data-column="11">STACKING</br>ON TRUCK</button></th>
			<th scope="col" class="no-sort d-none">SORT</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach($stackingdata as $key=>$row) :?>
		<tr>
			<td><?= $row['custref'] ?></td>
			<td><?= $row['supplier'] ?></td>
			<td>
				<?= $row['reference'] ?>
				<input type="hidden" name="stackinpallets[<?=$key?>][reference]" value="<?= $row['reference'] ?>">
			</td>
			<td><?= $row['location'] ?></td>
			<td><?= $row['palno'] ?></td>
			<td><?= $row['lmh'] ?></td>
			<td><?= $row['length'] ?></td>
			<td><?= $row['width'] ?></td>
			<td><?= $row['height'] ?></td>
			<td>
				<?php if(!empty($stackingedit) && $stackingedit) :?>
				<div class="row no-gutters">
					<div class="col-6">
						<input type="number" class="form-control form-control-sm w-75 ml-auto stcNR" name="stackinpallets[<?=$key?>][stacknr]" id="editorA<?= $key?>" min="1" max="120" value="<?= $row['stacknr'] ?>" data-id="<?= $key?>">
					</div>
					<div class="col-6">
						
						<select class="form-control form-control-sm w-75" id="editorB<?=$key?>" name="stackinpallets[<?=$key?>][stackpos]">
								<option value="" <?= $row['stackpos']=="" ? ' selected="true"' : null ?>></option>
								<option value="B"<?= $row['stackpos']=="B" ? ' selected="true"' : null ?>>B</option>
								<option value="M"<?= $row['stackpos']=="M" ? ' selected="true"' : null ?>>M</option>
								<option value="N"<?= $row['stackpos']=="N" ? ' selected="true"' : null ?>>N</option>
								<option value="M2"<?= $row['stackpos']=="M2" ? ' selected="true"' : null ?>>M2</option>
								<option value="T"<?= $row['stackpos']=="T" ? ' selected="true"' : null ?>>T</option>
						</select>
					</div>
				</div>		
				<?php else : ?>
				<?= $row['stacking'] ?>
				<?php endif ?>
			</td>
			<td class="text-center">
				<?php if(!empty($stackingedit) && $stackingedit) :?>
				<input type="text" style="width:80px;" class="form-control form-control-sm mx-auto onTruckEditor" name="stackinpallets[<?=$key?>][stacktruck]" id="editorC<?= $key?>" value="<?= $row['stacktruck'] ?>">	
				<?php else : ?>
				<?= $row['stacktruck'] ?>
				<?php endif ?>
			</td>
			<td id="editorC<?= $key?>_label" class="d-none">
				<?= $row['stacktruck'] ?>
			</td>
		</tr>
		<?php endforeach ?>	
	</tbody>
</table>
</div>
<script>
	var stackingplan_table=null; 
	$(function(){
		stackingplan_table=$('#stackingplan_table').DataTable({
			//'pageLength':1000,
                        'paging':false,
			'ordering':true,
			columnDefs: [
				{
      				orderable: false,
      				targets: "no-sort"
    			},
    			{
    				"targets": [11],
                	"visible": true,
    			}
    		],
    		order:[],
			'searching':false,
			dom:'Bfrtip',
			buttons: {
    			dom: {
      				button: {
        						tag: 'button',
       							className: ''
     				 		}
    				},
			buttons:[
						{ 
							extend: 'csv', 
							className: 'ml-1 mb-2 btn btn-sm btn-primary',
							text:'<i class="fas fa-file-csv mr-2"></i><?= lang('system.buttons.exportbtn') ?>',
							filename:$("#id_reference").val()+'_stackingplan',
							/*customize:function(data){
								data = data.split("\n");
								$.each(data.slice(1), function (index, row) {
									row = row.split(',');
									var row_9=$("#editorA"+index).val()+$("#editorB"+index+" option:selected").val();
									if (row_9.length > 0 && row_9!='NaN'){
										row[9]=row_9;
									}
									
									row[10]=$("#editorC"+index).val();
									row=row.slice(0,-1);
									//row.splice(7,1);
									data[index+1]=row.join(',');
								});
								data[0]=data[0].split(',');
								data[0]=data[0].slice(0,-1);
								data[0]=data[0].join(',');
								console.log(data.join('\n'));
								return data.join('\n');
							}*/
							/*action: function ( e, dt, node, config ){
								$("#stackingplan_table").table2csv();
							}*/
							customize:function(data){
								var csv=[];
								$("#stackingplan_table > thead > tr").each(function(){
									var row=[];
									$(this).find('th').each(function(rid){
										var cell=$(this).text();
										if (rid==11){
											cell='';
										}
										cell=cell.replace(/[\r\n\t]/gm, '');
										row.push(cell);
									});
									csv.push(row.join(','));
								});
								
								$("#stackingplan_table > tbody > tr").each(function(index, tr){
									var row=[];
									$(this).find('td').each(function(rid){
										var cell=$(this).text();
										if (rid==9){
											cell=($(this).find('input[type="number"]').val())+($(this).find('select').children('option').filter(':selected').text());
										}else
										if (rid==10){
											cell=($(this).find('input[type="text"]').val());
										}else
										if (rid==11){
											cell='';
										}else
										if (rid==2){
											cell='="'+cell+'"';
										}
										cell=cell.replace(/[\r\n\t]/gm, '');
										row.push(cell);
									});
									csv.push(row.join(','));
								});/**/
								return csv.join('\n');
							}
						},
						<?php if (!empty($load_id)) :?>
						{ 
						 	className: 'btn btn-danger btn-sm mb-2',
						 	text:'<i class="fas fa-magic mr-1"></i><?= lang('system.warehouse.stackwizard') ?>',
						 	action: function ( e, dt, node, config ) {
						 		window.location='<?= url('Warehouse','stackingplancreator',[$load_id],['refurl'=>current_url(FALSE,TRUE)]) ?>'; 
            				} 
						 },
						<?php endif ?>
						{ 
						 	className: 'btn btn-secondary btn-sm mb-2 ml-3',
						 	text:'<i class="fas fa-copy mr-1"></i><?= lang('system.buttons.copybtn') ?>',
						 	action: function ( e, dt, node, config ) {
						 		 $(".stcNR").each(function(){
						 		 	var id=$(this).attr('data-id');
						 		 	$("#editorC"+id).val($("#editorA"+id).val()+$("#editorB"+id+" option:selected").val());
						 		 });
            				} 
						 },
						 /*{ 
						 	className: 'btn btn-secondary btn-sm mb-2 ml-1',
						 	text:'<i class="fas fa-sort-numeric-down mr-1"></i>SORT',
                                                        name:'sort_dims', 
						 	action: function ( e, dt, node, config ) {
						 		 dt.order([11,'asc']).draw();
						 		 $('.sorting_asc').removeClass('sorting_asc');
            				} 
						 },*/
						 
					]
				}
		});
		
		$(".onTruckEditor").each(function(){
			parseNrForSort($(this));
		});
                stackingplan_table.button('sort_dims:name').trigger();
		
                $('.sort_custom').on('click',function(){
                    stackingplan_table.order([$(this).attr('data-column'),'asc']).draw();
                    $('.sorting_asc').removeClass('sorting_asc');
                });
	});
	
	$(".onTruckEditor").on("change",function(){
		parseNrForSort($(this));
	});
        
	
        
	function parseNrForSort(obj){
		var val=obj.val();
		
		if (val.length<3){
			val='00'+val;
		}else
		if (val.length <4){
			val='0'+val;
		}
		val=val.replace('M2',3).replace('B',0).replace('M',1).replace('N',2).replace('T',4);
		stackingplan_table.cell($("#"+obj.attr('id')+'_label')).data(val);
	}
</script>