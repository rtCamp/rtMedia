<?php

/**
 * NOTE: You should always use the wp_enqueue_script() and wp_enqueue_style() functions to include
 * javascript and css files.
 */

/**
 * rt_media_add_js()
 *
 * This function will enqueue the components javascript file, so that you can make
 * use of any javascript you bundle with your component within your interface screens.
 */
function rt_media_add_js() {
	global $bp;

	if ( $bp->current_component == $bp->rt_media->slug ){
		wp_enqueue_script( 'rt-bp-kaltura-js', WP_PLUGIN_URL . '/rt-bp-kaltura-plugin/js/general.js' );
//		wp_enqueue_script( 'rt-bp-kaltura-js', WP_PLUGIN_URL . '/rt-bp-kaltura-plugin/js/general.js',array('swfobject', 'swfupload-all', 'swfupload-handlers') );
                /*video page js:example imported from http://www.kaltura.org/kaltura-gallery-services-kgallery-website-integration-guide*/
                wp_enqueue_script( 'jquery.mousewheel', WP_PLUGIN_URL . '/rt-bp-kaltura-plugin/js/jquery.mousewheel.js' );
            	//wp_enqueue_script( 'jquery.tools.min', 'http://cdn.jquerytools.org/1.0.2/jquery.tools.min.js' );
                wp_enqueue_script( 'jquery.tools.min', WP_PLUGIN_URL . '/rt-bp-kaltura-plugin/js/jquery.tools.min.js' );

                /*photo thumbnail gallery imported from http://www.pirolab.it/pirobox/ */
                wp_enqueue_script( 'piroBox', WP_PLUGIN_URL . '/rt-bp-kaltura-plugin/js/piroBox.js' );
      		wp_enqueue_script( 'html', WP_PLUGIN_URL . '/rt-bp-kaltura-plugin/js/html.js' );
      		wp_enqueue_script( 'extra', WP_PLUGIN_URL . '/rt-bp-kaltura-plugin/js/extra.js' );
                wp_enqueue_script( 'swfobject', WP_PLUGIN_URL . '/rt-bp-kaltura-plugin/js/swfobject.js' );
                wp_enqueue_script( 'kaltura_swfobject_1.5', WP_PLUGIN_URL . '/rt-bp-kaltura-plugin/js/swfobject.js' );
//                wp_enqueue_script('kaltura_swfobject_1.5', WP_PLUGIN_URL . '/js/swfobject.js');

        }
}
add_action( 'template_redirect', 'rt_media_add_js', 1 );

/**
 * rt_media_add_structure_css()
 *
 * This function will enqueue structural CSS so that your component will retain interface
 * structure regardless of the theme currently in use. See the notes in the CSS file for more info.
 */
function rt_media_add_structure_css() {
	/* Enqueue the structure CSS file to give basic positional formatting for your component reglardless of the theme. */
	wp_enqueue_style( 'rt-bp-kaltura-structure', WP_PLUGIN_URL . '/rt-bp-kaltura-plugin/css/structure.css' );
	
        /*video sliding thumbnail */
        wp_enqueue_style( 'thumbScroller', WP_PLUGIN_URL . '/rt-bp-kaltura-plugin/css/thumb_list.css' );
        wp_enqueue_style( 'scrollable-navig', WP_PLUGIN_URL . '/rt-bp-kaltura-plugin/css/scrollable-navig.css' );

        /*photo gallery*/
	wp_enqueue_style( 'stile', WP_PLUGIN_URL . '/rt-bp-kaltura-plugin/css/stile.css' );
	wp_enqueue_style( 'thickbox', WP_PLUGIN_URL . '/rt-bp-kaltura-plugin/css/thickbox.css' );
        ?>
	<style type="text/css">
		li a#user-media, li a#my-media { background: url( <?php echo plugins_url( '/rt-bp-kaltura-plugin/images/16x16-media.png' ) ?> ) no-repeat 88% 52%; padding: 0.55em 3em 0.55em 0 !important; margin-right: 0.85em !important; }
		li#afilter-media a { background: url( <?php echo plugins_url( '/rt-bp-kaltura-plugin/images/16x16-media.png' ) ?> ) no-repeat; }
	</style>
        <?php
}
add_action( 'bp_styles', 'rt_media_add_structure_css' );
?>
