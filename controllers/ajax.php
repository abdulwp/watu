<?php
function watupro_ajax() {
	global $wpdb;
	
	switch($_POST['do']) {
		case 'mark_review':
			// mark question for review
			WatuPROQuestions :: mark_review();
		break;
		
		case 'select_grades':
			// select grades for a given quiz, return drop-down HTML
			if(!empty($_POST['exam_id'])) {
				$exam = $wpdb->get_row($wpdb->prepare("SELECT ID, reuse_default_grades, grades_by_percent FROM ".WATUPRO_EXAMS." WHERE ID=%d", intval(@$_POST['exam_id'])));
			}
			
			$html = '<option value="">------</option>';
			if(empty($_POST['exam_id'])) die($html); // when no exam, return only the main option
			
			print_r($exam);
			$grades = WTPGrade :: get_grades($exam);
			
			foreach($grades as $grade) {
				$html .= '<option value="'.$grade->ID.'">'.stripslashes($grade->gtitle).'</option>'."\n";
			}
			
			echo $html;
		break;
	}
	exit;
}