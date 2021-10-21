<?php
/**
 * Handles rtMedia medias.
 *
 * @package rtMedia
 */

/**
 * Class to handle rtMedia medias.
 *
 * @author joshua
 */
class RTMediaModel extends RTDBModel {

	/**
	 * Meta table.
	 *
	 * @var string
	 */
	public $meta_table_name = '';

	/**
	 * RTMediaModel constructor.
	 */
	public function __construct() {
		parent::__construct( 'rtm_media', false, 10, true );
		$this->meta_table_name = 'rt_rtm_media_meta';
	}

	/**
	 * Get DB rows by particular column
	 *
	 * @param string $name Column Name.
	 * @param array  $arguments Arguments.
	 *
	 * @return array
	 */
	public function __call( $name, $arguments ) {
		$result = parent::__call( $name, $arguments );
		if ( ! $result['result'] ) {
			$result['result'] = $this->populate_results_fallback( $name, $arguments );
		}

		return $result;
	}

	/**
	 * Get column data.
	 *
	 * @global object $wpdb
	 *
	 * @param array  $columns column names.
	 * @param mixed  $offset Offset to get data.
	 * @param mixed  $per_page Rows Per page.
	 * @param string $order_by Order by condition.
	 * @param bool   $count_flag Count flag.
	 *
	 * @return array|int|string
	 */
	public function get( $columns, $offset = false, $per_page = false, $order_by = 'media_id desc', $count_flag = false ) {
		global $wpdb, $rtmedia_interaction;

		$select = 'SELECT ';

		if ( $count_flag ) {
			$select .= 'count(*) ';
		} else {
			$select .= "{$this->table_name}.* ";
		}

		$from  = " FROM {$this->table_name} ";
		$join  = '';
		$where = ' where 2=2 ';
		if ( is_multisite() ) {
			$where .= $wpdb->prepare( " AND {$this->table_name}.blog_id =%d ", get_current_blog_id() ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		}
		$temp = 65;

		$columns = apply_filters( 'rtmedia-model-query-columns', $columns, $count_flag ); // phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores

		foreach ( (array) $columns as $colname => $colvalue ) {
			$colname = esc_sql( $colname );
			if ( 'meta_query' === strtolower( $colname ) ) {
				foreach ( $colvalue as $meta_query ) {
					if ( ! isset( $meta_query['compare'] ) ) {
						$meta_query['compare'] = '=';
					}
					$tbl_alias = esc_sql( chr( $temp ++ ) );
					if ( is_multisite() ) {
						$join .= " LEFT JOIN {$wpdb->base_prefix}{$this->meta_table_name} as {$tbl_alias} ON {$this->table_name}.id = {$tbl_alias}.media_id ";
					} else {
						$join .= " LEFT JOIN {$wpdb->prefix}{$this->meta_table_name} as {$tbl_alias} ON {$this->table_name}.id = {$tbl_alias}.media_id ";
					}
					$meta_query['compare'] = esc_sql( $meta_query['compare'] );
					if ( isset( $meta_query['value'] ) ) {
						$where .= $wpdb->prepare( " AND  ({$tbl_alias}.meta_key = %s and  {$tbl_alias}.meta_value  {$meta_query["compare"]}  %s ) ", $meta_query['key'], $meta_query['value'] ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
					} else {
						$where .= $wpdb->prepare( " AND  {$tbl_alias}.meta_key = %s ", $meta_query['key'] ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
					}
				}
			} else {
				if ( is_array( $colvalue ) ) {
					if ( ! isset( $colvalue['compare'] ) ) {
						$compare = 'IN';
					} else {
						$compare = $colvalue['compare'];
					}

					$tmp_val          = isset( $colvalue['value'] ) ? $colvalue['value'] : $colvalue;
					$col_val_comapare = ( is_array( $tmp_val ) ) ? implode( "','", esc_sql( $tmp_val ) ) : esc_sql( $tmp_val );

					if ( 'IS NOT' === $compare ) {
						$col_val_comapare = ! empty( $colvalue['value'] ) ? $colvalue['value'] : $col_val_comapare;
					}

					$compare = esc_sql( $compare );
					$where  .= " AND {$this->table_name}.{$colname} {$compare} ('{$col_val_comapare}')";
				} else {
					$where .= $wpdb->prepare( " AND {$this->table_name}.{$colname} = %s", $colvalue ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				}
			}
		}
		$qgroup_by = ' ';

		if ( $order_by ) {
			$order_by  = esc_sql( $order_by );
			$qorder_by = " ORDER BY {$this->table_name}.{$order_by}";
		} else {
			$qorder_by = '';
		}

		// phpcs:disable WordPress.NamingConventions.ValidHookName.UseUnderscores
		$select    = apply_filters( 'rtmedia-model-select-query', $select, $this->table_name );
		$join      = apply_filters( 'rtmedia-model-join-query', $join, $this->table_name );
		$where     = apply_filters( 'rtmedia-model-where-query', $where, $this->table_name, $join );
		$qgroup_by = apply_filters( 'rtmedia-model-group-by-query', $qgroup_by, $this->table_name );
		$qorder_by = apply_filters( 'rtmedia-model-order-by-query', $qorder_by, $this->table_name );
		// phpcs:enable WordPress.NamingConventions.ValidHookName.UseUnderscores

		$sql = $select . $from . $join . $where . $qgroup_by . $qorder_by;
		if ( false !== $offset ) {
			if ( ! is_integer( $offset ) ) {
				$offset = 0;
			}

			if ( intval( $offset ) < 0 ) {
				$offset = 0;
			}

			if ( ! is_integer( $per_page ) ) {
				$per_page = 1;
			}

			if ( intval( $per_page ) < 1 ) {
				$per_page = 1;
			}

			// filter added to change the LIMIT.
			$limit = apply_filters( 'rtmedia-model-limit-query', ' LIMIT ' . $offset . ',' . $per_page, $offset, $per_page ); // phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores

			$sql .= $limit;
		}

		if ( ! $count_flag ) {
			return $wpdb->get_results( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		} else {
			return $wpdb->get_var( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		}
	}

	/**
	 * Populate fallback results.
	 *
	 * @param string $name Name for fallback data.
	 * @param array  $arguments Arguments to populate data.
	 *
	 * @return array|bool
	 */
	public function populate_results_fallback( $name, $arguments ) {
		$result['result'] = false;

		if ( 'get_by_media_id' === $name && isset( $arguments[0] ) && $arguments[0] ) {

			$result['result'][0]->media_id = $arguments[0];

			$post_type = get_post_field( 'post_type', $arguments[0] );
			if ( 'attachment' === $post_type ) {
				$post_mime_type                  = explode( '/', get_post_field( 'post_mime_type', $arguments[0] ) );
				$result['result'][0]->media_type = $post_mime_type[0];
			} elseif ( 'bp_media_album' === $post_type ) {
				$result['result'][0]->media_type = 'bp_media_album';
			} else {
				$result['result'][0]->media_type = false;
			}

			$result['result'][0]->context_id = intval( get_post_meta( $arguments[0], 'bp-media-key', true ) );
			if ( intval( $result['result'][0]->context_id ) > 0 ) {
				$result['result'][0]->context = 'profile';
			} else {
				$result['result'][0]->context = 'group';
			}

			$result['result'][0]->activity_id = get_post_meta( $arguments[0], 'bp_media_child_activity', true );

			$result['result'][0]->privacy = get_post_meta( $arguments[0], 'bp_media_privacy', true );
		}

		return $result['result'];
	}

	/**
	 * Get media.
	 *
	 * @param array  $columns Columns names.
	 * @param mixed  $offset Offset to get data.
	 * @param mixed  $per_page Rows Per page.
	 * @param string $order_by Order by condition.
	 * @param bool   $count_flag Count flag.
	 *
	 * @return array
	 */
	public function get_media( $columns, $offset = false, $per_page = false, $order_by = 'media_id desc', $count_flag = false ) {
		if ( is_multisite() ) {
			$order_by = 'blog_id' . ( ( $order_by ) ? ',' . $order_by : '' );
		}

		$results = $this->get( $columns, $offset, $per_page, $order_by, $count_flag );

		return $results;
	}

	/**
	 * Get user albums.
	 *
	 * @param  integer $author_id Author id.
	 * @param  mixed   $offset Offset to get data.
	 * @param  mixed   $per_page Rows Per page.
	 * @param  string  $order_by Order by condition.
	 *
	 * @return array $results
	 */
	public function get_user_albums( $author_id, $offset, $per_page, $order_by = 'media_id desc' ) {
		global $wpdb;

		if ( is_multisite() ) {
			$order_by = 'blog_id' . ( ( $order_by ) ? ',' . $order_by : '' );
		}

		$sql = "SELECT * FROM {$this->table_name}  ";
		// phpcs:disable WordPress.DB.PreparedSQL.NotPrepared
		if ( is_multisite() ) {
			$sub_sql = $wpdb->prepare( "SELECT DISTINCT (album_id) FROM {$this->table_name} WHERE media_author = %d AND album_id IS NOT NULL AND media_type <> 'album' AND context <> 'group' AND blog_id = %d", $author_id, get_current_blog_id() );
		} else {
			$sub_sql = $wpdb->prepare( "SELECT DISTINCT (album_id) FROM {$this->table_name} WHERE media_author = %d AND album_id IS NOT NULL AND media_type <> 'album' AND context <> 'group'", $author_id );
		}

		$where = $wpdb->prepare(
			" WHERE (id IN( $sub_sql ) OR (media_author = %d ))
			    AND media_type = 'album'
			    AND (context = 'profile' or context is NULL) ",
			$author_id
		);
		if ( is_multisite() ) {
			$where .= $wpdb->prepare( " AND {$this->table_name}.blog_id = %d ", get_current_blog_id() );
		}

		$where = apply_filters( 'rtmedia-get-album-where-query', $where, $this->table_name ); // phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores

		// phpcs:enable WordPress.DB.PreparedSQL.NotPrepared
		$order_by  = esc_sql( $order_by );
		$qorder_by = " ORDER BY {$this->table_name}.$order_by ";
		$sql      .= $where . $qorder_by;

		if ( false !== $offset ) {
			if ( ! is_integer( $offset ) ) {
				$offset = 0;
			}
			if ( intval( $offset ) < 0 ) {
				$offset = 0;
			}

			if ( ! is_integer( $per_page ) ) {
				$per_page = 1;
			}
			if ( intval( $per_page ) < 1 ) {
				$per_page = 1;
			}

			$sql .= ' LIMIT ' . $offset . ',' . $per_page;
		}

		$results = $wpdb->get_results( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		return $results;
	}

	/**
	 * Get group Albums from group id.
	 *
	 * @param  integer $group_id Group id.
	 * @param  mixed   $offset Offset for data.
	 * @param  mixed   $per_page Rows per page.
	 * @param  string  $order_by Order by condition.
	 *
	 * @return array $results
	 */
	public function get_group_albums( $group_id, $offset, $per_page, $order_by = 'media_id desc' ) {
		global $wpdb;
		if ( is_multisite() ) {
			$order_by = 'blog_id' . ( ( $order_by ) ? ',' . $order_by : '' );
		}

		if ( is_multisite() ) {
			$sub_sql = $wpdb->prepare( "SELECT DISTINCT (album_id) FROM {$this->table_name} WHERE context_id = %d AND album_id IS NOT NULL AND media_type != 'album' AND context = 'group' AND blog_id = %d", $group_id, get_current_blog_id() ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		} else {
			$sub_sql = $wpdb->prepare( "SELECT DISTINCT (album_id) FROM {$this->table_name} WHERE context_id = %d AND album_id IS NOT NULL AND media_type != 'album' AND context = 'group'", $group_id ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		}
		$sql = $wpdb->prepare( "SELECT * FROM {$this->table_name} WHERE id IN( $sub_sql ) OR (media_type = 'album' AND context_id = %d AND context = 'group')", $group_id ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		if ( is_multisite() ) {
			$sql .= $wpdb->prepare( " AND  {$this->table_name}.blog_id = %d ", get_current_blog_id() ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		}
		$order_by = esc_sql( $order_by );
		$sql     .= " ORDER BY {$this->table_name}.$order_by";

		if ( false !== $offset ) {
			if ( ! is_integer( $offset ) ) {
				$offset = 0;
			}
			if ( intval( $offset ) < 0 ) {
				$offset = 0;
			}

			if ( ! is_integer( $per_page ) ) {
				$per_page = 1;
			}
			if ( intval( $per_page ) < 1 ) {
				$per_page = 1;
			}

			$sql .= ' LIMIT ' . $offset . ',' . $per_page;
		}

		$results = $wpdb->get_results( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		return $results;
	}

	/**
	 * Get counts.
	 *
	 * @param  mixed $user_id User id.
	 * @param  mixed $where_query Where query.
	 *
	 * @return string $result
	 */
	public function get_counts( $user_id = false, $where_query = false ) {
		if ( ! $user_id && ! $where_query ) {
			return false;
		}
		global $wpdb, $rtmedia;

		$query = "SELECT {$this->table_name}.privacy, ";
		foreach ( $rtmedia->allowed_types as $type ) {
			$type['name'] = esc_sql( $type['name'] );
			$query       .= $wpdb->prepare( "SUM(CASE WHEN {$this->table_name}.media_type LIKE %s THEN 1 ELSE 0 END) as {$type['name']}, ", $type['name'] ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		}
		$query .= "SUM(CASE WHEN {$this->table_name}.media_type LIKE 'album' THEN 1 ELSE 0 END) as album
		FROM
			{$this->table_name} WHERE 2=2 ";

		if ( is_multisite() ) {
			$query .= $wpdb->prepare( " AND {$this->table_name}.blog_id = %d ", get_current_blog_id() ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		}

		$where_query_sql = '';

		if ( $where_query ) {
			foreach ( $where_query as $colname => $colvalue ) {
				$colname = esc_sql( $colname );
				if ( 'meta_query' !== strtolower( $colname ) ) {
					if ( is_array( $colvalue ) ) {
						if ( ! isset( $colvalue['compare'] ) ) {
							$compare = 'IN';
						} else {
							$compare = $colvalue['compare'];
						}
						if ( ! isset( $colvalue['value'] ) ) {
							$colvalue['value'] = $colvalue;
						}
						$compare          = esc_sql( $compare );
						$where_query_sql .= " AND {$this->table_name}.{$colname} {$compare} ('" . implode( "','", esc_sql( $colvalue['value'] ) ) . "')";
					} else {
						$where_query_sql .= $wpdb->prepare( " AND {$this->table_name}.{$colname} = %s", $colvalue ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
					}
				}
			}
		}

		$where_query_sql = apply_filters( 'rtmedia-get-counts-where-query', $where_query_sql ); // phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores
		$query           = $query . $where_query_sql . ' GROUP BY privacy limit 100';
		$result          = $wpdb->get_results( $query ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		if ( ! is_array( $result ) ) {
			return false;
		}

		return $result;
	}

	/**
	 * Get other album count.
	 *
	 * @param  integer $profile_id Profile id.
	 * @param  string  $context Context.
	 *
	 * @return int
	 */
	public function get_other_album_count( $profile_id, $context = 'profile' ) {
		global $wpdb;

		$global = RTMediaAlbum::get_globals();
		$sql    = $wpdb->prepare( "select distinct album_id from {$this->table_name} where 2=2 AND context = %s ", $context ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		if ( is_multisite() ) {
			$sql .= $wpdb->prepare( " AND {$this->table_name}.blog_id = %d ", get_current_blog_id() ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		}

		if ( is_array( $global ) && count( $global ) > 0 ) {
			$sql .= ' and album_id in (';
			$sep  = '';
			foreach ( $global as $id ) {
				$sql .= $sep . esc_sql( $id );
				$sep  = ',';
			}
			$sql .= ')';
		}

		if ( 'profile' === $context ) {
			$sql .= $wpdb->prepare( ' AND media_author=%d ', $profile_id );
		} else {
			if ( 'group' === $context ) {
				$sql .= $wpdb->prepare( ' AND context_id=%d ', $profile_id );
			}
		}

		$sql   .= 'limit 100';
		$result = $wpdb->get_results( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		if ( isset( $result ) ) {
			return count( $result );
		} else {
			return 0;
		}
	}

	/**
	 * Get all media count
	 *
	 * @return array
	 */
	public function get_media_count() {
		$remaining_album     = 0;
		$remaining_photos    = 0;
		$remaining_music     = 0;
		$remaining_videos    = 0;
		$remaining_all_media = 0;

		// Fetch the remaining media count.
		if ( class_exists( 'RTMediaNav' ) ) {
			global $bp;
			$rtmedia_nav_obj = new RTMediaNav();
			$other_count     = 0;

			if ( function_exists( 'bp_is_group' ) && bp_is_group() ) {

				if ( ! empty( $bp->groups->current_group->id ) ) {
					$counts      = $rtmedia_nav_obj->actual_counts( $bp->groups->current_group->id, 'group' );
					$other_count = $this->get_other_album_count( $bp->groups->current_group->id, 'group' );
				}
			} else {

				if ( function_exists( 'bp_displayed_user_id' ) ) {
					$counts      = $rtmedia_nav_obj->actual_counts( bp_displayed_user_id(), 'profile' );
					$other_count = $this->get_other_album_count( bp_displayed_user_id(), 'profile' );
				}
			}

			$remaining_all_media = ( ! empty( $counts['total']['all'] ) ) ? $counts['total']['all'] : 0;
			$remaining_album     = ( isset( $counts['total']['album'] ) ) ? $counts['total']['album'] + $other_count : 0;
			$remaining_photos    = ( ! empty( $counts['total']['photo'] ) ) ? $counts['total']['photo'] : 0;
			$remaining_videos    = ( ! empty( $counts['total']['video'] ) ) ? $counts['total']['video'] : 0;
			$remaining_music     = ( ! empty( $counts['total']['music'] ) ) ? $counts['total']['music'] : 0;
		}

		$media_counts = array(
			'all_media_count' => $remaining_all_media,
			'photos_count'    => $remaining_photos,
			'music_count'     => $remaining_music,
			'videos_count'    => $remaining_videos,
			'albums_count'    => $remaining_album,
		);

		return $media_counts;
	}
}
