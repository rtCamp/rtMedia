<?php
/**
 * Template for Themes Content - RTMediaThemes::rtmedia_3rd_party_themes_content().
 *
 * @package rtMedia
 */

?>

<div class="theme-browser rtm-theme-browser rendered">
	<div class="themes rtm-themes clearfix">

		<?php
		foreach ( $themes as $theme ) {
			?>

			<div class="theme rtm-theme">
				<div class="theme-screenshot">
					<img src="<?php echo esc_url( $theme['image'] ); ?>"/>
				</div>

				<span class="more-details"><?php esc_html_e( 'Theme Details', 'buddypress-media' ); ?></span>

				<h3 class="theme-name"><?php echo esc_html( $theme['name'] ); ?></h3>

				<div class="theme-actions">
					<a class="button load-customize hide-if-no-customize"
						href="<?php echo esc_url( $theme['demo_url'] ); ?>"><?php esc_html_e( 'Live Demo', 'buddypress-media' ); ?></a>
					<a class="button button-primary load-customize hide-if-no-customize"
						href="<?php echo esc_url( $theme['buy_url'] ); ?>"><?php esc_html_e( 'Buy Now', 'buddypress-media' ); ?></a>
				</div>

				<div class="rtm-theme-content hide">
					<div class="theme-wrap">
						<div class="theme-header">
							<button class="left rtm-previous dashicons dashicons-no"><span
										class="screen-reader-text"><?php esc_html_e( 'Show previous theme', 'buddypress-media' ); ?></span>
							</button>
							<button class="right rtm-next dashicons dashicons-no"><span
										class="screen-reader-text"><?php esc_html_e( 'Show next theme', 'buddypress-media' ); ?></span>
							</button>
							<button class="close rtm-close dashicons dashicons-no"><span
										class="screen-reader-text"><?php esc_html_e( 'Close overlay', 'buddypress-media' ); ?></span>
							</button>
						</div>

						<div class="theme-about">
							<div class="theme-screenshots">
								<div class="screenshot">
									<a href="<?php echo esc_url( $theme['buy_url'] ); ?>" target="_blank"><img
												src="<?php echo esc_url( $theme['image'] ); ?>"/></a>
								</div>
							</div>

							<div class="theme-info">
								<h3 class="theme-name"><?php echo esc_html( $theme['name'] ); ?></h3>
								<h4 class="theme-author">By <a
											href="<?php echo esc_url( $theme['author_url'] ); ?>"><?php echo esc_html( $theme['author'] ); ?></a>
								</h4>
								<p class="theme-description"><?php echo esc_html( $theme['description'] ); ?> <a
											href="<?php echo esc_url( $theme['buy_url'] ); ?>" class="rtmedia-theme-inner-a"
											target="_blank"><?php esc_html_e( 'Read More', 'buddypress-media' ); ?></a>
								</p>
								<p class="theme-tags">
									<span><?php esc_html_e( 'Tags:', 'buddypress-media' ); ?></span><?php echo esc_html( $theme['tags'] ); ?>
								</p>
							</div>
						</div>

						<div class="theme-actions">
							<a class="button load-customize hide-if-no-customize"
								href="<?php echo esc_url( $theme['demo_url'] ); ?>"><?php esc_html_e( 'Live Demo', 'buddypress-media' ); ?></a>
							<a class="button button-primary load-customize hide-if-no-customize"
								href="<?php echo esc_url( $theme['buy_url'] ); ?>"><?php esc_html_e( 'Buy Now', 'buddypress-media' ); ?></a>
						</div>
					</div>
				</div>
			</div>

		<?php } ?>
	</div>
</div>

<div class="rtmedia-theme-warning rtm-warning">
	<?php esc_html_e( 'These are the third party themes. For any issues or queries regarding these themes please contact theme developers.', 'buddypress-media' ); ?>
</div>

<div>
	<h3 class="rtm-option-title"><?php esc_html_e( 'Are you a developer?', 'buddypress-media' ); ?></h3>

	<p>
		<?php esc_html_e( 'If you have developed a rtMedia compatible theme and would like it to list here, please email us at', 'buddypress-media' ); ?>
		<a href="mailto:rtmedia@rtcamp.com"><?php esc_html_e( 'rtmedia@rtcamp.com', 'buddypress-media' ); ?></a>.
	</p>
</div>
