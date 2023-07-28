<?php
/**
 * Template for RTMediaAdmin::rtmedia_dashboard_widget_function().
 *
 * @package rtMedia
 */

?>

<div class="clearfix">

	<div class="rtm-column alignleft">
		<h4 class="sub"><?php esc_html_e( 'Media Stats', 'buddypress-media' ); ?></h4>

		<table>
			<tbody>
			<?php
			$rtmedia_model = new RTMediaModel();
			global $wpdb;
			$results = wp_cache_get( 'rt-stats', 'rt-dashboard' );
			if ( false === $results ) {
				$results = $wpdb->get_results( $wpdb->prepare( "select media_type, count(id) as count from {$rtmedia_model->table_name} where blog_id=%d group by media_type", get_current_blog_id() ) );
				wp_cache_set( 'stats', $results, 'rt-dashboard', HOUR_IN_SECONDS );
			}
			if ( $results ) {
				foreach ( $results as $media ) {
					if ( defined( strtoupper( 'RTMEDIA_' . $media->media_type . '_PLURAL_LABEL' ) ) ) {
						?>
						<tr>
							<td class="b"> <?php echo esc_html( $media->count ); ?> </td>
							<td class="t"><?php echo esc_html( constant( strtoupper( 'RTMEDIA_' . $media->media_type . '_PLURAL_LABEL' ) ) ); ?></td>
						</tr>
						<?php
					}
				}
			}
			?>
			</tbody>
		</table>
	</div>

	<div class="rtm-column alignright">
		<h4 class="sub"><?php esc_html_e( 'Usage Stats', 'buddypress-media' ); ?></h4>

		<table>
			<tbody>
			<?php
			$total_count = wp_cache_get( 'total_count', 'rt-dashboard' );
			if ( false === $total_count ) {
				$total_count = $wpdb->get_var( "select count(*) from {$wpdb->users}" );
				wp_cache_set( 'total_count', $total_count, 'rt-dashboard', HOUR_IN_SECONDS );
			}
			?>
			<tr>
				<td class="b"> <?php echo esc_html( $total_count ); ?> </td>
				<td class="t"><?php esc_html_e( 'Total ', 'buddypress-media' ); ?></td>
			</tr>
			<?php
			$with_media_count = wp_cache_get( 'with_media', 'rt-dashboard' );
			if ( false === $with_media_count ) {
				$with_media_count = $wpdb->get_var( "select count(distinct media_author) from {$rtmedia_model->table_name}" );
				wp_cache_set( 'with_media', $with_media_count, 'rt-dashboard', HOUR_IN_SECONDS );
			}
			?>
			<tr>
				<td class="b"> <?php echo esc_html( $with_media_count ); ?> </td>
				<td class="t"><?php esc_html_e( 'With Media', 'buddypress-media' ); ?></td>
			</tr>
			<?php
			$comments = wp_cache_get( 'comments', 'rt-dashboard' ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
			if ( false === $comments ) {
				$comments = $wpdb->get_var( "select count(*) from {$wpdb->comments} where comment_post_ID in ( select media_id from {$rtmedia_model->table_name} )" ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
				wp_cache_set( 'comments', $comments, 'rt-dashboard', HOUR_IN_SECONDS );
			}
			?>
			<tr>
				<td class="b"> <?php echo esc_html( $comments ); ?> </td>
				<td class="t"><?php esc_html_e( 'Comments ', 'buddypress-media' ); ?></td>
			</tr>
			<?php
			$likes = wp_cache_get( 'likes', 'rt-dashboard' );
			if ( false === $likes ) {
				$likes = $wpdb->get_var( "select sum(likes) from {$rtmedia_model->table_name}" );
				wp_cache_set( 'likes', $likes, 'rt-dashboard', HOUR_IN_SECONDS );
			}
			?>
			<tr>
				<td class="b"> <?php echo esc_html( $likes ); ?> </td>
				<td class="t"><?php esc_html_e( 'Likes', 'buddypress-media' ); ?></td>
			</tr>
			</tbody>
		</table>
	</div>

</div>

<div class="rtm-meta-container">
	<ul class="rtm-meta-links">
		<li><b><?php esc_html_e( 'rtMedia Links:', 'buddypress-media' ); ?></b></li>
		<li><a href="https://rtmedia.io/"><?php esc_html_e( 'Homepage', 'buddypress-media' ); ?></a></li>
		<li>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=rtmedia-support#rtmedia-general' ) ); ?>">
				<?php esc_html_e( 'Free Support', 'buddypress-media' ); ?>
			</a>
		</li>
		<li>
			<a href="https://rtmedia.io/products/category/plugins/"><?php esc_html_e( 'Premium Addons', 'buddypress-media' ); ?></a>
		</li>
	</ul>
</div>
