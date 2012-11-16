<?php

global $bp_media_group_sub_nav;
$bp_media_group_sub_nav = array();

class BP_Media_Group_Extension extends BP_Group_Extension {

    function __construct() {
		global $bp;
        $this->name = BP_MEDIA_LABEL;
        $this->slug = BP_MEDIA_SLUG;
        $this->create_step_position = 21;
        $this->nav_item_position = 31;
    }

    function create_screen() {
        if ( !bp_is_group_creation_step( $this->slug ) )
            return false;
        ?>

        <p>The HTML for my creation step goes here.</p>

        <?php
        wp_nonce_field( 'groups_create_save_' . $this->slug );
    }

    function create_screen_save() {
        global $bp;

        check_admin_referer( 'groups_create_save_' . $this->slug );

        /* Save any details submitted here */
        groups_update_groupmeta( $bp->groups->new_group_id, 'my_meta_name', 'value' );
    }

    function edit_screen() {
        if ( !bp_is_group_admin_screen( $this->slug ) )
            return false; ?>

        <h2><?php echo esc_attr( $this->name ) ?></h2>

        <p>Edit steps here</p>
        <input type=&quot;submit&quot; name=&quot;save&quot; value=&quot;Save&quot; />

        <?php
        wp_nonce_field( 'groups_edit_save_' . $this->slug );
    }

    function edit_screen_save() {
        global $bp;

        if ( !isset( $_POST['save'] ) )
            return false;

        check_admin_referer( 'groups_edit_save_' . $this->slug );

        /* Insert your edit screen save code here */

        /* To post an error/success message to the screen, use the following */
        if ( !$success )
            bp_core_add_message( __( 'There was an error saving, please try again', 'buddypress' ), 'error' );
        else
            bp_core_add_message( __( 'Settings saved successfully', 'buddypress' ) );

        bp_core_redirect( bp_get_group_permalink( $bp->groups->current_group ) . '/admin/' . $this->slug );
    }

    function display() {
		global $bp_media_group_sub_nav;
		?>

        <div class="item-list-tabs no-ajax checkins-type-tabs" id="subnav">
			<ul>
				<li><a>Photos</a></li>
				<li><a>Videos</a></li>
				<li><a>Music</a></li>
			</ul>
		</div>
		<?php
    }

    function widget_display() { ?>
        <div class="info-group">
            <h4><?php echo esc_attr( $this->name ) ?></h4>
            <p>
                You could display a small snippet of information from your group extension here. It will show on the group
                home screen.
            </p>
        </div>
        <?php
    }
}
bp_register_group_extension( 'BP_Media_Group_Extension' );

function bp_media_group_custom_nav(){
	global $bp;
	$current_group = isset($bp->groups->current_group->slug)?$bp->groups->current_group->slug:null;

	if(!$current_group)
		return;
	if(!(isset($bp->bp_options_nav[$current_group])&&  is_array($bp->bp_options_nav[$current_group])))
		return;
	foreach ($bp->bp_options_nav[$current_group] as $key => $nav_item) {
		switch($nav_item['slug']){
			case BP_MEDIA_IMAGES_SLUG:
			case BP_MEDIA_VIDEOS_SLUG:
			case BP_MEDIA_AUDIO_SLUG:
			case BP_MEDIA_ALBUMS_SLUG:
				unset($bp->bp_options_nav[$current_group][$key]);
		}
		switch($bp->current_action){
			case BP_MEDIA_IMAGES_SLUG:
			case BP_MEDIA_VIDEOS_SLUG:
			case BP_MEDIA_AUDIO_SLUG:
			case BP_MEDIA_ALBUMS_SLUG:
				$count = count($bp->action_variables);
				for ($i = $count; $i > 0; $i--) {
					$bp->action_variables[$i] = $bp->action_variables[$i - 1];
				}
				$bp->action_variables[0] = $bp->current_action;
				$bp->current_action = BP_MEDIA_SLUG;
		}
	}
}
add_action('bp_actions','bp_media_group_custom_nav',999);

foreach(array('IMAGES','VIDEOS','AUDIO','ALBUMS') as $item){
	eval('
		class BP_Media_Group_Extension_'.constant('BP_MEDIA_'.$item.'_SLUG').' extends BP_Group_Extension{
			function __construct(){
				$this->name = BP_MEDIA_'.$item.'_LABEL;
				$this->slug = BP_MEDIA_'.$item.'_SLUG;
				$this->create_step_position = 21;
				$this->nav_item_position = 31;
				$enable_create_step = false;
				$enable_nav_item = false;
				$enable_edit_item = false;
			}
			function widget_display(){}
			function edit_screen_save(){}
			function create_screen_save(){}
		}
		bp_register_group_extension("BP_Media_Group_Extension_'.constant('BP_MEDIA_'.$item.'_SLUG').'" );
	');
}
?>