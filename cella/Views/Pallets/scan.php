<div class="modal" tabindex="-1" role="dialog" id="scanPallModal">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><?= lang('system.orders.supplier_scanmodal_title') ?></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <input type="text" class="form-control" id="scanPallModal_barcode" autofocus>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<script>
	$(function(){
		$(".btn-scanpallet").attr('onClick','scanPallModalShow()').removeAttr('aria-controls');
	});
	
	$('#scanPallModal').on('shown.bs.modal', function() {
  		$('#scanPallModal_barcode').focus();
	})
	
	$("#id_scanpallet").on("click",function(){
		scanPallModalShow();
	});
	
	$(".btn-scanpallet").on("click",function(){
		scanPallModalShow();
	});
		
	$("#id_newpallet").on("click",function(){
		window.location='<?= !empty($new_link) ? $new_link : ''  ?>';
	});
	
	$("#scanPallModal_barcode").on("change",function(){
		var link='<?= !empty($scan_link) ? $scan_link : url('Pallets','pallet',['-id-'],['refurl'=>current_url(FALSE,TRUE)]) ?>';
		link=link.replace("-id-",$(this).val());
		window.location=link;
	});
	
	function scanPallModalShow(){
		$("#scanPallModal").modal('show');
	}
</script>