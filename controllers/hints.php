<?php
function watupro_get_hints() {
	global $wpdb;

	// select the question
	$question = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".WATUPRO_QUESTIONS." WHERE ID=%d", $_POST['qid']));
	
	// select the quiz
	$quiz = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".WATUPRO_EXAMS." WHERE ID=%d", $question->exam_id));
	if(empty($quiz->question_hints)) watupro_hint_error(__('This quiz does not allow hints.', 'watupro'));
	list($hints_per_quiz, $hints_per_question) = explode("|", $quiz->question_hints);
	
	// see if quiz allows questions at all, if not - error
	if(empty($quiz->question_hints)) watupro_hint_error(__('No hints in this quiz!', 'watupro'));
	
	// see if this question has more hints
	$hints = explode("{{{split}}}", $question->hints);
	$no_hints = sizeof($hints);
	
	// if not, display error
	if($no_hints <= $_POST['num_hints']) watupro_hint_error(__('There are no more hints on this question.', 'watupro'));
	
	// if hints are available, check if the user has right to see 1 more
	if($hints_per_quiz and $_POST['num_hints_total'] >= $hints_per_quiz) {
		watupro_hint_error(__('You have used all your hints for this quiz.', 'watupro'));
	}
	// limit per question exceeded?
	if($hints_per_question and $_POST['num_hints'] >= $hints_per_question) {
		watupro_hint_error(__('You have used all your hints for this question.', 'watupro'));
	}
	
	// if yes, display it
   $hint = @$hints[$_POST['num_hints']];
   echo "SUCCESS|WATUPRO|";
   echo "<div class='watupro-hint'>".wpautop(stripslashes($hint))."</div>";
	
	// if num_hints + 1 = sizeof(hints) output third part "nomorehints"
	if($_POST['num_hints']+1 >= sizeof($hints) or ($hints_per_question and $_POST['num_hints']+1 >= $hints_per_question) 
		or ($hints_per_quiz and $_POST['num_hints_total']+1 >= $hints_per_quiz)) echo "|WATUPRO|nomorehints"; 		
	
	exit;
}

// small helper, outputs an error
function watupro_hint_error($error) {
	die("ERROR|WATUPRO|".$error."|WATUPRO|nomorehints");
}