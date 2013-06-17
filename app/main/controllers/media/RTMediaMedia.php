<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of RTMediaMedia
 *
 * @author Udit Desai <udit.desai@rtcamp.com>
 */
class RTMediaMedia {

	/**
	 *DB Model object to interact on Database operations
	 *
	 * @var type
	 */
	var $rt_media_media_model;

	/**
	 *
	 */
	public function __construct() {

		$this->rt_media_media_model = new RTMediaMediaModel();
	}

	static function media_nonce_generator($echo=true) {

		if($echo) {
			wp_nonce_field('rt_media_media_nonce','rt_media_media_nonce');
		} else {
			$token = array(
				'action' => 'rt_media_media_nonce',
				'nonce' => wp_create_nonce('rt_media_media_nonce')
			);

			return json_encode($token);
		}
	}

	/**
	 * Method verifies the nonce passed while performing any CRUD operations
	 * on the media.
	 *
	 * @param type $mode
	 * @return boolean
	 */
	function verify_nonce($mode) {

		$nonce = $_REQUEST["rt_media_{$mode}_media_nonce"];
		$mode = $_REQUEST['mode'];

		if (wp_verify_nonce($nonce, 'rt_media_' . $mode))
			return true;
		else
			return false;
	}

	/**
	 * Adds a hook to delete_attachment tag called
	 * when a media is deleted externally out of rtMedia context
	 */
	public function delete_hook() {
		add_action('delete_attachment',array($this,'delete'));
	}

	function add_taxonomy($attachments, $taxonomies) {

		foreach ($attachments as $id) {

			foreach ($taxonomies as $taxonomy=>$terms) {
				if( !taxonomy_exists($taxonomy) ) {
					register_taxonomy($taxonomy, 'attachment', array(
						'label' => __($taxonomy, 'rt-media'),
						'show_admin_column' => true,
						'hierarchical' => false,
						'capabilities' => array(
							'manage_terms' => 'manage_categories',
							'edit_terms' => 'manage_categories',
							'delete_terms' => 'manage_categories',
							'assign_terms' => 'edit_posts'
						),
					));
				}

				wp_set_object_terms($id, $terms, $taxonomy);
			}
		}

	}

	function add_meta($attachments, $custom_fields) {

		foreach ($attachments as $id) {
			$row = array( 'media_id' => $id );

			foreach ($custom_fields as $key => $value) {

				if( !is_null($value) ) {
					$row['meta_key'] = $key;
					$row['meta_value'] = $value;
					$status = $this->rt_media_media_model->add_meta($row);

					if (get_class($status) == 'WP_Error' || $status == 0)
						return false;
				}
			}
		}

		return true;
	}

	/**
	 * Helper method to check for multisite - will add a few additional checks
	 * for handling taxonomies
	 * @return boolean
	 */
	function is_multisite() {
		return is_multisite();
	}

	/**
	 * Generic method to add a media
	 *
	 * @param type $uploaded
	 * @param type $file_object
	 * @return type
	 */
	function add($uploaded, $file_object) {

		/* action to perform any task before adding a media */
		do_action('rt_media_before_add_media', $this);

		/* Generate media details required to feed in database */
		$attachments = $this->generate_post_array($uploaded, $file_object);

		/* Insert the media as an attachment in Wordpress context */
		$attachment_ids = $this->insert_attachment($attachments, $file_object);

		/* check for multisite and if valid then add taxonomies */
		if(!$this->is_multisite())
			$this->add_taxonomy($attachment_ids, $uploaded['taxonomy']);

		/* fetch custom fields and add them to meta table */
		$this->add_meta($attachment_ids, $uploaded['custom_fields']);

		/* if buddypress activity is enabled then insert an activity */
		if($this->activity_enabled())
			$this->insert_activity($uploaded);

		/* add media in rtMedia context */
		$this->rt_insert_media($attachment_ids,$uploaded);

		/* action to perform any task after adding a media */
		do_action('rt_media_after_add_media', $this);

		return $attachment_ids;

    }

	/**
	 * Generic method to update a media. media details can be changed from this method
	 *
	 * @param type $media_id
	 * @param type $meta
	 * @return boolean
	 */
	function update($id, $data, $media_id) {

		/* action to perform any task before updating a media */
		do_action('rt_media_before_update_media', $this);

		$defaults = array();
		$data = wp_parse_args($data, $defaults);
		$where = array( 'id' => $id );

		if(array_key_exists('media_title', $data) || array_key_exists('description', $data)) {
			$post_data['ID'] = $media_id;
			if(isset($data['media_title'])) {
				$post_data['post_title'] = $data['media_title'];
				$post_data['post_name'] = sanitize_title($data['media_title']);
			}
			if(isset($data['description'])) {
				$post_data['post_content'] = $data['description'];
				unset($data['description']);
			}
			wp_update_post($post_data);
		}

		$status = $this->rt_media_media_model->update($data, $where);

		if ($status == 0) {
			return false;
		} else {
			/* action to perform any task after updating a media */
			do_action('rt_media_after_update_media', $this);
			return true;
		}

	}

	/**
	 * Generic method to delete a media
	 *
	 * @param type $media_id
	 * @return boolean
	 */
	function delete($id, $media_id = false){

		do_action('rt_media_before_delete_media', $this);

		/* remove attachment --- confirm with saurabh */
		wp_update_post(array('ID'=>$media_id, 'post_parent'=>0));

		/* delete meta */
		$this->rt_media_media_model->delete_meta( array( 'media_id' => $media_id ) );

		$status = $this->rt_media_media_model->delete( array( 'id' => $id ) );

		if ($status == 0) {
			return false;
		} else {
			do_action('rt_media_after_delete_media', $this);
			return true;
		}
    }

	/**
	 * Move a media from one album to another
	 *
	 * @global type $wpdb
	 * @param type $media_id
	 * @param type $album_id
	 * @return boolean
	 */
	function move($media_id, $album_id) {

		global $wpdb;
		/* update the post_parent value in wp_post table */
		$status = $wpdb->update('wp_posts', array('post_parent' => $album_id), array('ID' => $media_id));

		if (get_class($status) == 'WP_Error' || $status == 0) {
				return false;
		} else {
			/* update album_id, context, context_id and privacy in rtMedia context */
			$album_data = $this->rt_media_media_model->get_by_media_id($media_id);
			$data = array(
					'album_id' => $album_id,
					'context' => $album_data['result'][0]['context'],
					'context_id' => $album_data['result'][0]['context_id'],
					'privacy' => $album_data['result'][0]['privacy']
				);
			return $this->update($media_id, $data);
		}
	}

	/**
	 *  Imports attachment as media
	 */
	function import_attachment() {

	}

	/**
	 *
	 * @return boolean
	 */
	function activity_enabled() {
		// as of now disabled; later on we need to check from the activity settings from BuddyPress
		return false;
	}

	/**
	 *
	 * @param type $uploaded
	 * @param type $file_object
	 * @return type
	 */
	function generate_post_array($uploaded, $file_object) {
        foreach ($file_object as $file) {
            $attachments[] = array(
                'post_mime_type' => $file['type'],
                'guid' => $file['url'],
                'post_title' => $uploaded['title'] ? $uploaded['title'] : $file['name'],
                'post_content' => $uploaded['description'] ? $uploaded['description'] : '',
                'post_parent' => $uploaded['album_id'] ? $uploaded['album_id'] : 0,
				'post_author' => $uploaded['media_author']
            );
        }
        return $attachments;
    }

	/**
	 *
	 * @param type $attachments
	 * @param type $file_object
	 * @return type
	 * @throws Exception
	 */
    function insert_attachment($attachments, $file_object) {
        foreach ($attachments as $key => $attachment) {
            $attachment_id = wp_insert_attachment($attachment, $file_object[$key]['file'], $attachment['post_parent']);
            if (!is_wp_error($attachment_id)) {
//                add_filter('intermediate_image_sizes', array($this, 'rt_media_image_sizes'), 99);
                wp_update_attachment_metadata($attachment_id, wp_generate_attachment_metadata($attachment_id, $file_object[$key]['file']));
            } else {
                unlink($file_object[$key]['file']);
                throw new Exception(__('Error creating attachment for the media file, please try again', 'buddypress-media'));
            }
            $updated_attachment_ids[] = $attachment_id;
        }

        return $updated_attachment_ids;
    }

	/**
	 *
	 * @param type $sizes
	 * @return type
	 */
    function rt_media_image_sizes($sizes) {
        return array('bp_media_thumbnail', 'bp_media_activity_image', 'bp_media_single_image');
    }

	/**
	 *
	 * @param type $attributes
	 */
	function rt_insert_album($attributes) {

		$this->rt_media_media_model->insert($attributes);
	}

	/**
	 *
	 * @param type $attachment_ids
	 * @param type $uploaded
	 */
    function rt_insert_media($attachment_ids,$uploaded){

		$defaults = array(
			'activity_id' => $this->activity_enabled(),
			'privacy' => false
		);

		$uploaded = wp_parse_args($uploaded, $defaults);

        $blog_id = get_current_blog_id();

		foreach ($attachment_ids as $id) {
			$attachment = get_post($id, ARRAY_A);
            $mime_type = explode('/',$attachment['post_mime_type']);
            $this->rt_media_media_model->insert(
                    array(
                        'blog_id' => $blog_id,
                        'media_id' => $id,
						'album_id' => $uploaded['album_id'],
						'media_author' => $attachment['post_author'],
						'media_title' => $attachment['post_title'],
                        'media_type' => $mime_type[0],
                        'context' => $uploaded['context'],
                        'context_id' => $uploaded['context_id'],
                        'activity_id' => $uploaded['activity_id'],
                        'privacy' => $uploaded['privacy']
            ));
        }
    }

	/**
	 *
	 * @global type $bp
	 * @return boolean
	 */
    function insert_activity(){
        global $bp;
        if (function_exists('bp_activity_add')) {
            $update_activity_id = false;
            if (!is_object($media)) {
                try {
                    $media = new BPMediaHostWordpress($media);
                } catch (exception $e) {
                    return false;
                }
            }
            $activity_content = $media->get_media_activity_content();
            $args = array(
                'action' => apply_filters('bp_media_added_media', sprintf(__('%1$s added a %2$s', 'buddypress-media'), bp_core_get_userlink($media->get_author()), '<a href="' . $media->get_url() . '">' . $media->get_media_activity_type() . '</a>')),
                'content' => $activity_content,
                'primary_link' => $media->get_url(),
                'item_id' => $media->get_id(),
                'type' => 'activity_update',
                'user_id' => $media->get_author()
            );

            $hidden = apply_filters('bp_media_force_hide_activity', $hidden);

            if ($activity || $hidden) {
                $args['secondary_item_id'] = -999;
            } else {
                $update_activity_id = get_post_meta($media->get_id(), 'bp_media_child_activity', true);
                if ($update_activity_id) {
                    $args['id'] = $update_activity_id;
                    $args['secondary_item_id'] = false;
                }
            }

            if ($hidden && !$activity) {
                do_action('bp_media_album_updated', $media->get_album_id());
            }

            if ($group) {
                $group_info = groups_get_group(array('group_id' => $group));
                $args['component'] = $bp->groups->id;
                $args['item_id'] = $group;
                if ('public' != $group_info->status) {
                    $args['hide_sitewide'] = 1;
                }
            }

            $activity_id = BPMediaFunction::record_activity($args);

            if ($group)
                bp_activity_update_meta($activity_id, 'group_id', $group);

            if (!$update_activity_id)
                add_post_meta($media->get_id(), 'bp_media_child_activity', $activity_id);
        }
    }
}

?>