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
		foreach ( $sub_tabs as $single_tab ) {

			// tab status.
			$active_class = '';
			$error_class  = '';

			if ( ! empty( $single_tab['args'] ) && ( empty( $single_tab['args']['status'] ) || 'valid' !== $single_tab['args']['status'] ) ) {
				$error_class = 'error';
			}
			if ( 1 === $i ) {
				$active_class = 'active';
			}

			?>
			<li class="<?php echo esc_attr( $active_class ); ?> <?php echo esc_attr( $error_class ); ?>">
				<a id="tab-<?php echo esc_attr( substr( $single_tab['href'], 1 ) ); ?>" title="<?php echo esc_attr( $single_tab['title'] ); ?>" href="<?php echo esc_url( $single_tab['href'] ); ?>" class="rtmedia-tab-title <?php echo esc_attr( sanitize_title( $single_tab['name'] ) ); ?>">
					<?php
					if ( isset( $single_tab['icon'] ) && ! empty( $single_tab['icon'] ) ) {
						?>
						<i class="<?php echo esc_attr( $single_tab['icon'] ); ?> dashicons"></i>
						<?php
					}
					?>
					<span><?php echo esc_html( $single_tab['name'] ); ?></span>
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
		foreach ( $sub_tabs as $single_tab ) {
			$active_class = '';
			if ( 1 === $k ) {
				$active_class = ' active';
			}
			$k++;
			if ( isset( $single_tab['icon'] ) && ! empty( $single_tab['icon'] ) ) {
				$icon = sprintf( '<i class="%1$s"></i>', esc_attr( $single_tab['icon'] ) );
			}
			$tab_without_hash = explode( '#', $single_tab['href'] );
			$tab_without_hash = $tab_without_hash[1];
			echo '<div class="rtm-content' . esc_attr( $active_class ) . '" id="' . esc_attr( $tab_without_hash ) . '">';
			if ( isset( $single_tab['args'] ) ) {
				call_user_func( $single_tab['callback'], $page_name, $single_tab['args'] );
			} else {
				call_user_func( $single_tab['callback'], $page_name );
			}
			echo '</div>';
		}
		?>
	</div>

</div>
