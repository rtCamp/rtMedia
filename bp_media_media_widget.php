<?php

/* Register widgets for media component */
function bp_media_register_widgets_for_all() {
	add_action('widgets_init', create_function('', 'return register_widget("BP_Media_Type_Widget");') );
}
add_action( 'bp_init', 'bp_media_register_widgets_for_all', 1 );

function bp_widget_all_css() {
        $css_path = BP_MEDIA_PLUGIN_URL.'/themes/media/css/';
        wp_enqueue_style( 'bp-media-widget-all-css', $css_path.'widgets.css');
       }

add_action( 'wp_print_styles', 'bp_widget_all_css' );

/*** Media WIDGET *****************/

class BP_Media_Type_Widget extends WP_Widget {
	function bp_media_type_widget() {
		parent::WP_Widget( false, $name = __( 'Photos/Audio/Video', 'buddypress' ), array( 'description' => __( 'Your Media Specific Categorized Widget', 'buddypress-media' ) ) );

		        if ( is_active_widget( false, false, $this->id_base ) ) {
                            $js_path = BP_MEDIA_PLUGIN_URL.'/themes/media/js/';
                            wp_enqueue_script( 'bp-media-media-widget-js', $js_path.'widget_al.js');
		}

	}

	function widget($args, $instance) {
		global $bp,$pictures_template;

                extract( $args );
                echo $before_widget;
		echo $before_title
		   .'<h2 class="bigPostTitle">'. $widget_name.'</h2>'
		   . $after_title; ?>
<?php if ( bp_has_media( 'type=recent&per_page=' . $instance['max_al_media'] . '&max=' . $instance['max_al_media'].'&scope=photo&view=widget' ) ) : ?>


<div class="item-options" id="media-allist-options">
<span class="ajax-loader" id="ajax-loader-all-media"></span>

<div class="tabs"><a href="<?php echo bp_get_root_domain() . '/' . $bp->media->slug ?>" id ="photo"><?php printf( __( 'Photos', 'buddypress' ) ) ?></a></div>
<div class="tabs"><a href="<?php echo bp_get_root_domain() . '/' . $bp->media->slug ?>" id ="audio"><?php printf( __( 'Audio', 'buddypress' ) ) ?></a></div>
<div class="tabs"><a href="<?php echo bp_get_root_domain() . '/' . $bp->media->slug ?>" id ="video"><?php printf( __( 'Videos', 'buddypress' ) ) ?></a> </div>
</div>
	<div id="media-content">

    <ul id="media-allist" class="item-list">
                <?php while ( bp_pictures() ) : bp_the_picture(); ?>
               <a href='<?php bp_picture_view_link() ?>'><img class="widget-thumbnail" src='<?php bp_picture_small_link() ?>' /></a>
        <?php endwhile; ?>
       <div class="clear"></div>
    </ul>
       <?php wp_nonce_field( 'bp_media_widget_allist', '_wpnonce-media-all' ); ?>
        <input type="hidden" name="almedia_widget_max" id="almedia_widget_max" value="<?php echo attribute_escape( $instance['max_al_media'] ); ?>" />
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
		$instance['max_al_media'] = strip_tags( $new_instance['max_al_media']);

                return $instance;
	}

	function form( $instance ) {

                $instance = wp_parse_args( (array) $instance, array( 'max_al_media' => 5 ) );
		$max_al_media = strip_tags( $instance['max_al_media'] );
//                $max_al_media = isset($instance['max_al_media']) ? esc_attr($instance['max_al_media']) : '';

		?>

                <p><label for="bp-media-widget-media-max"><?php _e('Max Media to show:', 'buddypress'); ?>
                        <input class="widefat" id="<?php echo $this->get_field_id( 'max_al_media' ); ?>" name="<?php echo $this->get_field_name( 'max_al_media' ); ?>" type="text" value="<?php echo attribute_escape( $max_al_media ); ?>" style="width: 30%" />
                    </label>
                </p>


	<?php
	}
}

//cdoe here for ajax

function bp_media_ajax_widget_get_allist() {
	global $bp, $pictures_template;

	check_ajax_referer('bp_media_widget_allist');



	switch ( $_POST['filter'] ) {
		case 'photo':
			$scope = 'photo';
			break;
		case 'audio':
			$scope = 'audio';
			break;
		case 'video':
			$scope = 'video';
			break;

	}


	if ( bp_has_media( 'scope=' . $scope . '&per_page=' . $_POST['max_al_media'] . '&max=' . $_POST['max_al_media'].'type=recent&view=widget' ) ) : ?>

            <?php echo "0[[SPLIT]]"; ?>
     <ul id="media-allist" class="item-list">
         <?php //echo $type; ?>
                <?php while ( bp_pictures() ) : bp_the_picture(); ?>
                       <a class="no-ajax" href='<?php bp_picture_view_link() ?>'><img class="widget-thumbnail" src='<?php bp_picture_small_link() ?>' /></a>
                <?php endwhile; ?>
       <div class="clear"></div>
    </ul>
        <?php wp_nonce_field( 'bp_media_widget_allist', '_wpnonce-media-all' ); ?>
        <input type="hidden" name="almedia_widget_max" id="almedia_widget_max" value="<?php echo attribute_escape( $_POST['max_al_media'] ); ?>" />
<?php else: ?>
		<?php echo "-1[[SPLIT]]<li>" . __("No media matched the current filter.", 'buddypress'); ?>

	<?php endif;

}
add_action( 'wp_ajax_widget_almedia_list', 'bp_media_ajax_widget_get_allist' );
add_action( 'wp_ajax_nopriv_widget_almedia_list', 'bp_media_ajax_widget_get_allist' );

?>