<?php
// add/edit exam
function watupro_exam() {
	global $wpdb, $user_ID, $wp_roles;
	$roles = $wp_roles->roles;	
		
	$multiuser_access = 'all';
	if(watupro_intel()) $multiuser_access = WatuPROIMultiUser::check_access('exams_access');
	if($multiuser_access == 'view' or $multiuser_access == 'group_view') wp_die("You are not allowed to do this");
	
	// set default points for answers?
	$set_default_points = get_option('watupro_set_default_points');
	
	if(isset($_REQUEST['submit'])) {
		// prepare advanced settings - email grades and contact info fields
		$advanced_settings = $wpdb->get_var($wpdb->prepare("SELECT advanced_settings FROM ".WATUPRO_EXAMS."
			WHERE id=%d",  @$_REQUEST['quiz']));
			
		if(!empty($advanced_settings)) $advanced_settings = unserialize( stripslashes($advanced_settings));
		else $advanced_settings = array();
		
		// email grades
		$advanced_settings['email_grades'] = @$_POST['email_grades'];
		
		// flag for review
		$advanced_settings['flag_for_review'] = @$_POST['flag_for_review'];
		
		// dont display question numbers
		$advanced_settings['dont_display_question_numbers'] = @$_POST['dont_display_question_numbers'];
		
		// contact fields	
		$advanced_settings['contact_fields'] = array();
		$advanced_settings['contact_fields']['intro_text'] = base64_encode($_POST['ask_contact_intro']);
		$advanced_settings['contact_fields']['email'] = $_POST['ask_for_email'];
		$advanced_settings['contact_fields']['email_label'] = base64_encode($_POST['ask_for_email_label']);		
		$advanced_settings['contact_fields']['name'] = $_POST['ask_for_name'];
		$advanced_settings['contact_fields']['name_label'] = base64_encode($_POST['ask_for_name_label']);
		$advanced_settings['contact_fields']['phone'] = $_POST['ask_for_phone'];
		$advanced_settings['contact_fields']['phone_label'] = base64_encode($_POST['ask_for_phone_label']);
		$advanced_settings['contact_fields']['company'] = $_POST['ask_for_company'];
		$advanced_settings['contact_fields']['company_label'] = base64_encode($_POST['ask_for_company_label']);
		$advanced_settings['contact_fields']['start_button'] = $_POST['ask_for_start_button'];
		$advanced_settings['contact_fields']['labels_encoded'] = 1;
		$advanced_settings['enumerate_choices'] = $_POST['enumerate_choices'];
		$advanced_settings['show_progress_bar'] = empty($_POST['show_progress_bar']) ? 0 : 1;
		$advanced_settings['progress_bar_percent'] = empty($_POST['progress_bar_percent']) ? 0 : 1;
		$advanced_settings['show_category_paginator'] = @$_POST['show_category_paginator'];
		$advanced_settings['retakings_per_period'] = @$_POST['retakings_per_period'];
		
		$advanced_settings['ask_for_contact_details'] = $_POST['ask_for_contact_details'];
		
		// question based captcha & honeypot
		$advanced_settings['require_text_captcha'] = @$_POST['require_text_captcha'];
		$advanced_settings['use_honeypot'] = @$_POST['use_honeypot'];
		
		// question difficulty level
		$advanced_settings['difficulty_level'] = @$_POST['difficulty_level'];
		
		// accept rating
		$advanced_settings['accept_rating'] = @$_POST['accept_rating'];
		
		// display result when no re-takings are allowed
		$advanced_settings['no_retake_display_result'] = @$_POST['no_retake_display_result'];
		
		// don't store takings in DB
		$advanced_settings['dont_store_taking'] = @$_POST['dont_store_taking'];
		
		// always show quiz description, even to non logged users
		$advanced_settings['always_show_description'] = @$_POST['always_show_description'];
		
		// when selecting "grades by %" what mode - correct answers or % of maximum points
		$advanced_settings['grades_by_percent_mode'] = @$_POST['grades_by_percent_mode'];
		
		// don't show previously answered questions to the user (for questions that pull from a pool)
		$advanced_settings['dont_show_answered'] = intval(@$_POST['dont_show_answered']);
		// or only these that were correctly answered
		$advanced_settings['dont_show_correctly_answered'] = intval(@$_POST['dont_show_correctly_answered']);
		
		// admin comments
		$advanced_settings['admin_comments'] = base64_encode($_POST['admin_comments']);
		
		// per-quiz default points
		if($set_default_points) {
			$advanced_settings['default_correct_answer_points'] = $_POST['default_correct_answer_points'];
			$advanced_settings['default_incorrect_answer_points'] = $_POST['default_incorrect_answer_points'];
		}
		
		// extra settings from the Intelligence module
		if(watupro_intel()) {
			$advanced_settings['premature_end_percent'] = intval(@$_POST['premature_end_percent']);
			$advanced_settings['premature_end_question'] = intval(@$_POST['premature_end_question']);
			if(empty($advanced_settings['premature_end_question'])) 	$advanced_settings['premature_end_percent'] = 0;
			$advanced_settings['prevent_forward_percent'] = intval(@$_POST['prevent_forward_percent']);
			$advanced_settings['premature_text'] = base64_encode($_POST['premature_text']);
			$advanced_settings['prevent_forward_question'] = intval(@$_POST['prevent_forward_question']);
			if(empty($advanced_settings['prevent_forward_question'])) $advanced_settings['prevent_forward_percent'] = 0;
			
			if(!empty($advanced_settings['premature_end_question']) or !empty($advanced_settings['prevent_forward_question'])) $_POST['store_progress'] = 1;
			$advanced_settings['dependency_type'] = $_POST['dependency_type'];
		}
		
		$_POST['advanced_settings'] = serialize($advanced_settings);
		
		if($_REQUEST['action'] == 'edit') { //Update goes here
			$exam_id = $_REQUEST['quiz'];

			if($multiuser_access == 'own') {
				$editor_id = $wpdb->get_var($wpdb->prepare("SELECT editor_id FROM ".WATUPRO_EXAMS." WHERE ID=%d", $exam_id));				
				if($editor_id != $user_ID) wp_die('You can edit only your own exams','watupro');
			}	
			
			if($multiuser_access == 'group') {		
				$cat_ids = WTPCategory::user_cats($user_ID);
				$cat_id_sql=implode(",",$cat_ids);
				$allowed_to_edit = $wpdb->get_var($wpdb->prepare("SELECT ID FROM ".WATUPRO_EXAMS." 
					WHERE cat_id IN ($cat_id_sql) AND ID=%d", $exam_id));
				if(!$allowed_to_edit) wp_die(__('You can edit only quizzes within your allowed categories', 'watupro'));					
			}					
			
			if(empty($_POST['use_different_email_output'])) $_POST['email_output']='';
			WTPExam::edit($_POST, $exam_id);
			if(!empty($_POST['auto_publish'])) watupro_auto_publish($exam_id);
			$wp_redirect = admin_url('admin.php?page=watupro_exams&message=updated');	
			
			// save advanced settings
			if($exam_id) {
				$_GET['exam_id'] = $exam_id;
				$_POST['ok'] = true;
				watupro_advanced_exam_settings();
			}
		} else {
			// add new exam
			$exam_id=WTPExam::add($_POST);			
			if($exam_id == 0 ) $wp_redirect = admin_url('admin.php?page=watupro_exams&message=fail');
			if($exam_id and !empty($_POST['auto_publish'])) watupro_auto_publish($exam_id);
			$wp_redirect = admin_url('admin.php?page=watupro_questions&message=new_quiz&quiz='.$exam_id);
		}
		
     echo "<meta http-equiv='refresh' content='0;url=$wp_redirect' />"; 
    exit;
	}
	
	$action = 'new';
	if(@$_REQUEST['action'] == 'edit') $action = 'edit';
	
	// global answer_display
	$answer_display=get_option('watupro_show_answers');
	// global single page display
	$single_page = get_option('watupro_single_page');
	
	$dquiz = array();
	$grades = array();
	
	// initialize advanced settings to avoid PHP notices
	$advanced_settings = array('play_levels' => '');
	
	if($action == 'edit') {
		$dquiz = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".WATUPRO_EXAMS." WHERE ID=%d", $_GET['quiz']));
		$single_page = $dquiz->single_page;

		if($multiuser_access == 'own' and $dquiz->editor_id != $user_ID) wp_die('You can edit only your own exams','watupro');		
		
		$grades = WTPGrade :: get_grades($dquiz);	
		$final_screen = stripslashes($dquiz->final_screen);
		$schedule_from = $dquiz->schedule_from;
		list($schedule_from) = explode(" ", $schedule_from);
		$schedule_to = $dquiz->schedule_to;
		list($schedule_to) = explode(" ", $schedule_to);
		
		$advanced_settings = unserialize( stripslashes($dquiz->advanced_settings));	
		
		// base64 decode fields?
	  	 if(!empty($advanced_settings['contact_fields']['labels_encoded'])) {
	  	 	$advanced_settings['contact_fields']['email_label'] = stripslashes(base64_decode($advanced_settings['contact_fields']['email_label']));
	  	 	$advanced_settings['contact_fields']['name_label'] = stripslashes(base64_decode($advanced_settings['contact_fields']['name_label']));
	  	 	$advanced_settings['contact_fields']['phone_label'] = stripslashes(base64_decode($advanced_settings['contact_fields']['phone_label']));
	  	 	$advanced_settings['contact_fields']['company_label'] = stripslashes(base64_decode($advanced_settings['contact_fields']['company_label']));
	  	 }
	} // end edit 
	else {
		$final_screen = __("<p>You have completed %%QUIZ_NAME%%.</p>\n\n<p>You scored %%SCORE%% correct out of %%TOTAL%% questions.</p>\n\n<p>You have collected %%POINTS%% points.</p>\n\n<p>Your obtained grade is <b>%%GRADE%%</b></p>\n\n<p>Your answers are shown below:</p>\n\n%%ANSWERS%%", 'watupro');
		$schedule_from = date("Y-m-d");
		$schedule_to = date("Y-m-d");
	}
	
	// select certificates if any
	$certificates=$wpdb->get_results("SELECT * FROM ".WATUPRO_CERTIFICATES." ORDER BY title");
	$cnt_certificates=sizeof($certificates);
	
	// categories if any
	$cats=$wpdb->get_results("SELECT * FROM ".WATUPRO_CATS." ORDER BY name");

	// avoid PHP notices
	if(empty($dquiz->ID)) {
		$dquiz = (object)array("ID"=>0, "name"=>"", "description"=>"", 
		"single_page" => WATUPRO_PAGINATE_ALL_ON_PAGE, 'schedule_from'=>'', 'schedule_to'=>'', 'email_output'=>''); 
	}
	
	// select other exams
	$other_exams=$wpdb->get_results("SELECT * FROM ".WATUPRO_EXAMS." WHERE ID!='".intval($dquiz->ID)."' ORDER BY name");
	
	if(watupro_intel()) {		
		require_once(WATUPRO_PATH."/i/models/dependency.php");
		$dependencies = WatuPRODependency::select($dquiz->ID);	
			
		// select all editors for the editors drop-down. Makes sense only if any roles are selected to manage exams so do it only then
		$editors = get_users(array("role" => 'administrator'));
		$more_roles = false;		
		foreach($roles as $key=>$r) {			
			$role = get_role($key);
			if(empty($role->capabilities['watupro_manage_exams'])) continue;
			
			// add users to $editors array
			$users = get_users(array("role" => $key)); 
			$editors = array_merge($editors, $users);
			$more_roles = true;
		}
			
	}
	
	// check if recaptcha keys are in place
	$recaptcha_public = get_option('watupro_recaptcha_public');
	$recaptcha_private = get_option('watupro_recaptcha_private');
	
	// is this quiz currently published?
	if(!empty($_GET['quiz'])) {
		$quiz_id = intval($_GET['quiz']);
		$is_published = $wpdb->get_var("SELECT ID FROM {$wpdb->posts} WHERE post_content LIKE '%[watupro ".$quiz_id."]%' 
				AND post_status='publish' AND post_title!=''");
	} 
	else $is_published = false;
	
	// any difficulty levels?
	$difficulty_levels = stripslashes(get_option('watupro_difficulty_levels'));
	if(!empty($difficulty_levels)) $difficulty_levels = explode(PHP_EOL, $difficulty_levels);
	
	$grades_by_percent_dropdown = '<select name="grades_by_percent_mode">
		<option value="correct_answer" '.((empty($advanced_settings['grades_by_percent_mode']) or $advanced_settings['grades_by_percent_mode'] != 'max_points') ? "selected" : '').'>'.__('% correct answers', 'watupro').'</option>
		<option value="max_points" '.((!empty($advanced_settings['grades_by_percent_mode']) and $advanced_settings['grades_by_percent_mode'] == 'max_points') ? "selected" : '').'>'.__('% from maximum points', 'watupro').'</option>
	</select>';
	
	 // default points
  	 if($set_default_points) {
  	 	$default_correct_answer_points = isset($advanced_settings['default_correct_answer_points']) ? $advanced_settings['default_correct_answer_points'] : get_option('watupro_correct_answer_points');
  	 	$default_incorrect_answer_points = isset($advanced_settings['default_incorrect_answer_points']) ? $advanced_settings['default_incorrect_answer_points'] : get_option('watupro_incorrect_answer_points');
  	 } // end default points
	
	watupro_enqueue_datepicker();
	if(@file_exists(get_stylesheet_directory().'/watupro/exam_form.php')) require get_stylesheet_directory().'/watupro/exam_form.php';
	else require WATUPRO_PATH."/views/exam_form.php";
}

// list exams
function watupro_exams() {
	global $wpdb, $user_ID;
	$multiuser_access = 'all';
	if(watupro_intel()) $multiuser_access = WatuPROIMultiUser::check_access('exams_access');
	
	if(!empty($_REQUEST['action']) and $_REQUEST['action'] == 'delete') {	
	   check_admin_referer('delete_quiz', 'delete_nonce');	
		if($multiuser_access == 'view' or $multiuser_access == 'group_view') wp_die("You are not allowed to do this");
		if($multiuser_access == 'own') {
			// make sure this is my quiz
			$quiz = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".WATUPRO_EXAMS." WHERE ID=%d", $_GET['quiz']));
			if($quiz->editor_id != $user_ID) wp_die(__('You can delete only your own quizzes.','watupro'));
		}
		if($multiuser_access == 'group') {
			// make sure I can delete
			$cat_ids = WTPCategory::user_cats($user_ID);
			$quiz = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".WATUPRO_EXAMS." WHERE ID=%d", $_GET['quiz']));
			if(!in_array($quiz->cat_id, $cat_ids)) wp_die(__('You are not allowed to delete this quiz', 'watupro'));
		}
		
		$wpdb->query($wpdb->prepare("DELETE FROM ".WATUPRO_EXAMS." WHERE ID=%d", $_GET['quiz']));
		$wpdb->query($wpdb->prepare("DELETE FROM ".WATUPRO_ANSWERS." WHERE question_id IN (SELECT ID FROM ".WATUPRO_QUESTIONS." WHERE exam_id=%d)", $_GET['quiz']));
		$wpdb->query($wpdb->prepare("DELETE FROM ".WATUPRO_QUESTIONS." WHERE exam_id=%d", $_GET['quiz']));		
		$wpdb->query($wpdb->prepare("DELETE FROM ".WATUPRO_GRADES." WHERE exam_id=%d", $_GET['quiz']));
		$wpdb->query($wpdb->prepare("DELETE FROM ".WATUPRO_STUDENT_ANSWERS." WHERE exam_id=%d", $_GET['quiz']));
	}
	
	// auto cleanup / blank out data
	if(get_option('watupro_auto_db_cleanup') == '1') {		
		$days = get_option('watupro_auto_db_cleanup_days');
		$days = intval($days) ? intval($days) : 30;
		if(get_option('watupro_auto_db_cleanup_mode') == 'cleanup') {
			$wpdb->query($wpdb->prepare("DELETE FROM ".WATUPRO_TAKEN_EXAMS." 
				WHERE date < '".date("Y-m-d", current_time('timestamp'))."' - INTERVAL %d DAY", $days));
			$wpdb->query($wpdb->prepare("DELETE FROM ".WATUPRO_STUDENT_ANSWERS." 
				WHERE timestamp < '".current_time('mysql')."' - INTERVAL %d DAY", $days));
		}
		else {
			$wpdb->query($wpdb->prepare("UPDATE ".WATUPRO_TAKEN_EXAMS." 
				SET details='data removed', catgrades='data removed' 
				WHERE date < '".date("Y-m-d", current_time('timestamp'))."' - INTERVAL %d DAY", $days));
				
			$wpdb->query($wpdb->prepare("UPDATE ".WATUPRO_STUDENT_ANSWERS." 
				SET question_text='', snapshot='data removed' 
				WHERE timestamp < '".current_time('mysql')."' - INTERVAL %d DAY", $days));	
		}
	}
	
	$ob = empty($_GET['ob']) ? "Q.ID" : $_GET['ob'];
	$dir = empty($_GET['dir']) ? "DESC" : $_GET['dir'];
	$odir = ($dir == 'ASC') ? 'DESC' : 'ASC';
	
	$offset = empty($_GET['offset']) ? 0 : $_GET['offset'];
	$limit_sql = $wpdb->prepare(" LIMIT %d, 50 ", $offset);
	
	// filters
	$filter_sql = $filter_params = "";
	if(isset($_GET['cat_id']) and $_GET['cat_id']!= -1) {
		$filter_sql .= $wpdb->prepare(" AND Q.cat_id = %d ", $_GET['cat_id']);
		$filter_params .= "&cat_id=$_GET[cat_id]";
	}
	if(!empty($_GET['title'])) {
		$_GET['title'] = esc_sql($_GET['title']);
		$filter_sql .= " AND Q.name LIKE '%$_GET[title]%' ";
		$filter_params .= "&title=$_GET[title]";
	}
	
	$editor_sql = '';
	if($multiuser_access == 'own') $editor_sql = $wpdb->prepare(" AND Q.editor_id = %d", $user_ID);
	// handle access to all exams but with user group restrictions
	if($multiuser_access == 'group' or $multiuser_access == 'group_view') {		
		$cat_ids = WTPCategory::user_cats($user_ID);
		$cat_id_sql=implode(",",$cat_ids);
		$editor_sql = " AND Q.cat_id IN ($cat_id_sql) ";
	}	
	
	$count_sqls = ",(SELECT COUNT(ID) FROM ".WATUPRO_QUESTIONS." WHERE exam_id=Q.ID) AS question_count,
	(SELECT COUNT(ID) FROM ".WATUPRO_TAKEN_EXAMS." WHERE exam_id=Q.ID AND in_progress=0) AS taken";
	
	$low_memory_mode = get_option('watupro_low_memory_mode');
	if($low_memory_mode == 1) $count_sqls = '';
	
	$exams = $wpdb->get_results("SELECT SQL_CALC_FOUND_ROWS Q.*, tC.name as cat, tU.user_login as author
	$count_sqls
	FROM ".WATUPRO_EXAMS." AS Q LEFT JOIN ".WATUPRO_CATS." as tC ON tC.id=Q.cat_id
	LEFT JOIN {$wpdb->users} tU ON tU.ID = Q.editor_id
	WHERE Q.ID > 0 $filter_sql $editor_sql
	ORDER BY $ob $dir $limit_sql");
	
	$count = $wpdb->get_var("SELECT FOUND_ROWS()");
	
	// now select all posts that have watupro shortcode in them
	$posts=$wpdb->get_results("SELECT * FROM {$wpdb->posts} 
		WHERE post_content LIKE '%[watupro %]%'
		AND (post_status='publish' OR post_status='private')
		AND post_title!=''
		ORDER BY post_date DESC");	
		
	// match posts to exams
	foreach($exams as $cnt=>$exam) {
		foreach($posts as $post) {
			if(stristr($post->post_content,"[watupro ".$exam->ID."]")) {
				$exams[$cnt]->post=$post;			
				break;
			}
		}
	}

	// select exam categories
	$cats = $wpdb->get_results("SELECT * FROM ".WATUPRO_CATS." ORDER BY name");
	
	if(@file_exists(get_stylesheet_directory().'/watupro/exams.php')) require get_stylesheet_directory().'/watupro/exams.php';
	else require WATUPRO_PATH."/views/exams.php";
}

// open form to copy quiz
function watupro_copy_exam() {	
	global $wpdb, $user_ID;
	$multiuser_access = 'all';
	if(watupro_intel()) $multiuser_access = WatuPROIMultiUser::check_access('exams_access');
	$own_sql = ($multiuser_access == 'own') ? $wpdb->prepare(" AND editor_id=%d ", $user_ID) : "";
	
	$exam = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".WATUPRO_EXAMS." WHERE ID=%d", $_GET['id']));
	$grades = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".WATUPRO_GRADES." WHERE  exam_id=%d order by ID ", $exam->ID) );
	$questions = $wpdb->get_results($wpdb->prepare("SELECT cat_id, question, ID FROM ".WATUPRO_QUESTIONS." WHERE exam_id=%d ORDER BY sort_order, ID", $exam->ID));
	$cids = array(0);
	foreach($questions as $question) {
		if(!in_array($question->cat_id, $cids)) $cids[] = $question->cat_id;
	}
	$cidsql = implode(", ", $cids);
	
	// select question categories to group questions by cats
	$qcats = $wpdb->get_results("SELECT * FROM ".WATUPRO_QCATS." WHERE ID IN ($cidsql) ORDER BY name"); 
	// add Uncategorized
	$qcats[] = (object) array("ID"=>0, "name"=>__('Uncategorized', 'watupro'));
	
	$other_exams=$wpdb->get_results("SELECT * FROM ".WATUPRO_EXAMS." WHERE ID>0 $own_sql ORDER BY name");
	
	if(!empty($_POST['copy_exam'])) {		
		try {
			$copy_to=($_POST['copy_option']=='new')?0:$_POST['copy_to'];
			WTPExam::copy($exam->ID, $copy_to);
			$_SESSION['flash'] =__("The quiz was successfully copied!", 'watupro');
			$redirect = "admin.php?page=watupro_exams";
			if(@$_GET['comefrom'] == 'edit') $redirect = "admin.php?page=watupro_exam&quiz=".$_GET['id']."&action=edit";
			if(@$_GET['comefrom'] == 'questions') $redirect = "admin.php?page=watupro_questions&quiz=".$_GET['id'];
			watupro_redirect($redirect);
		}
		catch(Exception $e) {
			$error=$e->getMessage();
		}	 
	}
	
	if(@file_exists(get_stylesheet_directory().'/watupro/copy-exam-form.html.php')) require get_stylesheet_directory().'/watupro/copy-exam-form.html.php';
	else require WATUPRO_PATH."/views/copy-exam-form.html.php";
}

// replace title & meta tags on shared URLs
// called on template_redirect from init.php
function watupro_share_redirect() {
	global $post, $wpdb;
	
	if(empty($_GET['waturl'])) return false;
	
	$url = @base64_decode($_GET['waturl']); 
	list($exam_id, $tid) = explode("|", $url); 
	if(!is_numeric($exam_id) or !is_numeric($tid)) return false;
		
	// select taking
	$taking = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".WATUPRO_TAKEN_EXAMS." WHERE ID=%d", $tid));
	if(empty($taking->grade_id)) return false;
	
	// select exam
	$shareable = $wpdb->get_var($wpdb->prepare("SELECT shareable_final_screen FROM ".WATUPRO_EXAMS." WHERE ID=%d", $taking->exam_id)); 
	if(!$shareable) return false;
	
	// select grade
	$grade = $wpdb->get_row($wpdb->prepare("SELECT gtitle, gdescription FROM ".WATUPRO_GRADES." WHERE ID=%d", $taking->grade_id));
	
	$post->post_title = stripslashes($grade->gtitle);
	$post->post_excerpt = stripslashes($taking->result);
}

// display snippets for social sharing
// used to force G+ and LinkedIn to use proper content
function watupro_social_share_snippet() {
	global $post, $wpdb;
	
	if(empty($_GET['tid']) or empty($_GET['watupro_sssnippet'])) return false;
		
	// select taking
	$taking = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".WATUPRO_TAKEN_EXAMS." WHERE ID=%d", $_GET['tid']));
	
	// select exam and make sure social sharing buttons are there. If not,  redirect to the post
	$quiz = $wpdb->get_row($wpdb->prepare("SELECT name, final_screen FROM ".WATUPRO_EXAMS." WHERE ID=%d", $taking->exam_id));
	
	if(!strstr($quiz->final_screen, '[watuproshare-buttons')) watupro_redirect(get_permalinkg($_GET['redirect_to']));
	$quiz_name = $quiz->name;
			
	// select grade
	$grade = $wpdb->get_row($wpdb->prepare("SELECT gtitle, gdescription FROM ".WATUPRO_GRADES." WHERE ID=%d", $taking->grade_id));
	if(empty($grade->gtitle)) $grade = (object)array("gtitle"=>'None', 'gdescription'=>'None');	
	
	// try to get the image
	// this code repeats in the social-sharing.php controller, let's try to avoid this
	$target_image = '';
	if(strstr($grade->gdescription, '<img')) {
		// find all pictures in the grade descrption
		$html = stripslashes($grade->gdescription);
		$dom = new DOMDocument;
		$dom->loadHTML($html);
		$images = array();
		foreach ($dom->getElementsByTagName('img') as $image) {
		    $src =  $image->getAttribute('src');	
		    $class = $image->getAttribute('class');
		    $images[] = array('src'=>$src, 'class'=>$class);
		} // end foreach DOM element
		
		if(sizeof($images)) {
			$target_image = $images[0]['src'];
			
			// but check if we have any that are marked with the class
			foreach($images as $image) {
				if(strstr($image['class'], 'watupro-share')) {
					$target_image = $image['src'];
					break;
				}
			}
		}
	}   // end searching for image
	
 	// prepare open graph title & description - same for LinkedIn and Gplus 
	$linkedin = get_option('watuproshare_linkedin');
	$og_msg = stripslashes($linkedin['msg']);
	$og_title = stripslashes($linkedin['title']);
			
	// title and description set up?
	if(!empty($og_title)) {
		$og_title = str_replace('{{{grade-title}}}', stripslashes($grade->gtitle), $og_title);				
		$og_title = str_replace('{{{quiz-name}}}', stripslashes($quiz_name), $og_title);
	}
	if(!empty($og_msg)) {
		$og_msg = str_replace('{{{grade-title}}}', stripslashes($grade->gtitle), $og_msg);			
		$og_msg = str_replace('{{{grade-description}}}', stripslashes($grade->gdescription), $og_msg);	
		$og_msg = str_replace('{{{quiz-name}}}', stripslashes($quiz_name), $og_msg);
		$og_msg = str_replace('{{{url}}}', get_permalink($_POST['post_id']), $og_msg);
	}
	
	// if not, default to grade title and desc
	if(empty($og_title)) $og_title = $grade->gtitle;
	if(empty($og_msg)) $og_msg = $grade->gdescription;
	
	$og_title = stripslashes($og_title);
	$og_msg = stripslashes($og_msg);	
	
	$og_description = str_replace('"',"'",$og_msg);
	$og_description = str_replace(array("\n","\r")," ",$og_description);	
	$og_description = strip_tags($og_description);
	$og_title = str_replace('"',"'",$og_title);
	$og_title = str_replace(array("\n","\r")," ",$og_title);
	
	include(WATUPRO_PATH."/views/social-share-snippet.html.php");
	exit;
}

// auto publish quiz in post
// some data comes directly from the $_POST to save unnecessary DB query
function watupro_auto_publish($quiz_id) {	
	global $wpdb;
	// if the quiz has category try to match with post categories
	$post_cat_id=0;
	$cat_name = $wpdb->get_var($wpdb->prepare("SELECT tC.name FROM ".WATUPRO_CATS." tC
		JOIN ".WATUPRO_EXAMS." tE ON tE.cat_id=tC.ID 
		WHERE tE.ID=%d", $quiz_id));
	if(!empty($cat_name)) {
		$post_cat_id = get_cat_ID($cat_name);
	}

	$post = array('post_content' => '[watupro '.$quiz_id.']', 'post_name'=> $_POST['name'], 
		'post_title'=>$_POST['name'], 'post_status'=>'publish', 'post_category' => array($post_cat_id));
	wp_insert_post($post);
}

// in case the user has set some CSS properties in the settings page, let's generate onpage CSS
function watupro_onpage_css() {
	$ui = get_option('watupro_ui'); 
	
	$css = '';
	
	if(!empty($ui['question_spacing'])) {
		$css .= "div.watu-question, div.show-question {
			margin-bottom: ".$ui['question_spacing']."px !important;
		}\n";
	}
	
	if(!empty($ui['answer_spacing'])) {
		$css .= ".question-choices, .show-question-choices {
			margin-top: ".$ui['answer_spacing']."px !important;
		}\n";
	}
	
	if(!empty($css)) {		
		$css = "<style type='text/css'>
		/* Onpage CSS generated by WatuPRO */
		$css
		</style>";
		
		return $css;
	}
}