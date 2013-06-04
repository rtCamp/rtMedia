<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of RTMediaMedia
 *
 * @author udit
 */
class RTMediaMedia {
	//put your code here
	
	function delete($post_id){
        $model = new BPMediaModel();
        $model->delete($post_id);
    }
}

?>
