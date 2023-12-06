<?php
// enhanced show_exam
function watuproi_show_exam($view, $exam) {
	$advanced_settings = unserialize(stripslashes($exam->advanced_settings));	
	
	if(@file_exists(get_stylesheet_directory().'/watupro/i/show-exam.html.php')) return get_stylesheet_directory().'/watupro/i/show-exam.html.php';
	else return WATUPRO_PATH."/i/views/show-exam.html.php";
}

// will evaluate % correctly answered questions and whether we have to continue, disallow or premature finish the quiz
// the output from this function will be a string like "continue", "end" or "stop" and will be used by the JS function WatuPROIntel.runTimeLogic
function watuproi_evaluate_on_the_fly($taking_id) {
	global $wpdb;
	
	// first select the exam & its advanced settings
	$exam = $wpdb->get_row($wpdb->prepare("SELECT tE.ID, advanced_settings FROM ".WATUPRO_EXAMS." tE
		JOIN ".WATUPRO_TAKEN_EXAMS." tT ON tT.exam_id = tE.ID 
		WHERE tT.ID=%d", $taking_id));
	$advanced_settings = unserialize(stripslashes($exam->advanced_settings));	
	
	// select % correct in this taking
	$total_answers = $wpdb->get_var($wpdb->prepare("SELECT COUNT(tA.ID) FROM ".WATUPRO_STUDENT_ANSWERS." tA
		JOIN ".WATUPRO_QUESTIONS." tQ ON tQ.ID = tA.question_id AND tQ.is_survey=0
		WHERE tA.taking_id=%d", $taking_id));
	if(empty($total_answers)) return true; // avoid division by zero
		
	 $correct_answers	= $wpdb->get_var($wpdb->prepare("SELECT COUNT(tA.ID) FROM ".WATUPRO_STUDENT_ANSWERS." tA
	 	JOIN ".WATUPRO_QUESTIONS." tQ ON tQ.ID = tA.question_id AND tQ.is_survey=0
		WHERE tA.taking_id=%d AND tA.is_correct=1", $taking_id));
		
	$percent = round(100 * $correct_answers / $total_answers);	
		
	// know what to do giving advantage to the premature end setting
	if($advanced_settings['premature_end_question'] <= $_POST['current_question']) {
		if($advanced_settings['premature_end_percent'] > $percent) {
			// must end the quiz
			echo "end";
			exit;
		}
	} // end premature end checks
	
	// disallow continue?
	if($advanced_settings['prevent_forward_question'] <= $_POST['current_question']) {
		if($advanced_settings['prevent_forward_percent'] > $percent) {
			echo "stop";
			exit;
		}
	}
	
	// defaults to continue
	echo "continue";
}

// creates a bar chart from personality quiz results. Called by the watupro-personality-chart shortcode.
// works similar to watupro-basic-chart shortcode
function watuproi_personality_chart($atts = null) {
	global $wpdb;
	
	$taking_id = empty($GLOBALS['watupro_taking_id']) ? 0 : intval($GLOBALS['watupro_taking_id']);
	if(empty($taking_id)) $taking_id = empty($GLOBALS['watupro_view_taking_id']) ? 0 : intval($GLOBALS['watupro_view_taking_id']);
	if(empty($taking_id)) return '';
	
	// normalize params
	if(!in_array($show, array('points', 'percent'))) $show = 'points';		
	$round_points = empty($atts['round_points']) ? false : true;
	$width = empty($atts['bar_width']) ? 100 : intval($atts['bar_width']); 
	$max_height = empty($atts['bar_max_height']) ? 200 : intval($atts['bar_max_height']);
	
	$default_label_text = __('{{personality-type}} - {{points}} points', 'watupro');
	$text = empty($atts['label']) ? $default_label_text : sanitize_text_field($atts['label']);
	
	// select taking
	$taking = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".WATUPRO_TAKEN_EXAMS." WHERE ID=%d", $taking_id));
	
	// select points matching each personality type
	$grade_ids = unserialize($taking->personality_grade_ids);
	$grade_ids = array_count_values($grade_ids);
	
	// define colors or get them from atts
	$default_colors = array('red', 'green', 'blue', 'yellow', 'black', 'orange', 'maroon', 'pink', 'lightblue', 'gray');
	$colors = empty($atts['colors']) ? $default_colors : array_map('trim', explode(",", sanitize_text_field($atts['colors'])));
	if(!empty($atts['assoc_colors'])) {
		$associated_colors = array_map('trim', explode(",", sanitize_text_field($atts['assoc_colors'])));
		$assoc_colors = array();
		foreach($associated_colors as $asc) {
			$parts = explode(":", $asc);
			$assoc_colors[intval(trim($parts[0]))] = trim($parts[1]);
		}
	}
	
	$total_points = 0;
	foreach($grade_ids as $points) $total_points += $points;
	
	// the points step should roughly make the higher points bar 200px high
	if($total_points == 0) $total_points = 1; // avoid division by zero just in case
	$points_step = round($max_height / $total_points, 2);
	
	// create & return the chart HTML
	$content = '<table class="watupro-basic-chart watupro-personality_chart"><tr>';
	
	// go through each personality to draw its bar
	$index = 0;	

	foreach($grade_ids as $id => $points) {
		// select this personality type
		$personality_type = $wpdb->get_var($wpdb->prepare("SELECT gtitle FROM ".WATUPRO_GRADES." WHERE ID=%d", $id));
		if(empty($personality_type)) continue;
		
		// check if there is an associated color
		$color = '';
		if(!empty($assoc_colors[$id])) $color = $assoc_colors[$id];
		
		if(empty($color)) $color = empty($colors[$index]) ? $default_colors[$index] : $colors[$index];
		
		$index++; 	
		if($index >= 10) break; // up to 10 total
		$bar_text = str_replace(array('{{personality-type}}', '{{points}}'), array($personality_type, $points), $text);	
		
		$content .= '<td style="vertical-align:bottom;"><table class="watupro-personality-chart-points"><tr>';
		
		$content .='<td align="center" style="vertical-align:bottom;">';
		$content .= '<table style="width:'.$width.'px;margin:auto;"><tr><td style="background-color:'.$color.';height:'.round($points_step * $points). 'px;">&nbsp;</td></tr></table>'; 
		$content .='</td></tr>';
		
		$content .='<tr><td style="text-align:center;">'.$bar_text.'</td></tr>';		
		
		$content .= '</table></td>';
	}
	
	$content .= '</tr></table>';
	
	return $content;
} // end watuproi_personality_chart
