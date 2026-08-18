<?php
/**
 * Template for RTMediaAdmin::render_admin_ui().
 *
 * @package rtMedia
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<div class="clearfix <?php echo esc_attr( $tab_position_class ); ?> rtm-admin-tab-container <?php echo esc_attr( $wrapper_class ); ?>">
	<ul class="rtm-tabs">
		<?php
		$i = 1; /* phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Legacy public naming retained for backward compatibility; renaming breaks dependent themes/add-ons. */
		foreach ( $sub_tabs as $single_tab ) { /* phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Legacy public naming retained for backward compatibility; renaming breaks dependent themes/add-ons. */

			// tab status.
			$active_class = ''; /* phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Legacy public naming retained for backward compatibility; renaming breaks dependent themes/add-ons. */
			$error_class  = ''; /* phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Legacy public naming retained for backward compatibility; renaming breaks dependent themes/add-ons. */

			if ( ! empty( $single_tab['args'] ) && ( empty( $single_tab['args']['status'] ) || 'valid' !== $single_tab['args']['status'] ) ) {
				$error_class = 'error'; /* phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Legacy public naming retained for backward compatibility; renaming breaks dependent themes/add-ons. */
			}
			if ( 1 === $i ) {
				$active_class = 'active'; /* phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Legacy public naming retained for backward compatibility; renaming breaks dependent themes/add-ons. */
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
		$k = 1; /* phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Legacy public naming retained for backward compatibility; renaming breaks dependent themes/add-ons. */
		foreach ( $sub_tabs as $single_tab ) { /* phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Legacy public naming retained for backward compatibility; renaming breaks dependent themes/add-ons. */
			$active_class = ''; /* phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Legacy public naming retained for backward compatibility; renaming breaks dependent themes/add-ons. */
			if ( 1 === $k ) {
				$active_class = ' active'; /* phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Legacy public naming retained for backward compatibility; renaming breaks dependent themes/add-ons. */
			}
			$k++;
			if ( isset( $single_tab['icon'] ) && ! empty( $single_tab['icon'] ) ) {
				$icon = sprintf( '<i class="%1$s"></i>', esc_attr( $single_tab['icon'] ) ); /* phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Legacy public naming retained for backward compatibility; renaming breaks dependent themes/add-ons. */
			}
			$tab_without_hash = explode( '#', $single_tab['href'] ); /* phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Legacy public naming retained for backward compatibility; renaming breaks dependent themes/add-ons. */
			$tab_without_hash = $tab_without_hash[1]; /* phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Legacy public naming retained for backward compatibility; renaming breaks dependent themes/add-ons. */
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
