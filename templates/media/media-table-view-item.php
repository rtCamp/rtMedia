<?php
/** That's all, stop editing from here * */
global $rtmedia_backbone;

$rtmedia_backbone = array(
	'backbone' => false,
	'is_album' => false,
	'is_edit_allowed' => false
);
if ( isset( $_POST[ 'backbone' ] ) ) {
	$rtmedia_backbone[ 'backbone' ] = $_POST[ 'backbone' ];
}

if ( isset( $_POST[ 'is_album' ] ) ) {
	$rtmedia_backbone[ 'is_album' ] = $_POST[ 'is_album' ][ 0 ];
}

if ( isset( $_POST[ 'is_edit_allowed' ] ) ) {
	$rtmedia_backbone[ 'is_edit_allowed' ] = $_POST[ 'is_edit_allowed' ][ 0 ];
}
?>
<?php
                    
                    
                    $rtmedia_file_size = rtmedia_file_size();
                    ?>
                    <tr class="rtmedia-table-list-row" id="<?php echo rtmedia_id(); ?>">
                        <td>
                            <a href="<?php rtmedia_permalink(); ?>">
                                <img src="<?php rtmedia_image("rt_media_thumbnail"); ?>" alt="<?php rtmedia_image_alt(); ?>" />
                            </a>  
                        </td>
                        <td data-value="<?php rtmedia_title(); ?>" >
                            <a href="<?php rtmedia_permalink(); ?>">
                                <?php rtmedia_title(); ?>
                            </a>
                        </td>
                        <td data-value="<?php rtmedia_media_upload_date() ?>" >
                            <?php rtmedia_media_upload_date(); ?>
                        </td>
                        <td data-value="<?php rtmedia_get_file_size_mb() ?>" >
                            <?php
                            if (function_exists('rtmedia_file_size')) {
                                rtmedia_get_file_size_mb();
                            } else {
                                echo '--';
                            }
                            ?>
                        </td>
                        <td>
                            <?php
                            if (is_user_logged_in() && rtmedia_edit_allowed()) {
                                ?>
                                <a href="<?php rtmedia_permalink(); ?>edit" class='no-popup' target='_blank'
                                   title='<?php _e('Edit this media', 'rtmedia'); ?>'><i class='dashicons dashicons-edit rtmicon'></i></a>
                                   <?php
                               }
                               ?>
                        </td>
                        <td>
                                <?php
                                    if (is_user_logged_in() && rtmedia_delete_allowed()) {
                                ?>
                                <a href="#" class="no-popup rtm-delete-table-media" title='<?php _e('Delete this media', 'rtmedia'); ?>'>
                                <?php
                                    $rtm_nonce = RTMediaMedia::media_nonce_generator(rtmedia_id(), false);
                                    $rtm_nonce = json_decode($rtm_nonce);
                                    $rtm_nonce_field = wp_nonce_field('rtmedia_delete_' . rtmedia_id(), $rtm_nonce->action);
                                ?>
                                    <i  class='dashicons dashicons-trash rtmicon'></i> 
                                </a>
                                <?php
                                }
                                ?>
                        </td>
                    </tr>
