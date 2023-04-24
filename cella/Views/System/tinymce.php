<?php if (!empty($connector) && !empty($toolbar) && ($tinytoolbar!='simple')) : ?>
 <!-- inlucde tiny editor -->
<?php endif  ?>

function tinyMceEditorInit(){
tinymce.init({
	selector: 'textarea<?= $id ?>',
	language : '<?= $language ?>',
  	plugins: 'print preview paste importcss searchreplace autolink directionality save code visualblocks visualchars fullscreen image link media template codesample table charmap hr pagebreak nonbreaking anchor toc insertdatetime advlist lists wordcount imagetools textpattern noneditable help charmap <?= $tinytoolbar=='full' ? ' quickbars ' : ''  ?>emoticons',
  	menubar: false,
  	<?php if ($tinytoolbar=='simple') : ?>
  	toolbar: 'undo redo | bold italic underline strikethrough | fontselect fontsizeselect | alignleft aligncenter alignright alignjustify | outdent indent',
  	<?php elseif ($tinytoolbar=='email') : ?>
  	toolbar:' undo redo | bold italic underline strikethrough | fontselect fontsizeselect formatselect | alignleft aligncenter alignright alignjustify | numlist bullist  | currentdate | forecolor backcolor removeformat | charmap emoticons | image media link',
  	<?php elseif ($tinytoolbar=='emailext') : ?>
  	toolbar:'  undo redo | bold italic underline strikethrough | fontselect fontsizeselect formatselect | alignleft aligncenter alignright alignjustify | numlist bullist  | currentdate | forecolor backcolor removeformat | charmap emoticons | image media link | code preview | pagemanager fontawesome',
  	<?php elseif ($tinytoolbar=='full'):  ?>
  	toolbar:' undo redo | bold italic underline strikethrough | fontselect fontsizeselect formatselect | alignleft aligncenter alignright alignjustify | outdent indent |  numlist bullist  | currentdate | forecolor backcolor removeformat | pagebreak | charmap emoticons | fullscreen  preview print | insertfile image media link anchor codesample | ltr rtl | code  | pagemanager customInsertButton',
  	<?php elseif ($tinytoolbar=='basic') : ?>
  	toolbar:'bold italic underline strikethrough | emoticons',
  	<?php else : ?>
  	toolbar:'<?= $tinytoolbar ?>',
  	<?php endif ?>
  	toolbar_sticky: true,
  	image_advtab: true,
	height:'<?= $height ?>',
  	image_caption: true,
  	quickbars_selection_toolbar: 'bold italic | quicklink h2 h3 blockquote quickimage',
  	noneditable_noneditable_class: "mceNonEditable",
  	toolbar_mode: 'sliding',
  	contextmenu: '',//"link image imagetools table",
  	branding: false,
  	<?php if (!empty($connector)  && !empty($toolbar) && $tinytoolbar!='simple') : ?>
  	file_picker_callback : vcmsFileEditor<?= !empty($editorid) ? '_'.$editorid : '' ?>.browser,
  	<?php endif  ?>
  	setup: function (editor) {
  		
    	editor.on('KeyUp change',function(e){
    		if (typeof tinyMceEditorChange == 'function') {
         		tinyMceEditorChange(editor.getContent());
         	}
         });

    	<?= !empty($setup) ? $setup : null ?>        
	}
});
}
tinyMceEditorInit();
