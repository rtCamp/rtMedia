<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of BPMediaPrivacySettings
 *
 * @author saurabh
 */
class BPMediaPrivacySettings {

	/**
	 *
	 */
	function __construct() {
		$this->progress = new rtProgress();

	}

	function ui($tabs, $tab){
		$idle_class = 'nav-tab';
        $active_class = 'nav-tab nav-tab-active';
		$tabs[] = array(
                'href' => bp_get_admin_url(add_query_arg(array('page' => 'bp-media-privacy'), 'admin.php')),
                'title' => __('Update BuddyPress Media Database', BP_MEDIA_TXT_DOMAIN),
                'name' => __('Update Database', BP_MEDIA_TXT_DOMAIN),
                'class' => ($tab == 'bp-media-privacy') ? $active_class : $idle_class
            );
		return $tabs;
	}

	function get_completed_count(){
		global $wpdb;
		$query =
                    "SELECT	COUNT(*) as Finished
	FROM
		$wpdb->posts RIGHT JOIN $wpdb->postmeta on wp_postmeta.post_id = wp_posts.id
	WHERE
		`meta_key` = 'bp_media_privacy' AND
		( post_mime_type LIKE 'image%' OR post_mime_type LIKE 'audio%' OR post_mime_type LIKE 'video%' OR post_type LIKE 'bp_media_album')";
            return $result = $wpdb->get_results($query);
	}

	function get_total_count(){
		global $wpdb;
		$query =
                    "SELECT	COUNT(*) as Total
	FROM
		$wpdb->posts RIGHT JOIN $wpdb->postmeta on wp_postmeta.post_id = wp_posts.id
	WHERE
		`meta_key` = 'bp-media-key' AND
		( post_mime_type LIKE 'image%' OR post_mime_type LIKE 'audio%' OR post_mime_type LIKE 'video%' OR post_type LIKE 'bp_media_album')";
            $result = $wpdb->get_results($query);
		return $result;
	}

	function query(){

	}

	function init(){
		$total = $this->get_total_count();
		$total = $total[0];
		$finished = $this->get_completed_count();
		$finished = $finished[0];


		echo '<div id="rtprivacyinstaller">';

		foreach($total as $type=>$count){
			echo '<div class="rtprivacytype" id="'.strtolower($type).'">';
			echo '<strong>';
			echo '<span class="finished">'.$finished->Finished .'</span> / <span class="total">'.$count.'</span>';
			echo '</strong>';
			$progress =100;
			if($count!=0){
				$todo = $count-$finished->Finished;
				$steps = ceil($todo/20);
				$laststep = $todo%20;
				$progress = $this->progress->progress($finished->Finished,$count);
				echo '<input type="hidden" value="'.$finished->Finished.'" name="finished"/>';
				echo '<input type="hidden" value="'.$count.'" name="total"/>';
				echo '<input type="hidden" value="'.$todo.'" name="todo"/>';
				echo '<input type="hidden" value="'.$steps.'" name="steps"/>';
				echo '<input type="hidden" value="'.$laststep.'" name="laststep"/>';

			}
			$this->progress->progress_ui($progress);
			echo "<br>";
			echo '</div>';
		}
		echo '<button id="rtprivacyinstall" class="button button-primary">';
		_e('Start',BP_MEDIA_TXT_DOMAIN);
		echo '</button>';
		echo '</div>';
	}
}

?>
