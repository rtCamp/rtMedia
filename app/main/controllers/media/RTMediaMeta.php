<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of RTMediaMeta
 *
 * @author saurabh
 */
class RTMediaMeta {

	/**
	 * Initialises the model object of the media object for meta.
	 */
	public function __construct(){
		$this->model = new RTDBModel( 'rtm_media_meta', false, 10, true );
	}

	/**
	 * Get Meta
	 *
	 * @param boolean $id
	 *
	 * @param boolean $key
	 *
	 * @return boolean
	 */
	public function get_meta( $id = false, $key = false ){
		if ( $id === false ){
			return false;
		}
		if ( $key === false ){
			return $this->get_all_meta( $id );
		} else {
			return $this->get_single_meta( $id, $key );
		}
	}

	/**
	 * Get all meta data.
	 *
	 * @param boolean $id
	 *
	 * @return mixed unserialized data
	 */
	private function get_all_meta( $id = false ){
		if ( $id === false ){
			return false;
		}

		return maybe_unserialize( $this->model->get( array( 'media_id' => $id ) ) );
	}

	/**
	 * Get single meta data.
	 *
	 * @param boolean $id
	 *
	 * @param boolean $key
	 *
	 * @return mixed unserialized data if true, or boolean false
	 */
	private function get_single_meta( $id = false, $key = false ){
		if ( $id === false ){
			return false;
		}
		if ( $key === false ){
			return false;
		}
		$value = $this->model->get( array( 'media_id' => $id, 'meta_key' => $key ) );
		if ( isset( $value[ 0 ] ) ){
			return maybe_unserialize( $value[ 0 ]->meta_value );
		} else {
			return false;
		}
	}

	/**
	 * Add meta
	 *
	 * @param boolean $id
	 *
	 * @param boolean $key
	 *
	 * @param boolean $value
	 *
	 * @param boolean $duplicate
	 *
	 * @return mixed unserialized data
	 */
	public function add_meta( $id = false, $key = false, $value = false, $duplicate = false ){
		return $this->update_meta( $id, $key, $value, $duplicate );
	}

	/**
	 * Update meta
	 *
	 * @param boolean $id
	 *
	 * @param boolean $key
	 *
	 * @param boolean $value
	 *
	 * @param boolean $duplicate
	 *
	 * @return mixed $media_meta
	 */
	public function update_meta( $id = false, $key = false, $value = false, $duplicate = false ){
		if ( $id === false ){
			return false;
		}
		if ( $key === false ){
			return false;
		}
		if ( $value === false ){
			return false;
		}
		$value = maybe_serialize( $value );

		if ( $duplicate === true ){
			$media_meta = $this->model->insert( array( 'media_id' => $id, 'meta_key' => $key, 'meta_value' => $value ) );
		} else {
			if ( $this->get_single_meta( $id, $key ) ){
				$meta       = array( 'meta_value' => $value );
				$where      = array( 'media_id' => $id, 'meta_key' => $key );
				$media_meta = $this->model->update( $meta, $where );
			} else {
				$media_meta = $this->model->insert( array( 'media_id' => $id, 'meta_key' => $key, 'meta_value' => $value ) );
			}
		}

		return $media_meta;
	}

	/**
	 * Delete meta
	 *
	 * @param boolean $id
	 *
	 * @param boolean $key
	 *
	 * @return null
	 */
	public function delete_meta( $id = false, $key = false ){
		if ( $id === false ){
			return false;
		}
		if ( $key === false ){
			$where = array( 'media_id' => $id );
		} else {
			$where = array( 'media_id' => $id, 'meta_key' => $key );
		}

		return $this->model->delete( $where );
	}

}