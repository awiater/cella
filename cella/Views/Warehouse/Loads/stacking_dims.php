<div class="table-responsive">
<table class="table table-striped" id="stacking_viewer_table">
	<thead class="thead-dark">
		<tr>
			<th>REFERENCE</th>
			<th>XSNUMBER</th>
			<th>OPERATOR</th>
			<th>LMH</th>
			<th>SIZE</th>
			<th>HEIGHT</th>
			<th>LOCATION</th>
			<th>SUPPLIER</th>
			<th>SUPPLIER PALL REF</th>
			<th>B/T NR</th>
			<th>B/T</th>
			<th>DIM`D</th>
			<th>CUBE</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach($data as $key=>$row) :?>
		<tr id="stacking_viewer_table_<?= $row['reference']; ?>">
			<td><?= $row['reference']; ?></td>
			<td><?= $row['xsnumber']; ?></td>
			<td><?= $row['operator']; ?></td>
			<td><?= $row['lmh']; ?></td>
			<td><?= $row['size']; ?></td>
			<td><?= $row['height']; ?></td>
			<td><?= $row['location']; ?></td>
			<td><?= $row['supplier']; ?></td>
			<td><?= $row['suppalref']; ?></td>
			<td>
				<?php if (!empty($readonly) && $readonly) :?>
					<?= $row['B/T NR'] ?>
				<?php else :?>
				<input type="number" class="form-control form-control-sm editorA" name="pallets[<?=$key?>][stacknr]" id="editorA<?=$key?>" min="1" max="120" value="<?= $row['B/T NR'] ?>">
				<input type="hidden" name="pallets[<?=$key?>][reference]" value="<?=$row['reference']?>">
				<?php endif ?>
			</td>
			<td>
				<?php if (!empty($readonly) && $readonly) :?>
					<?= $row['B/T'] ?>
				<?php else :?>
				<?php $value=$row['B/T'] ?>
				<select class="form-control form-control-sm editorB" id="editorB<?=$key?>" name="pallets[<?=$key?>][stackpos]">
				<?php foreach($btlist as $item) :?>
					<option value="<?= $item ?>"<?= $value==$item ? ' selected="true"' : null?>><?= $item ?></option>
				<?php endforeach ?>
				</select>
				<?php endif ?>
			</td>
			<td><?= $row['DIM`D']; ?></td>
			<td><?= $row['cube']; ?></td>
		</tr>
		<?php endforeach ?>
	</tbody>
</table>
</div>
<script>
	var stacking_viewer_table=null;
	$(function(){
		stacking_viewer_table=$("#stacking_viewer_table").DataTable({
			'searching':false,
			'ordering':true,
			'paging':false,
			dom:'Bfrtip',
			buttons:[
				{ 
					extend: 'csv', 
					className: 'ml-1 mb-2 btn btn-sm btn-primary',
					text:'<i class="fas fa-file-csv mr-2"></i><?= lang('system.buttons.exportbtn') ?>',
					filename:$("#id_reference").val()+'_dimms',
					<?php if (empty($readonly)) :?>
					customize:function(data){
						return getStackingData(data);
						
					}
					<?php endif ?> 
				},
				{ 
					className: 'ml-1 mb-2 btn btn-sm btn-warning',
					text:'<i class="far fa-file-pdf mr-2"></i><?= lang('system.buttons.exportpdfbtn') ?>',
					action: function ( e, dt, node, config ) {
						var url='<?= $pdfBtn ?>';
            			window.open(url,'_blank');
            		} 
				},
			],
		});
		$(".dt-button").removeClass('dt-button');
		$("#stacking_viewer_table").find('tr th:nth-child(11)').css('width','40px');
		$("#stacking_viewer_table").find('tr th:nth-child(10)').css('width','40px');
	});
	
	$(".editorB").on('change',function(){
		var val=$("option:selected", this).val();
		var max=0;
		var cur=0;
		if (val=='N'){
			$(".editorA").each(function(index, value){
				cur=parseInt($(this).val());
				if (cur>max){
					max=cur;
				}
			});
			if (max<1){
				max=1;
			}else{
				max=max+1;
			}
			cur=$(this).attr('id');
			cur=cur.replace('editorB','editorA');
			$('#'+cur).val(max);
		}
		
	});
	
	function getStackingData(data=null){
		if (data==null){
			data=stacking_viewer_table.data().toArray();
		}
		
		if ($.isArray(data)){
			var headers=JSON.parse(atob('<?= base64_encode(json_encode($headers))?>'));
			
			data.forEach(function(row, i){
							var val= $("#editorB"+i+" option:selected").val();
							row[10]=$("#editorA"+i).val();
							row[11]=25;
							var oRow={};
							row.forEach(function(col, j){
								oRow[headers[j]]=row[j];
							});
							data[i]=oRow;	
						});
						
		return JSON.stringify(data);
		}else{
			data = data.split("\n");
			data[0]=data[0].split(',');
			data[0][9]='B/T';
			data[0][10]=data[0][11];
			data[0][11]=data[0][12];
			data[0][12]='@#';
			data[0]=data[0].join(',').replace(',@#','');
						$.each(data.slice(1), function (index, row) {
							row = row.split(',');
							var tr=row[0];
							tr=tr.replace('"','');
							tr=$('#stacking_viewer_table_'+tr.replace('"',''));
							row[0]='='+row[0];
							row[9]=tr.find('.editorA').val();
							row[10]=tr.find('.editorB').find('option:selected').val();
							row[9]=row[9]+row[10];
							row[10]=row[11];
							row[11]=row[12];
							row[12]='@#';
							data[index+1]=row.join(',');
							data[index+1]=data[index+1].replace(',@#','');
							//data[index+1]=data[index+1].substr(2);
						});
			//console.log(data.join('\n'));exit;
			return data.join('\n');
		}
	}
</script>
	