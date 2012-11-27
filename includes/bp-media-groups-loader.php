<?php

global $bp_media_group_sub_nav;
$bp_media_group_sub_nav = array();

class BP_Media_Groups_Extension extends BP_Group_Extension {

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
		bp_media_groups_display_screen();
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
bp_register_group_extension( 'BP_Media_Groups_Extension' );

function bp_media_groups_custom_nav(){
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
add_action('bp_actions','bp_media_groups_custom_nav',999);

/**
 * This loop creates dummy classes for images, videos, audio and albums so that the url structuring
 * can be uniform as it is in the members section.
 */
foreach(array('IMAGES','VIDEOS','AUDIO','ALBUMS') as $item){
	eval('
		class BP_Media_Group_Extension_'.constant('BP_MEDIA_'.$item.'_SLUG').' extends BP_Group_Extension{
			function __construct(){
				$this->name = BP_MEDIA_'.$item.'_LABEL;
				$this->slug = BP_MEDIA_'.$item.'_SLUG;
				$enable_create_step = false;
				$enable_nav_item = false;
				$enable_edit_item = false;
			}
			function display(){bp_media_groups_display_screen();}
			function widget_display(){}
			function edit_screen_save(){}
			function create_screen_save(){}
		}
		bp_register_group_extension("BP_Media_Group_Extension_'.constant('BP_MEDIA_'.$item.'_SLUG').'" );
	');
}

function bp_media_groups_display_screen(){
	global $bp_media_group_sub_nav,$bp;
	bp_media_groups_display_navigation_menu();
	if(isset($bp->action_variables[0])){
		switch($bp->action_variables[0]){

		}
	}
	else{
		bp_media_upload_screen_content();
	}
}

/**
 * Displays the navigation available to the group media tab for the
 * logged in user.
 *
 * @uses $bp Global Variable set by BuddyPress
 */
function bp_media_groups_display_navigation_menu(){
	global $bp;

	if(!isset($bp->current_action)||$bp->current_action!=BP_MEDIA_SLUG)
		return false;
	$current_tab = bp_media_groups_can_upload()?BP_MEDIA_UPLOAD_SLUG:BP_MEDIA_IMAGES_SLUG;
	if(isset($bp->action_variables[0])){
		$current_tab = $bp->action_variables[0];
	}

	/** This variable will be used to display the tabs in group component */
	$bp_media_group_tabs = apply_filters('bp_media_group_tabs', array(
		BP_MEDIA_UPLOAD_SLUG => array(
			'url'	=> trailingslashit(bp_get_group_permalink( $bp->groups->current_group )).BP_MEDIA_SLUG,
			'label'	=>	BP_MEDIA_UPLOAD_LABEL,
		),
		BP_MEDIA_IMAGES_SLUG => array(
			'url'	=> trailingslashit(bp_get_group_permalink( $bp->groups->current_group )).BP_MEDIA_IMAGES_SLUG,
			'label'	=>	BP_MEDIA_IMAGES_LABEL,
		),
		BP_MEDIA_VIDEOS_SLUG =>	array(
			'url'	=> trailingslashit(bp_get_group_permalink( $bp->groups->current_group )).BP_MEDIA_VIDEOS_SLUG,
			'label'	=>	BP_MEDIA_VIDEOS_LABEL,
		),
		BP_MEDIA_AUDIO_SLUG => array(
			'url'	=> trailingslashit(bp_get_group_permalink( $bp->groups->current_group )).BP_MEDIA_AUDIO_SLUG,
			'label'	=>	BP_MEDIA_AUDIO_LABEL,
		),
		BP_MEDIA_ALBUMS_SLUG => array(
			'url'	=> trailingslashit(bp_get_group_permalink( $bp->groups->current_group )).BP_MEDIA_ALBUMS_SLUG,
			'label'	=>	BP_MEDIA_ALBUMS_LABEL,
		),
	),$current_tab);
	?>
	<div class="item-list-tabs no-ajax bp-media-group-navigation" id="subnav">
		<ul>
			<?php
				foreach($bp_media_group_tabs as $tab_slug=>$tab_info){
					echo '<li id="'.$tab_slug.'-group-li" '.($current_tab==$tab_slug?'class="current selected"':'').'><a id="'.$tab_slug.'" href="'.$tab_info['url'].'" title="'.$tab_info['label'].'">'.$tab_info['label'].'</a></li>';
				}
			?>
		</ul>
	</div>
	<?php
}

/**
 * Checks whether the current logged in user has the ability to upload on
 * the given group or not
 *
 *
 */
function bp_media_groups_can_upload(){
	/** @todo Implementation Pending */
	return true;
}
?>