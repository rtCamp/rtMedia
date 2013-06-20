<?php

/**
 * Description of BPMediaModel
 *
 * @author joshua
 */
class RTMediaMediaModel extends RTDBModel {

    function __construct() {
        parent::__construct('rtm_media');
    }

	/**
	 *
	 * @param type $name
	 * @param type $arguments
	 * @return type
	 */
    function __call($name, $arguments) {
        $result = parent::__call($name, $arguments);
        if (!$result['result']) {
            $result['result'] = $this->populate_results_fallback($name, $arguments);
        }
        return $result;
    }

	/**
	 *
	 * @global type $wpdb
	 * @param type $columns
	 * @param type $offset
	 * @param type $per_page
	 * @param type $order_by
	 * @return type
	 */
	function get($columns, $offset=false, $per_page=false, $order_by= 'media_id desc') {
        $select = "SELECT * FROM {$this->table_name}";
        $join = "" ;
        $where = " where 2=2 " ;
        $temp = 65;
        foreach ($columns as $colname => $colvalue) {
            if(strtolower($colname) =="meta_query"){
                foreach($colvalue as $meta_query){
                    if(!isset($meta_query["compare"])){
                        $meta_query["compare"] = "=";
                    }
                    $tbl_alias = chr($temp++);
                    $join .= " LEFT JOIN {$this->meta_table_name} {$tbl_alias} ON {$this->table_name}.media_id = {$tbl_alias}.media_id ";
                    $where .= " AND  ({$tbl_alias}.meta_key = '{$meta_query["key"]}' and  {$tbl_alias}.meta_value  {$meta_query["compare"]}  '{$meta_query["value"]}' ) ";
                }
            }else{
				if(is_array($colvalue)) {
					if(!isset($colvalue['compare']))
						$colvalue['compare'] = 'IN';
					$where .= " AND {$this->table_name}.{$colname} {$colvalue['compare']} ('". implode("','", $colvalue['value'])."')";

				} else
					$where .= " AND {$this->table_name}.{$colname} = '{$colvalue}'";
            }
        }
        $sql = $select . $join . $where ;

		$sql .= " ORDER BY {$this->table_name}.$order_by";

		if(is_integer($offset) && is_integer($per_page)) {
			$sql .= ' LIMIT ' . $offset . ',' . $per_page;
		}
        global $wpdb;
        return $wpdb->get_results($sql);
    }

	/**
	 *
	 * @param type $name
	 * @param type $arguments
	 * @return type
	 */
    function populate_results_fallback($name, $arguments) {
        $result['result'] = false;
        if ('get_by_media_id' == $name && isset($arguments[0]) && $arguments[0]) {

            $result['result'][0]->media_id = $arguments[0];

            $post_type = get_post_field('post_type', $arguments[0]);
            if ('attachment' == $post_type) {
                $post_mime_type = explode('/', get_post_field('post_mime_type', $arguments[0]));
                $result['result'][0]->media_type = $post_mime_type[0];
            } elseif ('bp_media_album' == $post_type) {
                $result['result'][0]->media_type = 'bp_media_album';
            } else {
                $result['result'][0]->media_type = false;
            }

            $result['result'][0]->context_id = intval(get_post_meta($arguments[0], 'bp-media-key', true));
            if ($result['result'][0]->context_id > 0)
                $result['result'][0]->context = 'profile';
            else
                $result['result'][0]->context = 'group';

            $result['result'][0]->activity_id = get_post_meta($arguments[0], 'bp_media_child_activity', true);

            $result['result'][0]->privacy = get_post_meta($arguments[0], 'bp_media_privacy', true);
        }
        return $result['result'];
    }

	/**
	 *
	 * @param type $columns
	 * @param type $offset
	 * @param type $per_page
	 * @param type $order_by
	 * @return type
	 */
    function get_media($columns, $offset, $per_page, $order_by = 'media_id desc') {
        if (is_multisite()) {
            $results = $this->get($columns, $offset, $per_page, "blog_id ,".$order_by);
        } else {
            $results = $this->get($columns, $offset, $per_page, $order_by);
        }
        return $results;
    }

	/**
	 *
	 * @global type $wpdb
	 * @param type $media_id
	 * @return type
	 */
    function get_media_meta($media_id){
        $media_query_str = "";
        if (is_array($media_id)){
            $sep = "";
            foreach($media_id as $mid){
                $media_query_str .= $sep . $mid;
                $sep= ",";
            }
        }else{
            $media_query_str .= $media_id;
        }

        global $wpdb;
        $sql = "SELECT * FROM {$wpdb->posts} LEFT JOIN {$this->table_name}
            ON {$wpdb->posts}.ID = {$this->table_name}.media_id
            WHERE {$wpdb->posts}.ID in ({$media_query_str});";
        return $wpdb->get_results($sql,ARRAY_A);
    }

	/**
	 *
	 * @param type $row
	 * @param type $new
	 * @return type
	 */
	function add_meta($row, $new = false) {

		if($new) {
			return parent::insert_meta($row);
		} else {

			$columns = $row;
			unset($columns['meta_value']);
			$existing_meta = parent::get_meta($columns);

			if(count($existing_meta)) {
				$meta = array('meta_value' => $row['meta_value']);
				$where = array('media_id' => $row['media_id'], 'meta_key' => $row['meta_key']);
				return parent::update_meta($meta, $where);
			}
			else
				return parent::insert_meta ($row);
		}
	}
}
?>
