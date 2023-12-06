<?php
// shows and stores the live result when taking a test
function watupro_liveresult() {
	global $wpdb, $user_ID;
	$_watu = new WatuPRO();
	$_question = new WTPQuestion();
	
	// select exam
	$exam = $wpdb -> get_row($wpdb->prepare("SELECT * FROM ".WATUPRO_EXAMS." WHERE ID=%d", $_POST['quiz_id']));
	$_watu->this_quiz = $exam;
	$advanced_settings = unserialize( stripslashes($exam->advanced_settings));
	if(watupro_intel()) {
		WatuPROIQuestion :: $advanced_settings = $advanced_settings;
		WTPQuestion :: $advanced_settings = $advanced_settings;
	}
	
	$questions = watupro_unserialize_questions($_POST['watupro_questions']);		
	
	// find current question
	$ques = null;
	foreach($questions as $question) {
		if($question->ID == $_POST['question_id']) $ques = $question;
	}
	
	if(!is_object($ques)) {
		die(__("Sorry, we couldn't retrieve the answer", 'watupro'));
	}
	
	$ansArr = is_array( $_POST["answer-" . $ques->ID] )? $_POST["answer-" . $ques->ID] : array(); 
	
	list($points, $correct) = WTPQuestion::calc_answer($ques, $ansArr, $ques->q_answers);
	list($answer_text, $current_text, $unresolved_text) = $_question->process($_watu, $_POST['question_num'], $ques->question, $ques, $ansArr, $correct, $points);
	
	$current_text = apply_filters('watupro_content', $current_text);
	echo $current_text;
	
	// now save it in the user answers details if user is logged in
	if(is_user_logged_in()) {
		$taking_id = $_watu->add_taking($exam->ID, 1);
		$answer=serialize($_POST['answer-'.$_POST['question_id']]); // we need to store the serialized answer here
		
		$_watu->store_details($exam->ID, $taking_id, $ques->ID, $answer, $points, $ques->question, $correct, $current_text);
	}
	
	exit;
}