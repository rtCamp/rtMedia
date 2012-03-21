<?php
/*
Plugin Name: BuddyPress-Kaltura Media Component with KalturaCE Support
Plugin URI: http://www.wpveda.com/buddypress-kaltura-media-component/
Description: This BuddyPress-Kaltura media component adds features like photos, videos and audio upload to BuddyPress.
Version: 1.0
Revision Date: 7 Nov 2009
Author: rtCamp, Kapil Gonge
Email-id : kapil.gonge@rtcamp.com
Author URI: http://rtcamp.com
Site Wide Only: true
*/

/*
 * Kaltura API Library Version 3 is used in this plugin.
 * API library available @ http://kaltura.com
 */


 /*Define a constant that can be checked to see if the component is installed or not. */
define ( 'RT_MEDIA_IS_INSTALLED', '1' );
/* Define a constant that will hold the current version number of the component */
define ( 'RT_MEDIA_VERSION', '1.0' );
define ( 'RT_MEDIA_DB_VERSION', '1' );
wp_enqueue_script('jquery');
wp_enqueue_script('thickbox');

/* Define a slug constant that will be used to view this components pages (http://example.org/SLUG) */
if ( !defined( 'RT_MEDIA_SLUG' ) )
    define ( 'RT_MEDIA_SLUG', 'media' );
if ( file_exists( WP_PLUGIN_DIR . '/rt-bp-kaltura-plugin/languages/' . get_locale() . '.mo' ) )
    load_textdomain( 'rt-bp-kaltura', WP_PLUGIN_DIR . '/rt-bp-kaltura-plugin/languages/' . get_locale() . '.mo' );

/* The classes file should hold all database access classes and functions */
require ( WP_PLUGIN_DIR . '/rt-bp-kaltura-plugin/rt-bp-kaltura-classes.php' );

/* The ajax file should hold all functions used in AJAX queries */
require ( WP_PLUGIN_DIR . '/rt-bp-kaltura-plugin/rt-bp-kaltura-ajax.php' );

/* The cssjs file should set up and enqueue all CSS and JS files used by the component */
require ( WP_PLUGIN_DIR . '/rt-bp-kaltura-plugin/rt-bp-kaltura-cssjs.php' );

/* The templatetags file should contain classes and functions designed for use in template files */
require ( WP_PLUGIN_DIR . '/rt-bp-kaltura-plugin/rt-bp-kaltura-templatetags.php' );

/* The widgets file should contain code to create and register widgets for the component */
require ( WP_PLUGIN_DIR . '/rt-bp-kaltura-plugin/rt-bp-kaltura-widgets.php' );

/* The notifications file should contain functions to send email notifications on specific user actions */
require ( WP_PLUGIN_DIR . '/rt-bp-kaltura-plugin/rt-bp-kaltura-notifications.php' );

/* The filters file should create and apply filters to component output functions. */
require ( WP_PLUGIN_DIR . '/rt-bp-kaltura-plugin/rt-bp-kaltura-filters.php' );

/* Kaltura API is included here. */
require ( WP_PLUGIN_DIR . '/rt-bp-kaltura-plugin/lib-kaltura/KalturaClient.php' );

require ( WP_PLUGIN_DIR . '/rt-bp-kaltura-plugin/rt-bp-kaltura-admin.php' );

/* Checks whether component is installed or not */
function rt_media_install() {
    global $wpdb, $bp;

    if ( !empty($wpdb->charset) )
        $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";

    $table_name = $bp->rt_media->table_name;
    if ($wpdb->get_var("show tables like '$table_name'") != $table_name) {
        $sql = "CREATE TABLE " . $table_name . " (
			rt_content_id VARCHAR(30) NOT NULL,
			rt_content_type INT(20) NOT NULL,
            rt_user_id BIGINT (20) NOT NULL,
            rt_created_at datetime
		);";
    }
    require_once( ABSPATH . 'wp-admin/upgrade-functions.php' );
    dbDelta($sql);
    update_site_option( 'rt-bp-kaltura-db-version', RT_MEDIA_DB_VERSION );

}

/* Adds Setting Link @ admin after installtion of plugin*/
function rt_media_actions($links, $file) {
// adds the link to the settings page in the plugin list page
    if ($file == plugin_basename(dirname(__FILE__).'/rt-bp-kaltura.php')) {
        $settings_link = "<a href='options-general.php?page=rt_media_options'>" . __('Settings') . "</a>";
        array_unshift( $links, $settings_link );
    }
    return $links;
}
add_filter( 'plugin_action_links', 'rt_media_actions', -10, 2);

/**
 * rt_media_setup_globals()
 *
 * Sets up global variables for your component.
 */
function rt_media_setup_globals() {
    global $bp, $wpdb;
    $bp->rt_media->table_name = $wpdb->base_prefix . 'bp_rt_media';
    $bp->rt_media->slug = RT_MEDIA_SLUG;
    $bp->version_numbers->example = RT_MEDIA_VERSION;
}


add_action( 'plugins_loaded', 'rt_media_setup_globals', 5 );
add_action( 'admin_menu', 'rt_media_setup_globals', 1 );

/**
 * rt_media_check_installed()
 *
 * Checks to see if the DB tables exist or if you are running an old version
 * of the component. If it matches, it will run the installation function.
 */
function rt_media_check_installed() {
    global $wpdb, $bp;

    if ( !is_site_admin() )
        return false;
	/***
	 * If you call your admin functionality here, it will only be loaded when the user is in the
	 * wp-admin area, not on every page load.
	 */

	/* Need to check db tables exist, activate hook no-worky in mu-plugins folder. */
    if ( get_site_option('rt-bp-kaltura-db-version') < RT_MEDIA_DB_VERSION )
    //let it go directly to following function
        rt_media_install();
}
add_action( 'admin_menu', 'rt_media_check_installed' );

/**
 * rt_media_setup_nav()
 *
 * Sets up the navigation items for the component. This adds the top level nav
 * item and all the sub level nav items to the navigation array. This is then
 * rendered in the template.
 */

add_action('admin_menu', 'rt_media_add_menu');

function rt_media_setup_nav() {
    global $bp;
    /* Add 'Media' to the main navigation */
    bp_core_add_nav_item(
        __( 'Media', 'rt-media' ), /* The display name */
        $bp->rt_media->slug /* The slug */
    );

/* Set a specific sub nav item as the default when the top level item is clicked */
    bp_core_add_nav_default(
        $bp->rt_media->slug, /* The slug of the parent nav item */
        'rt_media_photo', /* The function to run when clicked */
        'photo' /* The slug of the sub nav item to make default */
    );

    $media_link = $bp->loggedin_user->domain . $bp->rt_media->slug . '/';

	/* Create two sub nav items for this component */
    bp_core_add_subnav_item(
        $bp->rt_media->slug, /* The slug of the parent */
        'photo', /* The slug for the sub nav item */
        __( 'Photo', 'rt-media' ), /* The display name for the sub nav item */
        $media_link, /* The URL of the parent */
        'rt_media_photo' /* The function to run when clicked */
    );

    bp_core_add_subnav_item(
        $bp->rt_media->slug,
        'video',
        __( 'Video', 'rt-media' ),
        $media_link,
        'rt_media_video',
        false /* We don't need to set a custom css ID for this sub nav item */
        //bp_is_home() /* We DO want to restrict only the logged in user to this sub nav item */
    );
    bp_core_add_subnav_item(
        $bp->rt_media->slug,
        'audio',
        __( 'Audio', 'rt-media' ),
        $media_link,
        'rt_media_screen_audio',
        false /* We don't need to set a custom css ID for this sub nav item */
        //bp_is_home() /* We DO want to restrict only the logged in user to this sub nav item */
    );
    bp_core_add_subnav_item(
        $bp->rt_media->slug,
        'upload',
        __( 'Upload', 'rt-media' ),
        $media_link,
        'rt_media_screen_upload',
        false, /* We don't need to set a custom css ID for this sub nav item */
        bp_is_home() /* We DO want to restrict only the logged in user to this sub nav item */
    );

	/* Add a nav item for this component under the settings nav item. See rt_media_screen_settings_menu() for more info */
//    bp_core_add_subnav_item( 'settings', 'media-admin', __( 'Media', 'rt-media' ), $bp->loggedin_user->domain . 'settings/', 'rt_media_screen_settings_menu', false, bp_is_home() );

	/* Only execute the following code if we are actually viewing this component  */
    if ( $bp->current_component == $bp->rt_media->slug ) {
        if ( bp_is_home() ) {
			/* If the user is viewing their own profile area set the title to "My Media" */
            $bp->bp_options_title = __( 'My Media', 'rt-media' );
        } else {
			/* If the user is viewing someone elses profile area, set the title to "[user fullname]" */
            $bp->bp_options_avatar = bp_core_get_avatar( $bp->displayed_user->id, 1 );
            $bp->bp_options_title = $bp->displayed_user->fullname;
        }
    }
}
add_action( 'wp', 'rt_media_setup_nav', 2 );
add_action( 'admin_menu', 'rt_media_setup_nav', 2 );


/**
 * rt_media_photo()
 *
 * Sets up and displays the screen output for the sub nav item "media/photo"
 * which is in bp_member theme
 */
function rt_media_photo() {
    global $bp;
    global $blog_name,$name, $email, $web_site_url, $description, $phone_number, $content_category, $adult_content, $agree_to_terms;

    bp_core_load_template( 'rt-bp-kaltura-theme/photo' );
}

function rt_media_photo_content() {
    global $bp;
    ?>
    <?php do_action( 'template_notices' ) // (error/success feedback) ?>
<?php
}

/**
 * rt_media_video()
 *
 * Sets up and displays the screen output for the sub nav item "example/video"
 */
function rt_media_video() {
    global $bp;
    //    global $post_data;
    //	if ( isset( $_POST['submit123'] )) {
    //        echo "value entered is  = ".$_POST['test'];
    //        $post_data = $_POST['test'];
    //    }
    bp_core_load_template( 'rt-bp-kaltura-theme/video' );

}

function rt_media_video_header() {
    _e( 'Video Header', 'rt-media' );
}

function rt_media_video_title() {
    _e( 'Video', 'rt-media' );
}

function rt_media_video_content() {
    global $bp; ?>

    <?php do_action( 'template_notices' ) ?>
    <?php
/********** Video List kaltura api starts here **********/
    //check rt_kaltura partner id available or not
    if(rt_check_partner_id()) {
    //display video contents

    }
    else {
        rt_get_partner_id();
    }



    ?>
<?php
}
function rt_check_partner_id() {
    if(!get_site_option('kaltura_partner_id')) {
        echo  "Partner ID not available";
        return false;
    }
    else
        return true;
}
function rt_get_partner_id() {
//write script to get partner id
    ?>
<div class="wrap">
    <h2><?php _e('All in One Video Pack Installation'); ?></h2>
    <p>
        Please enter your Kaltura Management Console (KMC) Email & password
    </p>
    <form name="form1" method="post" />
    <table>
        <tr>
            <td><?php _e("Partner ID"); ?>:</td>
            <td><input type="text" id="partner_id" name="partner_id" value=""/></td>
        </tr>
        <tr>
            <td><?php _e("Email"); ?>:</td>
            <td><input type="text" id="email" name="email" value=""/></td>
        </tr>
        <tr>
            <td><?php _e("Password"); ?>:</td>
            <td><input type="password" id="password" name="password" value="" size="20" /> </td>
        </tr>
    </table>

    <p class="submit" style="text-align: left; "><input type="submit" name="Submit" value="<?php _e('Complete installation') ?>" /></p>

    <input type="hidden" name="is_postback" value="postback" />
</form>
</div>
<?php
}

function rt_media_screen_audio() {
    global $bp;
    bp_core_load_template( 'rt-bp-kaltura-theme/audio' );
}

function rt_media_screen_upload() {
    global $bp, $post_data;
    if ( isset( $_POST['submit123'] )) {
        $post_data = $_POST['test'];
    }
    bp_core_load_template( 'rt-bp-kaltura-theme/upload' );
}

function rt_media_screen_settings_menu() {
    global $bp, $current_user, $bp_settings_updated, $pass_error;

    if ( isset( $_POST['submit'] ) && check_admin_referer('rt-media-admin') ) {
        $bp_settings_updated = true;

        /**
         * This is when the user has hit the save button on their settings.
         * The best place to store these settings is in wp_usermeta.
         */
        update_usermeta( $bp->loggedin_user->id, 'rt-media-option-one', attribute_escape( $_POST['rt-media-option-one'] ) );
    }

//    add_action( 'bp_template_content_header', 'rt_media_screen_settings_menu_header' );
//    add_action( 'bp_template_title', 'rt_media_screen_settings_menu_title' );
//    add_action( 'bp_template_content', 'rt_media_screen_settings_menu_content' );

    bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'plugin-template' ) );
}

function rt_media_screen_settings_menu_content() {
    global $bp, $bp_settings_updated; ?>
    <?php if ( $bp_settings_updated ) { ?>
<div id="message" class="updated fade">
    <p><?php _e( 'Changes Saved.', 'rt-media' ) ?></p>
</div>
    <?php } ?>

<form action="<?php echo $bp->loggedin_user->domain . 'settings/media-admin'; ?>" name="rt-media-admin-form" id="account-delete-form" class="rt-media-admin-form" method="post">

    <input type="checkbox" name="rt-media-option-one" id="rt-media-option-one" value="1"<?php if ( '1' == get_usermeta( $bp->loggedin_user->id, 'rt-media-option-one' ) ) : ?> checked="checked"<?php endif; ?> /> <?php _e( 'Do you love clicking checkboxes?', 'rt-media' ); ?>
    <p class="submit">
        <input type="submit" value="<?php _e( 'Save Settings', 'rt-media' ) ?> &raquo;" id="submit" name="submit" />
    </p>

        <?php
			/* This is very important, don't leave it out. */
        wp_nonce_field( 'rt-media-admin' );
        ?>

</form>
<?php
}

/**
 * rt_media_screen_notification_settings()
 * Adds notification settings for the component, so that a user can turn off email
 * notifications set on specific component actions.
 */
function rt_media_screen_notification_settings() {
    global $current_user;

    /**
     * Under Settings > Notifications within a users profile page they will see
     * settings to turn off notifications for each component.
     *
     * You can plug your custom notification settings into this page, so that when your
     * component is active, the user will see options to turn off notifications that are
     * specific to your component.
     */

    /**
     * Each option is stored in a posted array notifications[SETTING_NAME]
     * When saved, the SETTING_NAME is stored as usermeta for that user.
     *
     * For example, notifications[notification_friends_friendship_accepted] could be
     * used like this:
     *
     * if ( 'no' == get_usermeta( $bp['loggedin_userid], 'notification_friends_friendship_accepted' ) )
     *		// don't send the email notification
     *	else
     *		// send the email notification.
     */

    ?>
<table class="notification-settings" id="rt-media-notification-settings">
    <tr>
        <th class="icon"></th>
        <th class="title"><?php _e( 'Media', 'rt-media' ) ?></th>
        <th class="yes"><?php _e( 'Yes', 'rt-media' ) ?></th>
        <th class="no"><?php _e( 'No', 'rt-media' )?></th>
    </tr>
    <tr>
        <td></td>
        <td><?php _e( 'Action One', 'rt-media' ) ?></td>
        <td class="yes"><input type="radio" name="notifications[notification_example_action_one]" value="yes" <?php if ( !get_usermeta( $current_user->id,'notification_example_action_one') || 'yes' == get_usermeta( $current_user->id,'notification_example_action_one') ) { ?>checked="checked" <?php } ?>/></td>
        <td class="no"><input type="radio" name="notifications[notification_example_action_one]" value="no" <?php if ( get_usermeta( $current_user->id,'notification_example_action_one') == 'no' ) { ?>checked="checked" <?php } ?>/></td>
    </tr>
    <tr>
        <td></td>
        <td><?php _e( 'Action Two', 'rt-media' ) ?></td>
        <td class="yes"><input type="radio" name="notifications[notification_example_action_two]" value="yes" <?php if ( !get_usermeta( $current_user->id,'notification_example_action_two') || 'yes' == get_usermeta( $current_user->id,'notification_example_action_two') ) { ?>checked="checked" <?php } ?>/></td>
        <td class="no"><input type="radio" name="notifications[notification_example_action_two]" value="no" <?php if ( 'no' == get_usermeta( $current_user->id,'notification_example_action_two') ) { ?>checked="checked" <?php } ?>/></td>
    </tr>

        <?php do_action( 'rt_media_notification_settings' ); ?>
</table>
<?php
}
//add_action( 'bp_notification_settings', 'rt_media_screen_notification_settings' );

/**
 * rt_media_record_activity()
 *
 * If the activity stream component is installed, this function will record activity items for your
 * component.
 *
 * You must pass the function an associated array of arguments:
 *
 *     $args = array(
 *       'item_id' => The ID of the main piece of data being recorded, for example a group_id, user_id, forum_post_id
 *       'component_name' => The slug of the component.
 *       'component_action' => The action being carried out, for example 'new_friendship', 'joined_group'. You will use this to format activity.
 *		 'is_private' => Boolean. Should this not be shown publicly?
 *       'user_id' => The user_id of the person you are recording this activity stream item for.
 *		 'secondary_item_id' => (optional) If the activity is more complex you may need a second ID. For example a group forum post needs the group_id AND the forum_post_id.
 *       'secondary_user_id' => (optional) If this activity applies to two users, provide the second user_id. Eg, Andy and John are now friends should show on both users streams
 *		 'recorded_time' => (optional) The time you want to set as when the activity was carried out (defaults to now)
 *     )
 */
function rt_media_record_activity( $args ) {
    if ( function_exists('bp_activity_record') ) {
        extract( (array)$args );
        bp_activity_record( $item_id, $component_name, $component_action, $is_private, $secondary_item_id, $user_id, $secondary_user_id, $recorded_time );
    }
}

/**
 * rt_media_delete_activity()
 *
 * If the activity stream component is installed, this function will delete activity items for your
 * component.
 *
 * You should use this when items are deleted, to keep the activity stream in sync. For example if a user
 * publishes a new blog post, it would record it in the activity stream. However, if they then make it private
 * or they delete it. You'll want to remove it from the activity stream, otherwise you will get out of sync and
 * bad links.
 */
function rt_media_delete_activity( $args ) {
    if ( function_exists('bp_activity_delete') ) {
        extract( (array)$args );
        bp_activity_delete( $item_id, $component_name, $component_action, $user_id, $secondary_item_id );
    }
}

/**
 * rt_media_format_activity()
 *
 * Formatting your activity items is the other important step in adding your custom component activity into
 * activity streams.
 *
 * The rt_media_record_activity() function simply records ID's that are needed to fetch information about
 * the activity. The rt_media_format_activity() will take those ID's and make something that is human readable.
 *
 * You'll notice in the function rt_media_setup_globals() we set up a global called 'format_activity_function'.
 * This is the function name that the activity component will look at to format your component's activity when needed.
 *
 * This is where the 'component_action' variable set in rt_media_record_activity() comes into play. For each
 * one of those actions, you will need to define how that activity action is rendered.
 *
 * You do not have to call this function anywhere, or pass any parameters, the activity component will handle it.
 */
function rt_media_format_activity( $item_id, $user_id, $action, $secondary_item_id = false, $for_secondary_user = false ) {
    global $bp;

	/* $action is the 'component_action' variable set in the record function. */
    switch( $action ) {
        case 'accepted_terms':
			/* In this case, $item_id is the user ID of the user who accepted the terms. */
            $user_link = bp_core_get_userlink( $item_id );

            if ( !$user_link )
                return false;

			/***
			 * We return activity items as an array. The 'primary_link' is for RSS feeds, so when the reader clicks
			 * a new item header, it will go to this link (sometimes there is more than one link in an activity item).
			 */
            return array(
            'primary_link' => $user_link,
            'content' => apply_filters( 'rt_media_accepted_terms_activity', sprintf( __( '%s accepted the really exciting terms and conditions!', 'rt-media' ), $user_link ) . ' <span class="time-since">%s</span>', $user_link )
            );
            break;
        case 'rejected_terms':
            $user_link = bp_core_get_userlink( $item_id );

            if ( !$user_link )
                return false;

            return array(
            'primary_link' => $user_link,
            'content' => apply_filters( 'rt_media_rejected_terms_activity', sprintf( __( '%s rejected the really exciting terms and conditions.', 'rt-media' ), $user_link ) . ' <span class="time-since">%s</span>', $user_link )
            );
            break;
        case 'new_high_five':
			/* In this case, $item_id is the user ID of the user who recieved the high five. */
            $to_user_link = bp_core_get_userlink( $item_id );
            $from_user_link = bp_core_get_userlink( $user_id );

            if ( !$to_user_link || !$from_user_link )
                return false;

            return array(
            'primary_link' => $to_user_link,
            'content' => apply_filters( 'rt_media_new_high_five_activity', sprintf( __( '%s high-fived %s!', 'rt-media' ), $from_user_link, $to_user_link ) . ' <span class="time-since">%s</span>', $from_user_link, $to_user_link )
            );
            break;
    }

	/* By adding a do_action here, people can extend your component with new activity items. */
    do_action( 'rt_media_format_activity', $action, $item_id, $user_id, $action, $secondary_item_id, $for_secondary_user );

    return false;
}

/**
 * rt_media_format_notifications()
 *
 * Formatting notifications works in very much the same way as formatting activity items.
 *
 * These notifications are "screen" notifications, that is, they appear on the notifications menu
 * in the site wide navigation bar. They are not for email notifications.
 *
 * You do not need to make a specific notification recording function for your component because the
 * notification recorded functions are bundled in the core, which is required.
 *
 * The recording is done by using bp_core_add_notification() which you can search for in this file for
 * examples of usage.
 */
function rt_media_format_notifications( $action, $item_id, $secondary_item_id, $total_items ) {
    global $bp;

    switch ( $action ) {
        case 'new_high_five':
			/* In this case, $item_id is the user ID of the user who sent the high five. */

			/***
			 * We don't want a whole list of similar notifications in a users list, so we group them.
			 * If the user has more than one action from the same component, they are counted and the
			 * notification is rendered differently.
			 */
            if ( (int)$total_items > 1 ) {
                return apply_filters( 'rt_media_multiple_new_high_five_notification', '<a href="' . $bp->loggedin_user->domain . $bp->rt_media->slug . '/photo/" title="' . __( 'Multiple high-fives', 'rt-media' ) . '">' . sprintf( __( '%d new high-fives, multi-five!', 'rt-media' ), (int)$total_items ) . '</a>', $total_items );
            } else {
                $user_fullname = bp_core_get_user_displayname( $item_id, false );
                $user_url = bp_core_get_userurl( $item_id );
                return apply_filters( 'rt_media_single_new_high_five_notification', '<a href="' . $user_url . '?new" title="' . $user_fullname .'\'s profile">' . sprintf( __( '%s sent you a high-five!', 'rt-media' ), $user_fullname ) . '</a>', $user_fullname );
            }
            break;
    }

    do_action( 'rt_media_format_notifications', $action, $item_id, $secondary_item_id, $total_items );

    return false;
}


/***
 * From now on you will want to add your own functions that are specific to the component you are developing.
 * For example, in this section in the friends component, there would be functions like:
 *    friends_add_friend()
 *    friends_remove_friend()
 *    friends_check_friendship()
 *
 * Some guidelines:
 *    - Don't set up error messages in these functions, just return false if you hit a problem and
 *		deal with error messages in screen or action functions.
 *
 *    - Don't directly query the database in any of these functions. Use database access classes
 * 		or functions in your rt-media-classes.php file to fetch what you need. Spraying database
 * 		access all over your plugin turns into a maintainence nightmare, trust me.
 *
 *	  - Try to include add_action() functions within all of these functions. That way others will find it
 *		easy to extend your component without hacking it to pieces.
 */

/**
 * rt_media_accept_terms()
 *
 * Accepts the terms and conditions screen for the logged in user.
 * Records an activity stream item for the user.
 */
function rt_media_accept_terms() {
    global $bp;

    /**
     * First check the nonce to make sure that the user has initiated this
     * action. Remember the wp_nonce_url() call? The second parameter is what
     * you need to check for.
     */
    if ( !check_admin_referer( 'rt_media_accept_terms' ) )
        return false;

	/***
	 * Here is a good example of where we can post something to a users activity stream.
	 * The user has excepted the terms on Video, and now we want to post
	 * "Andy accepted the really exciting terms and conditions!" to the stream.
	 */
    rt_media_record_activity(
        array(
        'item_id' => $bp->loggedin_user->id,
        'user_id' => $bp->loggedin_user->id,
        'component_name' => $bp->rt_media->slug,
        'component_action' => 'accepted_terms',
        'is_private' => 0
        )
    );

	/* See rt_media_reject_terms() for an explanation of deleting activity items */
    rt_media_delete_activity(
        array(
        'item_id' => $bp->loggedin_user->id,
        'user_id' => $bp->loggedin_user->id,
        'component_name' => $bp->rt_media->slug,
        'component_action' => 'rejected_terms'
        )
    );

	/***
	 * Remember, even though we have recorded the activity, we still need to tell
	 * the activity component how to format that activity item into something readable.
	 * In the rt_media_format_activity() function, we need to make an entry for
	 * 'accepted_terms'
	 */

	/* Add a do_action here so other plugins can hook in */
    do_action( 'rt_media_accept_terms', $bp->loggedin_user->id );

	/***
	 * You'd want to do something here, like set a flag in the database, or set usermeta.
	 * just for the sake of the demo we're going to return true.
	 */

    return true;
}

/**
 * rt_media_reject_terms()
 *
 * Rejects the terms and conditions screen for the logged in user.
 * Records an activity stream item for the user.
 */
function rt_media_reject_terms() {
    global $bp;

    if ( !check_admin_referer( 'rt_media_reject_terms' ) )
        return false;

	/***
	 * In this example component, the user can reject the terms even after they have
	 * previously accepted them.
	 *
	 * If a user has accepted the terms previously, then this will be in their activity
	 * stream. We don't want both 'accepted' and 'rejected' in the activity stream, so
	 * we should remove references to the user accepting from all activity streams.
	 * A real world example of this would be a user deleting a published blog post.
	 */

	/* Delete any accepted_terms activity items for the user */
    rt_media_delete_activity(
        array(
        'item_id' => $bp->loggedin_user->id,
        'user_id' => $bp->loggedin_user->id,
        'component_name' => $bp->rt_media->slug,
        'component_action' => 'accepted_terms'
        )
    );

	/* Now record the new 'rejected' activity item */
    rt_media_record_activity(
        array(
        'item_id' => $bp->loggedin_user->id,
        'user_id' => $bp->loggedin_user->id,
        'component_name' => $bp->rt_media->slug,
        'component_action' => 'rejected_terms',
        'is_private' => 0
        )
    );

    do_action( 'rt_media_reject_terms', $bp->loggedin_user->id );

    return true;
}

/**
 * rt_media_send_high_five()
 *
 * Sends a high five message to a user. Registers an notification to the user
 * via their notifications menu, as well as sends an email to the user.
 *
 * Also records an activity stream item saying "User 1 high-fived User 2".
 */
function rt_media_send_highfive( $to_user_id, $from_user_id ) {
    global $bp;

    if ( !check_admin_referer( 'rt_media_send_high_five' ) )
        return false;

    /**
     * We'll store high-fives as usermeta, so we don't actually need
     * to do any database querying. If we did, and we were storing them
     * in a custom DB table, we'd want to reference a function in
     * rt-media-classes.php that would run the SQL query.
     */

	/* Get existing fives */
    $existing_fives = maybe_unserialize( get_usermeta( $to_user_id, 'high-fives' ) );

	/* Check to see if the user has already high-fived. That's okay, but lets not
	 * store duplicate high-fives in the database. What's the point, right?
	 */
    if ( !in_array( $from_user_id, (array)$existing_fives ) ) {
        $existing_fives[] = (int)$from_user_id;

		/* Now wrap it up and fire it back to the database overlords. */
        update_usermeta( $to_user_id, 'high-fives', serialize( $existing_fives ) );
    }

	/***
	 * Now we've registered the new high-five, lets work on some notification and activity
	 * stream magic.
	 */

	/***
	 * Post a screen notification to the user's notifications menu.
	 * Remember, like activity streams we need to tell the activity stream component how to format
	 * this notification in rt_media_format_notifications() using the 'new_high_five' action.
	 */
    bp_core_add_notification( $from_user_id, $to_user_id, $bp->rt_media->slug, 'new_high_five' );

	/* Now record the new 'new_high_five' activity item */
    rt_media_record_activity(
        array(
        'item_id' => $to_user_id,
        'user_id' => $from_user_id,
        'component_name' => $bp->rt_media->slug,
        'component_action' => 'new_high_five',
        'is_private' => 0
        )
    );

	/* We'll use this do_action call to send the email notification. See rt-media-notifications.php */
    do_action( 'rt_media_send_high_five', $to_user_id, $from_user_id );

    return true;
}

/**
 * rt_media_get_highfives_for_user()
 *
 * Returns an array of user ID's for users who have high fived the user passed to the function.
 */
function rt_media_get_highfives_for_user( $user_id ) {
    global $bp;

    if ( !$user_id )
        return false;

    return maybe_unserialize( get_usermeta( $user_id, 'high-fives' ) );
}

/**
 *
 */
function rt_media_remove_screen_notifications() {
    global $bp;

    /**
     * When clicking on a screen notification, we need to remove it from the menu.
     * The following command will do so.
     */
    bp_core_delete_notifications_for_user_by_type( $bp->loggedin_user->id, $bp->rt_media->slug, 'new_high_five' );
}
add_action( 'rt_media_photo', 'rt_media_remove_screen_notifications' );
add_action( 'xprofile_screen_display_profile', 'rt_media_remove_screen_notifications' );

/**
 * rt_media_remove_data()
 *
 * It's always wise to clean up after a user is deleted. This stops the database from filling up with
 * redundant information.
 */
function rt_media_remove_data( $user_id ) {
	/* You'll want to run a function here that will delete all information from any component tables
	   for this $user_id */

	/* Remember to remove usermeta for this component for the user being deleted */
    delete_usermeta( $user_id, 'rt_media_some_setting' );

    do_action( 'rt_media_remove_data', $user_id );
}
add_action( 'wpmu_delete_user', 'rt_media_remove_data', 1 );
add_action( 'delete_user', 'rt_media_remove_data', 1 );

/**
 * rt_media_load_buddypress()
 *
 * When we activate the component, we must make sure BuddyPress is loaded first (if active)
 * If it's not active, then the plugin should not be activated.
 */
function rt_media_load_buddypress() {
    if ( function_exists( 'bp_core_setup_globals' ) )
        return true;

	/* Get the list of active sitewide plugins */
    $active_sitewide_plugins = maybe_unserialize( get_site_option( 'active_sitewide_plugins' ) );
    if ( isset( $active_sidewide_plugins['buddypress/bp-loader.php'] ) && !function_exists( 'bp_core_setup_globals' ) ) {
        require_once( WP_PLUGIN_DIR . '/buddypress/bp-loader.php' );
        return true;
    }

	/* If we get to here, BuddyPress is not active, so we need to deactive the plugin and redirect. */
    require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
    if ( file_exists( ABSPATH . 'wp-admin/includes/mu.php' ) )
        require_once( ABSPATH . 'wp-admin/includes/mu.php' );

    deactivate_plugins( basename(__FILE__), true );
    if ( function_exists( 'deactivate_sitewide_plugin') )
        deactivate_sitewide_plugin( basename(__FILE__), true );

    wp_redirect( get_blog_option( BP_ROOT_BLOG, 'home' ) . '/wp-admin/plugins.php' );
}
add_action( 'plugins_loaded', 'rt_media_load_buddypress', 11 );

/* This alerts admin to configure Media Component properly */
if ( !get_site_option('bp_rt_kaltura_partner_id')) {

    function kaltura_warning() {
        echo "
		<div class='updated fade'><p><strong>".__('To complete the Media Component installation, <a href="'.get_settings('siteurl').'/wp-admin/options-general.php?page=rt_media_options">you must get a Partner ID.</a>')."</strong></p></div>	";
    }
    add_action('admin_notices', 'kaltura_warning');
}

?>
