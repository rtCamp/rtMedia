<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/* Register widgets for photo featured list */

    wp_enqueue_script('jquery-ui-tabs', '', array('jquery','jquery-ui-core'));


function bp_media_photo_widgets() {
	add_action('widgets_init', create_function('', 'return register_widget("BP_Media_Photo_Widget");') );
}
add_action( 'bp_init', 'bp_media_photo_widgets', 11 );

class BP_Media_Photo_Widget extends WP_Widget {
	function bp_media_photo_widget() {
		parent::WP_Widget( false, $name = __( 'Featured Photo - Media', 'buddypress' ), array( 'description' => __( ' BuddyPress media featured photo', 'buddypress-media' ) ) );

	}

        function widget($args, $instance) {
//            var_dump($instance);
            global $bp,$kaltura_validation_data,$wpdb;

            $rt_photo_list = get_option('rt_bp_add_to_photo_feature_list');
//            var_dump($rt_photo_list,'------------');
            $featured_data =  rt_get_featured_media($rt_photo_list);
            $featured_data = rt_sort_this_stuff($featured_data,'photo');

            $cnt = count($featured_data);

            $partner_id = $kaltura_validation_data['partner_id'];
            $kaltura_url = $kaltura_validation_data['config']->serviceUrl;

             echo $before_widget . '<div class="widget">';
             echo $before_title .'<h2 class="rtfeaturetitle">' .$instance['title'] .'</h2>'  . $after_title;

//                var_dump($featured_data,$partner_id, $kaltura_url);

//             var_dump($rt_photo_list);
//              $featured_data =  rt_get_featured_media($rt_photo_list);


             ?>
<style>
    .rt-widget-img-thumbnail{height: 56px;width:57px;padding: 2px;margin: 0px 0px;border:3px solid #DFDFDF}
</style>

         <div id="rt-photo-widget">
            
             <?php
             if(is_array($featured_data)){
             foreach($featured_data as $fpd){
                $i = $wpdb->get_var("select id from {$bp->media->table_media_data} where entry_id = '{$fpd->id}'");
             ?>
             <!-- PRASAD CODE -->
             <a href="<?php echo $bp->root_domain.'/'.BP_MEDIA_SLUG .'/photo_'.$i ?>"><img  class="rt-widget-img-thumbnail"  src ="<?php echo $fpd->thumbnailUrl ?>"/></a>
             <?php } } ?>
         </div>
             <?php echo $after_widget . '</div>';


             }

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title']);

                return $instance;
	}

	function form( $instance ) {

                $instance = wp_parse_args( (array) $instance, array( 'title' => 'Featured Photo' ) );
		$title = strip_tags( $instance['title'] );

		?>

                <p><label for="bp-media-featured_photo"><?php _e('Title', 'buddypress'); ?>
                        <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo attribute_escape( $title ); ?>" style="width: 60%" />
                    </label>
                </p>


	<?php
	}
}




?>