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
                'title' => __('Update BuddyPress Media Database', 'buddypress-media'),
                'name' => __('Update Database', 'buddypress-media'),
                'class' => ($tab == 'bp-media-privacy') ? $active_class : $idle_class
            );
		return $tabs;
	}

	function get_completed_count(){
		global $wpdb;
		$query =
                    "SELECT	COUNT(*) as Finished
	FROM
		$wpdb->posts INNER JOIN $wpdb->postmeta ON $wpdb->postmeta.post_id = $wpdb->posts.id
	WHERE
		$wpdb->postmeta.meta_key = 'bp_media_privacy' AND
		( $wpdb->posts.post_type LIKE 'attachment' OR $wpdb->posts.post_type LIKE 'bp_media_album')";
            $result = $wpdb->get_results($query);
			return $result;
	}

	function get_total_count(){
		global $wpdb;
		$query =
                    "SELECT	COUNT(*) as Total
	FROM
		$wpdb->posts INNER JOIN $wpdb->postmeta ON $wpdb->postmeta.post_id = $wpdb->posts.id
	WHERE
		$wpdb->postmeta.meta_key = 'bp-media-key' AND
		( $wpdb->posts.post_type LIKE 'attachment' OR $wpdb->posts.post_type LIKE 'bp_media_album')";
			$result = $wpdb->get_results($query);
			return $result;
	}


	function init(){
		$total = $this->get_total_count();
		$finished = $this->get_completed_count();
		$total = $total[0];
		$finished = $finished[0];

		//(isset($total) && isset($finished) && is_array($total) && is_array($finished)){
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
		_e('Start','buddypress-media');
		echo '</button>';
		echo '</div>';
		}
	//}
}

?>
