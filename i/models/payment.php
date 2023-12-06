<?php
// class handling payment restrictions, IPN etc
class WatuPROPayment {
	static $pdt_mode = false;	
	static $pdt_response = '';
	
	// render payment button and info if any	
<<<<<<< HEAD
	static function render($exam) {
		global $post, $user_ID, $watupro_keep_chars, $wpdb;
=======
	// @param $no_action_hook is used to avoid the redirect action in the view. Currently used when we have linked to Woo but using render() from 
	// $watu -> can_retake. This should display payment form only if attempts are exhausted
	static function render($exam, $is_certificate = false, $no_action_hook = false) {
		global $post, $user_ID, $watupro_keep_chars, $wpdb;
		if(!$is_certificate and $exam->fee > 0 and class_exists('WatuPROIExam') and method_exists('WatuPROIExam', 'adjust_price')) WatuPROIExam :: adjust_price($exam);
		$no_action_hook = apply_filters( 'watupro_payment_render_no_action_hook', $no_action_hook );
>>>>>>> branch/6.7.2
		
		$paypal_email = get_option("watupro_paypal");
		$accept_stripe = get_option('watupro_accept_stripe');
		$accept_paypoints = get_option('watupro_accept_paypoints');
<<<<<<< HEAD
		$other_payments = get_option("watupro_other_payments");
		$currency = get_option('watupro_currency');
		$watupro_keep_chars = true;
		
=======
		$accept_moolamojo = get_option('watupro_accept_moolamojo');
		$other_payments = get_option("watupro_other_payments");
		$currency = get_option('watupro_currency');
		$watupro_keep_chars = true;
		$advanced_settings = unserialize(stripcslashes(@$exam->advanced_settings));		
		
		// redirect to URL?	
		if(!empty($advanced_settings['paid_quiz_redirect']) and !$no_action_hook) {		
			watupro_redirect($advanced_settings['paid_quiz_redirect']);
		}
	
>>>>>>> branch/6.7.2
		// setup Stripe
		if($accept_stripe) {
				require_once(WATUPRO_PATH.'/i/lib/Stripe.php');
 
				$stripe = array(
				  'secret_key'      => get_option('watupro_stripe_secret'),
				  'publishable_key' => get_option('watupro_stripe_public')
				);
				 
<<<<<<< HEAD
				Stripe::setApiKey($stripe['secret_key']);
		}		
		
		if(empty($paypal_email) and empty($other_payments) and empty($accept_stripe) and empty($accept_paypoints)) {
			_e('There is fee to take this quiz but no Paypal ID or other payment method has been set.', 'watupro');
=======
				\Stripe\Stripe::setApiKey($stripe['secret_key']);
		}		
		
		if(empty($paypal_email) and empty($other_payments) and empty($accept_stripe) and empty($accept_paypoints) and empty($accept_moolamojo)) {
			// WooCommerce bridge installed and the quiz is linked to a product?
			if(function_exists('ww_bridge_init') and !empty($advanced_settings['woo_product_id']) and !$no_action_hook) {
				$ww_bridge_redirect = apply_filters( 'watupro_payment_render_ww_bridge_redirect', true, $exam, $user_ID );
				$link = get_permalink($advanced_settings['woo_product_id']);
				
				if ( $ww_bridge_redirect and $link ) {
					watupro_redirect($link);
				} else {
					do_action( 'watupro_payment_render_ww_bridge', $exam, $user_ID, $link );
					return true;
				}
			}
			
			printf(__('There is fee to take this %s but no Paypal ID or other payment method has been set.', 'watupro'), WATUPRO_QUIZ_WORD);
>>>>>>> branch/6.7.2
			return false;
		}
		
		// any coupon codes that can be used?
<<<<<<< HEAD
		$any_coupons = $wpdb->get_var("SELECT id FROM ".WATUPRO_COUPONS." WHERE num_uses = 0 OR (num_uses - times_used) > 0");
=======
		$any_coupons = $wpdb->get_var("SELECT id FROM ".WATUPRO_COUPONS." WHERE num_uses = 0 OR ( CAST(num_uses as SIGNED)- CAST(times_used as SIGNED) ) > 0");
		if($is_certificate) $any_coupons = false; // at this point we don't support coupons for certificate payments
		
		// let's allow pre-filling the coupon code if any and if $_POST is not overwriting it
		if(empty($_POST['watupro_coupon'])) {
			$existing_coupon = WatuPROICoupons :: existing_coupon($user_ID);
			if(!empty($existing_coupon)) $_POST['watupro_coupon'] = sanitize_text_field($existing_coupon);
		}
>>>>>>> branch/6.7.2
		
		// coupon code inserted
		if($any_coupons and !empty($_POST['watupro_coupon'])) {
			// check if the coupon is valid
			$coupon = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".WATUPRO_COUPONS." WHERE code=%s", trim($_POST['watupro_coupon'])));
			
			if(WatuPROICoupons :: is_valid($coupon, $exam->ID)) {
				// apply to the price
				$old_fee = $exam->fee;
				$exam->fee = WatuPROICoupons :: apply($coupon, $exam->fee, $user_ID);	
				$coupon_applied = true;			
			}
<<<<<<< HEAD
=======
			else $coupon_error = __('The discount code you have entered is wrong or expired.', 'watupro');
>>>>>>> branch/6.7.2
		}
		
		// replace shortcodes
		if(!empty($other_payments)) {
			$other_payments = str_replace("[AMOUNT]", $exam->fee, $other_payments);
			$other_payments = str_replace("[USER_ID]", $user_ID, $other_payments);
			$other_payments = str_replace("[EXAM_TITLE]", $exam->name, $other_payments);
			$other_payments = str_replace("[EXAM_ID]", $exam->ID, $other_payments);
<<<<<<< HEAD
			$other_payments = str_replace('[ITEM_TYPE]', 'exam', $other_payments);
=======
			$item_type = $is_certificate ? 'certificate' : 'exam';
			$other_payments = str_replace('[ITEM_TYPE]', $item_type, $other_payments);
>>>>>>> branch/6.7.2
		}
		
		// paypoints - render only if user balance allows it.
		if(!empty($accept_paypoints)) {
			$paypoints_price = get_option('watupro_paypoints_price');
			$paypoints_button = get_option('watupro_paypoints_button');
			
			$cost_in_points = round($exam->fee * $paypoints_price);
			$user_points = get_user_meta($user_ID, 'watuproplay-points', true);	
			
			if($user_points < $cost_in_points) $paybutton = __('Not enough points.', 'watupro');
			else {
				$url = admin_url("admin-ajax.php?action=watupro_pay_with_points");
				$paybutton = "<input type='button' value='".sprintf(__('Pay %d points', 'watupro'), $cost_in_points)."' onclick='WatuPROPay.payWithPoints({$exam->ID}, \"$url\");'>";
			}
			
			// replace the codes in the design
			$paypoints_button = str_replace('{{{points}}}', $cost_in_points, $paypoints_button);
			$paypoints_button = str_replace('{{{user-points}}}', $user_points, $paypoints_button);
			$paypoints_button = str_replace('{{{button}}}', $paybutton, $paypoints_button);
			$paypoints_button = stripslashes($paypoints_button);
		}
		
<<<<<<< HEAD
		// if user is not logged in, generate access code and place in session. Don't store it in the DB
		if(!is_user_logged_in()) {
			// is there access code in session?
			if(empty($_SESSION['watupro_access_code_'.$exam->ID])) {
				// if not, create
				$access_code = substr(md5($_SERVER['REMOTE_ADDR'].microtime()), 0, 10);
				$_SESSION['watupro_access_code_'.$exam->ID] = $access_code;	
			} else $access_code = $_SESSION['watupro_access_code_'.$exam->ID];
=======
		if(!empty($accept_moolamojo) and class_exists('MoolaMojo')) {
			$moola_price = get_option('watupro_moolamojo_price');
			$moola_button = get_option('watupro_moolamojo_button');
			
			$cost_in_moola = round($exam->fee * $moola_price);
			
			// get balance
			$moola_balance = get_user_meta($user_ID, 'moolamojo_balance', true);
			
			if($moola_balance < $cost_in_moola) $paybutton = sprintf(__('Not enough %s.', 'watupro'), MOOLA_CURRENCY);
			else {
				$url = admin_url("admin-ajax.php?action=watupro_pay_with_moolamojo");
				$paybutton = "<input type='button' value='".sprintf(__('Pay %d %s', 'watupro'), $cost_in_moola, MOOLA_CURRENCY)."' onclick='WatuPROPay.payWithMoolaMojo({$exam->ID}, \"$url\");'>";
			}
			
			// replace the codes in the design
			$moola_button = str_replace('{{{credits}}}', $cost_in_moola, $moola_button);
			$moola_button = str_replace('{{{button}}}', $paybutton, $moola_button);
			$moola_button = stripslashes($moola_button);
>>>>>>> branch/6.7.2
		}
			
		if(@file_exists(get_stylesheet_directory().'/watupro/i/views/payment.php')) require get_stylesheet_directory().'/watupro/i/views/payment.php';
		else require WATUPRO_PATH."/i/views/payment.php";
		return true;
	}
	
	// check if there is payment made from this user for this exam
	static function valid_payment($exam) {
		global $wpdb, $user_ID;
		
<<<<<<< HEAD
		if(empty($_SESSION['watupro_access_code_'.$exam->ID]) and empty($_SESSION['watupro_nouser_coupon']) and (empty($user_ID) or !is_numeric($user_ID))) return false;
		
		// user or access code clause
		$identify_sql = (empty($user_ID) and !empty($_SESSION['watupro_access_code_'.$exam->ID])) ? 
			$wpdb->prepare("access_code=%s", $_SESSION['watupro_access_code_'.$exam->ID]) :
			$wpdb->prepare("user_id=%d", $user_ID); 
		
		// any bundles that contain this quiz and the user paid for them?
		$valid_bundle_payment = $wpdb->get_var("SELECT tP.ID FROM ".WATUPRO_PAYMENTS." tP
			JOIN ".WATUPRO_BUNDLES." tB ON tB.ID = tP.bundle_id 
			WHERE $identify_sql AND status='completed' AND bundle_id!=0 AND (
			  (tB.bundle_type = 'category' AND cat_id={$exam->cat_id}) 
			  OR
			  (tB.bundle_type = 'quizzes' AND (quiz_ids LIKE '{$exam->ID}' 
				  OR quiz_ids LIKE '%,{$exam->ID}' OR quiz_ids LIKE '%,{$exam->ID},%' OR quiz_ids LIKE '{$exam->ID},%') )
			)");
			
		if(!empty($valid_bundle_payment)) return true;	
=======
		// the exam is accessible to this user (by group or role privileges?)
		$advanced_settings = unserialize(stripslashes($exam->advanced_settings));
		$use_wp_roles = get_option('watupro_use_wp_roles');	
		
		// by roles
		if($user_ID and $use_wp_roles and !empty($advanced_settings['free_access_roles']) and is_array($advanced_settings['free_access_roles'])) {
			// check if user has any of the free access roles
			$user = get_userdata($user_ID);
			foreach($advanced_settings['free_access_roles'] as $role) {
				if ( in_array( $role, (array) $user->roles ) ) return true;
			}
		}
		
		// by user group
		if($user_ID and !$use_wp_roles and !empty($advanced_settings['free_access_groups']) and is_array($advanced_settings['free_access_groups'])) {
			// check if user has any of the free access roles
			$user_groups = get_user_meta($user_ID, "watupro_groups", true);
			if(!is_array($user_groups)) $user_groups = array($user_groups);
			foreach($user_groups as $group) {
				if(in_array( $group, $advanced_settings['free_access_groups'])) return true;
			}
		}
		
		// by BP user group in case BuddyPress Bridge is installed
		if(class_exists('WatuPROBPBridge') and method_exists('WatuPROBPBridge', 'free_access')) {
			if(WatuPROBPBridge :: free_access($advanced_settings)) return true;
		} // end BP groups check
		
		// allow other plugins to plug here for a free access
		$external_valid = apply_filters('watupro-external-valid-payment', false, $exam);
		if($external_valid) return true;

		if(empty($_COOKIE['watupro_access_code_'.$exam->ID]) and empty($_COOKIE['watupro_nouser_coupon']) and (empty($user_ID) or !is_numeric($user_ID))) return false;
		
		// user or access code clause
		$identify_sql = (empty($user_ID) and !empty($_COOKIE['watupro_access_code_'.$exam->ID])) ? 
			$wpdb->prepare("access_code=%s", sanitize_text_field($_COOKIE['watupro_access_code_'.$exam->ID])) :
			$wpdb->prepare("user_id=%d", $user_ID); 
			
			//echo $identify_sql;
		
		// any bundles that contain this quiz and the user paid for them?		
		$valid_bundle_payment = $wpdb->get_var("SELECT tP.ID FROM ".WATUPRO_PAYMENTS." tP
			JOIN ".WATUPRO_BUNDLES." tB ON tB.ID = tP.bundle_id 
			WHERE $identify_sql AND status='completed' AND bundle_id!=0 AND (
			  (tB.bundle_type = 'category' AND (cat_ids=".$exam->cat_id." OR cat_ids LIKE '%|".$exam->cat_id."|%') ) 
			  OR
			  (tB.bundle_type = 'quizzes' AND (quiz_ids LIKE '{$exam->ID}' 
				  OR quiz_ids LIKE '%,{$exam->ID}' OR quiz_ids LIKE '%,{$exam->ID},%' OR quiz_ids LIKE '{$exam->ID},%') )
			  OR	  
			  (tB.bundle_type = 'num_quizzes' AND tP.used_quiz_ids LIKE '%|".$exam->ID."|%' )
			)
			AND (is_time_limited=0 
			   OR (is_time_limited=1 AND tP.date >= '".date('Y-m-d', current_time('timestamp'))."' - INTERVAL tB.time_limit DAY))");
					
		if(!empty($valid_bundle_payment)) {
			session_write_close();
			return true;
		}	
		
		// now we have to check for valid "num_quizzes" bundle payment that doesn't yet have the quiz in quizzes_used
		$valid_numquiz_bundle_payment = $wpdb->get_row("SELECT tP.ID as ID, tP.used_quiz_ids FROM ".WATUPRO_PAYMENTS." tP
			JOIN ".WATUPRO_BUNDLES." tB ON tB.ID = tP.bundle_id 
			WHERE $identify_sql AND status='completed' AND bundle_id!=0 AND tB.bundle_type = 'num_quizzes' AND tP.num_quizzes_used < tB.num_quizzes			
			AND (is_time_limited=0 
			   OR (is_time_limited=1 AND tP.date >= '".date('Y-m-d', current_time('timestamp'))."' - INTERVAL tB.time_limit DAY))");
			
		// found? Update num quizzes and quizzes used and return true
		if(!empty($valid_numquiz_bundle_payment->ID)) {		
				
			$used_quiz_ids = explode('|', $valid_numquiz_bundle_payment->used_quiz_ids);
			$used_quiz_ids[] = $exam->ID;
			$used_quiz_ids = array_filter($used_quiz_ids);
			
			$wpdb->query($wpdb->prepare("UPDATE ".WATUPRO_PAYMENTS." 
				SET num_quizzes_used = num_quizzes_used + 1, used_quiz_ids=%s WHERE ID=%d",
				'|'.implode('|', $used_quiz_ids).'|', $valid_numquiz_bundle_payment->ID));
			session_write_close();
			return true;
		}
		// end checking for "num quizzes" bundles
>>>>>>> branch/6.7.2
		
		// if no bundles, check for quiz payment	
		$payment = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".WATUPRO_PAYMENTS."
			WHERE exam_id=%d AND $identify_sql ORDER BY ID DESC LIMIT 1", $exam->ID));
		
		if(empty($payment->ID) or $payment->status != 'completed') {			
			// if valid 100% discount coupon code is entered, we'll still proceed
			$existing_coupon = WatuPROICoupons :: existing_coupon($user_ID);
<<<<<<< HEAD
			if(!empty($_POST['watupro_coupon']) or !empty($existing_coupon)) {
				$coupon_code = empty($_POST['watupro_coupon']) ? $existing_coupon : $_POST['watupro_coupon'];
				$coupon = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".WATUPRO_COUPONS." WHERE code=%s", trim($coupon_code)));
=======
			
			if(!empty($_POST['watupro_coupon']) or !empty($existing_coupon)) {
				$coupon_code = empty($_POST['watupro_coupon']) ? $existing_coupon : sanitize_text_field($_POST['watupro_coupon']);
				$coupon = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".WATUPRO_COUPONS." WHERE code=%s", trim($coupon_code)));
				
>>>>>>> branch/6.7.2
				if(!empty($coupon) and $coupon->discount >= 100 and $coupon->disc_type == 'percent') {
					// check valid and if yes, return true
					if(WatuPROICoupons :: is_valid($coupon, $exam->ID)) {				
						// insert coupon code payment
						$wpdb->query($wpdb->prepare("INSERT INTO ".WATUPRO_PAYMENTS." SET 
							exam_id=%d, user_id=%s, date=CURDATE(), amount=%s, status='completed', paycode='', 
							method=%s, bundle_id=%d, access_code=%s", 
<<<<<<< HEAD
							$exam->ID, $user_ID, 0, sprintf(__('Coupon %s', 'watupro'), $coupon_code), 0, @$_SESSION['watupro_access_code_'.$exam->ID]));
							
						// store coupon in session or user meta
						if($user_ID) update_user_meta($user_ID, 'watupro_coupon', $coupon->code);
						else $_SESSION['watupro_nouser_coupon'] = $coupon->code;			
						
						// apply the coupon only if not already using $existing_coupon						
=======
							$exam->ID, $user_ID, 0, sprintf(__('Coupon %s', 'watupro'), $coupon_code), 0, sanitize_text_field(@$_COOKIE['watupro_access_code_'.$exam->ID])));
							
						// store coupon in session or user meta
						if($user_ID) update_user_meta($user_ID, 'watupro_coupon', $coupon->code);
						else { // setcookie('watupro_nouser_coupon', $coupon->code, time() + 3600*24, '/');
						  	?>
						  	<script type="text/javascript" >
						  	var d = new Date();
							d.setTime(d.getTime() + (24*3600*1000));
							var expires = "expires="+ d.toUTCString();     				
						  	document.cookie = "watupro_nouser_coupon=<?php echo $coupon->code?>;" + expires + ";path=/";
						  	</script>
						  	<?php
						}		
						
						// apply the coupon only if not already using $existing_coupon				
						session_write_close();		
>>>>>>> branch/6.7.2
						WatuPROICoupons :: coupon_used($coupon, $user_ID);
						return true;
					}
				}
			} // end handling 100% coupon code
			
			return false; // we didn't have valid 100% coupon and have no valid quiz or bundle payment so return false 
		}
<<<<<<< HEAD
		
=======
		//echo "VALID PAYMENT FOUND";
>>>>>>> branch/6.7.2
		// this happens in case of valid quiz payment	
		return true;	
	}
	
<<<<<<< HEAD
=======
	// check for valid certificate payment
	static function valid_certificate_payment($certificate) {
		global $wpdb, $user_ID;
		$owner_id = $user_ID;
		
		if(current_user_can(WATUPRO_MANAGE_CAPS)) return true; // managers can access
		
		$public_certificates = get_option('watupro_public_certificates');
		
		// if this is a public certificate then the viewer may not be the same as the owner so we have to make sure the owner has paid
		if($public_certificates == 1) {
			$taking = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".WATUPRO_TAKEN_EXAMS."
				WHERE ID=%d", intval($_GET['taking_id'])));
			$GLOBALS['watupro_view_taking_id'] = $taking->ID;	
		
			$owner_id = $taking->user_id;
		}
		
		if(empty($_COOKIE['watupro_certaccess_code_'.$certificate->ID]) and empty($_COOKIE['watupro_nouser_coupon']) and (empty($owner_id) or !is_numeric($owner_id))) return false;
		
		// user or access code clause
		$identify_sql = (empty($owner_id) and !empty($_COOKIE['watupro_certaccess_code_'.$certificate->ID])) ? 
			$wpdb->prepare("access_code=%s", $_COOKIE['watupro_certaccess_code_'.$certificate->ID]) :
			$wpdb->prepare("user_id=%d", $owner_id); 
			
		// no bundles for certificates at the moment. Check for valid payment	
		$payment = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".WATUPRO_PAYMENTS."
			WHERE certificate_id=%d AND $identify_sql ORDER BY ID DESC LIMIT 1", $certificate->ID));
		
		// no coupons on paid certificates as well	
		if(empty($payment->ID) or $payment->status != 'completed') {		
		   if(function_exists('ww_bridge_display_payment_page')) ww_bridge_display_payment_page($certificate->ID, true);	
			return false;  
		}		
		
		return true;
	} // end valid certificate payment
	
>>>>>>> branch/6.7.2
	// handle query vars
	static function query_vars($vars) {
		// http://www.james-vandyne.com/process-paypal-ipn-requests-through-wordpress/
		$new_vars = array('watupro');
		$vars = array_merge($new_vars, $vars);
	   return $vars;
	} 
	
	// handle Paypal IPN request
	static function parse_request($wp) {
		// only process requests with "watupro=paypal"
	   if (array_key_exists('watupro', $wp->query_vars) 
<<<<<<< HEAD
	            && ($wp->query_vars['watupro'] == 'paypal' or $wp->query_vars['watupro'] == 'paypal_bundle')) {
=======
	            && ($wp->query_vars['watupro'] == 'paypal' or $wp->query_vars['watupro'] == 'paypal_bundle' or $wp->query_vars['watupro'] == 'paypal_certificate')) {
>>>>>>> branch/6.7.2
	        self::paypal_ipn($wp);
	   }	
	}
	
	// process paypal IPN
	static function paypal_ipn($wp = null) {
		global $wpdb;
		echo "<!-- WATUPROCOMMENT paypal IPN -->";
		
		// print_r($_GET);
		// read the post from PayPal system and add 'cmd'
		$pdt_mode = false;
		if(!empty($_GET['tx']) and !empty($_GET['watupro_pdt']) and get_option('watupro_use_pdt')==1) {
			// PDT			
			$req = 'cmd=_notify-synch';
			$tx_token = strtoupper($_GET['tx']);
			$auth_token = get_option('watupro_pdt_token');
			$req .= "&tx=$tx_token&at=$auth_token";
			$pdt_mode = true;
			$success_responce = "SUCCESS";
		}
		else {	
			// IPN		
			$req = 'cmd=_notify-validate';
			foreach ($_POST as $key => $value) { 
			  $value = urlencode(stripslashes($value)); 
			  $req .= "&$key=$value";
			}
			$success_responce = "VERIFIED";
		}		
		
		self :: $pdt_mode = $pdt_mode;	
		
		$paypal_host = "www.paypal.com";
		$paypal_sandbox = get_option('watupro_paypal_sandbox');
		if($paypal_sandbox == '1') $paypal_host = 'www.sandbox.paypal.com';
		
		// post back to PayPal system to validate
		// see CURL or fsockopen
		if(function_exists('curl_version')) {
			$ch = curl_init('https://'.$paypal_host.'/cgi-bin/webscr');
			curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);		
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
			curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close'));
			
			if( !($res = curl_exec($ch)) ) {
			   self::log_and_exit("Got " . curl_error($ch) . " when processing IPN data");
			   curl_close($ch);
			   exit;
			}
			curl_close($ch);			
			if (strstr ($res, $success_responce) or $paypal_sandbox == '1') self :: paypal_ipn_verify($res);
			else return self::log_and_exit("Paypal result is not VERIFIED: $res");
		}
		else {
			$header="";
			$header .= "POST /cgi-bin/webscr HTTP/1.0\r\n";
			$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
			$header .= "Content-Length: " . strlen($req) . "\r\n";
			$header .="Host: $paypal_host\r\n"; 
			$header .="Connection: close\r\n\r\n";		
			$fp = fsockopen ($paypal_host, 80, $errno, $errstr, 30);
			
			if($fp) {
				fputs ($fp, $header . $req);
				$pp_response = '';
			   while (!feof($fp)) {
			      $res = fgets ($fp, 1024);	
			      $pp_response .= $res;	     
			      if (strstr ($res, $success_responce) or $paypal_sandbox == '1') {
			      	self :: paypal_ipn_verify($pp_response);
			      	exit;
			     	}			     	
			   }  
			   fclose($fp);
			   return self::log_and_exit("Paypal result is not VERIFIED: $pp_response");  
			} 
			else return self::log_and_exit("Can't connect to Paypal via fsockopen");
		}
		exit;
	}
	
	static function paypal_ipn_verify($pp_response) {
		global $wpdb, $user_ID, $post;
		
		// when we are in PDT mode let's assign all lines as POST variables
		if(self :: $pdt_mode) {
			 $lines = explode("\n", $pp_response);	
				if (strcmp ($lines[0], "SUCCESS") == 0) {
				for ($i=1; $i<count($lines);$i++){
					if(strstr($lines[$i], '=')) list($key,$val) = explode("=", $lines[$i]);
					$_POST[urldecode($key)] = urldecode($val);
				}
			 }
			 
			 $_GET['user_id'] = $user_ID;
			 self :: $pdt_response = $pp_response;
			 
			 // access code?
<<<<<<< HEAD
			 if(empty($user_ID) and !empty($_SESSION['watupro_access_code_' . $_POST['item_number']])) {
			 	 $_GET['access_code'] = $_SESSION['watupro_access_code_' . $_POST['item_number']];
=======
			 if(empty($user_ID) and !empty($_COOKIE['watupro_access_code_' . $_POST['item_number']])) {
			 	 $_GET['access_code'] = $_COOKIE['watupro_access_code_' . $_POST['item_number']];
			 }
			 // certificate access code? we use different var for certificate payments
			 if(empty($user_ID) and !empty($_COOKIE['watupro_certaccess_code_' . $_POST['item_number']])) {
			 	 $_GET['access_code'] = $_COOKIE['watupro_certaccess_code_' . $_POST['item_number']];
>>>>>>> branch/6.7.2
			 }
		} // end PDT mode transfer from lines to $_POST	 				
					
		// check the payment_status is Completed
      // check that txn_id has not been previously processed
      // check that receiver_email is your Primary PayPal email
      // process payment
	   $payment_completed = false;
	   $txn_id_okay = false;
	   $receiver_okay = false;
	   $payment_currency_okay = false;
	   $payment_amount_okay = false;
	   $paypal_email = get_option("watupro_paypal");
	   
	   if(@$_POST['payment_status']=="Completed") {
	   	$payment_completed = true;
	   } 
	   else return self::log_and_exit("Payment status: $_POST[payment_status]");
	   
	   // check txn_id
	   $txn_exists = $wpdb->get_var($wpdb->prepare("SELECT paycode FROM {$wpdb->prefix}watupro_payments 
		   WHERE paycode=%s", $_POST['txn_id']));		   		   
		if(empty($txn_exists)) $txn_id_okay = true;
		else {
			// in PDT mode just redirect to the post because existing txn_id isn't a problem.
			// but of course we shouldn't insert second payment			
			if( self :: $pdt_mode) watupro_redirect(get_permalink(@$post->ID));
			return self::log_and_exit("TXN ID exists: $txn_exists");
		}  
		
		// check receiver email
		if($_POST['business']==$paypal_email or $_POST['receiver_id'] == $paypal_email) {
			$receiver_okay = true;
		}
		else return self::log_and_exit("Business email is wrong: $_POST[business]");
		
		// check payment currency
		if($_POST['mc_currency']==get_option("watupro_currency")) {
			$payment_currency_okay = true;
		}
		else return self::log_and_exit("Currency is $_POST[mc_currency]"); 
		
		// check amount					
		if(@$_GET['watupro'] == 'paypal_bundle' or @$_GET['watupro_pdt_bundle'] == 1) {
			$bundle = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}watupro_bundles WHERE ID=%d", $_POST['item_number']));
			$fee = $bundle->price;
			$coupon_exam_id = 0;
		} 
<<<<<<< HEAD
		else {
			$exam = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}watupro_master WHERE ID=%d", $_POST['item_number']));
			$fee = $exam->fee;
			$coupon_exam_id = $exam->ID;
=======
		else {			
			if(empty($_GET['watupro_is_certificate'])) {
				// by default handle exam payments
				$exam = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}watupro_master WHERE ID=%d", $_POST['item_number']));
				if($exam->fee > 0 and class_exists('WatuPROIExam') and method_exists('WatuPROIExam', 'adjust_price')) WatuPROIExam :: adjust_price($exam);
				$fee = $exam->fee;
				$coupon_exam_id = $exam->ID;
			}
			else {
				// certificate payments
				$certificate = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".WATUPRO_CERTIFICATES." WHERE ID=%d", intval($_POST['item_number'])));
				$fee = $certificate->fee;
				$coupon_exam_id = 0;
				$coupon_code = '';
			}
>>>>>>> branch/6.7.2
		}
		
		// used coupon?
		$coupon_code =  WatuPROICoupons :: existing_coupon($_GET['user_id']);
		 
		if(!empty($coupon_code)) {
			$coupon = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".WATUPRO_COUPONS." WHERE code=%s", trim($coupon_code)));
			if(WatuPROICoupons :: is_valid($coupon, $coupon_exam_id)) {
				// apply to the price
				$fee = WatuPROICoupons :: apply($coupon, $fee, $_GET['user_id'], false);
			}	
		}		
						
<<<<<<< HEAD
		if($_POST['mc_gross']>=$fee ) {
=======
		if($_POST['mc_gross'] >= $fee ) {
>>>>>>> branch/6.7.2
			$payment_amount_okay = true;
		}
		else return self::log_and_exit("Wrong amount: $_POST[mc_gross] when price is {$fee}"); 
		
		// everything OK, insert payment
		if($payment_completed and $txn_id_okay and $receiver_okay and $payment_currency_okay 
<<<<<<< HEAD
				and $payment_amount_okay) {						
			$wpdb->query($wpdb->prepare("INSERT INTO {$wpdb->prefix}watupro_payments SET 
				exam_id=%d, user_id=%s, date=CURDATE(), amount=%s, status='completed', paycode=%s, 
				method='paypal', bundle_id=%d, access_code=%s", 
				@$exam->ID, @$_GET['user_id'], $fee, $_POST['txn_id'], @$bundle->ID, @$_GET['access_code']));
=======
				and $payment_amount_okay) {			
            if( $fee < 0 ) $fee = 0;
			$wpdb->query($wpdb->prepare("INSERT INTO {$wpdb->prefix}watupro_payments SET 
				exam_id=%d, user_id=%s, date=CURDATE(), amount=%s, status='completed', paycode=%s, 
				method='paypal', bundle_id=%d, access_code=%s, certificate_id=%d", 
				($exam->ID ?? 0), ($_GET['user_id'] ?? 0), $fee, $_POST['txn_id'], ($bundle->ID ?? 0), 
				($_GET['access_code'] ?? ''), ($certificate->ID ?? 0) ));
				
			if(!empty($exam->ID) and !empty($_GET['user_id'])) self :: post_process($exam->ID, $_GET['user_id']);	
			if(!empty($bundle->ID) and !empty($_GET['user_id'])) self :: post_process_bundle($bundle->ID, $_GET['user_id']);	
>>>>>>> branch/6.7.2
				
			// cleanup coupon code if any
			if(!empty($coupon_code)) WatuPROICoupons :: coupon_used($coupon, $_GET['user_id']);	
			if(!self :: $pdt_mode) exit;
<<<<<<< HEAD
			else watupro_redirect(get_permalink($post->ID));
=======
			else {
				if(!empty($post->ID) and empty($_GET['watupro_is_certificate'])) watupro_redirect(esc_url(add_query_arg(array('stamp'=>time()), get_permalink( $post->ID ))));
				else watupro_redirect(esc_url(add_query_arg(array('stamp'=>time()), site_url("?watupro_view_certificate=1&taking_id=".WatuPROCertificate :: obfuscate(intval($_GET['taking_id']))."&id=".intval($_GET['id']))))); // maybe we are viewing certificate or other non-post page
			} // end PDT redirects
>>>>>>> branch/6.7.2
		}
	}
	
	// log paypal errors
	static function log_and_exit($msg) {
		// log
		$msg = "Payment error occurred at ".date(get_option('date_format').' '.get_option('time_format'))." with message: ".$msg;
		$errorlog=get_option("watupro_errorlog");
		$errorlog = $msg."\n".$errorlog;
		update_option("watupro_errorlog",$errorlog);
		
		// if we are in Paypal PDT mode just echo and don't exit
		if(self :: $pdt_mode) {
			echo $msg;
			if(get_option('watupro_debug_mode')) echo "<br>Full response: ".self :: $pdt_response;
			return true;
		}
		
		// throw exception as there's no need to contninue
		exit;
	}
	
	// process Stripe Payment
<<<<<<< HEAD
	static function Stripe($is_bundle = false) {
=======
	static function Stripe($is_bundle = false, $is_certificate = false) {
>>>>>>> branch/6.7.2
		global $wpdb, $user_ID;
		require_once(WATUPRO_PATH.'/i/lib/Stripe.php');
 
		$stripe = array(
		  'secret_key'      => get_option('watupro_stripe_secret'),
		  'publishable_key' => get_option('watupro_stripe_public')
		);
		 
<<<<<<< HEAD
		Stripe::setApiKey($stripe['secret_key']);
		$token  = $_POST['stripeToken'];
		
		if($is_bundle) {
			$bundle = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".WATUPRO_BUNDLES." WHERE ID=%d", $_POST['bundle_id']));
=======
		\Stripe\Stripe::setApiKey($stripe['secret_key']);
		$token  = $_POST['stripeToken'];
		
		if($is_bundle) {
			$bundle = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".WATUPRO_BUNDLES." WHERE ID=%d", intval($_POST['bundle_id'])));
>>>>>>> branch/6.7.2
			$fee = $bundle->price;
			$coupon_exam_id = 0;
		}
		else {
<<<<<<< HEAD
			$exam = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".WATUPRO_EXAMS." WHERE ID=%d", $_POST['exam_id']));
=======
			$exam = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".WATUPRO_EXAMS." WHERE ID=%d", intval($_POST['exam_id'])));
			if($exam->fee > 0 and class_exists('WatuPROIExam') and method_exists('WatuPROIExam', 'adjust_price')) WatuPROIExam :: adjust_price($exam);
>>>>>>> branch/6.7.2
			$fee = $exam->fee;
			$coupon_exam_id = $exam->ID;
		}
		
		// used coupon?		
		$coupon_code = WatuPROICoupons :: existing_coupon($user_ID);
<<<<<<< HEAD
=======
		
		// if it's a certificate, override exam & coupon settings
		if($is_certificate) {
			$certificate = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".WATUPRO_CERTIFICATES." WHERE ID=%d", intval($_POST['exam_id'])));
			$fee = $certificate->fee;
			$coupon_exam_id = 0;
			$coupon_code = '';
		}
>>>>>>> branch/6.7.2
		 
		if(!empty($coupon_code)) {
			$coupon = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".WATUPRO_COUPONS." WHERE code=%s", trim($coupon_code)));
			if(WatuPROICoupons :: is_valid($coupon, $coupon_exam_id)) {
				// apply to the price
				$fee = WatuPROICoupons :: apply($coupon, $fee, $user_ID, false);
			}	
		}				
		
		
		$user = get_userdata($user_ID);
		$currency = get_option('watupro_currency');
			 
		try {
<<<<<<< HEAD
			 $customer = Stripe_Customer::create(array(
=======
			 $customer = \Stripe\Customer::create(array(
>>>>>>> branch/6.7.2
		      'email' => empty($user->user_email) ? $_POST['stripeEmail'] : $user->user_email,
		      'card'  => $token
		  ));				
			
<<<<<<< HEAD
		  $charge = Stripe_Charge::create(array(
=======
		  $charge = \Stripe\Charge::create(array(
>>>>>>> branch/6.7.2
		      'customer' => $customer->id,
		      'amount'   => $fee*100,
		      'currency' => $currency
		  ));
		} 
		catch (Exception $e) {
			wp_die($e->getMessage());
		}	  
		
<<<<<<< HEAD
		// insert payment record
		$wpdb->query($wpdb->prepare("INSERT INTO ".WATUPRO_PAYMENTS." SET 
			exam_id=%d, user_id=%s, date=CURDATE(), amount=%s, status='completed', paycode=%s, 
			method='stripe', bundle_id=%d, access_code=%s", 
			@$exam->ID, $user_ID, $fee, $customer->ID, @$bundle->ID, @$_SESSION['watupro_access_code_'.@$exam->ID]));
=======
		$var_name = $is_certificate ? 'certaccess' : 'access';		
		
		// insert payment record
		$wpdb->query($wpdb->prepare("INSERT INTO ".WATUPRO_PAYMENTS." SET 
			exam_id=%d, user_id=%s, date=CURDATE(), amount=%s, status='completed', paycode=%s, 
			method='stripe', bundle_id=%d, access_code=%s, certificate_id=%d", 
			@$exam->ID, $user_ID, $fee, $customer->ID, @$bundle->ID, sanitize_text_field(@$_COOKIE['watupro_'.$var_name.'_code_'.@$exam->ID]), @$certificate->ID));
			
		if(!empty($exam->ID)) self :: post_process($exam->ID, $user_ID);	
		if(!empty($bundle->ID) and !empty($user_ID)) self :: post_process_bundle($bundle->ID, $user_ID);
>>>>>>> branch/6.7.2
			
	   // cleanup coupon code if any
		if(!empty($coupon_code)) WatuPROICoupons :: coupon_used($coupon, $user_ID);			
			
		// redirect to self to avoid inserting again
		watupro_redirect($_SERVER['REQUEST_URI']);	
	}	
	
	// when paid exam is completed see whether we have to change the associated payment status
	static function completed_exam($taking_id, $exam) {
		global $wpdb, $user_ID;
		
		// update the last payment of this user to status "used"
		$payment_id = $wpdb->get_var($wpdb->prepare("SELECT ID FROM ".WATUPRO_PAYMENTS."
			WHERE exam_id=%d AND user_id=%d ORDER BY ID DESC LIMIT 1", $exam->ID, $user_ID));
		
		if(!empty($payment_id)) $wpdb->query($wpdb->prepare("UPDATE ".WATUPRO_PAYMENTS." SET
			status='used' WHERE ID=%d AND user_id=%d", $payment_id, $user_ID));
	}
<<<<<<< HEAD
}
=======
	
	// post process payment. This happens for example to mark previus attempts as ignored so the user can re-start quiz taking
	// IMPORTANT: This function should be called also from Woo and EDD bridges!
	static function post_process($exam_id, $user_id) {
		global $wpdb;
		
		if(empty($exam_id) or empty($user_id)) return false;
		$exam_id = intval($exam_id);
		$user_id = intval($user_id);
		
		$advanced_settings = $wpdb->get_var($wpdb->prepare("SELECT advanced_settings FROM {$wpdb->prefix}watupro_master WHERE ID=%d", $exam_id));
		$advanced_settings = unserialize(stripslashes($advanced_settings));
		
		if(!empty($advanced_settings['payment_restarts_attempts'])) {
			$wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}watupro_taken_exams SET ignore_attempt=1 
				WHERE in_progress=0 AND exam_id=%d AND user_id=%d", $exam_id, $user_id));
		}
		
		WatuPROPayments :: ensure_access($user_id, $exam_id, 0);		
	} // end post process
	
	// post process bundles. Calls the above function for each quiz inside the bundle IF the bundle is about specific quizzes or a category
	static function post_process_bundle($bundle_id, $user_id) {
		global $wpdb;

		if(empty($bundle_id) or empty($user_id)) return false;
		$bundle_id = intval($bundle_id);
		$user_id = intval($user_id);
		
		$bundle = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}watupro_bundles WHERE ID=%d", $bundle_id));

		if($bundle->bundle_type == 'quizzes') {
			$quiz_ids = explode(',', $bundle->quiz_ids);
			$quiz_ids = watupro_int_array($quiz_ids);
			foreach($quiz_ids as $quiz_id) {
				self :: post_process($quiz_id, $user_id);
			}
		} // end if quizzes
		
		if($bundle->bundle_type == 'category') {
			 $cat_ids = explode('|', $bundle->cat_ids);
			 $cat_ids = watupro_int_array($cat_ids);
			 
			 // select all paid quizzes within these categories
			 $quiz_ids = $wpdb->get_results("SELECT ID FROM {$wpdb->prefix}watupro_master WHERE cat_id IN (".implode(',', $cat_ids).") AND fee > 0");
			 
			 foreach($quiz_ids as $quiz_id) {
				self :: post_process($quiz_id->ID, $user_id);
			}
		} // end if category
		
		WatuPROPayments :: ensure_access($user_id, 0, $bundle_id);
	} // end post_process_bundle
}
>>>>>>> branch/6.7.2
