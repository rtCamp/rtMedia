<?php

/**
 * Description of BPMediaSupport
 *
 * @author Gagandeep Singh <gagandeep.singh@rtcamp.com>
 * @author Joshua Abenazer <joshua.abenazer@rtcamp.com>
 */
if (!class_exists('BPMediaSupport')) {

    class BPMediaSupport {

        public function __construct() {
            ;
        }
        
        public function get_form( $form ) {
            if ( empty($form) )
                $form = (isset($_POST['form'])) ? $_POST['form'] : '';
            bp_media_bug_report_form($form);
        }

    }

}
?>
