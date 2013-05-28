<?php
/**
 * Description of BPMediaDelete
 *
 * @author joshua
 */
class BPMediaDelete {
    public function __construct() {
        add_action('delete_attachment',array($this,'delete_row'));
    }
    
    function delete_row($post_id){
        $model = new BPMediaModel();
        $model->delete($post_id);
    }
}

?>
