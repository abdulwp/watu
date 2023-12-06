<?php
/**
 * This will scan all the content pages that wordpress outputs for our special code. If the code is found, it will replace the requested quiz.
 */
function watupro_shortcode( $attr ) {
	global $wpdb, $post;
	$exam_id = $attr[0];

	$contents = '';
	if(!is_numeric($exam_id)) return $contents;
	
	watupro_vc_scripts();
	ob_start();
		
	// select exam
	$exam = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".WATUPRO_EXAMS." WHERE id=%d", $exam_id));		
	if(watupro_intel()) WatuPROIntelligence :: conditional_scripts($exam_id);
	watupro_conditional_scripts($exam);	
	
	// passed question ids?	
	if(!empty($attr['question_ids'])) $passed_question_ids = $attr['question_ids'];
	
	// submitting without ajax?	
	if(!empty($_POST['no_ajax']) and !empty($exam->no_ajax)) {		
		require(WATUPRO_PATH."/show_exam.php");
		$contents = ob_get_clean();
		$contents = apply_filters('watupro_content', $contents);
		return $contents;
	}
	
	// other cases, show here
	if(empty($_GET['waturl']) or !$exam->shareable_final_screen) {
		// showing the exam
		if(@$exam->mode=='practice' and watupro_intel()) WatuPracticeController::show($exam);
		else include(WATUPRO_PATH . '/show_exam.php');
		$contents = ob_get_contents();
	}
	else {
		// showing taking results
		$url = @base64_decode($_GET['waturl']); 
		
		list($exam_id, $tid) = explode("|", $url); 
		if(!is_numeric($exam_id) or !is_numeric($tid)) return $contents;
		
		// must check if public URL is allowed 
		$taking = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".WATUPRO_TAKEN_EXAMS." WHERE ID=%d", $tid));
		$contents = WatuPRO::cleanup($taking->details, 'web');
		
		$post->ID = 0;
		$post->comment_status = 'closed';
	}
	
	ob_end_clean();			
	$contents = apply_filters('watupro_content', $contents);
	
	return $contents;
}

// shortcodes to list exams 
function watupro_listcode($attr) {
	$cat_id = @$attr[0];
	if(empty($cat_id)) $cat_id = @$attr['cat_id'];
	
	// define orderby
	$ob = @$attr['orderby'];
	if(empty($ob)) $ob = @$attr[1];
		
	switch($ob) {		
		case 'title': $orderby = "tE.name"; break;
		case 'latest': $orderby = "tE.ID DESC"; break;
		case 'created': default: $orderby = "tE.ID"; break;
	}
	
	watupro_vc_scripts();
	
	$show_status = empty($attr['show_status']) ? false : true;
	$content = WTPExam::show_list($cat_id, $orderby, $show_status);
	
	return $content;	
}

// outputs my exams page in any post or page
function watupro_myexams_code($attr) {
	global $post;
	$cat_id = @$attr[0];
	$status = @$attr['status'];
	
	if(!empty($_GET['view_details'])) {
			watupro_taking_details(true);
			return false;
	}
	
	$content = '';
	if(!is_user_logged_in()) return __('This content is only for logged in users', 'watupro');
	watupro_vc_scripts();
	
	// define orderby
	$ob = @$attr[1];	
	switch($ob) {		
		case 'title': $orderby = "tE.name"; break;
		case 'latest': $orderby = "tE.ID DESC"; break;
		case 'created': default: $orderby = "tE.ID"; break;
	}
	
	ob_start();
	$reorder_by_latest_taking = empty($attr['reorder_by_latest_taking']) ? false : true;
	watupro_my_exams($cat_id, $orderby, $status, true, $reorder_by_latest_taking);
	$content = ob_get_contents();
	ob_end_clean();
	return $content;
}

// outputs my certificates in any post or page
function watupro_mycertificates_code($attr) {
	$content = '';
	if(!is_user_logged_in()) return __('This content is only for logged in users', 'watupro');
	watupro_vc_scripts();
	
	ob_start();	
	watupro_my_certificates(true);
	$content = ob_get_contents();
	ob_end_clean();
	return $content;
}

// outputs generic leaderboard from all tests
function watupro_leaderboard($attr) {
	global $wpdb;
	watupro_vc_scripts();
	
	$num = $attr[0]; // number of users to show
	if(empty($num) or !is_numeric($num)) $num = 10;
	
	// now select them ordered by total points
	$users = $wpdb -> get_results("SELECT SUM(tT.points) as points, tU.user_login as user_login 
		FROM {$wpdb->users} tU JOIN ".WATUPRO_TAKEN_EXAMS." tT ON tT.user_id = tU.ID
		WHERE tT.in_progress = 0 GROUP BY tU.ID ORDER BY points DESC LIMIT $num");
	
	$table = "<table class='watupro-leaderboard'><tr><th>".__('User', 'watupro')."</th><th>".__("Points", 'watupro')."</th></tr>";
	
	foreach($users as $user) {
		$table .= "<tr><td>".$user->user_login."</td><td>".$user->points."</td></tr>";
	}
	
	$table .= "</table>";
	
	return $table;
}

// displays data from user profile of the currently logged user
function watupro_userinfo($atts) {
	global $user_ID;
	
	// let's allow user ID to be passed or taken from certificate
	if(!empty($atts['user_id'])) {
		if($atts['user_id'] == 'certificate') {
			$user_id = $_POST['watupro_certificate_user_id'];
		}
		if(is_numeric($atts['user_id'])) $user_id = $atts['user_id'];
	}		
	
	if(empty($user_id) and !is_user_logged_in()) return @$atts[1];
	if(empty($user_id)) $user_id = $user_ID;	
		
	$field = $atts[0];
		
	$user = get_userdata($user_id);
	
	if(isset($user->data->$field) and !empty($user->data->$field)) return $user->data->$field;
	if(isset($user->data->$field) and empty($user->data->$field)) return @$atts[1];
	
	// not set? must be in meta then
	$metas = get_user_meta($user_id);
	if(count($metas) and is_array($metas)) {
		foreach($metas as $key => $meta) {
			if($key == $field and !empty($meta[0])) return $meta[0];
			if($key == $field and empty($meta[0])) return @$atts[1];
		}
	}
	
	// nothing found, return the default if any
	return @$atts[1];
}

// quiz info showing the points, percent or grade on a given quiz and user
function watupro_result($atts) {
	global $wpdb, $user_ID;
	$quiz_id = intval($atts['quiz_id']);
	$user_id = empty($atts['user_id']) ? $user_ID : intval($atts['user_id']);
	if(empty($user_id) or empty($quiz_id)) return __('N/a', 'watupro');
	
	$result = $wpdb->get_row($wpdb->prepare("SELECT tT.points as points, tT.percent_correct as percent_correct, 
		tG.gtitle as grade_title FROM ".WATUPRO_TAKEN_EXAMS." tT LEFT JOIN ".WATUPRO_GRADES." tG ON tT.grade_id = tG.ID
		WHERE tT.exam_id=%d AND tT.user_id=%d AND tT.in_progress=0 ORDER BY tT.ID DESC LIMIT 1", $quiz_id, $user_id));	
		
	$what = empty($atts['what']) ? 'points' : $atts['what'];
	
	switch($what) {
		case 'grade': return stripslashes($result->grade_title); break;
		case 'percent': return $result->percent_correct; break;
		case 'points':
		default:
			return $result->points;
		break;
	}		 
} // end watupro_quizinfo

// shortcode for showing the basic barchart included in the core WatuPRO
// call this ONLY in the Final Screen of the quiz
function watupro_basic_chart($atts) {
	$taking_id = $GLOBALS['watupro_taking_id'];
	$content = WatuPROTaking :: barchart($taking_id, $atts);
	return $content;
}

// num allowed quiz attempts total and num left for current user
function watupro_quiz_attempts($atts) {
	global $wpdb, $user_ID;
	$quiz_id = intval($atts['quiz_id']);
	if(empty($quiz_id)) return '';
	
	$show = ($atts['show'] == 'total') ? 'total' : 'left';
	
	// select quiz ID and num attempts allowed
	$quiz = $wpdb->get_row($wpdb->prepare("SELECT require_login, take_again, times_to_take, takings_by_ip 
		FROM "  . WATUPRO_EXAMS. " WHERE ID=%d", $quiz_id));
		
	// no takings by IP and (no login required OR login required but take_again and no times_to_take limit)
	if(!$quiz->takings_by_ip and (!$quiz->require_login or ($quiz->take_again and !$quiz->times_to_take))) return __('Unlimited', 'watupro');	
	
	// takings by IP is checked first in can_retake, so we'll use it here
	if($quiz->takings_by_ip) {
		if($show == 'total') return $quiz->takings_by_ip;
		
		// else see how many this user has left
		$num_attempts = $wpdb->get_var($wpdb->prepare("SELECT COUNT(ID) FROM ".WATUPRO_TAKEN_EXAMS." 
			WHERE exam_id=%d AND ip=%s AND in_progress=0", $quiz_id, $_SERVER['REMOTE_ADDR']));
		
		$num_left = $quiz->takings_by_ip - $num_attempts;
		if($num_left < 0) $num_left = 0;		
		return $num_left;	
	}
	
	// when quiz requires login:
	if($quiz->require_login) {
		$total = $quiz->take_again ? $quiz->times_to_take : 1;
		
		if($show == 'total') return $total;
		
		// else see how many this user has left
		$num_attempts = $wpdb->get_var($wpdb->prepare("SELECT COUNT(ID) FROM ".WATUPRO_TAKEN_EXAMS." 
			WHERE exam_id=%d AND user_id=%d AND in_progress=0", $quiz_id, $user_ID));
			
		$num_left = $total - $num_attempts;	
		if($num_left < 0) $num_left = 0;		
		return $num_left;
	}
}

function watupro_shortcode_takings($atts) {
	ob_start();
	watupro_takings(true, $atts);
	$content = ob_get_clean();
	return $content;
}