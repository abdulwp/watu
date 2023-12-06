<?php
// exam model, currently to handle copy exam function, but later let's wrap more methods here
class WTPExam {
	static function copy($id, $copy_to=0) {
		global $wpdb;
		
		// select exam
	   $exam=$wpdb->get_row($wpdb->prepare("SELECT * FROM ".WATUPRO_EXAMS." WHERE id=%d", $id));
	   if(empty($exam->ID)) throw new Exception(__("Invalid exam ID", 'watupro'));
		
		// select grades
		$grades=$wpdb->get_results($wpdb->prepare("SELECT * FROM ".WATUPRO_GRADES." WHERE exam_id=%d ORDER BY ID", $id));
		
		// copy only some grades?
		if(!empty($_POST['copy_select'])) {
			foreach($grades as $cnt=>$grade) {
				if(!@in_array($grade->ID, @$_POST['grade_ids'])) unset($grades[$cnt]);
			}
		}
		
		// select questions and choices
		$questions=$wpdb->get_results($wpdb->prepare("SELECT * FROM ".WATUPRO_QUESTIONS." WHERE exam_id=%d 
			ORDER BY sort_order, ID", $id), ARRAY_A);
			
		// copy only some questions?
		if(!empty($_POST['copy_select'])) {
			foreach($questions as $cnt=>$question) {
				if(!@in_array($question['ID'], @$_POST['question_ids'])) unset($questions[$cnt]);
			}
		}	
			
		$qids=array(0);
		foreach($questions as $question) $qids[]=$question['ID'];
		
		$choices=$wpdb->get_results("SELECT * FROM ".WATUPRO_ANSWERS." WHERE question_id IN (".implode(",",$qids).") 
			ORDER BY sort_order, ID");
		
		// match choices to questions
		foreach($questions as $cnt=>$question) {
			$questions[$cnt]['choices']=array();
			foreach($choices as $choice) {
				if($choice->question_id==$question['ID']) $questions[$cnt]['choices'][]=$choice;
			}
		}
		
		// insert/copy exam
		if(empty($copy_to)) {
			$new_exam_id=self::add(array("name"=>stripslashes($exam->name)." ".__("(Copy)", 'watupro'), 
			"description"=>stripslashes($exam->description),
			"content"=>stripslashes($exam->final_screen),
			"require_login"=>$exam->require_login,
			"take_again"=>$exam->take_again,
			"email_taker"=>$exam->email_taker,
			"email_admin"=>$exam->email_admin,
			"admin_email"=>$exam->admin_email,
			"randomize_questions"=>$exam->randomize_questions,
			"login_mode"=>$exam->login_mode,
			"time_limit"=>$exam->time_limit,
			"pull_random"=>$exam->pull_random,
			"show_answers"=>$exam->show_answers,
			"group_by_cat"=>$exam->group_by_cat,
			"num_answers"=>$exam->num_answers,
			"single_page"=>$exam->single_page,
			"cat_id"=>$exam->cat_id,
			"times_to_take"=>$exam->times_to_take,
			"mode" => $exam->mode,
			"require_captcha" => $exam->require_captcha,
			"grades_by_percent" => $exam->grades_by_percent,
			"disallow_previous_button" => $exam->disallow_previous_button,
			"random_per_category" => $exam->random_per_category,
			"email_output" => $exam->email_output,
			"live_result" => $exam->live_result,
			"fee" => $exam->fee,
			"is_scheduled" => $exam->is_scheduled,
      	"schedule_from" => $exam->schedule_from,
      	"schedule_to" => $exam->schedule_to,
     		"submit_always_visible" => $exam->submit_always_visible,
     		"retake_after" => $exam->retake_after, 
     		"reuse_questions_from" => $exam->reuse_questions_from,
     		"show_pagination" => $exam->show_pagination,
     		"advanced_settings" => $exam->advanced_settings,
     		"enable_save_button" => $exam->enable_save_button,
     		"shareable_final_screen" => $exam->shareable_final_screen,
     		"redirect_final_screen" => $exam->redirect_final_screen,
     		"question_hints" => $exam->question_hints,
     		"takings_by_ip" => $exam->takings_by_ip,     	
     		"reuse_default_grades" => $exam->reuse_default_grades,
     		"store_progress" => $exam->store_progress,  	  	
			"custom_per_page" => $exam->custom_per_page,
			"is_active" => $exam->is_active,
			"randomize_cats" => $exam->randomize_cats,
			"email_subject" => $exam->email_subject,
			"pay_always" => $exam->pay_always,
			"published_odd" => $exam->published_odd,
			"published_odd_url" => $exam->published_odd_url,
			"editor_id" => $exam->editor_id,
     		"retake_grades" => "" /*Intentionally empty to avoid nasty bugs!*/));
		}		
		else $new_exam_id=$copy_to;
		
		// insert grades
		foreach($grades as $grade) {			
			$wpdb->query($wpdb->prepare("INSERT INTO ".WATUPRO_GRADES." SET
					exam_id=%d, gtitle=%s, gdescription=%s, gfrom=%s, gto=%s, cat_id=%d, certificate_id=%d",
					$new_exam_id, stripcslashes($grade->gtitle), stripcslashes($grade->gdescription), $grade->gfrom, 
					$grade->gto, $grade->cat_id, $grade->certificate_id));
		}
		
		// insert questions and choices
		foreach($questions as $question) {
			$to_copy = array(
				"quiz" => $new_exam_id,
				"content" => $question['question'], 
				"answer_type" => $question['answer_type'],			
				"cat_id" => $question['cat_id'],
				"explain_answer" => $question['explain_answer'],
				"is_required" => $question['is_required'],
				"sort_order" => $question['sort_order'],
				"correct_gap_points" => $question['correct_gap_points'],
				"incorrect_gap_points" => $question['incorrect_gap_points'],
				"correct_sort_points" => $question['correct_gap_points'],
				"gaps_as_dropdowns" => $question['gaps_as_dropdowns'],
				"incorrect_sort_points" => $question['incorrect_gap_points'],
				"slide_from" => $question['correct_gap_points'],
				"slide_to" => $question['incorrect_gap_points'],
				"max_selections" => $question['max_selections'],
				"sorting_answers" => $question['sorting_answers'],
				"is_inactive" => $question['is_inactive'],
				"is_survey" => $question['is_survey'],
				"elaborate_explanation" => $question['elaborate_explanation'],
				"open_end_mode" => $question['open_end_mode'],
				"correct_condition" => $question['correct_condition'],
				"tags" => str_replace('|', ', ', substr($question['tags'], 1, strlen($question['tags'])-2) ),
				"open_end_display" => $question['open_end_display'], 
				"exclude_on_final_screen" => $question['exclude_on_final_screen'],
				"hints" => $question['hints'],
				"importance" => $question['importance'],
				"unanswered_penalty" => $question['unanswered_penalty'],
				"truefalse" => $question['truefalse'],
				"accept_feedback" => $question['accept_feedback'],
				"feedback_label" => $question['feedback_label'],				
				"reward_only_correct" => $question['reward_only_correct'],
				"discard_even_negative" => $question['discard_even_negative'],
				"difficulty_level" => $question['difficulty_level'],
				"compact_format" => $question['compact_format'],
				"round_points" => $question['round_points'],
				"calculate_whole" => $question['calculate_whole'],
				"num_columns" => $question['num_columns'],
			);	
			
			if(!empty($question['elaborate_explanation'])) $to_copy['do_elaborate_explanation'] = true;		
			
			$new_question_id = WTPQuestion::add($to_copy);
			
			foreach($question['choices'] as $choice) {
				$wpdb->query($wpdb->prepare("INSERT INTO ".WATUPRO_ANSWERS." (question_id,answer,correct,point, sort_order)
					VALUES(%d, %s, %s, %s, %d)", 
					$new_question_id, $choice->answer, $choice->correct, $choice->point, $choice->sort_order));
			}
		}
	}
	
	// add exam
	static function add($vars) {
		global $wpdb, $user_ID;
		
		// normally each quiz is active unless deactivated
		$is_active=1;
		if(!empty($vars['is_inactive'])) $is_active = 0;
		
		$editor_id = $user_ID;
		if(current_user_can('manage_options') and !empty($_POST['editor_id'])) $editor_id = $_POST['editor_id'];
		
		// normalize params
		if(empty($vars['fee'])) $vars['fee'] = "0.00";
		if(empty($vars['random_per_category'])) $vars['random_per_category'] = "0";
		if(empty($vars['schedule_from'])) $vars['schedule_from'] = "$vars[schedule_from] $vars[schedule_from_hour]:$vars[schedule_from_minute]:00";
		if(empty($vars['schedule_to']))  $vars['schedule_to'] = "$vars[schedule_to] $vars[schedule_to_hour]:$vars[schedule_to_minute]:00";		
		$retake_grades = empty($vars['retake_grades']) ? "" : "|".@implode("|", $vars['retake_grades'])."|";
				
		$wpdb->query($wpdb->prepare("INSERT INTO ".WATUPRO_EXAMS." SET			
			name=%s, description=%s, final_screen=%s,  added_on=NOW(), 
			require_login=%d, take_again=%d, email_taker=%d, 
			email_admin=%d, randomize_questions=%d, login_mode=%s, time_limit=%d, pull_random=%d, 
			show_answers=%s, group_by_cat=%d, num_answers=%d, single_page=%d, cat_id=%d, 
			times_to_take=%d, mode=%s, fee=%d, require_captcha=%d, grades_by_percent=%d,
			admin_email=%s, disallow_previous_button=%d, random_per_category=%d,
			email_output=%s, live_result=%d, is_scheduled=%d, schedule_from=%s, 
			schedule_to=%s, submit_always_visible=%d, retake_grades=%s, show_pagination=%d,
			enable_save_button=%d, redirect_final_screen=%d, 
			editor_id=%d, takings_by_ip=%d, advanced_settings=%s, store_progress=%d, 
			custom_per_page=%d, is_active=%d, randomize_cats=%d, email_subject=%s,
			pay_always=%d, published_odd=%d, published_odd_url= %s", 
			$vars['name'], $vars['description'], $vars['content'], @$vars['require_login'], 
			@$vars['take_again'], @$vars['email_taker'],
			@$vars['email_admin'], $vars['randomize_questions'], @$vars['login_mode'],
			$vars['time_limit'], $vars['pull_random'], $vars['show_answers'], 
			@$vars['group_by_cat'], $vars['num_answers'], $vars['single_page'], $vars['cat_id'], 
			$vars['times_to_take'], @$vars['mode'], $vars['fee'], @$vars['require_captcha'],
			@$vars['grades_by_percent'], $vars['admin_email'], @$vars['disallow_previous_button'],
			$vars['random_per_category'], $vars['email_output'], @$vars['live_result'],
			@$vars['is_scheduled'], $vars['schedule_from'], $vars['schedule_to'], 
			@$vars['submit_always_visible'], $retake_grades, @$vars['show_pagination'], @$vars['enable_save_button'],
			@$vars['redirect_final_screen'], $editor_id, $vars['takings_by_ip'], 
			@$vars['advanced_settings'], @$vars['store_progress'], $vars['custom_per_page'], $is_active, 
			@$vars['randomize_cats'], $vars['email_subject'], intval(@$vars['pay_always']), @$vars['published_odd'],
			$vars['published_odd_url']));		
			$exam_id = $wpdb->insert_id;
		
		if(watupro_intel()) {
			 require_once(WATUPRO_PATH."/i/models/dependency.php");
			 require_once(WATUPRO_PATH."/i/models/exam_intel.php");
			 WatuPRODependency::store($exam_id);
			 WatuPROIExam::extra_fields($exam_id, $vars);
		} 
		
		do_action('watupro_exam_saved', $exam_id);		
		return $exam_id;
	}
	
	// edit exam
	static function edit($vars, $exam_id) {
		global $wpdb, $user_ID;
		$editor_id = $user_ID;
		if(current_user_can('manage_options') and !empty($_POST['editor_id'])) $editor_id = $_POST['editor_id'];
		
		// normally each quiz is active unless deactivated
		$is_active=1;
		if(!empty($vars['is_inactive'])) $is_active = 0;
		
		// normalize params
		if(empty($vars['fee'])) $vars['fee'] = "0.00";
		if(empty($vars['random_per_category'])) $vars['random_per_category'] = "0";
		$vars['schedule_from'] = "$vars[schedule_from] $vars[schedule_from_hour]:$vars[schedule_from_minute]:00";
		$vars['schedule_to'] = "$vars[schedule_to] $vars[schedule_to_hour]:$vars[schedule_to_minute]:00";
		$retake_grades = empty($vars['retake_grades']) ? "" : "|".@implode("|", $vars['retake_grades'])."|";
		
		
		$wpdb->query($wpdb->prepare("UPDATE ".WATUPRO_EXAMS." 
			SET name=%s, description=%s, final_screen=%s,require_login=%d, take_again=%d, 
			email_taker=%d, email_admin=%d, randomize_questions=%d, 
			login_mode=%s, time_limit=%d, pull_random=%d, show_answers=%s, 
			group_by_cat=%d, num_answers=%d, single_page=%d, cat_id=%d, times_to_take=%d,
			mode=%s, fee=%s, require_captcha=%d, grades_by_percent=%d, admin_email=%s,
			disallow_previous_button=%d, random_per_category=%d, email_output=%s, live_result=%d,
			is_scheduled=%d, schedule_from=%s, schedule_to=%s, submit_always_visible=%d,
			retake_grades=%s, show_pagination=%d, enable_save_button=%d, 
			redirect_final_screen=%d, takings_by_ip=%d, store_progress=%d, 
			custom_per_page=%d, is_active=%d, advanced_settings=%s, randomize_cats=%d, email_subject=%s,
			pay_always=%d, published_odd = %d, published_odd_url = %s, editor_id=%d
			WHERE ID=%d", $vars['name'], $vars['description'], $vars['content'],
		@$vars['require_login'], @$vars['take_again'], @$vars['email_taker'],
		@$vars['email_admin'], $vars['randomize_questions'], @$vars['login_mode'],
		$vars['time_limit'], $vars['pull_random'], $vars['show_answers'], @$vars['group_by_cat'],
		$vars['num_answers'], $vars['single_page'], $vars['cat_id'], $vars['times_to_take'],
		@$vars['mode'], $vars['fee'], @$vars['require_captcha'], @$vars['grades_by_percent'], 
		$vars['admin_email'], @$vars['disallow_previous_button'], $vars['random_per_category'], 
		$vars['email_output'], @$vars['live_result'], @$vars['is_scheduled'], $vars['schedule_from'], 
		$vars['schedule_to'], @$vars['submit_always_visible'], $retake_grades, 
		@$vars['show_pagination'], @$vars['enable_save_button'],
		@$vars['redirect_final_screen'], $vars['takings_by_ip'], @$vars['store_progress'], 
		$vars['custom_per_page'], $is_active, @$vars['advanced_settings'], @$vars['randomize_cats'], 
		$vars['email_subject'], intval(@$vars['pay_always']), @$vars['published_odd'],
		$vars['published_odd_url'], $editor_id,
		$exam_id));
		
		if(watupro_intel()) {
			 require_once(WATUPRO_PATH."/i/models/dependency.php");
			 require_once(WATUPRO_PATH."/i/models/exam_intel.php");
			 WatuPRODependency::store($exam_id);
			 WatuPROIExam::extra_fields($exam_id, $vars);
		} 
		
		do_action('watupro_exam_saved', $exam_id);
		return true;
	}
	
	// selects exams that user has access to along with taken data, post, and category
	// $cat_id_sql - categories that $uid has access to
	// returns array($my_exams, $takings, $num_taken);
	static function my_exams($uid, $cat_id_sql, $orderby = "tE.ID", $reorder_by_latest_taking = false) {
		global $wpdb;
		
		$cat_id_sql = strlen($cat_id_sql)? "AND tE.cat_id IN ($cat_id_sql)" : "";
		
		$paid_ids_sql = '';		
		if(watupro_intel() and !current_user_can(WATUPRO_MANAGE_CAPS) and get_option('watupro_nodisplay_paid_quizzes')) {
			// don't display quizzes that require payment but are not paid for
			$pids = array(0);
			$paid_quizzes = array();
			$paid_ids = $wpdb->get_results($wpdb->prepare("SELECT tE.ID as ID, tE.cat_id as cat_id FROM ".WATUPRO_EXAMS." tE
				WHERE tE.fee > 0 AND tE.ID NOT IN 
				(SELECT tP.exam_id FROM ".WATUPRO_PAYMENTS." tP WHERE tP.user_id=%d AND tP.status = 'completed' )", $uid));
			foreach($paid_ids as $pid) {
				$pids[] = $pid->ID;
				$paid_quizzes[] = $pid;
			}	
			
			// but maybe there are bundle payments made by this user?
			$bundle_payments = $wpdb->get_results($wpdb->prepare("SELECT tP.ID, tB.* 
				FROM ".WATUPRO_PAYMENTS." tP JOIN ".WATUPRO_BUNDLES." tB ON tB.ID = tP.bundle_id
				WHERE tP.user_id=%d AND tP.status='completed' AND tP.bundle_id!=0", $uid));
	
			foreach($bundle_payments as $payment) {
				if($payment->cat_id) {					
					// this is a category bundle, make sure quizzes from the category are excluded from $pids
					foreach($paid_quizzes as $pid) {
						if($pid->cat_id == $payment->cat_id) {
							foreach($pids as $n=>$p) {
								if($p == $pid->ID) unset($pids[$n]);
							}
						}
					} // end foreach quiz
				} 
				else {
					// bundle of individual quizzes
					$qids = explode(",", $payment->quiz_ids);
					foreach($qids as $q) {
						foreach($pids as $n=>$p) {
								if($p == $q) unset($pids[$n]);
							}
					}
				}
			}	// end foreach payment			
			
			$paid_ids_sql = " AND tE.ID NOT IN (".implode(",", $pids).") ";
		}
		
		// select all exams along with posts they have been embedded in
		$exams = $wpdb->get_results("SELECT tE.*, tC.name as cat 
			FROM ".WATUPRO_EXAMS." tE LEFT JOIN ".WATUPRO_CATS." tC
			ON tC.ID=tE.cat_id
			WHERE tE.is_active=1 $cat_id_sql $paid_ids_sql ORDER BY $orderby");
		
		// now select all posts that have watupro shortcode in them
		$posts = $wpdb->get_results("SELECT * FROM {$wpdb->posts} 
			WHERE (post_content LIKE '%[watupro %]%' OR post_content LIKE '%[wtpuc quiz_id=%') 
			AND post_status='publish' AND post_title!=''
			ORDER BY post_date DESC");
			
		// select all exams that I have taken
		# $wpdb->show_errors=true;
		$takings=$wpdb->get_results($wpdb->prepare("SELECT * FROM ".WATUPRO_TAKEN_EXAMS."
			WHERE user_id=%d AND in_progress=0 ORDER BY ID DESC", $uid));
		$tids=array();
		foreach($takings as $taking) $tids[]=$taking->exam_id;
		
		// final exams array - should contain only one post per exam, and we should know which one
		// is taken and which one is not
		$my_exams=array();
		$num_taken=0;
		
		foreach($exams as $cnt=>$exam) {
			$my_exam=$exam;
			if(in_array($exam->ID, $tids)) $my_exam->is_taken=1;
			else $my_exam->is_taken=0;
		
			$post_found=false;
			foreach($posts as $post) {
				if(stristr($post->post_content,"[watupro ".$exam->ID."]") or stristr($post->post_content,"[wtpuc quiz_id=".$exam->ID." mode")) {
					$my_exam->post=$post;
					$post_found=true;
					break;
				}
			}
			
			// maybe post wasn't found but the quiz is published innon-standard way?
			if($exam->published_odd) $post_found = true;
		
			if($post_found) {
				// match latest taking and fill all takings
				$my_exam->takings = array();
				foreach($takings as $taking) {
					if($taking->exam_id!=$exam->ID) continue;
					
					if(empty($my_exam->taking)) { 
						$my_exam->taking=$taking;
						$num_taken++;
					}
					
					$my_exam->takings[] = $taking;
				}
		
				// add to the final array
				$my_exams[]=$my_exam;
			} // end if $post_found
		} // end foreach exam
		
		// reordering by latest taking
		if($reorder_by_latest_taking) {
			uasort($my_exams, array(__CLASS__, "reorder_by_latest_taking"));			
		}
		
		// primary returns $my_exams, but $takings may also be used as it's retrieved anyway
		return array($my_exams, $takings, $num_taken);
	}
	
	// reorders the exams by latest taking on top. If one of the exams is not taken at all, return 0
	static function reorder_by_latest_taking($a, $b) {
		if(empty($a->taking) or empty($b->taking)) return 0;
		
		if(strtotime($a->taking->end_time) > strtotime($b->taking->end_time)) return -1;
		else return 1; 
	}
	
	// lists all published exams or these within given category
	static function show_list($cat_id = 'ALL', $orderby = "tE.ID", $show_status = false) {
		 global $wpdb, $user_ID;
		 $cat_id_sql = ($cat_id == 'ALL') ? "" : $cat_id;
		 		
		 list($exams) = WTPExam::my_exams($user_ID, $cat_id_sql, $orderby);
		 $eids = array(0);
		 foreach($exams as $exam) $eids[] = $exam->ID;
		 
		 // if show_status we need to take the latest taking of this user for each exam and figure out the status
		 if($show_status and !empty($user_ID)) {
		 	 $takings = $wpdb->get_results($wpdb->prepare("SELECT ID, exam_id, in_progress FROM ".WATUPRO_TAKEN_EXAMS."
		 	 	WHERE user_id=%d AND exam_id IN (".implode(',', $eids).") AND ID IN (
		 	 		SELECT MAX(ID) FROM ".WATUPRO_TAKEN_EXAMS." WHERE user_id=%d GROUP BY exam_id
		 	 	)
				ORDER BY ID DESC", $user_ID, $user_ID));
		 }
		 
		 $content = "";		 
		 foreach($exams as $exam) {
		 		$content .= "<p><a href=".get_permalink($exam->post->ID)." target='_blank'>".stripslashes($exam->name)."</a>";
		 		if($show_status and !empty($user_ID) and sizeof($takings)) {
		 			$status = __('Not started', 'watupro');
		 			foreach($takings as $taking) {
		 				if($taking->exam_id == $exam->ID) $status = $taking->in_progress ? __('In progress', 'watupro') : __('Completed', 'watupro');
		 			}
		 			
		 			$content .= "<br><i>".$status."</i>";
		 		}
		 		$content .="</p>";
		 }	 
		 
		 return $content;
	}
	
	// displays numbered pagination
	static function paginator($num_questions, $in_progress = null) {		
		$html = '';
		$html .= "<div class='watupro-paginator-wrap' style='display:none;'><ul class='watupro-paginator watupro-question-paginator watupro-paginator-custom'>";
		for($i = 0; $i < $num_questions; $i++) {
			$j = $i+1;
			if($j == 1) $activeclass='class="active"';
			else $activeclass = '';
			$html .= "<li $activeclass id='WatuPROPagination".$j."' onclick='WatuPRO.goto(event, ".$j.");'>".$j."</li>";
		}
		$html .="</ul></div>";
		
		if(!empty($in_progress)) $html .= "<script type='text/javascript'>
		jQuery(function(){
			WatuPRO.hilitePaginator($num_questions);
		});
		</script>";		
		
		return $html;
	}
	
	// paginator of the answers (the %%ANSWERS-PAGINATED%% variable)
	static function answers_paginator($num_questions) {		
		$html = '';
		$html .= "<div class='watupro-paginator-wrap watupro-answers-paginator-wrap'>
			<ul class='watupro-paginator watupro-question-paginator watupro-paginator-custom  watupro-answers-paginator'>";
		for($i = 0; $i < $num_questions; $i++) {
			$j = $i+1;
			if($j == 1) $activeclass='class="active"';
			else $activeclass = '';
			$html .= "<li $activeclass id='WatuPROAnswerPagination".$j."' onclick='WatuPRO.ansGoto(event, ".$j.");'>".$j."</li>";
		}
		$html .="</ul></div>";
		return $html;
	}	
	
	// numbered pagination when the quiz is "one page per question category" or "custom no. questions per page
	// when "one page per categfory, then we use $questions to figure out the number of cats
	// otherwise $num_pages has the number 
	// NOT USED
	/*static function page_paginator($single_page, $num_pages, $questions, $in_progress = null) {
		$html = '';
		if($single_page == WATUPRO_PAGINATE_PAGE_PER_CATEGORY) {
			$catids = array();
			foreach($questions as $question) {
				if(!in_array($question->cat_id, $catids)) $catids[] = $question->cat_id;
			}
			
			$num_pages = sizeof($catids);
		} // end one page per category case
		
		// now having $num_pages we can display the paginator
		$html .= "<div class='watupro-paginator-wrap'><ul class='watupro-paginator watupro-paginator-custom'>";
		for($i = 0; $i < $num_pages; $i++) {
			$j = $i+1;
			if($j == 1) $activeclass='class="active"';
			else $activeclass = '';

			// on the 1st page link the boolean passed to nextCategory should be false and the page number should be 2
			// so we actually do sth like "previous page"
			$bool = $i ? 'true' : 'false';
			$curcatpage = $i ? $i : 2;		
			
			$html .= "<li $activeclass id='WatuPROPagination".$j."' onclick='WatuPRO.curCatPage=".$curcatpage.";WatuPRO.nextCategory(".$num_pages.", ".$bool.");'>".sprintf(__('Page %d', 'watupro'), $j)."</li>";
		}
		$html .="</ul></div>";
		
		return $html;
	}*/
	
	// category paginator for quizzes where we have "show category based paginator"
	static function category_paginator($questions, $exam, $in_progress = null) {
		if($exam->single_page == WATUPRO_PAGINATE_CUSTOM_NUMBER) return ''; // doesn't make sense in this case
		if(!$exam->group_by_cat) return ''; // doesn't work when not grouped by category
		
		$activeclass = '';
		// now let's construct it
		$html = "<div class='watupro-paginator-wrap'><ul class='watupro-paginator watupro-category-paginator watupro-category-paginator-custom' id='watuPROCatPaginator".$exam->ID."'>";
		$qcat_ids = array();
		foreach($questions as $cnt=>$question) {
			if(!in_array($question->cat_id, $qcat_ids)) {
				$qcat_ids[] = $question->cat_id;				
				if($question->cat_id == 0) $question->cat = __('Uncategorized', 'watupro');
				$gotopage = $cnt+1;
				
				$html .= "<li $activeclass id='exam-".$exam->ID."-WatuPROCatPagination-".$gotopage."' onclick='";

				switch($exam->single_page) {
					case WATUPRO_PAGINATE_ONE_PER_PAGE:
					case WATUPRO_PAGINATE_PAGE_PER_CATEGORY:						
						$html .= "WatuPRO.goto(event, $gotopage);";
					break;
					case WATUPRO_PAGINATE_ALL_ON_PAGE:						
						$html .= 'WatuPRO.scrollTo("questionWrap-'.$gotopage.'");';
					break;
				}				
				
				$html .= "'>".stripslashes($question->cat)."</li>";
			}
		}
		$html .="</ul></div>";
		
		return $html;
	} // end category paginator()

	// description along with a start button
	// @param $inside boolean - whether the call is inside the quiz div. 
	// On timed quizzes this means we should not show description 
	function maybe_show_description($exam, $inside = false) {
		global $user_ID;		
		$description = stripslashes($exam->description);	
		$advanced_settings = unserialize(stripslashes($exam->advanced_settings));
		
		if(empty($_POST['watupro_contact_details_requested'.$exam->ID]) and !empty($advanced_settings['ask_for_contact_details']) and $advanced_settings['ask_for_contact_details'] == 'start') {	
			$_POST['watupro_contact_details_requested'.$exam->ID]=true; // to show only once on the exam, this is important
			ob_start();
			$this->maybe_ask_for_contact($exam, 'start', $description);
			$content = ob_get_clean();		
			
			if(!empty($content)) {
				$description = $description . $content;
				
				// in this case button becomes required
				if(!strstr($description, "{{{button")) {					
					$start_text = empty($advanced_settings['contact_fields']['start_button']) 
						? __('Start quiz!', 'watupro'): $advanced_settings['contact_fields']['start_button'];
					$description .= "<p align='center'>{{{button \"".$start_text."\"}}}</p>"; 
				}
			}			
		} // end request contact info
		
		if(empty($description)) return "";
		// if($exam->time_limit and $inside) return "";		
				
		// is there a button?
		if(!strstr($description, "{{{button")) $button = false;
		else {
			// let's parse the button			
			$matches = array();
			preg_match("/{{{button([^}}}])*}}}/", $description, $matches);
		  $button_code = $matches[0];
		  $button_code = str_replace("{{{","", $button_code);
		  $button_code = str_replace("}}}","", $button_code);
		  
		  $parts = explode(' "', $button_code);
		  		  
		  $text = empty($parts[1]) ? __('Start Quiz!', 'watupro') : substr($parts[1], 0, strlen($parts[1])-1);
		  $style = empty($parts[2]) ? '' : substr($parts[2], 0, strlen($parts[2])-1);
		  
		  if($exam->time_limit) {
		  	$button = "<button style='$style' onclick=\"WatuPRO.InitializeTimer(".($exam->time_limit*60).", ".$exam->ID.", 1);\">$text</button>";
		  }
			else {
				$button = "<button style='$style' onclick=\"WatuPRO.startButton();\">$text</button>";
			}
		
			// now replace the button in the description
			$description = preg_replace("/{{{button([^}}}])*}}}/", $button, $description);
		}
	
		// when these fields are presented and user is logged in, we have to prefill them
		$email_value_prefilled = $name_value_prefilled = '';
		if(is_user_logged_in() and ( strstr($description, "{{{email-field") or strstr($description, "{{{name-field"))) {
			$user = get_userdata($user_ID);
			$email_value_prefilled = 'value="'.$user->user_email.'"'; 
			$name_value_prefilled = 'value="'.$user->display_name.'"';
		}
		
		if(!empty($_POST['watupro_taker_email'])) $email_value_prefilled = 'value="'.stripslashes($_POST['watupro_taker_email']).'"';
		if(!empty($_POST['watupro_taker_name'])) $name_value_prefilled = 'value="'.stripslashes($_POST['watupro_taker_name']).'"';
		
		// are there name/email fields?
		if(empty($advanced_settings['ask_for_contact_details']) and strstr($description, "{{{email-field")) {
			$matches = array();
			preg_match("/{{{email-field([^}}}])*}}}/", $description, $matches);
			$field_code = $matches[0];
			$field_code = str_replace("{{{","", $field_code);
		   $field_code = str_replace("}}}","", $field_code);
		   
			$parts = explode(" ", $field_code);
			array_shift($parts);
			$atts = implode(" ", $parts); // if any attributes are passed add them here
			
			$field_code = "<input type='text' name='watupro_taker_email' id='watuproTakerEmail".$exam->ID."' $atts $email_value_prefilled>"; 
			$description = preg_replace("/{{{email-field([^}}}])*}}}/", $field_code, $description);
		}
		if(empty($advanced_settings['ask_for_contact_details']) and strstr($description, "{{{name-field")) {
			$matches = array();
			preg_match("/{{{name-field([^}}}])*}}}/", $description, $matches);
			$field_code = $matches[0];
			$field_code = str_replace("{{{","", $field_code);
		   $field_code = str_replace("}}}","", $field_code);
		   
			$parts = explode(" ", $field_code);
			array_shift($parts);
			$atts = implode(" ", $parts); // if any attributes are passed add them here
			
			$field_code = "<input type='text' name='watupro_taker_name' id='watuproTakerName".$exam->ID."' $atts $name_value_prefilled>"; 
			$description = preg_replace("/{{{name-field([^}}}])*}}}/", $field_code, $description);
		}		
		
		// when we come from timer submitted the description should still be there but not visible
		$style = empty($_POST['watupro_start_timer']) ? "" : ' style="display:none;" ';
		echo '<div class="watupro-exam-description" id="description-quiz-'.$exam->ID.'"'.$style.'>'.wpautop($description).'</div>';
		
		return $button;
  }
  
  // show div that asks for contact details
  function maybe_ask_for_contact($exam, $position, $description = '') {
  	 global $user_email, $user_identity;
  	 $advanced_settings = unserialize(stripslashes($exam->advanced_settings));
  	 
  	 if(empty($advanced_settings['ask_for_contact_details']) or $advanced_settings['ask_for_contact_details']!= $position) return ""; 
  	 
  	 // base64 decode fields?
  	 if(!empty($advanced_settings['contact_fields']['labels_encoded'])) {
  	 	$advanced_settings['contact_fields']['email_label'] = stripslashes(base64_decode($advanced_settings['contact_fields']['email_label']));
  	 	$advanced_settings['contact_fields']['name_label'] = stripslashes(base64_decode($advanced_settings['contact_fields']['name_label']));
  	 	$advanced_settings['contact_fields']['phone_label'] = stripslashes(base64_decode($advanced_settings['contact_fields']['phone_label']));
  	 	$advanced_settings['contact_fields']['company_label'] = stripslashes(base64_decode($advanced_settings['contact_fields']['company_label']));
  	 }
  	 
  	 // now include the div  	 
  	 if(@file_exists(get_stylesheet_directory().'/watupro/ask-for-contact.html.php')) require get_stylesheet_directory().'/watupro/ask-for-contact.html.php';
	else require WATUPRO_PATH."/views/ask-for-contact.html.php";
  }
  
  // replaces the info from the user's contact fields when "ask for contact data" is selected.
  // can be used in certificates and final screen
  static function replace_contact_fields($exam, $contact_fields, $output) {  	
  	if(!strstr($output, '%%FIELD-COMPANY%%') and !strstr($output, '%%FIELD-PHONE%%')) return $output;
  	if(empty($contact_fields)) return $output;
  	$advanced_settings = unserialize(stripslashes($exam->advanced_settings));
  	
  	if(strstr($output, '%%FIELD-COMPANY%%')) {
  		$output = str_replace('%%FIELD-COMPANY%%', $contact_fields['company'], $output);
  	}	
  	
  	if(strstr($output, '%%FIELD-PHONE%%')) {
  		$output = str_replace('%%FIELD-PHONE%%', $contact_fields['phone'], $output);
  	}	
  	
  	return $output;
  }
}