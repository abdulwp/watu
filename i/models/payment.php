<?php
// class handling payment restrictions, IPN etc
class WatuPROPayment {
	static $pdt_mode = false;	
	static $pdt_response = '';
	
	// render payment button and info if any	
	static function render($exam) {
		global $post, $user_ID, $watupro_keep_chars, $wpdb;
		
		$paypal_email = get_option("watupro_paypal");
		$accept_stripe = get_option('watupro_accept_stripe');
		$accept_paypoints = get_option('watupro_accept_paypoints');
		$other_payments = get_option("watupro_other_payments");
		$currency = get_option('watupro_currency');
		$watupro_keep_chars = true;
		
		// setup Stripe
		if($accept_stripe) {
				require_once(WATUPRO_PATH.'/i/lib/Stripe.php');
 
				$stripe = array(
				  'secret_key'      => get_option('watupro_stripe_secret'),
				  'publishable_key' => get_option('watupro_stripe_public')
				);
				 
				Stripe::setApiKey($stripe['secret_key']);
		}		
		
		if(empty($paypal_email) and empty($other_payments) and empty($accept_stripe) and empty($accept_paypoints)) {
			_e('There is fee to take this quiz but no Paypal ID or other payment method has been set.', 'watupro');
			return false;
		}
		
		// any coupon codes that can be used?
		$any_coupons = $wpdb->get_var("SELECT id FROM ".WATUPRO_COUPONS." WHERE num_uses = 0 OR (num_uses - times_used) > 0");
		
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
		}
		
		// replace shortcodes
		if(!empty($other_payments)) {
			$other_payments = str_replace("[AMOUNT]", $exam->fee, $other_payments);
			$other_payments = str_replace("[USER_ID]", $user_ID, $other_payments);
			$other_payments = str_replace("[EXAM_TITLE]", $exam->name, $other_payments);
			$other_payments = str_replace("[EXAM_ID]", $exam->ID, $other_payments);
			$other_payments = str_replace('[ITEM_TYPE]', 'exam', $other_payments);
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
		
		// if user is not logged in, generate access code and place in session. Don't store it in the DB
		if(!is_user_logged_in()) {
			// is there access code in session?
			if(empty($_SESSION['watupro_access_code_'.$exam->ID])) {
				// if not, create
				$access_code = substr(md5($_SERVER['REMOTE_ADDR'].microtime()), 0, 10);
				$_SESSION['watupro_access_code_'.$exam->ID] = $access_code;	
			} else $access_code = $_SESSION['watupro_access_code_'.$exam->ID];
		}
			
		if(@file_exists(get_stylesheet_directory().'/watupro/i/views/payment.php')) require get_stylesheet_directory().'/watupro/i/views/payment.php';
		else require WATUPRO_PATH."/i/views/payment.php";
		return true;
	}
	
	// check if there is payment made from this user for this exam
	static function valid_payment($exam) {
		global $wpdb, $user_ID;
		
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
		
		// if no bundles, check for quiz payment	
		$payment = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".WATUPRO_PAYMENTS."
			WHERE exam_id=%d AND $identify_sql ORDER BY ID DESC LIMIT 1", $exam->ID));
		
		if(empty($payment->ID) or $payment->status != 'completed') {			
			// if valid 100% discount coupon code is entered, we'll still proceed
			$existing_coupon = WatuPROICoupons :: existing_coupon($user_ID);
			if(!empty($_POST['watupro_coupon']) or !empty($existing_coupon)) {
				$coupon_code = empty($_POST['watupro_coupon']) ? $existing_coupon : $_POST['watupro_coupon'];
				$coupon = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".WATUPRO_COUPONS." WHERE code=%s", trim($coupon_code)));
				if(!empty($coupon) and $coupon->discount >= 100 and $coupon->disc_type == 'percent') {
					// check valid and if yes, return true
					if(WatuPROICoupons :: is_valid($coupon, $exam->ID)) {				
						// insert coupon code payment
						$wpdb->query($wpdb->prepare("INSERT INTO ".WATUPRO_PAYMENTS." SET 
							exam_id=%d, user_id=%s, date=CURDATE(), amount=%s, status='completed', paycode='', 
							method=%s, bundle_id=%d, access_code=%s", 
							$exam->ID, $user_ID, 0, sprintf(__('Coupon %s', 'watupro'), $coupon_code), 0, @$_SESSION['watupro_access_code_'.$exam->ID]));
							
						// store coupon in session or user meta
						if($user_ID) update_user_meta($user_ID, 'watupro_coupon', $coupon->code);
						else $_SESSION['watupro_nouser_coupon'] = $coupon->code;			
						
						// apply the coupon only if not already using $existing_coupon						
						WatuPROICoupons :: coupon_used($coupon, $user_ID);
						return true;
					}
				}
			} // end handling 100% coupon code
			
			return false; // we didn't have valid 100% coupon and have no valid quiz or bundle payment so return false 
		}
		
		// this happens in case of valid quiz payment	
		return true;	
	}
	
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
	            && ($wp->query_vars['watupro'] == 'paypal' or $wp->query_vars['watupro'] == 'paypal_bundle')) {
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
			 if(empty($user_ID) and !empty($_SESSION['watupro_access_code_' . $_POST['item_number']])) {
			 	 $_GET['access_code'] = $_SESSION['watupro_access_code_' . $_POST['item_number']];
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
		else {
			$exam = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}watupro_master WHERE ID=%d", $_POST['item_number']));
			$fee = $exam->fee;
			$coupon_exam_id = $exam->ID;
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
						
		if($_POST['mc_gross']>=$fee ) {
			$payment_amount_okay = true;
		}
		else return self::log_and_exit("Wrong amount: $_POST[mc_gross] when price is {$fee}"); 
		
		// everything OK, insert payment
		if($payment_completed and $txn_id_okay and $receiver_okay and $payment_currency_okay 
				and $payment_amount_okay) {						
			$wpdb->query($wpdb->prepare("INSERT INTO {$wpdb->prefix}watupro_payments SET 
				exam_id=%d, user_id=%s, date=CURDATE(), amount=%s, status='completed', paycode=%s, 
				method='paypal', bundle_id=%d, access_code=%s", 
				@$exam->ID, @$_GET['user_id'], $fee, $_POST['txn_id'], @$bundle->ID, @$_GET['access_code']));
				
			// cleanup coupon code if any
			if(!empty($coupon_code)) WatuPROICoupons :: coupon_used($coupon, $_GET['user_id']);	
			if(!self :: $pdt_mode) exit;
			else watupro_redirect(get_permalink($post->ID));
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
	static function Stripe($is_bundle = false) {
		global $wpdb, $user_ID;
		require_once(WATUPRO_PATH.'/i/lib/Stripe.php');
 
		$stripe = array(
		  'secret_key'      => get_option('watupro_stripe_secret'),
		  'publishable_key' => get_option('watupro_stripe_public')
		);
		 
		Stripe::setApiKey($stripe['secret_key']);
		$token  = $_POST['stripeToken'];
		
		if($is_bundle) {
			$bundle = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".WATUPRO_BUNDLES." WHERE ID=%d", $_POST['bundle_id']));
			$fee = $bundle->price;
			$coupon_exam_id = 0;
		}
		else {
			$exam = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".WATUPRO_EXAMS." WHERE ID=%d", $_POST['exam_id']));
			$fee = $exam->fee;
			$coupon_exam_id = $exam->ID;
		}
		
		// used coupon?		
		$coupon_code = WatuPROICoupons :: existing_coupon($user_ID);
		 
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
			 $customer = Stripe_Customer::create(array(
		      'email' => empty($user->user_email) ? $_POST['stripeEmail'] : $user->user_email,
		      'card'  => $token
		  ));				
			
		  $charge = Stripe_Charge::create(array(
		      'customer' => $customer->id,
		      'amount'   => $fee*100,
		      'currency' => $currency
		  ));
		} 
		catch (Exception $e) {
			wp_die($e->getMessage());
		}	  
		
		// insert payment record
		$wpdb->query($wpdb->prepare("INSERT INTO ".WATUPRO_PAYMENTS." SET 
			exam_id=%d, user_id=%s, date=CURDATE(), amount=%s, status='completed', paycode=%s, 
			method='stripe', bundle_id=%d, access_code=%s", 
			@$exam->ID, $user_ID, $fee, $customer->ID, @$bundle->ID, @$_SESSION['watupro_access_code_'.@$exam->ID]));
			
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
}