<?php
class WatuPROIExam {
	// update extra fields when adding and saving
	static function extra_fields($exam_id, $vars) {
		 global $wpdb;
		 $wpdb->query($wpdb->prepare("UPDATE ".WATUPRO_EXAMS." 
		 	SET retake_after=%d, is_personality_quiz=%d WHERE ID=%d", 
		 	$vars['retake_after'], @$vars['is_personality_quiz'], $exam_id));
	}
	
	// check extra limitations for resubmitting the exam
	static function can_retake($exam) {		
		if($exam->retake_after == 0) return true;
				
		global $wpdb, $user_ID;
		
		// see if the latest attempt is "too recent"
		$recent_attempt = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".WATUPRO_TAKEN_EXAMS." 
			WHERE exam_id=%d AND user_id=%d AND end_time > '".current_time('mysql')."' - INTERVAL %d HOUR AND ignore_attempt=0", $exam->ID, $user_ID, $exam->retake_after));
					
		if(!empty($recent_attempt->ID)) {
			// if ratake_after is > 100, let's round to days
			$time = $exam->retake_after>100 ? __('days', 'watupro'):__('hours', 'watupro');
			$retake_after_time = $exam->retake_after>100 ? round($exam->retake_after / 24) : $exam->retake_after;			
			
			
			printf(__("You need to wait at least %d %s after your previous attempt on this test.", 'watupro'), $retake_after_time, $time);
			return false;
		}			
			
		return true;	
	}
	
	// alter price of an exam - for example because this user has taken it already but there is a setting that next takings
	// are cheaper or more expensive
	static function adjust_price(&$exam) {
		global $wpdb, $user_ID;
		
		if(!is_user_logged_in()) return false;
		
		// this is needed only for exams where users have to pay each time
		if(empty($exam->pay_always) or empty($exam->fee)) return false;
		
		$advanced_settings = unserialize(stripslashes($exam->advanced_settings));
		
		// should the price be changed on each attempt?
		if(empty($advanced_settings['attempts_price_change_amt'])) return false;
		
		// so, we have a change defined. Let's see how many paid attempts did this user make so far
		// so we can calculate the adjusted price
		$num_used_payments = $wpdb->get_var($wpdb->prepare("SELECT COUNT(ID) FROM ".WATUPRO_PAYMENTS."
			WHERE exam_id=%d AND user_id=%d AND status='used'", $exam->ID, $user_ID));
		if(!$num_used_payments) return false;
		
		$adjustment_amount = $num_used_payments * $advanced_settings['attempts_price_change_amt'];
		
		if($advanced_settings['attempts_price_change_action'] == 'reduce') {
			$new_fee = $exam->fee - $adjustment_amount;
			if($advanced_settings['attempts_price_change_limit'] and $new_fee < $advanced_settings['attempts_price_change_limit']) $new_fee = $advanced_settings['attempts_price_change_limit'];
			if($new_fee < 0) $new_fee = 0; 
		}
		else {
			// increase price
			$new_fee = $exam->fee + $adjustment_amount;
			if($advanced_settings['attempts_price_change_limit'] and $new_fee > $advanced_settings['attempts_price_change_limit']) $new_fee = $advanced_settings['attempts_price_change_limit'];
		}	
		
		// finally assign
		$exam->fee = $new_fee;
		
		return true;
	} // end adjust_price
	
	// if exam reuses only selected questions, return SQL to limit a query to them
	// @param $exam contains the exam object with the 3 necessary properties: reuse_questions_from, limit_reused_questions and reused_question_ids
	// @param $field - the DB field that will be used
	static function reused_questions_sql($exam, $field) {
		if(empty($exam->reuse_questions_from) or empty($exam->limit_reused_questions) or empty($exam->reused_question_ids)) return "";
		
		// ensure that we have the IDs in proper SQL format (they are supposed to be so in the DB but ensure)
		$exam->reused_question_ids = str_replace(' ', '', $exam->reused_question_ids);
		$q_ids = explode(",", $exam->reused_question_ids);
		$q_ids = watupro_int_array($q_ids);
		$q_ids = array_filter($q_ids);
		
		$sql = " AND $field IN (".implode(', ', $q_ids).") ";
		
		return $sql;		
	} // end reused_questions_sql
}