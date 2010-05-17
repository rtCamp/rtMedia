<?php
function rt_js_escape($str) {
    for ($i = 0, $l = strlen($str), $new_str = ''; $i < $l; $i++) {
        $new_str .= (ord(substr($str, $i, 1)) < 16 ? '\\x0' : '\\x') . dechex(ord(substr($str, $i, 1)));
    }
    return $new_str;
}

$root = dirname(__FILE__);
while(!file_exists($root.'/wp-load.php')){
	if($root == dirname($root)){
		echo "WTF is WordPress???"; exit;
	}
	$root = dirname($root);
}

//Load wordpress
define('WP_USE_THEMES', false);
require_once($root.'/wp-load.php');

//include globals
global $wpdb;
global $bp;


//parse query
$filter = $_GET['filter'];	//'filter' must be 'mediaall' | 'photo' | 'audio' | 'video'

if ( bp_has_media( "scope=$filter&per_page=99999" ) ) : 
	global $pictures_template;
//if ( bp_has_media( bp_ajax_querystring( 'media' ) ) ) : 
?>
       <?php /* var_dump($pictures_template) */ ?>

<ul id="media-list" class="item-list">
        <?php while ( bp_pictures() ) : bp_the_picture(); 
            global $picture;
        ?>
    <li class="picture-thumb">
        <a href='<?php bp_picture_view_link() ?> target="_blank"'><img src='<?php bp_picture_small_link() ?>' /></a><br />
        <span class="title"><?php bp_picture_title() ?></span>
        <p class="insert-into-post">
        	<?php
        		//insert code
        		//type checking
                        if($picture->mediaType == 2){
        		//photo code
        		$code = '<a href="'.bp_get_picture_view_link().'"><img src="'.$picture->downloadUrl.'"/></a>';
                        }
                        else{ //for audio and video
                            $code = rt_get_object_markup();
                        }
        	?>
        	<input type="submit" value="Insert into Post" class="button insert-into-post-button" onclick="insert_into_post('<?php echo rt_js_escape($code); ?>')"/>
        </p>
    </li>

        <?php endwhile; ?>
            <div class="clear"></div>
</ul>

<?php else: ?>

<div id="message" class="info">
    <p><?php _e( 'There were no media content found matching your request.', 'buddypress' ) ?></p>
</div>

<?php endif; ?>

