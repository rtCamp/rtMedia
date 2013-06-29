<?php
global $rt_media_query;

if ( is_rt_media_album_gallery() ) {
	$template = 'album-gallery';
} elseif ( is_rt_media_album() || is_rt_media_gallery() ) {
	$template = 'media-gallery';
	if (
			is_rt_media_album() &&
			isset( $rt_media_query->media_query ) &&
			get_current_user_id() == $rt_media_query->media_query[ 'media_author' ] &&
			$rt_media_query->action_query->action == 'edit'
			)
		$template = 'album-single-edit';

} else if ( is_rt_media_single() ) {
	$template = 'media-single';
	if($rt_media_query->action_query->action == 'edit')
		$template = 'media-single-edit';

}

$ajax = false;


if (
		! empty( $_SERVER[ 'HTTP_X_REQUESTED_WITH' ] ) &&
		strtolower( $_SERVER[ 'HTTP_X_REQUESTED_WITH' ] ) == 'xmlhttprequest'
		)
	$ajax = true;


if ( ! $ajax ) {

	if ( class_exists( 'BuddyPress' ) && ! bp_is_blog_page() ){
		$template_type = 'buddypress';
	}else{
		$template_type = '';
	}
	get_header( $template_type );




	if ( $template_type == 'buddypress' ) {
		?>

		<div id="buddypress">

			<?php if ( bp_displayed_user_id() ) { ?>
				<div id="item-header">

					<?php bp_get_template_part( 'members/single/member-header' ) ?>

				</div>

				<div id="item-nav">
					<div class="item-list-tabs no-ajax" id="object-nav" role="navigation">
						<ul>

							<?php bp_get_displayed_user_nav(); ?>

							<?php do_action( 'bp_member_options_nav' ); ?>

						</ul>
					</div>
				</div>

				<div id="item-body">

					<?php do_action( 'bp_before_member_body' ); ?>

				<?php } else if ( bp_is_group() ) { ?>

					<?php if ( bp_has_groups() ) : while ( bp_groups() ) : bp_the_group(); ?>
							<div id="item-header">

								<?php bp_get_template_part( 'groups/single/group-header' ); ?>

							</div>

							<div id="item-nav">
								<div class="item-list-tabs no-ajax" id="object-nav" role="navigation">
									<ul>

										<?php bp_get_options_nav(); ?>

										<?php do_action( 'bp_group_options_nav' ); ?>

									</ul>
								</div>
							</div>
						<?php endwhile;
					endif;
					?>

					<div id="item-body">

						<?php do_action( 'bp_before_group_body' ); ?>

					<?php } ?>
					<?php
				}
			}
			include(RTMediaTemplate::locate_template( $template ));
			if ( ! $ajax ) {
				if ( $template_type == 'buddypress' && (bp_is_user() || bp_is_group()) ) {
					?>
				</div>
			</div>
			<?php
		}

		get_sidebar( $template_type );

		get_footer( $template_type );
	}
	?>
