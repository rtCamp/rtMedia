<?php

/* Register widgets for media component */
function bp_media_register_widgets() {
	add_action('widgets_init', create_function('', 'return register_widget("BP_Media_Widget");') );
}
add_action( 'bp_init', 'bp_media_register_widgets', 11 );

/*** Media WIDGET *****************/

function bp_widget_css() {
        $css_path = BP_MEDIA_PLUGIN_URL.'/themes/media/css/';
        wp_enqueue_style( 'bp-media-widget-css', $css_path.'widgets.css');
       }

add_action( 'wp_print_styles', 'bp_widget_css' );

class BP_Media_Widget extends WP_Widget {
	function bp_media_widget() {
		parent::WP_Widget( false, $name = __( 'Buddypress Media Component', 'buddypress' ), array( 'description' => __( 'Your BuddyPress media', 'buddypress-media' ) ) );

		        if ( is_active_widget( false, false, $this->id_base ) ) {
                            $js_path = BP_MEDIA_PLUGIN_URL.'/themes/media/js/';
                            wp_enqueue_script( 'bp-media-widget-js', $js_path.'widgets.js');
		}

	}

	function widget($args, $instance) {
		global $bp,$pictures_template;

                extract( $args );
//                $max_media = $instance['max_media'];

                echo $before_widget;
		echo $before_title
		   . $widget_name
		   . $after_title; ?>
<?php if ( bp_has_media( 'type=recent&per_page=' . $instance['max_media'] . '&max=' . $instance['max_media'].'&scope=mediaall&view=widget' ) ) : ?>


<div class="item-options" id="media-list-options">
<span class="ajax-loader" id="ajax-loader-media"></span>

<div class="tabs"><a href="<?php echo bp_get_root_domain() . '/' . $bp->media->slug ?>" id ="recent"><?php printf( __( 'Recent', 'buddypress' ) ) ?></a></div>
<div class="tabs"><a href="<?php echo bp_get_root_domain() . '/' . $bp->media->slug ?>" id ="popular"><?php printf( __( 'Popular', 'buddypress' ) ) ?></a></div>
<div class="tabs"><a href="<?php echo bp_get_root_domain() . '/' . $bp->media->slug ?>" id ="rating"><?php printf( __( 'Rating', 'buddypress' ) ) ?></a> </div>
</div>
	<div id="media-content">

    <ul id="media-list" class="item-list">
                <?php while ( bp_pictures() ) : bp_the_picture(); ?>
               <a href='<?php bp_picture_view_link() ?>'><img class="widget-thumbnail" src='<?php bp_picture_small_link() ?>' /></a>
        <?php endwhile; ?>
       <div class="clear"></div>
    </ul>
       <?php wp_nonce_field( 'bp_media_widget_list', '_wpnonce-media' ); ?>
        <input type="hidden" name="media_widget_max" id="media_widget_max" value="<?php echo attribute_escape( $instance['max_media'] ); ?>" />
<?php else: ?>
    <div class="widget-error">
        <?php _e('There are no media to display.', 'buddypress') ?>
    </div>
<?php endif;?>
</div>



<?php echo $after_widget; ?>
	<?php
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['max_media'] = strip_tags( $new_instance['max_media']);

                return $instance;
	}

	function form( $instance ) {

                $instance = wp_parse_args( (array) $instance, array( 'max_media' => 5 ) );
		$max_media = strip_tags( $instance['max_media'] );
//                $max_media = isset($instance['max_media']) ? esc_attr($instance['max_media']) : '';
		                
		?>
         
                <p><label for="bp-media-widget-media-max"><?php _e('Max Media to show:', 'buddypress'); ?>
                        <input class="widefat" id="<?php echo $this->get_field_id( 'max_media' ); ?>" name="<?php echo $this->get_field_name( 'max_media' ); ?>" type="text" value="<?php echo attribute_escape( $max_media ); ?>" style="width: 30%" />
                    </label>
                </p>

        
	<?php
	}
}

//cdoe here for ajax

function bp_media_ajax_widget_get_list() {
	global $bp, $pictures_template;

	check_ajax_referer('bp_media_widget_list');

        

	switch ( $_POST['filter'] ) {
		case 'recent':
			$type = 'recent';
			break;
		case 'popular':
			$type = 'popular';
			break;
		case 'rating':
			$type = 'rating';
			break;

	}

        
	if ( bp_has_media( 'type=' . $type . '&per_page=' . $_POST['max_media'] . '&max=' . $_POST['max_media'].'&scope=mediaall&view=widget' ) ) : ?>

            <?php echo "0[[SPLIT]]"; ?>
     <ul id="media-list" class="item-list">
                <?php while ( bp_pictures() ) : bp_the_picture(); ?>
                       <a class="no-ajax" href='<?php bp_picture_view_link() ?>'><img class="widget-thumbnail" src='<?php bp_picture_small_link() ?>' /></a>
                <?php endwhile; ?>
       <div class="clear"></div>
    </ul>
        <?php wp_nonce_field( 'bp_media_widget_list', '_wpnonce-media' ); ?>
        <input type="hidden" name="media_widget_max" id="media_widget_max" value="<?php echo attribute_escape( $_POST['max_media'] ); ?>" />
<?php else: ?>
		<?php echo "-1[[SPLIT]]<li>" . __("No media matched the current filter.", 'buddypress'); ?>

	<?php endif;

}
add_action( 'wp_ajax_widget_media_list', 'bp_media_ajax_widget_get_list' );

?>