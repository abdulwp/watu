<?php
function watupro_export_questions() {
	global $wpdb;
	$newline=watupro_define_newline();
	
	// select questions
	$questions=$wpdb->get_results($wpdb->prepare("SELECT tQ.*, tC.name as category 
		FROM ".WATUPRO_QUESTIONS." tQ LEFT JOIN ".WATUPRO_QCATS." tC ON tC.ID=tQ.cat_id 
		WHERE tQ.exam_id=%d ORDER BY tQ.sort_order, tQ.ID", $_GET['exam_id']), ARRAY_A);		
		
	$qids=array(0);
	foreach($questions as $question) $qids[]=$question['ID'];
	$qid_sql=implode(",", $qids);
		
	// select all answers in the exam
	$answers=$wpdb->get_results("SELECT * FROM ".WATUPRO_ANSWERS." WHERE question_id IN ($qid_sql)");
	
	// match answers to questions
	foreach($questions as $cnt=>$question) {
		$questions[$cnt]['answers']=array();
		foreach($answers as $answer) {
			if($answer->question_id==$question['ID']) $questions[$cnt]['answers'][]=$answer;
		}
	}
	
	// run last query to define the max number of answers
	$num_ans=$wpdb->get_row("SELECT COUNT(ID) as num_answers FROM ".WATUPRO_ANSWERS." WHERE question_id IN ($qid_sql)
			GROUP BY question_id ORDER BY num_answers DESC");
			
	$rows=array();
	
	$delim = get_option('watupro_csv_delim');
	if(empty($delim) or !in_array($delim, array(",", "tab"))) $delim = ",";
	if($delim == 'tab') $delim = "\t";
	$quote = get_option('watupro_csv_quotes');
	if(empty($quote)) $quote = ''; 
	else $quote = '"';
	
	if(empty($_GET['copy'])) {
		$titlerow="Question ID".$delim."Question".$delim."Answer Type".$delim."Order".$delim."Category".$delim."Explanation/Feedback".$delim."Required?".$delim."Correct answer condition".$delim."Fill the gap/sorting points".$delim."Sorting Answers".$delim."Max selections".$delim."Is Inactive?".$delim."Is Survey?".$delim."Elaborate answer feedback";
		for($i=1;$i<=$num_ans->num_answers;$i++) $titlerow.="".$delim."Answer ID".$delim."Answer".$delim."Points";
	}
	else {
		$titlerow="Question".$delim."Answer Type".$delim."Order".$delim."Category".$delim."Explanation/Feedback".$delim."Required?".$delim."Correct answer condition".$delim."Fill the gap/sorting points".$delim."Sorting Answers".$delim."Max selections".$delim."Is Inactive?".$delim."Is survey?".$delim."Elaborate answer feedback";
		
		// non-legacy export
		if(empty($GET['legacy'])) {
			$titlerow .= "".$delim."Open end mode".$delim."tags".$delim."Open-end question display style".$delim."Exclude from showing on the final screen? (0 or 1)".$delim."Hints".$delim."Display in compact format? (0 or 1)".$delim."Round the points to the closest decimal? (0 or 1)".$delim."Is this an important question? (0 or 100)"
			.$delim."Difficulty level".$delim."Penalty for non-answering".$delim."Multiple gaps as drop-downs";
		}		
		
		if(empty($GET['legacy'])) for($i=1;$i<=$num_ans->num_answers;$i++) $titlerow.=$delim."Answer".$delim."Is Correct?".$delim."Points";
		else for($i=1;$i<=$num_ans->num_answers;$i++) $titlerow.=$delim."Answer".$delim."Points";
	}		
	
	$rows[]=$titlerow;
		
	foreach($questions as $question) {
		// replace tabulators and quotes to avoid issues with excel
		$question['question'] = str_replace("\t", "   ", $question['question']);
		$question['question'] = str_replace('"', "'", $question['question']);
		$question['question'] = watupro_nl2br($question['question']);		
		$question['explain_answer'] = str_replace("\t", "   ", $question['explain_answer']);
		$question['explain_answer'] = str_replace('"', "'", $question['explain_answer']);
		$question['explain_answer'] = watupro_nl2br($question['explain_answer']);
		$question['explain_answer'] = str_replace("\n", "", $question['explain_answer']);
		$question['explain_answer'] = str_replace("\r", "", $question['explain_answer']);
		$question['hints'] = str_replace("\t", "   ", $question['hints']);
		$question['hints'] = str_replace('"', "'", $question['hints']);
		$question['hints'] = watupro_nl2br($question['hints']);		
		$question['sorting_answers'] = str_replace('"', "'", $question['sorting_answers']);
		$question['sorting_answers'] = str_replace("\n", "|||", $question['sorting_answers']);
		$question['sorting_answers'] = str_replace("\r", "|||", $question['sorting_answers']);
		
		// handle true/false questions
		if($question['answer_type'] == 'radio' and $question['truefalse']) $question['answer_type'] = "true/false";
		
		$row = "";		
		if(empty($_GET['copy'])) $row .= $question['ID'].$delim;
		$row .= $quote.stripslashes($question['question']).$quote.$delim.$question['answer_type'].$delim.$question['sort_order'].
			$delim.$question['category'].$delim.$quote.stripslashes($question['explain_answer']).$quote.$delim.$question['is_required'].
			$delim.$question['correct_condition'].$delim.$question['correct_gap_points']."/".$question['incorrect_gap_points'].
			$delim.$quote.stripslashes($question['sorting_answers']).$quote.$delim.$question['max_selections'].$delim.$question['is_inactive'].
			$delim.$question['is_survey'].$delim.$question['elaborate_explanation'];
			
		// new export - adds the new fields
		if(empty($_GET['legacy'])) {
			$row .= $delim.$question['open_end_mode'].$delim.$quote.$question['tags'].$quote.$delim.$question['open_end_display'].
						$delim.$question['exclude_on_final_screen'].$delim.$quote.$question['hints'].$quote.$delim.$question['compact_format'].
						$delim.$question['round_points'].$delim.$question['importance'].$delim.$question['difficulty_level'].
						$delim.$question['unanswered_penalty'].$delim.$question['gaps_as_dropdowns'];
		}	
		
		foreach($question['answers'] as $answer) {
			if(empty($_GET['copy'])) $row .= $delim.$answer->ID;
			$row .= $delim.$quote.stripslashes($answer->answer).$quote.$delim.$answer->correct.$delim.$answer->point;
		}		
		
		$row = str_replace("\n", "", $row);
		$row = str_replace("\r", "", $row);				
		$rows[]=$row;
	}
	
	$csv=implode($newline,$rows);
	
	// credit to http://yoast.com/wordpress/users-to-csv/	
	$now = gmdate('D, d M Y H:i:s') . ' GMT';
	
	if(empty($_GET['copy'])) $filename = 'exam-'.$_GET['exam_id'].'-questions-edit.csv';
	else $filename = 'exam-'.$_GET['exam_id'].'-questions.csv';

	header('Content-Type: ' . watupro_get_mime_type());
	header('Expires: ' . $now);
	header('Content-Disposition: attachment; filename="'.$filename.'"');
	header('Pragma: no-cache');
	echo $csv;
	exit;
}


function watupro_import_questions() {
	global $wpdb;
	
	$row = 0;
	ini_set("auto_detect_line_endings", true);
	if (($handle = fopen($_FILES['csv']['tmp_name'], "r")) !== FALSE) {
		
		 // select all current questions and answers in the exam. it's required to make fast checks
		 // if a given ID exists or not		
		 $questions=$wpdb->get_results($wpdb->prepare("SELECT * FROM ".WATUPRO_QUESTIONS." WHERE exam_id=%d", $_GET['quiz']));
		 $qids=array(0);
		 foreach($questions as $question) $qids[]=$question->ID;
		 $qid_sql=implode(",", $qids);
			
		 // select all answers in the exam
		 $answers=$wpdb->get_results("SELECT * FROM ".WATUPRO_ANSWERS." WHERE question_id IN ($qid_sql)");
		 		
		 // select all categories so we can see if given one exists or not
		 $cats=$wpdb->get_results("SELECT * FROM ".WATUPRO_QCATS);
		 		
		 $delimiter=$_POST['delimiter'];
		 if($delimiter=="tab") $delimiter="\t";
		 $csvtype = 'short';
		 
		 if(empty($_POST['import_fails'])) {		
		    while (($data = fgetcsv($handle, 10000, $delimiter)) !== FALSE) {	    	  
		    	  $row++;	
		        if(empty($data)) continue;			  			  
		        if(!empty($_POST['skip_title_row']) and $row == 1) continue;	        
		        watupro_import_question($data, $cats);	        
		    } // end while
		 } else {
		 	// the customer says that import fails - let's try the handmade import function
		 	while(($csv_line = fgets($handle, 10000)) !== FALSE) {
		 		$row++;
		 		if(empty($csv_line)) continue;			  			  
		      if(!empty($_POST['skip_title_row']) and $row == 1) continue;
		      $data = watupro_parse_csv_line($csv_line);		         
		      watupro_import_question($data, $cats);	      
		 	} // end while
		 }
	    
	    fclose($handle);
	}	// end open file
	
	wp_redirect("admin.php?page=watupro_questions&quiz=$_GET[quiz]");
}

// imports a single question when passed $data array of cells
function watupro_import_question($data, &$cats) {
	global $wpdb;
	
	if($_POST['file_type']=='new') {
  		$cat_id=WTPCategory::discover(@$data[3], $cats);	
  		
  		// handle true/false subtype
  		$truefalse = 0;
  		if($data[1] == 'true/false') {
  			$truefalse = 1;
  			$data[1] = 'radio';
  		}
					  	
  		// only new questions and answers
  		$stripfrom = 6;
		$question_array = array("content"=>$data[0], "answer_type"=>$data[1], "sort_order"=>$data[2],
  				"cat_id"=>$cat_id, "explain_answer"=>$data[4], "is_required"=>$data[5], "quiz"=>$_GET['quiz']);	
  			
  		$stripfrom = 13;
  		$question_array['correct_condition'] = $data[6];
  		$gapdata = explode("/", $data[7]); // handle both gap & sort
  		$question_array['correct_gap_points'] = $question_array['correct_sort_points']= @$gapdata[0];
		$question_array['incorrect_gap_points'] = $question_array['incorrect_sort_points'] = @$gapdata[1];
		$question_array['sorting_answers'] = $data[8];
		$question_array['max_selections'] = $data[9];
		$question_array['is_inactive'] = $data[10];
		$question_array['is_survey'] = $data[11];
		$question_array['elaborate_explanation'] = $data[12];
		$question_array['truefalse'] = $truefalse;	
		$question_array['feedback_label'] = ''; // temp as it's not yet included in export
		
		// sorting answers may contain ||| or |||||| for new lines separator
		$question_array['sorting_answers'] = str_replace('||||||', "\n", $question_array['sorting_answers']);
		$question_array['sorting_answers'] = str_replace('|||', "\n", $question_array['sorting_answers']);
  		
		$qid = WTPQuestion::add($question_array);  		
  					  		
  		// extract answers
  		$data=array_slice($data, $stripfrom);
  		
  		$answers=array();
  		$step=1;
  		foreach($data as $cnt=>$d) {			  			
  			if($step==1) {
  				$answer=array();
  				$answer['answer']=$d;
  				$answer['is_correct']=0;
  				$step=2;
  			}
  			else {
  				$answer['points']=$d;
  				$step=1;
  				$answers[]=$answer;
  			}
  		}		
  	
  		// now we have the answers in the array, let's identify which ones are correct
  		if($data[1] == 'radio') {
  			// for 'single answer' it's the one with most points
			$top_points=0;
			foreach($answers as $answer) {
				if($answer['points']>$top_points) $top_points=$answer['points'];
			}		
			// once again
			foreach($answers as $cnt=>$answer) {
				if($answer['points']==$top_points) {
					$answers[$cnt]['is_correct']=1;
					break;
				}
			}	  
		} 
		else {
			// for other types answer with positive points is correct
			foreach($answers as $cnt=>$answer) {
				if($answer['points'] > 0) $answers[$cnt]['is_correct'] = 1;
			}
		}
		
		// finally insert them	
		$vals=array();
		foreach($answers as $cnt=>$answer) {
			if($answer['answer']==='') continue;
			$cnt++;
			$vals[]=$wpdb->prepare("(%d, %s, %s, %s, %d)", $qid, $answer['answer'], 
				$answer['is_correct'], $answer['points'], $cnt);
		}
		$values_sql=implode(",",$vals);
		
		if(sizeof($answers)) { $wpdb->query("INSERT INTO ".WATUPRO_ANSWERS." (question_id,answer,correct,point, sort_order) 
			VALUES $values_sql"); }
  }			   
  else {
  		// for old files import	
  		if(empty($data[1])) return false; // skip first line
  		$cat_id=WTPCategory::discover($data[4], $cats);
  		$stripfrom = 14;
  		$gapdata = explode("/", $data[8]);
  				  		
  		if(empty($data[0])) {
  			$question_array = array("content"=>$data[1], "answer_type"=>$data[2], "sort_order"=>$data[3],
  				"cat_id"=>$cat_id, "explain_answer"=>$data[5], "is_required"=>$data[6], 
  				"quiz"=>$_GET['quiz']);						
  			$question_array['correct_condition'] = $data[7];				  			
			$question_array['correct_gap_points'] = $gapdata[0];
			$question_array['incorrect_gap_points'] = $gapdata[1];
			$question_array['sorting_answers'] = $data[9];
			$question_array['max_selections'] = $data[10];
			$question_array['is_inactive'] = $data[11];
			$question_array['is_survey'] = $data[12];
			$question_array['elaborate_explanation'] = $data[13];
			$question_array['feedback_label'] = ''; // temp as it's not yet included in export
			
			// sorting answers may contain ||| or |||||| for new lines separator
		$question_array['sorting_answers'] = str_replace('||||||', "\n", $question_array['sorting_answers']);
		$question_array['sorting_answers'] = str_replace('|||', "\n", $question_array['sorting_answers']);
	  			
  			$qid = WTPQuestion::add($question_array);			  		
  		}
  		else {			  			  			
  			$wpdb->query($wpdb->prepare("UPDATE ".WATUPRO_QUESTIONS." SET question=%s, answer_type=%s,
  				sort_order=%d, cat_id=%d, explain_answer=%s, is_required=%d, correct_condition=%s,
  				correct_gap_points=%s, incorrect_gap_points=%s, sorting_answers = %s, max_selections = %d,
  				is_inactive=%d, is_survey = %d, elaborate_explanation = %s
  				WHERE ID=%d", $data[1], $data[2], $data[3], $cat_id, $data[5], $data[6], $data[7], 
  				$gapdata[0], $gapdata[1], $data[9], $data[10], $data[11], $data[12], $data[13], $data[0]));
  						  			
  			$qid=$data[0];
  		}
  		
  		// now answers, first extract them similar to the "new file" option			  	
  		$data=array_slice($data, $stripfrom);
  		
  		$answers=array();
  		$step=1;
		
  		foreach($data as $cnt=>$d) {			  			
  			switch($step) {
  				case 1:
  					$answer=array();
  					$answer['id']=$d;
  					$step=2;
  				break;
  				case 2:			  					
	  				$answer['answer']=$d;			  			
	  				$step=3;
  				break;
  				case 3:
  					$answer['points']=$d;
  					$step=1;
  					$answers[]=$answer;
  				break;
  			}			  			
  		} // end foreach
  		
  		// now insert or update
  		foreach($answers as $cnt=>$answer) {
  			if($answer['answer']==='') continue;
  			$cnt++;
			
			// assume 1st is correct
			if($cnt==1) $correct=1;
			else $correct=0;			  			
  			
  			if($answer['id']) {
  				$wpdb->query($wpdb->prepare("UPDATE ".WATUPRO_ANSWERS." SET answer=%s, point=%d WHERE ID=%d",
  					$answer['answer'], $answer['points'], $answer['id']));
  			}
  			else 
  			{
  				$wpdb->query($wpdb->prepare("INSERT INTO ".WATUPRO_ANSWERS."
  						(question_id,answer,correct,point, sort_order) VALUES (%d, %s, %s, %s, %d) ",
  						$qid, $answer['answer'], $correct, $answer['points'], $cnt));
  			}
  		}	// end foreach
  } // end else pf $_POST['file_type']=='new'  
}

// nl2br but without screwing tables and other tags
function watupro_nl2br($content) {
	$content = preg_replace("/\>(\r?\n){1,}/", ">", $content);	
	
	$content = nl2br($content);
	
	// remove br inside pre
	$match = array();
	if(preg_match_all('/<(pre)(?:(?!<\/\1).)*?<\/\1>/s', $content, $match)){		
	    foreach($match as $a){
	        foreach($a as $b){	        		
	           $content = str_replace($b, str_replace("<br />", "", $b), $content);	           
	        }
	    }
	}
	
	return $content;
}