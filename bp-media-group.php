<?php
/**
 * Custom Group extension
 */
class BP_Media_Group_Extension extends BP_Group_Extension {

	function  __construct() {
		// required
		$this->name = sprintf( __( 'Media', 'buddypress-media' ));
		$this->slug = BP_MEDIA_SLUG;
		// optional
		$this->visibility  = 'private';
		$this->enable_create_step  = false;
		$this->enable_edit_item  = false;
	}

	function display() {
		global $bp;

                if ( 'upload' == $bp->action_variables[0] ) {
			// load create group media template
			bp_media_locate_template( array( 'groups/single/media-upload.php' ), true );
                        
		} else {
			// load group media list template
			bp_media_locate_template( array( 'groups/single/media-list.php' ), true );
		}
	}

//	function widget_display() {
//		return;
//	}
}
bp_register_group_extension( 'BP_Media_Group_Extension' );

?>
