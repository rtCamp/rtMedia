<?php
/**
 * Template for RTMediaFormHandler::sizes_content().
 *
 * @package rtMedia
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<div class="rtm-option-wrapper rtm-img-size-setting">
	<h3 class="rtm-option-title">
		<?php esc_html_e( 'Media Size Settings', 'buddypress-media' ); ?>
	</h3>

	<table class="form-table">
		<tr>
			<th><strong><?php esc_html_e( 'Category', 'buddypress-media' ); ?></strong></th>
			<th><strong><?php esc_html_e( 'Entity', 'buddypress-media' ); ?></strong></th>
			<th><strong><?php esc_html_e( 'Width', 'buddypress-media' ); ?></strong></th>
			<th><strong><?php esc_html_e( 'Height', 'buddypress-media' ); ?></strong></th>
			<th><strong><?php esc_html_e( 'Crop', 'buddypress-media' ); ?></strong></th>
		</tr>

		<?php
		foreach ( $render_data as $parent_key => $section ) { /* phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Legacy public naming retained for backward compatibility; renaming breaks dependent themes/add-ons. */
			$entities = $section; /* phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Legacy public naming retained for backward compatibility; renaming breaks dependent themes/add-ons. */
			unset( $entities['title'] );
			$count    = 0; /* phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Legacy public naming retained for backward compatibility; renaming breaks dependent themes/add-ons. */
			$row_span = count( $entities ); /* phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Legacy public naming retained for backward compatibility; renaming breaks dependent themes/add-ons. */
			foreach ( $entities as $entity ) { /* phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Legacy public naming retained for backward compatibility; renaming breaks dependent themes/add-ons. */
				?>
				<tr>
					<?php
					if ( 0 === $count ) {
						?>
						<td class="rtm-row-title" rowspan="<?php echo esc_attr( $row_span ); ?>">
							<?php echo esc_html( ucfirst( $section['title'] ) ); ?>
						</td>
						<?php
					}
					?>
					<td>
						<?php echo esc_html( ucfirst( $entity['title'] ) ); ?>
					</td>

					<?php
					$args = array( /* phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Legacy public naming retained for backward compatibility; renaming breaks dependent themes/add-ons. */
						'key' => 'defaultSizes_' . $parent_key . '_' . $entity['title'],
					);
					foreach ( $entity as $child_key => $value ) { /* phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Legacy public naming retained for backward compatibility; renaming breaks dependent themes/add-ons. */
						if ( 'title' !== $child_key ) {
							$args[ $child_key ] = $value; /* phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Legacy public naming retained for backward compatibility; renaming breaks dependent themes/add-ons. */
						}
					}
					self::dimensions( $args );
					?>
				</tr>
				<?php
				$count++;
			}
		}
		?>
	</table>

</div>
