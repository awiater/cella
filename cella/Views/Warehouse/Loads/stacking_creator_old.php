<?php $truckLength=13600; $truckHeight=2650; $truckWidth=2450; $noOfPallSpaces=33;$palletnr=1;$eurLength=1200; $stdWidth=1000; $eurWidth=800;?>
<button type="button" class="btn btn-sm btn-primary" id="printPDFBTN">PDF</button>
<div class="container m-0 mw-100">
	<div class="row mb-2">
		<div class="col-2">Truck Width: <?= $truckLength ?></div>
		<div class="col-2">Avaliable Width: <b id="avalWidth"><?= $truckLength ?></b></div>
		<div class="col-2">Euro Spaces: <b id="eurSpc"><?= ($truckLength/$eurLength*3)-1 ?></b></div>
		<div class="col-2">UK Spaces: <b id="ukSpc"><?= (int)($truckLength/$stdWidth*2)-1 ?></b></div>
	</div>
	<div class="row border mb-3">
		<div class="col-2 p-2">
			<div class="mb-1">
				Space Avaliable Height: <b id="spaceHeightLBL"><?= $truckHeight ?></b>
			</div>
			<div class="mb-1">
				Space Length: <b id="spaceWidthLBL">1200</b>
			</div>
			<div class="border bg-warning pt-2" style="height:70%" id="draggable_space">
				<div class="container" style="height:100%">
					<div class="droppable_pallet sortable container" data-height="<?= $truckHeight ?>" style="height:100%" data-length="0">
				</div>
				</div>
			</div>
			<div class="droppable_pallet_remove bg-danger mt-2 text-white text-center" style="height:50px;">
				DRAG HERE TO REMOVE
			</div>
		</div>
		<div class="row col" id="truck">
		<?php for ($i=0; $i < 13; $i++) :?>			
		<div class="col p-1 bg-secondary truckspace_row" style="height:380px" id="truckspace_row_<?= $i ?>">
			<?php for ($ii=0; $ii < 3; $ii++) :?>
				<div class="border container mb-2 p-1 bg-warning" style="height:31%" id="truckspace_<?= $i.$ii ?>" data-length="0" data-width="800" data-row="<?= $i ?>">
					<button type="button" id="addPalletToTruck_<?= $i.$ii ?>" class="btn btn-sm btn-success addPalletToTruckBtn <?= $i>0 ? 'd-none' : ''?>"><i class="fa fa-plus"></i></button><?= $i.$ii ?>
				</div>
			<?php endfor ?>
		</div>
		<?php endfor ?>
		</div>
	</div>
	<div class="row border">
		<table class="table table-striped" id="avalPalletsTbl">
			<thead>
				<tr>
					<th>reference</th>
					<th>lmh</th>
					<th>length</th>
					<th>width</th>
					<th>height</th>
				</tr>
			</thead>
			<tbody id="avalPalletsTblBody">
				<?php foreach($data as $key=>$pallet) : ?>
					<tr class="draggable" id="<?= $pallet['reference']?>" data-height="<?= $pallet['height'] ?>" data-length="<?= $pallet['iseur'] ? $pallet['length'] : $pallet['width'] ?>" data-width="<?= $pallet['width'] ?>" data-rowid="<?= $key ?>">
						<td><?= $pallet['reference'] ?></td>
						<td><?= $pallet['lmh'] ?></td>
						<td><?= $pallet['length'] ?></td>
						<td><?= $pallet['width'] ?></td>
						<td><?= $pallet['height'] ?></td>
					</tr>
				<?php endforeach ?>
			</tbody>
		</table>
	</div>
</div>
<div id="editor"></div>
<script>
	var maxPall=13;
	var PalletsTabel=null;
	
	$(function(){		
		PalletsTabel=$('#avalPalletsTbl').DataTable({'ordering':false});
				
		$( ".sortable" ).sortable();
		
		addDragItems();
		 
		 $(".draggable_space").draggable({
		 	handle: ".draggable_space_hand",
		 });
		 
		 $(".droppable_pallet").droppable({
		 	accept : function(ui){
		 		var palH=parseInt(ui.attr('data-height'));
		 		var spcH=parseInt($(this).attr('data-height'));
		 		var palL=parseInt(ui.attr('data-length'));
		 		var spcL=parseInt($(this).attr('data-length'));
		 		return (spcH>=palH) && ((palL==spcL) || (spcL==0));
		 	},
		 	drop: function( event, ui ){
		 		var item=ui.draggable;
		 		var palH=parseInt(item.find('td:nth-child(5)').text());
		 		var spcH=parseInt($(this).attr('data-height'));
		 		spcH=spcH-palH;
		 		$(this).attr('data-height',spcH);
		 		$("#spaceHeightLBL").text(spcH);
		 		$("#spaceWidthLBL").text(item.attr('data-length'));
		 		if ($(this).attr('data-length')==0){
		 			$(this).attr('data-length',item.attr('data-length'));
		 			$(this).attr('data-width',item.attr('data-width'));
		 		}
		 		$(this).append(createHelper(item));
		 		PalletsTabel.row(item).remove().draw();
		 	}
		 });
		 
		  $( ".droppable_pallet_remove" ).droppable({
		 	drop: function( event, ui ){
		 		var item=ui.draggable;
		 		var row=$(atob(item.attr('data-object')));
		 		var palH=parseInt(row.attr('data-height'));
		 		var spcH=parseInt($(".droppable_pallet").attr('data-height'));
		 		spcH=spcH+palH;
		 		$(".droppable_pallet").attr('data-height',spcH);
		 		$("#spaceHeightLBL").text(spcH);
		 		
		 		PalletsTabel.row.add(row).draw();
		 		item.remove();
		 		
		 		if ($(".droppable_pallet").html().length<114){
		 			$("#spaceWidthLBL").text('<?= $eurLength?>');
		 			$(".droppable_pallet").attr('data-length',0);	
		 		}
		 	}
		 });
	});
	
	function addDragItems(id='#avalPalletsTblBody TR'){
		 $(id).each(function(){
		 	$(this).draggable({
		 		helper: function() {
		 			return createHelper($(this));
		 		}
		 	});
		 });
	}
	
	$('#avalPalletsTbl').on( 'draw.dt', function () {
		addDragItems(true);
	});
	
	$(".addPalletToTruckBtn").on("click",function(){
		var item=$(".droppable_pallet");
		var id=$(this).attr('id');
		var truckWidth=parseInt($("#avalWidth").text());
		
		id=id.replace('addPalletToTruck_','truckspace_');
		
		if (item.html().length > 13 && ( (parseInt(item.attr('data-length'))==parseInt($("#"+id).attr('data-length'))) || $("#"+id).attr('data-length')=='0' ) ){
			var html='';
			item.children().each(function () {
				$(this).children().each(function () {
					if ($(this).attr('data-id')=='reference'){
    					html+='<div class="row col">'+$(this).text()+'</div>';
    				}
				}); 			
			});
			truckWidth=truckWidth-parseInt(item.attr('data-length'));
			var noPall=truckWidth/<?= $eurLength?>;
			noPall=parseInt(noPall);
			
			maxPall--;
			$("#eurSpc").text(noPall+'/'+maxPall);
			noPall=(13-maxPall)+noPall-1;
			for (let i = 0; i < 13; i++) {
				if (i>noPall){
					$("#truckspace_row_"+i).addClass("d-none");
				}
			}
			
			$("#truckspace_"+$("#"+id).attr('data-row')+'1').attr('data-length',item.attr('data-length'));
			$("#truckspace_"+$("#"+id).attr('data-row')+'2').attr('data-length',item.attr('data-length'));
			
			if (item.attr('data-width')!='<?= $eurWidth ?>'){
				var wd=parseInt('<?= $truckWidth ?>')/parseInt(item.attr('data-width'));
				wd=parseInt(100/parseInt(wd))-2;
				
				$("#"+id).attr('style','height:'+wd+'%');
				$("#truckspace_"+$("#"+id).attr('data-row')+'1').attr('style','height:'+wd+'%');
				$("#truckspace_"+$("#"+id).attr('data-row')+'2').addClass("d-none");
			}
			
			$("#addPalletToTruck_"+(parseInt($("#"+id).attr('data-row'))+1)+'0').removeClass('d-none');
			$("#addPalletToTruck_"+(parseInt($("#"+id).attr('data-row'))+1)+'1').removeClass('d-none');
			$("#addPalletToTruck_"+(parseInt($("#"+id).attr('data-row'))+1)+'2').removeClass('d-none');
			
			$("#avalWidth").text(truckWidth);
			
			$("#"+id).html(html);
			$(".droppable_pallet").html('');
			$(".droppable_pallet").attr('data-height','<?= $truckHeight ?>');
			$(".droppable_pallet").attr('data-length',0);
			$("#spaceHeightLBL").text($(".droppable_pallet").attr('data-height'));
			$("#spaceHeightLBL").text('<?= $eurLength?>');
		}else{
			alert('<?= lang('system.collections.novalid_space_error') ?>');
		}
	});
	
	function createHelper(object){
		var html=$('<div class="row border bg-primary" data-object="'+btoa(object.prop('outerHTML'))+'"></div>');
		html.attr('data-height',object.attr('data-height'));
		html.attr('data-length',object.attr('data-length'));
		html.attr('data-width',object.attr('data-width'));
		html.append('<div class="col border-right" data-id="reference">'+object.find('td:nth-child(1)').text()+'</div>');
		html.append('<div class="col border-right" data-id="lmh"><small>'+object.find('td:nth-child(2)').text()+'</small></div>');
		html.append('<div class="col border-right" data-id="length">'+object.find('td:nth-child(3)').text()+'</div>');
		html.append('<div class="col border-right" data-id="width">'+object.find('td:nth-child(4)').text()+'</div>');
		html.append('<div class="col" data-id="height">'+object.find('td:nth-child(5)').text()+'</div>');
		return html;
	}
	$("#printPDFBTN").on("click",function(){
		 html2canvas(document.getElementById('truck'),{
                        onrendered: function (canvas) {                     
                               var imgString = canvas.toDataURL("image/png");
                               var a = document.createElement('a');
                               a.href = imgString;
                               a.download = "image.png";
                               document.body.appendChild(a);
                               a.click();
                               document.body.removeChild(a);              
                    }
                });
	});
</script>
<div class="row border" data-height="800" data-length="1200">
	<div class="col border-left" data-id="reference">135175</div><div class="col border-left" data-id="lmh">H-H</div>
<div class="col border-left" data-id="length">1200</div><div class="col border-left" data-id="width">800</div><div class="col" data-id="height">800</div></div><div class="row border" data-height="800" data-length="1200"><div class="col border-left" data-id="reference">135176</div><div class="col border-left" data-id="lmh">H-H</div><div class="col border-left" data-id="length">1200</div><div class="col border-left" data-id="width">800</div><div class="col" data-id="height">800</div></div>
$truckLength=<?= $truckLength ?><br>
$truckHeight=<?= $truckHeight ?><br>
<?= dump($data) ?>
