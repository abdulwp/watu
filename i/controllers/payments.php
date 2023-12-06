<?php
class WatuPROPayments {
	// view/add payments for exam
	static function manage() {
<<<<<<< HEAD
		global $wpdb;
=======
		global $wpdb, $user_ID;
		
		$field = '';
		$item = (object)array("ID" => 0);
>>>>>>> branch/6.7.2
		
		// select this exam
		if(!empty($_GET['exam_id'])) {
			$exam = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM ".WATUPRO_EXAMS." WHERE ID = %d", $_GET['exam_id']));
			if(empty($exam->ID)) wp_die(__('No quiz with this ID', 'watupro'));
			$item = $exam;
			$field = 'exam_id';
		}
		if(!empty($_GET['bundle_id'])) {
			$bundle = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM ".WATUPRO_BUNDLES." WHERE ID = %d", $_GET['bundle_id']));
			if(empty($bundle->ID)) wp_die(__('No bundle with this ID', 'watupro'));
			$item = $bundle;
			$field = 'bundle_id';
<<<<<<< HEAD
			if($bundle->bundle_type == 'category') $bundle_name = sprintf(__('Access to a category of %s (Bundle ID: %d)', 'watupro'), __('quizzes', 'watupro'), $bundle->ID);
		  else $bundle_name = sprintf(__('Access to a selection of %s (Bundle ID: %d)', 'watupro'), __('quizzes', 'watupro'), $budle->ID); 
=======
			if(empty($bundle->name)) {
				if($bundle->bundle_type == 'category') $bundle_name = sprintf(__('Access to a categories of %s (Bundle ID: %d)', 'watupro'), WATUPRO_QUIZ_WORD_PLURAL, $bundle->ID);
		      else $bundle_name = sprintf(__('Access to a selection of %s (Bundle ID: %d)', 'watupro'), WATUPRO_QUIZ_WORD_PLURAL, $bundle->ID);
			}
			else $bundle_name = $bundle->name;
>>>>>>> branch/6.7.2
		}	
		
		// add payment manually
		if(!empty($_POST['add_payment'])) {
			// find the given user first
			$user = get_user_by('login', $_POST['user_login']);
			if(empty($user->user_login)) wp_die(__('Unrecognized user login', 'watupro'));
			
			// now insert the payment
			$wpdb->query($wpdb->prepare("INSERT INTO ".WATUPRO_PAYMENTS." SET
				$field=%d, user_id=%d, date=CURDATE(), amount=%s, status='completed', paycode='manual', method='manual'", 
				$item->ID, $user->ID, $_POST['amount']));
<<<<<<< HEAD
=======
			
			$quiz_id = ($field == 'exam_id') ? $item->ID : 0;
			$bundle_id = ($field == 'bundle_id') ? $item->ID : 0; 	
			self :: ensure_access($user->ID, $quiz_id, $bundle_id);	
>>>>>>> branch/6.7.2
				
			watupro_redirect("admin.php?page=watupro_payments&".$field."=".$item->ID);	
		}
		
		$offset = empty($_GET['offset']) ? 0 : intval($_GET['offset']);
		
		// delete payment
		if(!empty($_GET['delete'])) {
			$wpdb->query( $wpdb->prepare("DELETE FROM ".WATUPRO_PAYMENTS." WHERE id=%d", $_GET['id']));
			watupro_redirect("admin.php?page=watupro_payments&".@$field."=".@$item->ID."&offset=$offset");
		}
		
		// approve/unapprove payment
		if(!empty($_GET['change_status'])) {
			$status = empty($_GET['status']) ? 'pending' : 'completed';
			$wpdb->query( $wpdb->prepare("UPDATE ".WATUPRO_PAYMENTS." SET status='$status' WHERE id=%d", $_GET['id']));
			watupro_redirect("admin.php?page=watupro_payments&".@$field."=".@$item->ID."&offset=$offset");
		}
		
<<<<<<< HEAD
		// select payments made		
		$see_all_quizzes = empty($field); // when $field variable is empty we are looking at the payments made for all quizzes
		if(!$see_all_quizzes) {
			$payments = $wpdb->get_results( $wpdb->prepare("SELECT SQL_CALC_FOUND_ROWS tP.*, tU.user_login as user_login 
=======
		// mass delete payments		
		if(!empty($_POST['mass_delete']) and check_admin_referer('watupro_payments')) {
			$pids = is_array($_POST['pids']) ? watupro_int_array($_POST['pids']) : array(0);
			
			$wpdb->query("DELETE FROM ".WATUPRO_PAYMENTS." WHERE ID IN (".implode(',', $pids).")");
		}
		
		// select payments made		
		$see_all_quizzes = empty($field); // when $field variable is empty we are looking at the payments made for all quizzes
		if(!$see_all_quizzes) {
			$payments = $wpdb->get_results( $wpdb->prepare("SELECT SQL_CALC_FOUND_ROWS tP.*, CONCAT(tU.user_login, ' / ', tU.user_email) as user_login 
>>>>>>> branch/6.7.2
				FROM ".WATUPRO_PAYMENTS." tP LEFT JOIN {$wpdb->users} tU ON tU.ID = tP.user_id
				WHERE tP.$field=%d ORDER BY tP.ID DESC LIMIT $offset, 100", $item->ID));
		}
		else {
<<<<<<< HEAD
			$payments = $wpdb->get_results("SELECT SQL_CALC_FOUND_ROWS tP.*, tE.name as quiz_name, tU.user_login as user_login 
=======
			$payments = $wpdb->get_results("SELECT SQL_CALC_FOUND_ROWS tP.*, tE.name as quiz_name, CONCAT(tU.user_login, ' / ', tU.user_email) as user_login 
>>>>>>> branch/6.7.2
				FROM ".WATUPRO_PAYMENTS." tP LEFT JOIN {$wpdb->users} tU ON tU.ID = tP.user_id
				JOIN ".WATUPRO_EXAMS." tE ON tE.ID = tP.exam_id
				ORDER BY tP.ID DESC LIMIT $offset, 100");
		}	
			
		$count = $wpdb->get_var("SELECT FOUND_ROWS()");	
		
		$currency = get_option('watupro_currency');
		$paypoints_price = get_option('watupro_paypoints_price');
		
		// select all paid quizzes for the dropdown
		$paid_exams = $wpdb->get_results("SELECT name, ID FROM ".WATUPRO_EXAMS." WHERE fee>0 ORDER BY name");
			
		if(@file_exists(get_stylesheet_directory().'/watupro/i/payments.html.php')) require get_stylesheet_directory().'/watupro/i/payments.html.php';
		else require WATUPRO_PATH."/i/views/payments.html.php";
	}
	
	// handle the ajax request of the payment with points
	static function pay_with_points() {
		global $wpdb, $user_ID;
		
		if(!is_user_logged_in()) die("ERROR: Not logged in");
		
		// payment with points accepted at all?
		$accept_paypoints = get_option('watupro_accept_paypoints');
		if(empty($accept_paypoints)) die("ERROR: points not accepted as payment method."); 
		
		// enough points to pay?
		$paypoints_price = get_option('watupro_paypoints_price');
		if(empty($_POST['is_bundle'])) {
			$exam = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".WATUPRO_EXAMS." WHERE ID=%d", $_POST['id']));
<<<<<<< HEAD
=======
			if($exam->fee > 0 and class_exists('WatuPROIExam') and method_exists('WatuPROIExam', 'adjust_price')) WatuPROIExam :: adjust_price($exam);
>>>>>>> branch/6.7.2
			$fee = $exam->fee;
			$cost_in_points = $exam->fee * $paypoints_price;
		}
		else {
			// bundle
			$bundle = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".WATUPRO_BUNDLES." WHERE ID=%d", $_POST['id']));
			$fee = $bundle->price;
			$cost_in_points = $bundle->price * $paypoints_price;
		}
		
		// used coupon?
		$coupon_code = get_user_meta($user_ID, 'watupro_coupon', true);		 
		if(!empty($coupon_code)) {
			$coupon = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".WATUPRO_COUPONS." WHERE code=%s", trim($coupon_code)));
			if(WatuPROICoupons :: is_valid($coupon)) {
				// apply to the price
				$fee = WatuPROICoupons :: apply($coupon, $fee, $user_ID, false);
				$cost_in_points = $fee * $paypoints_price;
			}	
		}		
		
		$user_points = get_user_meta($user_ID, 'watuproplay-points', true);	
		if($user_points < $cost_in_points) die("ERROR: Not enough points");
		
		// now make payment
<<<<<<< HEAD
		$wpdb->query($wpdb->prepare("INSERT INTO ".WATUPRO_PAYMENTS." SET 
			exam_id=%d, user_id=%s, date=CURDATE(), amount=%s, status='completed', paycode=%s, 
			method='points', bundle_id=%d", 
			@$exam->ID, $user_ID, $fee, '', @$bundle->ID));
=======
		$quiz_id = empty($exam->ID) ? 0 : $exam->ID;
		$bundle_id = empty($bundle->ID) ? 0 : $bundle->ID;
		$wpdb->query($wpdb->prepare("INSERT INTO ".WATUPRO_PAYMENTS." SET 
			exam_id=%d, user_id=%s, date=CURDATE(), amount=%s, status='completed', paycode=%s, 
			method='points', bundle_id=%d", 
			$quiz_id, $user_ID, $fee, '', $bundle_id));			
		
		self :: ensure_access($user_ID, $quiz_id, $bundle_id);	
>>>>>>> branch/6.7.2
				
		// deduct user points
		$user_points -= $cost_in_points;
		update_user_meta($user_ID, 'watuproplay-points', $user_points);	
		
		 // cleanup coupon code if any
		if(!empty($coupon_code)) if(!empty($coupon_code)) WatuPROICoupons :: coupon_used($coupon, $user_ID);		
			
		echo "SUCCESS";
		exit;
	}
	
<<<<<<< HEAD
	// display and create buttons for buying quiz bundles
	static function bundles() {
		global $wpdb;
=======
	
		// handle the ajax request of the payment with MoolaMojo
	static function pay_with_moolamojo() {
		global $wpdb, $user_ID;
		
		if(!is_user_logged_in()) die("ERROR: Not logged in");
		
		// payment with moolamojo accepted at all?
		$accept_moolamojo = get_option('watupro_accept_moolamojo');
		if(empty($accept_moolamojo)) die("ERROR: virtual credits are not accepted as payment method."); 
		
		// enough points to pay?
		$moola_price = get_option('watupro_moolamojo_price');
		if(empty($_POST['is_bundle'])) {
			$exam = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".WATUPRO_EXAMS." WHERE ID=%d", intval($_POST['id'])));
			if($exam->fee > 0 and class_exists('WatuPROIExam') and method_exists('WatuPROIExam', 'adjust_price')) WatuPROIExam :: adjust_price($exam);
			$fee = $exam->fee;
			$cost_in_moola = $exam->fee * $moola_price;
		}
		else {
			// bundle
			$bundle = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".WATUPRO_BUNDLES." WHERE ID=%d", intval($_POST['id'])));
			$fee = $bundle->price;
			$cost_in_moola = $bundle->price * $moola_price;
		}
		
		// used coupon?
		$coupon_code = get_user_meta($user_ID, 'watupro_coupon', true);		 
		if(!empty($coupon_code)) {
			$coupon = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".WATUPRO_COUPONS." WHERE code=%s", trim($coupon_code)));
			if(WatuPROICoupons :: is_valid($coupon)) {
				// apply to the price
				$fee = WatuPROICoupons :: apply($coupon, $fee, $user_ID, false);
				$cost_in_moola = $fee * $moola_price;
			}	
		}		
		
		$user_balance = get_user_meta($user_ID, 'moolamojo_balance', true);	
		if($user_balance < $cost_in_moola) die("ERROR: Not enough virtual credits");
		
		// now make payment
		$quiz_id = empty($exam->ID) ? 0 : $exam->ID;
		$bundle_id = empty($bundle->ID) ? 0 : $bundle->ID;
		$wpdb->query($wpdb->prepare("INSERT INTO ".WATUPRO_PAYMENTS." SET 
			exam_id=%d, user_id=%s, date=CURDATE(), amount=%s, status='completed', paycode=%s, 
			method='moolamojo', bundle_id=%d", 
			$quiz_id, $user_ID, $fee, '', $bundle_id));
			
		self :: ensure_access($user_ID, $quiz_id, $bundle_id);		
				
		// deduct user points
		$user_balance -= $cost_in_moola;
		update_user_meta($user_ID, 'moolamojo_balance', $user_balance);	
		
		 // cleanup coupon code if any
		if(!empty($coupon_code)) if(!empty($coupon_code)) WatuPROICoupons :: coupon_used($coupon, $user_ID);		
			
		echo "SUCCESS";
		exit;
	}
	
	// display and create buttons for buying quiz bundles
	static function bundles() {
		global $wpdb, $user_ID;
>>>>>>> branch/6.7.2
		$currency = get_option('watupro_currency');
		$accept_stripe = get_option('watupro_accept_stripe');
		$accept_points = get_option('watupro_accept_paypoints');
		$other_payments = get_option('watupro_other_payments');
		$do = empty($_GET['do']) ? 'list' : $_GET['do'];
		
<<<<<<< HEAD
=======
		$multiuser_access = 'all';
		if(watupro_intel()) $multiuser_access = WatuPROIMultiUser::check_access('bundles_access');
		
>>>>>>> branch/6.7.2
		// select all quizzes
		$quizzes = $wpdb->get_results("SELECT ID, name FROM ".WATUPRO_EXAMS." WHERE fee > 0 ORDER BY name");
		
		// select quiz cats
		$cats = $wpdb->get_results("SELECT ID, name FROM ".WATUPRO_CATS." ORDER BY name");
		
<<<<<<< HEAD
		switch($do) {
			case 'add':
				if(!empty($_POST['ok'])) {
					$cat_id = ($_POST['bundle_type'] == 'quizzes') ? 0 : $_POST['cat_id'];
					$quiz_ids = ($_POST['bundle_type'] == 'quizzes') ? implode(",", @$_POST['quizzes']) : '';
					$wpdb->query($wpdb->prepare("INSERT INTO ".WATUPRO_BUNDLES." SET
						price=%f, bundle_type=%s, quiz_ids=%s, cat_id=%d, redirect_url=%s",
						$_POST['price'], $_POST['bundle_type'], $quiz_ids, $cat_id, $_POST['redirect_url']));
=======
		$is_time_limited = empty($_POST['is_time_limited']) ? 0 : 1;
		
		switch($do) {
			case 'add':
				if(!empty($_POST['ok']) and check_admin_referer('watupro_bundle')) {
					$cat_ids = ($_POST['bundle_type'] == 'quizzes') ? '' : '|'.implode('|', watupro_int_array(@$_POST['cat_ids'])).'|';
					$quiz_ids = ($_POST['bundle_type'] == 'quizzes') ? implode(",", watupro_int_array(@$_POST['quizzes'])) : '';
					$num_quizzes = empty($_POST['num_quizzes']) ? 0 : intval($_POST['num_quizzes']);
					$wpdb->query($wpdb->prepare("INSERT INTO ".WATUPRO_BUNDLES." SET
						price=%f, bundle_type=%s, quiz_ids=%s, cat_ids=%s, redirect_url=%s, is_time_limited=%d, time_limit=%d, 
						name=%s, editor_id=%d, num_quizzes=%d",
						floatval($_POST['price']), sanitize_text_field($_POST['bundle_type']), $quiz_ids, $cat_ids, esc_url_raw($_POST['redirect_url']), 
						$is_time_limited, intval($_POST['time_limit']), sanitize_text_field($_POST['name']), $user_ID, $num_quizzes));
>>>>>>> branch/6.7.2
						
					watupro_redirect("admin.php?page=watupro_bundles");	
				}
				
				if(@file_exists(get_stylesheet_directory().'/watupro/i/bundle.html.php')) require get_stylesheet_directory().'/watupro/i/bundle.html.php';
		else require WATUPRO_PATH."/i/views/bundle.html.php";
			break;		
			
			case 'edit':
<<<<<<< HEAD
				if(!empty($_POST['ok'])) {
					$cat_id = ($_POST['bundle_type'] == 'quizzes') ? 0 : $_POST['cat_id'];
					$quiz_ids = ($_POST['bundle_type'] == 'quizzes') ? implode(",", @$_POST['quizzes']) : '';
					$wpdb->query($wpdb->prepare("UPDATE ".WATUPRO_BUNDLES." SET
						price=%f, bundle_type=%s, quiz_ids=%s, cat_id=%d, redirect_url=%s
						WHERE ID=%d",
						$_POST['price'], $_POST['bundle_type'], $quiz_ids, $cat_id, $_POST['redirect_url'], $_GET['id']));
=======
				$own_sql = ($multiuser_access == 'own') ? $wpdb->prepare(" AND editor_id = %d ", $user_ID) : "";
				
				if(!empty($_POST['ok']) and check_admin_referer('watupro_bundle')) {
					$cat_ids = ($_POST['bundle_type'] == 'quizzes') ? '' : '|'.implode('|', watupro_int_array(@$_POST['cat_ids'])).'|';
					$quiz_ids = ($_POST['bundle_type'] == 'quizzes') ? implode(",", watupro_int_array(@$_POST['quizzes'])) : '';
					$num_quizzes = empty($_POST['num_quizzes']) ? 0 : intval($_POST['num_quizzes']);
					$wpdb->query($wpdb->prepare("UPDATE ".WATUPRO_BUNDLES." SET
						price=%f, bundle_type=%s, quiz_ids=%s, cat_ids=%s, redirect_url=%s, is_time_limited=%d, 
						time_limit=%d, name=%s, num_quizzes=%d
						WHERE ID=%d $own_sql",
						floatval($_POST['price']), sanitize_text_field($_POST['bundle_type']), $quiz_ids, $cat_ids, esc_url_raw($_POST['redirect_url']), 
						$is_time_limited, intval($_POST['time_limit']), sanitize_text_field($_POST['name']), $num_quizzes, intval($_GET['id'])));
>>>>>>> branch/6.7.2
						
					watupro_redirect("admin.php?page=watupro_bundles");	
				}
				
<<<<<<< HEAD
				$bundle = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".WATUPRO_BUNDLES." WHERE ID=%d", $_GET['id']));
=======
				$bundle = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".WATUPRO_BUNDLES." WHERE ID=%d $own_sql ", intval($_GET['id'])));
>>>>>>> branch/6.7.2
				$qids = explode(",", $bundle->quiz_ids);
				
				if(@file_exists(get_stylesheet_directory().'/watupro/i/bundle.html.php')) require get_stylesheet_directory().'/watupro/i/bundle.html.php';
		else require WATUPRO_PATH."/i/views/bundle.html.php";
			break;				
			
			case 'list':
			default:
<<<<<<< HEAD
				if(!empty($_GET['del'])) {
					$wpdb->query($wpdb->prepare("DELETE FROM ".WATUPRO_BUNDLES." WHERE ID=%d", $_GET['id']));
					watupro_redirect("admin.php?page=watupro_bundles");
				}			
			
				// select current bundles left join by cat
				$bundles = $wpdb->get_results("SELECT tB.*, tC.name as cat 
					FROM ".WATUPRO_BUNDLES." tB LEFT JOIN ".WATUPRO_CATS." tC
					ON tC.ID = tB.cat_id
					ORDER BY tB.ID");
					
				// add quizzes to the bundles
=======
				$own_sql = ($multiuser_access == 'own') ? $wpdb->prepare(" AND editor_id = %d ", $user_ID) : "";
				
				if(!empty($_GET['del'])) {
					$wpdb->query($wpdb->prepare("DELETE FROM ".WATUPRO_BUNDLES." WHERE ID=%d $own_sql ", intval($_GET['id'])));
					watupro_redirect("admin.php?page=watupro_bundles");
				}		
				
				if(!empty($_POST['bundle_settings']) and check_admin_referer('watupro_bundle_settings')) {
					$enable_my_bundles = empty($_POST['enable_my_bundles']) ? 0 : 1;
					update_option('watupro_enable_my_bundles', $enable_my_bundles);
				}
				
				$enable_my_bundles = get_option('watupro_enable_my_bundles');
			
				$own_sql = ($multiuser_access == 'own') ? $wpdb->prepare(" AND tB.editor_id = %d ", $user_ID) : "";
				
				// select current bundles left join by cat
				$bundles = $wpdb->get_results("SELECT tB.* FROM ".WATUPRO_BUNDLES." tB WHERE 1=1 $own_sql ORDER BY tB.ID");
					
				// add cats and quizzes to the bundles
>>>>>>> branch/6.7.2
				foreach($bundles as $cnt => $bundle) {
					$quiz_names = array();
					if($bundle->bundle_type == 'quizzes') {
						$qids = explode(",", $bundle->quiz_ids);
						foreach($quizzes as $quiz) {
<<<<<<< HEAD
							if(in_array($quiz->ID, $qids)) $quiz_names[] = $quiz->name;
=======
							if(in_array($quiz->ID, $qids)) $quiz_names[] = stripslashes($quiz->name);
>>>>>>> branch/6.7.2
						} // end foreach quiz
						
						$bundles[$cnt]->quizzes = implode(", ", $quiz_names);
					} // end if
<<<<<<< HEAD
=======
					
					$cat_names = array();
					if($bundle->bundle_type == 'category') {
						$cids = explode("|", $bundle->cat_ids);
						foreach($cats as $cat) {
							if(in_array($cat->ID, $cids)) $cat_names[] = stripslashes($cat->name);
						} // end foreach quiz
						
						$bundles[$cnt]->cat = implode(", ", $cat_names);
					} // end if
>>>>>>> branch/6.7.2
				}	// end foreach bundle
				
				if(@file_exists(get_stylesheet_directory().'/watupro/i/bundles.html.php')) require get_stylesheet_directory().'/watupro/i/bundles.html.php';
		else require WATUPRO_PATH."/i/views/bundles.html.php";
			break;
		}
	} // end managing bundles
	
<<<<<<< HEAD
=======
	// list bundles I have purchased
	static function my_bundles($in_shortcode = false) {
		global $wpdb, $user_ID;
		
		if(!is_user_logged_in()) return "";
		$enable_my_bundles = get_option('watupro_enable_my_bundles');
		if(!$enable_my_bundles) return "";
		
		// select all quizzes
		$quizzes = $wpdb->get_results("SELECT ID, name FROM ".WATUPRO_EXAMS." WHERE fee > 0 ORDER BY name");
		
		// select quiz cats
		$cats = $wpdb->get_results("SELECT ID, name FROM ".WATUPRO_CATS." ORDER BY name");
		
		// select my bundles
		$bundles = $wpdb->get_results($wpdb->prepare("SELECT tB.*, tP.date as payment_date, tP.num_quizzes_used as num_quizzes_used 
			FROM ".WATUPRO_BUNDLES." tB 
			JOIN ".WATUPRO_PAYMENTS." tP ON tP.user_id=%d AND tP.status='completed' AND tP.bundle_id=tB.ID
			ORDER BY tB.name", $user_ID));
			
		// add quizzes to the bundles
		foreach($bundles as $cnt => $bundle) {
			$quiz_names = array();
			if($bundle->bundle_type == 'quizzes') {
				$qids = explode(",", $bundle->quiz_ids);
				foreach($quizzes as $quiz) {
					if(in_array($quiz->ID, $qids)) $quiz_names[] = $quiz->name;
				} // end foreach quiz
				
				$bundles[$cnt]->quizzes = implode(", ", $quiz_names);
			} // end if
			
			$cat_names = array();
			if($bundle->bundle_type == 'category') {
				$cids = explode("|", $bundle->cat_ids);
				foreach($cats as $cat) {
					if(in_array($cat->ID, $cids)) $cat_names[] = stripslashes($cat->name);
				} // end foreach quiz
				
				$bundles[$cnt]->cat = implode(", ", $cat_names);
			} // end if
		}	// end foreach bundle	
		
		$dateformat = get_option('date_format');
		if(@file_exists(get_stylesheet_directory().'/watupro/i/my-bundles.html.php')) require get_stylesheet_directory().'/watupro/i/my-bundles.html.php';
		else require WATUPRO_PATH."/i/views/my-bundles.html.php";
	}
	
	// call my_bundles from shortcode
	static function my_bundles_shortcode() {
		ob_start();
		self :: my_bundles(true);
		$content = ob_get_clean();
		return $content;
	}
	
>>>>>>> branch/6.7.2
	// display payment bundle button
	static function bundle_button($atts) {
		global $wpdb, $post, $user_ID;
		watupro_vc_scripts();
		$mode = @$atts['mode'];
		$currency = get_option('watupro_currency');
		
		if(empty($user_ID)) return __('You need to be logged in.', 'watupro');
				
		// select this bundle
		$bundle = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".WATUPRO_BUNDLES." WHERE ID=%d", $atts['id']));
		if(empty($bundle->ID)) return "<p>".__('This offer has been disabled.', 'watupro')."</p>";
		
		// if the user already paid for this bundle and the bundle has redirect URL defined, just go to it
		if(!empty($bundle->redirect_url)) {
<<<<<<< HEAD
			$valid_bundle_payment = $wpdb->get_var($wpdb->prepare("SELECT ID FROM ".WATUPRO_PAYMENTS."
				WHERE user_id=%d AND bundle_id=%d AND status='completed'", $user_ID, $bundle->ID));
			if($valid_bundle_payment) watupro_redirect($bundle->redirect_url);	
		}
	
		// coupon code inserted
		$any_coupons = $wpdb->get_var("SELECT id FROM ".WATUPRO_COUPONS." WHERE num_uses = 0 OR (num_uses - times_used) > 0");
=======
			$valid_bundle_payment = $wpdb->get_row($wpdb->prepare("SELECT ID, date FROM ".WATUPRO_PAYMENTS."
				WHERE user_id=%d AND bundle_id=%d AND status='completed'", $user_ID, $bundle->ID));
				
			// if the bundle is time limited and the limit has expired, unset the ID
			if($bundle->is_time_limited and !empty($valid_bundle_payment->ID)) {
				$payment_time = strtotime($valid_bundle_payment->date);
				if(current_time('timestamp') > ($payment_time + 24 * 3600 * $bundle->time_limit) ) unset($valid_bundle_payment->ID);
			}	
				
			if(!empty($valid_bundle_payment->ID)) watupro_redirect($bundle->redirect_url);	
		}
	
		// coupon code inserted
		$any_coupons = $wpdb->get_var("SELECT id FROM ".WATUPRO_COUPONS." WHERE num_uses = 0 OR (CAST(num_uses as signed) - CAST(times_used as signed)) > 0");

>>>>>>> branch/6.7.2
		if($any_coupons and !empty($_POST['watupro_coupon'])) {
			// check if the coupon is valid
			$coupon = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".WATUPRO_COUPONS." WHERE code=%s", trim($_POST['watupro_coupon'])));
			
			if(WatuPROICoupons :: is_valid($coupon)) {
				// apply to the price
				$old_fee = $bundle->price;
				$bundle->price = WatuPROICoupons :: apply($coupon, $bundle->price, $user_ID);	
				$coupon_applied = true;			
			}
		}
		
		// prepare bundle name
<<<<<<< HEAD
		$bundle_name = '';
		if($bundle->bundle_type == 'category') $bundle_name = sprintf(__('Access to a category of %s', 'watupro'), __('quizzes', 'watupro'));
		else $bundle_name = sprintf(__('Access to a selection of %s', 'watupro'), __('quizzes', 'watupro')); 
		
		ob_start();
=======
		if(empty($bundle->name)) {
			$bundle_name = '';
			if($bundle->bundle_type == 'category') $bundle_name = sprintf(__('Access to a category of %s', 'watupro'), __('quizzes', 'watupro'));
	    	else $bundle_name = sprintf(__('Access to a selection of %s', 'watupro'), __('quizzes', 'watupro'));
		}
		else $bundle_name = $bundle->name;
 
		
		ob_start();
		
		// display extra info, i.e. price?
		$info = '';
		if(!empty($atts['info'])) {
			$info = $atts['info'];
			if(!empty($atts['info_p'])) {
				$info = empty($atts['center']) ? '<p class="watupro-bundle-info">'.$info.'</p>' : '<p class="watupro-bundle-info" align="center">'.$info.'</p>';
			}
		}
		
>>>>>>> branch/6.7.2
		switch($mode) {
			case 'paypoints':
				$paypoints_price = get_option('watupro_paypoints_price');
				$paypoints_button = get_option('watupro_paypoints_button');
				
				$cost_in_points = round($bundle->price * $paypoints_price);
				$user_points = get_user_meta($user_ID, 'watuproplay-points', true);	
				
				if($user_points < $cost_in_points) $paybutton = __('Not enough points.', 'watupro');
				else {
					$url = admin_url("admin-ajax.php?action=watupro_pay_with_points");
					$paybutton = "<input type='button' value='".sprintf(__('Pay %d points', 'watupro'), $cost_in_points)."' onclick='WatuPROPay.payWithPoints({$bundle->ID}, \"$url\", 1, \"{$bundle->redirect_url}\");'>";
				}
				
				// replace the codes in the design
				$paypoints_button = str_replace('{{{points}}}', $cost_in_points, $paypoints_button);
				$paypoints_button = str_replace('{{{user-points}}}', $user_points, $paypoints_button);
				$paypoints_button = str_replace('{{{button}}}', $paybutton, $paypoints_button);
				$paypoints_button = stripslashes($paypoints_button);
<<<<<<< HEAD
=======
				
				if(!empty($info)) {
					$info = str_replace('{{{cost}}}', $cost_in_points, $info);				
					echo $info;
				}

>>>>>>> branch/6.7.2
				echo do_shortcode($paypoints_button);
			break;			
			
			case 'stripe':
				include_once(WATUPRO_PATH.'/i/lib/Stripe.php');
 
				$stripe = array(
				  'secret_key'      => get_option('watupro_stripe_secret'),
				  'publishable_key' => get_option('watupro_stripe_public')
				);
				 
<<<<<<< HEAD
				Stripe::setApiKey($stripe['secret_key']);
				?>
				<form method="post">
					
=======
				\Stripe\Stripe::setApiKey($stripe['secret_key']);
				
				if(!empty($info)) {
					$info = str_replace('{{{cost}}}', $bundle->price, $info);				
					echo $info;
				}
				?>
				<form method="post">
					<?php if(!empty($atts['center'])):?><p align="center"><?php endif;?>
>>>>>>> branch/6.7.2
				  <script src="https://checkout.stripe.com/v2/checkout.js" class="stripe-button"
				          data-key="<?php echo $stripe['publishable_key']; ?>"
				          data-amount="<?php echo $bundle->price*100?>" data-description="<?php echo $bundle_name?>" data-currency="<?php echo $currency?>"></script>
				<input type="hidden" name="stripe_bundle_pay" value="1">
				<input type="hidden" name="bundle_id" value="<?php echo $bundle->ID?>">
<<<<<<< HEAD
				
=======
				   <?php if(!empty($atts['center'])):?></p><?php endif;?>
>>>>>>> branch/6.7.2
				</form>
			<?php break;			
			
			case 'paypal':
			default:
			if(!empty($_GET['watupro_pdt'])) WatuPROPayment::paypal_ipn(); // in case return URL is here, check payment
<<<<<<< HEAD
=======
			
			if(!empty($info)) {
				$info = str_replace('{{{cost}}}', $bundle->price, $info);				
				echo $info;
			}			
			
>>>>>>> branch/6.7.2
			$return_url = $bundle->redirect_url ? $bundle->redirect_url : get_permalink( $post->ID );
			$use_pdt = get_option('watupro_use_pdt');
			if($use_pdt == 1) $return_url = esc_url(add_query_arg(array('watupro_pdt' => 1, 'watupro_pdt_bundle'=>1), $return_url));
			$paypal_email = get_option("watupro_paypal");
			$paypal_host = "www.paypal.com";
<<<<<<< HEAD
		$paypal_sandbox = get_option('watupro_paypal_sandbox');
		if($paypal_sandbox == '1') $paypal_host = 'www.sandbox.paypal.com'; ?>
			<form action="https://<?php echo $paypal_host?>/cgi-bin/webscr" method="post">
				<input type="hidden" name="cmd" value="_xclick">
				<input type="hidden" name="business" value="<?php echo $paypal_email?>">
				<input type="hidden" name="item_name" value="<?php echo $bundle_name?>">
				<input type="hidden" name="item_number" value="<?php echo $bundle->ID?>">
				<input type="hidden" name="amount" value="<?php echo $bundle->price?>">
				<input type="hidden" name="return" value="<?php echo $return_url;?>">
				<input type="hidden" name="notify_url" value="<?php echo site_url('?watupro=paypal_bundle&user_id='.$user_ID, 'https');?>">
				<input type="hidden" name="no_shipping" value="1">
				<input type="hidden" name="no_note" value="1">
				<input type="hidden" name="currency_code" value="<?php echo $currency;?>">
				<input type="hidden" name="lc" value="US">
				<input type="hidden" name="bn" value="PP-BuyNowBF">
				<input type="image" src="https://www.paypal.com/en_US/i/btn/x-click-butcc.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
				<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
=======
			$paypal_sandbox = get_option('watupro_paypal_sandbox');
			$paypal_button = get_option('watupro_paypal_button');
			if(empty($paypal_button)) $paypal_button = 'https://www.paypal.com/en_US/i/btn/x-click-butcc.gif';
		if($paypal_sandbox == '1') $paypal_host = 'www.sandbox.paypal.com'; ?>
			<form action="https://<?php echo $paypal_host?>/cgi-bin/webscr" method="post">
				<?php if(!empty($atts['center'])):?><p align="center"><?php endif;?>
				<input type="hidden" name="cmd" value="_xclick">
				<input type="hidden" name="business" value="<?php echo $paypal_email?>">
				<input type="hidden" name="item_name" value="<?php echo $bundle_name?>">
				<input type="hidden" name="item_number" value="<?php echo $bundle->ID?>">
				<input type="hidden" name="amount" value="<?php echo $bundle->price?>">
				<input type="hidden" name="return" value="<?php echo $return_url;?>">
				<input type="hidden" name="notify_url" value="<?php echo site_url('?watupro=paypal_bundle&user_id='.$user_ID, 'https');?>">
				<input type="hidden" name="no_shipping" value="1">
				<input type="hidden" name="no_note" value="1">
				<input type="hidden" name="currency_code" value="<?php echo $currency;?>">
				<input type="hidden" name="lc" value="US">
				<input type="hidden" name="bn" value="PP-BuyNowBF">
				<input type="image" src="<?php echo $paypal_button?>" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
				<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
				<?php if(!empty($atts['center'])):?></p><?php endif;?>
>>>>>>> branch/6.7.2
			</form> 
			<?php break;
			
			case 'custom':
				$contents = stripslashes(get_option('watupro_other_payments'));
				$contents = str_replace('[AMOUNT]', $bundle->price, $contents);
				$contents = str_replace('[USER_ID]', $user_ID, $contents);
				$contents = str_replace('[EXAM_TITLE]', $bundle_name, $contents);
				$contents = str_replace('[EXAM_ID]', $bundle->ID, $contents);
				$contents = str_replace('[ITEM_TYPE]', 'bundle', $contents);
<<<<<<< HEAD
				return do_shortcode($contents);
=======
				
				if(!empty($info)) {					
					$info = str_replace('{{{cost}}}', $bundle->price, $info);
				}				
				
				return $info . do_shortcode($contents);
>>>>>>> branch/6.7.2
			break;
		}
		
		$contents = ob_get_clean();
		return $contents;		
	} // end bundle_button
<<<<<<< HEAD
=======
	
	// view and manage certificate payments
	static function certificate_payments() {
		global $wpdb, $user_ID;
		
		// select certificate
		$certificate = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".WATUPRO_CERTIFICATES." WHERE ID=%d", intval($_GET['certificate_id'])));
		$offset = empty($_GET['offset']) ? 0 : intval($_GET['offset']);
		
		// add payment manually
		if(!empty($_POST['add_payment']) and check_admin_referer('watupro_certificate_payments')) {
			// find the given user first
			$user = get_user_by('login', $_POST['user_login']);
			if(empty($user->user_login)) wp_die(__('Unrecognized user login', 'watupro'));
			
			// now insert the payment
			$wpdb->query($wpdb->prepare("INSERT INTO ".WATUPRO_PAYMENTS." SET
				certificate_id=%d, user_id=%d, date=CURDATE(), amount=%s, status='completed', paycode='manual', method='manual'", 
				$certificate->ID, $user->ID, floatval($_POST['amount'])));
				
			watupro_redirect("admin.php?page=watupro_certificate_payments&certificate_id=".$certificate->ID."&offset=$offset");	
		}
		
		// delete payment
		if(!empty($_GET['delete'])) {
			$wpdb->query( $wpdb->prepare("DELETE FROM ".WATUPRO_PAYMENTS." WHERE id=%d", intval($_GET['id'])));
			watupro_redirect("admin.php?page=watupro_certificate_payments&certificate_id=".$certificate->ID."&offset=$offset");
		}
		
		// approve/unapprove payment
		if(!empty($_GET['change_status'])) {
			$status = empty($_GET['status']) ? 'pending' : 'completed';
			$wpdb->query( $wpdb->prepare("UPDATE ".WATUPRO_PAYMENTS." SET status='$status' WHERE id=%d", intval($_GET['id'])));
			watupro_redirect("admin.php?page=watupro_certificate_payments&certificate_id=".$certificate->ID."&offset=$offset");
		}
		
		
		$payments = $wpdb->get_results($wpdb->prepare("SELECT SQL_CALC_FOUND_ROWS tP.*, tC.title as certificate, tU.user_login as user_login 
				FROM ".WATUPRO_PAYMENTS." tP LEFT JOIN {$wpdb->users} tU ON tU.ID = tP.user_id
				JOIN ".WATUPRO_CERTIFICATES." tC ON tC.ID = tP.certificate_id
				WHERE tC.id=%d ORDER BY tP.ID DESC LIMIT %d, 100", $certificate->ID, $offset));
			
		$count = $wpdb->get_var("SELECT FOUND_ROWS()");	
		
		$currency = get_option('watupro_currency');
		$paypoints_price = get_option('watupro_paypoints_price');
		
		if(@file_exists(get_stylesheet_directory().'/watupro/i/certificate-payments.html.php')) require get_stylesheet_directory().'/watupro/i/certificate-payments.html.php';
		else require WATUPRO_PATH."/i/views/certificate-payments.html.php";
	} // end certificate_payments()
	
	// show quizzes available through a bundle
	static function list_quizzes($atts) {
		global $wpdb;
		if(empty($atts['id']) or !is_numeric($atts['id'])) return '';
		
		// select bundle
		$bundle = $wpdb->get_row($wpdb->prepare("SELECT * FROM ". WATUPRO_BUNDLES." WHERE ID=%d", intval($atts['id'])));
		
		if(empty($bundle->ID)) return __('Bundle not found', 'watupro');
		
		switch($bundle->bundle_type) {
			case 'num_quizzes': return sprintf(__('%d %s', 'watupro'), $bundle->num_quizzes, WATUPRO_QUIZ_WORD_PLURAL); break;
			case 'quizzes':
				$bundle->quiz_ids = preg_replace("/[^0-9\.,]/", '', $bundle->quiz_ids);
				$quizzes = $wpdb->get_results("SELECT name, ID FROM ".WATUPRO_EXAMS." WHERE ID IN (".$bundle->quiz_ids.") ORDER BY name");
				
				$output = '<ul class="watupro-bundle-list">';
				foreach($quizzes as $quiz) {
					$output .= '<li><a href="'.watupro_exam_url_raw($quiz->ID, $quiz).'">'.stripslashes($quiz->name).'</a></li>'."\n";
				}
				$output .=  '</ul>';
			break;
			case 'category':				
				// select cats, then for each cat select paid & published tests
				$cat_ids = explode("|", $bundle->cat_ids);
				$cat_ids = array_filter($cat_ids);
				$cat_ids = array_map('intval', $cat_ids);
				
				$output = '';				
				foreach($cat_ids as $cat_id) {
					$cat = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".WATUPRO_CATS." WHERE ID=%d", $cat_id));
					
					$output .= '<h3 class="watupro-bundle-list-cat">'.stripslashes($cat->name)."</h3>\n";
					
					// select quizzes
					$quizzes = $wpdb->get_results($wpdb->prepare("SELECT name, ID FROM ".WATUPRO_EXAMS." WHERE cat_id=%d AND fee>0 ORDER BY name", $cat_id));
					if(count($quizzes)) {
						$output .= '<ul class="watupro-bundle-list">';
						
						foreach($quizzes as $quiz) {
							$url = watupro_exam_url_raw($quiz->ID, $quiz);
							if(!empty($url)) $output .= '<li><a href="'.$url.'">'.stripslashes($quiz->name).'</a></li>'."\n";
						}
						
						$output .= "</ul>\n";
					} // end if count quizzes
				} 
			break;
		}
		
		return $output;
	} // end list_quizzes
	
	// runs after successful payment to automatically insert user groups or WP roles required
	// figures out categories of the quiz or bundle. Then if these categories are restricted to roles or user groups. 
	// Then if the option on the payment settings page is selected, it assigns these roles or groups
	static function ensure_access($user_id = 0, $quiz_id = 0, $bundle_id = 0) {
		global $wpdb;
		if($user_id == 0) return false; // this is only for logged in users
		$user = get_userdata($user_id);
		
		if(get_option('watupro_paid_assign_groups') != '1') return false;
		
		$cat_ids = [];
		
		if($quiz_id > 0) {
			$cat_id = $wpdb->get_var($wpdb->prepare("SELECT cat_id FROM ".WATUPRO_EXAMS." WHERE ID=%d", $quiz_id));
			
			if($cat_id) $cat_ids[] = $cat_id;
		} // end single quiz
		
		if($bundle_id > 0) {
			// cats in this bundle
			$bundle = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".WATUPRO_BUNDLES." WHERE ID=%d", $bundle_id));
			if($bundle->bundle_type == 'num_quizzes') return false; // for this type no categories
			
			switch($bundle->bundle_type) {
				case 'category':
					$cat_ids = array_filter(explode("|", $bundle->cat_ids));
				break;
				
				case 'quizzes':
				   $quiz_ids = array_filter(explode("|", $bundle->quiz_ids));
				   $cats = $wpdb->get_results("SELECT cat_id FROM ".WATUPRO_EXAMS." WHERE ID IN (".implode(',', $quiz_ids).")");
				   foreach($cats as $cat) {
				   	if($cat->cat_id > 0) $cat_ids[] = $cat->cat_id;
				   }
				break;
			} // end switch
		} // end bundle
		
		// now we have cat IDs, let's figure out associated roles or groups
		$cats = $wpdb->get_results("SELECT * FROM ".WATUPRO_CATS." WHERE ID IN (".implode(',', $cat_ids).") AND ugroups != '' AND ugroups != '||' ");
		
		if(get_option('watupro_use_wp_roles') == 1) {
			$roles_to_assign = [];
			foreach($cats as $cat) {
				$roles = array_filter(explode('|', $cat->ugroups));
				foreach($roles as $role) {
					if(!in_array($role, $roles_to_assign)) $roles_to_assign[] = $role;
				}
			} // end foreach cat
			
			// now assign the roles
			foreach($roles_to_assign as $role_name) {
				$user->add_role($role_name);
			}
		}
		else {
			// use user groups
			$groups_to_assign = [];
			foreach($cats as $cat) {
				$groups = watupro_int_array(array_filter(explode('|', $cat->ugroups)));
				foreach($groups as $group) {
					if(!in_array($group, $groups_to_assign)) $groups_to_assign[] = $group;
				}
			} // end foreach cat
			
			// assign the groups
			$old_user_groups = get_user_meta($user_id, 'watupro_groups', true);
			$groups_to_assign = array_merge($old_user_groups, $groups_to_assign);
			$groups_to_assign = array_unique($groups_to_assign);
			watupro_assign_groups($user_id, $groups_to_assign);
		}
	} // end ensure access
>>>>>>> branch/6.7.2
}