<?php

/**
 * This function should include all classes and functions that access the database.
 * In most BuddyPress components the database access classes are treated like a model,
 * where each table has a class that can be used to create an object populated with a row
 * from the corresponding database table.
 * 
 * By doing this you can easily save, update and delete records using the class, you're also
 * abstracting database access.
 */

class rt_media_TableName {
	var $id;
	var $field_1;
	var $field_2;
	var $field_3;
	
	/**
	 * rt_media_tablename()
	 *
	 * This is the constructor, it is auto run when the class is instantiated.
	 * It will either create a new empty object if no ID is set, or fill the object
	 * with a row from the table if an ID is provided.
	 */
	function rt_media_tablename( $id = null ) {
		global $wpdb, $bp;
		
		if ( $id ) {
			$this->id = $id;
			$this->populate( $this->id );
		}
	}
	
	/**
	 * populate()
	 *
	 * This method will populate the object with a row from the database, based on the
	 * ID passed to the constructor.
	 */
	function populate() {
		global $wpdb, $bp, $creds;
		
		if ( $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$bp->rt_media->table_name} WHERE id = %d", $this->id ) ) ) {
			$this->field_1 = $row->field_1;
			$this->field_2 = $row->field_2;
			$this->field_3 = $row->field_3;
		}
	}
	
	/**
	 * save()
	 *
	 * This method will save an object to the database. It will dynamically switch between
	 * INSERT and UPDATE depending on whether or not the object already exists in the database.
	 */
	
	function save() {
		global $wpdb, $bp;
		
		/***
		 * In this save() method, you should add pre-save filters to all the values you are saving to the
		 * database. This helps with two things -
		 * 
		 * 1. Blanket filtering of values by plugins (for example if a plugin wanted to force a specific 
		 *	  value for all saves)
		 * 
		 * 2. Security - attaching a wp_filter_kses() call to all filters, so you are not saving
		 *	  potentially dangerous values to the database.
		 *
		 * It's very important that for number 2 above, you add a call like this for each filter to
		 * 'rt-sneakerfiles-filters.php'
		 *
		 *   add_filter( 'example_data_fieldname1_before_save', 'wp_filter_kses' );
		 */	
		
		$this->fieldname1 = apply_filters( 'rt_media_data_fieldname1_before_save', $this->fieldname1, $this->id );
		$this->fieldname2 = apply_filters( 'rt_media_data_fieldname2_before_save', $this->fieldname2, $this->id );
		
		/* Call a before save action here */
		do_action( 'rt_media_data_before_save', $this );
						
		if ( $this->id ) {
			// Update
			$result = $wpdb->query( $wpdb->prepare( 
					"UPDATE {$bp->rt_media->table_name} SET
						field_1 = %d,
						field_2 = %d,
						field_3 = %d
					WHERE id = %d",
						$this->field_1,
						$this->field_2,
						$this->field_3,
						$this->id 
					) );
		} else {
			// Save
			$result = $wpdb->query( $wpdb->prepare( 
					"INSERT INTO {$bp->rt_media->table_name} (
						field_1,
						field_2,
						field_3 
					) VALUES ( 
						%d, %d, %d 
					)", 
						$this->field_1,
						$this->field_2,
						$this->field_3 
					) );
		}
				
		if ( !$result )
			return false;
		
		if ( !$this->id ) {
			$this->id = $wpdb->insert_id;
		}	
		
		/* Add an after save action here */
		do_action( 'rt_media_data_after_save', $this );
		
		return $result;
	}

	/**
	 * delete()
	 *
	 * This method will delete the corresponding row for an object from the database.
	 */	
	function delete() {
		global $wpdb, $bp;
		
		return $wpdb->query( $wpdb->prepare( "DELETE FROM {$bp->rt_media->table_name} WHERE id = %d", $this->id ) );
	}

	/* Static Functions */

	/**
	 * Static functions can be used to bulk delete items in a table, or do something that
	 * doesn't necessarily warrant the instantiation of the class.
	 *
	 * Look at bp-core-classes.php for examples of mass delete.
	 */

	function delete_all() {

	}

	function delete_by_user_id() {

	}
}

?>