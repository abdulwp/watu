<?php
// inits the plugin - activate, menus etc

/// Initialize this plugin. Called by 'init' hook.
function watupro_init() {
	global $user_ID, $wpdb;
	if(get_option('watupro_debug_mode'))  {
		$wpdb->show_errors();
		if(!defined('DIEONDBERROR')) define( 'DIEONDBERROR', true );
	}
	
	require(WATUPRO_PATH."/helpers/htmlhelper.php");
	
	if (!session_id()) @session_start();
	
	// define table names
	define('WATUPRO_EXAMS', $wpdb->prefix."watupro_master");
	define('WATUPRO_TAKEN_EXAMS', $wpdb->prefix."watupro_taken_exams");
	define('WATUPRO_QUESTIONS', $wpdb->prefix."watupro_question");
	define('WATUPRO_STUDENT_ANSWERS', $wpdb->prefix."watupro_student_answers");
	define('WATUPRO_USER_CERTIFICATES', $wpdb->prefix."watupro_user_certificates");
	define('WATUPRO_CATS', $wpdb->prefix."watupro_cats");
	define('WATUPRO_QCATS', $wpdb->prefix."watupro_qcats");
	define('WATUPRO_GRADES', $wpdb->prefix."watupro_grading");
	define('WATUPRO_CERTIFICATES', $wpdb->prefix."watupro_certificates");
	define('WATUPRO_ANSWERS', $wpdb->prefix."watupro_answer");
	define('WATUPRO_GROUPS', $wpdb->prefix."watupro_groups");
	define('WATUPRO_DEPENDENCIES', $wpdb->prefix."watupro_dependencies");
	define('WATUPRO_PAYMENTS', $wpdb->prefix."watupro_payments");
	define('WATUPRO_USER_FILES', $wpdb->prefix."watupro_user_files");
	define('WATUPRO_BUNDLES', $wpdb->prefix."watupro_bundles"); // bundles of paid quizzes
	define('WATUPRO_COUPONS', $wpdb->prefix.'watupro_coupons'); // promo codes
	define('WATUPRO_UNLOCK_LEVELS', $wpdb->prefix.'watupro_unlock_levels'); // unlock difficulty level criteria
	define('WATUPRO_UNLOCK_LOGS', $wpdb->prefix.'watupro_unlock_logs'); // unlock difficulty level log
	
	// pagination definitions - let's use something more understandable than 0,1,2,3
	define('WATUPRO_PAGINATE_ONE_PER_PAGE', 0);
	define('WATUPRO_PAGINATE_ALL_ON_PAGE', 1);
	define('WATUPRO_PAGINATE_PAGE_PER_CATEGORY', 2);
	define('WATUPRO_PAGINATE_CUSTOM_NUMBER', 3);
    
	load_plugin_textdomain('watupro', false, WATUPRO_RELATIVE_PATH . '/languages/' );    
	
	// need to redirect the user?
	if(!empty($user_ID)) {
		$redirect=get_user_meta($user_ID, "watupro_redirect", true);		
		
		update_user_meta($user_ID, "watupro_redirect", "");
		
		if(!empty($redirect)) {
			 echo "<meta http-equiv='refresh' content='0;url=$redirect' />"; 
			 exit;
		}
	}	

    $manage_caps = current_user_can('manage_options')?'manage_options':'watupro_manage_exams';
    define('WATUPRO_MANAGE_CAPS', $manage_caps);
    
   $version = get_bloginfo('version');
   if($version <= 3.3 or get_option('watupro_always_load_scripts')=="1") add_action('wp_enqueue_scripts', 'watupro_vc_scripts');
	add_action('admin_enqueue_scripts', 'watupro_vc_scripts'); 
	add_action('wp_enqueue_scripts', 'watupro_vc_jquery');
	add_action('register_form','watupro_group_field'); // select user group
   
   add_shortcode( 'WATUPRO-LEADERBOARD', 'watupro_leaderboard' ); 
   add_shortcode( 'watupro-leaderboard', 'watupro_leaderboard' );
   add_shortcode( 'WATUPRO-MYEXAMS', 'watupro_myexams_code' );
   add_shortcode( 'watupro-myexams', 'watupro_myexams_code' );
	add_shortcode( 'WATUPRO-MYCERTIFICATES', 'watupro_mycertificates_code' );
	add_shortcode( 'watupro-mycertificates', 'watupro_mycertificates_code' );
	add_shortcode( 'WATUPROLIST', 'watupro_listcode' );
	add_shortcode( 'watuprolist', 'watupro_listcode' );
	add_shortcode( 'WATUPRO', 'watupro_shortcode' );
	add_shortcode( 'watupro', 'watupro_shortcode' );
	add_shortcode('watupro-userinfo', 'watupro_userinfo');
	add_shortcode('watuproshare-buttons', array('WatuPROSharing', 'display'));	
	add_shortcode('watupro-result', 'watupro_result');
	add_shortcode('watupro-basic-chart', 'watupro_basic_chart');
	add_shortcode('watupro-quiz-attempts', 'watupro_quiz_attempts');
	add_shortcode('watupro-takings', 'watupro_shortcode_takings');
	
	// prepare the custom filter on the content
	watupro_define_filters();
	
	// handle view certificate in new way - since version 3.7
	add_action('template_redirect', 'watupro_certificate_redirect');
	// replace title & meta tags on shared final screen
	add_action('template_redirect', 'watupro_share_redirect');
	add_action('template_redirect', 'watupro_social_share_snippet');
	// handle file downloads
	add_action('template_redirect', array('WatuPROFileHandler', 'download'));
	// delete user data when deleting the user
	add_action('deleted_user', array('WTPUser', 'auto_delete_data'));
	// handle unlocking difficulty levels 
	add_action('watupro_completed_exam', array('WatuPRODiffLevels', 'completed_exam'));
	
	// dashboard widget
	add_action('wp_dashboard_setup', array('WatuPROWidgets', 'widget'));
	
	if(watupro_intel() and !empty($_POST['stripe_bundle_pay'])) WatuPROPayment::Stripe(true); // process Stripe payment if any
	
	$design_theme = get_option('watupro_design_theme');
	if(empty($design_theme)) update_option('watupro_design_theme', 'default');
	
	add_action('admin_notices', 'watupro_admin_notices');	
	
	// Namaste! LMS integration filters and actions
	add_action('namaste-show-students-filter', 'watupro_namaste_show_students_filter');
	add_filter('namaste-students-filter', 'watupro_namaste_students_filter');
	
	$db_version=get_option("watupro_db_version");
	if($db_version < 4.81) watupro_activate(true);
} // end init()

// actual activation & installation
function watupro_activate($update = false) {
	global $wpdb;
	
	if(!$update) watupro_init();

	// Initial options.
	add_option('watupro_show_answers', 1);
	add_option('watupro_single_page', 0);
	add_option('watupro_answer_type', 'radio');    
	
   $wpdb->show_errors();
        
        // exams
        if($wpdb->get_var("SHOW TABLES LIKE '".WATUPRO_EXAMS."'") != WATUPRO_EXAMS) {  
            $sql = "CREATE TABLE `".WATUPRO_EXAMS."`(
						`ID` int(11) unsigned NOT NULL auto_increment,
						`name` varchar(255) NOT NULL DEFAULT '',
						`description` TEXT NOT NULL,
						`final_screen` TEXT NOT NULL,
						`added_on` datetime NOT NULL,
			         `is_active` TINYINT UNSIGNED NOT NULL DEFAULT '1',
			         `require_login` TINYINT UNSIGNED NOT NULL DEFAULT '0',
			         `take_again` TINYINT UNSIGNED NOT NULL DEFAULT '0', 
			         `email_taker` TINYINT UNSIGNED NOT NULL DEFAULT '0', 
			         `email_admin` TINYINT UNSIGNED NOT NULL DEFAULT '0', 
			         `randomize_questions` TINYINT UNSIGNED DEFAULT '0', 
			         `login_mode` VARCHAR(100) NOT NULL DEFAULT 'open',
			         `time_limit` INT UNSIGNED NOT NULL DEFAULT '0',
						`pull_random` INT UNSIGNED NOT NULL DEFAULT '0',
						`show_answers` VARCHAR(10) NOT NULL default '',
						`random_per_category` TINYINT UNSIGNED NOT NULL default 0,
						`group_by_cat` TINYINT UNSIGNED NOT NULL DEFAULT 0,
						`num_answers` INT UNSIGNED NOT NULL DEFAULT 0,
						`single_page` TINYINT UNSIGNED NOT NULL DEFAULT 0,
						`cat_id` INT UNSIGNED NOT NULL DEFAULT 0,
						PRIMARY KEY  (ID)
					) CHARACTER SET utf8;";
            $wpdb->query($sql);   
        }    
        
        // questions
        if($wpdb->get_var("SHOW TABLES LIKE '".WATUPRO_QUESTIONS."'") != WATUPRO_QUESTIONS) {  
            $sql = "CREATE TABLE `".WATUPRO_QUESTIONS."` (
							ID int(11) unsigned NOT NULL auto_increment,
							exam_id int(11) unsigned NOT NULL DEFAULT '0',
							question mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
							answer_type char(15) COLLATE utf8_unicode_ci NOT NULL default '',
							sort_order int(3) NOT NULL default 0,
							cat_id INT UNSIGNED NOT NULL DEFAULT 0,
							random_per_category TINYINT UNSIGNED NOT NULL DEFAULT 0,
							explain_answer TEXT,
							PRIMARY KEY  (ID),
							KEY quiz_id (exam_id)
						) CHARACTER SET utf8;";
            $wpdb->query($sql);    
        }    
        
        // answers
        if($wpdb->get_var("SHOW TABLES LIKE '".WATUPRO_ANSWERS."'") != WATUPRO_ANSWERS) {  
            $sql = "CREATE TABLE `".WATUPRO_ANSWERS."` (
						ID int(11) unsigned NOT NULL auto_increment,
						question_id int(11) unsigned NOT NULL default '0',
						answer TEXT NOT NULL,
						correct enum('0','1') NOT NULL default '0',
						point DECIMAL(6,2) DEFAULT '0.00',
						sort_order int(3) NOT NULL default 0,
						PRIMARY KEY  (ID)
					) CHARACTER SET utf8;";
            $wpdb->query($sql);         
        }  
        
		// grades
		if($wpdb->get_var("SHOW TABLES LIKE '".WATUPRO_GRADES."'") != WATUPRO_GRADES) {  
            $sql = "CREATE TABLE `".WATUPRO_GRADES."` (
				 `ID` int(11) NOT NULL AUTO_INCREMENT,
				 `exam_id` int(11) NOT NULL default 0,
				 `gtitle` varchar (255) NOT NULL default '',
				 `gdescription` mediumtext COLLATE utf8_unicode_ci NOT NULL,
				 `gfrom` DECIMAL(10,2) NOT NULL default '0.00',
				 `gto` DECIMAL(10,2) NOT NULL default '0.00',
				 `certificate_id` INT UNSIGNED NOT NULL default 0,
				 PRIMARY KEY (`ID`)
				) CHARACTER SET utf8";
            $wpdb->query($sql);             
        }   
        
        // taken exams
        if($wpdb->get_var("SHOW TABLES LIKE '".WATUPRO_TAKEN_EXAMS."'") != WATUPRO_TAKEN_EXAMS) {  
            $sql = "CREATE TABLE `".WATUPRO_TAKEN_EXAMS."` (
				  	`ID` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
	            `user_id` INT UNSIGNED NOT NULL ,
	            `exam_id` INT UNSIGNED NOT NULL ,
	            `date` DATE NOT NULL ,
	            `points` DECIMAL(6,2) NOT NULL DEFAULT '0.00',
	            `details` MEDIUMTEXT NOT NULL ,
	            `result` TEXT NOT NULL ,
	            `start_time` DATETIME NOT NULL DEFAULT '2000-01-01 00:00:00',
				  `ip` VARCHAR(20) NOT NULL,
				  `in_progress` TINYINT UNSIGNED NOT NULL default 0
				) CHARACTER SET utf8";
            $wpdb->query($sql);             
        }   
        
        // links to taken_exams
        if($wpdb->get_var("SHOW TABLES LIKE '".WATUPRO_STUDENT_ANSWERS."'") != WATUPRO_STUDENT_ANSWERS) {  
            $sql = "CREATE TABLE `".WATUPRO_STUDENT_ANSWERS."` (
				  `ID` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
                  `user_id` INT UNSIGNED NOT NULL default 0, 
                  `exam_id` INT UNSIGNED NOT NULL default 0,
                  `taking_id` INT UNSIGNED NOT NULL default 0,
                  `question_id` INT UNSIGNED NOT NULL default 0,
                  `answer` TEXT NOT NULL,
				  `points` DECIMAL(6,2) NOT NULL default '0.00',
				  `question_text` TEXT  NOT NULL
				) CHARACTER SET utf8";
            $wpdb->query($sql);              
        }

		// certificates
        if($wpdb->get_var("SHOW TABLES LIKE '".WATUPRO_CERTIFICATES."'") != WATUPRO_CERTIFICATES) {  
            $sql = "CREATE TABLE `".WATUPRO_CERTIFICATES."` (
				  `ID` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
				  `title` VARCHAR(255) NOT NULL default '', 
           		`html` LONGTEXT NOT NULL 
				) CHARACTER SET utf8";
            $wpdb->query($sql);         
        }
       
      // question categories
      if($wpdb->get_var("SHOW TABLES LIKE '".WATUPRO_QCATS."'") != WATUPRO_QCATS) {  
            $sql = "CREATE TABLE `".WATUPRO_QCATS."` (
				  `ID` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
				  `name` VARCHAR(255) NOT NULL default ''
				) CHARACTER SET utf8";
            $wpdb->query($sql);         
      } 
      
      // exam categories
      if($wpdb->get_var("SHOW TABLES LIKE '".WATUPRO_CATS."'") != WATUPRO_CATS) {  
            $sql = "CREATE TABLE `".WATUPRO_CATS."` (
				  `ID` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
				  `name` VARCHAR(255) NOT NULL DEFAULT '',
				  `ugroups` VARCHAR(255) NOT NULL DEFAULT ''
				) CHARACTER SET utf8";
            $wpdb->query($sql);         
      } 
		      
      // user groups - optionally user can have a group
      if($wpdb->get_var("SHOW TABLES LIKE '".WATUPRO_GROUPS."'") != WATUPRO_GROUPS) {  
            $sql = "CREATE TABLE `".WATUPRO_GROUPS."` (
				  `ID` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
				  `name` VARCHAR(255) NOT NULL DEFAULT '',
				  `is_def` TINYINT UNSIGNED NOT NULL DEFAULT 0
				) CHARACTER SET utf8";
            $wpdb->query($sql);         
      }
      
      // keep track about user's certificates
      if($wpdb->get_var("SHOW TABLES LIKE '".WATUPRO_USER_CERTIFICATES."'") != WATUPRO_USER_CERTIFICATES) {  
            $sql = "CREATE TABLE `".WATUPRO_USER_CERTIFICATES."` (
						  `ID` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
						  `user_id` INT UNSIGNED NOT NULL default 0,
						  `certificate_id` INT UNSIGNED NOT NULL default 0
						) CHARACTER SET utf8";
            $wpdb->query($sql);              
      }     
      
      // files uploaded as user answers
      // keep track about user's certificates
      if($wpdb->get_var("SHOW TABLES LIKE '".WATUPRO_USER_FILES."'") != WATUPRO_USER_FILES) {  
            $sql = "CREATE TABLE `".WATUPRO_USER_FILES."` (
						  `ID` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
						  `user_id` INT UNSIGNED NOT NULL default 0,
						  `taking_id` INT UNSIGNED NOT NULL default 0,
						  `user_answer_id` INT UNSIGNED NOT NULL default 0,
						  `filename` VARCHAR(255) NOT NULL default '',
						  `filesize` INT UNSIGNED NOT NULL default 0 /* size in  KB */,
						  `filetype` VARCHAR(50) NOT NULL default '',
						  `filecontents` LONGBLOB
						) CHARACTER SET utf8";
            $wpdb->query($sql);              
      }      
      
       // intelligence tables
      if(watupro_intel()) { 
      	// exam dependencies
	      if($wpdb->get_var("SHOW TABLES LIKE '".WATUPRO_DEPENDENCIES."'") != WATUPRO_DEPENDENCIES) {  
	            $sql = "CREATE TABLE `".WATUPRO_DEPENDENCIES."` (
					  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
					  `exam_id` int(10) unsigned NOT NULL default 0,
					  `depend_exam` int(10) unsigned NOT NULL default 0,
					  `depend_points` int(11) NOT NULL default 0,
					  `mode` VARCHAR(100) NOT NULL DEFAULT 'points',
					  PRIMARY KEY (`ID`)
					) CHARACTER SET utf8";
	        $wpdb->query($sql);           
	      }
	       
	      // exam fee payments
	      if($wpdb->get_var("SHOW TABLES LIKE '".WATUPRO_PAYMENTS."'") != WATUPRO_PAYMENTS) {  
	            $sql = "CREATE TABLE `".WATUPRO_PAYMENTS."` (
					  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
					  `exam_id` int(10) unsigned NOT NULL default 0,
					  `user_id` int(10) unsigned NOT NULL default 0,
					  `date` DATE NOT NULL,
					  `amount` DECIMAL(8,2) NOT NULL default '0.00',
					  `status` VARCHAR(100) NOT NULL default '',
					  `paycode` VARCHAR(100) NOT NULL default '',
					  PRIMARY KEY (`ID`)
					) CHARACTER SET utf8";
	          $wpdb->query($sql);       
	            
	            // add also the USD option by default
					update_option("watupro_currency", "USD");         
	      } 
	      
	      // bundles of paid quizzes - will be stored in the DB so we can check the price
	      // will generate shortcodes for publishing payment buttons
	      if($wpdb->get_var("SHOW TABLES LIKE '".WATUPRO_BUNDLES."'") != WATUPRO_BUNDLES) {  
	            $sql = "CREATE TABLE `".WATUPRO_BUNDLES."` (
					  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
					  `price` DECIMAL(8,2),
					  `bundle_type` VARCHAR(100) NOT NULL DEFAULT 'quizzes', /* quizzes or category */
					  `quiz_ids` VARCHAR(255) NOT NULL DEFAULT '',
					  `cat_id` INT UNSIGNED NOT NULL DEFAULT 0,  
					  PRIMARY KEY (`ID`)
					) CHARACTER SET utf8";
	        $wpdb->query($sql);           
	      }
	      
	      // promo codes table
		   if($wpdb->get_var("SHOW TABLES LIKE '".WATUPRO_COUPONS."'") != WATUPRO_COUPONS) {  
            $sql = "CREATE TABLE `".WATUPRO_COUPONS."`(
						`ID` int(11) unsigned NOT NULL auto_increment,
						`discount` TINYINT UNSIGNED NOT NULL DEFAULT 0,
						`code` VARCHAR(100) NOT NULL DEFAULT '',
						`num_uses` INT UNSIGNED NOT NULL DEFAULT 0,
						`times_used` INT UNSIGNED NOT NULL DEFAULT 0,
						PRIMARY KEY  (ID)
					) CHARACTER SET utf8;";
            $wpdb->query($sql);   
        }    
        
        // unock difficulty levels criteria
		   if($wpdb->get_var("SHOW TABLES LIKE '".WATUPRO_UNLOCK_LEVELS."'") != WATUPRO_UNLOCK_LEVELS) {  
            $sql = "CREATE TABLE `".WATUPRO_UNLOCK_LEVELS."`(
						`ID` int(11) unsigned NOT NULL auto_increment,
						`unlock_level` VARCHAR(255) NOT NULL DEFAULT '',
						`min_points` INT UNSIGNED NOT NULL DEFAULT 0,
						`min_questions` INT UNSIGNED NOT NULL DEFAULT 0,
						`percent_correct` TINYINT UNSIGNED DEFAULT 0,
						`from_level` VARCHAR(255) NOT NULL DEFAULT '',
						PRIMARY KEY  (ID)
					) CHARACTER SET utf8;";
            $wpdb->query($sql);   
        }    
        
        // log for unlocked difficulty levels        
		   if($wpdb->get_var("SHOW TABLES LIKE '".WATUPRO_UNLOCK_LOGS."'") != WATUPRO_UNLOCK_LOGS) {  
            $sql = "CREATE TABLE `".WATUPRO_UNLOCK_LOGS."`(
						`ID` int(11) unsigned NOT NULL auto_increment,
						`unlocked_level` VARCHAR(255) NOT NULL DEFAULT '',
						`user_id` INT UNSIGNED NOT NULL DEFAULT 0,
						`taking_id` INT UNSIGNED NOT NULL DEFAULT 0,
						PRIMARY KEY  (ID)
					) CHARACTER SET utf8;";
            $wpdb->query($sql);   
        }    
	      
	      watupro_add_db_fields(array(
      		array("name"=>"method", "type"=>"VARCHAR(100) NOT NULL DEFAULT 'paypal'"),
      		array("name"=>"bundle_id", "type"=>"INT UNSIGNED NOT NULL DEFAULT 0"),
      		array("name"=>"access_code", "type"=>"VARCHAR(20) NOT NULL DEFAULT ''"), /* if user is not logged in */					
			), WATUPRO_PAYMENTS); 
			
			 watupro_add_db_fields(array(
      		array("name"=>"redirect_url", "type"=>"VARCHAR(255) NOT NULL DEFAULT ''"),      							
			), WATUPRO_BUNDLES); 
			
			watupro_add_db_fields(array(
      		array("name"=>"disc_type", "type"=>"VARCHAR(20) NOT NULL DEFAULT 'percent'"),      				
      		array("name"=>"quiz_id", "type"=>"INT UNSIGNED NOT NULL DEFAULT 0"),
      		array("name"=>"date_condition", "type"=>"TINYINT UNSIGNED NOT NULL DEFAULT 0"),
      		array("name"=>"start_date", "type"=>"DATE"),
      		array("name"=>"end_date", "type"=>"DATE"),
			), WATUPRO_COUPONS); 
	   }
       
		# $wpdb->print_error();				
		update_option( "watupro_delete_db", '' );
        
      // add student role if not exists
      // most probably this is no longer required
      $res = add_role('student', 'Student', array(
            'read' => true, // True allows that capability
            'watupro_exams' => true));   
            
      // database upgrades - version 1.1
      $db_version=get_option("watupro_db_version");
      
      watupro_add_db_fields(array(      		
					array("name"=>"end_time", "type"=>"DATETIME NOT NULL DEFAULT '2000-01-01 00:00:00'"),
					array("name"=>"grade_id", "type"=>"INT UNSIGNED NOT NULL default 0"),
					array("name"=>"percent_correct", "type"=>"TINYINT UNSIGNED NOT NULL default 0"),
					array("name"=>"email", "type"=>"VARCHAR(255) NOT NULL DEFAULT ''"),					
					array("name"=>"catgrades", "type"=>"TEXT"),
					array("name"=>"catgrades_serialized", "type"=>"TEXT"),
					array("name"=>"serialized_questions", "type"=>"TEXT"),
					array("name"=>"num_hints_used", "type"=>"SMALLINT UNSIGNED NOT NULL DEFAULT 0"),
					array("name"=>"name", "type"=>"VARCHAR(100) NOT NULL DEFAULT ''"),
					array("name"=>"contact_data", "type"=>"TEXT"),
					array("name"=>"marked_for_review", "type"=>"TEXT"),
					array("name"=>"field_company", "type"=>"VARCHAR(255) NOT NULL DEFAULT ''"),
					array("name"=>"field_phone", "type"=>"VARCHAR(255) NOT NULL DEFAULT ''"),
					array("name"=>"percent_points", "type"=>"TINYINT UNSIGNED NOT NULL default 0"),
				), WATUPRO_TAKEN_EXAMS);
	
		 watupro_add_db_fields(array(   		
   		array("name"=>"times_to_take", "type"=>"SMALLINT UNSIGNED NOT NULL DEFAULT 0"),
   		array("name"=>"mode", "type"=>"VARCHAR(100) DEFAULT 'live'"),
			array("name"=>"fee", "type"=>"DECIMAL(8,2) NOT NULL DEFAULT '0.00'"),
			array("name"=>"require_captcha", "type"=>"TINYINT NOT NULL DEFAULT '0'"),
			array("name"=>"grades_by_percent", "type"=>"TINYINT NOT NULL DEFAULT '0'"),
			array("name"=>"admin_email", "type"=>"VARCHAR(255) NOT NULL default ''"),
			array("name"=>"disallow_previous_button", "type"=>"TINYINT UNSIGNED NOT NULL default 0"),
			array("name"=>"email_output", "type"=>"TEXT"),
			array("name"=>"live_result", "type"=>"TINYINT UNSIGNED NOT NULL DEFAULT 0"),
			array("name"=>"gradecat_design", "type"=>"TEXT"),
			array("name"=>"is_scheduled", "type"=>"TINYINT UNSIGNED NOT NULL DEFAULT 0"),
      	array("name"=>"schedule_from", "type"=>"DATETIME"),
      	array("name"=>"schedule_to", "type"=>"DATETIME"),
      	array("name"=>"submit_always_visible", "type"=>"TINYINT UNSIGNED NOT NULL DEFAULT 0"),
      	array("name"=>"retake_grades", "type"=>"TEXT"),
      	array("name"=>"show_pagination", "type"=>"TINYINT UNSIGNED NOT NULL DEFAULT 0"),
      	array("name"=>"advanced_settings", "type"=>"TEXT"), /* this will be serialized array */
      	array("name"=>"enable_save_button", "type"=>"TINYINT UNSIGNED NOT NULL DEFAULT 0"),
      	array("name"=>"shareable_final_screen", "type"=>"TINYINT UNSIGNED NOT NULL DEFAULT 0"),
      	array("name"=>"redirect_final_screen", "type"=>"TINYINT UNSIGNED NOT NULL DEFAULT 0"),
      	array("name"=>"editor_id", "type"=>"INT UNSIGNED NOT NULL DEFAULT 0"),
      	array("name"=>"question_hints", "type"=>"VARCHAR(10) NOT NULL DEFAULT ''"), /* a string like 1|10|3 */
      	array("name"=>"takings_by_ip", "type"=>"INT UNSIGNED NOT NULL DEFAULT 0"),
      	array("name"=>"reuse_default_grades", "type"=>"TINYINT UNSIGNED NOT NULL DEFAULT 0"),		
      	array("name"=>"store_progress", "type"=>"TINYINT UNSIGNED NOT NULL DEFAULT 0"), /*store progress on the fly (when 1 question per page)*/
      	array("name"=>"custom_per_page", "type"=>"SMALLINT UNSIGNED NOT NULL DEFAULT 0"), /* when paginated custom number per page */
      	array("name"=>"randomize_cats", "type"=>"TINYINT UNSIGNED NOT NULL DEFAULT 0"),
      	array("name"=>"no_ajax", "type"=>"TINYINT UNSIGNED NOT NULL DEFAULT 0"),
      	array("name"=>"email_subject", "type"=>"VARCHAR(255) NOT NULL default ''"),
      	array("name"=>"pay_always", "type"=>"TINYINT UNSIGNED NOT NULL DEFAULT 0"), /* whether to pay once for each quiz attempt */
      	array("name" => "reuse_questions_from", "type" => "VARCHAR(255) NOT NULL DEFAULT ''"), /* This exists here and in the Intelligence module */
      	array("name"=>"published_odd", "type"=>"TINYINT UNSIGNED NOT NULL DEFAULT 0"), /* Published in non-standard way*/
      	array("name"=>"published_odd_url", "type"=>"VARCHAR(255) NOT NULL default ''"), /* The URL where it's publushed in non-srandard way */
		), WATUPRO_EXAMS);	
				
			 watupro_add_db_fields(array(
	    		array("name"=>"is_required", "type"=>"TINYINT UNSIGNED NOT NULL DEFAULT 0"),
	    		array("name"=>"correct_condition", "type"=>"VARCHAR(255) NOT NULL DEFAULT ''"),
	    		array("name"=>"max_selections", "type"=>"TINYINT UNSIGNED NOT NULL DEFAULT 0"),
	    		array("name"=>"is_inactive", "type"=>"TINYINT UNSIGNED NOT NULL DEFAULT 0"),
	    		array("name" => "is_survey", "type"=>"TINYINT UNSIGNED NOT NULL DEFAULT 0"),
	    		array("name" => "elaborate_explanation", "type"=>"VARCHAR(100) NOT NULL DEFAULT ''"),
	    		array("name" => "open_end_mode", "type" => "VARCHAR(255) NOT NULL DEFAULT ''"),
	    		array("name" => "tags", "type" => "VARCHAR(255) NOT NULL DEFAULT ''"),
				array("name" => "open_end_display", "type" => "VARCHAR(255) NOT NULL DEFAULT 'medium'"),
				array("name" => "exclude_on_final_screen", "type" => "TINYINT UNSIGNED NOT NULL DEFAULT 0"),
				array("name" => "hints", "type" => "TEXT"),
				array("name" => "compact_format", "type" => "TINYINT UNSIGNED NOT NULL DEFAULT 0"),
				array("name" => "round_points", "type" => "TINYINT UNSIGNED NOT NULL DEFAULT 0"),
				array("name" => "importance", "type" => "TINYINT UNSIGNED DEFAULT 0"), /* when 100, always include */ 
				array("name" => "calculate_whole", "type" => "TINYINT UNSIGNED DEFAULT 0"), /* whether to treat (sorting) question is a whole when calculating points */
				array("name" => "unanswered_penalty", "type" => "DECIMAL(8,2) NOT NULL DEFAULT '0.00'"), /*penalize not-answering with negative points*/
				array("name" => "truefalse", "type" => "TINYINT NOT NULL DEFAULT 0"), /* is this True/False subtype? */
				array("name" => "accept_feedback", "type" => "TINYINT NOT NULL DEFAULT 0"), /* accept user feedback */
				array("name" => "feedback_label", "type" => "VARCHAR(100) NOT NULL DEFAULT ''"), /* The text before the feedback box */
				array("name" => "reward_only_correct", "type" => "TINYINT NOT NULL DEFAULT 0"), /* reward positive points only when the answer is correct */
				array("name" => "discard_even_negative", "type" => "TINYINT NOT NULL DEFAULT 0"), /* reward ANY points only when the answer is correct */
				array("name" => "difficulty_level", "type" => "VARCHAR(100) NOT NULL DEFAULT ''"), 
				array("name" => "num_columns", "type" => "TINYINT NOT NULL DEFAULT 0"), // num columns for the choices 1 to 4
			), WATUPRO_QUESTIONS);
			
			watupro_add_db_fields(array(	    		
	    		array("name"=>"require_approval", "type"=>"TINYINT UNSIGNED NOT NULL default 0"),
	    		array("name"=>"require_approval_notify_admin", "type"=>"TINYINT UNSIGNED NOT NULL default 0"),
	    		array("name"=>"approval_notify_user", "type"=>"TINYINT UNSIGNED NOT NULL default 0"),
	    		array("name"=>"approval_email_subject", "type"=>"VARCHAR(255) NOT NULL default ''"),
	    		array("name"=>"approval_email_message", "type"=>"TEXT"),
	    		array("name"=>"editor_id", "type"=>"INT UNSIGNED NOT NULL DEFAULT 0"),
	    		array("name"=>"is_multi_quiz", "type"=>"TINYINT UNSIGNED NOT NULL DEFAULT 0"),	   	    		
	    		array("name"=>"quiz_ids", "type"=>"VARCHAR(255) NOT NULL DEFAULT ''"), /* string like |1|2|3| when is_multi_quiz is 1 */
	    		array("name"=>"avg_points", "type"=>"INT UNSIGNED NOT NULL DEFAULT 0"), /* avg. points per taking required when multi_quiz is 1 */
	    		array("name"=>"avg_percent", "type"=>"TINYINT UNSIGNED NOT NULL DEFAULT 0"), /* avg. percent per taking required when multi_quiz is 1 */	   	    		
			), WATUPRO_CERTIFICATES);
			
			watupro_add_db_fields(array(
	    		array("name"=>"exam_id", "type"=>"INT UNSIGNED NOT NULL default 0"),
	    		array("name"=>"taking_id", "type"=>"INT UNSIGNED NOT NULL default 0"),
	    		array("name"=>"pending_approval", "type"=>"TINYINT UNSIGNED NOT NULL default 0"),
	    		array("name"=>"pdf_output", "type"=>"LONGBLOB"), /* currently Docraptor, to avoid multiple requests*/
	    		array("name"=>"public_access", "type"=>"TINYINT UNSIGNED NOT NULL DEFAULT 0"),
	    		array("name"=>"email", "type"=>"VARCHAR(100) NOT NULL DEFAULT ''"), /* will be filled only for non-users */
	    		array("name"=>"quiz_ids", "type"=>"VARCHAR(255) NOT NULL DEFAULT ''"), /* string like |1|2|3| when is_multi_quiz is 1 */
	    		array("name"=>"avg_points", "type"=>"INT UNSIGNED NOT NULL DEFAULT 0"), /* avg. points per taking achieved when multi_quiz is 1 */
	    		array("name"=>"avg_percent", "type"=>"TINYINT UNSIGNED NOT NULL DEFAULT 0"), /* avg. percent per taking achieved when multi_quiz is 1 */	   
			), WATUPRO_USER_CERTIFICATES);
			
			watupro_add_db_fields(array(
    			array("name"=>"description", "type"=>"TEXT NOT NULL"),
    			array("name"=>"editor_id", "type"=>"INT UNSIGNED NOT NULL DEFAULT 0"),
    			array("name"=>"parent_id", "type"=>"INT UNSIGNED NOT NULL DEFAULT 0"),
    			array("name"=>"exclude_from_reports", "type"=>"TINYINT UNSIGNED NOT NULL DEFAULT 0"), /* Exclude from reports and exports */
			), WATUPRO_QCATS);
			
			watupro_add_db_fields(array(
    			array("name"=>"editor_id", "type"=>"INT UNSIGNED NOT NULL DEFAULT 0"),
			), WATUPRO_CATS);
			
			watupro_add_db_fields(array(
    			array("name"=>"cat_id", "type"=>"INT UNSIGNED NOT NULL DEFAULT 0"), // question category ID
    			array("name"=>"editor_id", "type"=>"INT UNSIGNED NOT NULL DEFAULT 0"), // used only for default grades
    			array("name"=>"percentage_based", "type"=>"INT UNSIGNED NOT NULL DEFAULT 0"), // used only for default grades
    			array("name"=>"redirect_url", "type"=>"VARCHAR(255) NOT NULL DEFAULT ''"), 
			), WATUPRO_GRADES);
			
			watupro_add_db_fields(array(
    			array("name"=>"is_correct", "type"=>"TINYINT UNSIGNED NOT NULL DEFAULT 0"),
				array("name"=>"snapshot", "type"=>"TEXT NOT NULL"),
				array("name"=>"hints_used", "type"=>"TEXT"),
				array("name"=>"num_hints_used", "type"=>"SMALLINT UNSIGNED NOT NULL DEFAULT 0"),
				array("name"=>"onpage_question_num", "type"=>"SMALLINT UNSIGNED NOT NULL DEFAULT 0"), /* use to return on the last page when returning to complete quiz with $in_progress*/
				array("name"=>"timestamp", "type"=>"TIMESTAMP"),
				array("name"=>"feedback", "type"=>"TEXT"),
				array("name"=>"rating", "type"=>"TINYINT UNSIGNED NOT NULL DEFAULT 0"),
			), WATUPRO_STUDENT_ANSWERS);
			
			watupro_add_db_fields(array(
				array('name' => 'explanation', 'type' => 'TEXT'),
				array("name"=>"grade_id", "type"=>"VARCHAR(255) NOT NULL DEFAULT '0' COMMENT 'Used only in personality quizzes' "),			
			), WATUPRO_ANSWERS);
			
			
			// db updates 3.0
			if(empty($db_version) or $db_version<3) {
				$sql = "ALTER TABLE ".WATUPRO_ANSWERS." CHANGE `point` `point` DECIMAL(6,2) DEFAULT '0.00'";
				$wpdb->query($sql);
			}
			
			// db updates 3.4
			if(empty($db_version) or $db_version<3.41) {
				$sql = "ALTER TABLE ".WATUPRO_EXAMS." CHANGE `name` `name` VARCHAR(255) DEFAULT ''";
				$wpdb->query($sql);
			}
			
			// Intelligence specific fields
			if(watupro_intel()) {
				 require_once(WATUPRO_PATH."/i/models/i.php");
				 WatuPROIntelligence::activate();
			}
			
			// add indexes
			$index_taken_exams = $wpdb->get_var("SHOW INDEX FROM ".WATUPRO_TAKEN_EXAMS." WHERE KEY_NAME = 'user_id'");
			if(empty($index_taken_exams)) {
				$wpdb->query("ALTER TABLE ".WATUPRO_TAKEN_EXAMS." ADD INDEX user_id (user_id),
					ADD INDEX exam_id (exam_id), ADD INDEX points (points), ADD INDEX ip (ip),
					ADD INDEX grade_id (grade_id), ADD INDEX percent_correct (percent_correct),
					ADD INDEX date (date)");
			}
			$index_student_answers = $wpdb->get_var("SHOW INDEX FROM ".WATUPRO_STUDENT_ANSWERS." WHERE KEY_NAME = 'user_id'");
			if(empty($index_student_answers)) {
				$wpdb->query("ALTER TABLE ".WATUPRO_STUDENT_ANSWERS." ADD INDEX user_id (user_id),
					ADD INDEX exam_id (exam_id), ADD INDEX taking_id (taking_id), ADD INDEX question_id (question_id)");
			}
			$index_questions = $wpdb->get_var("SHOW INDEX FROM ".WATUPRO_QUESTIONS." WHERE KEY_NAME = 'exam_id'");
			if(empty($index_questions)) {
				$wpdb->query("ALTER TABLE ".WATUPRO_QUESTIONS." ADD INDEX exam_id (exam_id), ADD INDEX cat_id (cat_id), 
					ADD INDEX is_inactive (is_inactive), ADD INDEX is_survey (is_survey)");
			}
			
			// change pdf_output field
			if($db_version < 4) {
				$wpdb->query("ALTER TABLE ".WATUPRO_USER_CERTIFICATES." CHANGE pdf_output pdf_output LONGBLOB");
			}
			
			if($db_version < 4.121) {
				$wpdb->query("ALTER TABLE ".WATUPRO_TAKEN_EXAMS." CHANGE details details MEDIUMTEXT");
			}
			
			if($db_version < 4.13) {
				$wpdb->query("ALTER TABLE ".WATUPRO_GRADES." CHANGE gfrom gfrom DECIMAL(8,2) NOT NULL DEFAULT '0.00', 
					CHANGE gto gto DECIMAL(8,2) NOT NULL DEFAULT '0.00'");
			}
			
			// once update all old quizzes with "store_progress" => true
			if($db_version < 4.15) {
				$wpdb->query("UPDATE ".WATUPRO_EXAMS." SET store_progress=1");
			}
			
			// multiple personality grades
			// once update all old quizzes with "store_progress" => true
			if($db_version < 4.3) {
				$wpdb->query("ALTER TABLE ".WATUPRO_ANSWERS." CHANGE grade_id grade_id VARCHAR(255) NOT NULL DEFAULT '0'");
			}
			
			// default CSV separator if not set
			if(get_option('watupro_csv_delim') == '') {
				update_option('watupro_csv_delim', ',');
				update_option('watupro_csv_quotes', '1');
			}
      	
      // set current DB version
      update_option("watupro_db_version", 4.81);
}

// assign the role - is this at all needed anymore? Check.
function watupro_register_role($user_id, $password="", $meta=array()) {
   $userdata = array();
   $userdata['ID'] = $user_id;
   $userdata['role'] = @$_POST['role'];

   //only allow if user role is my_role
   if (@$userdata['role'] == "student"){
      wp_update_user($userdata);
   }
   
   // also update redirection so we can go back to the exam after login
   if(!empty($_POST['redirect_to'])) {
   	update_user_meta($user_id, "watupro_redirect", $_POST['redirect_to']);
   }
}

// output role field
function watupro_role_field() {
    // thanks to http://www.jasarwebsolutions.com/2010/06/27/how-to-change-a-users-role-on-the-wordpress-registration-form/
    ?>
    <input id="role" type="hidden" tabindex="20" size="25" value="student"  name="role" />
    <input id="role" type="hidden" tabindex="20" size="25" value="student"  name="redirect_to" value="<?php echo $_GET['redirect_to']?>" />
    <?php
}

// add settings link in the plugins page
function watupro_plugin_action_links($links, $file) {		
	if ( strstr($file, "watupro/" )) {
		$settings_link = '<a href="admin.php?page=watupro_options">' . __( 'Settings', 'watupro' ) . '</a>';
		array_unshift( $links, $settings_link );
	}
	return $links;
}

/**
 * Add jQuery Validation script on posts.
 */
function watupro_vc_scripts() {    
    wp_enqueue_script('jquery');
        
    wp_enqueue_style(
			'watupro-style',
			WATUPRO_URL.'style.css',
			array(),
			'4.8');
		
		wp_enqueue_script(
			'watupro-script',
			WATUPRO_URL.'lib/main.js',
			array(),
			'4.8.5');
			
		$translation_array = array('answering_required' => __('Answering this question is required', 'watupro'),
			'did_not_answer' => __('You did not answer the question. Are you sure you want to continue?', 'watupro'),
			'missed_required_question' => __('You have not chosen a selection for one or more questions. Please respond to every question so you can receive your results.', 'watupro'),
			'please_wait' => __('Please wait...', 'watupro'),
			'try_again' => __('Try again', 'watupro'),
			'time_over' => __("Sorry, your time is over! I'm submitting your results... Done!", 'watupro'),
			'seconds' => __('seconds', 'watupro'),
			'minutes_and' => __('minutes and', 'watupro'),
			'hours' => __('hours,', 'watupro'),
			'time_left' => __('Time left:', 'watupro'),
			'email_required' => __('Please enter your email address', 'watupro'),
			'name_required' => __('Please enter your name', 'watupro'),
			'field_required' => __('This field is required', 'watupro'),
			'not_last_page' => __('You are not on the last page. Are you sure you want to submit the quiz?', 'watupro'),
			'please_answer' => __('Please first answer the question', 'watupro'),
			'selections_saved' => __('Your work has been saved. You can come later to continue.', 'watupro'),
			'confirm_submit' => __('Are you sure you want to submit the quiz?', 'watupro'),
			'taking_details' => __('Details of submitted quiz', 'watupro'),
			'questions_pending_review' => __('The following questions have been flagged for review: %s. Are you sure you want to submit your results? Click OK to submit and Cancel to go back and review these questions.', 'watupro'),
			'ajax_url' => admin_url('admin-ajax.php'), 
			'complete_captcha' => __('You need to enter the image verification code', 'watupro'),	
			'complete_text_captcha' => __('You need to answer the verification question', 'watupro'),
			'cant_continue_low_correct_percent' => __("You can't continue until you reach the desired % correct answers.", 'watupro'),
			'please_wait_feedback' => __('Please wait for the feedback...', 'watupro'));
		wp_localize_script( 'watupro-script', 'watupro_i18n', $translation_array );	
		
		if(watupro_intel()) {
			 wp_enqueue_style(
				'watupro-intelligence-css',
				WATUPRO_URL.'i/css/main.css',
				array(),
				'4.4.5');
				
			wp_enqueue_script(
				'watupro-intelligence',
				WATUPRO_URL.'i/js/main.js',
				array(),
				'4.4.7');
		} // endif intel
		
		// design theme?
		$design_theme = get_option('watupro_design_theme');
		if(!empty($design_theme) and $design_theme != '-1') {
			wp_enqueue_style(
			'watupro-theme',
			WATUPRO_URL.'css/themes/'.$design_theme.'.css',
			array(),
			'4.6');
		}
}

// always enqueue jquery
function watupro_vc_jquery() {
	 wp_enqueue_script('jquery');
}

// scripts that are used only in some quizzes
function watupro_conditional_scripts($exam) {
	$advanced_settings = unserialize( stripslashes(@$exam->advanced_settings));
	
	if(!empty($advanced_settings['flag_for_review']) or !empty($advanced_settings['accept_rating'])) {
		wp_enqueue_style(
			'watupro-specific',
			WATUPRO_URL.'css/conditional.css',
			array(),
			'4.5');
	}
	
	if(!empty($advanced_settings['flag_for_review'])) {		
		wp_enqueue_script(
				'watupro-mark-review',
				WATUPRO_URL.'lib/mark-review.js',
				array(),
				'4.5');	
	}
	
	if(!empty($advanced_settings['accept_rating'])) {
		wp_enqueue_script(
				'watupro-star-rating',
				WATUPRO_URL.'lib/star-rating/jsimple-star-rating.min.js',
				array(),
				'4.5');
	}	
}

// admin menu
function watupro_add_menu_links() {
	global $wp_version, $_registered_pages;
	$page = 'tools.php';
	
	$student_caps = current_user_can(WATUPRO_MANAGE_CAPS) ? WATUPRO_MANAGE_CAPS:'read'; // used to be watupro_exams
	
	// multiuser settings - let's first default all to WATUPRO_MANAGE_CAPS in case of no Intelligence module	
	$exam_caps = $certificate_caps = $cat_caps = $ugroup_caps = $qcat_caps = $setting_caps = WATUPRO_MANAGE_CAPS;
	if(watupro_intel() and !current_user_can('administrator')) {
		if( !WatuPROIMultiUser :: check_access('exams_access', true)) $exam_caps = 'administrator';
		if( !WatuPROIMultiUser :: check_access('certificates_access', true)) $certificate_caps = 'administrator';
		if( !WatuPROIMultiUser :: check_access('cats_access', true)) $cat_caps = 'administrator';
		if( !WatuPROIMultiUser :: check_access('usergroups_access', true)) $ugroup_caps = 'administrator';
		if( !WatuPROIMultiUser :: check_access('qcats_access', true)) $qcat_caps = 'administrator';
		if( !WatuPROIMultiUser :: check_access('settings_access', true)) $setting_caps = 'administrator';		
	}
	
	// students part
	if(!get_option('watupro_nodisplay_myquizzes')) add_menu_page(sprintf(__('My %s', 'watupro'), __('Quizzes', 'watupro')), sprintf(__('My %s', 'watupro'), __('Quizzes', 'watupro')), $student_caps, "my_watupro_exams", 'watupro_my_exams');
	else add_submenu_page(null, sprintf(__('My %s', 'watupro'), __('Quizzes', 'watupro')), sprintf(__('My %s', 'watupro'), __('Quizzes', 'watupro')), $exam_caps, "my_watupro_exams", 'watupro_my_exams');
	if(!get_option('watupro_nodisplay_mycertificates')) add_submenu_page('my_watupro_exams', __("My Certificates", 'watupro'), __("My Certificates", 'watupro'), $student_caps, 'watupro_my_certificates', 'watupro_my_certificates');
	else add_submenu_page(null, __("My Certificates", 'watupro'), __("My Certificates", 'watupro'), $exam_caps, 'watupro_my_certificates', 'watupro_my_certificates');
	
	do_action('watupro_user_menu');
	
	if(!get_option('watupro_nodisplay_mysettings')) add_submenu_page('my_watupro_exams', sprintf(__("%s Settings", 'watupro'), __('Quiz', 'watupro')), sprintf(__("%s Settings", 'watupro'), __('Quiz', 'watupro')), $student_caps, 'watupro_my_options', 'watupro_my_options');
	
	// admin menus
	// "watupro_exams" menu is always accessible to WATUPRO_MANAGE_CAPS because it's the main menu item
    add_menu_page(__('Watu PRO', 'watupro'), __('Watu PRO', 'watupro'), WATUPRO_MANAGE_CAPS, "watupro_exams", 'watupro_exams');  
    add_submenu_page('watupro_exams', __('Quizzes', 'watupro'), __('Quizzes', 'watupro'), WATUPRO_MANAGE_CAPS, "watupro_exams", 'watupro_exams');
	 add_submenu_page('watupro_exams', __("Watu PRO Certificates", 'watupro'), __("Certificates", 'watupro'), $certificate_caps, 'watupro_certificates', 'watupro_certificates');
	 add_submenu_page('watupro_exams',__('Quiz Categories', 'watupro'), __('Quiz Categories', 'watupro'), $cat_caps, "watupro_cats", "watupro_cats"); 
	 add_submenu_page('watupro_exams',__('User Groups', 'watupro'), __('User Groups', 'watupro'), $ugroup_caps, "watupro_groups", "watupro_groups"); 
	 add_submenu_page('watupro_exams',__('Question Categories', 'watupro'), __('Question Categories', 'watupro'), $qcat_caps, "watupro_question_cats", "watupro_question_cats");
	 add_submenu_page( 'watupro_exams' ,__('Difficulty Levels', 'watupro'), __('Difficulty Levels', 'watupro'), $exam_caps, "watupro_diff_levels", array('WatuPRODiffLevels', 'manage')); 
	 add_submenu_page( 'watupro_exams' ,__('Default Grades', 'watupro'), __('Default Grades', 'watupro'), $exam_caps, "watupro_default_grades", "watupro_default_grades"); 
	 add_submenu_page( 'watupro_exams' ,__('All Quiz Submissions', 'watupro'), __('All Quiz Submissions', 'watupro'), $exam_caps, "watupro_takings", "watupro_takings"); 
		 
	 // accessible only to superadmin
	 add_submenu_page('watupro_exams',__('Modules', 'watupro'), __('Modules', 'watupro'), 'manage_options', "watupro_modules", "watupro_modules"); 
	 add_submenu_page('watupro_exams',__('Settings', 'watupro'), __('Settings', 'watupro'), $setting_caps, "watupro_options", "watupro_options"); 
	 add_submenu_page('watupro_exams', __("Social Sharing", 'watuproshare'), __('Social Sharing', 'watuproshare'), 
		'manage_options', 'watupro_social_share',  array('WatuPROSharing', 'options'));
	 
	 do_action('watupro_admin_menu');	 
	 
	 // always accessible to WATUPRO_MANAGE_CAPS
	 add_submenu_page('watupro_exams',__('Help', 'watupro'), __('Help', 'watupro'), WATUPRO_MANAGE_CAPS, "watupro_help", "watupro_help"); 
	 	 
 	 // not visible in menu 
 	 add_submenu_page(NULL,__('Add/Edit Exam', 'watupro'), __('Add/Edit Exam', 'watupro'), $exam_caps, "watupro_exam", "watupro_exam"); 
 	 add_submenu_page(NULL,__('Add/Edit Question', 'watupro'), __('Add/Edit Question', 'watupro'), $exam_caps, "watupro_question", "watupro_question");  // add/edit question
 	 add_submenu_page(NULL,__('Manage Questions', 'watupro'), __('Manage Questions', 'watupro'), $exam_caps, "watupro_questions", "watupro_questions");  // manage questions
 	 add_submenu_page(NULL,__('Taken Exam Data', 'watupro'), __('Taken Exam Data', 'watupro'), $exam_caps, "watupro_takings", "watupro_takings");  // view takings
 	 add_submenu_page(NULL,__('Manage Grades', 'watupro'), __('Manage Grades', 'watupro'), $exam_caps, "watupro_grades", "watupro_grades");  // manage grades
 	 add_submenu_page(NULL,__('Copy Exam', 'watupro'), __('Copy Exam', 'watupro'), $exam_caps, "watupro_copy_exam", "watupro_copy_exam");  // copy exam
 	 add_submenu_page(NULL,__('Users Who Earned Certificate', 'watupro'), __('Users Who Earned Certificate', 'watupro'), $certificate_caps, "watupro_user_certificates", "watupro_user_certificates");  // view/approve user certificates
 	 add_submenu_page(NULL,__('Editing an answer to question', 'watupro'), __('Editing an answer to question', 'watupro'), $exam_caps, "watupro_edit_choice", "watupro_edit_choice"); 
 	 add_submenu_page(NULL,__('Advanced questions import', 'watupro'), __('Advanced questions import', 'watupro'), $exam_caps, "watupro_advanced_import", array('WatuPROImport', 'dispatch')); 
 	 add_submenu_page(NULL,__('User feedback on questions', 'watupro'), __('User feedback on questions', 'watupro'), $exam_caps, "watupro_questions_feedback", 'watupro_questions_feedback'); 
 	 add_submenu_page(NULL,__('Assign users to a group', 'watupro'), __('Assign users to a group', 'watupro'), $exam_caps, "watupro_group_assign", 'watupro_group_assign'); 
}

// function to conditionally add DB fields
function watupro_add_db_fields($fields, $table) {
		global $wpdb;
		
		// check fields
		$table_fields = $wpdb->get_results("SHOW COLUMNS FROM `$table`");
		$table_field_names = array();
		foreach($table_fields as $f) $table_field_names[] = $f->Field;		
		$fields_to_add=array();
		
		foreach($fields as $field) {
			 if(!in_array($field['name'], $table_field_names)) {
			 	  $fields_to_add[] = $field;
			 } 
		}
		
		// now if there are fields to add, run the query
		if(!empty($fields_to_add)) {
			 $sql = "ALTER TABLE `$table` ";
			 
			 foreach($fields_to_add as $cnt => $field) {
			 	 if($cnt > 0) $sql .= ", ";
			 	 $sql .= "ADD $field[name] $field[type]";
			 } 
			 
			 $wpdb->query($sql);
		}
	}

// manually apply Wordpress filters on the content
// to avoid calling apply_filters('the_content')	
function watupro_define_filters() {
	global $wp_embed, $watupro_keep_chars;
		
	// add_filter( 'watupro_content', 'watupro_autop' );	
	add_filter( 'watupro_content', 'wptexturize' ); // Questionable use!
	add_filter( 'watupro_content', 'convert_smilies' );
   add_filter( 'watupro_content', 'convert_chars' );
	add_filter( 'watupro_content', 'shortcode_unautop' );
	add_filter( 'watupro_content', 'do_shortcode' );
	
	// Compatibility with specific plugins
	// qTranslate
	if(function_exists('qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage')) {
		add_filter('watupro_content', 'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage');
		add_filter('watupro_qtranslate', 'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage');
		add_filter( 'watupro_qtranslate', 'wptexturize' );
	}
	
	// WP Quick LaTeX
	if(function_exists('quicklatex_parser')) add_filter( 'watupro_content',  'quicklatex_parser', 7);
}	

function watupro_autop($content) {
	return wpautop($content, false);	
}

// send various admin notices
function watupro_admin_notices() {
	$notices = get_option('watupro_admin_notices');
	
	// make sure the plugin is not network-activated
	// this notice will always appear until the issue is resolved
	if ( function_exists( 'is_plugin_active_for_network' ) and is_plugin_active_for_network( 'watupro/watupro.php' ))	{		
		$notices .= "<br><b><font color='red'>".__('WatuPRO plugin is network activated and will not function properly.', 'watupro').'</font> '.__('Please go to your Network administration and deactivate it. Then activate the plugin as blog admin for each blog which needs it.', 'watupro')."</b>";
	}	
	
	if(!empty($notices)) {
		echo "<div class='updated'><p>".stripslashes($notices)."</p></div>";
	}
	// once shown, cleanup
	update_option('watupro_admin_notices', '');
}