<?php
/**
 * @package Media_Component
 */
?>
<div class="item-list-tabs no-ajax" id="subnav">
	<ul>


		<?php do_action( 'bp_media_activity_syndication_options' ) ?>

		<li id="activity-filter-select" class="last">
		
		</li>
	</ul>
</div><!-- .item-list-tabs -->

<?php do_action( 'bp_before_media_activity_post_form' ) ?>

<?php if ( is_user_logged_in() ) : ?>
	<?php bp_media_locate_template( array( 'media/activity/post-form.php' ), true ) ?>
<?php endif; ?>

<?php do_action( 'bp_after_media_activity_post_form' ) ?>
<?php do_action( 'bp_before_media_activity_content' ) ?>

<div class="activity single-link">
	<?php locate_template( array( 'activity/activity-loop.php' ), true ) ?>
</div><!-- .activity -->

<?php do_action( 'bp_after_link_activity_content' ) ?>
