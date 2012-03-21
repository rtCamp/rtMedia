<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/* Register widgets for video featured list */

    wp_enqueue_script('jquery-ui-tabs', '', array('jquery','jquery-ui-core'));


function bp_media_video_widgets() {
	add_action('widgets_init', create_function('', 'return register_widget("BP_Media_Video_Widget");') );
}
add_action( 'bp_init', 'bp_media_video_widgets', 11 );

class BP_Media_Video_Widget extends WP_Widget {
	function bp_media_video_widget() {
		parent::WP_Widget( false, $name = __( 'Featured Video - Media', 'buddypress' ), array( 'description' => __( ' BuddyPress media featured video', 'buddypress-media' ) ) );

	}

        function widget($args, $instance) {
        
            global $bp,$kaltura_validation_data;

            $rt_video_list = get_option('rt_bp_add_to_video_feature_list');
//            var_dump($rt_photo_list,'------------');
            $featured_data =  rt_get_featured_media($rt_video_list);
            $featured_data = rt_sort_this_stuff($featured_data,'video');

            $cnt = count($featured_data);
   
            $partner_id = $kaltura_validation_data['partner_id'];
            $kaltura_url = $kaltura_validation_data['config']->serviceUrl;
           
             echo $before_widget . '<div class="widget rt-video-widget">';
             echo $before_title .'<h2 class="rtfeaturetitle">' .$instance['title'] .'</h2>'  . $after_title;
           
//                var_dump($featured_data,$partner_id, $kaltura_url);

//             var_dump($rt_video_list);
//              $featured_data =  rt_get_featured_media($rt_video_list);

             
             ?>
<style>
.ui-tabs-nav {float:left;width: 250px;}
.ui-tabs-nav li{float: left}
.ui-tabs-nav li a {color: #8C8C8C;text-decoration: none}
.ui-tabs-nav li a:hover,.ui-tabs-nav li a:selected {text-decoration: underline;color:#CD1713}
.ui-tabs-nav li a span{display: block; width: 10px}
.ui-tabs .ui-tabs-hide{display: none;}
.ui-tabs .ui-tabs-panel ul{clear: both; /* border: 1px solid #EAEAEA; */}

</style>
             <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/swfobject/2.2/swfobject.js"></script>
             <script>
                 jQuery(document).ready(function(){
                    jQuery('#tabs').tabs();
                 })
             
            </script>
         <div id="tabs">
<style>

    .widget #tab ul{list-style: none;}
</style>

            <ul>
                <?php for($i=1 ;$i<=count($featured_data);$i++){ ?>
                <li><a href="#recent-posts-<?php echo $i ?>"><span><?php echo $i; ?></span></a></li>
              <?php } ?>
             </ul>
             <?php  $i =1;
             if(is_array($featured_data)){
             foreach($featured_data as $fvd){
             ?>
             <div id="recent-posts-<?php echo $i?>">
   <object name="kaltura_player" id="kaltura_player" type="application/x-shockwave-flash"
        allowScriptAccess="always" allowNetworking="all"
        allowFullScreen="true" height="<?php echo $instance['height'];?>" width="<?php echo $instance['width'];?>"
        data="<?php echo  $kaltura_url;?>/index.php/kwidget/cache_st/1274050232/wid/_<?php echo $partner_id?>/uiconf_id/48410/entry_id/<?php echo $fvd->id?>">
    <param name="allowScriptAccess" value="always" /><param name="allowNetworking" value="all" />
    <param name="allowFullScreen" value="true" /><param name="bgcolor" value="#000000" />
    <param name="movie" value="<?php echo  $kaltura_url;?>/index.php/kwidget/cache_st/1274050232/wid/_<?php echo $partner_id?>/uiconf_id/48410/entry_id/<?php echo $fvd->id?>"/>
    <param name="flashVars" value=""/></object>
             </div>
             <?php $i++; } } ?>
         </div>
             <?php echo $after_widget . '</div>';

             
             }

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title']);
                $instance['height'] = strip_tags( $new_instance['height']);
                $instance['width'] = strip_tags( $new_instance['width']);

                return $instance;
	}

	function form( $instance ) {

                $instance = wp_parse_args( (array) $instance, array( 'title' => 'Featured Video','height'=>'230','width'=> '285') );
		$title = strip_tags( $instance['title'] );
                $height = strip_tags( $instance['height'] );
                $width = strip_tags( $instance['width'] );


		?>

                <p><label for="bp-media-featured_video"><?php _e('Title', 'buddypress'); ?>
                        <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo attribute_escape( $title ); ?>" style="width: 60%" />
                    </label>
                </p>
                <p><label for="bp-media-featured_video_height"><?php _e('Height', 'buddypress'); ?>
                        <input class="widefat" id="<?php echo $this->get_field_id( 'height' ); ?>" name="<?php echo $this->get_field_name( 'height' ); ?>" type="text" value="<?php echo attribute_escape( $height ); ?>" style="width: 20%" /> px
                    </label>
                </p>

                <p><label for="bp-media-featured_video_width"><?php _e('Width', 'buddypress'); ?>
                        <input class="widefat" id="<?php echo $this->get_field_id( 'width' ); ?>" name="<?php echo $this->get_field_name( 'width' ); ?>" type="text" value="<?php echo attribute_escape( $width ); ?>" style="width: 20%" /> px
                    </label>
                </p>


	<?php
	}
}



function rt_get_featured_media($rt_entry_list) {
    global $bp, $wpdb, $kaltura_validation_data;
    $error = false;

    $rt_entry_list_for_kaltura = '';
    foreach ($rt_entry_list as $key ) {
        $rt_entry_list_for_kaltura = $rt_entry_list_for_kaltura . $key . ',';
    }
    try{
    $entry_id_data =  $kaltura_validation_data['client']->baseEntry->getByIds($rt_entry_list_for_kaltura);
    }
    catch(Exception $e){
        echo 'Oops Error communicating Server';
    }
    return ($entry_id_data);
    

}

























?>