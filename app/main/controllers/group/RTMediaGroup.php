<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 * 
 */
class RTMediaGroup{
    public $create_slug = "media-setting";
    function __construct() {
        if(bp_is_active("groups")){
            bp_register_group_extension("RTMediaGroupExtension");
        }
        
    }
  
}
