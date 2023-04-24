<?php foreach ($fields as $field):?>
	<?php if ($field['label']=='@hidden'): ?>
		<?= $field['value']; ?>
	<?php else : ?>
		<div class="form-group<?= !empty($_form_fields_group_class) ? ' '.$_form_fields_group_class : null ?><?= ((array_key_exists('type', $field['args']) &&  $field['args']['type']=='hidden') || (array_key_exists('data-mode', $field['args']) &&  $field['args']['data-mode']=='hidden')) ? ' d-none' : null ?>" id="<?= array_key_exists('id', $field['args']) ? $field['args']['id'].'_field' : 'id_'.$field['name'].'_field'  ?>">
    		<label for="<?= array_key_exists('id', $field['args']) ? $field['args']['id'] : 'id_'.$field['name']  ?>" class="mr-2">
    			<?= lang($field['label']) ?>
    			<?php if (array_key_exists('required', $field['args'])) : ?>
    			<b class="text-danger">*</b>
    			<?php endif ?>
    		</label>
   	 		<?= $field['value'] ?>
    		<small id="<?= array_key_exists('id', $field['args']) ? $field['args']['id'].'_tooltip' : 'id_'.$field['name'].'_tooltip'  ?>" class="form-text text-muted">
    			<?php $_tooltip=array_key_exists('tooltip', $field['args']) ? lang($field['args']['tooltip']) : lang($field['label'].'_tooltip'); ?>
    			<?= $_tooltip==$field['label'].'_tooltip' ? '' : $_tooltip ?>
    		</small>
 		</div>
 	<?php endif ?>
<?php endforeach;?>