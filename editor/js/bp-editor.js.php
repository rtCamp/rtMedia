<?php
$root = dirname(__FILE__);
while(!file_exists($root.'/wp-load.php')){
	if($root == dirname($root)){
		echo "WTF is WordPress???"; exit;
	}
	$root = dirname($root);
}

//Load wordpress
define('WP_USE_THEMES', false);
require_once($root.'/wp-load.php');
?>

//
function addslashes(str) {
	str=str.replace(/\'/g,'\\\'');
	str=str.replace(/\"/g,'\\"');
	str=str.replace(/\\/g,'\\\\');
	str=str.replace(/\0/g,'\\0');
	return str;
}


//function to insert codes into post-editor
function insert_into_post(code){
	//embed codes	 	 
	var eview = top.document.getElementById('edButtonHTML').className;
	
	if ( eview == 'active' ){
		top.send_to_editor ( code );
	}else{
		top.switchEditors.go('content');
		top.send_to_editor ( code );
		top.switchEditors.go('content');
	}			
	top.tb_remove();
}


jQuery(document).ready(function() {

	//tabs
	jQuery("#media-upload-header").tabs( { 
		ajaxOptions: { 
				beforeSend: function(obj) { 
					jQuery('#kaltura_lib').html('<img id="loading" src="<?php echo WP_PLUGIN_URL . "/" . basename(dirname(dirname(dirname(__FILE__)))); ?>/editor/img/loading.gif">'); 
						},//end of beforeSend
				load: function(event,ui){
					alert('test');
				}		
			}//end of ajaxOptions 									
	});//end of tabs	
	
	
});//end of document-ready