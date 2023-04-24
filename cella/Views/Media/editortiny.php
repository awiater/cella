const vcmsFileEditor<?= !empty($editorid) ? '_'.$editorid : '' ?> = new tinymceElfinder({
    // connector URL (Set your connector)
    url: '<?= !empty($connecturl) ? $connecturl :''  ?>',
    lang:'<?= !empty($language) ? $language :''?>',
    // upload target folder hash for this tinyMCE
    <?php if (!empty($dir)) : ?>
    startPathHash:'<?= $dir ?>',  
    uploadTargetHash: '<?= $dir ?>',
    <?php else : ?>
    uploadTargetHash: 'l1_lw',
    <?php endif ?>
    // elFinder dialog node id
    nodeId: 'elfinder', // Any ID you decide
    uiOptions:{
    	toolbar:JSON.parse(atob('<?= $toolbar ?>')),
    },
    <?php if (!empty($onlyMimes)) : ?>
    onlyMimes:JSON.parse(atob('<?= $onlyMimes ?>')),
    <?php endif ?>
    ui:['toolbar'],
    rememberLastDir:false,
    width:<?= $_ismobile ? '350' : '500' ?>,
    height:400,
    win_title:'<?= lang('cms.general.tiny_mce.file_editor_title') ?>',
});