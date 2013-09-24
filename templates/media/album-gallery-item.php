<?php
/** That's all, stop editing from here * */
if ( ! defined( 'WP_LOAD_PATH' ) ) {
	/** classic root path if wp-content and plugins is below wp-config.php */
	$classic_root = dirname( dirname( dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) ) ) . '/';

	//$classic_root = dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . '/' ;
	if ( file_exists( $classic_root . 'wp-load.php' ) )
		define( 'WP_LOAD_PATH', $classic_root );
	else
	if ( file_exists( $path . 'wp-load.php' ) )
		define( 'WP_LOAD_PATH', $path );
	else
		exit( "Could not find wp-load.php" );

	// let's load WordPress
	require_once( WP_LOAD_PATH . 'wp-load.php');
}

global $rtmedia_backbone;
$rtmedia_backbone = array(
	'backbone' => false,
	'is_album' => false,
	'is_edit_allowed' => false
);
if ( isset( $_POST[ 'backbone' ] ) )
	$rtmedia_backbone['backbone'] = $_POST[ 'backbone' ];
if ( isset( $_POST[ 'is_album' ] ) )
	$rtmedia_backbone['is_album'] = $_POST[ 'is_album' ][0];
if ( isset( $_POST[ 'is_edit_allowed' ] ) )
	$rtmedia_backbone['is_edit_allowed'] = $_POST[ 'is_edit_allowed' ][0];
?>
<li class="rtmedia-list-item">
    <div class="rtmedia-item-thumbnail">
        <a href ="<?php rtmedia_permalink (); ?>">
            <img src="<?php rtmedia_image ( 'rt_media_thumbnail' ); ?>" >
        </a>
    </div>

    <div class="rtmedia-item-title">
        <h4 title="<?php echo rtmedia_title (); ?>">
            <a href="<?php rtmedia_permalink (); ?>">
                <?php echo rtmedia_title (); ?>
            </a>
        </h4>
    </div>

</li>