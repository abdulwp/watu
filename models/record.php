<?php
// object to handle "takings" - stored records of taken exams
class WTPRecord {
<<<<<<< HEAD
	 function export($takings, $exam) {
			global $wpdb;	 	
	 		$newline=watupro_define_newline();
			$rows=array();
=======
	 function export($takings, $exam = null) {
			global $wpdb;	 	
	 		$newline = watupro_define_newline();
			$rows = array();
>>>>>>> branch/6.7.2
			
			$delim = get_option('watupro_csv_delim');
			if(empty($delim) or !in_array($delim, array(",", "tab"))) $delim = ",";
			if($delim == 'tab') $delim = "\t";
			$quote = get_option('watupro_csv_quotes');
			if(empty($quote)) $quote = ''; 
			else $quote = '"';
			
<<<<<<< HEAD
=======
			$dateformat = get_option('date_format');	
			$timeformat = get_option('time_format');
			
>>>>>>> branch/6.7.2
			// add all first names and last names to match them when exporting
			$uids = array(0);
			foreach($takings as $taking) {
				if(!empty($taking->user_id)) $uids[] = $taking->user_id;
			} 
			$uids = array_unique($uids);
			$first_names = $wpdb->get_results("SELECT meta_value, user_id FROM {$wpdb->usermeta}
				WHERE meta_key = 'first_name' AND user_id IN (".implode(",", $uids).")");
			$last_names = $wpdb->get_results("SELECT meta_value, user_id FROM {$wpdb->usermeta}
				WHERE meta_key = 'last_name' AND user_id IN (".implode(",", $uids).")");
<<<<<<< HEAD
=======
				
			$tids = array(0);	
>>>>>>> branch/6.7.2
			foreach($takings as $cnt=>$taking) {
				foreach($first_names as $first_name) {
					if($first_name->user_id == $taking->user_id) $takings[$cnt]->first_name = $first_name->meta_value;
				}
				
				foreach($last_names as $last_name) {
					if($last_name->user_id == $taking->user_id) $takings[$cnt]->last_name = $last_name->meta_value;
				}
<<<<<<< HEAD
			}	// end adding first / last names to takings	 
			
			if(empty($_GET['details'])) $rows[]=__('First name', 'watupro').$delim.__('Last name', 'watupro').$delim.__('Username and details', 'watupro').
				$delim.__('Email', 'watupro').$delim.__('IP', 'watupro').	$delim.__('Date', 'watupro').$delim.__('Points', 'watupro').
				$delim.__('Percent correct', 'watupro').$delim.__('Grade', 'watupro');
			else {
				// exports with questions and answers
				$questions = $wpdb->get_results($wpdb->prepare("SELECT tQ.*, tC.exclude_from_reports 
					FROM ".WATUPRO_QUESTIONS." tQ LEFT JOIN ".WATUPRO_QCATS." tC ON tC.ID = tQ.cat_id
					WHERE tQ.exam_id=%d ORDER BY tQ.ID", $exam->ID));
					
					$titlerow =__('First name', 'watupro').$delim.__('Last name', 'watupro').$delim.__('Username', 'watupro').$delim.__('Email', 'watupro').$delim.__('IP', 'watupro').$delim.__('Date', 'watupro') . $delim;
=======
				$tids[] = $taking->ID;
			}	// end adding first / last names to takings	 
			
			// if the quiz requests contact fields we have to add columns for them
			$contact_field_columns = ''; 
			if(!empty($exam->ID)) {
				$advanced_settings = unserialize(stripslashes($exam->advanced_settings));
		  	 	if(!empty($advanced_settings['contact_fields']['phone'])) {
		  	 		$phone_label = stripslashes(rawurldecode($advanced_settings['contact_fields']['phone_label']));
		  	 		$contact_field_columns .= $delim . $phone_label;
		  	 	}
		  	 	if(!empty($advanced_settings['contact_fields']['company'])) {
		  	 		$company_label = stripslashes(rawurldecode($advanced_settings['contact_fields']['company_label']));
		  	 		$contact_field_columns .= $delim . $company_label;	  	 		
		  	 	}
		  	 	if(!empty($advanced_settings['contact_fields']['field1'])) {
		  	 		$field1_label = stripslashes(rawurldecode($advanced_settings['contact_fields']['field1_label']));
		  	 		$contact_field_columns .= $delim . $field1_label;	  	 		
		  	 	}
		  	 	if(!empty($advanced_settings['contact_fields']['field2'])) {
		  	 		$field2_label = stripslashes(rawurldecode($advanced_settings['contact_fields']['field2_label']));
		  	 		$contact_field_columns .= $delim . $field2_label;	  	 		
		  	 	}
			}
			
			
			if(empty($_GET['details']) or empty($exam->ID)) {
				$titlerow = '';

				if(empty($exam->ID)) $titlerow .= ucfirst(WATUPRO_QUIZ_WORD).$delim;				
				  
				$titlerow .=__('ID', 'watupro').$delim.__('First name', 'watupro').$delim.__('Last name', 'watupro').$delim.__('Username', 'watupro').
				$delim.__('Email', 'watupro').$delim.__('User ID', 'watupro').$delim.__('IP', 'watupro'). $contact_field_columns . $delim.__('Date', 'watupro').$delim.__('Points', 'watupro').
				$delim.__('Percent correct', 'watupro').$delim.__('Percent from max. points', 'watupro').$delim.__('Correct answers', 'watupro').
				$delim.__('Wrong answers', 'watupro').	$delim.__('Unanswered', 'watupro').$delim.__('Grade', 'watupro').$delim.__('Grade ID', 'watupro').$delim.__('Time spent', 'watupro');
				
				$titlerow = apply_filters('watupro_result_export_title_row', $titlerow);
				
				$rows[] = $titlerow;
			}
			else {
				// exports with questions and answers
				$q_exam_id = $exam->reuse_questions_from ? $exam->reuse_questions_from : $exam->ID;
				$questions = $wpdb->get_results("SELECT tQ.*, tC.exclude_from_reports 
					FROM ".WATUPRO_QUESTIONS." tQ LEFT JOIN ".WATUPRO_QCATS." tC ON tC.ID = tQ.cat_id
					WHERE tQ.exam_id IN ($q_exam_id) ORDER BY tQ.sort_order, tQ.ID");
		
					$titlerow = __('ID', 'watupro').$delim.__('First name', 'watupro').$delim.__('Last name', 'watupro').$delim.__('Username', 'watupro').$delim.__('Email', 'watupro').$delim
						.__('User ID', 'watupro').$delim.__('IP', 'watupro'). $contact_field_columns . $delim.__('Date', 'watupro') . $delim;
>>>>>>> branch/6.7.2
					foreach($questions as $question) {
						  if($question->exclude_from_reports) continue;
						 // strip tags and remove semicolon to protect the CSV sanity						 
						 $question_txt = strip_tags(str_replace("\t","   ",$question->question));
						 $question_txt = apply_filters('watupro_qtranslate', $question_txt);						 
						 $question_txt = str_replace("\n", " ", $question_txt);
						 $question_txt = str_replace("\r", " ", $question_txt);
						 $question_txt = stripslashes($question_txt);
						 if($quote) $question_txt = str_replace('"',"'", $question_txt);
						 $titlerow .= $quote.$question_txt.$quote.$delim;
					}
<<<<<<< HEAD
					$titlerow .= __('Points', 'watupro').$delim.__('% Correct', 'watupro').$delim.__('Grade', 'watupro');		
=======
					$titlerow .= __('Points', 'watupro').$delim.__('Percent correct', 'watupro').$delim.__('Percent from max points', 'watupro').
					$delim.__('Correct answers', 'watupro').$delim.__('Wrong answers', 'watupro').
					$delim.__('Unanswered', 'watupro').$delim.__('Grade', 'watupro').$delim.__('Grade ID', 'watupro').$delim.__('Time spent', 'watupro');		
				
					$titlerow = apply_filters('watupro_result_export_title_row', $titlerow);
>>>>>>> branch/6.7.2
					$rows[] = $titlerow;		
					
					// we also have to get full details so they can be matched below
					$details = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".WATUPRO_STUDENT_ANSWERS."
<<<<<<< HEAD
						WHERE exam_id=%d", $exam->ID));	
			}
			
			foreach($takings as $taking) {
					$taking_email = ($taking->user_id) ? $taking->user_email : $taking->email;
					$taking_name = ($taking->user_id ? $taking->user_login : (empty($taking->name) ? "N/A" : stripslashes($taking->name))); 
					
					// add contact data if any
					if(!empty($taking->contact_data)) $taking_name .= ' - '.$taking->contact_data;					
					
				   $row = $quote . (!empty($taking->first_name) ? $taking->first_name : "N/A") . $quote . $delim . 
				   $quote . (!empty($taking->last_name) ? $taking->last_name : "N/A") . $quote .  $delim .
					$quote . $taking_name . $quote .$delim.
					($taking_email?$taking_email:"N/A").$delim.
					$quote . $taking->ip. $quote . $delim. $quote . date(get_option('date_format'), strtotime($taking->date)). $quote . $delim;
=======
						WHERE exam_id=%d AND taking_id IN (".implode(',', $tids).")", $exam->ID));
								
			}
			
			foreach($takings as $taking) {
					$taking_email = empty($taking->email) ? $taking->user_email : $taking->email;					
					$taking_name = ($taking->user_id ? $taking->user_login : (empty($taking->name) ? "N/A" : stripslashes($taking->name))); 
										
					// add contact data if any
					// if(!empty($taking->contact_data)) $taking_name .= ' - '.$taking->contact_data;					
					$row = $quote.$taking->ID.$quote.$delim;
					
					if(empty($exam->ID)) $row .= $quote . stripslashes(apply_filters('watupro_qtranslate', $taking->exam_name)) . $quote . $delim;					
					
				   $row .= $quote . (!empty($taking->first_name) ? $taking->first_name : (empty($taking->name) ? "N/A" : $taking->name) ) . $quote . $delim . 
				   $quote . (!empty($taking->last_name) ? $taking->last_name : "N/A") . $quote .  $delim .
					$quote . $taking_name . $quote .$delim.
					($taking_email ? $taking_email : "N/A").$delim.
					($taking->user_id ? $taking->user_id : "N/A").$delim.
					$quote . $taking->ip. $quote;
					
					// add contact fields if any					
					if(!empty($advanced_settings['contact_fields']['phone'])) $row .= $delim . $quote . stripslashes($taking->field_phone) . $quote;
			  	 	if(!empty($advanced_settings['contact_fields']['company'])) $row .= $delim . $quote . stripslashes($taking->field_company) . $quote;
			  	 	if(!empty($advanced_settings['contact_fields']['field1'])) $row .= $delim . $quote . stripslashes($taking->custom_field1) . $quote;
			  	 	if(!empty($advanced_settings['contact_fields']['field2'])) $row .= $delim . $quote . stripslashes($taking->custom_field2) . $quote;
			  	 	
			  	 	$datetime = date_i18n($dateformat.' '.$timeformat, strtotime(($taking->end_time == '2000-01-01 00:00:00') ? $taking->date : $taking->end_time));
					$row .= $delim. $quote . $datetime. $quote . $delim;
>>>>>>> branch/6.7.2
					
			  if(!empty($_GET['details'])) {
			  	 foreach($questions as $question) {
			  	 		if($question->exclude_from_reports) continue;
			  	 		
			  	 		$answer = $feedback = "";
			  	 		foreach($details as $detail) {
		  	 			 if($detail->taking_id==$taking->ID and $detail->question_id==$question->ID) {		
								// handle matrix better
								if($question->answer_type == 'matrix' or $question->answer_type == 'nmatrix') {
									$detail->answer = str_replace('</td><td>', ' = ', $detail->answer);
									$detail->answer = str_replace('</tr><tr>', '; ', $detail->answer);
								}			  	 			 
		  	 			 	  	 			 		
		  	 			 		$answer = strip_tags(str_replace("\t","   ",$detail->answer));
		  	 			 		$answer = apply_filters('watupro_qtranslate', $answer);
		  	 			 		$answer = str_replace("\n", " ", $answer);
		  	 			 		$answer = str_replace("\r", " ", $answer);
		  	 			 		if($quote) $answer = str_replace('"',"'", $answer);
								$answer = stripslashes($answer);
								
								// question accepts user feedback?
								if($question->accept_feedback and !empty($detail->feedback)) {									
									$feedback = strip_tags(str_replace("\t","   ",$detail->feedback));
									$feedback = apply_filters('watupro_qtranslate', $feedback);
				  	 			 	$feedback = str_replace("\n", " ", $feedback);
				  	 			 	$feedback = str_replace("\r", " ", $feedback);
				  	 			 	if($quote) $feedback = str_replace('"',"'", $feedback);
									$feedback = stripslashes($feedback);
									$answer .= "; ".stripslashes($question->feedback_label)." ".$feedback;
								}	// end if accepts feedback
		  	 			 	} // end if detail matches taking and question
						}	// end foreach answer				
							
						$row .= $quote.$answer.$quote.$delim;
			  	 } // end foreach question
			  }					
<<<<<<< HEAD
					
=======

>>>>>>> branch/6.7.2
				$taking_result = strip_tags(str_replace("\t","   ",$taking->result));
				$taking_result = apply_filters('watupro_qtranslate', $taking_result);
			  	$taking_result = str_replace("\n", " ", $taking_result);
			  	$taking_result = str_replace("\r", " ", $taking_result);
			  	if($quote) $taking_result = str_replace('"',"'", $taking_result);
				$taking_result = stripslashes($taking_result);	
					
<<<<<<< HEAD
				$row .=	$taking->points.$delim . $taking->percent_correct.$delim .$quote.$taking_result.$quote;
					
				$rows[] = $row;	
			}
=======
				$time_spent = WTPRecord :: time_spent_human(WTPRecord :: time_spent($taking));
									
				$row .=	$taking->points.$delim . $taking->percent_correct.$delim .$taking->percent_of_max.$delim. $taking->num_correct. 
				$delim . $taking->num_wrong. $delim . $taking->num_empty. $delim .$quote.$taking_result.$quote.$delim.
				$quote.$taking->grade_id.$quote.$delim.$quote.$time_spent.$quote;
				
				$row = apply_filters('watupro_result_export_row', $row, $taking);	
				$rows[] = $row;	
			} // end foreach taking
>>>>>>> branch/6.7.2
			
			$csv = implode($newline,$rows);
			
			// credit to http://yoast.com/wordpress/users-to-csv/	
			$now = gmdate('D, d M Y H:i:s') . ' GMT';
		
			header('Content-Type: ' . watupro_get_mime_type());
			header('Expires: ' . $now);
<<<<<<< HEAD
			header('Content-Disposition: attachment; filename="quiz-'.$exam->ID.'.csv"');
=======
			if(empty($exam->ID)) header('Content-Disposition: attachment; filename="all-results.csv"'); 
			else header('Content-Disposition: attachment; filename="quiz-'.$exam->ID.'.csv"');
>>>>>>> branch/6.7.2
			header('Pragma: no-cache');
			echo $csv;
			exit;
	 }
	 
	 // helper to calculate time spent in exam
	static function time_spent($taking) {
		list($date, $time) = explode(" ", $taking->start_time);
		list($y, $m, $d) = explode("-", $date);
		list($h, $min, $s) = explode(":", $time);		 		
 		$start_time = @mktime($h, $min, $s, $m, $d, $y);
 		
 		list($date, $time) = explode(" ", $taking->end_time);
 		list($y, $m, $d) = explode("-", $date);
 		list($h, $min, $s) = explode(":", $time);
 		$end_time = @mktime($h, $min, $s, $m, $d, $y);
 		
 		$diff = $end_time - $start_time;
 		
 		if($diff < 0) $diff = 0;
 		
 		return $diff;
	} 
	
	static function time_spent_human($time_spent) {
<<<<<<< HEAD
		$time_spent = ($time_spent > 600) ? gmdate("H:i", $time_spent) : gmdate("H:i:s", $time_spent);
		return $time_spent;
	}	
}
=======
		$time_spent = gmdate("H:i:s", $time_spent);
		return $time_spent;
	}	
	
	// export My Quizzes completed records (GDRP)
	static function export_my_exams($my_exams, $takings, $num_taken) {
		global $wpdb;
		$newline = watupro_define_newline();
		$rows = array();
		
		$delim = get_option('watupro_csv_delim');
		if(empty($delim) or !in_array($delim, array(",", "tab"))) $delim = ",";
		if($delim == 'tab') $delim = "\t";
		$quote = get_option('watupro_csv_quotes');
		if(empty($quote)) $quote = ''; 
		else $quote = '"';		
		
		$titlerow = sprintf(__('%s Title', 'watupro'), ucfirst(WATUPRO_QUIZ_WORD)).$delim.__('Points', 'watupro')
			. $delim . __('% Correct', 'watupro') . $delim . __('Result/Grade', 'watupro') . $delim . __('Details', 'watupro');
		$rows[] = $titlerow;	
		
		// reorder previous takings in the same array
		$final_exams = array();
		foreach($my_exams as $exam) {
			$final_exams[] = $exam;
			if($num_taken > 1 and empty($disallow_results)) {
				foreach($exam->takings as $ttt=>$taking) {
					$new_exam = array('taking' => $taking);
					$final_exams[] = $new_exam;
				}
			}
		} // end rearranging the array
		$my_exams = $final_exams;
		
		foreach($my_exams as $exam) {
			$row = "";
			if(empty($exam->is_taken)) continue;
			$exam->delay_results = WTPUser :: delay_results($exam);
			if(!current_user_can(WATUPRO_MANAGE_CAPS) and $exam->delay_results and current_time('timestamp') < strtotime($exam->delay_results_date)) {
				$exam->taking->points = __('n/a', 'watupro');
				$exam->taking->percent_correct = __('n/a', 'watupro');
				$exam->taking->result = apply_filters('watupro_content', stripslashes($exam->delay_results_content));
				$disallow_results = true;
			}
			$num_takings = count($exam->takings);
			
			$exam->taking->details = str_replace($newline, ' ', $exam->taking->details);
			$exam->taking->details = str_replace($quote, '&quot;', $exam->taking->details);
			
			$row .= $quote . stripslashes(apply_filters('watupro_qtranslate', $exam->name)) . $quote . $delim . $exam->taking->points 
				. $delim . sprintf(__('%d%%', 'watupro'), $exam->taking->percent_correct) . $delim . $quote 
				. apply_filters('watupro_content', $exam->taking->result) . $quote . $delim . $quote 
				. apply_filters('watupro_content', $exam->taking->details) . $quote;
			$rows[] = $row;	
		} // end foreach exam
		
		$csv = implode($newline,$rows);
					
		// credit to http://yoast.com/wordpress/users-to-csv/	
		$now = gmdate('D, d M Y H:i:s') . ' GMT';	
		header('Content-Type: ' . watupro_get_mime_type());
		header('Expires: ' . $now);		 
		header('Content-Disposition: attachment; filename="my_results.csv"');
		header('Pragma: no-cache');
		echo $csv;
		exit;
	} // end exporting of self user data
	
	/**
	* Import taking records. Will import only in the taken_exams table (no individual answer details).
	* When the ID column contains an existing ID, the record will be updated, otherwise inserted.
	* Inserting a new record will fire the watupro_exam_submitted hook.
	**/
	public static function import(mixed $quiz) : int {
        global $wpdb;
        
        if(empty($_FILES['csv']['name'])) wp_die(__('Please upload file', 'watupro'));
        
        $advanced_settings = unserialize(stripslashes($quiz->advanced_settings));
        
        $delimiter = sanitize_text_field($_POST['delimiter']);
        if($delimiter=="tab") $delimiter="\t";
        
        $num_records = 0;
        $titles = [];
        
        // check for non UTF-8 encoding
        $content = file_get_contents($_FILES['csv']['tmp_name']);
		if(!mb_detect_encoding($content, 'UTF-8', true)) $non_utf8_error = true;
		
		$row = 0;
		if (($handle = fopen($_FILES['csv']['tmp_name'], "r")) !== FALSE) {
                if(empty($_POST['import_fails'])) {
                    while (($data = fgetcsv($handle, 10000, $delimiter)) !== FALSE) {	    	  
                            $row++;	
                            //echo "CSV LINE $row";
                        if(empty($data)) continue;			  
                        if($row === 1) {
                            $titles = $data;
                            continue;
                        }
                                
                        if( self :: import_taking($data, $advanced_settings, $titles, $quiz->ID) )  $num_records++;     
                    } // end while
                } else {
                // the customer says that import fails - let's try the handmade import function
                while(($csv_line = fgets($handle, 10000)) !== FALSE) {
                    $row++;				
                    //echo "HERE $row<br>";		 		
                    if(empty($csv_line)) continue;			  			  
                    
                    $data = watupro_parse_csv_line($csv_line);	
                    if($row === 1) $titles = $data;
                    
                    if( self :: import_taking($data, $advanced_settings, $titles, $quiz->ID) )  $num_records++;
                } // end while
            }	// end alternate CSV parsing
            $result = true;
        } // end if $handle
        
        return $num_records;
	} // end import
	
	// imports a single taking record
	private static function import_taking(iterable $data, iterable $advanced_settings, iterable $titles, int $quiz_id) : bool {
        global $wpdb;
        
        // for easier array_search
        $titles = array_map('strtolower', $titles);
        
        $id = absint($data[0] ?? 0);
        $first_name = sanitize_text_field($data[1]);
        if($first_name == 'N/A') $first_name = '';
        $last_name = sanitize_text_field($data[2]);
        if($last_name == 'N/A') $last_name = '';
        $name = ($first_name or $last_name) ? trim($first_name.' '.$last_name) : '';
        $username = sanitize_text_field($data[3]);
        $email = sanitize_text_field($data[4]);
        if($email == 'N/A') $email = '';
        $user_id = is_numeric($data[5]) ? absint($data[5]) : 0;
               
        // contact fields may only be 4        
        $contact_sql = '';
        
        // contact fields will be matched only if the labels match        
        if(!empty($advanced_settings['contact_fields']['phone'])) {
            $phone_label = stripslashes(rawurldecode($advanced_settings['contact_fields']['phone_label']));
            if($i = array_search(strtolower($phone_label), $titles)) {
                $contact_sql .= $wpdb->prepare(', field_phone=%s', sanitize_text_field($data[$i] ?? ''));
            }
        }
        if(!empty($advanced_settings['contact_fields']['company'])) {
            $company_label = stripslashes(rawurldecode($advanced_settings['contact_fields']['company_label']));
            if($i = array_search(strtolower($company_label), $titles)) {
                $contact_sql .= $wpdb->prepare(', field_company=%s', sanitize_text_field($data[$i] ?? ''));
            }
        }
        if(!empty($advanced_settings['contact_fields']['field1'])) {
            $field1_label = stripslashes(rawurldecode($advanced_settings['contact_fields']['field1_label']));
            if($i = array_search(strtolower($field1_label), $titles)) {
                $contact_sql .= $wpdb->prepare(', custom_field1=%s', sanitize_text_field($data[$i] ?? ''));
            }	 		
        }
        if(!empty($advanced_settings['contact_fields']['field2'])) {
            $field2_label = stripslashes(rawurldecode($advanced_settings['contact_fields']['field2_label']));
            if($i = array_search(strtolower($field2_label), $titles)) {
                $contact_sql .= $wpdb->prepare(', custom_field2=%s', sanitize_text_field($data[$i] ?? ''));
            }	  	 		
        }
        
        // other fields
        $date_pos = array_search(strtolower(__('Date', 'watupro')), $titles);
        $date = date('Y-m-d', strtotime(sanitize_text_field($data[$date_pos])));  
        $end_time = date('Y-m-d H:i:s', strtotime(sanitize_text_field($data[$date_pos])));
        $points_pos = array_search(strtolower(__('Points', 'watupro')), $titles);
        $points = floatval($data[$points_pos]);
        $percent_pos = array_search(strtolower(__('Percent correct', 'watupro')), $titles);
        $percent = absint($data[$percent_pos]);
        $percent_points_pos = array_search(strtolower(__('Percent from max. points', 'watupro')), $titles);
        $percent_points = absint($data[$percent_points_pos]);
        $num_correct_pos = array_search(strtolower(__('Correct answers', 'watupro')), $titles);
        $num_correct = absint($data[$num_correct_pos]);
        $num_wrong_pos = array_search(strtolower(__('Wrong answers', 'watupro')), $titles);
        $num_wrong = absint($data[$num_wrong_pos]);
        $num_empty_pos = array_search(strtolower(__('Unanswered', 'watupro')), $titles);
        $num_empty = absint($data[$num_empty_pos]);
        $grade_pos = array_search(strtolower(__('Grade', 'watupro')), $titles);
        $grade = sanitize_text_field($data[$grade_pos]);     
        $grade_id_pos = array_search(strtolower(__('Grade ID', 'watupro')), $titles);
        $grade_id = absint($data[$grade_id_pos]);
        
        // if $id > 0 check that it actually exists, otherwise unset it
        if($id > 0) {
            $id = $wpdb->get_var($wpdb->prepare("SELECT ID FROM ".WATUPRO_TAKEN_EXAMS."
                WHERE ID=%d AND exam_id=%d", $id, $quiz_id));
        }
        
        // now insert or update
        if($id > 0) {
            $wpdb->query($wpdb->prepare("UPDATE ".WATUPRO_TAKEN_EXAMS." SET 
                name=%s, email=%s, user_id=%d, $contact_sql
                date=%s, points=%f, percent_correct=%d, percent_points=%d, num_correct=%d,
                num_wrong=%d, num_empty=%d, result=%s, grade_id=%d WHERE id=%d AND exam_id=%d", 
                $name, $email, $user_id, $date,
                $points, $percent, $percent_points, $num_correct, $num_wrong, $num_empty, $grade, $grade_id, 
                $id, $quiz_id));

                
            if(!empty($_POST['fire_hooks'])) do_action('watupro_completed_exam_edited', $id);
        }
        else {
            
            $wpdb->query($wpdb->prepare("INSERT INTO ".WATUPRO_TAKEN_EXAMS." SET 
                exam_id=%d, name=%s, email=%s, user_id=%d, $contact_sql
                date=%s, points=%f, percent_correct=%d, percent_points=%d, num_correct=%d,
                num_wrong=%d, num_empty=%d, result=%s, grade_id=%d, end_time=%s", $quiz_id, $name, $email, $user_id, $date,
                $points, $percent, $percent_points, $num_correct, $num_wrong, $num_empty, $grade, $grade_id, $end_time
                ));
                
            $taking_id = $wpdb->insert_id;    
            if(!empty($_POST['fire_hooks'])) do_action('watupro_completed_exam', $taking_id);
        }
          
        return true;
	} // end import_taking
}
>>>>>>> branch/6.7.2
