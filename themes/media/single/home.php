<?php
/**
 * @package Media_Component
 */
?>
<?php if(!is_user_logged_in()) {?>
<script>
 jQuery('.star').rating();
 jQuery('.star').rating('readOnly');
</script>
<?php } ?>
<?php get_header() ?>

	<div id="content">
		<div class="padder">
			<?php if ( bp_has_media() ) : while ( bp_pictures() ) : bp_the_picture(); ?>

			<?php do_action( 'bp_before_media_home_content' ) ?>

			<div id="item-header">
				<?php bp_media_locate_template( array( 'media/single/media-header.php' ), true ) ?>
			</div>

			<div id="item-nav">
				<div class="item-list-tabs no-ajax" id="object-nav">
					<ul>
						<li class="selected"><a>Media Activity</a></li>
						<?php do_action( 'bp_media_options_nav' ) ?>
					</ul>
				</div>
			</div>

			<div id="item-body">
                            <!--<h3>Media activity coming soon...!</h3>-->
                                <?php
                                ?>
				<?php do_action( 'bp_before_media_body' ) ?>

				<?php //zif ( bp_is_group_admin_page() && bp_group_is_visible() ) : ?>
					<?php bp_media_locate_template( array( 'media/single/activity.php' ), true ) ?>

				<?php //else : ?>
					<?php /* The group is not visible, show the status message */ ?>

					<?php //do_action( 'bp_before_media_status_message' ) ?>

					<!--<div id="message" class="info">
						<p><?php //echo 'msg here';//bp_media_status_message() ?></p>
					</div>-->

					<?php do_action( 'bp_after_media_status_message' ) ?>
				<?php //endif; ?>

				<?php do_action( 'bp_after_media_body' ) ?>
			</div>

			<?php do_action( 'bp_after_media_home_content' ) ?>

			<?php endwhile; endif; ?>
                   
		</div><!-- .padder -->
	</div><!-- #content -->

	<?php locate_template( array( 'sidebar.php' ), true ) ?>

<?php get_footer() ?>
