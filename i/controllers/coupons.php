<?php
// manage and apply coupon codes for paid quizzes
class WatuPROICoupons {
	// applies the coupon and returns the price
	// @param $coupon - object, the coupon code fetched from the DB
	// @param $price - the price of the stuff
	// @param $user_id INT - the user who uses it
	// @param $update - boolean, whether we should update the user meta
	static function apply($coupon, $price, $user_id, $update = true) {
		global $wpdb, $user_ID;		
		// recalc price
		if(empty($coupon->disc_type)  or $coupon->disc_type == 'percent') $price = $price - round($price *  ($coupon->discount/ 100), 2);
		else {
			$price = $price - $coupon->discount;
			if($price < 0) $price = 0;
		} 
		
		// update user meta
		if($update) {
			if($user_id) update_user_meta($user_id, 'watupro_coupon', $coupon->code);
<<<<<<< HEAD
			else $_SESSION['watupro_nouser_coupon'] = $coupon->code;
		}
		
=======
			else setcookie('watupro_nouser_coupon', $coupon->code, time() + 24*3600, '/');
		}
		session_write_close();
>>>>>>> branch/6.7.2
		return $price;
	}
	
	// is the coupon valid?
	// @param $coupon - the DB record of the coupon
	static function is_valid($coupon, $quiz_id = 0) {
		if(empty($coupon->ID)) return false;
		// quiz condition?
		if(!empty($quiz_id) and !empty($coupon->quiz_id) and $coupon->quiz_id != $quiz_id) return false;
		
		// date condition?
		if($coupon->date_condition) {
			$now = current_time('timestamp');
			$start = strtotime($coupon->start_date);
			$end = strtotime($coupon->end_date);
			
			if($now < $start or $now > $end) return false;
		}		
		
		if($coupon->num_uses == 0 or ($coupon->num_uses - $coupon->times_used) > 0) return true;
		return false;
	}
	
	// gets currently used coupon. If user is logged in, searches meta, otherwise sessions
<<<<<<< HEAD
	static function existing_coupon($user_id = 0) {
=======
	static function existing_coupon($user_id = 0) {	
		
>>>>>>> branch/6.7.2
		$existing_coupon = '';
		if($user_id) {
			$existing_coupon = get_user_meta($user_id, 'watupro_coupon', true);
			return $existing_coupon;
		}

		// no user ID but session?
<<<<<<< HEAD
		if(!empty($_SESSION['watupro_nouser_coupon'])) $existing_coupon = $_SESSION['watupro_nouser_coupon'];
=======
		if(!empty($_COOKIE['watupro_nouser_coupon'])) $existing_coupon = $_COOKIE['watupro_nouser_coupon'];
>>>>>>> branch/6.7.2
		
		return $existing_coupon;
	}	
	
	// the coupon is used, update user meta and coupon usages
	static function coupon_used($coupon, $user_id) {
		global $wpdb;
		
		update_user_meta($user_id, 'watupro_coupon', '');
<<<<<<< HEAD
		unset($_SESSION['watupro_nouser_coupon']);
		
		$wpdb->query($wpdb->prepare("UPDATE ".WATUPRO_COUPONS." SET times_used = times_used+1 WHERE ID=%d", $coupon->ID));
=======
		//setcookie('watupro_nouser_coupon', '', time() - 3600*24, '/');?>
		<script type="text/javascript">
		var d = new Date();
		d.setTime(d.getTime() + (24*3600*1000));
		var expires = "expires="+ d.toUTCString();
		document.cookie = "watupro_nouser_coupon=<?php echo $access_code;?>;" + expires + ";path=/";
		</script>
		<?php
		
		$wpdb->query($wpdb->prepare("UPDATE ".WATUPRO_COUPONS." SET times_used = times_used+1 WHERE ID=%d", $coupon->ID));
		session_write_close();
>>>>>>> branch/6.7.2
	} // end coupon_used()
	
	// manage coupons
	static function manage() {
<<<<<<< HEAD
		global $wpdb;
		$dateformat = get_option('date_format');
		$start_date = date("Y-m-d");
		$end_date = date("Y-m-d", strtotime("+1 month"));
=======
		global $wpdb, $user_ID;
		$dateformat = get_option('date_format');
		$start_date = date("Y-m-d");
		$end_date = date("Y-m-d", strtotime("+1 month"));
		$multiuser_access = 'all';
		if(watupro_intel()) $multiuser_access = WatuPROIMultiUser::check_access('coupons_access');
		
		$own_sql = ($multiuser_access == 'own') ? $wpdb->prepare(" AND editor_id = %d ", $user_ID) : "";
>>>>>>> branch/6.7.2
		
		if(!empty($_POST['add']) and check_admin_referer('watupro_coupons')) {
			$wpdb->query($wpdb->prepare("INSERT INTO ".WATUPRO_COUPONS." 
				SET discount=%d, code=%s, num_uses=%d, disc_type=%s, 
<<<<<<< HEAD
				quiz_id=%d, date_condition=%d, start_date=%s, end_date=%s", 
				$_POST['discount'], $_POST['code'], $_POST['num_uses'], $_POST['disc_type'], $_POST['quiz_id'],
				intval(@$_POST['date_condition']), $_POST['start_date'], $_POST['end_date']));
=======
				quiz_id=%d, date_condition=%d, start_date=%s, end_date=%s, editor_id=%d", 
				$_POST['discount'], $_POST['code'], $_POST['num_uses'], $_POST['disc_type'], $_POST['quiz_id'],
				intval(@$_POST['date_condition']), $_POST['start_date'], $_POST['end_date'], $user_ID));
>>>>>>> branch/6.7.2
			watupro_redirect("admin.php?page=watupro_coupons");	
		}
		
		if(!empty($_POST['del']) and check_admin_referer('watupro_coupons')) {
<<<<<<< HEAD
			$wpdb->query($wpdb->prepare("DELETE FROM ".WATUPRO_COUPONS." WHERE ID=%d", $_POST['id']));
=======
			$wpdb->query($wpdb->prepare("DELETE FROM ".WATUPRO_COUPONS." WHERE ID=%d $own_sql ", $_POST['id']));
>>>>>>> branch/6.7.2
			watupro_redirect("admin.php?page=watupro_coupons");
		}
		
		if(!empty($_POST['save']) and check_admin_referer('watupro_coupons')) {
			$wpdb->query($wpdb->prepare("UPDATE ".WATUPRO_COUPONS." 
				SET discount=%d, code=%s, num_uses=%d, disc_type=%s, quiz_id=%d, date_condition=%d, 
<<<<<<< HEAD
				start_date=%s, end_date=%s WHERE ID=%d", 
=======
				start_date=%s, end_date=%s WHERE ID=%d $own_sql ", 
>>>>>>> branch/6.7.2
				$_POST['discount'], $_POST['code'], $_POST['num_uses'], $_POST['disc_type'], $_POST['quiz_id'], 
				intval(@$_POST['date_condition']), $_POST['start_date'], $_POST['end_date'], $_POST['id']));			
		}
		
		// select existing coupons
<<<<<<< HEAD
		$coupons = $wpdb->get_results("SELECT * FROM ".WATUPRO_COUPONS." ORDER BY ID");
=======
		$coupons = $wpdb->get_results("SELECT * FROM ".WATUPRO_COUPONS." WHERE 1=1 $own_sql ORDER BY ID");
>>>>>>> branch/6.7.2
		
		$currency = get_option('watupro_currency');		
		
		// select all quizzes
		$quizzes = $wpdb->get_results("SELECT ID, name FROM ".WATUPRO_EXAMS." ORDER BY name");
		
		watupro_enqueue_datepicker();
		include(WATUPRO_PATH."/i/views/coupons.html.php");
	} // end manage()
	
	// shortcode to display enter coupon field
	static function coupon_field($atts) {
		global $wpdb;
	
<<<<<<< HEAD
		$any_coupons = $wpdb->get_var("SELECT id FROM ".WATUPRO_COUPONS." WHERE num_uses = 0 OR (num_uses - times_used) > 0");
		if(!$any_coupons) return '';
		
=======
		$any_coupons = $wpdb->get_var("SELECT ID FROM ".WATUPRO_COUPONS." WHERE num_uses = 0 OR (CAST(num_uses as signed) - CAST(times_used as signed)) > 0");
>>>>>>> branch/6.7.2
		$content = '';		
		
		// if coupon is entered but is not valid add it to the output
		if(!empty($_POST['watupro_coupon'])) {
			$coupon = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".WATUPRO_COUPONS." WHERE code=%s", trim($_POST['watupro_coupon'])));
			if(self :: is_valid($coupon)) $content .= "<p align='center'>".__('Valid coupon code applied.', 'watupro')."</p>";
			else $content .= "<p align='center'>".__('The coupon code is invalid.', 'watupro')."</p>";
		}
		
		$label = empty($atts['label']) ? __('Coupon code', 'watupro') : $atts['label'];	
		$button_text = empty($atts['button_text']) ? __('Apply', 'watupro') : $atts['button_text'];
		$content .= '<form method="post" action="#">
				<p align="center">'.$label.' <input type="text" name="watupro_coupon" value="'.@$_POST['watupro_coupon'].'"> <input type="submit" value="'.$button_text.'"></p>
		</form>';
		
		return $content;
	} // end coupon_field
}