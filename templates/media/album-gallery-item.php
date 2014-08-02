<?php
/** That's all, stop editing from here **/
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
        <a href ="<?php rtmedia_permalink (); ?>" title="<?php echo rtmedia_title (); ?>">
            <img src="<?php rtmedia_image ( 'rt_media_thumbnail' ); ?>" alt="<?php echo rtmedia_title(); ?>">
        </a>
    </div>

	<?php if( apply_filters( 'rtmedia_media_gallery_show_media_title', true ) ){ ?>
		<div class="rtmedia-item-title">
			<h4 title="<?php echo rtmedia_title (); ?>">
				<a href="<?php rtmedia_permalink (); ?>" title="<?php echo rtmedia_title (); ?>">
					<?php echo rtmedia_title (); ?>
				</a>
			</h4>
		</div>
	<?php } ?>
</li>