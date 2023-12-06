<?php
class WatuPROCertificate {
	// returns certificate link and inserts the certificate in user-certificates table
	static function assign($exam, $taking_id, $certificate_id, $user_id) {
		global $wpdb;		
		
		if(!empty($_POST['watupro_taker_email'])) $_POST['taker_email'] = $_POST['watupro_taker_email'];
		$email = @$_POST['taker_email'];
		
		// select certificate
		$cert = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".WATUPRO_CERTIFICATES." WHERE ID=%d", $certificate_id));
		if(empty($cert)) return "";
		
		if(!empty($cert->require_approval)) {
			$pending_approval = 1;
			$certificate_text = "";
			if($cert->require_approval_notify_admin) self :: pending_approval_notify($cert, $user_id, $exam, $taking_id);
		}
		else {
			$certificate_text = "<p>".__('You can now', 'watupro')." <a href='".site_url("?watupro_view_certificate=1&taking_id=$taking_id&id=".$certificate_id)."' target='_blank'>".__('print your certificate', 'watupro')."</a></p>";
			$pending_approval = 0;
		}
		
		// select quiz ID
		$quiz_id = $wpdb->get_var($wpdb->prepare("SELECT exam_id FROM ".WATUPRO_TAKEN_EXAMS." 
			WHERE ID=%d", $taking_id));
		
		// delete any previous records for this user
		if(!get_option('watupro_multiple_certificates')) {
			if(!empty($user_id)) {
				$wpdb->query($wpdb->prepare("DELETE FROM ".WATUPRO_USER_CERTIFICATES." 
				WHERE user_id=%d AND certificate_id = %d AND exam_id=%d", $user_id, $certificate_id, $quiz_id));
			}
			else {
				// delete certificates by email				
				$wpdb->query($wpdb->prepare("DELETE FROM ".WATUPRO_USER_CERTIFICATES." 
				WHERE email = %s AND certificate_id = %d AND exam_id=%d", $email, $certificate_id, $quiz_id));
			}
		}
	       
		// if(empty($user_id) and empty($email)) return ''; // either logged in user or taking email is required	       
	       
	   // store in user certificates
	   $sql = "INSERT INTO ".WATUPRO_USER_CERTIFICATES." (user_id, certificate_id, exam_id, taking_id, pending_approval, email) 
	    	VALUES (%d, %d, %d, %d, %d, %s) ";
	   $wpdb->query($wpdb->prepare($sql, $user_id, $certificate_id, $exam->ID, $taking_id, $pending_approval, $email));
	   $ucert_id = $wpdb->insert_id;
	   
	   if($cert->is_multi_quiz) {
	   	// update the record with the multi-quiz details
	   	$details = get_user_meta($user_id, 'watupro_multicertificate_details', true);
	   	$wpdb->query($wpdb->prepare("UPDATE ".WATUPRO_USER_CERTIFICATES." SET
	   		quiz_ids=%s, avg_points=%d, avg_percent=%d
	   		WHERE ID=%d", $details['quiz_ids'], $details['avg_points'], $details['avg_percent'],  $ucert_id));
		}
    
 	   return $certificate_text;
	}
	
	// send notification email to admin when someone earns a certificate that requires approval
	static function pending_approval_notify($cert, $user_id, $exam, $taking_id) {
		global $wpdb;
		if(empty($user_id)) {
			$user_nicename = empty($_POST['watupro_taker_name']) ? @$_POST['taker_name'] : $_POST['watupro_taker_name'];
		}
		else {
			$user = get_userdata($user_id);
			$user_nicename = $user->user_nicename;
		}
				
		$subject = __('A certificate is earned and is pending approval.', 'watupro');
		$message = __('The user "%%user-name%%" has earned the certificate "%%certificate-name%%".  
		To view users who are pending approvals on this certificate visit %%url%%', 'watupro');
		$message = str_replace('%%user-name%%', $user_nicename, $message);
		$message = str_replace('%%certificate-name%%', $cert->title, $message); 
		$message = str_replace('%%url%%', admin_url('admin.php?page=watupro_user_certificates&id='.$cert->ID), $message);
		
		// send email
		$admin_email = watupro_admin_email();
		$headers  = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=utf8' . "\r\n";
		$headers .= 'From: '. $admin_email . "\r\n";		
		// echo "$admin_email, $subject, $message<br><br>";
		wp_mail($admin_email, $subject, $message, $headers);
	}
	
	// sends approval notification to the user when their assigned certificate is approved
	static function approval_notify($certificate, $user_certificate_id) {
		global $wpdb; 
		
		// select user certificate along with taking date
		$user_certificate = $wpdb->get_row($wpdb->prepare("SELECT tUC.*, tT.date as date, tT.email as taking_email 
			FROM ".WATUPRO_USER_CERTIFICATES." tUC
			JOIN ".WATUPRO_TAKEN_EXAMS." tT ON tT.ID = tUC.taking_id 
			WHERE tUC.ID = %d AND tUC.certificate_id=%d", $user_certificate_id, $certificate->ID));
				
		$admin_email = watupro_admin_email();
		$user = get_userdata($user_certificate->user_id);
		$exam = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".WATUPRO_EXAMS." WHERE ID=%d", $user_certificate->exam_id));		
			
		// replace email subject and contents	
		$date = date( get_option('date_format'), strtotime($user_certificate->date));
		
		$subject = str_replace('{{quiz-name}}', stripslashes($exam->name), stripslashes($certificate->approval_email_subject));
		$subject = str_replace('{{certificate}}', stripslashes($certificate->title), $subject);
		$subject = str_replace('{{date}}', $date, $subject);
		
		$message = str_replace('{{quiz-name}}', stripslashes($exam->name), stripslashes($certificate->approval_email_message));
		$message = str_replace('{{certificate}}', stripslashes($certificate->title), $message);
		$message = str_replace('{{date}}', $date, $message);
		$message = str_replace('{{url}}', site_url("?watupro_view_certificate=1&taking_id=".$user_certificate->taking_id."&id=".$certificate->ID), $message);
		
		$message = apply_filters('watupro_content', $message);
		
		// send email
		$headers  = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=utf8' . "\r\n";
		$headers .= 'From: '. $admin_email . "\r\n";
		$user_email = empty($user_certificate->taking_email) ? $user->user_email :  $user_certificate->taking_email;
		// echo "$user_email, $subject, $message<br><br>";
		wp_mail($user_email, $subject, $message, $headers);
	}
	
	// find multi-quiz certificate
	static function multi_quiz($exam_id, $achieved, $percent) {
		global $wpdb, $user_ID;
		
		if(!is_user_logged_in()) return false; // such certificate can only work with logged in users
		
		// find any multi-quiz certificates that contain this quiz
		$certificates = $wpdb->get_results("SELECT * FROM " . WATUPRO_CERTIFICATES . 
			" WHERE is_multi_quiz=1 AND quiz_ids LIKE '%|".$exam_id."|%' ORDER BY avg_points DESC, avg_percent DESC, ID");	
			
		// all takings of this user
		$takings = $wpdb->get_results($wpdb->prepare("SELECT ID, exam_id, points, percent_correct 
			FROM ".WATUPRO_TAKEN_EXAMS." WHERE user_id=%d AND in_progress=0", $user_ID));	
		$taken_quiz_ids = array();
		foreach($takings as $taking) {
			if(!in_array($taking->exam_id, $taken_quiz_ids)) $taken_quiz_ids[] = $taking->exam_id;
		}	
		
		// when the first is found, return it
		foreach($certificates as $certificate) {
			// extract quiz IDs
			$quiz_ids = explode('|', $certificate->quiz_ids);
			$quiz_ids = array_filter($quiz_ids);
			if(empty($quiz_ids)) continue;
			
			// did the user take them all?
			foreach($quiz_ids as $quiz_id) {
				if(!in_array($quiz_id, $taken_quiz_ids)) continue 2; // even one non-taken quiz means this certificate won't be earned
			}
			
			// did the user collect the required averages?
			$total_points = $total_percent = 0;
			foreach($takings as $taking) {
				if(!in_array($taking->exam_id, $quiz_ids)) continue;
				$total_points += $taking->points;
				$total_percent += $taking->percent_correct;
			}
			
			$num_quizzes = count($takings);
			$avg_points = round($total_points / $num_quizzes);
			$avg_percent = round($total_percent / $num_quizzes);
			
			// if all is true, return the certificate ID
			if($avg_points >= $certificate->avg_points and $avg_percent >= $certificate->avg_percent) {
				// update user meta with the certificate criteria because we'll need this on assign()
				$details = array('quiz_ids'=> implode(',', $quiz_ids), 'avg_points'=>$avg_points, 'avg_percent'=>$avg_percent);
				update_user_meta($user_ID, 'watupro_multicertificate_details', $details);
				
				return  $certificate->ID;
			}
		} // end foreach certificate
	} // end multi_quiz
}