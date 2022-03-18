<?php
/**
 * Template for RTMediaAdmin::render_admin_ui().
 *
 * @package rtMedia
 */

?>

<div class="clearfix <?php echo esc_attr( $tab_position_class ); ?> rtm-admin-tab-container <?php echo esc_attr( $wrapper_class ); ?>">
	<ul class="rtm-tabs">
		<?php
		$i = 1;
		foreach ( $sub_tabs as $tab ) {

			// tab status.
			$active_class = '';
			$error_class  = '';

			if ( ! empty( $tab['args'] ) && ( empty( $tab['args']['status'] ) || 'valid' !== $tab['args']['status'] ) ) {
				$error_class = 'error';
			}
			if ( 1 === $i ) {
				$active_class = 'active';
			}

			?>
			<li class="<?php echo esc_attr( $active_class ); ?> <?php echo esc_attr( $error_class ); ?>">
				<a id="tab-<?php echo esc_attr( substr( $tab['href'], 1 ) ); ?>" title="<?php echo esc_attr( $tab['title'] ); ?>" href="<?php echo esc_url( $tab['href'] ); ?>" class="rtmedia-tab-title <?php echo esc_attr( sanitize_title( $tab['name'] ) ); ?>">
					<?php
					if ( isset( $tab['icon'] ) && ! empty( $tab['icon'] ) ) {
						?>
						<i class="<?php echo esc_attr( $tab['icon'] ); ?> dashicons"></i>
						<?php
					}
					?>
					<span><?php echo esc_html( $tab['name'] ); ?></span>
				</a>
			</li>
			<?php
			$i++;
		}
		?>
	</ul>

	<div class="tabs-content rtm-tabs-content">
		<?php
		$k = 1;
		foreach ( $sub_tabs as $tab ) {
			$active_class = '';
			if ( 1 === $k ) {
				$active_class = ' active';
			}
			$k++;
			if ( isset( $tab['icon'] ) && ! empty( $tab['icon'] ) ) {
				$icon = sprintf( '<i class="%1$s"></i>', esc_attr( $tab['icon'] ) );
			}
			$tab_without_hash = explode( '#', $tab['href'] );
			$tab_without_hash = $tab_without_hash[1];
			echo '<div class="rtm-content' . esc_attr( $active_class ) . '" id="' . esc_attr( $tab_without_hash ) . '">';
			if ( isset( $tab['args'] ) ) {
				call_user_func( $tab['callback'], $page_name, $tab['args'] );
			} else {
				call_user_func( $tab['callback'], $page_name );
			}
			echo '</div>';
		}
		?>
	</div>

</div>
