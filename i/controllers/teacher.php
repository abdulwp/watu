<?php
// handles manual actions that the teacher does
class WatuPROITeacherController {
	// edits the points in already taken exam
	static function edit_taking() {
		global $wpdb, $user_ID;
		$multiuser_access = 'all';
		if(watupro_intel()) $multiuser_access = WatuPROIMultiUser::check_access('exams_access');
		
		if($multiuser_access == 'view' or $multiuser_access == 'group_view') wp_die(__("You are not allowed to do this", 'watupro'));
	
		// select this taking
		$taking = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".WATUPRO_TAKEN_EXAMS." WHERE ID=%d", $_GET['id']));
		
		$exam = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".WATUPRO_EXAMS." WHERE id=%d", $taking->exam_id));
		
		if($multiuser_access == 'own' and $exam->editor_id != $user_ID) wp_die(__("You are not allowed to do this", 'watupro'));
		if($multiuser_access == 'group') {
			$cat_ids = WTPCategory::user_cats($user_ID);
			if(!in_array($exam->cat_id, $cat_ids)) wp_die(__("You are not allowed to do this", 'watupro'));
		}
		
		// select answers in details
		$answers=$wpdb->get_results($wpdb->prepare("SELECT tA.*, tQ.question as question, tQ.is_survey as is_survey,
		   tC.name as category, tQ.cat_id as cat_id
			FROM ".WATUPRO_STUDENT_ANSWERS." tA JOIN ".WATUPRO_QUESTIONS." tQ ON tQ.id=tA.question_id
			LEFT JOIN ".WATUPRO_QCATS." tC ON tC.ID = tQ.cat_id 
			WHERE taking_id=%d ORDER BY id", $taking->ID));
		
		if(!empty($_POST['ok'])) {
			require_once(WATUPRO_PATH."/i/models/teacher.php");
			WatuPROITeacher::edit_details($exam, $taking, $answers);
			
			// reselect taking and answers?
			watupro_redirect("admin.php?page=watupro_takings&exam_id=".$exam->ID."&msg=Details edited");
		}
		
		// if there is logged in user of this taking, select them
		if(!empty($taking->user_id)) {
			$student = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->users} WHERE ID=%d", $taking->user_id));
			$receiver_email = $student->user_email;
		}
		else $receiver_email = $taking->email;
		
		if(@file_exists(get_stylesheet_directory().'/watupro/i/teacher-edit-details.php')) require get_stylesheet_directory().'/watupro/i/teacher-edit-details.php';
		else require WATUPRO_PATH."/i/views/teacher-edit-details.php";
	}
}