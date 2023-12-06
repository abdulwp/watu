<?php 
// contains little procedural functions to output various HTML strings

// Adapted code from the MIT licensed QuickDD class
// created also by us
if(!function_exists('WTPquickDD_date')) {
	function WTPquickDD_date($name, $date=NULL, $format=NULL, $markup=NULL, $start_year=1900, $end_year=2100)
	{
	   // normalize params
	   if(empty($date) or !preg_match("/\d\d\d\d\-\d\d-\d\d/",$date)) $date=date("Y-m-d");
	    if(empty($format)) $format="YYYY-MM-DD";
	    if(empty($markup)) $markup=array();
	
	    $parts=explode("-",$date);
	    $html="";
	
	    // read the format
	    $format_parts=explode("-",$format);
	
	    $errors=array();
	    
	    // let's output
	    foreach($format_parts as $cnt=>$f)
	    {
	        if(preg_match("/[^YMD]/",$f)) 
	        { 
	            $errors[]="Unrecognized format part: '$f'. Skipped.";
	            continue;
	        }
	
	        // year
	        if(strstr($f,"Y"))
	        {
	            $extra_html="";
	            if(isset($markup[$cnt]) and !empty($markup[$cnt])) $extra_html=" ".$markup[$cnt];
	            $html.=" <select name=\"".$name."year\"".$extra_html.">\n";
	
	            for($i=$start_year;$i<=$end_year;$i++)
	            {
	                $selected="";
	                if(!empty($parts[0]) and $parts[0]==$i) $selected=" selected";
	                
	                $val=$i;
	                // in case only two digits are passed we have to strip $val for displaying
	                // it's either 4 or 2, everything else is ignored
	                if(strlen($f)<=2) $val=substr($val,2);        
	                
	                $html.="<option value='$i'".$selected.">$val</option>\n";
	            }
	
	            $html.="</select>";    
	        }
	
	        // month
	        if(strstr($f,"M"))
	        {
	            $extra_html="";
	            if(isset($markup[$cnt]) and !empty($markup[$cnt])) $extra_html=" ".$markup[$cnt];
	            $html.=" <select name=\"".$name."month\"".$extra_html.">\n";
	
	            for($i=1;$i<=12;$i++)
	            {
	                $selected="";
	                if(!empty($parts[1]) and intval($parts[1])==$i) $selected=" selected";
	                
	                $val=sprintf("%02d",$i);
	                    
	                $html.="<option value='$val'".$selected.">$val</option>\n";
	            }
	
	            $html.="</select>";    
	        }
	
	        // day - we simply display 1-31 here, no extra intelligence depending on month
	        if(strstr($f,"D"))
	        {
	            $extra_html="";
	            if(isset($markup[$cnt]) and !empty($markup[$cnt])) $extra_html=" ".$markup[$cnt];
	            $html.=" <select name=\"".$name."day\"".$extra_html.">\n";
	
	            for($i=1;$i<=31;$i++)
	            {
	                $selected="";
	                if(!empty($parts[2]) and intval($parts[2])==$i) $selected=" selected";
	                
	                if(strlen($f)>1) $val=sprintf("%02d",$i);
	                else $val=$i;
	                    
	                $html.="<option value='$val'".$selected.">$val</option>\n";
	            }
	
	            $html.="</select>";    
	        }
	    }
	
	    // that's it, return dropdowns:
	    return $html;
	}
}

// safe redirect
function watupro_redirect($url)
{
	echo "<meta http-equiv='refresh' content='0;url=$url' />"; 
	exit;
}


// displays session flash, errors etc, and clears them if required
function watupro_display_alerts() {
	global $error, $success;
	
	if(!empty($_SESSION['flash'])) {
		echo "<div class='watupro-alert'><p>".$_SESSION['flash']."</p></div>";
		unset($_SESSION['flash']);
	}
	
	if(!empty($error)) {
		echo '<div class="watupro-error"><p>'.$error.'</p></div>';
	}
	
	if(!empty($success)){
		echo '<div class="watupro-success"><p>'.$success.'</p></div>';
	}
}

// program-specific serialization of questions with answers
// serializes like this: qID:ansID,ansID,ansID|qID:|qID:ansID,ansID etc
function watupro_serialize_questions($questions) {
	$str = "";
	foreach($questions as $ct=>$question) {
		if($ct) $str.=" | ";
		$str.=$question->ID.":";
		if(is_array($question->q_answers)) {
			foreach($question->q_answers as $cnt=>$answer) {
				if($cnt) $str.=",";
				$str .= $answer->ID;
			}
		}
	}
	
	return $str;
}

// unserialization from the format given above in watupro_serialize_questions
function watupro_unserialize_questions($str) {
	global $wpdb;
	
	$questions = explode(" | ", $str);
	
	// extract all IDs to save queries
	$qids = $aids = array(0);
	
	foreach($questions as $question) {
		 $parts = explode(":", $question);
		 if(!empty($parts[0]) and $parts[0] != 'undefined') $qids[] = $parts[0];
		 $answers = explode(",", @$parts[1]);
		 foreach($answers as $answer) {
		 	   if(empty($answer)) continue;
		 		$aids[] = $answer;
		 	}	
	}
	
	// now select all questions and answers
	$all_questions = $wpdb->get_results("SELECT tQ.*, tC.name as cat, tC.description as cat_description
        FROM ".WATUPRO_QUESTIONS." tQ LEFT JOIN ".WATUPRO_QCATS." tC ON tQ.cat_id = tC.ID
        WHERE tQ.ID IN (".implode(",", $qids).") AND tQ.is_inactive=0");
  if(!sizeof($all_questions)) return null;      
	$all_answers = $wpdb->get_results("SELECT * FROM ".WATUPRO_ANSWERS." WHERE ID IN (".implode(",", $aids).")");
	
	// now re-match them in the stored way
	$final_questions = array();
	foreach($questions as $question) {
		list($qid, $aids) = explode(":", $question);
		$aids = explode(",", $aids);
		
		foreach($all_questions as $q) {
			 if($q->ID == $qid) {
			 		$answers = array();
			 		foreach($aids as $aid) {
			 			foreach($all_answers as $answer) {
			 				if($answer->ID == $aid) $answers[] = $answer;
			 			} 
			 		}
			 		// add newly found answers to the matching question
			 		$q->q_answers = $answers;
			 		$final_questions[] = $q;
			 }	
		}
	}
	
	return $final_questions;
}	

// unserialize answer from "in progress" taking
function watupro_unserialize_answer($answer) {
	global $wpdb;
	
	$answer_arr = unserialize(stripslashes($answer->answer));
	
	$question = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".WATUPRO_QUESTIONS." WHERE ID=%d", $answer->question_id));
	
	$answer_value = $answer_arr[0];
	list($points, $is_correct) = WTPQuestion :: calc_answer($question, $answer_value);
	
	$answer->answer = $answer_value;
	$answer->is_correct = $is_correct;
	$answer->points  = $points;
	
	return $answer;
}

// this function outputs basic email field when "email user..." is selected and the user is not logged in
function watupro_ask_for_email($exam) {
	$advanced_settings = unserialize(stripslashes($exam->advanced_settings));	
	
	// do this only if the exam description does not contain the {{{email-field tag
	if(strstr($exam->description, "{{{email-field") 
		or (!empty($advanced_settings['ask_for_contact_details']) and !empty($advanced_settings['contact_fields']['email']))) return '';		
	
	echo "<p><label>".__('Enter email to receive quiz results:','watupro') .
		"</label> <input type='text' size='30' name='watupro_taker_email' id='watuproTakerEmail".$exam->ID."' class='watupro-autogenerated'></p>";
}

function watupro_mktime($datetime) {
	list($date, $time) = explode(" ", $datetime);
	list($year, $month, $day) = explode("-", $date);
	list($h, $m, $s) = explode(":", $time);
	$unixtime = mktime($h,$m,$s,$month,$day, $year);
	return $unixtime;
}

// displays category header or pagination divs when exam is 
// paginated by category
// used both in initial exam display and in submit_exam
function watupro_cat_header($exam, $qct, $ques, $mode = 'show') {
	if(($mode == 'show' and $exam->single_page != WATUPRO_PAGINATE_PAGE_PER_CATEGORY) or !$exam->group_by_cat) return '';	
	global $cat_count, $question_catids;
	if(empty($cat_count)) $cat_count = 1;
	$output = '';

	if(!in_array($ques->cat_id, $question_catids)) {
		 if($qct and $mode == 'show') $output .= "</div>"; // close previous category div	   	 	
   	 if($mode=='show') $output .= "<div id='catDiv".$cat_count."' style='display:".($qct?'none':'block')."' class='watupro_catpage'>";
   	 $output .= "<h3>".stripslashes(apply_filters('watupro_qtranslate', $ques->cat))."</h3>";
   	 if(!empty($ques->cat_description)) $output .= "<div>".apply_filters('watupro_content', stripslashes($ques->cat_description))."</div>";
   	 $cat_count++;   	 
   }
	 
   return $output;
}

// similar to cat_header but used when pagination is "X questions per page"
function watupro_paginate_header($exam, $qct, $num_pages, $mode = 'show') {
	if($exam->single_page != WATUPRO_PAGINATE_CUSTOM_NUMBER) return '';	
	
	global $page_count;
	if(empty($page_count)) $page_count = 1;
	$output = '';
	
	if($exam->single_page == WATUPRO_PAGINATE_CUSTOM_NUMBER and (($qct % $exam->custom_per_page) == 0) ) {
		 if($qct and $mode == 'show') $output .= "</div>"; // close previous pagination div	   	 	
   	 if($mode=='show') $output .= "<div id='catDiv".$page_count."' style='display:".($qct?'none':'block')."' class='watupro_catpage'>";
   	 $output .= "<p>".sprintf(__('Page %d of %d', 'watupro'), $page_count, $num_pages)."</p>";   	 
   	 $page_count++;   	 
   }
	 
   return $output;
}

// get admin email. This overwrites the global setting with the watupro's setting.
function watupro_admin_email() {
	$admin_email = get_option('watupro_admin_email');
	if(empty($admin_email)) $admin_email = get_option('admin_email');
	
	return $admin_email;
}

// load stored page position when returning with $in_progress
// this function actually outputs javascript
function watupro_load_page($in_progress) {
	global $wpdb;
	
	if(empty($in_progress->ID)) return false;
	
	$current_page = $wpdb->get_var($wpdb->prepare("SELECT onpage_question_num FROM ".
		WATUPRO_STUDENT_ANSWERS." WHERE taking_id=%d ORDER BY `timestamp` DESC LIMIT 1", $in_progress->ID));
	if(empty($current_page) or $current_page == 1) return true;
	
	echo "WatuPRO.current_question=$current_page;\nWatuPRO.pagePreLoaded=true;\n";
}

// escapes user input to be usable in preg_replace
// seems redundant. Let's call preg_quote instead but keep this function here for a while
function watupro_preg_escape($input) {
	return preg_quote($input, '/');
	/*return str_replace(array('^', '.', '|', '(', ')', '[', ']', '*', '+', '?', '{', '}', '$', '/' ), 
		array('\^', '\.', '\|', '\(', '\)', '\[', '\]', '\*', '\+', '\?', '\{', '\}', '\$', '\/' ), $input);*/
}

// thanks to http://stackoverflow.com/questions/20025030/convert-all-types-of-smart-quotes-with-php
// and http://stackoverflow.com/questions/9027472/weird-dash-character-in-php
function watupro_convert_smart_quotes($string) { 
	$endash = html_entity_decode('&#x2013;', ENT_COMPAT, 'UTF-8');	

   $chr_map = array(
   // Windows codepage 1252
   "\xC2\x82" => "'", // U+0082 -> U+201A single low-9 quotation mark
   "\xC2\x84" => '"', // U+0084->U+201E double low-9 quotation mark
   "\xC2\x8B" => "'", // U+008B->U+2039 single left-pointing angle quotation mark
   "\xC2\x91" => "'", // U+0091->U+2018 left single quotation mark
   "\xC2\x92" => "'", // U+0092->U+2019 right single quotation mark
   "\xC2\x93" => '"', // U+0093->U+201C left double quotation mark
   "\xC2\x94" => '"', // U+0094->U+201D right double quotation mark
   "\xC2\x9B" => "'", // U+009B->U+203A single right-pointing angle quotation mark
   "\xC2\x96" => "-", // U+2013 en dash
   "\xC2\x97" => "-", // U+2014 em dash   
   $endash => '-',
   
   // Regular Unicode     // U+0022 quotation mark 
                          // U+0027 apostrophe     
   "\xC2\xAB"     => '"', // U+00AB left-pointing double angle quotation mark
   "\xC2\xBB"     => '"', // U+00BB right-pointing double angle quotation mark
   "\xE2\x80\x98" => "'", // U+2018 left single quotation mark
   "\xE2\x80\x99" => "'", // U+2019 right single quotation mark
   "\xE2\x80\x9A" => "'", // U+201A single low-9 quotation mark
   "\xE2\x80\x9B" => "'", // U+201B single high-reversed-9 quotation mark
   "\xE2\x80\x9C" => '"', // U+201C left double quotation mark
   "\xE2\x80\x9D" => '"', // U+201D right double quotation mark
   "\xE2\x80\x9E" => '"', // U+201E double low-9 quotation mark
   "\xE2\x80\x9F" => '"', // U+201F double high-reversed-9 quotation mark
   "\xE2\x80\xB9" => "'", // U+2039 single left-pointing angle quotation mark
   "\xE2\x80\xBA" => "'", // U+203A single right-pointing angle quotation mark
	);
	$chr = array_keys  ($chr_map); // but: for efficiency you should
	$rpl = array_values($chr_map); // pre-calculate these two arrays
	$string = str_replace($chr, $rpl, html_entity_decode($string, ENT_QUOTES, "UTF-8"));
	
	return $string;
}

// returns clickable URL of a published quiz if found
function watupro_exam_url($exam_id, $target="_blank") {
	global $wpdb;
	
	$exam = $wpdb->get_row($wpdb->prepare("SELECT name, ID FROM ".WATUPRO_EXAMS." WHERE ID=%d", $exam_id));
	
	$post_id = $wpdb->get_var("SELECT ID FROM {$wpdb->posts} WHERE post_status='publish'
		AND post_content LIKE '%[watupro ".$exam->ID."]%' ORDER BY ID DESC LIMIT 1");
		
	if(empty($post_id)) return stripslashes($exam->name);
	else return '<a href="'.get_permalink($post_id).'" target="'.$target.'">'.stripslashes($exam->name).'</a>';	 
}

// enqueue the localized and themed datepicker
function watupro_enqueue_datepicker() {
	$locale_url = get_option('watupro_locale_url');	
	wp_enqueue_script('jquery-ui-datepicker');	
	if(!empty($locale_url)) {
		// extract the locale
		$parts = explode("datepicker-", $locale_url);
		$sparts = explode(".js", $parts[1]);
		$locale = $sparts[0];
		wp_enqueue_script('jquery-ui-i18n-'.$locale, $locale_url, array('jquery-ui-datepicker'));
	}
	$css_url = get_option('watupro_datepicker_css');
	if(empty($css_url)) $css_url = '//ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css';
	wp_enqueue_style('jquery-style', $css_url);
}

/*
 * Matches each symbol of PHP date format standard
 * with jQuery equivalent codeword
 * @author Tristan Jahier
 * thanks to http://tristan-jahier.fr/blog/2013/08/convertir-un-format-de-date-php-en-format-de-date-jqueryui-datepicker
 */
if(!function_exists('dateformat_PHP_to_jQueryUI')) { 
	function dateformat_PHP_to_jQueryUI($php_format) {
	    $SYMBOLS_MATCHING = array(
	        // Day
	        'd' => 'dd',
	        'D' => 'D',
	        'j' => 'd',
	        'l' => 'DD',
	        'N' => '',
	        'S' => '',
	        'w' => '',
	        'z' => 'o',
	        // Week
	        'W' => '',
	        // Month
	        'F' => 'MM',
	        'm' => 'mm',
	        'M' => 'M',
	        'n' => 'm',
	        't' => '',
	        // Year
	        'L' => '',
	        'o' => '',
	        'Y' => 'yy',
	        'y' => 'y',
	        // Time
	        'a' => '',
	        'A' => '',
	        'B' => '',
	        'g' => '',
	        'G' => '',
	        'h' => '',
	        'H' => '',
	        'i' => '',
	        's' => '',
	        'u' => ''
	    );
	    $jqueryui_format = "";
	    $escaping = false;
	    for($i = 0; $i < strlen($php_format); $i++)
	    {
	        $char = $php_format[$i];
	        if($char === '\\') // PHP date format escaping character
	        {
	            $i++;
	            if($escaping) $jqueryui_format .= $php_format[$i];
	            else $jqueryui_format .= '\'' . $php_format[$i];
	            $escaping = true;
	        }
	        else
	        {
	            if($escaping) { $jqueryui_format .= "'"; $escaping = false; }
	            if(isset($SYMBOLS_MATCHING[$char]))
	                $jqueryui_format .= $SYMBOLS_MATCHING[$char];
	            else
	                $jqueryui_format .= $char;
	        }
	    }
	    return $jqueryui_format;
	}
}