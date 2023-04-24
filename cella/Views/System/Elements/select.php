<?php $argStr=[]; ?>

<?php if(!empty($args['selectwithicons'])) :?>
	<?php foreach ($args as $key=>$arg) :?>
		<?php if($key!='name' && $key!='selectwithicons') :?>
		<?php $argStr[]=$key.'="'.$arg.'"'; ?>
		<?php endif ?>
	<?php endforeach ?>
	<div class="btn-group w-100">
		<a class="btn d-flex border h-100" data-toggle="dropdown" href="#" <?= implode(' ',$argStr) ?>>
    		<span id="<?= $args['id'] ?>_text"><?= array_key_exists($value, $options) ? '<i class="'.$options[$value]['icon'].' mr-1"'.(array_key_exists('icon_color', $options[$value]) ? ' style="color:'.$options[$value]['icon_color'].'"' : '').'></i>'.$options[$value]['value']: '&nbsp;' ?></span>
    		<span class="ml-auto d-inline-block" id="<?= $args['id'] ?>_arrow">
    			<i class="fas fa-angle-down align-middle"></i>
    		</span>
  		</a>
  		<ul class="dropdown-menu w-100">
  			<?php foreach ($options as $key=>$value) :?>
  			<li class="dropdown-item p-0">
  				<button type="button" class="btn w-100 text-left" onClick="set_<?=$args['id'] ?>_text($(this),'<?= $key ?>')">
  					<i class="<?= $value['icon'] ?> mr-1"<?= array_key_exists('icon_color', $value) ? ' style="color:'.$value['icon_color'].'"' : ''?>></i><?= $value['value'] ?>
  				</button>
  			</li>
  			<?php endforeach ?>
  		</ul>
  		<?= form_input(['type'=>'hidden','name'=>$args['name'],'id'=>$args['id'].'_value']) ?>
	</div>
	<script>
		function set_<?=$args['id'] ?>_attr(value,readonly=0,custFunc=null){
			var options=JSON.parse(atob('<?= base64_encode(json_encode($options))?>'));
			$("#<?= $args['id'] ?>_text").html('<i class="'+options[value]['icon']+' mr-1"></i>'+options[value]['value']);
			$("#<?= $args['id'] ?>_value").val(value);
			if (readonly==1 || readonly=='1'){
				$("#<?= $args['id'] ?>_arrow").removeClass('d-inline-block').addClass('d-none');
				$('#<?= $args['id'] ?>').removeAttr('data-toggle').attr('style','cursor:default;');
			}
			if (custFunc!=null && (typeof(custFunc)=='function')){
				custFunc($("#<?= $args['id']?>"),value);
			}
		}
		function set_<?=$args['id'] ?>_text(button,key){
			$('#<?= $args['id'] ?>_text').html(button.html());
			$("#<?= $args['id'] ?>_value").val(key);
		}
	</script>
<?php else :?>
<?php foreach ($args as $key=>$arg) :?>
	<?php $argStr[]=$key.'="'.$arg.'"'; ?>
<?php endforeach ?>	
<select <?= implode(' ',$argStr) ?>>
  <?php foreach ($options as $key=>$val) :?>
  	<option value="<?= $key ?>" <?= $key==$value ? 'selected="true"' : '' ?>><?= $val ?></option>
 <?php endforeach ?>
</select>
<?php endif ?>
</script>