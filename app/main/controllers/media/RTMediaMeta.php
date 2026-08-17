<?php
/**
 * Handles media meta operations.
 *
 * @package rtMedia
 * @author saurabh
 */

/**
 * Class to handle media meta operations.
 */
class RTMediaMeta {

	/**
	 * RTDBModel object.
	 *
	 * @var RTDBModel
	 */
	public $model;

	/**
	 * RTMediaMeta constructor.
	 */
	public function __construct() {
		$this->model = new RTDBModel( 'rtm_media_meta', false, 10, true );
	}

	/**
	 * Get meta from media.
	 *
	 * @param bool|int    $id Media id.
	 * @param bool|string $key Meta key.
	 *
	 * @return bool|mixed
	 */
	public function get_meta( $id = false, $key = false ) {
		if ( false === $id ) {
			return false;
		}
		if ( false === $key ) {
			return $this->get_all_meta( $id );
		} else {
			return $this->get_single_meta( $id, $key );
		}
	}

	/**
	 * Get all meta of media.
	 *
	 * @param bool $id Media id.
	 *
	 * @return bool|mixed
	 */
	private function get_all_meta( $id = false ) {
		if ( false === $id ) {
			return false;
		}

		return maybe_unserialize( $this->model->get( array( 'media_id' => $id ) ) );
	}

	/**
	 * Get single meta value.
	 *
	 * @param bool|int    $id Media id.
	 * @param bool|string $key Meta key.
	 *
	 * @return bool|mixed
	 */
	private function get_single_meta( $id = false, $key = false ) {
		if ( false === $id ) {
			return false;
		}

		if ( false === $key ) {
			return false;
		}

		$value = $this->model->get(
			array(
				'media_id' => $id,
				'meta_key' => $key, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- Column of rtMedia custom meta table (rt_rtm_media_meta), not WP post meta; not a WP_Query.
			)
		);

		if ( isset( $value[0] ) ) {
			return maybe_unserialize( $value[0]->meta_value );
		} else {
			return false;
		}
	}

	/**
	 * Add meta.
	 *
	 * @param bool|int    $id Media id.
	 * @param bool|string $key Meta key.
	 * @param bool|string $value Meta value.
	 * @param bool        $duplicate Duplicate meta or not.
	 *
	 * @return bool|false|int
	 */
	public function add_meta( $id = false, $key = false, $value = false, $duplicate = false ) {
		return $this->update_meta( $id, $key, $value, $duplicate );
	}

	/**
	 * Update meta.
	 *
	 * @param bool|int    $id Media id.
	 * @param bool|string $key Meta key.
	 * @param bool|string $value Meta value.
	 * @param bool        $duplicate Duplicate meta or not.
	 *
	 * @return bool|false|int
	 */
	public function update_meta( $id = false, $key = false, $value = false, $duplicate = false ) {
		if ( false === $id ) {
			return false;
		}
		if ( false === $key ) {
			return false;
		}
		if ( false === $value ) {
			return false;
		}
		$value = maybe_serialize( $value );

		if ( true === $duplicate ) {
			$media_meta = $this->model->insert(
				array(
					'media_id'   => $id,
					'meta_key'   => $key, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- Column of rtMedia custom meta table (rt_rtm_media_meta), not WP post meta; not a WP_Query.
					'meta_value' => $value, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value -- Column of rtMedia custom meta table (rt_rtm_media_meta), not WP post meta; not a WP_Query.
				)
			);
		} elseif ( false !== $this->get_single_meta( $id, $key ) ) {
				$meta       = array( 'meta_value' => $value ); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value -- Column of rtMedia custom meta table (rt_rtm_media_meta), not WP post meta; not a WP_Query.
				$where      = array(
					'media_id' => $id,
					'meta_key' => $key, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- Column of rtMedia custom meta table (rt_rtm_media_meta), not WP post meta; not a WP_Query.
				);
				$media_meta = $this->model->update( $meta, $where );
		} else {
			$media_meta = $this->model->insert(
				array(
					'media_id'   => $id,
					'meta_key'   => $key, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- Column of rtMedia custom meta table (rt_rtm_media_meta), not WP post meta; not a WP_Query.
					'meta_value' => $value, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value -- Column of rtMedia custom meta table (rt_rtm_media_meta), not WP post meta; not a WP_Query.
				)
			);
		}

		return $media_meta;
	}

	/**
	 * Delete meta for media id.
	 *
	 * @param bool|int    $id Media id.
	 * @param bool|string $key Meta key.
	 *
	 * @return array|bool
	 */
	public function delete_meta( $id = false, $key = false ) {
		if ( false === $id ) {
			return false;
		}

		if ( false === $key ) {
			$where = array( 'media_id' => $id );
		} else {
			$where = array(
				'media_id' => $id,
				'meta_key' => $key, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- Column of rtMedia custom meta table (rt_rtm_media_meta), not WP post meta; not a WP_Query.
			);
		}

		return $this->model->delete( $where );
	}
}
