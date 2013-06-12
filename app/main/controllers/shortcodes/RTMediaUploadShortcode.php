<?php

/**
 * Description of BPMediaUploadShortcode
 *
 * @author joshua
 */
class RTMediaUploadShortcode {

    var $add_sc_script = false;
	var $deprecated = false;

    public function __construct() {
		
        add_shortcode('rtmedia_uploader', array($this, 'pre_render'));
		$method_name = strtolower(str_replace('RTMedia', '', __CLASS__));

		if(is_callable("RTMediaDeprecated::{$method_name}",true, $callable_name)){
			$this->deprecated=RTMediaDeprecated::$method_name();
		}
		
        // add_action('init', array($this, 'register_script'));
        //add_action('wp_footer', array($this, 'print_script'));
    }

    function pre_render($attr) {

		if( !(is_home() || is_post_type_archive()) ) {
			$this->add_sc_script = true;
			RTMediaUploadTemplate::render($attr);
		}
    }
	
	
	
	

    

}

?>
