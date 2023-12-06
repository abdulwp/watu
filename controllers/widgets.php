<?php
// displays dashboard stats widgets and maybe other widgets
class WatuPROWidgets {
	// actually calculates and displays the stats
	static function stats() {
		global $wpdb;
		
		// num quizzes taken today
		$num_today = $wpdb->get_var("SELECT COUNT(ID) FROM ".WATUPRO_TAKEN_EXAMS."
		 WHERE in_progress=0 AND date = '".date('Y-m-d', current_time('timestamp'))."'");
		 
		// num taken yesterday 
		$num_yesterday = $wpdb->get_var("SELECT COUNT(ID) FROM ".WATUPRO_TAKEN_EXAMS."
		 WHERE in_progress=0 AND date = '".date('Y-m-d', current_time('timestamp') - 24*3600)."'");
		 
		// percentage up or down	
		$today_arrow = 'green'; 	 
		if($num_today == 0) $percent_today = 0;
		else {
			if($num_yesterday == 0) $percent_today = '&infin;';
			else {
				if($num_today >= $num_yesterday) $percent_today = round(100 * $num_today / $num_yesterday) - 100;
				else {
					$percent_today = round(100 * $num_today / $num_yesterday);
					$today_arrow = 'red';
				}
			}
		}
		
		// num quizzes taken last 7 days
		$num_7_days = $wpdb->get_var("SELECT COUNT(ID) FROM ".WATUPRO_TAKEN_EXAMS."
		 WHERE in_progress=0 AND date >= '".date('Y-m-d', current_time('timestamp'))."' - INTERVAL 7 DAY");
		 
		// num taken previous 7 days
		$num_prev_7_days = $wpdb->get_var("SELECT COUNT(ID) FROM ".WATUPRO_TAKEN_EXAMS."
		 WHERE in_progress=0 AND date < '".date('Y-m-d', current_time('timestamp'))."' - INTERVAL 7 DAY
		 	AND date >= '".date('Y-m-d', current_time('timestamp'))."' - INTERVAL 14 DAY"); 
		 
		$sevenday_arrow = 'green'; 	
		if($num_7_days == 0) $percent_7_days = 0;
		else {
			if($num_prev_7_days == 0) $percent_7_days = '&infin;';
			else {
				if($num_7_days >= $num_prev_7_days) $percent_7_days = round(100 * $num_7_days / $num_prev_7_days) - 100;
				else {
					$percent_7_days = round(100 * $num_7_days / $num_prev_7_days);
					$sevenday_arrow = 'red';
				}
			}
		}
		
		// num quizzes taken this month
		$num_this_month = $wpdb->get_var("SELECT COUNT(ID) FROM ".WATUPRO_TAKEN_EXAMS."
		 WHERE in_progress=0 AND date >= '".date("Y-m", current_time('timestamp'))."-01' AND date <= '".date('Y-m-d', current_time('timestamp'))."'");
		 
		// last month, same dates 
		$current_month = intval(date('m', current_time('timestamp')));
		if($current_month == 1) { $last_month = 12; $last_year = date('Y', current_time('timestamp')) - 1; }
		else { $last_month = $current_month - 1; $last_year = date('Y', current_time('timestamp')); }
		
		$num_last_month = $wpdb->get_var("SELECT COUNT(ID) FROM ".WATUPRO_TAKEN_EXAMS."
		 WHERE in_progress=0 AND date >= '$last_year-$last_month-01' AND date <= '$last_year-$last_month-".date("d", current_time('timestamp'))."'");
		 
		$month_arrow = 'green'; 	
		if($num_this_month == 0) $percent_this_month = 0;
		else {
			if($num_last_month == 0) $percent_this_month = '&infin;';
			else {
				if($num_this_month >= $num_last_month) $percent_this_month = round(100 * $num_this_month / $num_last_month) - 100;
				else {
					$percent_this_month = round(100 * $num_this_month / $num_last_month);
					$month_arrow = 'red';
				}
			}
		} 
		
		// total published quizzes
		$uids = array();
		$posts = $wpdb->get_results("SELECT post_content FROM {$wpdb->posts} 
			WHERE post_content LIKE '%[watupro %]%' AND (post_status='publish' OR post_status='private')");
		
		$quizzes = $wpdb->get_results("SELECT ID, published_odd FROM ".WATUPRO_EXAMS);	
			
		// go through posts to extract unique quiz IDs
		foreach($posts as $post) {
			foreach($quizzes as $quiz) {
				if(stristr($post->post_content, '[watupro '.$quiz->ID.']')) {
					if(!in_array($quiz->ID, $uids)) $uids[] = $quiz->ID;
				}
			}
		}	
		
		// add any "oddly" published quizzes to the $udis array
		foreach($quizzes as $quiz) {
			if($quiz->published_odd and !in_array($quiz->ID, $uids)) $uids[] = $quiz->ID;
		}
		
		$total = count($uids);			
		
		// most popular quiz
		$most_popular = $wpdb->get_row("SELECT tE.name as name, tE.ID as ID, COUNT(tT.ID) as takings 
			FROM ".WATUPRO_EXAMS." tE LEFT JOIN ".WATUPRO_TAKEN_EXAMS." tT
			ON tT.exam_id = tE.ID GROUP BY tE.ID ORDER BY takings DESC LIMIT 1"); 
		
		// latest quiz
		$latest = $wpdb->get_row("SELECT ID, name FROM ".WATUPRO_EXAMS." ORDER BY ID DESC LIMIT 1");
		
		// latest quiz attempt / result
		$latest_attempt = $wpdb->get_row("SELECT tE.name as name, tE.ID as ID, tT.end_time as end_time, tT.ID as taking_id
			FROM ".WATUPRO_EXAMS." tE JOIN ".WATUPRO_TAKEN_EXAMS." tT ON tE.ID = tT.exam_id
			WHERE tT.in_progress=0 ORDER BY tT.ID DESC LIMIT 1");
			
		include(WATUPRO_PATH . "/views/dashboard-widget.html.php");	
	}	
	
	static function widget() {
		if(get_option('watupro_low_memory_mode') == 1) return true;
		wp_add_dashboard_widget('watupro_dashboard', __('Watu PRO Quizzes', 'watupro'),
   				array(__CLASS__, 'stats'));
	}
}