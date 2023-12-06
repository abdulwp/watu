<?php
// class to handle manual grading of exams
class WatuPROITeacher {
	 // saves the grading details
	 // probably send email to student with the results
	 static function edit_details($exam, $taking, $answers) {
	 		global $wpdb;
	 		
	 		// if exam calculates grades by % of points we have to select all questions from the $answers
	 		// to match their q_answers and calculate the max points
	 		// $max_points += WTPQuestion::max_points($ques);
	 		$advanced_settings = unserialize(stripslashes($exam->advanced_settings));
	 		$max_points = 0;
	 		$qids = array(0);
 			foreach($answers as $answer) $qids[] = $answer->question_id;
 			$questions = $wpdb->get_results("SELECT * FROM ".WATUPRO_QUESTIONS." WHERE ID IN (".implode(',', $qids).")");
 			$_watu = new WatuPRO();
 			$_watu->match_answers($questions, $exam);	 	
 			foreach($questions as $question) $max_points += WTPQuestion::max_points($question);			
	 		
	 		// update each answer
	 		$total_points = $total_answers = $total_question_answers = $correct_answers = $percent_correct = 0;
	 		foreach($answers as $answer) {
				 $wpdb->query($wpdb->prepare("UPDATE ".WATUPRO_STUDENT_ANSWERS." SET
				 	points=%s, is_correct=%d, teacher_comments = %s WHERE id=%d", 
				 		$_POST['points'.$answer->ID], @$_POST['is_correct'.$answer->ID], 
				 		$_POST['teacher_comments'.$answer->ID], $answer->ID));
				 	$total_points += $_POST['points'.$answer->ID];
				 	$total_answers++;
				 	if(!$answer->is_survey) $total_question_answers++;
				 	if(!empty($_POST['is_correct'.$answer->ID])) $correct_answers++;
	 		}
	 		
	 		// now recalculate percent correct
	 		if($total_question_answers==0) $percent_correct=0;
			else $percent_correct = number_format($correct_answers / $total_question_answers * 100, 2);
		
			if($max_points == 0) $pointspercent = 0;
			else $pointspercent = number_format($total_points / $max_points * 100, 2);

			list($grade, $certificate_id, $do_redirect, $grade_obj) 
				= WTPGrade::calculate($exam->ID, $total_points, $percent_correct, 0, null, $pointspercent);

			$grade_title = empty($grade_obj->gtitle) ? __('None', 'watupro') : $grade_obj->gtitle;			
				
			// update taking details	
			$_POST['teacher_comments']=''; // for now empty
			$wpdb->query($wpdb->prepare("UPDATE ".WATUPRO_TAKEN_EXAMS." SET
				points=%s, result=%s, grade_id=%d, percent_correct=%d, teacher_comments=%s, last_edited=%s, percent_points=%d
				WHERE id=%d",
				$total_points, $grade_title, @$grade_obj->ID, $percent_correct, 
				$_POST['teacher_comments'], date('Y-m-d', current_time('timestamp')), $pointspercent, $taking->ID));
				
			// add student certificate
			if($taking->user_id and $certificate_id) $certificate = WatuPROCertificate::assign($exam, $taking->ID, $certificate_id, $taking->user_id);
			
			do_action('watupro_completed_exam_edited', $taking->ID);
			
			// send email to the user
			if(!empty($_POST['send_email'])) {
				 $subject = stripslashes($_POST['subject']);
				 $message = wpautop(stripslashes($_POST['msg']));
				 
				 // replace vars
				 $subject = str_replace("%%QUIZ_NAME%%", $exam->name, $subject);
				 $message = str_replace("%%QUIZ_NAME%%", $exam->name, $message);
				 
				 // replace other vars from final screen
				 $message = str_replace("%%CORRECT%%", $correct_answers, $message);			 
				 $message = str_replace("%%TOTAL%%", $total_answers, $message);
				 $message = str_replace("%%POINTS%%", $total_points, $message);
				 $message = str_replace("%%POINTS-ROUNDED%%", round($total_points), $message);
				 $message = str_replace("%%PERCENTAGE%%", $percent_correct, $message);
				 $message = str_replace("%%GRADE%%", $grade, $message);
				 $message = str_replace("%%GTITLE%%", @$grade_obj->gtitle, $message);
				 $message = str_replace("%%GDESC%%", @$grade_obj->gdescription, $message);
				 $message = str_replace("%%DATE%%", date(get_option('date_format'), strtotime($taking->date)), $message);
				 $message = str_replace("%%EMAIL%%", $_POST['email'], $message);
				 $message = str_replace("%%CERTIFICATE%%", @$certificate, $message);
				 
				 // user info shortcodes?
				 $message = str_replace('user_id="quiz-taker"', 'user_id='.$taking->user_id, $message);
				 
				 if(strstr($message, "%%ANSWERS%%")) {
				 		// prepare answers table
				 		$answers_table = "<table border='1' cellpadding='4'><tr><th>".__('Question', 'watupro')."</th><th>".
				 			__('Answer(s) given', 'watupro')."</th><th>".__('Points', 'watupro').
				 			"</th><th>".__('Is Correct?', 'watupro')."</th><th>".__('Comments', 'watupro')."</th></tr>";
				 			
						foreach($answers as $answer) {
							 $answers_table.= "<tr><td>".wpautop(stripslashes($answer->question))."</td><td>".
							 	wpautop(stripslashes($answer->answer))."</td><td>".$_POST['points'.$answer->ID].
							 	"</td><td>".(@$_POST['is_correct'.$answer->ID]?__('yes', 'watupro'):__('no','watupro'))."</td><td>".
							 	wpautop(stripslashes($_POST['teacher_comments'.$answer->ID]))."</td></tr>";
						}				 			
				 			
				 		$answers_table.="</table>";	
				 		
				 		$message = str_replace("%%ANSWERS%%", $answers_table, $message);
				 }
				 
				 // now do send
				 $headers  = 'MIME-Version: 1.0' . "\r\n";
				 $headers .= 'Content-type: text/html; charset=utf8' . "\r\n";
				 $headers .= 'From: '. watupro_admin_email() . "\r\n";
				 $message = apply_filters('watupro_content', stripslashes($message));
				 // echo $message;		
				 $output='<html><head><title>'.$subject.'</title>
				 </head>	<html><body>'.$message.'</body></html>';		
				 
				 wp_mail($_POST['email'], $subject, $output, $headers);
				 
				 // update options to reuse subject & message next time
				 update_option('watupro_manual_grade_subject', $_POST['subject']);
				 update_option('watupro_manual_grade_message', $_POST['msg']);
				 
			} // end sending mail
	 }
}