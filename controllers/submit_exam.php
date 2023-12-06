<?php
// called when exam is submitted
$_question = new WTPQuestion();
<<<<<<< HEAD
global $user_email, $user_identity, $question_catids, $post, $do_redirect, $achieved, $percent;	
=======
global $user_email, $user_identity, $question_catids, $post, $do_redirect, $achieved, $percent, $user_ID, $pointspercent;	
>>>>>>> branch/6.7.2
if(!is_user_logged_in()) $user_email = @$_POST['taker_email'];

if(watupro_intel()) require_once(WATUPRO_PATH."/i/models/question.php");

$taking_id = $_watu->add_taking($exam->ID);

<<<<<<< HEAD
=======
if(empty($_POST['no_ajax'])) setcookie('watupro_taking_id', $taking_id, time() + 24*3600, '/');
else {
  	?>
  	<script type="text/javascript" >
  	var d = new Date();
	d.setTime(d.getTime() + (24*3600*1000));
	var expires = "expires="+ d.toUTCString();     				
  	document.cookie = "watupro_taking_id=<?php echo $taking_id;?>;" + expires + ";path=/";
  	</script>
  	<?php
}

>>>>>>> branch/6.7.2
$_POST['watupro_current_taking_id'] = $GLOBALS['watupro_taking_id'] = $taking_id;  // needed in personality quizzes and shortcodes
if(empty($_POST['post_id']) and is_object($post)) $_POST['post_id'] = $post->ID;
$_watu->this_quiz = $exam;

<<<<<<< HEAD
$total = $score = $achieved = $max_points = $paginated_cnt = 0; 
$result = $unresolved_questions = $current_text = $paginated_result = '';
$user_grade_ids = array(); // used in personality quizzes (Intelligence module)
$cats_maxpoints = array();

$question_catids = array(); // used for category based pagination
foreach ($all_question as $qct=>$ques) {	
=======
// $score is num correctly answered questions
$total = $score = $achieved = $max_points = $paginated_cnt = $num_wrong = $num_empty = 0; 
$result = $unresolved_questions = $resolved_questions = $current_text = $paginated_result = $short_answers = '';
$user_grade_ids = array(); // used in personality quizzes (Intelligence module)
$cats_maxpoints = array();
$question_catids_closed = []; // closed category divs for watupro-rcwrapper
$all_feedbacks = []; // to consturct the onlyfeedback variable

// select answers for this taking ID that were stored during the quiz. We'll use them for question hint point adjustments and maybe for something else
if(!empty($exam->question_hints) ) {
	$stored_answers = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".WATUPRO_STUDENT_ANSWERS." WHERE taking_id=%d", $taking_id));
}
  
$question_catids = []; // used for category based pagination
$correct_nums = $empty_nums = []; // used in the answers paginator
$unresolved_by_cat = $answers_by_cat = []; // used to allow showing unresolved and answers vars per category
foreach ($all_question as $qct => $ques) {	
>>>>>>> branch/6.7.2
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
<<<<<<< HEAD
				WHERE taking_id=%d AND question_id=%d", $in_progress->ID, $ques->ID));
=======
				WHERE taking_id=%d AND question_id=%d", @$in_progress->ID, $ques->ID));
>>>>>>> branch/6.7.2
			if(empty($inprogress_answer)	or strlen($inprogress_answer) < 3) $_POST["answer-" . $ques->ID] = null;
		}
		
      $qct++;
      $question_content = $ques->question;
      // fill the gaps need to replace gaps
      if($ques->answer_type=='gaps') $question_content = preg_replace("/{{{([^}}}])*}}}/", "_____", $question_content);

		$ansArr = is_array( @$_POST["answer-" . $ques->ID] )? $_POST["answer-" . $ques->ID] : array();      
				
		// points and correct calculation
<<<<<<< HEAD
		list($points, $correct) = WTPQuestion::calc_answer($ques, $ansArr, $ques->q_answers, $user_grade_ids);
		$ques_max_points = WTPQuestion::max_points($ques);		
		$max_points += $ques_max_points;
		if($ques->cat_id) {
			if(!isset($cats_maxppoints[$ques->cat_id])) $cats_maxppoints[$ques->cat_id]['max_points']=0;
			$cats_maxppoints[$ques->cat_id]['max_points'] += $ques_max_points;
=======
		list($points, $correct, $is_empty) = WTPQuestion::calc_answer($ques, $ansArr, $ques->q_answers, $user_grade_ids);
		
		// adjustment for used hints?
		if(!empty($exam->question_hints)) $points = watupro_hint_adjust_points($stored_answers, $ques, $points);	
		
		// allow external adjustments
		$points = apply_filters('watupro_adjust_points', $points, $taking_id, $ques->ID, $ansArr);
		
		$ques_max_points = WTPQuestion::max_points($ques);		
		$max_points += $ques_max_points;
		if($ques->cat_id) {
			if(!isset($cats_maxpoints[$ques->cat_id])) $cats_maxpoints[$ques->cat_id]['max_points']=0;
			$cats_maxpoints[$ques->cat_id]['max_points'] += $ques_max_points;
			
			// are we adding subcategory max points to main category max points?
			if($ques->cat_parent_id and !empty($advanced_settings['sum_subcats_catgrades'])) {
				if(!isset($cats_maxpoints[$ques->cat_parent_id])) $cats_maxpoints[$ques->cat_parent_id]['max_points']=0;
				$cats_maxpoints[$ques->cat_parent_id]['max_points'] += $ques_max_points;
			} 
>>>>>>> branch/6.7.2
		}
		
		// handle sorting personalities
		if(!empty($exam->is_personality_quiz) and $ques->answer_type == 'sort' and watupro_intel()) {
			WatuPROIQuestion :: sort_question_personality($ques, $ansArr, $user_grade_ids);
		}
		
		// discard points?
		if($points > 0 and !$correct and $ques->reward_only_correct) $points = 0; 
		if($points and !$correct and $ques->discard_even_negative) $points = 0; 
<<<<<<< HEAD
						  			
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
=======
		if($points < 0 and $ques->no_negative) $points = 0;
		if(!empty($ques->max_allowed_points) and $ques->max_allowed_points > 0  and $points > $ques->max_allowed_points) $points = $ques->max_allowed_points;
			  			
  		list($answer_text, $current_text, $unresolved_text, $short_text, $answer_feedback) = $_question->process($_watu, $qct, $question_content, $ques, $ansArr, $correct, $points);
  		$unresolved_questions .= str_replace('[[watupro-resolvedclass]]', '', $unresolved_text);
      $all_feedbacks[] = $answer_feedback;
      
      // add unresolved by cat and answers by cat
      if(isset($answers_by_cat[$ques->cat_id])) $answers_by_cat[$ques->cat_id][] = $current_text;
      else $answers_by_cat[$ques->cat_id] = [$current_text];
      if(isset($unresolved_by_cat[$ques->cat_id])) $unresolved_by_cat[$ques->cat_id][] = $unresolved_text;
      else $unresolved_by_cat[$ques->cat_id] = [$unresolved_text];
  		
  		// replace the resolved class
  		if($correct) {
  			$current_text = str_replace('[[watupro-resolvedclass]]','watupro-resolved',$current_text);
  			$short_text = str_replace('[[watupro-resolvedclass]]','watupro-resolved',$short_text);
  		}
  		else {
  			$current_text = str_replace('[[watupro-resolvedclass]]','watupro-unresolved',$current_text);
  			$short_text = str_replace('[[watupro-resolvedclass]]','watupro-unresolved',$short_text);
  		}
  		
        // if last in the category and 
        if(!$ques->exclude_on_final_screen and !in_array($ques->cat_id, $question_catids_closed) and ($exam->single_page == WATUPRO_PAGINATE_PAGE_PER_CATEGORY or $exam->single_page == WATUPRO_PAGINATE_ONE_PER_PAGE) and $exam->group_by_cat) {
            $current_text .= '</div><!--close watupro-rcwrapper-->';
            $question_catids_closed[] = $ques->cat_id;
        }
            
  		if(empty($ques->exclude_on_final_screen)) {
  			$result .= $current_text;
  			$short_answers .= $short_text;
  			$paginated_result .= $current_text . "</div>";
  			if($correct) $resolved_questions .= $current_text;
  		}		 
  		
  		// insert taking data
  		$_watu->store_details($exam->ID, $taking_id, $ques->ID, $answer_text, $points, $ques->question, $correct, $current_text, $ques_max_points);
        
      if($correct) {
      	$score++;
      	$correct_nums[] = $qct;
      }  
      $achieved += $points;   
      
      // num empty and num wrong
      if($is_empty and empty($ques->is_survey)) {
      	$num_empty++;
      	$empty_nums[] = $qct;
      }
      if(!$is_empty and !$correct and empty($ques->is_survey)) $num_wrong++;
}

// allow third party plugins to modify number of points
$achieved = apply_filters('watupro-achieved-points', $achieved, $taking_id);

// uploaded files?
if($exam->no_ajax) {
	$result = WatuPROFileHandler :: final_screen($result, $taking_id);
	$paginated_result = WatuPROFileHandler :: final_screen($paginated_result, $taking_id);
}

$paginated_result .= WTPExam :: answers_paginator($paginated_cnt, $correct_nums, $empty_nums);
>>>>>>> branch/6.7.2
    
// calculate percentage
if($total==0) $percent=0;
else $percent = number_format($score / $total * 100, 2);
$percent = round($percent);

// percentage of max points
if($achieved <= 0 or $max_points <= 0) $pointspercent = 0;
else $pointspercent = number_format($achieved / $max_points * 100, 2);

<<<<<<< HEAD
// generic rating
$rating=$_watu->calculate_rating($total, $score, $percent);
	
// assign grade
list($grade, $certificate_id, $do_redirect, $grade_obj) = WTPGrade::calculate($exam_id, $achieved, $percent, 0, $user_grade_ids, $pointspercent);

// assign certificate if any
$certificate="";
if(!empty($certificate_id)) {	
	$certificate = WatuPROCertificate::assign($exam, $taking_id, $certificate_id, $user_ID);	
=======
// assign cats max points to grade object
WTPGrade :: $cats_maxpoints = $cats_maxpoints;

// generic rating
$rating = $_watu->calculate_rating($total, $score, $percent);
	
// assign grade - DEFAULT behavior. If quiz will calculate final grade based on category performance, then we'll calculate after categories 
if(empty($advanced_settings['final_grade_depends_on_categories']) or !empty($exam->reuse_default_grades)) {
	list($grade, $certificate_id, $do_redirect, $grade_obj) = WTPGrade::calculate($exam_id, $achieved, $percent, 0, $user_grade_ids, $pointspercent);	
}

// use default final screen and email output?
if( !empty( $advanced_settings['use_default_final_screen'] ) ) {
    $default_final_screen = get_option('watupro_default_final_screen');
    $exam->final_screen = $default_final_screen;
}
if( !empty( $advanced_settings['use_default_email_output'] ) ) {
    $default_email_output = get_option('watupro_default_email_output');
    $exam->email_output = $default_email_output;
>>>>>>> branch/6.7.2
}

// this is important for qTranslate-X integration. Should be done before replacing any variables
$exam->final_screen = apply_filters('watupro_qtranslate', $exam->final_screen);
<<<<<<< HEAD
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
=======
WTPUser :: split_final_screen($exam->final_screen);
$exam->email_output = apply_filters('watupro_qtranslate', $exam->email_output);
WTPUser :: split_final_screen($exam->email_output);

// category grades if any
list($catgrades, $catgrades_array) = WTPGrade::replace_category_grades($exam->final_screen, $taking_id, $exam->ID, $exam->email_output);

// replace category grades
if(!empty($catgrades_array) and is_array($catgrades_array)) {
	foreach($catgrades_array as $cnt => $catgrade) {	
		// category percentageofmax
		$percent_of_max = empty($cats_maxpoints[$catgrade['cat_id']]['max_points']) ? 0 : round(100 * $catgrade['points'] / $cats_maxpoints[$catgrade['cat_id']]['max_points']);
		if($catgrade['points'] <= 0) $percent_of_max = 0;			
		$catgrades_array[$cnt]['percent_points'] = $percent_of_max;
>>>>>>> branch/6.7.2
	
		$exam->final_screen =  str_replace('%%CATEGORY-NAME-'.$catgrade['cat_id'].'%%', $catgrade['name'], $exam->final_screen);
		$exam->final_screen =  str_replace('%%CATEGORY-DESCRIPTION-'.$catgrade['cat_id'].'%%', $catgrade['description'], $exam->final_screen);
		$exam->final_screen =  str_replace('%%CATEGORY-CORRECT-'.$catgrade['cat_id'].'%%', $catgrade['correct'], $exam->final_screen);
<<<<<<< HEAD
=======
		$exam->final_screen =  str_replace('%%CATEGORY-WRONG-'.$catgrade['cat_id'].'%%', $catgrade['wrong'], $exam->final_screen);
		$exam->final_screen =  str_replace('%%CATEGORY-EMPTY-'.$catgrade['cat_id'].'%%', $catgrade['empty'], $exam->final_screen);
>>>>>>> branch/6.7.2
		$exam->final_screen =  str_replace('%%CATEGORY-TOTAL-'.$catgrade['cat_id'].'%%', $catgrade['total'], $exam->final_screen);
		$exam->final_screen =  str_replace('%%CATEGORY-POINTS-'.$catgrade['cat_id'].'%%', $catgrade['points'], $exam->final_screen);
		$exam->final_screen =  str_replace('%%CATEGORY-PERCENTAGE-'.$catgrade['cat_id'].'%%', $catgrade['percent'], $exam->final_screen);
		$exam->final_screen =  str_replace('%%CATEGORY-GTITLE-'.$catgrade['cat_id'].'%%', $catgrade['gtitle'], $exam->final_screen);
		$exam->final_screen =  str_replace('%%CATEGORY-GDESCRIPTION-'.$catgrade['cat_id'].'%%', $catgrade['gdescription'], $exam->final_screen);	
		$exam->final_screen =  str_replace('%%CATEGORY-PERCENTAGEOFMAX-'.$catgrade['cat_id'].'%%', $percent_of_max, $exam->final_screen);
<<<<<<< HEAD
=======
		$exam->final_screen =  str_replace('%%CATEGORY-ANSWERS-'.$catgrade['cat_id'].'%%', WTPCategory :: answers_var($catgrade['cat_id'], $answers_by_cat), $exam->final_screen);
		$exam->final_screen =  str_replace('%%CATEGORY-UNRESOLVED-'.$catgrade['cat_id'].'%%', WTPCategory :: answers_var($catgrade['cat_id'], $unresolved_by_cat), $exam->final_screen);
>>>>>>> branch/6.7.2
		
		// same for email_output
		$exam->email_output =  str_replace('%%CATEGORY-NAME-'.$catgrade['cat_id'].'%%', $catgrade['name'], $exam->email_output);
		$exam->email_output =  str_replace('%%CATEGORY-DESCRIPTION-'.$catgrade['cat_id'].'%%', $catgrade['description'], $exam->email_output);
		$exam->email_output =  str_replace('%%CATEGORY-CORRECT-'.$catgrade['cat_id'].'%%', $catgrade['correct'], $exam->email_output);
<<<<<<< HEAD
=======
		$exam->email_output =  str_replace('%%CATEGORY-WRONG-'.$catgrade['cat_id'].'%%', $catgrade['wrong'], $exam->email_output);
		$exam->email_output =  str_replace('%%CATEGORY-EMPTY-'.$catgrade['cat_id'].'%%', $catgrade['empty'], $exam->email_output);
>>>>>>> branch/6.7.2
		$exam->email_output =  str_replace('%%CATEGORY-TOTAL-'.$catgrade['cat_id'].'%%', $catgrade['total'], $exam->email_output);
		$exam->email_output =  str_replace('%%CATEGORY-POINTS-'.$catgrade['cat_id'].'%%', $catgrade['points'], $exam->email_output);
		$exam->email_output =  str_replace('%%CATEGORY-PERCENTAGE-'.$catgrade['cat_id'].'%%', $catgrade['percent'], $exam->email_output);
		$exam->email_output =  str_replace('%%CATEGORY-GTITLE-'.$catgrade['cat_id'].'%%', $catgrade['gtitle'], $exam->email_output);
		$exam->email_output =  str_replace('%%CATEGORY-GDESCRIPTION-'.$catgrade['cat_id'].'%%', $catgrade['gdescription'], $exam->email_output);	
		$exam->email_output =  str_replace('%%CATEGORY-PERCENTAGEOFMAX-'.$catgrade['cat_id'].'%%', $percent_of_max, $exam->email_output);
<<<<<<< HEAD
		
		$getCatGradeFinalGradeCountArray[] = $catgrade['finalGradeCount'];
	}
}

// replace some old confusingly named vars
$exam->final_screen = str_replace("%%SCORE%%", "%%CORRECT%%", $exam->final_screen);
=======
		$exam->email_output =  str_replace('%%CATEGORY-ANSWERS-'.$catgrade['cat_id'].'%%', WTPCategory :: answers_var($catgrade['cat_id'], $answers_by_cat), $exam->email_output);
		$exam->email_output =  str_replace('%%CATEGORY-UNRESOLVED-'.$catgrade['cat_id'].'%%', WTPCategory :: answers_var($catgrade['cat_id'], $unresolved_by_cat), $exam->email_output);
	}
}

// assign grade - DEPENDS ON CATEGORY behavior. If quiz will calculate final grade based on category performance, then we'll calculate after categories 
if(!empty($advanced_settings['final_grade_depends_on_cats']) and empty($exam->reuse_default_grades)) {
	list($grade, $certificate_id, $do_redirect, $grade_obj) = WTPGrade::calculate_dependent($exam_id, $catgrades_array, $achieved, $percent, $user_grade_ids, $pointspercent, $certificate_id);
}

// assign certificate if any
$certificate="";
if(!empty($certificate_id)) {	
   // here if array, we'll loop through it and add to $certificate var instead of just replacing once
   if(!is_array($certificate_id)) $certificate_id = array($certificate_id);
   $certificate = '';
   foreach($certificate_id as $cert_id) {
   	$certificate .= WatuPROCertificate::assign($exam, $taking_id, $cert_id, $user_ID);
   }	
}

// replace some old confusingly named vars
$exam->final_screen = str_replace("%%SCORE%%", "%%CORRECT%%", $exam->final_screen);
$exam->email_output = str_replace("%%SCORE%%", "%%CORRECT%%", $exam->email_output);
>>>>>>> branch/6.7.2

// url to share the final screen and maybe redirect to it?
$post_url = empty($post) ? get_permalink($_POST['post_id']) : get_permalink($post->ID);
$post_url .= strstr($post_url, "?") ? "&" : "?";  
$share_url = $post_url."waturl=".base64_encode($exam->ID."|".$taking_id);
if(!empty($exam->shareable_final_screen) and !empty($exam->redirect_final_screen)) $do_redirect = $share_url;

<<<<<<< HEAD
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
=======
// finally, $do_redirect could be overridden by session
if(!empty($_COOKIE['watupro_goto_url'])) {
	$do_redirect = sanitize_text_field($_COOKIE['watupro_goto_url']);
	setcookie('watupro_goto_url', '', time() - 24*3600, '/');
}

$taking = $wpdb->get_row($wpdb->prepare("SELECT start_time, end_time FROM ".WATUPRO_TAKEN_EXAMS." WHERE ID=%d", $taking_id));

####################### VARIOUS AVERAGE CALCULATIONS (think about placing them in function / method #######################
// calculate averages
$avg_points = $avg_percent = $avg_percentofmax = '';
if(strstr($exam->final_screen, '%%AVG-POINTS%%') or strstr($exam->email_output, '%%AVG-POINTS%%')) $avg_points = WatuPROTaking :: avg_points($taking_id, $exam->ID);
if(strstr($exam->final_screen, '%%AVG-PERCENT%%') or strstr($exam->email_output, '%%AVG-PERCENT%%')) $avg_percent = WatuPROTaking :: avg_percent($taking_id, $exam->ID); 
if(strstr($exam->final_screen, '%%AVG-PERCENTOFMAX%%') or strstr($exam->email_output, '%%AVG-PERCENTOFMAX%%')) $avg_percentofmax = WatuPROTaking :: avg_percent_of_max($taking_id, $exam->ID);
>>>>>>> branch/6.7.2

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

<<<<<<< HEAD
=======
// time spent on this quiz
$time_spent = '';
if(strstr($exam->final_screen, '%%TIME-SPENT%%') or strstr($exam->email_output, '%%TIME-SPENT%%')) {
	$taking->end_time =	current_time('mysql');
	$time_spent = WTPRecord :: time_spent_human( WTPRecord :: time_spent($taking));
}

>>>>>>> branch/6.7.2
// prepare contact data about the user
$_POST['taker_name'] = empty($_POST['taker_name']) ? @$_POST['watupro_taker_name'] : $_POST['taker_name']; // when coming from non-ajax quiz
$user_name = empty($_POST['taker_name']) ? $user_identity : $_POST['taker_name'];
if(empty($user_name)) $user_name = __('Guest', 'watupro');
$_POST['taker_email'] = empty($_POST['taker_email']) ? @$_POST['watupro_taker_email'] : $_POST['taker_email']; // when coming from non-ajax quiz
$user_email = empty($_POST['taker_email']) ? $user_email : $_POST['taker_email'];
$_POST['taker_phone'] = empty($_POST['taker_phone']) ? @$_POST['watupro_taker_phone'] : $_POST['taker_phone']; // when coming from non-ajax quiz
$_POST['taker_company'] = empty($_POST['taker_company']) ? @$_POST['watupro_taker_company'] : $_POST['taker_company']; // when coming from non-ajax quiz
<<<<<<< HEAD
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
=======
$_POST['taker_field1'] = empty($_POST['taker_field1']) ? @$_POST['watupro_taker_field1'] : $_POST['taker_field1']; // when coming from non-ajax quiz
$_POST['taker_field2'] = empty($_POST['taker_field2']) ? @$_POST['watupro_taker_field2'] : $_POST['taker_field2']; // when coming from non-ajax quiz
$_POST['taker_checkbox'] = empty($_POST['taker_checkbox']) ? @$_POST['watupro_taker_checkbox'] : $_POST['taker_checkbox']; // when coming from non-ajax quiz
// user data from contact fields
$contact_data = '';
   
if(!empty($_POST['taker_phone']) or !empty($_POST['taker_company']) or !empty($_POST['taker_field1']) or !empty($_POST['taker_field2'])) {
		$contact_datas = array();
		if(!empty($_POST['taker_phone'])) $contact_datas[] = sprintf(__('%s: %s', 'watupro'), stripslashes(rawurldecode($advanced_settings['contact_fields']['phone_label'])), $_POST['taker_phone']);
		if(!empty($_POST['taker_company'])) $contact_datas[] = sprintf(__('%s: %s', 'watupro'), stripslashes(rawurldecode($advanced_settings['contact_fields']['company_label'])), $_POST['taker_company']);
		if(!empty($_POST['taker_field1'])) $contact_datas[] = sprintf(__('%s: %s', 'watupro'), stripslashes(rawurldecode($advanced_settings['contact_fields']['field1_label'])), $_POST['taker_field1']);
		if(!empty($_POST['taker_field2'])) $contact_datas[] = sprintf(__('%s: %s', 'watupro'), stripslashes(rawurldecode($advanced_settings['contact_fields']['field2_label'])), $_POST['taker_field2']);
		$contact_data = implode(', ', $contact_datas);

      $replace_fields = array('company'=>@$_POST['taker_company'], 'phone' => @$_POST['taker_phone'], 'field1' => @$_POST['taker_field1'], 'field2' => @$_POST['taker_field2']);
		$exam->final_screen = WTPExam :: replace_contact_fields($exam, $replace_fields, $exam->final_screen);
		$exam->email_output = WTPExam :: replace_contact_fields($exam, $replace_fields, $exam->email_output);	
}

// extra contact fields still not replaced? Replace with n/a
$exam->final_screen = str_replace(array('%%FIELD-COMPANY%%', '%%FIELD-PHONE%%', '%%FIELD-1%%', '%%FIELD-2%%'), __('N/a', 'watupro'), $exam->final_screen); 
$exam->email_output = str_replace(array('%%FIELD-COMPANY%%', '%%FIELD-PHONE%%', '%%FIELD-1%%', '%%FIELD-2%%'), __('N/a', 'watupro'), $exam->email_output);

// %%ANSWERS-NOFEEDBACK%%
$result_nofeedback = preg_replace("/<!--watupro-feedback-->[\s\S]+?<!--end-watupro-feedback-->/", '', $result);
$feedback_noanswers = preg_replace("/<!--watupro-question-choices-->[\s\S]+?<!--end-watupro-question-choices-->/", '', $result);
$onlyfeedback = implode(' ', $all_feedbacks);
// now remove all the unnecessary elements from onlyfeedback
>>>>>>> branch/6.7.2

// prepare output
$taken_start_time = date(get_option('date_format').' '.get_option('time_format'), strtotime($taking->start_time));
$taken_end_time = date(get_option('date_format').' '.get_option('time_format'), current_time('timestamp'));
<<<<<<< HEAD
=======

$percent_wrong = 100 - $percent;

>>>>>>> branch/6.7.2
$replace_these	= array('%%CORRECT%%', '%%TOTAL%%', '%%PERCENTAGE%%', '%%RATING%%', '%%CORRECT_ANSWERS%%', 
	'%%QUIZ_NAME%%', '%%DESCRIPTION%%', '%%POINTS%%', '%%CERTIFICATE%%', '%%GTITLE%%', '%%UNRESOLVED%%', 
'%%ANSWERS%%', '%%CATGRADES%%', '%%DATE%%', '%%EMAIL%%', '%%MAX-POINTS%%', '%%watupro-share-url%%',
	'%%TIME-SPENT%%', '%%USER-NAME%%', '%%AVG-POINTS%%', '%%AVG-PERCENT%%', '%%CONTACT%%', '%%BETTER-THAN%%', 
<<<<<<< HEAD
	'%%PERCENTAGEOFMAX%%', '%%ANSWERS-PAGINATED%%', '%%POINTS-ROUNDED%%', '%%START-TIME%%', '%%END-TIME%%', '%%TASK-SUPPORT%%', '%%TASK-INNOVATION%%','%%SOCIAL-RELATIONSHIPS%%','%%PERSONAL-FREEDOM%%','%%DYNAMIC-PDF-LINK%%');
$with_these= array($score, $total,  $percent, $rating, $score, stripslashes($exam->name), wpautop(stripslashes($exam->description)), $achieved,  $certificate, stripslashes(@$grade_obj->gtitle), $unresolved_questions, $result, $catgrades, date(get_option('date_format'), current_time('timestamp')), $user_email, $max_points, $share_url, $time_spent, 
$user_name, $avg_points, $avg_percent, $contact_data, $better_than, $pointspercent, $paginated_result, round($achieved), $taken_start_time, $taken_end_time, $getTsFinalPoints, $getTiFinalPoints, $getSrFinalPoints, $getPfFinalPoints,$dynamicDownloadPdfLink);
=======
	'%%PERCENTAGEOFMAX%%', '%%ANSWERS-PAGINATED%%', '%%POINTS-ROUNDED%%', '%%START-TIME%%', '%%END-TIME%%', '%%WRONG%%', 
	'%%WRONG_ANSWERS%%', '%%SHORT_ANSWERS%%', '%%SHORT-ANSWERS%%','%%CERTIFICATE_ID%%', '%%EMPTY%%', '%%ATTEMPTED%%', '%%UNIQUE-ID%%', 
	'%%AVG-PERCENTOFMAX%%', '%%AVG-PERCENTAGEOFMAX%%', '%%PERCENT%%', '%%PERCENTOFMAX%%', '%%RESOLVED%%', '%%PERCENT-WRONG%%', 
	'%%PERCENTOFMAXLEFT%%', '%%ANSWERS-NOFEEDBACK%%', '%%FEEDBACK-NOANSWERS%%', '%%ONLYFEEDBACK%%');
	
$with_these = array($score, $total,  $percent, $rating, $score, stripslashes($exam->name), wpautop(stripslashes($exam->description)), $achieved,  $certificate, 
   stripslashes(@$grade_obj->gtitle), $unresolved_questions, $result, $catgrades, date_i18n(get_option('date_format'), current_time('timestamp')), $user_email, 
   $max_points,  $share_url, $time_spent, stripslashes($user_name), $avg_points, $avg_percent, $contact_data, $better_than, round($pointspercent), $paginated_result, 
   round($achieved), $taken_start_time, $taken_end_time, $num_wrong, $num_wrong, $short_answers, $short_answers, (is_array($certificate_id) ? implode(', ', $certificate_id) : $certificate_id), $num_empty, ($total - $num_empty),
   sprintf('%08d',$taking_id), $avg_percentofmax, $avg_percentofmax, $percent, round($pointspercent), $resolved_questions, $percent_wrong, 100 - round($pointspercent), $result_nofeedback, $feedback_noanswers, $onlyfeedback);
>>>>>>> branch/6.7.2

// Show the results    
$output = "<div id='startOutput'>&nbsp;</div>";
$output .= str_replace($replace_these, $with_these, wpautop(stripslashes($exam->final_screen), false));
<<<<<<< HEAD
$output = watupro_parse_answerto($output, $taking_id);

$email_output=str_replace($replace_these, $with_these, wpautop(stripslashes($exam->email_output), false));
$email_output = watupro_parse_answerto($email_output, $taking_id);  
=======
$output = watupro_parse_answerto($output, $taking_id, $exam);
$email_output = str_replace($replace_these, $with_these, wpautop(stripslashes($exam->email_output), false));
$email_output = watupro_parse_answerto($email_output, $taking_id, $exam);  

// Answers table on the final screen
if(strstr($output, '%%ANSWERS-TABLE%%')) $output = str_replace('%%ANSWERS-TABLE%%', WatuPROTaking :: answers_table($taking_id), $output);
if(strstr($email_output, '%%ANSWERS-TABLE%%')) $email_output = str_replace('%%ANSWERS-TABLE%%', WatuPROTaking :: answers_table($taking_id, 'email'), $email_output); // for now replace with nothing. We may think about an email version
>>>>>>> branch/6.7.2

// replace also in result
$grade = str_replace($replace_these, $with_these, $grade);

// store this taking
<<<<<<< HEAD
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
=======
$_watu->advanced_settings = $advanced_settings;
$other_taking_data = array('num_correct' => $score, 'num_wrong' => $num_wrong, 'num_empty' => $num_empty, 'max_points' => $max_points);
$_watu->update_taking($taking_id, $achieved, $grade, $output, $percent, $grade_obj, $catgrades, $contact_data, $pointspercent, $catgrades_array, $other_taking_data);

if(!empty($user_ID)) update_user_meta($user_ID, 'watupro_current_taking', 0);

// update contact details if required
watupro_save_contacts();

// clear retake allowance used by the shortcode att in [watupro X show_last_result=1]
if(!empty($user_ID)) delete_user_meta($user_ID, 'watupro_retake_mode_'.$exam->ID);

// clear any timer related info for this exam
delete_user_meta( $user_ID, "start_exam_".$exam->ID );
if(!empty($_COOKIE['start_time'.$exam->ID])) {
	if(empty($_POST['no_ajax'])) setcookie('start_time'.$exam->ID, '', time() - 48*3600, '/');
	else {
	  	?>
	  	<script type="text/javascript" >
	  	var d = new Date();
		d.setTime(d.getTime() - (24*3600*1000));
		var expires = "expires="+ d.toUTCString();     				
	  	document.cookie = "start_time<?php echo $exam->ID?>=;" + expires + ";path=/";
	  	</script>
	  	<?php
	}
}  

if(empty($_POST['no_ajax'])) {	
	setcookie('watupro_taking_id_' . $exam->ID, '', time() - 48*3600, '/');	
}
else {	
  	?>
  	<script type="text/javascript" >
  	var d = new Date();
	d.setTime(d.getTime() - (168*3600*1000));
	var expires = "expires="+ d.toUTCString();
  	document.cookie = "watupro_taking_id_<?php echo $exam->ID?>=;" + expires + ";path=/";
	//alert("watupro_taking_id_<?php echo $exam->ID?>=;" + expires + ";path=/");
  	</script>
  	<?php
}

// send API call
// if is_user_logged_in is used because we may have "store_taking_only_logged" selected
if(empty($advanced_settings['dont_store_taking'])) {	
	if(empty($advanced_settings['store_taking_only_logged']) or is_user_logged_in()) {
		do_action('watupro_completed_exam', $taking_id);
		if(watupro_intel() and !empty($exam->fee) and !empty($exam->pay_always)) do_action('watupro_completed_paid_exam', $taking_id, $exam);
	}	
}

$output = apply_filters('watupro_content', $output);	
$email_output = apply_filters('watupro_content', $email_output);
$output = apply_filters('watupro_custom_vars', $output, $taking_id);	
$email_output = apply_filters('watupro_custom_vars', $email_output, $taking_id);

// premature quiz text?
if(!empty($_POST['premature_end']) and !empty($advanced_settings['premature_text'])) {
	$output = wpautop(stripslashes(rawurldecode($advanced_settings['premature_text']))) . $output;
}

// ran out of time due to timer?
if(!empty($_POST['auto_submitted'])) {
	$output = "<p style='color:red';>".sprintf(__('You ran out of time and your %s was automatically submitted.', 'watupro'), WATUPRO_QUIZ_WORD)."</p>" . $output;
}

// show output on the screen
$screen_output = $output;

// delayed results?
$exam->delay_results = WTPUser :: delay_results($exam);
if($exam->delay_results and current_time('timestamp') < strtotime($exam->delay_results_date)) {	
	$screen_output = stripslashes($exam->delay_results_content);
	$screen_output  = apply_filters('watupro_content', $screen_output);
}

if(empty($do_redirect)) print WatuPRO::cleanup($screen_output, 'web');
else {
	if(empty($exam->no_ajax)) echo "WATUPRO_REDIRECT:::".$do_redirect.":::";
>>>>>>> branch/6.7.2
}

// update taking output with the filters
$wpdb->query( $wpdb->prepare( "UPDATE ".WATUPRO_TAKEN_EXAMS." SET details=%s WHERE ID=%d", $output, $taking_id));

if(!empty($exam->email_output)) $output = $email_output; // here maybe replace output with email output

<<<<<<< HEAD
// clear any timer related info for this exam
delete_user_meta( $user_ID, "start_exam_".$exam->ID );
if(!empty($_SESSION['start_time'.$exam->ID])) unset($_SESSION['start_time'.$exam->ID]);
unset($_SESSION['watupro_taking_id_' . $exam->ID]);
   
=======
>>>>>>> branch/6.7.2
// email details if required
if(strstr($output, '%%ADMIN-URL%%')) $output = str_replace('%%ADMIN-URL%%', admin_url("admin.php?page=watupro_takings&exam_id=".$exam->ID."&taking_id=".$taking_id), $output);
$exam->user_name = $user_name; // to use for email subject in email_results
$email_certificate_id = empty($certificate) ? 0 : $certificate_id;
<<<<<<< HEAD
$_watu->email_results($exam, $output, @$grade_obj->ID, $email_certificate_id);
if(!empty($exam->no_ajax) and !empty($do_redirect)) watupro_redirect($do_redirect);
  
// won't store results? delete the taking
if(!empty($advanced_settings['dont_store_taking'])) {
=======
$_watu->email_results($exam, $output, @$grade_obj->ID, $email_certificate_id, $taking_id);
if(!empty($exam->no_ajax) and !empty($do_redirect)) watupro_redirect($do_redirect);

// send this action regardless if we store taking. Will be used by MoolaMojo and perhaps other plugins
do_action('watupro_completed_exam_detailed', $taking_id, $exam, @$user_ID, $achieved, @$grade_obj->ID);

// maybe calculate the total number of points collected by the user?
if(get_option('watupro_calculate_total_user_points') == 1 and is_user_logged_in()) {
	$total_user_points = absint( get_user_meta($user_ID, 'watupro_total_points', true) );
	$total_user_points += $achieved;
	update_user_meta($user_ID, 'watupro_total_points', $total_user_points);
}
  
// in case of $advanced_settings['store_taking_first_last'] == 'first', we just need to check if at this moment there is more than 
// one attempt of this user in the DB. If yes, set $advanced_settings['dont_store_taking'] to true
if(empty($advanced_settings['dont_store_taking']) and !empty($advanced_settings['store_taking_only_logged']) 
    and !empty($advanced_settings['store_taking_first_last']) and $advanced_settings['store_taking_first_last'] == 'first' 
    and is_user_logged_in()) {
        $n = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".WATUPRO_TAKEN_EXAMS." 
            WHERE exam_id=%d AND user_id=%d AND in_progress=0", $exam->ID, $user_ID));
        if($n > 1) $advanced_settings['dont_store_taking'] = true;
}
  
// won't store results? delete the taking
if(!empty($advanced_settings['dont_store_taking']) or (!empty($advanced_settings['store_taking_only_logged']) and !is_user_logged_in())) {
>>>>>>> branch/6.7.2
	$wpdb->query($wpdb->prepare("DELETE FROM ".WATUPRO_TAKEN_EXAMS." WHERE ID=%d", $taking_id));
	$wpdb->query($wpdb->prepare("DELETE FROM ".WATUPRO_STUDENT_ANSWERS." WHERE taking_id=%d", $taking_id));
}

<<<<<<< HEAD
=======
// or store only last attempt? need to delete any other attempts
if(!empty($advanced_settings['store_taking_only_logged']) and !empty($advanced_settings['store_taking_first_last']) and $advanced_settings['store_taking_first_last'] == 'last' and is_user_logged_in()) {
	$wpdb->query($wpdb->prepare("DELETE FROM ".WATUPRO_TAKEN_EXAMS." WHERE user_id=%d AND exam_id=%d AND ID!=%d", $user_ID, $exam->ID, $taking_id));
	$wpdb->query($wpdb->prepare("DELETE FROM ".WATUPRO_STUDENT_ANSWERS." WHERE user_id=%d AND exam_id=%d AND taking_id != %d", $taking_id));
}

>>>>>>> branch/6.7.2
// clear coupons if any
if(watupro_intel()) {
	$existing_coupon = WatuPROICoupons :: existing_coupon($user_ID);
	if(!empty($existing_coupon)) {
		$coupon = $coupon = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".WATUPRO_COUPONS." WHERE code=%s", trim($existing_coupon)));
		WatuPROICoupons :: coupon_used($coupon, $user_ID);
	}
}
<<<<<<< HEAD

if(empty($exam->no_ajax)) exit;// Exit due to ajax call
=======
if(empty($exam->no_ajax)) exit;// Exit due to ajax call
if(!empty($exam->no_ajax)):
	if(get_option('watupro_disable_copy') == 1): ?>
	<script type="text/javascript" >
	jQuery('.show-question').bind("cut copy",function(e) {
      	e.preventDefault();
	   });
	   jQuery('.show-question').bind("contextmenu",function(e) {
	     	e.preventDefault();
	   });
	</script>
<?php endif; 
endif;
>>>>>>> branch/6.7.2
