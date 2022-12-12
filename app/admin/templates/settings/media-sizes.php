<?php
/**
 * Template for RTMediaFormHandler::sizes_content().
 *
 * @package rtMedia
 */

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
		foreach ( $render_data as $parent_key => $section ) {
			$entities = $section;
			unset( $entities['title'] );
			$count    = 0;
			$row_span = count( $entities );
			foreach ( $entities as $entity ) {
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
					$args = array(
						'key' => 'defaultSizes_' . $parent_key . '_' . $entity['title'],
					);
					foreach ( $entity as $child_key => $value ) {
						if ( 'title' !== $child_key ) {
							$args[ $child_key ] = $value;
						}
					}
					self::dimensions( $args );
					?>
				</tr>
				<?php
				$count ++;
			}
		}
		?>
	</table>

</div>
