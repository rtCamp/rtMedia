<?php
/**
 * Template for RTMediaFormHandler::types_content().
 *
 * @package rtMedia
 */

?>

<div class="rtm-option-wrapper">
	<?php do_action( 'rtmedia_media_type_setting_message' ); ?>

	<h3 class="rtm-option-title">
		<?php esc_html_e( 'Media Types Settings', 'buddypress-media' ); ?>
	</h3>

	<table class="form-table">

		<?php do_action( 'rtmedia_type_settings_before_heading' ); ?>

		<tr>
			<th>
				<strong><?php esc_html_e( 'Media Type', 'buddypress-media' ); ?></strong>
			</th>

			<th>
				<span class="rtm-tooltip bottom">
					<strong class="rtm-title"><?php esc_html_e( 'Allow Upload', 'buddypress-media' ); ?></strong>
					<span class="rtm-tip-top">
						<?php esc_html_e( 'Allows you to upload a particular media type on your post.', 'buddypress-media' ); ?>
					</span>
				</span>
			</th>

			<th>
				<span class="rtm-tooltip bottom">
					<strong class="rtm-title"><?php esc_html_e( 'Set Featured', 'buddypress-media' ); ?></strong>
					<span class="rtm-tip-top">
						<?php esc_html_e( 'Place a specific media as a featured content on the post.', 'buddypress-media' ); ?>
					</span>
				</span>
			</th>

			<?php do_action( 'rtmedia_type_setting_columns_title' ); ?>
		</tr>

		<?php
		do_action( 'rtmedia_type_settings_after_heading' );

		foreach ( $render_data as $key => $section ) {

			if ( isset( $section['settings_visibility'] ) && true === $section['settings_visibility'] ) {
				do_action( 'rtmedia_type_settings_before_body' );

				// allow upload.
				$uplaod_args           = array(
					'key'   => 'allowedTypes_' . $key . '_enabled',
					'value' => $section['enabled'],
				);
				$allow_upload_checkbox = self::checkbox( $uplaod_args, false );
				$allow_upload_checkbox = apply_filters( 'rtmedia_filter_allow_upload_checkbox', $allow_upload_checkbox, $key, $uplaod_args );

				// allow featured.
				$featured_args     = array(
					'key'   => 'allowedTypes_' . $key . '_featured',
					'value' => $section['featured'],
				);
				$featured_checkbox = self::checkbox( $featured_args, false );
				$featured_checkbox = apply_filters( 'rtmedia_filter_featured_checkbox', $featured_checkbox, $key );

				if ( ! isset( $section['extn'] ) || ! is_array( $section['extn'] ) ) {
					$section['extn'] = array();
				}

				$extensions = implode( ', ', $section['extn'] );
				?>

				<tr>
					<td>
						<?php
						echo esc_html( $section['name'] );

						if ( 'other' !== $key ) {
							?>
							<span class="rtm-tooltip rtm-extensions">
								<i class="dashicons dashicons-info"></i>
								<span class="rtm-tip">
									<strong><?php esc_html_e( 'File Extensions', 'buddypress-media' ); ?></strong><br/>
									<hr/>
									<?php echo esc_html( $extensions ); ?>
								</span>
							</span>
							<?php
						}
						?>
					</td>

					<td>
						<span class="rtm-field-wrap">
							<?php
							// escaping done into inner function.
							echo wp_kses(
								$allow_upload_checkbox,
								array(
									'span'  => array(
										'class'    => array(),
										'data-on'  => array(),
										'data-off' => array(),
									),
									'label' => array(
										'for'   => array(),
										'class' => array(),
									),
									'input' => array(
										'type'    => array(),
										'checked' => array(),
										'data-toggle' => array(),
										'id'      => array(),
										'name'    => array(),
										'value'   => array(),
									),
								)
							);
							?>
						</span>
					</td>

					<td>
						<?php
						// escaping done into inner function.
						echo wp_kses(
							$featured_checkbox,
							array(
								'span'  => array(
									'class'    => array(),
									'data-on'  => array(),
									'data-off' => array(),
								),
								'label' => array(
									'for'   => array(),
									'class' => array(),
								),
								'input' => array(
									'type'        => array(),
									'checked'     => array(),
									'data-toggle' => array(),
									'id'          => array(),
									'name'        => array(),
									'value'       => array(),
								),
							)
						);
						?>
					</td>

					<?php do_action( 'rtmedia_type_setting_columns_body', $key, $section ); ?>
				</tr>

				<?php do_action( 'rtmedia_other_type_settings_textarea', $key ); ?>

				<?php
				do_action( 'rtmedia_type_settings_after_body', $key, $section );
			} else {

				?>

				<tr class="hide">
					<td colspan="3">
						<input type="hidden" value="1" name="rtmedia-options[allowedTypes_<?php echo esc_attr( $key ); ?>_enabled]">
						<input type="hidden" value='0' name="rtmedia-options[allowedTypes_<?php echo esc_attr( $key ); ?>_featured]">
					</td>
				</tr>

				<?php
			}
		}
		?>
	</table>
</div>
