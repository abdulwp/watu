<?php
// called when exam is submitted
$_question = new WTPQuestion();
global $user_email, $user_identity, $question_catids, $post, $do_redirect, $achieved, $percent;	
if(!is_user_logged_in()) $user_email = @$_POST['taker_email'];

if(watupro_intel()) require_once(WATUPRO_PATH."/i/models/question.php");

$taking_id = $_watu->add_taking($exam->ID);

$_POST['watupro_current_taking_id'] = $GLOBALS['watupro_taking_id'] = $taking_id;  // needed in personality quizzes and shortcodes
if(empty($_POST['post_id']) and is_object($post)) $_POST['post_id'] = $post->ID;
$_watu->this_quiz = $exam;

$total = $score = $achieved = $max_points = $paginated_cnt = 0; 
$result = $unresolved_questions = $current_text = $paginated_result = '';
$user_grade_ids = array(); // used in personality quizzes (Intelligence module)
$cats_maxpoints = array();

$question_catids = array(); // used for category based pagination
foreach ($all_question as $qct=>$ques) {	
		if(empty($ques->is_survey)) $total ++;
		// the two rows below are about the category headers
		if(!$ques->exclude_on_final_screen) {
			$paginated_cnt++;
			$paginated_hidden = ($paginated_cnt == 1) ? '' : 'watupro-paginated-hidden';
			if($paginated_cnt == 1) {
				$paginated_result .= "<p id='watuproPaginatedAnswersStart'>&nbsp;</p>";
			}
			$paginated_result .= "<div class='watupro-paginated-answer $paginated_hidden' id='watuPROPaginatedAnswer-".$paginated_cnt."'>";
			$cat_header = watupro_cat_header($exam, $qct, $ques, 'submit');
			$result .= $cat_header;
			$paginated_result .= $cat_header;
			
			if(!in_array($ques->cat_id, $question_catids)) $question_catids[] = $ques->cat_id;
		}
		
		// in case timeout exceeded and there is missing in progress answer for this question, we'll unset the answer
		if(!empty($timeout_submit)) {
			$inprogress_answer = $wpdb->get_var($wpdb->prepare("SELECT answer FROM " . WATUPRO_STUDENT_ANSWERS . "
				WHERE taking_id=%d AND question_id=%d", $in_progress->ID, $ques->ID));
			if(empty($inprogress_answer)	or strlen($inprogress_answer) < 3) $_POST["answer-" . $ques->ID] = null;
		}
		
      $qct++;
      $question_content = $ques->question;
      // fill the gaps need to replace gaps
      if($ques->answer_type=='gaps') $question_content = preg_replace("/{{{([^}}}])*}}}/", "_____", $question_content);

		$ansArr = is_array( @$_POST["answer-" . $ques->ID] )? $_POST["answer-" . $ques->ID] : array();      
				
		// points and correct calculation
		list($points, $correct) = WTPQuestion::calc_answer($ques, $ansArr, $ques->q_answers, $user_grade_ids);
		$ques_max_points = WTPQuestion::max_points($ques);		
		$max_points += $ques_max_points;
		if($ques->cat_id) {
			if(!isset($cats_maxppoints[$ques->cat_id])) $cats_maxppoints[$ques->cat_id]['max_points']=0;
			$cats_maxppoints[$ques->cat_id]['max_points'] += $ques_max_points;
		}
		
		// handle sorting personalities
		if(!empty($exam->is_personality_quiz) and $ques->answer_type == 'sort' and watupro_intel()) {
			WatuPROIQuestion :: sort_question_personality($ques, $ansArr, $user_grade_ids);
		}
		
		// discard points?
		if($points > 0 and !$correct and $ques->reward_only_correct) $points = 0; 
		if($points and !$correct and $ques->discard_even_negative) $points = 0; 
						  			
  		list($answer_text, $current_text, $unresolved_text) = $_question->process($_watu, $qct, $question_content, $ques, $ansArr, $correct, $points);
  		$unresolved_questions .= str_replace('[[watupro-resolvedclass]]', '', $unresolved_text);
  		
  		// replace the resolved class
  		if($correct) $current_text = str_replace('[[watupro-resolvedclass]]','watupro-resolved',$current_text);
  		else $current_text = str_replace('[[watupro-resolvedclass]]','watupro-unresolved',$current_text);
  		
  		if(empty($ques->exclude_on_final_screen)) {
  			$result .= $current_text;
  			$paginated_result .= $current_text . "</div>";
  		}		 
  		
  		// insert taking data
  		$_watu->store_details($exam->ID, $taking_id, $ques->ID, $answer_text, $points, $ques->question, $correct, $current_text);
        
      if($correct) $score++;  
      $achieved += $points;   
}

// uploaded files?
if($exam->no_ajax) $result = WatuPROFileHandler :: final_screen($result, $taking_id);

$paginated_result .= WTPExam :: answers_paginator($paginated_cnt);
    
// calculate percentage
if($total==0) $percent=0;
else $percent = number_format($score / $total * 100, 2);
$percent = round($percent);

// percentage of max points
if($achieved <= 0 or $max_points <= 0) $pointspercent = 0;
else $pointspercent = number_format($achieved / $max_points * 100, 2);

// generic rating
$rating=$_watu->calculate_rating($total, $score, $percent);
	
// assign grade
list($grade, $certificate_id, $do_redirect, $grade_obj) = WTPGrade::calculate($exam_id, $achieved, $percent, 0, $user_grade_ids, $pointspercent);

// assign certificate if any
$certificate="";
if(!empty($certificate_id)) {	
	$certificate = WatuPROCertificate::assign($exam, $taking_id, $certificate_id, $user_ID);	
}

// this is important for qTranslate-X integration. Should be done before replacing any variables
$exam->final_screen = apply_filters('watupro_qtranslate', $exam->final_screen);
$exam->email_output = apply_filters('watupro_qtranslate', $exam->email_output);

/***Get custom part 2 score by creating custom function ***/
list($part2grades) = WTPIGrade::custom_part2_scores($taking_id);
$getPart2GradesFinalGradeCountArray = array();
if(!empty($part2grades) and is_array($part2grades)) 
{
	foreach($part2grades as $part2grade) 
	{
		$getPart2GradesFinalGradeCountArray[] = $part2grade['part2FinalGradeCount'];
	}		
}
 
// category grades if any
list($catgrades, $catgrades_array) = WTPGrade::replace_category_grades($exam->final_screen, $taking_id, $exam->ID);
// replace category grades
$getCatGradeFinalGradeCountArray = array();
if(!empty($catgrades_array) and is_array($catgrades_array)) {
	foreach($catgrades_array as $catgrade) {	
		// category percentageofmax
		$percent_of_max = empty($cats_maxppoints[$catgrade['cat_id']]['max_points']) ? 0 : round(100 * $catgrade['points'] / $cats_maxppoints[$catgrade['cat_id']]['max_points']);
		if($catgrade['points'] <= 0) $percent_of_max = 0;			
	
		$exam->final_screen =  str_replace('%%CATEGORY-NAME-'.$catgrade['cat_id'].'%%', $catgrade['name'], $exam->final_screen);
		$exam->final_screen =  str_replace('%%CATEGORY-DESCRIPTION-'.$catgrade['cat_id'].'%%', $catgrade['description'], $exam->final_screen);
		$exam->final_screen =  str_replace('%%CATEGORY-CORRECT-'.$catgrade['cat_id'].'%%', $catgrade['correct'], $exam->final_screen);
		$exam->final_screen =  str_replace('%%CATEGORY-TOTAL-'.$catgrade['cat_id'].'%%', $catgrade['total'], $exam->final_screen);
		$exam->final_screen =  str_replace('%%CATEGORY-POINTS-'.$catgrade['cat_id'].'%%', $catgrade['points'], $exam->final_screen);
		$exam->final_screen =  str_replace('%%CATEGORY-PERCENTAGE-'.$catgrade['cat_id'].'%%', $catgrade['percent'], $exam->final_screen);
		$exam->final_screen =  str_replace('%%CATEGORY-GTITLE-'.$catgrade['cat_id'].'%%', $catgrade['gtitle'], $exam->final_screen);
		$exam->final_screen =  str_replace('%%CATEGORY-GDESCRIPTION-'.$catgrade['cat_id'].'%%', $catgrade['gdescription'], $exam->final_screen);	
		$exam->final_screen =  str_replace('%%CATEGORY-PERCENTAGEOFMAX-'.$catgrade['cat_id'].'%%', $percent_of_max, $exam->final_screen);
		
		// same for email_output
		$exam->email_output =  str_replace('%%CATEGORY-NAME-'.$catgrade['cat_id'].'%%', $catgrade['name'], $exam->email_output);
		$exam->email_output =  str_replace('%%CATEGORY-DESCRIPTION-'.$catgrade['cat_id'].'%%', $catgrade['description'], $exam->email_output);
		$exam->email_output =  str_replace('%%CATEGORY-CORRECT-'.$catgrade['cat_id'].'%%', $catgrade['correct'], $exam->email_output);
		$exam->email_output =  str_replace('%%CATEGORY-TOTAL-'.$catgrade['cat_id'].'%%', $catgrade['total'], $exam->email_output);
		$exam->email_output =  str_replace('%%CATEGORY-POINTS-'.$catgrade['cat_id'].'%%', $catgrade['points'], $exam->email_output);
		$exam->email_output =  str_replace('%%CATEGORY-PERCENTAGE-'.$catgrade['cat_id'].'%%', $catgrade['percent'], $exam->email_output);
		$exam->email_output =  str_replace('%%CATEGORY-GTITLE-'.$catgrade['cat_id'].'%%', $catgrade['gtitle'], $exam->email_output);
		$exam->email_output =  str_replace('%%CATEGORY-GDESCRIPTION-'.$catgrade['cat_id'].'%%', $catgrade['gdescription'], $exam->email_output);	
		$exam->email_output =  str_replace('%%CATEGORY-PERCENTAGEOFMAX-'.$catgrade['cat_id'].'%%', $percent_of_max, $exam->email_output);
		
		$getCatGradeFinalGradeCountArray[] = $catgrade['finalGradeCount'];
	}
}

// replace some old confusingly named vars
$exam->final_screen = str_replace("%%SCORE%%", "%%CORRECT%%", $exam->final_screen);

// url to share the final screen and maybe redirect to it?
$post_url = empty($post) ? get_permalink($_POST['post_id']) : get_permalink($post->ID);
$post_url .= strstr($post_url, "?") ? "&" : "?";  
$share_url = $post_url."waturl=".base64_encode($exam->ID."|".$taking_id);
if(!empty($exam->shareable_final_screen) and !empty($exam->redirect_final_screen)) $do_redirect = $share_url;

$taking = $wpdb->get_row($wpdb->prepare("SELECT start_time, end_time FROM ".WATUPRO_TAKEN_EXAMS." WHERE ID=%d", $taking_id));

// time spent on this quiz
$time_spent = '';
if(strstr($exam->final_screen, '%%TIME-SPENT%%') or strstr($exam->email_output, '%%TIME-SPENT%%')) {
	$taking->end_time =	current_time('mysql');
	$time_spent = WTPRecord :: time_spent_human( WTPRecord :: time_spent($taking));
}

####################### VARIOUS AVERAGE CALCULATIONS (think about placing them in function / method #######################
// calculate averages
$avg_points = $avg_percent = '';
if(strstr($exam->final_screen, '%%AVG-POINTS%%') or strstr($exam->email_output, '%%AVG-POINTS%%')) $avg_points = WatuPROTaking :: avg_points($taking_id, $exam->ID);
if(strstr($exam->final_screen, '%%AVG-PERCENT%%') or strstr($exam->email_output, '%%AVG-PERCENT%%')) $avg_percent = WatuPROTaking :: avg_percent($taking_id, $exam->ID); 

// better than what %?
$better_than = '';
if(strstr($exam->final_screen, '%%BETTER-THAN%%')) {
	// select total completed quizzes
	$total_takings = $wpdb->get_var($wpdb->prepare("SELECT COUNT(ID) FROM ".WATUPRO_TAKEN_EXAMS."
		WHERE exam_id=%d AND in_progress=0", $exam->ID));	
	
	if($exam->grades_by_percent) {
		$num_lower = $wpdb->get_var($wpdb->prepare("SELECT COUNT(ID) FROM ".WATUPRO_TAKEN_EXAMS."
			WHERE exam_id=%d AND in_progress=0 AND percent_correct < %d", $exam->ID, $percent));	
	}
	else {
		$num_lower = $wpdb->get_var($wpdb->prepare("SELECT COUNT(ID) FROM ".WATUPRO_TAKEN_EXAMS."
			WHERE exam_id=%d AND in_progress=0 AND points < %f", $exam->ID, $achieved));
	}
	
	$better_than = $total_takings ? round($num_lower * 100 / $total_takings) : 0;
}
####################### END VARIOUS AVERAGE CALCULATIONS #######################

// replace grade and gdesc first so any variables used in them can be replaced after that
// $exam->final_screen = wpautop($exam->final_screen);
$exam->final_screen = str_replace(array('%%GRADE%%', '%%GDESC%%'), array(wpautop($grade, false), wpautop(stripslashes(@$grade_obj->gdescription), false)), $exam->final_screen);
$exam->email_output = str_replace(array('%%GRADE%%', '%%GDESC%%'), array(wpautop($grade, false), wpautop(stripslashes(@$grade_obj->gdescription), false)), $exam->email_output);

// prepare contact data about the user
$_POST['taker_name'] = empty($_POST['taker_name']) ? @$_POST['watupro_taker_name'] : $_POST['taker_name']; // when coming from non-ajax quiz
$user_name = empty($_POST['taker_name']) ? $user_identity : $_POST['taker_name'];
if(empty($user_name)) $user_name = __('Guest', 'watupro');
$_POST['taker_email'] = empty($_POST['taker_email']) ? @$_POST['watupro_taker_email'] : $_POST['taker_email']; // when coming from non-ajax quiz
$user_email = empty($_POST['taker_email']) ? $user_email : $_POST['taker_email'];
$_POST['taker_phone'] = empty($_POST['taker_phone']) ? @$_POST['watupro_taker_phone'] : $_POST['taker_phone']; // when coming from non-ajax quiz
$_POST['taker_company'] = empty($_POST['taker_company']) ? @$_POST['watupro_taker_company'] : $_POST['taker_company']; // when coming from non-ajax quiz
// user data from contact fields
$contact_data = '';
   
if(!empty($_POST['taker_phone']) or !empty($_POST['taker_company'])) {
		$contact_datas = array();
		if(!empty($_POST['taker_phone'])) $contact_datas[] = sprintf(__('Phone: %s', 'watupro'), $_POST['taker_phone']);
		if(!empty($_POST['taker_company'])) $contact_datas[] = sprintf(__('Company: %s', 'watupro'), $_POST['taker_company']);
		$contact_data = implode(', ', $contact_datas);

		$exam->final_screen = WTPExam :: replace_contact_fields($exam, 
			array('company'=>@$_POST['taker_company'], 'phone' => @$_POST['taker_phone']), $exam->final_screen);
		$exam->email_output = WTPExam :: replace_contact_fields($exam, 
			array('company'=>@$_POST['taker_company'], 'phone' => @$_POST['taker_phone']), $exam->email_output);	
}

// Categories Wise Data
global $wpdb;
global $wpdb;
$getTsFinalPoints = $getTs1FinalPoints = $getTs2FinalPoints = $getTiFinalPoints = $getTi1FinalPoints =$getTi2FinalPoints =$getSrFinalPoints = $getSr1FinalPoints = $getSr2FinalPoints = $getPfFinalPoint = $getPf1FinalPoints = $getPf2FinalPoints = 0;
$getCatIDTs1 = 3;
$getCatIDTs2 = 7;
$getCatIDTi1 = 4;
$getCatIDTi2 = 8;
$getCatIDSr1 = 5;
$getCatIDSr2 = 9;
$getCatIDPf1 = 6;
$getCatIDPf2 = 10;
$tablePrefix = $wpdb->prefix;
$getExamID = $exam->ID;
$getAutoProQuestionTable = $tablePrefix."watupro_question";
$getAutoProStudentAnswerTable = $tablePrefix."watupro_student_answers";

$gc=1;
for($gc=1;$gc<=8;$gc++)
{
	if($gc == 1)
	{
		$getCatDynamicID = $getCatIDTs1;
	}
	else if($gc == 2)
	{
		$getCatDynamicID = $getCatIDTs2;
	}
	else if($gc == 3)
	{
		$getCatDynamicID = $getCatIDTi1;
	}
	else if($gc == 4)
	{
		$getCatDynamicID = $getCatIDTi2;
	}
	else if($gc == 5)
	{
		$getCatDynamicID = $getCatIDSr1;
	}
	else if($gc == 6)
	{
		$getCatDynamicID = $getCatIDSr2;
	}
	else if($gc == 7)
	{
		$getCatDynamicID = $getCatIDPf1;
	}
	else if($gc == 8)
	{
		$getCatDynamicID = $getCatIDPf2;
	}
	$getPointsQuery =  "SELECT * FROM $getAutoProQuestionTable WHERE exam_id = $getExamID AND cat_id = $getCatDynamicID";
	$getPointsQueryRow = $wpdb->get_results($getPointsQuery);
	$getQuestionIDArray = array();
	if ( $getPointsQueryRow )
	{
		foreach($getPointsQueryRow as $getPointsQueryRowValue)
		{
			$getQuestionIDArray[] = $getPointsQueryRowValue->ID;
		}
		if($getQuestionIDArray && is_array($getQuestionIDArray))
		{
			$getQuestionIDImplode = implode(",",$getQuestionIDArray);
			$getQuestionPointsQuery =  "SELECT sum(points) as totalPoints FROM $getAutoProStudentAnswerTable WHERE question_id IN($getQuestionIDImplode) and taking_id = $taking_id";
			$getQuestionPointsQueryRow = $wpdb->get_row($getQuestionPointsQuery);
			if($getQuestionPointsQueryRow)
			{
				if($gc == 1)
				{
					$getTs1FinalPoints = $getQuestionPointsQueryRow->totalPoints;
				}
				else if($gc == 2)
				{
					$getTs2FinalPoints = $getQuestionPointsQueryRow->totalPoints;
				}
				else if($gc == 3)
				{
					$getTi1FinalPoints = $getQuestionPointsQueryRow->totalPoints;
				}
				else if($gc == 4)
				{
					$getTi2FinalPoints = $getQuestionPointsQueryRow->totalPoints;
				}
				else if($gc == 5)
				{
					$getSr1FinalPoints = $getQuestionPointsQueryRow->totalPoints;
				}
				else if($gc == 6)
				{
					$getSr2FinalPoints = $getQuestionPointsQueryRow->totalPoints;
				}
				else if($gc == 7)
				{
					$getPf1FinalPoints = $getQuestionPointsQueryRow->totalPoints;
				}
				else if($gc == 8)
				{
					$getPf2FinalPoints = $getQuestionPointsQueryRow->totalPoints;
				}
			}
		}
	}
}
$getTsFinalPoints = round($getTs2FinalPoints - $getTs1FinalPoints);
$getTiFinalPoints = round($getTi2FinalPoints - $getTi1FinalPoints);
$getSrFinalPoints = round($getSr2FinalPoints - $getSr1FinalPoints);
$getPfFinalPoints = round($getPf2FinalPoints - $getPf1FinalPoints);

if($getExamID == 5)
{
	$getCultureScore = $getCatGradeFinalGradeCountArray[0];
	$getSkillsScore = $getCatGradeFinalGradeCountArray[1];
	$getTeamsScore = $getCatGradeFinalGradeCountArray[2];
	$getStrategyScore = $getCatGradeFinalGradeCountArray[3];
	$getRewardScore = $getCatGradeFinalGradeCountArray[4];
	$getGradualScore = $getCatGradeFinalGradeCountArray[5];
	$getRadicalScore = $getCatGradeFinalGradeCountArray[6];
	$getLearningScore = $getCatGradeFinalGradeCountArray[7];

	$getCompetingScore = $getPart2GradesFinalGradeCountArray[0];
	$getCollaboratingScore = $getPart2GradesFinalGradeCountArray[1];
	$getCompromisingScore = $getPart2GradesFinalGradeCountArray[2];
	$getAvoidingScore = $getPart2GradesFinalGradeCountArray[3];
	$getAccommodatingScore = $getPart2GradesFinalGradeCountArray[4];
	
	include 'generate-pdf/fpdf.php';
}

// prepare output
$taken_start_time = date(get_option('date_format').' '.get_option('time_format'), strtotime($taking->start_time));
$taken_end_time = date(get_option('date_format').' '.get_option('time_format'), current_time('timestamp'));
$replace_these	= array('%%CORRECT%%', '%%TOTAL%%', '%%PERCENTAGE%%', '%%RATING%%', '%%CORRECT_ANSWERS%%', 
	'%%QUIZ_NAME%%', '%%DESCRIPTION%%', '%%POINTS%%', '%%CERTIFICATE%%', '%%GTITLE%%', '%%UNRESOLVED%%', 
'%%ANSWERS%%', '%%CATGRADES%%', '%%DATE%%', '%%EMAIL%%', '%%MAX-POINTS%%', '%%watupro-share-url%%',
	'%%TIME-SPENT%%', '%%USER-NAME%%', '%%AVG-POINTS%%', '%%AVG-PERCENT%%', '%%CONTACT%%', '%%BETTER-THAN%%', 
	'%%PERCENTAGEOFMAX%%', '%%ANSWERS-PAGINATED%%', '%%POINTS-ROUNDED%%', '%%START-TIME%%', '%%END-TIME%%', '%%TASK-SUPPORT%%', '%%TASK-INNOVATION%%','%%SOCIAL-RELATIONSHIPS%%','%%PERSONAL-FREEDOM%%','%%DYNAMIC-PDF-LINK%%');
$with_these= array($score, $total,  $percent, $rating, $score, stripslashes($exam->name), wpautop(stripslashes($exam->description)), $achieved,  $certificate, stripslashes(@$grade_obj->gtitle), $unresolved_questions, $result, $catgrades, date(get_option('date_format'), current_time('timestamp')), $user_email, $max_points, $share_url, $time_spent, 
$user_name, $avg_points, $avg_percent, $contact_data, $better_than, $pointspercent, $paginated_result, round($achieved), $taken_start_time, $taken_end_time, $getTsFinalPoints, $getTiFinalPoints, $getSrFinalPoints, $getPfFinalPoints,$dynamicDownloadPdfLink);

// Show the results    
$output = "<div id='startOutput'>&nbsp;</div>";
$output .= str_replace($replace_these, $with_these, wpautop(stripslashes($exam->final_screen), false));
$output = watupro_parse_answerto($output, $taking_id);

$email_output=str_replace($replace_these, $with_these, wpautop(stripslashes($exam->email_output), false));
$email_output = watupro_parse_answerto($email_output, $taking_id);  

// replace also in result
$grade = str_replace($replace_these, $with_these, $grade);

// store this taking
$_watu->update_taking($taking_id, $achieved, $grade, $output, $percent, $grade_obj, $catgrades, $contact_data, $pointspercent, $catgrades_array);

// send API call
if(empty($advanced_settings['dont_store_taking'])) {
	do_action('watupro_completed_exam', $taking_id);
	if(watupro_intel() and !empty($exam->fee) and !empty($exam->pay_always)) do_action('watupro_completed_paid_exam', $taking_id, $exam);
}
$output = apply_filters('watupro_content', $output);	
$email_output = apply_filters('watupro_content', $email_output);

// premature quiz text?
if(!empty($_POST['premature_end']) and !empty($advanced_settings['premature_text'])) {
	$output = wpautop(stripslashes(base64_decode($advanced_settings['premature_text']))) . $output;
}

// show output on the screen
if(empty($do_redirect)) print WatuPRO::cleanup($output, 'web');
else {
	if(empty($exam->no_ajax)) echo "WATUPRO_REDIRECT:::".$do_redirect;
}

// update taking output with the filters
$wpdb->query( $wpdb->prepare( "UPDATE ".WATUPRO_TAKEN_EXAMS." SET details=%s WHERE ID=%d", $output, $taking_id));

if(!empty($exam->email_output)) $output = $email_output; // here maybe replace output with email output

// clear any timer related info for this exam
delete_user_meta( $user_ID, "start_exam_".$exam->ID );
if(!empty($_SESSION['start_time'.$exam->ID])) unset($_SESSION['start_time'.$exam->ID]);
unset($_SESSION['watupro_taking_id_' . $exam->ID]);
   
// email details if required
if(strstr($output, '%%ADMIN-URL%%')) $output = str_replace('%%ADMIN-URL%%', admin_url("admin.php?page=watupro_takings&exam_id=".$exam->ID."&taking_id=".$taking_id), $output);
$exam->user_name = $user_name; // to use for email subject in email_results
$email_certificate_id = empty($certificate) ? 0 : $certificate_id;
$_watu->email_results($exam, $output, @$grade_obj->ID, $email_certificate_id);
if(!empty($exam->no_ajax) and !empty($do_redirect)) watupro_redirect($do_redirect);
  
// won't store results? delete the taking
if(!empty($advanced_settings['dont_store_taking'])) {
	$wpdb->query($wpdb->prepare("DELETE FROM ".WATUPRO_TAKEN_EXAMS." WHERE ID=%d", $taking_id));
	$wpdb->query($wpdb->prepare("DELETE FROM ".WATUPRO_STUDENT_ANSWERS." WHERE taking_id=%d", $taking_id));
}

// clear coupons if any
if(watupro_intel()) {
	$existing_coupon = WatuPROICoupons :: existing_coupon($user_ID);
	if(!empty($existing_coupon)) {
		$coupon = $coupon = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".WATUPRO_COUPONS." WHERE code=%s", trim($existing_coupon)));
		WatuPROICoupons :: coupon_used($coupon, $user_ID);
	}
}

if(empty($exam->no_ajax)) exit;// Exit due to ajax call