<?php
/**
 * Base class for any Database Model like Media, Album etc.
 *
 * @package    rtMedia
 *
 * @author udit
 */

if ( ! class_exists( 'RTDBModel' ) ) {
	/**
	 * Base class for any Database Model like Media, Album etc.
	 */
	class RTDBModel {

		/**
		 * Database table linked to the model.
		 * All the queries will be fired on that table or with the join in this table.
		 *
		 * @var string $table_name
		 */
		public $table_name;

		/**
		 * Number of rows per page to be displayed
		 *
		 * @var int $per_page
		 */
		public $per_page;

		/**
		 * Var mu_single_table.
		 *
		 * @var string $mu_single_table
		 */
		public $mu_single_table;

		/**
		 * RTDBModel class constructor.
		 *
		 * @param string  $table_name Table name for model.
		 * @param boolean $withprefix Set true if $tablename is with prefix otherwise it will prepend WordPress prefix with "rt_".
		 * @param int     $per_page Per Page.
		 * @param bool    $mu_single_table single table.
		 */
		public function __construct( $table_name, $withprefix = false, $per_page = 10, $mu_single_table = false ) {
			$this->mu_single_table = $mu_single_table;
			$this->set_table_name( $table_name, $withprefix );
			$this->set_per_page( $per_page );
		}

		/**
		 * Set table name for class.
		 *
		 * @global object $wpdb
		 *
		 * @param string $table_name Table name.
		 * @param mixed  $withprefix With prefix or not.
		 */
		public function set_table_name( $table_name, $withprefix = false ) {
			global $wpdb;
			if ( ! $withprefix ) {
				$table_name = ( ( $this->mu_single_table ) ? $wpdb->base_prefix : $wpdb->prefix ) . 'rt_' . $table_name;
			}
			$this->table_name = $table_name;
		}

		/**
		 * Set number of rows per page for pagination
		 *
		 * @param integer $per_page Rows per page.
		 */
		public function set_per_page( $per_page ) {
			$this->per_page = $per_page;
		}

		/**
		 * Magic Method for getting DB rows by particular column.
		 * E.g., get_by_<columnName>(params)
		 *
		 * @global object $wpdb
		 *
		 * @param string $name Added get_by_<coulmname>(value,pagging=true,page_no=1).
		 * @param array  $arguments Arguments.
		 *
		 * @return bool|array  result array
		 */
		public function __call( $name, $arguments ) {
			$column_name = str_replace( 'get_by_', '', strtolower( $name ) );
			$paging      = false;
			$page        = 1;
			if ( $arguments && ! empty( $arguments ) ) {
				if ( ! isset( $arguments[1] ) ) {
					$paging = true;
				} else {
					$paging = $arguments[1];
				}

				if ( ! isset( $arguments[2] ) ) {
					$page = 1;
				} else {
					$page = $arguments[2];
				}

				$this->per_page         = apply_filters( 'rt_db_model_per_page', $this->per_page, $this->table_name );
				$return_array           = array();
				$return_array['result'] = false;

				global $wpdb;
				$return_array['total'] = intval( $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM  {$this->table_name} WHERE {$column_name} = %s", $arguments[0] ) ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

				if ( $return_array['total'] > 0 ) {
					$other = '';
					if ( $paging ) {
						if ( intval( $this->per_page ) < 0 ) {
							$this->per_page = 1;
						}

						$offset = ( $page - 1 ) * $this->per_page;

						if ( ! is_integer( $offset ) ) {
							$offset = 0;
						}

						if ( intval( $offset ) < 0 ) {
							$offset = 0;
						}

						if ( $offset <= $return_array['total'] ) {
							$other = ' LIMIT ' . $offset . ',' . $this->per_page;
						} else {
							return false;
						}
					}

					$return_array['result'] = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$this->table_name} WHERE {$column_name} = %s {$other}", $arguments[0] ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				}

				return $return_array;
			} else {
				return false;
			}
		}

		/**
		 * Insert.
		 *
		 * @global object $wpdb
		 *
		 * @param array $row Row array.
		 *
		 * @return integer
		 */
		public function insert( $row ) {
			global $wpdb;
			$insertdata = array();
			foreach ( $row as $key => $val ) {
				if ( null !== $val ) {
					$insertdata[ $key ] = $val;
				}
			}

			$wpdb->insert( $this->table_name, $insertdata );

			return $wpdb->insert_id;
		}

		/**
		 * Update table.
		 *
		 * @global object $wpdb
		 *
		 * @param array $data Data to update.
		 * @param array $where Where clause.
		 *
		 * @return false|int
		 */
		public function update( $data, $where ) {
			global $wpdb;

			return $wpdb->update( $this->table_name, $data, $where );
		}

		/**
		 * Get all the rows according to the columns set in $columns parameter.
		 * offset and rows per page can also be passed for pagination.
		 *
		 * @global object  $wpdb
		 *
		 * @param array    $columns Columns to get.
		 * @param int|bool $offset Offset for query.
		 * @param int|bool $per_page Per page data.
		 * @param string   $order_by Order by.
		 *
		 * @return array
		 */
		public function get( $columns, $offset = false, $per_page = false, $order_by = 'id desc' ) {
			global $wpdb;

			$select = "SELECT * FROM {$this->table_name}";
			$where  = ' where 2=2 ';
			foreach ( $columns as $colname => $colvalue ) {
				if ( is_array( $colvalue ) ) {
					if ( ! isset( $colvalue['compare'] ) ) {
						$compare = 'IN';
					} else {
						$compare = $colvalue['compare'];
					}
					if ( ! isset( $colvalue['value'] ) ) {
						$colvalue['value'] = esc_sql( $colvalue );
					}
					$col_val_comapare = ( is_array( $colvalue['value'] ) ) ? '(\'' . implode( "','", $colvalue['value'] ) . '\')' : '(\'' . $colvalue['value'] . '\')';
					$where           .= " AND {$this->table_name}.{$colname} {$compare} {$col_val_comapare}";
				} else {
					$where .= $wpdb->prepare( " AND {$this->table_name}.{$colname} = %s", $colvalue ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				}
			}
			$sql = $select . $where;

			$sql .= " ORDER BY {$this->table_name}.$order_by";
			if ( false !== $offset ) {
				if ( ! is_integer( $offset ) ) {
					$offset = 0;
				}
				if ( intval( $offset ) < 0 ) {
					$offset = 0;
				}

				if ( ! is_integer( $per_page ) ) {
					$per_page = 0;
				}
				if ( intval( $per_page ) < 0 ) {
					$per_page = 1;
				}
				$sql .= $wpdb->prepare( ' LIMIT %d, %d', $offset, $per_page );

			}
			return $wpdb->get_results( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		}

		/**
		 * Delete row.
		 *
		 * @global object $wpdb
		 *
		 * @param array $where Where clause.
		 *
		 * @return false|int
		 */
		public function delete( $where ) {
			global $wpdb;

			return $wpdb->delete( $this->table_name, $where );
		}
	}
}
